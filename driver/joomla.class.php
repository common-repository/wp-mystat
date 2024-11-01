<?php
if(!defined('MYSTAT_VERSION')){
  throw new Exception('File not exist 404');
}

class mystat_joomla{

  protected $run = false;
  protected $php = false;
  protected $context;
  protected $cookie = false;
  protected $param = false;

  public function __construct($context,$param=false){
    $this->context = $context;
    $this->param = $param;
  }

  public function getName(){
    return 'joomla';
  }

  public function isEngineRun(){
    if(!defined('_JEXEC') or !class_exists('JRequest')){
      return 'Driver can not run without Joomla CMS';
    }
    return true;
  }

  public function getTime($no_gmt=false){
    return JFactory::getDate('now',$no_gmt?null:JFactory::getApplication()->get('offset'))->format('U',!$no_gmt);
  }

  public function getGMT(){
    return (int)JFactory::getDate('now',JFactory::getApplication()->get('offset'))->format('Z',true)/3600;
  }

  public function isAjax(){
    return $this->getParam('ajax','false')=='false'?false:true;
  }

  public function startDriver(){
    if(in_array($this->getParam('task'),Array('install','install.install'))){
      $this->getInstallTable();
      $this->installModule();
      $this->context->updateDefinition(false);
      return;
    }
    if(in_array($this->getParam('task'),Array('remove'))){
      $this->getUninstallTable();
      return;
    }
    $app = JFactory::getApplication();
    if($app->getName()=='site'){
      $this->dbSizeCollect();
      $page = (string)$this->getParam('report','dashboard');
      if(in_array($page,Array('insert','image'))){
        call_user_func(array_shift($this->run),array_shift($this->run));
        echo '{"success":true}';
        exit;
      }
      $this->initJoomla();
      call_user_func(array_shift($this->php),array_shift($this->php));
      return;
    }
    if(!$app->isAdmin()){
      echo '<h1>ACCESS DENY</h1>';
      return;
    }
    $dbo = JFactory::getDbo();
    $query = $dbo->getQuery(true);
    if($this->getRole()=='USER'){
      $query->update($dbo->quoteName('#__assets'))->set(Array('rules = \'{"core.admin":[],"core.manage":{"1":1},"core.create":[],"core.delete":[],"core.edit":[],"core.edit.state":[]}\''))->where(Array('name = "com_mystat"'));
    }elseif($this->getRole()=='EDITOR'){
      $query->update($dbo->quoteName('#__assets'))->set(Array('rules = \'{"core.admin":[],"core.manage":{"1":1,"9":0,"2":0},"core.create":[],"core.delete":[],"core.edit":[],"core.edit.state":[]}\''))->where(Array('name = "com_mystat"'));
    }else{
      $query->update($dbo->quoteName('#__assets'))->set(Array('rules = \'\''))->where(Array('name = "com_mystat"'));
    }
    $dbo->setQuery($query);
    $dbo->execute();    
    JToolBarHelper::title($this->__('My Statistics'), 'health.png');
    if($error = $this->context->isInstallCorrect(true) and sizeof($error)>0){
      foreach($error as $e){
        switch($e){
          case 'WRITE':
            echo '<div class="alert alert-error">';
            echo '<strong>'.$this->__('My Statistics').':</strong> '.$this->__('Plugin has no permissions to write to the directory "cache". Plugin can not independently resolve this error. Contact your administrator.').'';
            echo '</div>';
            break;
          case 'ZLIB':
            echo '<div class="alert">';
            echo '<strong>'.$this->__('My Statistics').':</strong> <a href="http://php.net/manual/en/zlib.installation.php" target="_blank">'.$this->__('You need set up your PHP with ZLIB extension').'</a>';
            echo '</div>';
            break;
          case 'ZIP':
            echo '<div class="alert">';
            echo '<strong>'.$this->__('My Statistics').':</strong> <a href="http://php.net/manual/en/zip.installation.php" target="_blank">'.$this->__('You need set up your PHP with ZIP extension').'</a>';
            echo '</div>';
            break;
          case 'DOM':
            echo '<div class="alert alert-error">';
            echo '<strong>'.$this->__('My Statistics').':</strong> <a href="http://php.net/manual/en/dom.installation.php" target="_blank">'.$this->__('You need set up your PHP with DOM extension').'</a>';
            echo '</div>';
            break;
          case 'XSLT':
            echo '<div class="alert alert-error">';
            echo '<strong>'.$this->__('My Statistics').':</strong> <a href="http://php.net/manual/en/xsl.installation.php" target="_blank">'.$this->__('You need set up your PHP with XSL extension').'</a>';
            echo '</div>';
            break;
        }
      }
      return false;
    }
    if($this->run===false){return;}
    $this->adminScripts();
    if($this->context->isNeedUpdate()){
      $bar = JToolBar::getInstance('toolbar');
      $dhtml = '<button onclick="jQuery(\'#loading\').show();jQuery.ajax({url: document.location+\'&format=raw\',data: {report: \'update\'},timeout: 300000, dataType: \'html\',type: \'POST\',success: function(data, textStatus){document.location=\''.$this->getRedirectUri().'\';},error: function(){jQuery(\'#loading\').hide();alert(\''.addslashes($this->__('An error occurred during the update, please, try again later.')).'\');}});return false;" class="btn btn-small btn-warning"><span class="icon-download"></span><strong>'.$this->__('My Statistics').'</strong>: '.$this->__('Need to update definitions').'</button>';
      $bar->appendButton('Custom', $dhtml);
    }
    $ajax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')?true:false;
    if($this->getParam('in')){$ajax=true;}
    echo !$ajax?'<div id="mystat">':'';
    if(file_exists($this->getCacheDir().'alert.dat')){
      $alert = @file_get_contents($this->getCacheDir().'alert.dat');
      if(trim($alert)!=''){
        $alert = strip_tags(base64_decode($alert),'<br/><b><i><a><div><p><img><span><strong><em><table><td><th><tr><h1><h2><h3><h4><button>');
        $app->enqueueMessage($alert,'');
      }
    }
    call_user_func(array_shift($this->run),array_shift($this->run));
    echo !$ajax?'</div>':'';
  }

  public function getCacheDir($web=false){
    if($web){
      preg_match('/(.*)\/components\/com_([A-z]*)/i',JPATH_COMPONENT,$m);
      return JUri::root().'administrator/components/com_'.$m[2].'/cache/';
    }
    return dirname(__FILE__).'/../cache/';
  }

  public function setUpdateStop($report=false){
  }

  public function setUpdateStart(){
    echo str_repeat('.',100);
    flush();
    usleep(100);
  }

  public function setRunHook($el,$func){
    $this->run = Array($func,$el);
  }

  public function getParam($name,$default=false){
    $el = JRequest::getVar($name);
    return !empty($el)?$el:$default;
  }

  public function getCurrentRole(){
    $user = JFactory::getUser();
    if($user->authorise('core.admin')){
      return 'ADMIN';
    }
    if($user->authorise('core.edit')){
      return 'EDITOR';
    }
    return 'USER';
  }

  public function getRole(){
    $role = 'ADMIN';
    switch($this->getOption('mystataccess','ADMIN')){
      case 'USER':
        $role = 'USER';
        break;
      case 'EDITOR':
        $role = 'EDITOR';
        break;
      case 'ADMIN':
      default:
        $role = 'ADMIN';
        break;
    }
    return $role;
  }

  public function setRole($role){
    switch($role){
      case 'USER':
        $this->setOption('mystataccess','USER');
        break;
      case 'EDITOR':
        $this->setOption('mystataccess','EDITOR');
        break;
      case 'ADMIN':
      default:
        $this->setOption('mystataccess','ADMIN');
        break;
    }
    return $this;
  }

  public function isAccess(){
    $app = JFactory::getApplication();
    if(!$app->isAdmin()){
      return false;
    }
    $rule = $this->getRole();
    $cur = $this->getCurrentRole();
    if($cur =='ADMIN' or $rule==$cur or $rule=='USER'){
      return true;
    }
    return false;
  }

  public function getUserHash(){
    if($this->cookie===false){
      $app = JFactory::getApplication();
      $session = JFactory::getSession();
      $cookies = $session->get('mystathash', '');
      if(!empty($cookies)){
        $this->cookie = $cookies;
      }else{
        $this->cookie = md5($_SERVER['HTTP_USER_AGENT'].$this->context->getIP().rand());
      }
    }
    return $this->cookie;
  }

  protected function initJoomla(){
    $app = JFactory::getApplication();
    if(!$app->isAdmin()){
      $session = JFactory::getSession();
      $cookie = $session->get('mystathash','');
      if(empty($cookie)){
        $cookie = $this->getUserHash();
      }
      $session->set('mystathash', $cookie);
    }
  }

  public function isFeed(){
    return false;
  }

  public function getOption($name,$default=false){
    $dbo = JFactory::getDbo();
    $sql = $dbo->getQuery(true)->select('COUNT(*)')->from('#__extensions')->where($dbo->qn('element').' = '.$dbo->q('com_mystat'));
    $dbo->setQuery($sql);
    $count = $dbo->loadResult();
    if(!$count){return $default;}
    $table = new JTableExtension(JFactory::getDbo());
    $table->load(array('element' => 'com_mystat'));
    $prm = json_decode($table->params,true);
    if(isset($prm[$name])){
      return $prm[$name];
    }
    return $default;
  }

  public function setOption($name,$value=false){
    $dbo = JFactory::getDbo();
    $sql = $dbo->getQuery(true)->select('COUNT(*)')->from('#__extensions')->where($dbo->qn('element').' = '.$dbo->q('com_mystat'));
    $dbo->setQuery($sql);
    $count = $dbo->loadResult();
    if(!$count){return $this;}
    $table = new JTableExtension(JFactory::getDbo());
    $table->load(array('element' => 'com_mystat'));
    $prm = json_decode($table->params,true);
    if($value===false){
      if(isset($prm[$name])){
        unset($prm[$name]);
      }
    }else{
      $prm[$name] = $value;
    }
    $table->set('params', json_encode($prm));
    $table->store();
    return $this;
  }

  public function __($text){
    $txt = JText::_($this->getStringKeyFromSource($text));
    if($txt==$this->getStringKeyFromSource($text)){
      return $text;
    }
    return $txt;
  }

  public function getWebPath(){
    preg_match('/(.*)\/components\/com_([A-z]*)/i',JPATH_COMPONENT,$m);
    return JUri::root().'administrator/components/com_'.$m[2].'/asset/';
  }

  public function getExportUrl(){
    return $this->getRedirectUri().'&format=raw&ajax=true';
  }

  private function getRedirectUri($report=false){
    preg_match('/(.*)\/components\/com_([A-z]*)/i',JPATH_COMPONENT,$m);
    return JUri::root().'administrator/index.php?option=com_'.$m[2].($report!==false?'&report='.$report:'');
  }

  public function getLanguage(){
    $lang = JFactory::getLanguage()->getLocale();
    return strtoupper(substr($lang[0],0,2));
  }
  

  public function is404(){
    return false;
  }

  public function setCodeHook($el,$func){
    $this->php = Array($func,$el);
  }

  public function setJsSendClick($id){
    $url = JUri::root().'index.php?option=com_ajax&module=mystat';
    $ret =  <<<JS
    <script type="text/javascript" charset="utf-8">//<![CDATA[
      function runStatisticMyStatClickSend(data){
        $.ajax({
          url: '{$url}&format=json',
          data: {
            report: 'insertclick',
            data: Base64.encode(JSON.stringify(data)),
            coding: 'base64'
          },
          dataType: 'json',
          type: 'POST',
          success: function(data, textStatus){
          },
          error: function(){
          }
        });
      }  
    //]]></script>
JS;
    return $ret;
  }

  public function setJsSend($id){
    $url = JUri::root().'index.php?option=com_ajax&module=mystat';
    $ret =  <<<JS
    <noscript>
      <img src="{$url}&format=raw&report=image&id={$id}" width="1px" height="1px" style="position:absolute;width:1px;height:1px;bottom:0px;right:0px;" />
    </noscript>
    <script type="text/javascript" charset="utf-8">
      jQuery(document).ready(function($) {
        var img = new Image();
        img.src = '{$url}&format=raw&report=image&id={$id}';
        img.width = '1px';
        img.height = '1px';
        img.style.position = 'absolute';
        img.style.width = '1px';
        img.style.height = '1px';
        img.style.bottom = '0';
        img.style.right = '0';
        document.body.appendChild(img);
        var stat = runStatisticMyStat();
        $.ajax({
          url: '{$url}&format=json',
          data: {
            report: 'insert',
            data: Base64.encode(JSON.stringify(stat)),
            coding: 'base64'
          },
          dataType: 'json',
          type: 'POST',
          success: function(data, textStatus){
          },
          error: function(){
          }
        });
      });
    </script>
JS;
    return $ret;
  }

  public function getStatCacheByUserAgent($id,$ua){
    $param = new \stdClass();
    $dbo = JFactory::getDbo();
    $query = $dbo->getQuery(true)
      ->select('*')
      ->from($dbo->quoteName('#__mystatdata'))
      ->where('ua='.$dbo->Quote($ua))
      ->where('browser IS NOT NULL')
      ->where('browser != ""')
      ->where('browser != "Default Browser"')
      ->where('id != '.(int)$id)
      ->order('created_at DESC');
    $dbo->setQuery($query);
    $row=$dbo->loadObject();
    if(!empty($row)){
      $param->browser = (string)$row->browser;
      $param->version = (string)$row->browser_version;
      $param->os = (string)$row->os;
      $param->osver = (string)$row->osver;
      $param->osname = (string)$row->osname;
      $param->osbit = (int)$row->osbit;
      $param->crawler = (bool)$row->crawler;
      if($ua==''){$param->crawler = true;}
      $param->mobile = (bool)$row->mobile;
      $param->tablet = (bool)$row->tablet;
      $param->device = (string)$row->device;
      $param->device_name = (string)$row->device_name;
    }
    return $param;
  }

  public static function convertResult($row){
    $el = new myStat_row();
    $el->setJson($row->param);
    $el->time_load = (float)$row->time_load;
    $el->id = (int)$row->id;
    $el->hash = (string)$row->hash;
    $el->ua = (string)$row->ua;
    $el->browser = (string)$row->browser;
    $el->version = (string)$row->browser_version;
    $el->os = (string)$row->os;
    $el->osver = (string)$row->osver;
    $el->osname = (string)$row->osname;
    $el->osbit = (int)$row->osbit;
    $el->crawler = (bool)$row->crawler;
    $el->mobile = (bool)$row->mobile;
    $el->tablet = (bool)$row->tablet;
    $el->device = (string)$row->device;
    $el->device_name = (string)$row->device_name;
    $el->ip = (float)$row->ip;
    $el->country = strtoupper((string)$row->country);
    $el->city = (string)$row->city;
    $el->www = (bool)$row->www;
    $el->image = (string)$row->image;
    $el->host = (string)$row->host;
    $el->lang = strtoupper((string)$row->lang);
    $el->uri = (string)$row->uri;
    $el->file = (string)$row->file;
    $el->gzip = (bool)$row->gzip;
    $el->deflate = (bool)$row->deflate;
    $el->proxy = (bool)$row->proxy;
    $el->referer = new stdClass();
    $el->referer->url = (string)$row->referer;
    $el->referer->type = (string)$row->reftype;
    $el->referer->name = (string)$row->refname;
    $el->referer->query = (string)$row->refquery;
    $el->is404 = (bool)$row->is404;
    $el->tor = (bool)$row->is_tor;
    $el->feed = (bool)$row->is_feed;
    $el->title = (string)$row->title;
    $screen = (string)$row->screen;
    $screen = preg_split('/x/',$screen);
    $el->screen = new stdClass();
    $el->screen->width = isset($screen[0])?(int)$screen[0]:0;
    $el->screen->height = isset($screen[1])?(int)$screen[1]:0;
    $el->screen->depth = (int)$row->depth;
    $el->count = (int)$row->count;
    $el->created_at = strtotime($row->created_at);
    $el->updated_at = strtotime($row->updated_at);
    return $el;
  }

  public function getStatById($id){
    $dbo = JFactory::getDbo();
    $query = $dbo->getQuery(true)
      ->select('*')
      ->from($dbo->quoteName('#__mystatdata'))
      ->where('id = '.(int)$id);
    $dbo->setQuery($query);
    $row=$dbo->loadObject();
    $el = Array();
    if(!empty($row)){
      $el = $this->convertResult($row);
    }
    return $el;
  }

  public function setStatInsertFirst($param){
    $dbo = JFactory::getDbo();
    $query = $dbo->getQuery(true)
      ->select('id')
      ->from($dbo->quoteName('#__mystatdata'))
      ->where('created_at>='.'TIMESTAMP('.$dbo->Quote(date('Y-m-d',$this->getTime(false))).')')
      ->where('ip='.$dbo->Quote($param->ip))
      ->where('ua='.$dbo->Quote($param->ua))
      ->where('hash='.$dbo->Quote($param->hash))
      ->where('referer='.$dbo->Quote($param->referer->url))
      ->where('host='.$dbo->Quote($param->host))
      ->where('uri='.$dbo->Quote($param->uri));
    $dbo->setQuery($query);
    $id=(int)$dbo->loadResult();
    $timer = microtime(true);
    if($id==0){
      $query = $dbo->getQuery(true)
        ->insert($dbo->quoteName('#__mystatdata'))
        ->set('time_start='.($timer-floor($timer))*10000)
        ->set('hash='.$dbo->Quote($param->hash))
        ->set('ua='.$dbo->Quote($param->ua))
        ->set('time_load=0')
        ->set('ip='.$dbo->Quote($param->ip))
        ->set('host='.$dbo->Quote($param->host))
        ->set('www='.(int)$param->www)
        ->set('uri='.$dbo->Quote($param->uri))
        ->set('referer='.$dbo->Quote($param->referer->url))
        ->set('lang='.$dbo->Quote($param->lang))
        ->set('gzip='.(int)$param->gzip)
        ->set('deflate='.(int)$param->deflate)
        ->set('proxy='.(int)$param->proxy)
        ->set('is404='.(int)$param->is404)
        ->set('is_feed='.(int)$param->feed)
        ->set('file='.$dbo->Quote($param->file))
        ->set('title=""')
        ->set('screen=""')
        ->set('depth=0')
        ->set('count=1')
        ->set('created_at='.$dbo->Quote(date('Y-m-d H:i:s',$this->getTime(false))))
        ->set('updated_at='.$dbo->Quote(date('Y-m-d H:i:s',$this->getTime(false))));
      $dbo->setQuery($query);
      $dbo->execute();
      if($dbo->getAffectedRows()>0){
        $id=$dbo->insertId();
      }
      return $id;
    }
    $query = $dbo->getQuery(true)
      ->update($dbo->quoteName('#__mystatdata'))
      ->set('time_start='.($timer-floor($timer))*10000)
      ->set('count=count+1')
      ->set('updated_at='.$dbo->Quote(date('Y-m-d H:i:s',$this->getTime(false))))
      ->where('id='.$id);
    $dbo->setQuery($query);
    $dbo->execute();
    return -$id;
  }

  public function setStatInsertNext($id,$param){
    if($id==0){return false;}
    $dbo = JFactory::getDbo();
    $query = $dbo->getQuery(true)
      ->update($dbo->quoteName('#__mystatdata'))
      ->set('browser='.$dbo->Quote($param->browser))
      ->set('browser_version='.$dbo->Quote($param->version))
      ->set('device='.$dbo->Quote($param->device))
      ->set('device_name='.$dbo->Quote($param->device_name))
      ->set('referer='.$dbo->Quote($param->referer->url))
      ->set('reftype='.$dbo->Quote($param->referer->type))
      ->set('refname='.$dbo->Quote($param->referer->name))
      ->set('refquery='.$dbo->Quote($param->referer->query))
      ->set('country='.$dbo->Quote($param->country))
      ->set('city='.$dbo->Quote($param->city))
      ->set('mobile='.(int)$param->mobile)
      ->set('tablet='.(int)$param->tablet)
      ->set('crawler='.(int)$param->crawler)
      ->set('os='.$dbo->Quote($param->os))
      ->set('osver='.$dbo->Quote($param->osver))
      ->set('osname='.$dbo->Quote($param->osname))
      ->set('osbit='.(int)$param->osbit)
      ->set('updated_at='.$dbo->Quote(date('Y-m-d H:i:s',$this->getTime(false))))
      ->where('id='.$id);
    $dbo->setQuery($query);
    $dbo->execute();
    return true;
  }

  public function setStatImage($id,$ip){
    if($id>0){
      $dbo = JFactory::getDbo();
      $query = $dbo->getQuery(true)
        ->select('id')
        ->from($dbo->quoteName('#__mystatdata'))
        ->where('id='.(int)$id)
        ->where('ip='.ip2long($ip));
      $dbo->setQuery($query);
      $dbo->execute();
      if($dbo->getAffectedRows()>0){
        $query = $dbo->getQuery(true)
          ->update('#__mystatdata')
          ->set('image=1')
          ->where('id='.$id);
        $dbo->setQuery($query);
        $dbo->execute();
      }
    }
    Header('Content-Type: image/gif');
    echo base64_decode('R0lGODlhAQABAJEAAAAAAP///////wAAACH5BAEAAAIALAAAAAABAAEAAAICVAEAOw==');
    exit;
  }

  public function setStatUpdate($id,$param,$ip,$tor){
    if($id>0){
      $timer = microtime(true);
      $dbo = JFactory::getDbo();
      $query = $dbo->getQuery(true)
        ->select('updated_at')
        ->select('time_start')
        ->from($dbo->quoteName('#__mystatdata'))
        ->where('id='.(int)$id)
        ->where('ip='.ip2long($ip));
      $dbo->setQuery($query);
      $dbo->execute();
      if($dbo->getAffectedRows()==0){return;}
      $rows = $dbo->loadAssoc();
      $tload = ($this->getTime(false)+($rows['time_start']/10000))-(strtotime($rows['updated_at'])+($timer-floor($timer)));
      $title = (string)$param->title;unset($param->title);
      $screen = '';
      if(isset($param->screen->width) and (int)$param->screen->width>0){
        $screen = $param->screen->width.'x'.$param->screen->height;
        $depth = $param->screen->depth;
        unset($param->screen);
      }
      $query = $dbo->getQuery(true)
        ->update($dbo->quoteName('#__mystatdata'))
        ->set('time_load='.$tload)
        ->set('is_tor='.(int)$tor)
        ->set('title='.$dbo->Quote($title))
        ->set('screen='.$dbo->Quote($screen))
        ->set('depth='.(int)$depth)
        ->set('param='.$dbo->Quote(json_encode($param)))
        ->set('updated_at='.$dbo->Quote(date('Y-m-d H:i:s',$this->getTime(false))))
        ->where('id='.(int)$id);
      $dbo->setQuery($query);
      $dbo->execute();
    }
    $this->postDetected();
  }

  private function postDetected(){
    $start = time();
    $dbo = JFactory::getDbo();
    while((time()-$start)<10){
      $query = $dbo->getQuery(true)
        ->select('*')
        ->from($dbo->quoteName('#__mystatdata'))
        ->where('browser="" OR browser IS NULL')
        ->setLimit('1');
      $dbo->setQuery($query);
      $rows=$dbo->loadObject();
      if(sizeof($rows)>0){
        $this->context->setStatisticsById((int)$rows->id,$this->convertResult($rows));
      }else{
        break;
      }
    }
  }

  public function getStatByPeriod($from,$to){
    $dbo = JFactory::getDbo();
    $query = $dbo->getQuery(true)
      ->select('*')
      ->from($dbo->quoteName('#__mystatdata'))
      ->where('created_at>='.$dbo->Quote(date('Y-m-d 00:00:00',$from)))
      ->where('created_at<='.$dbo->Quote(date('Y-m-d 23:59:59',$to)));
    if($dbo->name=='mysqli'){
      $result = @mysqli_query($dbo->getConnection(),preg_replace('/#__/',$dbo->getPrefix(),$query),MYSQLI_USE_RESULT);
      if(!$result){return Array();}
      return new mystat_dbResultJoomla2($result);
    }
    $dbo->setQuery($query);
    return new mystat_dbResultJoomla1($dbo->getIterator());
  }

  protected function dbSizeCollect(){
    if($this->getOption('mystatcleanstart')==date('dmY',$this->getTime(false))){
      return;
    }
    $days = (int)$this->getOption('mystatcleanday',120);
    $days = $days>30?$days:30;
    $dbo = JFactory::getDbo();
    $query = $dbo->getQuery(true)
      ->delete('#__mystatdata')
      ->where('created_at<='.'TIMESTAMP('.$dbo->Quote(date('Y-m-d 00:00:00',strtotime(date('Y-m-d',$this->getTime(false)).' -'.$days.' days'))).')');
    $dbo->setQuery($query);
    $dbo->execute();
    $query = $dbo->getQuery(true)
      ->delete('#__mystatclick')
      ->where('created_at<='.'TIMESTAMP('.$dbo->Quote(date('Y-m-d 00:00:00',strtotime(date('Y-m-d',$this->getTime(false)).' -'.$days.' days'))).')');
    $dbo->setQuery($query);
    $dbo->execute();
    $query = $dbo->getQuery(true)
      ->delete('#__mystatsize')
      ->where('date<='.'TIMESTAMP('.$dbo->Quote(date('Y-m-d 00:00:00',strtotime(date('Y-m-d',$this->getTime(false)).' -'.$days.' days'))).')');
    $dbo->setQuery($query);
    $dbo->execute();

    $dbo->setQuery('OPTIMIZE TABLE '.$dbo->getPrefix().'mystatdata');
    $dbo->execute();
    $dbo->setQuery('OPTIMIZE TABLE '.$dbo->getPrefix().'mystatclick');
    $dbo->execute();
    $dbo->setQuery('OPTIMIZE TABLE '.$dbo->getPrefix().'mystatsize');
    $dbo->execute();

    $dbo->setQuery('SHOW TABLE STATUS LIKE \''.$dbo->getPrefix().'mystat%\'');
    $query = $dbo->loadAssocList();
    $size = 0;
    foreach($query as $el){
      $size+= $el['Data_length'] + $el['Index_length'];
    }
    $query = $dbo->getQuery(true)
      ->select('COUNT(*) as count')
      ->from($dbo->quoteName('#__mystatsize'))
      ->where('date='.$dbo->Quote(date('Y-m-d',$this->getTime(false))));
    $dbo->setQuery($query);
    $exist = $dbo->loadResult();
    if((int)$exist==0){
      $query = $dbo->getQuery(true)
        ->insert($dbo->quoteName('#__mystatsize'))
        ->set('date='.$dbo->Quote(date('Y-m-d',$this->getTime(false))))
        ->set('size='.$size);
      $dbo->setQuery($query);
      $dbo->execute();
    }else{
      $query = $dbo->getQuery(true)
        ->update($dbo->quoteName('#__mystatsize'))
        ->set('size='.$size)
        ->where('date='.$dbo->Quote(date('Y-m-d',$this->getTime(false))));
      $dbo->setQuery($query);
      $dbo->execute();
    }
    $this->setOption('mystatcleanstart',date('dmY',$this->getTime(false)));
  }

  public function getDbSizeByPeriod($from,$to){
    $dbo = JFactory::getDbo();
    $query = $dbo->getQuery(true)
      ->select('*')
      ->from($dbo->quoteName('#__mystatsize'))
      ->where('date>='.$dbo->Quote(date('Y-m-d 00:00:00',$from)))
      ->where('date<='.$dbo->Quote(date('Y-m-d 23:59:59',$to)));
    $dbo->setQuery($query);
    $dbo->execute();
    if($dbo->getAffectedRows()==0){return Array();}
    $query = $dbo->loadAssocList();
    return $query;
  }

################################################################################

  protected function adminScripts(){
    $webpath = $this->getWebPath();
    $document = JFactory::getDocument();
    $jquery = 'jquery.framework';
    JHtml::_($jquery);
    if($this->getOption('mystatproxygoogle','false')=='true'){
      $document->addScript('https://my-stat.com/google/loader.js');
    }else{
      $document->addScript('https://www.gstatic.com/charts/loader.js');
    }
    $document->addScriptVersion(trim($webpath,'/').'/logo.min.js','0.4.2');
    $document->addScriptVersion(trim($webpath,'/').'/moment.min.js','2.9.0');
    $document->addScriptVersion(trim($webpath,'/').'/jquery.mask.min.js','1.14.3');
    $document->addScriptVersion(trim($webpath,'/').'/jquery.daterangepicker.min.js','0.0.5');
    $document->addStyleSheetVersion(trim($webpath,'/').'/jquery.daterangepicker.min.css','0.0.5');
  }

  protected function getInstallTable(){
    $xml = '<?xml version="1.0" encoding="utf-8"?>
<data><row><Field>id</Field><Type>int(11) unsigned</Type><Null>NO</Null><Key>PRI</Key><Default>(NULL)</Default><Extra>auto_increment</Extra></row>
<row><Field>hash</Field><Type>varchar(32)</Type><Null>YES</Null><Key></Key><Default>(NULL)</Default><Extra></Extra></row>
<row><Field>ua</Field><Type>text</Type><Null>YES</Null><Key></Key><Default>(NULL)</Default><Extra></Extra></row>
<row><Field>browser</Field><Type>varchar(200)</Type><Null>YES</Null><Key></Key><Default>(NULL)</Default><Extra></Extra></row>
<row><Field>browser_version</Field><Type>varchar(10)</Type><Null>YES</Null><Key></Key><Default>(NULL)</Default><Extra></Extra></row>
<row><Field>device</Field><Type>varchar(200)</Type><Null>YES</Null><Key></Key><Default>(NULL)</Default><Extra></Extra></row>
<row><Field>device_name</Field><Type>varchar(200)</Type><Null>YES</Null><Key></Key><Default>(NULL)</Default><Extra></Extra></row>
<row><Field>time_start</Field><Type>int(11) unsigned</Type><Null>YES</Null><Key></Key><Default>(NULL)</Default><Extra></Extra></row>
<row><Field>time_load</Field><Type>float(9,4) unsigned</Type><Null>YES</Null><Key></Key><Default>(NULL)</Default><Extra></Extra></row>
<row><Field>ip</Field><Type>bigint(20)</Type><Null>YES</Null><Key></Key><Default>(NULL)</Default><Extra></Extra></row>
<row><Field>image</Field><Type>tinyint(1)</Type><Null>YES</Null><Key></Key><Default>(NULL)</Default><Extra></Extra></row>
<row><Field>proxy</Field><Type>tinyint(1)</Type><Null>YES</Null><Key></Key><Default>(NULL)</Default><Extra></Extra></row>
<row><Field>is404</Field><Type>tinyint(1)</Type><Null>YES</Null><Key></Key><Default>(NULL)</Default><Extra></Extra></row>
<row><Field>is_tor</Field><Type>tinyint(1)</Type><Null>YES</Null><Key></Key><Default>(NULL)</Default><Extra></Extra></row>
<row><Field>is_feed</Field><Type>tinyint(1)</Type><Null>YES</Null><Key></Key><Default>(NULL)</Default><Extra></Extra></row>
<row><Field>title</Field><Type>text</Type><Null>YES</Null><Key></Key><Default>(NULL)</Default><Extra></Extra></row>
<row><Field>host</Field><Type>varchar(200)</Type><Null>YES</Null><Key></Key><Default>(NULL)</Default><Extra></Extra></row>
<row><Field>www</Field><Type>tinyint(1)</Type><Null>YES</Null><Key></Key><Default>(NULL)</Default><Extra></Extra></row>
<row><Field>file</Field><Type>varchar(200)</Type><Null>YES</Null><Key></Key><Default>(NULL)</Default><Extra></Extra></row>
<row><Field>uri</Field><Type>text</Type><Null>YES</Null><Key></Key><Default>(NULL)</Default><Extra></Extra></row>
<row><Field>referer</Field><Type>text</Type><Null>YES</Null><Key></Key><Default>(NULL)</Default><Extra></Extra></row>
<row><Field>lang</Field><Type>char(2)</Type><Null>YES</Null><Key></Key><Default>(NULL)</Default><Extra></Extra></row>
<row><Field>reftype</Field><Type>varchar(50)</Type><Null>YES</Null><Key></Key><Default>(NULL)</Default><Extra></Extra></row>
<row><Field>refname</Field><Type>varchar(50)</Type><Null>YES</Null><Key></Key><Default>(NULL)</Default><Extra></Extra></row>
<row><Field>refquery</Field><Type>text</Type><Null>YES</Null><Key></Key><Default>(NULL)</Default><Extra></Extra></row>
<row><Field>country</Field><Type>char(2)</Type><Null>YES</Null><Key></Key><Default>(NULL)</Default><Extra></Extra></row>
<row><Field>city</Field><Type>char(150)</Type><Null>YES</Null><Key></Key><Default>(NULL)</Default><Extra></Extra></row>
<row><Field>screen</Field><Type>varchar(12)</Type><Null>YES</Null><Key></Key><Default>(NULL)</Default><Extra></Extra></row>
<row><Field>depth</Field><Type>smallint(6)</Type><Null>YES</Null><Key></Key><Default>(NULL)</Default><Extra></Extra></row>
<row><Field>gzip</Field><Type>tinyint(1)</Type><Null>YES</Null><Key></Key><Default>(NULL)</Default><Extra></Extra></row>
<row><Field>deflate</Field><Type>tinyint(1)</Type><Null>YES</Null><Key></Key><Default>(NULL)</Default><Extra></Extra></row>
<row><Field>mobile</Field><Type>tinyint(1)</Type><Null>YES</Null><Key></Key><Default>(NULL)</Default><Extra></Extra></row>
<row><Field>tablet</Field><Type>tinyint(1)</Type><Null>YES</Null><Key></Key><Default>(NULL)</Default><Extra></Extra></row>
<row><Field>crawler</Field><Type>tinyint(1)</Type><Null>YES</Null><Key></Key><Default>(NULL)</Default><Extra></Extra></row>
<row><Field>os</Field><Type>varchar(50)</Type><Null>YES</Null><Key></Key><Default>(NULL)</Default><Extra></Extra></row>
<row><Field>osver</Field><Type>varchar(10)</Type><Null>YES</Null><Key></Key><Default>(NULL)</Default><Extra></Extra></row>
<row><Field>osname</Field><Type>varchar(250)</Type><Null>YES</Null><Key></Key><Default>(NULL)</Default><Extra></Extra></row>
<row><Field>osbit</Field><Type>tinyint(4)</Type><Null>YES</Null><Key></Key><Default>(NULL)</Default><Extra></Extra></row>
<row><Field>count</Field><Type>int(11) unsigned</Type><Null>YES</Null><Key></Key><Default>(NULL)</Default><Extra></Extra></row>
<row><Field>param</Field><Type>longtext</Type><Null>YES</Null><Key></Key><Default>(NULL)</Default><Extra></Extra></row>
<row><Field>created_at</Field><Type>timestamp</Type><Null>NO</Null><Key></Key><Default>0000-00-00 00:00:00</Default><Extra></Extra></row>
<row><Field>updated_at</Field><Type>timestamp</Type><Null>NO</Null><Key></Key><Default>0000-00-00 00:00:00</Default><Extra></Extra></row>
</data>';
    $xml = simplexml_load_string($xml);
    $this->setInstallTable('mystatdata',$xml);
    $xml = '<?xml version="1.0" encoding="utf-8"?>
<data><row><Field>date</Field><Type>date</Type><Null>YES</Null><Key></Key><Default>(NULL)</Default><Extra></Extra></row>
<row><Field>size</Field><Type>int(11) unsigned</Type><Null>YES</Null><Key></Key><Default>(NULL)</Default><Extra></Extra></row>
</data>';
    $xml = simplexml_load_string($xml);
    $this->setInstallTable('mystatsize',$xml);
    $xml = '<?xml version="1.0" encoding="utf-8"?>
<data><row><Field>x</Field><Type>smallint(6) unsigned</Type><Null>YES</Null><Key></Key><Default>(NULL)</Default><Extra></Extra></row>
<row><Field>y</Field><Type>smallint(6) unsigned</Type><Null>YES</Null><Key></Key><Default>(NULL)</Default><Extra></Extra></row>
<row><Field>width</Field><Type>smallint(6) unsigned</Type><Null>YES</Null><Key></Key><Default>(NULL)</Default><Extra></Extra></row>
<row><Field>touch</Field><Type>tinyint(1)</Type><Null>YES</Null><Key></Key><Default>(NULL)</Default><Extra></Extra></row>
<row><Field>uri</Field><Type>text</Type><Null>YES</Null><Key></Key><Default>(NULL)</Default><Extra></Extra></row>
<row><Field>xpath</Field><Type>text</Type><Null>YES</Null><Key></Key><Default>(NULL)</Default><Extra></Extra></row>
<row><Field>id_usr</Field><Type>int(11) unsigned</Type><Null>YES</Null><Default>(NULL)</Default><Extra></Extra></row>
<row><Field>created_at</Field><Type>timestamp</Type><Null>NO</Null><Key></Key><Default>0000-00-00 00:00:00</Default><Extra></Extra></row>
</data>';
    $xml = simplexml_load_string($xml);
    $this->setInstallTable('mystatclick',$xml);
  }

  protected function setInstallTable($tname,$xml){
    $dbo = JFactory::getDbo();
    $dbo->setQuery('SHOW TABLES;');
    $result = $dbo->loadAssocList();
    $exist = false;
    foreach($result as $r){
      $r = array_values($r);
      if($r[0] == $dbo->getPrefix().$tname){
        $exist = true;
        break;
      }
    }
    $sql = Array();
    if($exist){
      $dbo->setQuery('SHOW COLUMNS FROM #__'.$tname.';');
      $result = $dbo->loadAssocList();
      $chn = Array();
      $del = Array();
      $new = Array();
      foreach($result as $r){
        $found=false;
        $old='';
        foreach($xml->row as $rn){
          if($r['Field'] == (string)$rn->Field){
            $found=$rn;
            break;
          }
          $old = (string)$rn->Field;
        }
        if($found){
          if((string)$found->Default=='(NULL)'){(string)$found->Default=NULL;}
          if((string)$found->Type!=$r['Type'] or (string)$found->Null!=$r['Null'] or (string)$found->Key!=$r['Key'] or (string)$found->Default!=$r['Default'] or (string)$found->Extra!=$r['Extra']){
            $chn[] = Array(
              'Field' => (string)$found->Field,
              'Type' => (string)$found->Type,
              'Null' => (string)$found->Null,
              'Key' => (string)$found->Key,
              'Default' => (string)$found->Default,
              'Extra' => (string)$found->Extra,
              'After' => $old
            );
          }
        }else{
          $del[] = $r;
        }
      }
      $old='';
      foreach($xml->row as $rn){
        $found=false;
        foreach($result as $r){
          if($r['Field'] == $rn->Field){
            $found=true;
            break;
          }
        }
        if(!$found){
          $new[] = Array(
            'Field' => (string)$rn->Field,
            'Type' => (string)$rn->Type,
            'Null' => (string)$rn->Null,
            'Key' => (string)$rn->Key,
            'Default' => (string)$rn->Default,
            'Extra' => (string)$rn->Extra,
            'After' => $old
          );
        }
        $old = (string)$rn->Field;
      }
      if(sizeof($chn)>0 or sizeof($del)>0 or sizeof($new)>0){
        $el = 'ALTER TABLE #__'.$tname.' ';
        $row = Array();
        foreach($new as $r){
          $t = 'ADD COLUMN '.$r['Field'].' '.$r['Type'];
          if($r['Null']=='NO'){
            $t.= ' NOT NULL';
          }
          if($r['Default']!='(NULL)'){
            $t.= ' DEFAULT \''.addslashes($r['Default']).'\'';
          }
          if($r['Extra']!=''){
            $t.= ' '.strtoupper($r['Extra']);
          }
          if($r['After']!=''){
            $t.= ' AFTER '.$r['After'];
          }
          $row[] = $t;
        }
        foreach($chn as $r){
          $t = 'CHANGE '.$r['Field'].' '.$r['Field'].' '.$r['Type'];
          if($r['Null']=='NO'){
            $t.= ' NOT NULL';
          }
          if($r['Default']!=''){
            $t.= ' DEFAULT \''.addslashes($r['Default']).'\'';
          }
          if($r['Extra']!=''){
            $t.= ' '.strtoupper($r['Extra']);
          }
          $row[] = $t;
        }
        foreach($del as $r){
          $row[] = 'DROP COLUMN '.$r['Field'].'';
        }
        $el.= join(', ',$row);
        $sql[] = $el;
      }
    }else{
      $row = Array();
      $key = Array();
      foreach($xml->row as $r){
        $el = $r->Field.' '.$r->Type.' ';
        if($r->Null=='NO'){
          $el.= 'NOT NULL ';
        }
        if($r->Default!='(NULL)'){
          $el.= 'DEFAULT \''.addslashes($r->Default).'\' ';
        }
        if($r->Extra!=''){
          $el.= strtoupper($r->Extra).' ';
        }
        if($r->Key=='PRI'){
          $key[]= 'PRIMARY KEY ('.$r->Field.')';
        }
        $el = trim($el);
        $row[] = $el;
      }
      $row = array_merge($row,$key);
      $el = 'CREATE TABLE #__'.$tname.' (';
      $el.= join(',',$row);
      $el.= ') DEFAULT CHARSET=utf8;';
      $sql[] = $el;
    }
    foreach($sql as $s){
      $dbo->setQuery($s);
      $dbo->execute();
    }
  }

  protected function getUninstallTable(){
    $dbo = JFactory::getDbo();
    $dbo->dropTable('#__mystatdata');
    $dbo->dropTable('#__mystatsize');
    $dbo->dropTable('#__mystatclick');
    $path = JPATH_SITE.'/modules/mod_mystat/';
    if(is_dir($path)){
      unlink($path.'mod_mystat.php');
      unlink($path.'helper.php');
      unlink($path.'mod_mystat.xml');
      rmdir($path);
    }
    $sql = $dbo->getQuery(true)->delete($dbo->qn('#__modules'))->where($dbo->qn('module').' = '.$dbo->q('mod_mystat'));
    $dbo->setQuery($sql);
    $dbo->execute();
    $sql = $dbo->getQuery(true)->delete($dbo->qn('#__extensions'))->where($dbo->qn('element').' = '.$dbo->q('mod_mystat'));
    $dbo->setQuery($sql);
    $dbo->execute();
  }

  protected function installModule(){
    $dbo = JFactory::getDbo();
    $path = JPATH_SITE.'/modules/mod_mystat/';
    if(is_dir($path)){
      unlink($path.'mod_mystat.php');
      unlink($path.'helper.php');
      unlink($path.'mod_mystat.xml');
      rmdir($path);
    }
    mkdir($path);
    $f = fopen($path.'mod_mystat.php','w+');
    fwrite($f,'<?php'."\n".'require_once(dirname(__FILE__).\'/../../administrator/components/com_mystat/index.php\');'."\n");
    fclose($f);
    $f = fopen($path.'helper.php','w+');
    fwrite($f,'<?php'."\n".'class modMystatHelper{'."\n".' public static function getAjax(){'."\n".'  require_once(dirname(__FILE__).\'/../../administrator/components/com_mystat/index.php\');'."\n".' }'."\n".'}'."\n");
    fclose($f);
    $f = fopen($path.'mod_mystat.xml','w+');
    fwrite($f,'<?xml version="1.0" encoding="utf-8"?>'."\n");
    fwrite($f,'<extension version="3.0" type="module" method="install" client="site">'."\n");
    fwrite($f,'<name>mySTAT</name>'."\n");
    fwrite($f,'<author>Smyshlaev Evgeniy</author>'."\n");
    fwrite($f,'<creationDate>August 2015</creationDate>'."\n");
    fwrite($f,'<copyright>Copyright (C) 2008 - 2015 SINKAI LLC. All rights reserved.</copyright>'."\n");
    fwrite($f,'<license>GNU General Public License version 2 or later</license>'."\n");
    fwrite($f,'<authorEmail>info@my-stat.com</authorEmail>'."\n");
    fwrite($f,'<authorUrl>my-stat.com</authorUrl>'."\n");
    fwrite($f,'<version>'.MYSTAT_VERSION.'</version>'."\n");
    fwrite($f,'<description>MyStat is a flexible and versatile system intended for accumulation and analysis of the site attendance statistics. myStat suits to upcoming projects perfectly. There are more than 50 reports available in the system. The system is easy to install and to set up; it allows counting all the visitors of your web-site - both humans and robots. All visits data is stored at your server, which meets safety and confidentiality requirements.</description>'."\n");
    fwrite($f,'</extension>'."\n");
    fclose($f);
    $sql = $dbo->getQuery(true)->select('COUNT(*)')->from('#__modules')->where($dbo->qn('module').' = '.$dbo->q('mod_mystat'));
    $dbo->setQuery($sql);
    try{
      $count = $dbo->loadResult();
    }catch(Exception $e){
      $count = 0;
    }
    if(!$count){
      $sql = $dbo->getQuery(true)->insert($dbo->qn('#__modules'))
        ->set($dbo->qn('module').' = '.$dbo->q('mod_mystat'))
        ->set($dbo->qn('title').' = '.$dbo->q('mySTAT'))
        ->set($dbo->qn('access').' = '.$dbo->q('1'))
        ->set($dbo->qn('showtitle').' = '.$dbo->q('0'))
        ->set($dbo->qn('language').' = '.$dbo->q('*'))
        ->set($dbo->qn('position').' = '.$dbo->q('footer'));
      $sql->set($dbo->qn('published').' = '.$dbo->q('1'));
      $dbo->setQuery($sql);
      try{
        $dbo->execute();
      }catch(Exception $e){
      }
      $sql = $dbo->getQuery(true)->insert($dbo->qn('#__extensions'))
        ->set($dbo->qn('element').' = '.$dbo->q('mod_mystat'))
        ->set($dbo->qn('name').' = '.$dbo->q('mySTAT'))
        ->set($dbo->qn('enabled').' = '.$dbo->q('1'))
        ->set($dbo->qn('type').' = '.$dbo->q('module'));
      $dbo->setQuery($sql);
      try{
        $dbo->execute();
      }catch(Exception $e){
      }
      try{
        $query = $dbo->getQuery(true);
        $query->select('id')->from($dbo->qn('#__modules'))->where($dbo->qn('module').' = '.$dbo->q('mod_mystat'));
        $dbo->setQuery($query);
        $moduleid = $dbo->loadResult();
        $query = $dbo->getQuery(true);
        $query->select('*')->from($dbo->qn('#__modules_menu'))->where($dbo->qn('moduleid').' = '.$dbo->q($moduleid));
        $dbo->setQuery($query);
        $assignments = $dbo->loadObjectList();
        $isAssigned = !empty($assignments);
        if(!$isAssigned){
          $o = (object) array(
            'moduleid' => $moduleid,
            'menuid' => 0
          );
          $dbo->insertObject('#__modules_menu', $o);
        }
      }catch(Exception $e){
      }
      $sql = $dbo->getQuery(true)->insert($dbo->qn('#__assets'))
        ->set($dbo->qn('parent_id').' = '.$dbo->q('1'))
        ->set($dbo->qn('level').' = '.$dbo->q('1'))
        ->set($dbo->qn('name').' = '.$dbo->q('com_mystat'))
        ->set($dbo->qn('title').' = '.$dbo->q('mySTAT'))
        ->set($dbo->qn('rules').' = '.$dbo->q('{"core.admin":[],"core.manage":{"1":1},"core.create":[],"core.delete":[],"core.edit":[],"core.edit.state":[]}'));
      $dbo->setQuery($sql);
      $sql = $dbo->getQuery(true)->delete($dbo->qn('#__extensions'))->where($dbo->qn('element').' = '.$dbo->q(''));
      $dbo->setQuery($sql);
      $dbo->execute();
    }
  }

  protected function getStringKeyFromSource($str){
    $converter = array(
        ' ' => '_',   '\'' => '',
        'а' => 'a',   'б' => 'b',   'в' => 'v',
        'г' => 'g',   'д' => 'd',   'е' => 'e',
        'ё' => 'e',   'ж' => 'zh',  'з' => 'z',
        'и' => 'i',   'й' => 'y',   'к' => 'k',
        'л' => 'l',   'м' => 'm',   'н' => 'n',
        'о' => 'o',   'п' => 'p',   'р' => 'r',
        'с' => 's',   'т' => 't',   'у' => 'u',
        'ф' => 'f',   'х' => 'h',   'ц' => 'c',
        'ч' => 'ch',  'ш' => 'sh',  'щ' => 'sch',
        'ь' => '',    'ы' => 'y',   'ъ' => '',
        'э' => 'e',   'ю' => 'yu',  'я' => 'ya',
        'є' => 'e',   'і' => 'i',   'ї' => 'yi',

        'А' => 'A',   'Б' => 'B',   'В' => 'V',
        'Г' => 'G',   'Д' => 'D',   'Е' => 'E',
        'Ё' => 'E',   'Ж' => 'Zh',  'З' => 'Z',
        'И' => 'I',   'Й' => 'Y',   'К' => 'K',
        'Л' => 'L',   'М' => 'M',   'Н' => 'N',
        'О' => 'O',   'П' => 'P',   'Р' => 'R',
        'С' => 'S',   'Т' => 'T',   'У' => 'U',
        'Ф' => 'F',   'Х' => 'H',   'Ц' => 'C',
        'Ч' => 'Ch',  'Ш' => 'Sh',  'Щ' => 'Sch',
        'Ь' => '',    'Ы' => 'Y',   'Ъ' => '',
        'Э' => 'E',   'Ю' => 'Yu',  'Я' => 'Ya',
        'Є' => 'e',   'І' => 'i',   'Ї' => 'yi',
        ':' => '_',   '-' => '_',   '.' => '',
        '`' => '',    '@' => '_',   '#' => '_',
        '$' => '',    '%' => '',    '^' => '_',
        '&' => '',    '*' => '',    '(' => '',
        ')' => '',    '=' => '_',   '+' => '_',
        '"' => '',    '№' => '_',   '?' => '_',
        ';' => '_',   '!' => '_',   ',' => '',
        '—' => '_',    '–'=> '_',   ' ' => '_',
        '«' => '',     '»'=> '',    '\\'=> '',
        '/'=> '',      '{'=> '_',   '}' => '_'
    );
    $ret = strtr($str, $converter);
    $ret = preg_replace('/_{2,}/','_',$ret);
    return strtoupper($ret);
  }

}

class mystat_dbResultJoomla1 implements Iterator{

  private $link = null;

  public function __construct(&$link){
    $this->link = $link;
  }

  function rewind(){
    $this->link->rewind();
  }

  function current(){
    $el = mystat_joomla::convertResult($this->link->current());
    return $el;
  }

  function key(){
    $this->link->key();
  }

  function next(){
    $this->link->next();
  }

  function valid(){
    return $this->link->valid();
  }

}

class mystat_dbResultJoomla2 implements Iterator{

  private $link = null;
  private $row = null;
  private $count = 0;
  private $position = 0;

  public function __construct(&$link){
    global $wpdb;
    $this->link = $link;
  }

  function rewind(){
  }

  function current(){
    global $wpdb;
    $el = mystat_joomla::convertResult($this->row);
    return $el;
  }

  function key(){
    return $this->position;
  }

  function next(){
    $this->row = null;
    ++$this->position;
  }

  function valid(){
    global $wpdb;
    $r = mysqli_fetch_object($this->link);
    $this->row = $r;
    if($this->row!=null){
      return true;
    }
    mysqli_free_result($this->link);
    return false;
  }

}
