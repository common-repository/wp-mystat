<?php
if(!defined('MYSTAT_VERSION')){
  throw new Exception('File not exist 404');
}

class mystat_wordpress{

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
    return 'wordpress';
  }

  public function getDBPrefix(){
    return 'wp_';
  }

  public static function getTime($no_gmt=false){
    return (int)current_time('timestamp',$no_gmt);
  }

  public static function getGMT(){
    return (int)get_option('gmt_offset');
  }

  public function isAjax(){
    return $this->getParam('ajax','false')=='false'?false:true;
  }

  public function isEngineRun(){
    if(!function_exists('register_activation_hook') or !class_exists('WP_Widget')){
      return 'Driver can not run without WordPress CMS';
    }
    return true;
  }

  public function startDriver(){
    global $wpdb;
    $wpdb->hide_errors();
    define('DIEONDBERROR', false);
    $wpdb->flush();
    if(function_exists('w3tc_get_user_agent_group')){
      define('W3TC_DYNAMIC_SECURITY', '1jksaj@#J@okmKWMOFJB#iewjnm');
    }
    register_activation_hook(realpath(dirname(__FILE__).'/../index.php'),Array($this,'installPlugin'));
    register_deactivation_hook(realpath(dirname(__FILE__).'/../index.php'),Array($this,'unstallPlugin'));
    register_uninstall_hook(realpath(dirname(__FILE__).'/../index.php'),Array(__CLASS__,'removePlugin'));
    add_action('wpmu_new_blog',Array($this,'addNewBlog'), 10, 6);
    add_action('plugins_loaded',Array($this,'updatePlugin'));
    add_action('wp_ajax_mystat',Array($this,'ajaxRun'));
    if(!($error = $this->context->isInstallCorrect(false) and sizeof($error)>0)){
      add_action('admin_menu',Array($this,'addMenu'));
      add_action('admin_bar_menu',Array($this,'addMenuInBar'),20);
      add_filter('manage_sites_action_links', Array($this,'addMenuInSiteListFull'),10,2);
      add_filter('myblogs_blog_actions', Array($this,'addMenuInSiteList'),10,2);
    }
    if(sizeof($this->context->isInstallCorrect(false))==0 and $this->context->isAllFileExists()){
//      add_action('wp_head',Array($this,'addHeaderCode'));
      add_action($this->getOption('mystatwpplace','wp_footer'),Array($this,'addHookCode'));
      add_action('wp_ajax_nopriv_mystat', Array($this,'ajaxRunPublic'));
      add_action('init', Array($this,'initWP'));
      add_action('admin_init',Array($this,'directRun'));
    }
    add_action('admin_notices',Array($this,'adminNotice'));
    add_action('admin_enqueue_scripts', Array($this,'adminScripts'));
    if($this->context->isShow()){
      add_action('widgets_init',Array($this,'widgetRegister1'));
    }
    add_action('restrict_manage_users', Array($this,'addWPStatUser'));
    add_action('restrict_manage_posts', Array($this,'addWPStatPost'));
    add_action('manage_comments_nav', Array($this,'addWPStatComm'));
    $dir = plugins_url('',dirname(__FILE__));
    $dir = preg_split('/\//',trim($dir));
    $dir = array_pop($dir);
    add_action('in_plugin_update_message-'.$dir.'/mystat.php',Array($this,'updateInPluginList'));
  }

  public function setUpdateStop($report=false){
//    $ret = '<script>document.location=\''.$this->getRedirectUri($report).'\';</script>';
//    return $ret;
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
    return isset($_POST[$name])?$_POST[$name]:((in_array($name,Array('report','id','type','in')) and isset($_GET[$name]))?$_GET[$name]:$default);
  }

  public static function getOption($name,$default=false){
    return get_option($name,$default);
  }

  public function getUserHash(){
    if($this->cookie===false){
      if(isset($_COOKIE['mystathash']) and $_COOKIE['mystathash']!=''){
        $this->cookie = $_COOKIE['mystathash'];
      }else{
        $this->cookie = md5($_SERVER['HTTP_USER_AGENT'].$this->context->getIP().rand());
      }
    }
    return $this->cookie;
  }

  public function isFeed(){
    return is_feed();
  }

  public function setOption($name,$value=false){
    if($value===false){
      delete_option($name);
      return $this;
    }
    update_option($name,$value);
    return $this;
  }

  public static function __($text){
    return __($text,'mystat');
  }

  public static function getWebPath(){
    return plugins_url('asset/', dirname(__FILE__));
  }

  public function getExportUrl(){
    return $this->getRedirectUri();
  }

  public function getLanguage(){
    return strtoupper(substr(get_locale(),0,2));
  }
  
  public function is404(){
    return is_404();
  }

  public function setCodeHook($el,$func){
    $this->php = Array($func,$el);
  }

  public function setJsSendClick($id){
    $url = admin_url('admin-ajax.php');
    $ret =  <<<JS
    <script type="text/javascript" charset="utf-8">//<![CDATA[
      var Base64={_keyStr:"ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=",encode:function(e){var t="";var n,r,i,s,o,u,a;var f=0;e=Base64._utf8_encode(e);while(f<e.length){n=e.charCodeAt(f++);r=e.charCodeAt(f++);i=e.charCodeAt(f++);s=n>>2;o=(n&3)<<4|r>>4;u=(r&15)<<2|i>>6;a=i&63;if(isNaN(r)){u=a=64}else if(isNaN(i)){a=64}t=t+this._keyStr.charAt(s)+this._keyStr.charAt(o)+this._keyStr.charAt(u)+this._keyStr.charAt(a)}return t},decode:function(e){var t="";var n,r,i;var s,o,u,a;var f=0;e=e.replace(/[^A-Za-z0-9\\+\\/\\=]/g,"");while(f<e.length){s=this._keyStr.indexOf(e.charAt(f++));o=this._keyStr.indexOf(e.charAt(f++));u=this._keyStr.indexOf(e.charAt(f++));a=this._keyStr.indexOf(e.charAt(f++));n=s<<2|o>>4;r=(o&15)<<4|u>>2;i=(u&3)<<6|a;t=t+String.fromCharCode(n);if(u!=64){t=t+String.fromCharCode(r)}if(a!=64){t=t+String.fromCharCode(i)}}t=Base64._utf8_decode(t);return t},_utf8_encode:function(e){e=e.replace(/\\r\\n/g,"\\n");var t="";for(var n=0;n<e.length;n++){var r=e.charCodeAt(n);if(r<128){t+=String.fromCharCode(r)}else if(r>127&&r<2048){t+=String.fromCharCode(r>>6|192);t+=String.fromCharCode(r&63|128)}else{t+=String.fromCharCode(r>>12|224);t+=String.fromCharCode(r>>6&63|128);t+=String.fromCharCode(r&63|128)}}return t},_utf8_decode:function(e){var t="";var n=0;var r=c1=c2=0;while(n<e.length){r=e.charCodeAt(n);if(r<128){t+=String.fromCharCode(r);n++}else if(r>191&&r<224){c2=e.charCodeAt(n+1);t+=String.fromCharCode((r&31)<<6|c2&63);n+=2}else{c2=e.charCodeAt(n+1);c3=e.charCodeAt(n+2);t+=String.fromCharCode((r&15)<<12|(c2&63)<<6|c3&63);n+=3}}return t}}
      var ajax = {};ajax.x = function() {if (typeof XMLHttpRequest !== 'undefined') {return new XMLHttpRequest();  }var versions = ["MSXML2.XmlHttp.5.0",   "MSXML2.XmlHttp.4.0",  "MSXML2.XmlHttp.3.0",   "MSXML2.XmlHttp.2.0",  "Microsoft.XmlHttp"];var xhr;for(var i = 0; i < versions.length; i++) {  try {  xhr = new ActiveXObject(versions[i]);  break;  } catch (e) {}}return xhr;};ajax.send = function(url, callback, method, data, sync) {var x = ajax.x();x.open(method, url, sync);x.onreadystatechange = function() {if (x.readyState == 4) {callback(x.responseText)}};if (method == 'POST') {x.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');}x.send(data)};ajax.get = function(url, data, callback, sync) {var query = [];for (var key in data) {query.push(encodeURIComponent(key) + '=' + encodeURIComponent(data[key]));}ajax.send(url + '?' + query.join('&'), callback, 'GET', null, sync)};ajax.post = function(url, data, callback, sync) {var query = [];for (var key in data) {query.push(encodeURIComponent(key) + '=' + encodeURIComponent(data[key]));}ajax.send(url, callback, 'POST', query.join('&'), sync)};
      function lookupElementByXPath(path) { 
        var evaluator = new XPathEvaluator(); 
        var result = evaluator.evaluate(path, document.documentElement, null,XPathResult.FIRST_ORDERED_NODE_TYPE, null); 
        return  result.singleNodeValue; 
      } 
      function runStatisticMyStatClickSend(data){
        ajax.post('{$url}',{action: 'mystat',report: 'insertclick',data: Base64.encode(JSON.stringify(data)),coding: 'base64'},function(){},true);
      }  
    //]]></script>
JS;
    return $ret;
  }

  public function setJsSend($id){
    $url = admin_url('admin-ajax.php');
    $ret =  <<<JS
    <noscript>
      <img src="{$url}?action=mystat&report=image&id={$id}" width="1px" height="1px" style="position:absolute;width:1px;height:1px;bottom:0px;right:0px;" />
    </noscript>
    <script type="text/javascript" charset="utf-8">//<![CDATA[
      var addListener = document.addEventListener || document.attachEvent,
        removeListener =  document.removeEventListener || document.detachEvent
        eventName = document.addEventListener ? "DOMContentLoaded" : "onreadystatechange"

      addListener.call(document, eventName, function(){
        var img = new Image();
        img.src = '{$url}?action=mystat&report=image&id={$id}';
        img.width = '1px';
        img.height = '1px';
        img.style.position = 'absolute';
        img.style.width = '1px';
        img.style.height = '1px';
        img.style.bottom = '0';
        img.style.right = '0';
        document.body.appendChild(img);
        var stat = runStatisticMyStat();
        ajax.post('{$url}',{action: 'mystat',report: 'insert',data: Base64.encode(JSON.stringify(stat)),coding: 'base64'},function(){},true);
        removeListener( eventName, arguments.callee, false )
      }, false )
    //]]></script>
JS;
    return $ret;
  }

  public function getStatCacheByUserAgent($id,$ua){
    global $wpdb;
    $param = new \stdClass();
    $row=$wpdb->get_row(
      $wpdb->prepare('
        SELECT * FROM '.$wpdb->prefix.'mystatdata
        WHERE
          ua = %s AND
          browser IS NOT NULL AND
          browser != "" AND
          browser != %s AND
          id != %d
        ORDER BY created_at DESC LIMIT 0,1
        ',
        $ua,
        'Default Browser',
        $id
      )
    );
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
    global $wpdb;
    $row=$wpdb->get_row(
      $wpdb->prepare('
        SELECT * FROM '.$wpdb->prefix.'mystatdata
        WHERE
          id = %d
        ',
        $id
      )
    );
    $el = Array();
    if(!empty($row)){
      $el = $this->convertResult($row);
    }
    return $el;
  }

  public function setStatInsertClick($data){
    if($this->getOption('mystatclickevent','true')=='true'){
      global $wpdb;
      $r = $wpdb->replace(
        $wpdb->prefix.'mystatclick',
        Array(
          'x' => $data->x,
          'y' => $data->y,
          'width' => $data->width,
          'uri' => $data->uri,
          'touch' => $data->touch?1:0,
          'xpath' => $data->target,
          'id_usr' => $data->id,
          'created_at' => date('Y-m-d H:i:s',$this->getTime(false))
        ),
        Array('%d','%d','%d','%s','%d','%s','%d','%s')
      );
    }
  }

  public function setStatInsertFirst($param){
    global $wpdb;
    $timer = microtime(true);
    $id=(int)$wpdb->get_var(
      $wpdb->prepare('
        SELECT id FROM '.$wpdb->prefix.'mystatdata
        WHERE
          created_at>=TIMESTAMP(%s) AND
          ip=%d AND
          ua=%s AND
          hash=%s AND
          referer=%s AND
          host=%s AND
          uri=%s
        ',
        date('Y-m-d',$this->getTime(false)),
        $param->ip,
        $param->ua,
        $param->hash,
        $param->referer->url,
        $param->host,
        $param->uri
      )
    );
    if($id==0){
      $r = $wpdb->replace(
        $wpdb->prefix.'mystatdata',
        Array(
          'time_start' => ($timer-floor($timer))*10000,
          'hash' => $param->hash,
          'ua' => $param->ua,
          'time_load' => 0,
          'ip' => $param->ip,
          'host' => $param->host,
          'www' => $param->www,
          'uri' => $param->uri,
          'referer' => $param->referer->url,
          'proxy' => $param->proxy,
          'is404' => $param->is404,
          'is_feed' => $param->feed,
          'lang' => $param->lang,
          'gzip' => $param->gzip,
          'deflate' => $param->deflate,
          'file' => $param->file,
          'title' => '',
          'count' => 1,
          'screen' => '',
          'depth' => 0,
          'created_at' => date('Y-m-d H:i:s',$this->getTime(false)),
          'updated_at' => date('Y-m-d H:i:s',$this->getTime(false))
        ),
        Array('%d','%s','%s','%d','%d','%s','%d','%s','%s','%d','%d','%d','%s','%d','%d','%s','%s','%d','%s','%d','%s','%s')
      );
      if($r>0){
        $id=$wpdb->insert_id;
      }
      return $id;
    }
    $wpdb->query("UPDATE ".$wpdb->prefix."mystatdata SET time_start=".(($timer-floor($timer))*10000).",count=count+1,updated_at='".date('Y-m-d H:i:s',$this->getTime(false))."' WHERE id=".$id);
    return -$id;
  }

  public function setStatInsertNext($id,$param){
    if($id==0){return false;}
    global $wpdb;
    $wpdb->update(
      $wpdb->prefix.'mystatdata',
      Array(
        'browser' => $param->browser,
        'browser_version' => $param->version,
        'device' => $param->device,
        'device_name' => $param->device_name,
        'referer' => $param->referer->url,
        'reftype' => $param->referer->type,
        'refname' => $param->referer->name,
        'refquery' => $param->referer->query,
        'country' => $param->country,
        'city' => $param->city,
        'mobile' => $param->mobile,
        'tablet' => $param->tablet,
        'crawler' => $param->crawler,
        'os' => $param->os,
        'osver' => $param->osver,
        'osname' => $param->osname,
        'osbit' => $param->osbit,
        'updated_at' => date('Y-m-d H:i:s',$this->getTime(false))
      ),
      Array(
        'id' => $id
      ),
      Array('%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%d','%d','%d','%s','%s','%s','%d','%s'),
      Array('%d')
    );
    return true;
  }

  public function setStatImage($id,$ip){
    header('Content-Type: image/gif');
    echo base64_decode('R0lGODlhAQABAJEAAAAAAP///////wAAACH5BAEAAAIALAAAAAABAAEAAAICVAEAOw==');
    if(function_exists('fastcgi_finish_request')){
//      fastcgi_finish_request();
    }
    global $wpdb;
    if($id>0){
      $rows=$wpdb->get_var(
        $wpdb->prepare('
          SELECT image FROM '.$wpdb->prefix.'mystatdata
          WHERE
            id=%d AND
            ip=%d
          ',
          $id,
          ip2long($ip)
        )
      );
      if($rows!=true or $rows==null){
        $rows=$wpdb->get_var(
          $wpdb->prepare('
            UPDATE '.$wpdb->prefix.'mystatdata SET
              image=%d
            WHERE
              id=%d
            ',
            true,
            $id
          )
        );
      }
    }
    exit;
  }

  public function setStatUpdate($id,$param,$ip,$tor){
    global $wpdb;
    if($id>0){
      $timer = microtime(true);
      $rows=$wpdb->get_results(
        $wpdb->prepare('
          SELECT updated_at,time_start FROM '.$wpdb->prefix.'mystatdata
          WHERE
            id=%d AND
            ip=%d
          ',
          $id,
          ip2long($ip)
        )
      );
      if(sizeof($rows)==0){return;}
      $tload = ($this->getTime(false)+($rows[0]->time_start/10000))-(strtotime($rows[0]->updated_at)+($timer-floor($timer)));

      $title = (string)$param->title;unset($param->title);
      $screen = '';
      if(isset($param->screen->width) and (int)$param->screen->width>0){
        $screen = $param->screen->width.'x'.$param->screen->height;
        $depth = $param->screen->depth;
        unset($param->screen);
      }
      $rows=$wpdb->get_var(
        $wpdb->prepare('
          UPDATE '.$wpdb->prefix.'mystatdata SET
            time_load=%f,
            is_tor=%d,
            title=%s,
            screen=%s,
            depth=%d,
            param=%s,
            updated_at=%s
          WHERE
            id=%d
          ',
          $tload,
          $tor,
          $title,
          $screen,
          $depth,
          json_encode($param),
          date('Y-m-d H:i:s',$this->getTime(false)),
          $id
        )
      );
    }
    $this->postDetected();
  }

  private function postDetected(){
    global $wpdb;
    $start = time();
    while((time()-$start)<10){
      $rows=$wpdb->get_results('
        SELECT * FROM '.$wpdb->prefix.'mystatdata
        WHERE
          browser="" OR
          browser IS NULL
        LIMIT 0,1
      ');
      if(sizeof($rows)>0){
        $wpdb->flush();
        foreach($rows as $row){
          $this->context->setStatisticsById((int)$row->id,$this->convertResult($row));
        }
      }else{
        break;
      }
    }
  }

  public static function getStatByPeriod($from,$to){
    global $wpdb;
    if(isset($this)){
      $this->postDetected();
    }
    $query = $wpdb->prepare('
      SELECT * FROM '.$wpdb->prefix.'mystatdata WHERE
        created_at>=%s AND
        created_at<=%s
      ',
      date('Y-m-d 00:00:00',$from),
      date('Y-m-d 23:59:59',$to)
    );
    if($wpdb->use_mysqli){
      $result = @mysqli_query($wpdb->dbh,$query,MYSQLI_USE_RESULT);
    }else{
      $result = @mysql_query($query,$wpdb->dbh);
    }
    if(!$result){return Array();}
    return new mystat_dbResultWordpress($result);
  }

  public function getDbSizeByPeriod($from,$to){
    global $wpdb;
    $query = $wpdb->get_results($wpdb->prepare('
      SELECT * FROM '.$wpdb->prefix.'mystatsize WHERE
        date>=%s AND
        date<=%s
      ',
      date('Y-m-d 00:00:00',$from),
      date('Y-m-d 23:59:59',$to)
    ),ARRAY_A);
    if(!$query){return Array();}
    return $query;
  }

  public function getClickStatByPeriod($from,$to){
    global $wpdb;
    $query = $wpdb->get_results($wpdb->prepare('
      SELECT * FROM '.$wpdb->prefix.'mystatclick WHERE
        created_at>=%s AND
        created_at<=%s
      ',
      date('Y-m-d 00:00:00',$from),
      date('Y-m-d 23:59:59',$to)
    ),ARRAY_A);
    if(!$query){return Array();}
    return $query;
  }

##############################################################

  public static function getCacheDir($web=false){
    $dir = plugins_url('',dirname(__FILE__));
    $dir = preg_split('/\//',trim($dir));
    $dir = array_pop($dir);
    if($web){
      $upload_dir = wp_upload_dir();
      return $upload_dir['baseurl'].'/'.$dir.'/';
    }
    if(!file_exists(dirname(__FILE__).'/../../../uploads/')){
      mkdir(dirname(__FILE__).'/../../../uploads/');
    }
    if(!file_exists(dirname(__FILE__).'/../../../uploads/'.$dir.'/')){
      mkdir(dirname(__FILE__).'/../../../uploads/'.$dir.'/');
    }
    return dirname(__FILE__).'/../../../uploads/'.$dir.'/';
    if(!file_exists(dirname(__FILE__).'/../cache/')){
      mkdir(dirname(__FILE__).'/../cache/');
    }
    return dirname(__FILE__).'/../cache/';
  }

  public function isAccess(){
    return current_user_can($this->getOption('mystataccess','update_plugins'));
  }

  public function getCurrentRole(){
    if(current_user_can('update_plugins')){
      return 'ADMIN';
    }
    if(current_user_can('publish_posts')){
      return 'EDITOR';
    }
    return 'USER';
  }

  public function getRole(){
    $role = 'ADMIN';
    switch($this->getOption('mystataccess','update_plugins')){
      case 'read':
        $role = 'USER';
        break;
      case 'publish_posts':
        $role = 'EDITOR';
        break;
      case 'update_plugins':
      default:
        $role = 'ADMIN';
        break;
    }
    return $role;
  }

  public function setRole($role){
    switch($role){
      case 'USER':
        $this->setOption('mystataccess','read');
        break;
      case 'EDITOR':
        $this->setOption('mystataccess','publish_posts');
        break;
      case 'ADMIN':
      default:
        $this->setOption('mystataccess','update_plugins');
        break;
    }
    return $this;
  }

  public function addMenuInSiteList($blog,$conf){
    if(current_user_can($this->getOption('mystataccess','update_plugins'))){
      return $blog.' | <a href="'.esc_url(get_admin_url($conf->userblog_id).'admin.php?page='.plugin_basename('statistics.aspx')).'">'.$this->__('My Statistics').'</a>';
    }
  }

  public function addMenuInSiteListFull($blog,$id){
    $blog1 = Array();
    if(current_user_can($this->getOption('mystataccess','update_plugins'))){
      $blog1['mystat'] = '<a href="'.esc_url(get_admin_url($id).'admin.php?page='.plugin_basename('statistics.aspx')).'">'.$this->__('My Statistics').'</a>';
    }
    $blog1 = array_merge($blog1,$blog);
    return $blog1;
  }

  public function addMenuInBar($bar){
    if(!is_network_admin() && is_admin() && current_user_can($this->getOption('mystataccess','update_plugins'))){
      $bar->add_menu(Array(
        'parent' => 'site-name',
        'id'     => 'site-mystat',
        'title'  => $this->__('My Statistics'),
        'href'   => get_admin_url().'admin.php?page='.plugin_basename('statistics.aspx')
      ));
    }
    if(is_user_logged_in() && is_multisite()){
      foreach((array)$bar->user->blogs as $blog){
        switch_to_blog($blog->userblog_id);
        $menu_id  = 'blog-'.$blog->userblog_id;
        if(current_user_can($this->getOption('mystataccess','update_plugins'))){
          $bar->add_menu(Array(
            'parent' => $menu_id,
            'id'     => $menu_id . '-mystat',
            'title'  => $this->__('My Statistics'),
            'href'   => get_admin_url($blog->userblog_id).'admin.php?page='.plugin_basename('statistics.aspx')
          ));
        }
        restore_current_blog();
      }
    }
  }

  public function addHookCode(){
//    if(is_feed() or is_robots() or is_trackback() or is_preview()){return;}
    $header = headers_list();
    foreach($header as $head){
      if(preg_match('/text\/xml/i',$head)){
        return;
      }
    }
    if(is_admin()){return;}
    ob_start();
    call_user_func(array_shift($this->php),array_shift($this->php));
    $html = ob_get_contents();
    ob_clean();
    if(strlen($html)>0){
      echo '<!-- mfunc W3TC_DYNAMIC_SECURITY -->';
      echo $html;
      echo '<!-- /mfunc W3TC_DYNAMIC_SECURITY -->';
    }
    if(function_exists('fastcgi_finish_request')){
//      fastcgi_finish_request();
    }
    $this->addHeaderCode();
  }

  public function installPlugin($networkwide=false){
    global $wpdb;
    if(function_exists('is_multisite') && is_multisite()){
      if($networkwide){
        $blogids = $wpdb->get_col('SELECT blog_id FROM '.$wpdb->blogs);
        foreach($blogids as $blog_id){
          switch_to_blog($blog_id);
          $this->installPluginRun(false);
          restore_current_blog();
        }
        return;
      }   
    } 
    $this->installPluginRun();   
  }

  protected function installPluginRun($redirect = true){
    global $wpdb;
    $charset_collate = '';
    if(!empty($wpdb->charset)){
      $charset_collate = 'DEFAULT CHARACTER SET '.$wpdb->charset;
    }
    if(!empty( $wpdb->collate ) ) {
      $charset_collate.= ' COLLATE '.$wpdb->collate;
    }
    require_once(ABSPATH.'wp-admin/includes/upgrade.php');
    $table_name = $wpdb->prefix.'mystatdata';
    $sql = 'CREATE TABLE '.$table_name.' ('."\n".
      'id int(11) UNSIGNED NOT NULL AUTO_INCREMENT,'."\n".
      'hash varchar(32),'."\n".
      'ua text,'."\n".
      'browser varchar(200),'."\n".
      'browser_version varchar(10),'."\n".
      'device varchar(200),'."\n".
      'device_name varchar(200),'."\n".
      'time_start int(11) UNSIGNED,'."\n".
      'time_load float(9,4) UNSIGNED,'."\n".
      'ip bigint,'."\n".
      'image bool,'."\n".
      'proxy bool,'."\n".
      'is404 bool,'."\n".
      'is_tor bool,'."\n".
      'is_feed bool,'."\n".
      'title text,'."\n".
      'host varchar(200),'."\n".
      'www bool,'."\n".
      'file varchar(200),'."\n".
      'uri text,'."\n".
      'referer text,'."\n".
      'lang char(2),'."\n".
      'reftype varchar(50),'."\n".
      'refname varchar(50),'."\n".
      'refquery text,'."\n".
      'country char(2),'."\n".
      'city char(150),'."\n".
      'screen varchar(12),'."\n".
      'depth smallint,'."\n".
      'gzip bool,'."\n".
      'deflate bool,'."\n".
      'mobile bool,'."\n".
      'tablet bool,'."\n".
      'crawler bool,'."\n".
      'os varchar(50),'."\n".
      'osver varchar(10),'."\n".
      'osname varchar(250),'."\n".
      'osbit tinyint,'."\n".
      'count int(11) UNSIGNED,'."\n".
      'param longtext,'."\n".
      'created_at timestamp NOT NULL default \'0000-00-00 00:00:00\','."\n".
      'updated_at timestamp NOT NULL default \'0000-00-00 00:00:00\','."\n".
      'UNIQUE KEY id (id)'."\n".
    ') '.$charset_collate.';';
    dbDelta($sql);
    $table_name = $wpdb->prefix.'mystatclick';
    $sql='CREATE TABLE '.$table_name.' ('."\n".
      'x smallint(6) UNSIGNED,'."\n".
      'y smallint(6) UNSIGNED,'."\n".
      'width smallint(6) UNSIGNED,'."\n".
      'touch bool,'."\n".
      'uri text,'."\n".
      'xpath text,'."\n".
      'id_usr int(11) UNSIGNED,'."\n".
      'created_at timestamp NOT NULL default \'0000-00-00 00:00:00\''."\n".
    ') '.$charset_collate.';';
    dbDelta($sql);
    $table_name = $wpdb->prefix.'mystatsize';
    $sql='CREATE TABLE '.$table_name.' ('."\n".
      'date date,'."\n".
      'size int(11) unsigned'."\n".
    ') '.$charset_collate.';';
    dbDelta($sql);
    $this->setOption('mystatversion',MYSTAT_VERSION);
    $this->setOption('mystat');
    $this->setOption('mystatlastupdate');
    $this->context->updateDefinition(false);
    if($redirect){
      if(!($error = $this->context->isInstallCorrect(false) and sizeof($error)>0)){
        wp_safe_redirect($this->getRedirectUri());
      }
    }
  }

  public function addNewBlog($blog_id, $user_id, $domain, $path, $site_id, $meta){
    global $wpdb;
    if(is_plugin_active_for_network(plugins_url('index.php', dirname(__FILE__)))){
      $old_blog = $wpdb->blogid;
      switch_to_blog($blog_id);
      $this->installPluginRun();   
      switch_to_blog($old_blog);
    }
  }

  public function unstallPlugin($networkwide){
    global $wpdb;
    if(function_exists('is_multisite') && is_multisite()){
      if($networkwide){
        $blogids = $wpdb->get_col('SELECT blog_id FROM '.$wpdb->blogs);
        foreach($blogids as $blog_id){
          switch_to_blog($blog_id);
          $this->unstallPluginRun();
          restore_current_blog();
        }
        return;
      }   
    } 
    $this->unstallPluginRun();   
  }

  public function unstallPluginRun(){
    if(!current_user_can('activate_plugins')){return;}
    $plugin = isset($_REQUEST['plugin'])?$_REQUEST['plugin']:'';
    check_admin_referer('deactivate-plugin_'.$plugin);
    $this->setOption('mystatversion');
    $this->setOption('mystat');
    $this->setOption('mystatlastupdate');
  }
  
  public static function removePlugin(){
    if(!current_user_can('activate_plugins')){return;}
    check_admin_referer('bulk-plugins');
    global $wpdb;
    $wpdb->query('DROP TABLE '.$wpdb->prefix.'mystatdata;');
    $wpdb->query('DROP TABLE '.$wpdb->prefix.'mystatclick;');
    $wpdb->query('DROP TABLE '.$wpdb->prefix.'mystatsize;');
  }

  public function updatePlugin(){
    global $wpdb;
    load_plugin_textdomain('mystat',false,dirname(plugin_basename(realpath(dirname(__FILE__).'/../index.php'))).'/language');
    if($this->getOption('mystatversion') != MYSTAT_VERSION){
      if(function_exists('is_multisite') && is_multisite()){
        $blogids = $wpdb->get_col('SELECT blog_id FROM ' . $wpdb->blogs);
        foreach($blogids as $blog_id){
          switch_to_blog($blog_id);
          $this->installPluginRun(false);
          restore_current_blog();
        }
        return;
      }else{
        $this->installPlugin();
      }
    }
  }

  public function addMenu(){
    add_menu_page($this->__('My Statistics'),$this->__('My Statistics'),$this->getOption('mystataccess','update_plugins'),'statistics.aspx',Array($this,'setOpenPage'),'dashicons-chart-bar','2.000123');
  }

  public function updateInPluginList(){
    echo '<div id="mystat_up_div"></div><script>jQuery("#mystat_up_div").parent().children(".update-link").removeClass("update-link");</script>';
  }

  public function directRun(){
    if($this->getParam('in')){
      $this->setOpenPage(true);
      exit;
    }
  }

  public function setOpenPage($ajax = false){
    if($this->run===false){return;}
    echo !$ajax?'<div id="mystat">':'';
    call_user_func(array_shift($this->run),array_shift($this->run));
    echo !$ajax?'</div>':'';
  }

  public function ajaxRun(){
    $this->setOpenPage(true);
    exit;
  }

  public function ajaxRunPublic(){
    $page = (string)$this->getParam('report','dashboard');
    if(in_array($page,Array('insert','insertclick'))){
      $this->setOpenPage(true);
      echo '{"success":true}';
      exit;
    }
    echo '{"success":false}';
    exit;
  }
  
  public function adminNotice(){
    if($this->getCurrentRole()!='ADMIN'){return;}
    if($error = $this->context->isInstallCorrect(true) and sizeof($error)>0){
      foreach($error as $e){
        switch($e){
          case 'WRITE':
            echo '<div class="error">';
            echo '<p><strong>'.$this->__('My Statistics').':</strong> '.$this->__('Plugin has no permissions to write to the directory "cache". Plugin can not independently resolve this error. Contact your administrator.').'</p>';
            echo '</div>';
            break;
          case 'ZLIB':
            echo '<div class="notice notice-warning">';
            echo '<p><strong>'.$this->__('My Statistics').':</strong> <a href="http://php.net/manual/en/zlib.installation.php" target="_blank">'.$this->__('You need set up your PHP with ZLIB extension').'</a></p>';
            echo '</div>';
            break;
          case 'ZIP':
            echo '<div class="notice notice-warning">';
            echo '<p><strong>'.$this->__('My Statistics').':</strong> <a href="http://php.net/manual/en/zip.installation.php" target="_blank">'.$this->__('You need set up your PHP with ZIP extension').'</a></p>';
            echo '</div>';
            break;
          case 'DOM':
            echo '<div class="error">';
            echo '<p><strong>'.$this->__('My Statistics').':</strong> <a href="http://php.net/manual/en/dom.installation.php" target="_blank">'.$this->__('You need set up your PHP with DOM extension').'</a></p>';
            echo '</div>';
            break;
          case 'XSLT':
            echo '<div class="error">';
            echo '<p><strong>'.$this->__('My Statistics').':</strong> <a href="http://php.net/manual/en/xsl.installation.php" target="_blank">'.$this->__('You need set up your PHP with XSL extension').'</a></p>';
            echo '</div>';
            break;
        }
      }
      return false;
    }
    if($this->context->isNeedUpdate()){
      echo '<div class="update-nag">';
      echo '<strong>'.$this->__('My Statistics').':</strong> '.$this->__('Need to update definitions');
      echo '<a id="update_mystat" style="margin-left: 10px;margin-top:-3px;margin-bottom:-3px;white-space:normal;" class="button button-small button-primary" onclick="if(!jQuery(\'#update_mystat\').hasClass(\'button-primary\')){return false;};jQuery(\'#update_mystat\').removeClass(\'button-primary\');jQuery(\'#update_mystat .spinner\').show();jQuery.ajax({url: ajaxurl,data: {action: \'mystat\',report: \'update\'},timeout: 300000, dataType: \'html\',type: \'POST\',success: function(data, textStatus){document.location=\''.$this->getRedirectUri().'\';},error: function(){alert(\''.$this->__('An error occurred during the update, please, try again later.').'\');jQuery(\'#update_mystat\').addClass(\'button-primary\');jQuery(\'#update_mystat .spinner\').hide();}});return false;">'.$this->__('Update').' <span class="spinner" style="visibility: visible;display: none;margin: 1px 10px 0;"></span></a>';
      echo '</div>';
    }
    if(file_exists($this->getCacheDir().'alert.dat')){
      $alert = @file_get_contents($this->getCacheDir().'alert.dat');
      if(trim($alert)!=''){
        $alert = strip_tags(base64_decode($alert),'<br/><b><i><a><div><p><img><span><strong><em><table><td><th><tr><h1><h2><h3><h4><button>');
        echo '<div class="notice notice-info is-dismissible">';
        echo '<p>'.$alert.'</p>';
        echo '</div>';
      }
    }
  }

  public function adminScripts(){
    $webpath = $this->getWebPath();
    if($this->getOption('mystatproxygoogle','false')=='true'){
      wp_register_script('google_js', 'https://my-stat.com/google/loader.js');
    }else{
      wp_register_script('google_js', 'https://www.gstatic.com/charts/loader.js');
    }
    wp_enqueue_script('google_js');
    wp_register_script('mystatlogo_js', trim($webpath,'/').'/logo.min.js',false,'0.4.2' );
    wp_enqueue_script('mystatlogo_js');
    wp_register_script('moment_js', trim($webpath,'/').'/moment.min.js', Array('jquery-core'), '2.12.0' );
    wp_enqueue_script('moment_js');
    wp_register_script('mask_js', trim($webpath,'/').'/jquery.mask.min.js', Array('jquery-core'), '1.14.3' );
    wp_enqueue_script('mask_js');
    wp_register_script('daterangepicker_js', trim($webpath,'/').'/jquery.daterangepicker.min.js', Array('jquery-core','moment_js'), '0.0.9' );
    wp_enqueue_script('daterangepicker_js');
    wp_register_style('daterangepicker_css', trim($webpath,'/').'/jquery.daterangepicker.min.css', false, '0.0.9' );
    wp_enqueue_style('daterangepicker_css');
  }

  public function initWP(){
    if(!is_admin()){
      $cookie = '';
      if(isset($_COOKIE['mystathash']) and $_COOKIE['mystathash']!=''){
        $cookie = $_COOKIE['mystathash'];
      }
      if($cookie==''){
        $cookie = $this->getUserHash();
      }
      @setcookie('mystathash',$cookie,$this->getTime(false)+(60*60*24*365),COOKIEPATH, COOKIE_DOMAIN);
    }
  }

  private function getRedirectUri($report=false){
    return admin_url('admin.php?page='.plugin_basename('statistics.aspx')).($report!==false?'&report='.$report:'');
  }

  public function addWPStatUser(){
    $user_query = new WP_User_Query(array('orderby'=>'registered', 'order'=>'ASC'));
    $users = (array)$user_query->results;
    $arr = $arr30 = Array();
    for($i=29;$i>=0;$i--){
      $arr30[date('Y-m-d',strtotime(date('Y-m-d',$this->getTime(true)).' 00:00:00 -'.$i.'days'))] = 0;
    }
    foreach($users as $u){
      $date = date('Y-m-d',strtotime($u->user_registered));
      if(isset($arr30[$date])){
        $arr30[$date]++;
      }
      if(isset($arr[$date])){
        $arr[$date]++;
      }else{
        $arr[$date] = 1;
      }
    }
    $json30 = $jsonall = Array();
    foreach($arr30 as $date=>$count){
      $json30[] = '[new Date('.strtotime($date.' 00:00:00').' * 1000),'.$count.']';
    }
    $json30 = join(',',$json30);
    foreach($arr as $date=>$count){
      $jsonall[] = '[new Date('.strtotime($date.' 00:00:00').' * 1000),'.$count.']';
    }
    $json = join(',',$jsonall);
    ?>
    <style>
      #mystat_graphic{clear:both;margin: 20px;}
      #mystat_title{float:left;font-size:16px;margin:10px 50px;}
      #mystat_button{float:right;margin:10px;}
    </style>
    <script>
      if(jQuery("#mystat_graphic").length==0){
        var json30 = [<?php echo $json30;?>];
        var jsonall = [<?php echo $json;?>];
        var el = jQuery(".wrap h2");
        if(el.length==0){
          el = jQuery(".wrap h1");
        }
        el.after("<div class='postbox'><div id='mystat_title'><?php echo addslashes($this->__('User registrations'));?></div><div id='mystat_button'><a class='button button-small button-primary' onclick='mystatChartReload(false);'><?php echo addslashes($this->__('Within 30 days'));?></a> <a class='button button-small' onclick='mystatChartReload(true);'><?php echo addslashes($this->__('Throughout the whole period'));?></a></div><div id='mystat_graphic'></div></div>");
        if(typeof google != 'undefined' && typeof google.visualization == 'undefined'){
          google.charts.load('current',{'packages':['corechart'], 'language':'<?php echo $this->getLanguage();?>'});
          google.charts.setOnLoadCallback(viewChart);
        }
        var chart = null;
        var data = null;
        var options = {
          height: 150,
          legend: {
            position: 'labeled'
          },
          vAxis: {
            format: '#'
          },
          pieHole: 0.4,
          dataOpacity: 0.9,
          theme: 'maximized',
          focusTarget: 'category'
        };
        function viewChart(){
          if(typeof google == 'undefined' || typeof google.visualization == 'undefined' || typeof google.visualization.DataTable == 'undefined'){return;}
          data = new google.visualization.DataTable();
          data.addColumn('date', '');
          data.addColumn('number', "<?php echo addslashes($this->__('Users registered'));?>");
          data.addRows(json30);
          chart = new google.visualization.ColumnChart(document.getElementById('mystat_graphic'));
          chart.draw(data, options);
        }
        function mystatChartReload(all){
          console.info(jQuery('#mystat_button a'));
          jQuery('#mystat_button a').eq(all?0:1).removeClass('button-primary');
          jQuery('#mystat_button a').eq(all?1:0).addClass('button-primary');
          data.removeRows(0,all?json30.length:jsonall.length);
          data.addRows(all?jsonall:json30);
          chart.draw(data,options);
        }
       }
    </script>
    <?php
  }

  public function addWPStatPost(){
    global $typenow;
    if($typenow!='post'){return;}
    $posts = get_posts(array('posts_per_page'=>5000));
    $arr = $arr30 = Array();
    for($i=29;$i>=0;$i--){
      $arr30[date('Y-m-d',strtotime(date('Y-m-d',$this->getTime(true)).' 00:00:00 -'.$i.'days'))] = 0;
    }
    foreach($posts as $p){
      $date = date('Y-m-d',strtotime($p->post_date));
      if(isset($arr30[$date])){
        $arr30[$date]++;
      }
      if(isset($arr[$date])){
        $arr[$date]++;
      }else{
        $arr[$date] = 1;
      }
    }
    $json30 = $jsonall = Array();
    foreach($arr30 as $date=>$count){
      $json30[] = '[new Date('.strtotime($date.' 00:00:00').' * 1000),'.$count.']';
    }
    $json30 = join(',',$json30);
    foreach($arr as $date=>$count){
      $jsonall[] = '[new Date('.strtotime($date.' 00:00:00').' * 1000),'.$count.']';
    }
    $json = join(',',$jsonall);
    ?>
    <style>
      #mystat_graphic{clear:both;margin: 20px;}
      #mystat_title{float:left;font-size:16px;margin:10px 50px;}
      #mystat_button{float:right;margin:10px;}
    </style>
    <script>
      if(jQuery("#mystat_graphic").length==0){
        var json30 = [<?php echo $json30;?>];
        var jsonall = [<?php echo $json;?>];
        var el = jQuery(".wrap h2");
        if(el.length==0){
          el = jQuery(".wrap h1");
        }
        el.after("<div class='postbox'><div id='mystat_title'><?php echo addslashes($this->__('User posts'));?></div><div id='mystat_button'><a class='button button-small button-primary' onclick='mystatChartReload(false);'><?php echo addslashes($this->__('Within 30 days'));?></a> <a class='button button-small' onclick='mystatChartReload(true);'><?php echo addslashes($this->__('Throughout the whole period'));?></a></div><div id='mystat_graphic'></div></div>");
        if(typeof google != 'undefined' && typeof google.visualization == 'undefined'){
          google.charts.load('current',{'packages':['corechart'], 'language':'<?php echo $this->getLanguage();?>'});
          google.charts.setOnLoadCallback(viewChart);
        }
        var chart = null;
        var data = null;
        var options = {
          height: 150,
          legend: {
            position: 'labeled'
          },
          vAxis: {
            format: '#'
          },
          pieHole: 0.4,
          dataOpacity: 0.9,
          theme: 'maximized',
          focusTarget: 'category'
        };
        function viewChart(){
          if(typeof google == 'undefined' || typeof google.visualization == 'undefined' || typeof google.visualization.DataTable == 'undefined'){return;}
          data = new google.visualization.DataTable();
          data.addColumn('date', '');
          data.addColumn('number', "<?php echo addslashes($this->__('User posts'));?>");
          data.addRows(json30);
          chart = new google.visualization.ColumnChart(document.getElementById('mystat_graphic'));
          chart.draw(data, options);
        }
        function mystatChartReload(all){
          console.info(jQuery('#mystat_button a'));
          jQuery('#mystat_button a').eq(all?0:1).removeClass('button-primary');
          jQuery('#mystat_button a').eq(all?1:0).addClass('button-primary');
          data.removeRows(0,all?json30.length:jsonall.length);
          data.addRows(all?jsonall:json30);
          chart.draw(data,options);
        }
       }
    </script>
    <?php
  }

  public function addWPStatComm(){
    $comment = get_comments();
    $arr = $arr30 = Array();
    for($i=29;$i>=0;$i--){
      $arr30[date('Y-m-d',strtotime(date('Y-m-d',$this->getTime(true)).' 00:00:00 -'.$i.'days'))] = 0;
    }
    foreach($comment as $c){
      $date = date('Y-m-d',strtotime($c->comment_date));
      if(isset($arr30[$date])){
        $arr30[$date]++;
      }
      if(isset($arr[$date])){
        $arr[$date]++;
      }else{
        $arr[$date] = 1;
      }
    }
    $json30 = $jsonall = Array();
    foreach($arr30 as $date=>$count){
      $json30[] = '[new Date('.strtotime($date.' 00:00:00').' * 1000),'.$count.']';
    }
    $json30 = join(',',$json30);
    foreach($arr as $date=>$count){
      $jsonall[] = '[new Date('.strtotime($date.' 00:00:00').' * 1000),'.$count.']';
    }
    $jsonall = join(',',$jsonall);
    ?>
    <style>
      #mystat_graphic{clear:both;margin: 20px;}
      #mystat_title{float:left;font-size:16px;margin:10px 50px;}
      #mystat_button{float:right;margin:10px;}
    </style>
    <script>
      if(jQuery("#mystat_graphic").length==0){
        var json30 = [<?php echo $json30;?>];
        var jsonall = [<?php echo $jsonall;?>];
        var el = jQuery(".wrap h2");
        if(el.length==0){
          el = jQuery(".wrap h1");
        }
        el.after("<div class='postbox'><div id='mystat_title'><?php echo addslashes($this->__('User comments'));?></div><div id='mystat_button'><a class='button button-small button-primary' onclick='mystatChartReload(false);'><?php echo addslashes($this->__('Within 30 days'));?></a> <a class='button button-small' onclick='mystatChartReload(true);'><?php echo addslashes($this->__('Throughout the whole period'));?></a></div><div id='mystat_graphic'></div></div>");
        if(typeof google != 'undefined' && typeof google.visualization == 'undefined'){
          google.charts.load('current',{'packages':['corechart'], 'language':'<?php echo $this->getLanguage();?>'});
          google.charts.setOnLoadCallback(viewChart);
        }
        var chart = null;
        var data = null;
        var options = {
          height: 150,
          legend: {
            position: 'labeled'
          },
          vAxis: {
            format: '#'
          },
          pieHole: 0.4,
          dataOpacity: 0.9,
          theme: 'maximized',
          focusTarget: 'category'
        };
        function viewChart(){
          if(typeof google == 'undefined' || typeof google.visualization == 'undefined' || typeof google.visualization.DataTable == 'undefined'){return;}
          data = new google.visualization.DataTable();
          data.addColumn('date', '');
          data.addColumn('number', "<?php echo addslashes($this->__('User comments'));?>");
          data.addRows(json30);
          chart = new google.visualization.ColumnChart(document.getElementById('mystat_graphic'));
          chart.draw(data, options);
        }
        function mystatChartReload(all){
          console.info(jQuery('#mystat_button a'));
          jQuery('#mystat_button a').eq(all?0:1).removeClass('button-primary');
          jQuery('#mystat_button a').eq(all?1:0).addClass('button-primary');
          data.removeRows(0,all?json30.length:jsonall.length);
          data.addRows(all?jsonall:json30);
          chart.draw(data,options);
        }
       }
    </script>
    <?php
  }

  function widgetRegister1() {
    register_widget('mystat_widgetPlugin1');
  }

  public function dbSizeCollect(){
    global $wpdb;
    if($this->getOption('mystatcleanstart')==date('dmY',$this->getTime(false))){
      return;
    }
    $days = (int)$this->getOption('mystatcleanday',120);
    $days = $days>30?$days:30;
    $wpdb->query('DELETE FROM '.$wpdb->prefix.'mystatdata WHERE created_at<=TIMESTAMP("'.date('Y-m-d 00:00:00',strtotime(date('Y-m-d',$this->getTime(false)).' -'.$days.' days')).'")');
    $wpdb->query('DELETE FROM '.$wpdb->prefix.'mystatclick WHERE created_at<=TIMESTAMP("'.date('Y-m-d 00:00:00',strtotime(date('Y-m-d',$this->getTime(false)).' -'.$days.' days')).'")');
    $wpdb->query('DELETE FROM '.$wpdb->prefix.'mystatsize WHERE date<=TIMESTAMP("'.date('Y-m-d 00:00:00',strtotime(date('Y-m-d',$this->getTime(false)).' -'.$days.' days')).'")');
    $wpdb->query('OPTIMIZE TABLE '.$wpdb->prefix.'mystatdata');
    $wpdb->query('OPTIMIZE TABLE '.$wpdb->prefix.'mystatclick');
    $wpdb->query('OPTIMIZE TABLE '.$wpdb->prefix.'mystatsize');
    $query = $wpdb->get_results('SHOW TABLE STATUS LIKE \''.$wpdb->prefix.'mystat%\'',ARRAY_A);
    $size = 0;
    foreach($query as $el){
      $size+= $el['Data_length'] + $el['Index_length'];
    }
    $exist = (int)$wpdb->get_var($wpdb->prepare('
      SELECT count(*)
      FROM '.$wpdb->prefix.'mystatsize
      WHERE date = %s', 
      date('Y-m-d',$this->getTime(false))
    ));
    if($exist==0){
      $wpdb->insert(
        $wpdb->prefix.'mystatsize',
        Array(
          'date' => date('Y-m-d',$this->getTime(false)),
          'size' => $size
        ),
        Array('%s','%f')
      );
    }else{
      $wpdb->update(
        $wpdb->prefix.'mystatsize',
        Array(
          'size' => $size
        ),
        Array(
          'date' => date('Y-m-d',$this->getTime(false))
        ),
        Array('%f'),
        Array('%s')
      );
    }
    $this->setOption('mystatcleanstart',date('dmY',$this->getTime(false)));
  }

  public function addHeaderCode(){
    $this->dbSizeCollect();
  }

}

class mystat_dbResultWordpress implements Iterator{

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
    $el = mystat_wordpress::convertResult($this->row);
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
    if($wpdb->use_mysqli){
      $r = mysqli_fetch_object($this->link);
    }else{
      $r = mysql_fetch_object($this->link);
    }
    $this->row = $r;
    if($this->row!=null){
//    if($this->position<$this->count){
      return true;
    }
    if($wpdb->use_mysqli){
      mysqli_free_result($this->link);
    }else{
      mysql_free_result($this->link);
    }
    return false;
  }

}

if(class_exists('WP_Widget')){
  
  class mystat_widgetPlugin1 extends WP_Widget{

    function __construct() {
      parent::__construct('mystat_widget_1', mystat_wordpress::__('My Statistics'),Array('description' => mystat_wordpress::__('Site Visitor Statistics')));
    }

    protected function isUser($el){
      return ($el->crawler!=true and !is_null($el->crawler));
    }

    function widget($args, $instance){
      echo $args['before_widget'];
      if (!empty( $instance['title'])){
        echo $args['before_title'];
        echo esc_html($instance['title']);
        echo $args['after_title'];
      }
      if($instance['style']=='DEFAULT'){
      ?>
        <style type="text/css">
          .widget.widget_mystat_widget_1{
            font-family: Arial;
          }
          .widget.widget_mystat_widget_1 .user_info{
            padding: 3px;
            margin: 10px 0;
          }
          .widget.widget_mystat_widget_1 .user_info .os, .widget.widget_mystat_widget_1 .user_info .browser, .widget.widget_mystat_widget_1 .user_info .ip{
            background-color:#fff;
            border: 1px solid #ccc;
            margin: 2px;
            height: 34px;
            line-height: 30px;
          }
          .widget.widget_mystat_widget_1 .user_info .os .osname, .widget.widget_mystat_widget_1 .user_info .browser .browsername, .widget.widget_mystat_widget_1 .user_info .ip .text{
            font-size: 14px;
          }
          .widget.widget_mystat_widget_1 .user_info .os .osbit, .widget.widget_mystat_widget_1 .user_info .browser .browserversion, .widget.widget_mystat_widget_1 .user_info .ip .ipaddress{
            font-size: 14px;
            color: #bbb;
          }
          .widget.widget_mystat_widget_1 .user_info .ip .text{
            margin-left: 10px;
          }
          .widget.widget_mystat_widget_1 .user_count, .widget.widget_mystat_widget_1 .user_online, .widget.widget_mystat_widget_1 .robot_online{
            margin: 15px 0;
          }
          .widget.widget_mystat_widget_1 .user_count .text, .widget.widget_mystat_widget_1 .user_online .text, .widget.widget_mystat_widget_1 .robot_online .text{
            text-align:center;
            font-size: 18px;
          }
          .widget.widget_mystat_widget_1 .user_count .count, .widget.widget_mystat_widget_1 .user_online .count, .widget.widget_mystat_widget_1 .robot_online .count{
            font-weight: bold;
            font-size:40px;
            line-height:120px;
            text-align:center;
            background-color:#eee;
            border-radius:50%;
            margin:0 auto;
            width:120px;
            height:120px;
            box-shadow: 0px 0px 4px rgba(0, 0, 0, 0.4);
            overflow: hidden;
          }
          .widget.widget_mystat_widget_1 .country_top{
            padding: 3px;
            margin: 10px 0;
          }
          .widget.widget_mystat_widget_1 .country_top .item{
            background-color:#fff;
            border: 1px solid #ccc;
            margin: 2px;
            height: 26px;
            line-height: 22px;
            font-size: 14px;
          }
          .widget.widget_mystat_widget_1 .country_flag img{
            background-color:#fff;
            border: 1px solid #eee;
            margin: 2px;
            box-shadow: 0px 0px 1px rgba(0, 0, 0, 0.4);
          }
        </style>
      <?php
      }
      $period = Array();
      switch($instance['period']){
        case 'TD':
          $period['start'] = strtotime(date('d.m.Y 00:00:00',mystat_wordpress::getTime()));
          $period['end'] = strtotime(date('d.m.Y 23:59:59',mystat_wordpress::getTime()));
          break;
        case 'YD':
          $period['start'] = strtotime(date('d.m.Y 00:00:00',mystat_wordpress::getTime()).' -1 day');
          $period['end'] = strtotime(date('d.m.Y 23:59:59',mystat_wordpress::getTime()).' -1 day');
          break;
        case 'LW':
          $period['start'] = strtotime(date('d.m.Y 00:00:00',mystat_wordpress::getTime()).' -7 days');
          $period['end'] = strtotime(date('d.m.Y 23:59:59',mystat_wordpress::getTime()));
          break;
        case 'LM':
          $period['start'] = strtotime(date('d.m.Y 00:00:00',mystat_wordpress::getTime()).' -30 days');
          $period['end'] = strtotime(date('d.m.Y 23:59:59',mystat_wordpress::getTime()));
          break;
      }
      $data = mystat_wordpress::getStatByPeriod($period['start'],$period['end']);
      $uniqip = Array();$uniq = false;
      $country = Array();
      $userbyday = Array();
      foreach($data as $d){
        if(in_array($d->ip,$uniqip)){
          $uniq = false;
        }else{
          $uniqip[] = $d->ip;
          $uniq = true;
        }
        if($this->isUser($d) and $uniq){
          if(isset($userbyday[date('Y-m-d',$d->created_at)])){
            $userbyday[date('Y-m-d',$d->created_at)]++;
          }else{
            $userbyday[date('Y-m-d',$d->created_at)] = 1;
          }
          if(!in_array($d->country,Array('','-','AA'))){
            if(!array_key_exists($d->country,$country)){
              $country[$d->country] = 1;
            }else{
              $country[$d->country]++;
            }
          }
        }
      }
      arsort($country);
      $runchart = Array();
      if($instance['country_map']==1){
        $uuid = round(microtime(true)*10000);
        $runchart[] = 'viewChart'.$uuid.'();'
        ?>
        <div id="mystat_chart_<?php echo $uuid;?>"></div>
        <script type="text/javascript">
          function viewChart<?php echo $uuid;?>(){
            var data = new google.visualization.DataTable();
            data.addColumn('string', '');
            data.addColumn('string', '');
            data.addColumn('number', "<?php echo addslashes(mystat_wordpress::__('Unique visitors'));?>");
            data.addRows(<?php 
              $ret = Array();
              require_once(dirname(__FILE__).'/../lib/country.class.php');
              $cn = new mystat_country();
              $cn->setCacheDir(mystat_wordpress::getCacheDir());
              foreach($country as $k=>$v){
                $ret[] = Array($k,$cn->getCountryByCode($k,strtoupper(substr(get_locale(),0,2))),$v);
              }
              echo json_encode($ret);
            ?>);
            var options = {
              forceIFrame: true,
              legend: {
                position: 'labeled'
              },
              vAxis: {
                format: '#'
              },
              theme: 'maximized',
              dataOpacity: 0.9,
              focusTarget: 'category'
            };
            var chart = new google.visualization.GeoChart(document.getElementById('mystat_chart_<?php echo $uuid;?>'));
            chart.draw(data, options);
          }
        </script>
        <?php
      }
      if($instance['country_top']==1){
        require_once(dirname(__FILE__).'/../lib/country.class.php');
        $cn = new mystat_country();
        $cn->setCacheDir(mystat_wordpress::getCacheDir());
        $i=0;
        echo '<div class="country_top">';
        foreach($country as $k=>$v){
          if(file_exists(mystat_wordpress::getCacheDir().'icon/flags/'.$k.'.png')){
            echo '<div class="item"><img src="'.mystat_wordpress::getCacheDir(true).'icon/flags/'.$k.'.png'.'" width="24px" height="24px" style="width:24px;height:24px;" title="'.addslashes($cn->getCountryByCode($k,strtoupper(substr(get_locale(),0,2)))).' - '.$v.'"/> '.$cn->getCountryByCode($k,strtoupper(substr(get_locale(),0,2))).' - '.$v.'</div>';
          }
          $i++;
          if($i>10){break;}
        }
        echo '</div>';
      }
      if($instance['country_flag']==1){
        require_once(dirname(__FILE__).'/../lib/country.class.php');
        $cn = new mystat_country();
        $cn->setCacheDir(mystat_wordpress::getCacheDir());
        echo '<div class="country_flag">';
        foreach($country as $k=>$v){
          if(file_exists(mystat_wordpress::getCacheDir().'icon/flags/'.$k.'.png')){
            echo '<img src="'.mystat_wordpress::getCacheDir(true).'icon/flags/'.$k.'.png'.'" width="24px" height="24px" style="width:24px;height:24px;" title="'.addslashes($cn->getCountryByCode($k,strtoupper(substr(get_locale(),0,2)))).' - '.$v.'"/>';
          }
        }
        echo '</div>';
      }
      if($instance['user_info']==1){
        require_once(dirname(__FILE__).'/../lib/browscap.class.php');
        $browscap = new mystat_browscap();
        $browscap->setCacheDir(mystat_wordpress::getCacheDir());
        $br = $browscap->getBrowser($_SERVER['HTTP_USER_AGENT']);
        echo '<div class="user_info">';
          echo '<div class="os">';
            $name = trim(isset($br['Platform_Description'])?$br['Platform_Description']:'');
            $k = preg_replace('/_{2,}/','_',trim(str_replace(Array(' ','.','-','&',','),'_',strtolower($name)),'_'));
            if(preg_match('/^[A-z0-9_]*$/',$k) and strlen($k)>1 and file_exists(mystat_wordpress::getCacheDir().'icon/os/'.$k.'.png')){
              echo '<img src="'.mystat_wordpress::getCacheDir(true).'icon/os/'.$k.'.png'.'" width="32px" height="32px" style="width:32px;height:32px;vertical-align: bottom;" title="'.addslashes($name).'"/>';
            }
            echo ' <span class="osname">'.$name.'</span>'.(isset($br['Platform_Bits'])?' <span class="osbit">'.$br['Platform_Bits'].' '.mystat_wordpress::__('bits').'</span>':'');
          echo '</div>';
          echo '<div class="browser">';
            $name = trim(isset($br['Browser'])?$br['Browser']:'');
            $k = preg_replace('/_{2,}/','_',trim(str_replace(Array(' ','.','-','&',','),'_',strtolower($name)),'_'));
            if(preg_match('/^[A-z0-9_]*$/',$k) and strlen($k)>1 and file_exists(mystat_wordpress::getCacheDir().'icon/browser/'.$k.'.png')){
              echo '<img src="'.mystat_wordpress::getCacheDir(true).'icon/browser/'.$k.'.png'.'" width="32px" height="32px" style="width:32px;height:32px;vertical-align: bottom;" title="'.addslashes($name).'"/>';
            }
            echo ' <span class="browsername">'.$name.'</span>'.(isset($br['Version'])?' <span class="browserversion">'.$br['Version'].'</span>':'');
          echo '</div>';
          echo '<div class="ip">';
            echo '<span class="text">'.mystat_wordpress::__('IP address').'</span> <span class="ipaddress">'.(($_SERVER['REMOTE_ADDR']==$_SERVER['SERVER_ADDR'])?(isset($_SERVER['HTTP_X_REAL_IP'])?$_SERVER['HTTP_X_REAL_IP']:(isset($_SERVER['HTTP_X_FORWARDED_FOR'])?$_SERVER['HTTP_X_FORWARDED_FOR']:$_SERVER['REMOTE_ADDR'])):$_SERVER['REMOTE_ADDR']).'</span>';
          echo '</div>';
        echo '</div>';
      }
      if($instance['user_online']==1){
        $data = mystat_wordpress::getStatByPeriod(strtotime(date('Y-m-d 00:00:00',mystat_wordpress::getTime(false))),strtotime(date('Y-m-d 23:59:59',mystat_wordpress::getTime(false))));
        $online=0;$uniqonline=Array();
        foreach($data as $d){
          if($d->updated_at>mystat_wordpress::getTime(false)-(15*60)){
            if(!in_array($d->ip,$uniqonline)){
              if($this->isUser($d)){
                $online++;
              }
              $uniqonline[] = $d->ip;
            }
          }
        }
        echo '<div class="user_online">';
          echo '<div class="text">'.$instance['user_online_text'].'</div>';
          echo '<div class="count">'.$online.'</div>';
        echo '</div>';
      }
      if($instance['robot_online']==1){
        $data = mystat_wordpress::getStatByPeriod(strtotime(date('Y-m-d 00:00:00',mystat_wordpress::getTime(false))),strtotime(date('Y-m-d 23:59:59',mystat_wordpress::getTime(false))));
        $online=0;$uniqonline=Array();
        foreach($data as $d){
          if($d->updated_at>mystat_wordpress::getTime(false)-(15*60)){
            if(!in_array($d->ip,$uniqonline)){
              if(!$this->isUser($d)){
                $online++;
              }
              $uniqonline[] = $d->ip;
            }
          }
        }
        echo '<div class="robot_online">';
          echo '<div class="text">'.$instance['robot_online_text'].'</div>';
          echo '<div class="count">'.$online.'</div>';
        echo '</div>';
      }
      if($instance['user_graph']==1){
        $uuid = round(microtime(true)*10000);
        $runchart[] = 'viewChart'.$uuid.'();'
        ?>
        <div id="mystat_chart_<?php echo $uuid;?>"></div>
        <script type="text/javascript">
          function viewChart<?php echo $uuid;?>(){
            var data = new google.visualization.DataTable();
            data.addColumn('datetime', '');
            data.addColumn('number', "<?php echo addslashes(mystat_wordpress::__('Unique'));?>");
            data.addRows([<?php 
              $ret = Array();
              foreach($userbyday as $k=>$v){
                $ret[] = '[new Date('.strtotime($k.' 00:00:00').' * 1000),'.(int)$v.']';
              }
              echo join(',',$ret);
            ?>]);
            var options = {
              forceIFrame: true,
              legend: {
                position: 'labeled'
              },
              vAxis: {
                format: '#'
              },
              theme: 'maximized',
              dataOpacity: 0.9,
              focusTarget: 'category'
            };
            var chart = new google.visualization.ColumnChart(document.getElementById('mystat_chart_<?php echo $uuid;?>'));
            chart.draw(data, options);
          }
        </script>
        <?php
      }
      if($instance['user_count']==1){
        $cnt = 0;
        foreach($userbyday as $d=>$v){
          $cnt+=$v;
        }
        echo '<div class="user_count">';
          echo '<div class="text">'.$instance['user_count_text'].'</div>';
          echo '<div class="count">'.$cnt.'</div>';
        echo '</div>';
      }
      if(sizeof($runchart)>0){
        $uuid = round(microtime(true)*10000);
        ?>
        <?php if(mystat_wordpress::getOption('mystatproxygoogle','false')): ?>
          <script type="text/javascript" src="https://my-stat.com/google/loader.js"></script>
        <?php else: ?>
          <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
        <?php endif; ?>
        <script type="text/javascript">
          if(typeof google != 'undefined' && typeof google.visualization == 'undefined' && typeof viewChart != 'undefined'){
            google.charts.load('current',{'packages':['corechart','geochart'], 'language':'<?php echo strtolower(substr(get_locale(),0,2));?>'});
            google.charts.setOnLoadCallback(viewChart);
          }
          function runChart<?php echo $uuid;?>(){
            <?php echo join("\n",$runchart);?>
          }
        </script>
        <?php
      }
      echo $args['after_widget'];
    }

    function update( $new_instance, $old_instance ) {
      $instance = Array();
      $instance['title'] = strip_tags(isset($new_instance['title'])?$new_instance['title']:'');
      $instance['period'] = strip_tags(isset($new_instance['period'])?$new_instance['period']:'');
      $instance['country_map'] = isset($new_instance['country_map'])?(int)$new_instance['country_map']:0;
      $instance['country_top'] = isset($new_instance['country_top'])?(int)$new_instance['country_top']:0;
      $instance['country_flag'] = isset($new_instance['country_flag'])?(int)$new_instance['country_flag']:0;
      $instance['user_info'] = isset($new_instance['user_info'])?(int)$new_instance['user_info']:0;
      $instance['user_online'] = isset($new_instance['user_online'])?(int)$new_instance['user_online']:0;
      $instance['user_online_text'] = strip_tags(isset($new_instance['user_online_text'])?$new_instance['user_online_text']:'');
      $instance['robot_online'] = isset($new_instance['robot_online'])?(int)$new_instance['robot_online']:0;
      $instance['robot_online_text'] = strip_tags(isset($new_instance['robot_online_text'])?$new_instance['robot_online_text']:'');
      $instance['user_graph'] = isset($new_instance['user_graph'])?(int)$new_instance['user_graph']:0;
      $instance['user_count'] = isset($new_instance['user_count'])?(int)$new_instance['user_count']:0;
      $instance['user_count_text'] = strip_tags(isset($new_instance['user_count_text'])?$new_instance['user_count_text']:'');
      $instance['style'] = strip_tags(isset($new_instance['style'])?$new_instance['style']:'');
      return $instance;
    }

    function form($instance){
      $uuid = round(microtime(true)*10000);
  ?>
      <p>
        <label for="<?php echo $this->get_field_id('title'); ?>"><?php echo mystat_wordpress::__('Title');?>:</label>
        <input type="text" class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" value="<?php echo esc_attr(($instance and isset($instance['title']))?$instance['title']:mystat_wordpress::__('My Statistics')); ?>" />
      </p>
      <p>
        <label for="<?php echo $this->get_field_id('period'); ?>"><?php echo mystat_wordpress::__('Report display period');?>:</label>
        <select class="widefat" id="<?php echo $this->get_field_id('period'); ?>" name="<?php echo $this->get_field_name('period'); ?>">
          <option value="TD"<?php echo ($instance and isset($instance['period']) and $instance['period']=='TD')?' selected':'';?>><?php echo mystat_wordpress::__('Today');?></option>
          <option value="YD"<?php echo ($instance and isset($instance['period']) and $instance['period']=='YD')?' selected':'';?>><?php echo mystat_wordpress::__('Yesterday');?></option>
          <option value="LW"<?php echo ($instance and isset($instance['period']) and $instance['period']=='LW')?' selected':'';?>><?php echo mystat_wordpress::__('Last week (last 7 days)');?></option>
          <option value="LM"<?php echo ($instance and isset($instance['period']) and $instance['period']=='LM')?' selected':'';?>><?php echo mystat_wordpress::__('Last month (last 30 days)');?></option>
        </select>
      </p>
      <hr/>
      <p>
        <label for="<?php echo $this->get_field_id('country_map'); ?>"><?php echo mystat_wordpress::__('Map of visitors');?>:</label>
        <input type="checkbox" class="widefat" id="<?php echo $this->get_field_id('country_map'); ?>" name="<?php echo $this->get_field_name('country_map'); ?>" value="1"<?php echo ($instance and isset($instance['country_map']) and $instance['country_map']=='1')?' checked':'';?> />
      </p>
      <p>
        <label for="<?php echo $this->get_field_id('country_top'); ?>"><?php echo mystat_wordpress::__('Top 10 countries of visitors');?>:</label>
        <input type="checkbox" class="widefat" id="<?php echo $this->get_field_id('country_top'); ?>" name="<?php echo $this->get_field_name('country_top'); ?>" value="1"<?php echo ($instance and isset($instance['country_top']) and $instance['country_top']=='1')?' checked':'';?> />
      </p>
      <p>
        <label for="<?php echo $this->get_field_id('country_flag'); ?>"><?php echo mystat_wordpress::__('Flags of countries visitors');?>:</label>
        <input type="checkbox" class="widefat" id="<?php echo $this->get_field_id('country_flag'); ?>" name="<?php echo $this->get_field_name('country_flag'); ?>" value="1"<?php echo ($instance and isset($instance['country_flag']) and $instance['country_flag']=='1')?' checked':'';?> />
      </p>
      <hr/>
      <p>
        <label for="<?php echo $this->get_field_id('user_info'); ?>"><?php echo mystat_wordpress::__('Information about the visitor');?>:</label>
        <input type="checkbox" class="widefat" id="<?php echo $this->get_field_id('user_info'); ?>" name="<?php echo $this->get_field_name('user_info'); ?>" value="1"<?php echo ($instance and isset($instance['user_info']) and $instance['user_info']=='1')?' checked':'';?> />
      </p>
      <p>
        <label for="<?php echo $this->get_field_id('user_online'); ?>"><?php echo mystat_wordpress::__('Users visiting the site now');?>:</label>
        <input onclick="var el=jQuery('#user_online_text_<?php echo $uuid;?>');if(this.checked){el.show();}else{el.hide();}" type="checkbox" class="widefat" id="<?php echo $this->get_field_id('user_online'); ?>" name="<?php echo $this->get_field_name('user_online'); ?>" value="1"<?php echo ($instance and isset($instance['user_online']) and $instance['user_online']=='1')?' checked':'';?> />
      </p>
      <p id="user_online_text_<?php echo $uuid;?>" style="<?php echo ($instance and isset($instance['user_online']) and $instance['user_online']=='1')?'':'display:none;';?>padding-left: 25px;font-style: italic;">
        <label for="<?php echo $this->get_field_id('user_online_text'); ?>"><?php echo mystat_wordpress::__('Text label');?>:</label>
        <input type="text" class="widefat" id="<?php echo $this->get_field_id('user_online_text'); ?>" name="<?php echo $this->get_field_name('user_online_text'); ?>" value="<?php echo esc_attr(($instance and isset($instance['user_online_text']))?$instance['user_online_text']:mystat_wordpress::__('Users visiting the site now')); ?>" />
      </p>
      <p>
        <label for="<?php echo $this->get_field_id('robot_online'); ?>"><?php echo mystat_wordpress::__('Now your site is scanned (by robots or spiders)');?>:</label>
        <input onclick="var el=jQuery('#robot_online_text_<?php echo $uuid;?>');if(this.checked){el.show();}else{el.hide();}" type="checkbox" class="widefat" id="<?php echo $this->get_field_id('robot_online'); ?>" name="<?php echo $this->get_field_name('robot_online'); ?>" value="1"<?php echo ($instance and isset($instance['robot_online']) and $instance['robot_online']=='1')?' checked':'';?> />
      </p>
      <p id="robot_online_text_<?php echo $uuid;?>" style="<?php echo ($instance and isset($instance['robot_online']) and $instance['robot_online']=='1')?'':'display:none;';?>padding-left: 25px;font-style: italic;">
        <label for="<?php echo $this->get_field_id('robot_online_text'); ?>"><?php echo mystat_wordpress::__('Text label');?>:</label>
        <input type="text" class="widefat" id="<?php echo $this->get_field_id('robot_online_text'); ?>" name="<?php echo $this->get_field_name('robot_online_text'); ?>" value="<?php echo esc_attr(($instance and isset($instance['robot_online_text']))?$instance['robot_online_text']:mystat_wordpress::__('Now your site is scanned (by robots or spiders)')); ?>" />
      </p>
      <hr/>
      <p>
        <label for="<?php echo $this->get_field_id('user_graph'); ?>"><?php echo mystat_wordpress::__('Graph of visitors');?>:</label>
        <input type="checkbox" class="widefat" id="<?php echo $this->get_field_id('user_graph'); ?>" name="<?php echo $this->get_field_name('user_graph'); ?>" value="1"<?php echo ($instance and isset($instance['user_graph']) and $instance['user_graph']=='1')?' checked':'';?> />
      </p>
      <p>
        <label for="<?php echo $this->get_field_id('user_count'); ?>"><?php echo mystat_wordpress::__('Site traffic');?>:</label>
        <input onclick="var el=jQuery('#user_count_text_<?php echo $uuid;?>');if(this.checked){el.show();}else{el.hide();}" type="checkbox" class="widefat" id="<?php echo $this->get_field_id('user_count'); ?>" name="<?php echo $this->get_field_name('user_count'); ?>" value="1"<?php echo ($instance and isset($instance['user_count']) and $instance['user_count']=='1')?' checked':'';?> />
      </p>
      <p id="user_count_text_<?php echo $uuid;?>" style="<?php echo ($instance and isset($instance['user_count']) and $instance['user_count']=='1')?'':'display:none;';?>padding-left: 25px;font-style: italic;">
        <label for="<?php echo $this->get_field_id('user_count_text'); ?>"><?php echo mystat_wordpress::__('Text label');?>:</label>
        <input type="text" class="widefat" id="<?php echo $this->get_field_id('user_count_text'); ?>" name="<?php echo $this->get_field_name('user_count_text'); ?>" value="<?php echo esc_attr(($instance and isset($instance['user_count_text']))?$instance['user_count_text']:mystat_wordpress::__('Site traffic')); ?>" />
      </p>
      <hr/>
      <p>
        <label for="<?php echo $this->get_field_id('style'); ?>"><?php echo mystat_wordpress::__('Display style widget');?>:</label>
        <select class="widefat" id="<?php echo $this->get_field_id('style'); ?>" name="<?php echo $this->get_field_name('style'); ?>">
          <option value="NONE"<?php echo ($instance and isset($instance['style']) and $instance['style']=='NONE')?' selected':'';?>><?php echo mystat_wordpress::__('No style');?></option>
          <option value="DEFAULT"<?php echo ((!isset($instance) or !isset($instance['style'])) or ($instance and isset($instance['style']) and $instance['style']=='DEFAULT'))?' selected':'';?>><?php echo mystat_wordpress::__('Default style');?></option>
        </select>
      </p>
      <hr/>
  <?php
    }
  }

}