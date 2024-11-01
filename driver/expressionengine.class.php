<?php
if(!defined('MYSTAT_VERSION')){
  throw new Exception('File not exist 404');
}

class mystat_expressionengine{

  protected $run = false;
  protected $php = false;
  protected $context;
  protected $cookie = false;
  protected $param = false;
  protected $arr_lang = Array(
    'english' => Array('en','en-GB'),
    'spanish' => Array('es','es-ES'),
    'german' => Array('de','de-DE'),
    'polish' => Array('pl','pl-PL'),
    'ukranian' => Array('uk','uk-UA'),
    'russian' => Array('ru','ru-RU'),
  );

  public function __construct($context,$param=false){
    $this->context = $context;
    $this->param = $param;
  }

  public function getName(){
    return 'expressionengine';
  }

  public function isEngineRun(){
    if(!defined('BASEPATH') or !function_exists('ee')){
      return 'Driver can not run without ExpressionEngine';
    }
    return true;
  }

  public function getTime($no_gmt=false){
    return ee()->localize->now;
  }

  public function getGMT(){
    return (int)ee()->localize->format_date('%Z')/3600;
  }

  public function isAjax(){
    return $this->getParam('ajax','false')=='false'?false:true;
  }

  public function startDriver(){
    if($this->param=='install'){
      $this->installModule();
      return;
    }elseif($this->param=='update'){
      $this->updateModule();
      return;
    }elseif($this->param=='uninstall'){
      $this->uninstallModule();
      return;
    }elseif($this->param=='adminpanel'){
      $ajax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')?true:false;
      if($this->getParam('in')){$ajax=true;}
      if(!$ajax){
        $this->adminScripts();
        echo '<style>';
          echo '.alert.inline.success{margin: 0;padding: 0;border: 0;background-color: transparent;color: #000;}';
          echo '.box h1,.box .form-ctrls,.wrap .breadcrumb,.box.full.mb{display:none;}';
          echo '.col.w-16.last > .box{background-color: transparent;border:0;box-shadow: none;}';
        echo '</style>';
      }
      if(!$ajax){
        if($this->context->isNeedUpdate()){
          echo '<div class="breadcrumb" style="display:block;margin-left: 160px;">';
          echo '<button onclick="jQuery(\'#loading\').show();jQuery.ajax({url: document.location,data: {report: \'update\'},timeout: 300000, dataType: \'html\',type: \'POST\',success: function(data, textStatus){document.location.reload();},error: function(){jQuery(\'#loading\').hide();alert(\''.addslashes($this->__('An error occurred during the update, please, try again later.')).'\');}});return false;" class="btn btn-small btn-warning"><span class="icon-download"></span><strong>'.$this->__('My Statistics').'</strong>: '.$this->__('Need to update definitions').'</button>';
          echo '</div>';
        }
        if(file_exists($this->getCacheDir().'alert.dat')){
          $alert = @file_get_contents($this->getCacheDir().'alert.dat');
          if(trim($alert)!=''){
            $alert = strip_tags(base64_decode($alert),'<br/><b><i><a><div><p><img><span><strong><em><table><td><th><tr><h1><h2><h3><h4><button>');
            echo '<div class="breadcrumb" style="display:block;margin-left: 160px;">';
            echo '<p>'.$alert.'</p>';
            echo '</div>';
          }
        }
        if($error = $this->context->isInstallCorrect(true) and sizeof($error)>0){
          $ex = true;
          foreach($error as $e){
            switch($e){
              case 'WRITE':
                echo '<div class="alert inline issue">';
                echo '<strong>'.$this->__('My Statistics').':</strong> '.$this->__('Plugin has no permissions to write to the directory "cache". Plugin can not independently resolve this error. Contact your administrator.').'';
                echo '</div>';
                break;
              case 'ZLIB':
                echo '<div class="alert inline warn" style="margin-left: 160px;">';
                echo '<strong>'.$this->__('My Statistics').':</strong> <a href="http://php.net/manual/en/zlib.installation.php" target="_blank">'.$this->__('You need set up your PHP with ZLIB extension').'</a>';
                echo '</div>';
                $ex = false;
                break;
              case 'ZIP':
                echo '<div class="alert inline warn" style="margin-left: 160px;">';
                echo '<strong>'.$this->__('My Statistics').':</strong> <a href="http://php.net/manual/en/zip.installation.php" target="_blank">'.$this->__('You need set up your PHP with ZIP extension').'</a>';
                echo '</div>';
                $ex = false;
                break;
              case 'DOM':
                echo '<div class="alert inline issue">';
                echo '<strong>'.$this->__('My Statistics').':</strong> <a href="http://php.net/manual/en/dom.installation.php" target="_blank">'.$this->__('You need set up your PHP with DOM extension').'</a>';
                echo '</div>';
                break;
              case 'XSLT':
                echo '<div class="alert inline issue">';
                echo '<strong>'.$this->__('My Statistics').':</strong> <a href="http://php.net/manual/en/xsl.installation.php" target="_blank">'.$this->__('You need set up your PHP with XSL extension').'</a>';
                echo '</div>';
                break;
            }
          }
          if($ex){
            return false;
          }
        }
      }
      echo !$ajax?'<div id="mystat">':'';
      call_user_func(array_shift($this->run),array_shift($this->run));
      echo !$ajax?'</div>':'';
      if($ajax){exit;}
    }elseif($this->param=='code' or $this->param=='404' or $this->param=='ajax'){
      $this->dbSizeCollect();
      $page = ee()->uri->segment(3)?ee()->uri->segment(3):'dashboard';
      if(in_array($page,Array('export','insert','image'))){
        call_user_func(array_shift($this->run),array_shift($this->run));
        echo '{"success":true}';
        exit;
      }
      $this->initEE();
      call_user_func(array_shift($this->php),array_shift($this->php));
    }
  }

  public function installModule(){
    $this->getInstallTable();
    $settings = array(
      'mystatversion' => MYSTAT_VERSION,
      'mystat' => false,
      'mystatlastupdate' => false,
      'link' => ee('CP/URL', 'addons/settings/mystat')
    );
    $data = array(
      'class'     => 'Mystat_ext',
      'method'    => 'addMenuItem',
      'hook'      => 'cp_js_end',
      'settings'  => serialize($settings),
      'priority'  => 10,
      'version'   => MYSTAT_VERSION,
      'enabled'   => 'y'
    );
    ee()->db->insert('extensions', $data);
    $data = array(
      'class'     => 'Mystat_ext',
      'method'    => 'addCodeSniff',
      'hook'      => 'template_post_parse',
      'settings'  => '',
      'priority'  => 10,
      'version'   => MYSTAT_VERSION,
      'enabled'   => 'y'
    );
    ee()->db->insert('extensions', $data);
    $this->context->updateDefinition(false);
    $this->installLanguage();
  }

  protected function installLanguage(){
    foreach($this->arr_lang as $ll=>$lc){
      if(file_exists(dirname(__FILE__).'/../language/'.$ll)){
        $this->delTree(dirname(__FILE__).'/../language/'.$ll);
      }
    }
    $name = strtolower(ee()->config->item('deft_lang'));
    if(!isset($this->arr_lang[$name])){
      $name = 'english';
    }
    $lc = $this->arr_lang[$name];
    mkdir(dirname(__FILE__).'/../language/'.$name);
    $file = file(dirname(__FILE__).'/../language/'.$lc[1].'.com_mystat.ini');
    $f = fopen(dirname(__FILE__).'/../language/'.$name.'/mystat_lang.php','w+');
    fwrite($f,'<?php'."\n".'$lang = Array('."\n");
    foreach($file as $fl){
      if(trim($fl)!=''){
        preg_match('/^(.*) = \"(.*)\"$/i',trim($fl),$m);
        fwrite($f,'  \''.$m[1].'\' => "'.$m[2].'",'."\n");
      }
    }
    fwrite($f,');');
    fclose($f);
  }

  public function uninstallModule(){
    $this->getUninstallTable();
    ee()->db->where('class', 'Mystat_ext');
    ee()->db->delete('extensions');
    foreach($this->arr_lang as $ll=>$lc){
      if(file_exists(dirname(__FILE__).'/../language/'.$ll)){
        $this->delTree(dirname(__FILE__).'/../language/'.$ll);
      }
    }
  }

  public function updateModule(){
    $this->getInstallTable();
    ee()->db->where('class', 'Mystat_ext');
    ee()->db->update('extensions',array('version' => MYSTAT_VERSION));
    $this->context->updateDefinition(false);
    $this->installLanguage();
  }

  public function getCacheDir($web=false){
    return $web?ee()->uri->config->slash_item('site_url').substr(PATH_THIRD,strlen(substr(SYSPATH,0,-strlen(SYSDIR)-1))).'mystat/'.'cache/':dirname(__FILE__).'/../cache/';
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
    if($name=='report' and !$this->isAccess()){
      return ee()->uri->segment(3)?ee()->uri->segment(3):$default;
    }
    if(ee()->uri->segment(4)){
      parse_str(ee()->uri->segment(4),$output);
      if(isset($output[$name])){
        return $output[$name];
      }
    }
    $param = ee()->input->get_post($name);
    return empty($param)?$default:$param;
  }

  public function isAccess(){
    if(!isset(ee()->cp)){
      return false;
    }
    $rule = $this->getRole();
    $cur = $this->getCurrentRole();
    if($cur =='ADMIN' or $rule==$cur or $rule=='USER'){
      return true;
    }
    return false;
  }

  public function getCurrentRole(){
    if(ee('Permission')->has('can_access_security_settings')){
      return 'ADMIN';
    }
    if(ee('Permission')->has('can_create_entries')){
      return 'EDITOR';
    }
    return 'USER';
  }

  public function getRole(){
    $role = 'ADMIN';
    if(ee('Permission')->has('can_post_comments')){
      $role = 'USER';
    }elseif(ee('Permission')->has('can_create_entries')){
      $role = 'EDITOR';
    }else{
      $role = 'ADMIN';
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

  public function getUserHash(){
    if($this->cookie===false){
      $cookies = ee()->input->cookie('mystathash');
      if(!empty($cookies)){
        $this->cookie = $cookies;
      }else{
        $this->cookie = md5($_SERVER['HTTP_USER_AGENT'].$this->context->getIP().rand());
      }
    }
    return $this->cookie;
  }

  protected function initEE(){
    if(!$this->isAccess()){
      ee()->load->helper('cookie');
      $cookie = get_cookie('mystathash');
      if(!empty($cookie)){
        $cookie = $cookie;
      }else{
        $cookie = $this->getUserHash();
      }
      set_cookie('mystathash', $cookie, $this->getTime(false)+(60*60*24*365));
    }
  }

  public function isFeed(){
    return false;
  }

  public function getOption($name,$default=false){
    $extension_model = ee('Model')->get('Extension')
      ->filter('class', 'Mystat_ext')
      ->filter('hook', 'cp_js_end')
      ->first();
    $settings = $extension_model->settings;
    if(!is_array($settings)){
      return $default;
    }
    return isset($settings[$name])?$settings[$name]:$default;
  }

  public function setOption($name,$value=false){
    $extension_model = ee('Model')->get('Extension')
      ->filter('class', 'Mystat_ext')
      ->filter('hook', 'cp_js_end')
      ->first();
    $settings = $extension_model->settings;
    if(!is_array($settings)){
      $settings = Array();
    }
    if($value===false and isset($settings[$name])){
      unset($settings[$name]);
    }else{
      $settings[$name] = $value;
    }
    ee()->db->update('extensions', Array('settings'=>serialize($settings)), Array('class'=>'Mystat_ext','hook'=>'cp_js_end'));
    return $this;
  }

  public function __($text){
    $txt = ee()->lang->line(self::getStringKeyFromSource($text));
    if($txt==self::getStringKeyFromSource($text)){
      return $text;
    }
    return $txt;
  }

  public function getWebPath(){
    return ee()->uri->config->slash_item('site_url').substr(PATH_THIRD,strlen(substr(SYSPATH,0,-strlen(SYSDIR)-1))).'mystat/'.'asset/';
  }

  public function getExportUrl(){
    return ee('CP/URL')->make('addons/settings/mystat');
  }

  private function getRedirectUri($report=false){
    $url = ee()->functions->create_url('mystat/ajax'.($report?'/'.$report:''));
    if(!preg_match('/index\.php/i',$url)){
      $url = preg_replace('/mystat\/ajax/i','index.php/mystat/ajax',$url);
    }
    return $url;
  }

  public function getLanguage(){
    if(isset($this->arr_lang[strtolower(ee()->config->item('deft_lang'))])){
      return strtoupper($this->arr_lang[ee()->config->item('deft_lang')][0]);
    }
    return 'EN';
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
    $row = ee()->db->get_where('mystatdata',Array('id'=>(int)$id));
    $el = Array();
    if(!empty($row)){
      $el = $this->convertResult($row->first_row());
    }
    return $el;
  }

  public function is404(){
    return $this->param=='404'?true:false;
  }

  public function setCodeHook($el,$func){
    $this->php = Array($func,$el);
  }

  public function setJsSendClick($id){
    $url = $this->getRedirectUri();
    $token = ee()->csrf->get_user_token();
    $ret =  <<<JS
    <script type="text/javascript" charset="utf-8">//<![CDATA[
      var Base64={_keyStr:"ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=",encode:function(e){var t="";var n,r,i,s,o,u,a;var f=0;e=Base64._utf8_encode(e);while(f<e.length){n=e.charCodeAt(f++);r=e.charCodeAt(f++);i=e.charCodeAt(f++);s=n>>2;o=(n&3)<<4|r>>4;u=(r&15)<<2|i>>6;a=i&63;if(isNaN(r)){u=a=64}else if(isNaN(i)){a=64}t=t+this._keyStr.charAt(s)+this._keyStr.charAt(o)+this._keyStr.charAt(u)+this._keyStr.charAt(a)}return t},decode:function(e){var t="";var n,r,i;var s,o,u,a;var f=0;e=e.replace(/[^A-Za-z0-9\\+\\/\\=]/g,"");while(f<e.length){s=this._keyStr.indexOf(e.charAt(f++));o=this._keyStr.indexOf(e.charAt(f++));u=this._keyStr.indexOf(e.charAt(f++));a=this._keyStr.indexOf(e.charAt(f++));n=s<<2|o>>4;r=(o&15)<<4|u>>2;i=(u&3)<<6|a;t=t+String.fromCharCode(n);if(u!=64){t=t+String.fromCharCode(r)}if(a!=64){t=t+String.fromCharCode(i)}}t=Base64._utf8_decode(t);return t},_utf8_encode:function(e){e=e.replace(/\\r\\n/g,"\\n");var t="";for(var n=0;n<e.length;n++){var r=e.charCodeAt(n);if(r<128){t+=String.fromCharCode(r)}else if(r>127&&r<2048){t+=String.fromCharCode(r>>6|192);t+=String.fromCharCode(r&63|128)}else{t+=String.fromCharCode(r>>12|224);t+=String.fromCharCode(r>>6&63|128);t+=String.fromCharCode(r&63|128)}}return t},_utf8_decode:function(e){var t="";var n=0;var r=c1=c2=0;while(n<e.length){r=e.charCodeAt(n);if(r<128){t+=String.fromCharCode(r);n++}else if(r>191&&r<224){c2=e.charCodeAt(n+1);t+=String.fromCharCode((r&31)<<6|c2&63);n+=2}else{c2=e.charCodeAt(n+1);c3=e.charCodeAt(n+2);t+=String.fromCharCode((r&15)<<12|(c2&63)<<6|c3&63);n+=3}}return t}}
      var ajax = {};ajax.x = function() {if (typeof XMLHttpRequest !== 'undefined') {return new XMLHttpRequest();  }var versions = ["MSXML2.XmlHttp.5.0",   "MSXML2.XmlHttp.4.0",  "MSXML2.XmlHttp.3.0",   "MSXML2.XmlHttp.2.0",  "Microsoft.XmlHttp"];var xhr;for(var i = 0; i < versions.length; i++) {  try {  xhr = new ActiveXObject(versions[i]);  break;  } catch (e) {}}return xhr;};ajax.send = function(url, callback, method, data, sync) {var x = ajax.x();x.open(method, url, sync);x.onreadystatechange = function() {if (x.readyState == 4) {callback(x.responseText)}};if (method == 'POST') {x.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');}x.send(data)};ajax.get = function(url, data, callback, sync) {var query = [];for (var key in data) {query.push(encodeURIComponent(key) + '=' + encodeURIComponent(data[key]));}ajax.send(url + '?' + query.join('&'), callback, 'GET', null, sync)};ajax.post = function(url, data, callback, sync) {var query = [];for (var key in data) {query.push(encodeURIComponent(key) + '=' + encodeURIComponent(data[key]));}ajax.send(url, callback, 'POST', query.join('&'), sync)};
      function runStatisticMyStatClickSend(data){
        ajax.post('{$url}/insertclick',{csrf_token:'{$token}',data: Base64.encode(JSON.stringify(data)),coding: 'base64'},function(){},true);
      }  
    //]]></script>
JS;
    return $ret;
  }

  public function setJsSend($id){
    $url = $this->getRedirectUri();
    $token = ee()->csrf->get_user_token();
    $ret =  <<<JS
    <noscript>
      <img src="{$url}/image/id={$id}" width="1px" height="1px" style="position:absolute;width:1px;height:1px;bottom:0px;right:0px;" />
    </noscript>
    <script type="text/javascript" charset="utf-8">
      var addListener = document.addEventListener || document.attachEvent,
        removeListener =  document.removeEventListener || document.detachEvent
        eventName = document.addEventListener ? "DOMContentLoaded" : "onreadystatechange"

      addListener.call(document, eventName, function(){
        var img = new Image();
        img.src = '{$url}/image/id={$id}';
        img.width = '1px';
        img.height = '1px';
        img.style.position = 'absolute';
        img.style.width = '1px';
        img.style.height = '1px';
        img.style.bottom = '0';
        img.style.right = '0';
        document.body.appendChild(img);
        var stat = runStatisticMyStat();
        ajax.post('{$url}/insert',{csrf_token:'{$token}',data: Base64.encode(JSON.stringify(stat)),coding: 'base64'},function(){},true);
        removeListener( eventName, arguments.callee, false )
      }, false )
    </script>
JS;
    return $ret;
  }

  public function getStatCacheByUserAgent($id,$ua){
    $param = Array();
    $row = ee()->db->select('*')
      ->from('mystatdata')
      ->where('ua',$ua)
      ->where('browser IS NOT NULL')
      ->where('browser != ""')
      ->where('browser != "Default Browser"')
      ->where('id !='.(int)$id)
      ->order_by('created_at','DESC');
    $row = $row->get()->first_row();
    if(!empty($row)){
      $param['browser'] = $row->browser;
      $param['version'] = $row->browser_version;
      $param['os'] = $row->os;
      $param['osver'] = $row->osver;
      $param['osname'] = $row->osname;
      $param['osbit'] = $row->osbit;
      $param['crawler'] = (bool)$row->crawler;
      if($ua==''){$param['crawler'] = true;}
      $param['mobile'] = (bool)$row->mobile;
      $param['tablet'] = (bool)$row->tablet;
      $param['device'] = $row->device;
      $param['device_name'] = $row->device_name;
    }
    return $param;
  }

  public function setStatInsertFirst($param){
    $id = ee()->db->select('id')->get_where('mystatdata',Array(
      'created_at >=' => 'TIMESTAMP('.date('Y-m-d',$this->getTime(false)).')',
      'ip' => $param['ip'],
      'ua' => $param['ua'],
      'hash' => $param['hash'],
      'referer' => $param['referer']['url'],
      'host' => $param['host'],
      'uri' => $param['uri']
    ))->first_row('array');
    $id = sizeof($id)>0?$id['id']:0;
    $timer = microtime(true);
    if($id==0){
      ee()->db->insert('mystatdata',Array(
        'time_start' => ($timer-floor($timer))*10000,
        'hash' => $param['hash'],
        'ua' => $param['ua'],
        'time_load' => 0,
        'ip' => $param['ip'],
        'host' => $param['host'],
        'www' => (int)$param['www'],
        'uri' => $param['uri'],
        'referer' => $param['referer']['url'],
        'lang' => $param['lang'],
        'gzip' => (int)$param['gzip'],
        'deflate' => (int)$param['deflate'],
        'proxy' => (int)$param['proxy'],
        'is404' => (int)$param['404'],
        'is_feed' => (int)$param['feed'],
        'file' => $param['file'],
        'title' => '',
        'screen' => '',
        'depth' => 0,
        'count' => 1,
        'created_at' => date('Y-m-d H:i:s',$this->getTime(false)),
        'updated_at' => date('Y-m-d H:i:s',$this->getTime(false))
      ));
      if(ee()->db->affected_rows()>0){
        $id=ee()->db->insert_id();
      }
      return $id;
    }
    ee()->db->query("UPDATE ".ee()->db->dbprefix."mystatdata SET time_start=".(($timer-floor($timer))*10000).",count=count+1,updated_at='".date('Y-m-d H:i:s',$this->getTime(false))."' WHERE id=".$id);
    return -$id;
  }

  public function setStatInsertNext($id,$param){
    if($id==0){return false;}
    ee()->db->update('mystatdata',Array(
      'browser' => $param['browser'],
      'browser_version' => $param['version'],
      'device' => $param['device'],
      'device_name' => $param['device_name'],
      'referer' => $param['referer']['url'],
      'reftype' => $param['referer']['type'],
      'refname' => $param['referer']['name'],
      'refquery' => $param['referer']['query'],
      'country' => $param['country'],
      'city' => $param['city'],
      'mobile' => (int)$param['mobile'],
      'tablet' => (int)$param['tablet'],
      'crawler' => (int)$param['crawler'],
      'os' => $param['os'],
      'osver' => $param['osver'],
      'osname' => $param['osname'],
      'osbit' => (int)$param['osbit'],
      'updated_at' => date('Y-m-d H:i:s',$this->getTime(false))
    ),Array('id'=>$id));
    return true;
  }

  public function setStatImage($id,$ip){
    if($id>0){
      $el = ee()->db->select('id')->get_where('mystatdata',Array(
        'ip' => ip2long($ip),
        'id' => (int)$id
      ))->first_row('array');
      if(sizeof($el)>0){
        ee()->db->update('mystatdata',Array('image'=>1),Array('id'=>$id));
      }
    }
    Header('Content-Type: image/gif');
    echo base64_decode('R0lGODlhAQABAJEAAAAAAP///////wAAACH5BAEAAAIALAAAAAABAAEAAAICVAEAOw==');
    exit;
  }

  public function setStatUpdate($id,$param,$ip,$tor){
    if($id>0){
      $timer = microtime(true);
      $rows = ee()->db->select('updated_at,time_start')->get_where('mystatdata',Array(
        'ip' => ip2long($ip),
        'id' => (int)$id
      ))->first_row('array');
      if(sizeof($rows)==0){return;}
      $tload = ($this->getTime(false)+($rows['time_start']/10000))-(strtotime($rows['updated_at'])+($timer-floor($timer)));
      $title = (string)$param['title'];unset($param['title']);
      $screen = '';
      if(isset($param['screen']['width']) and (int)$param['screen']['width']>0){
        $screen = $param['screen']['width'].'x'.$param['screen']['height'];
        $depth = $param['screen']['depth'];
        unset($param['screen']);
      }
      ee()->db->update('mystatdata',Array(
        'time_load' => $tload,
        'is_tor' => (int)$tor,
        'title' => $title,
        'screen' => $screen,
        'depth' => $depth,
        'param' => json_encode($param),
        'updated_at' => date('Y-m-d H:i:s',$this->getTime(false))
      ),Array('id'=>(int)$id));
    }
    $this->postDetected();
  }

  private function postDetected(){
    $start = time();
    while((time()-$start)<10){
      $query = ee()->db->select('*')
        ->from('mystatdata')
        ->where('browser','')
        ->or_where('browser IS NULL')
        ->limit(1);
      $rows = $query->get()->first_row();
      if(sizeof($rows)>0){
        $this->context->setStatisticsById((int)$rows->id,$this->convertResult($rows));
      }else{
        break;
      }
    }
  }

  public function getStatByPeriod($from,$to){
    $query = ee()->db->select('*')
      ->from('mystatdata')
      ->where('created_at >=', date('Y-m-d 00:00:00',$from))
      ->where('created_at <=', date('Y-m-d 23:59:59',$to));
    return new dbResultExpressionengine($query);
  }

  protected function dbSizeCollect(){
    if($this->getOption('mystatcleanstart')==date('dmY',$this->getTime(false))){
      return;
    }
    $days = (int)$this->getOption('mystatcleanday',120);
    $days = $days>30?$days:30;
    ee()->db->delete('mystatdata',Array('created_at <='=>'TIMESTAMP('.ee()->db->escape(date('Y-m-d 00:00:00',strtotime(date('Y-m-d',$this->getTime(false)).' -'.$days.' days'))).')'));
    ee()->db->delete('mystatclick',Array('created_at <='=>'TIMESTAMP('.ee()->db->escape(date('Y-m-d 00:00:00',strtotime(date('Y-m-d',$this->getTime(false)).' -'.$days.' days'))).')'));
    ee()->db->delete('mystatsize',Array('date <='=>'TIMESTAMP('.ee()->db->escape(date('Y-m-d 00:00:00',strtotime(date('Y-m-d',$this->getTime(false)).' -'.$days.' days'))).')'));

    ee()->db->query('OPTIMIZE TABLE '.ee()->db->dbprefix.'mystatdata');
    ee()->db->query('OPTIMIZE TABLE '.ee()->db->dbprefix.'mystatclick');
    ee()->db->query('OPTIMIZE TABLE '.ee()->db->dbprefix.'mystatsize');

    $query = ee()->db->query('SHOW TABLE STATUS LIKE \''.ee()->db->dbprefix.'mystat%\'');
    $size = 0;
    foreach($query->result_array() as $el){
      $size+= $el['Data_length'] + $el['Index_length'];
    }
    $exist = ee()->db->from('mystatsize')->where('date',date('Y-m-d',$this->getTime(false)))->count_all_results();
    if((int)$exist==0){
      ee()->db->insert('mystatsize',Array('date'=>date('Y-m-d',$this->getTime(false)),'size'=>$size));
    }else{
      ee()->db->update('mystatsize', Array('size'=>$size), array('date' => date('Y-m-d',$this->getTime(false))));
    }
    $this->setOption('mystatcleanstart',date('dmY',$this->getTime(false)));
  }

  public function getDbSizeByPeriod($from,$to){
    $query = ee()->db->select('*')
      ->from('mystatsize')
      ->where('date >=', date('Y-m-d 00:00:00',$from))
      ->where('date <=', date('Y-m-d 23:59:59',$to));
    return $query->get()->result_array();
  }

################################################################################

  protected function adminScripts(){
    $webpath = $this->getWebPath();
//    if(method_exists(ee()->cp,'add_to_foot')){
//    ee()->cp->load_package_js('asset/jquery.min');
//      ee()->cp->add_to_foot('<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>');
//      ee()->cp->add_to_foot('<script type="text/javascript" src="'.trim($webpath,'/').'/logo.min.js"></script>');
//      ee()->cp->add_to_foot('<script type="text/javascript" src="'.trim($webpath,'/').'/moment.min.js"></script>');
//      ee()->cp->add_to_foot('<script type="text/javascript" src="'.trim($webpath,'/').'/jquery.min.js"></script>');
//      ee()->cp->add_to_foot('<script type="text/javascript" src="'.trim($webpath,'/').'/jquery.mask.min.js"></script>');
//      ee()->cp->add_to_foot('<script type="text/javascript" src="'.trim($webpath,'/').'/jquery.daterangepicker.min.js"></script>');
//      ee()->cp->add_to_foot('<link rel="stylesheet" href="'.trim($webpath,'/').'/jquery.daterangepicker.min.css" type="text/css" />');
//    }else{
      if($this->getOption('mystatproxygoogle','false')=='true'){
        ee()->cp->add_to_head('<script type="text/javascript" src="https://my-stat.com/google/loader.js"></script>');
      }else{
        ee()->cp->add_to_head('<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>');
      }
      ee()->cp->add_to_head('<script type="text/javascript" src="'.trim($webpath,'/').'/logo.min.js"></script>');
      ee()->cp->add_to_head('<script type="text/javascript" src="'.trim($webpath,'/').'/moment.min.js"></script>');
      ee()->cp->add_to_head('<script type="text/javascript" src="'.trim($webpath,'/').'/jquery.min.js"></script>');
      ee()->cp->add_to_head('<script type="text/javascript" src="'.trim($webpath,'/').'/jquery.mask.min.js"></script>');
      ee()->cp->add_to_head('<script type="text/javascript" src="'.trim($webpath,'/').'/jquery.daterangepicker.min.js"></script>');
      ee()->cp->add_to_head('<link rel="stylesheet" href="'.trim($webpath,'/').'/jquery.daterangepicker.min.css" type="text/css" />');
//    }

    ee()->cp->load_package_js('../asset/jquery.mask.min');
    ee()->cp->load_package_js('../asset/jquery.daterangepicker.min');
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
    $sql = Array();
    if(ee()->db->table_exists($tname)){
      $query = ee()->db->query('SHOW COLUMNS FROM '.ee()->db->dbprefix($tname).'');
      $result = $query->result_array();
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
        $el = 'ALTER TABLE '.ee()->db->dbprefix($tname).' ';
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
      $el = 'CREATE TABLE '.ee()->db->dbprefix($tname).' (';
      $el.= join(',',$row);
      $el.= ') DEFAULT CHARSET=utf8;';
      $sql[] = $el;
    }
    foreach($sql as $s){
      ee()->db->simple_query($s);
    }
  }

  protected function getUninstallTable(){
    if(ee()->db->table_exists('mystatdata')){
      ee()->db->simple_query('DROP TABLE '.ee()->db->dbprefix('mystatdata'));
    }
    if(ee()->db->table_exists('mystatsize')){
      ee()->db->simple_query('DROP TABLE '.ee()->db->dbprefix('mystatsize'));
    }
    if(ee()->db->table_exists('mystatclick')){
      ee()->db->simple_query('DROP TABLE '.ee()->db->dbprefix('mystatclick'));
    }
//    ee()->db->where('class', 'Mystat_ext');
//    ee()->db->delete('extensions');
//    if(file_exists(APPPATH.'language/english/mystat_lang.php')){
//      unlink(APPPATH.'language/english/mystat_lang.php');
//    }
  }

  public static function delTree($dir){
    $files = array_diff(scandir($dir), array('.','..'));
    foreach ($files as $file){
      (is_dir($dir.'/'.$file))?self::delTree($dir.'/'.$file):unlink($dir.'/'.$file);
    }
    return rmdir($dir);
  }

  protected static function getStringKeyFromSource($str){
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

class dbResultExpressionengine implements Iterator{

  private $link = null;
  private $row = null;
  private $count = 0;
  private $position = 0;

  public function __construct(&$link){
    $this->link = $link->get();
  }

  function rewind(){
  }

  function current(){
    $el = mystat_expressionengine::convertResult($this->row);;
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
    $this->row = $this->link->row($this->position);
    if($this->position>=$this->link->num_rows()){return false;}
    return true;
  }

}
