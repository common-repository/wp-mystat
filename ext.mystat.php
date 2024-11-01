<?php
if(!defined('BASEPATH')){
	throw new Exception('File not exist 404');
}

class Mystat_ext{

  var $settings = array();
  var $name = 'mySTAT';
  var $description = 'Site Visitor Statistics';
  var $version = MYSTAT_VERSION;
  var $settings_exist = 'y';
  var $docs_url = 'http://my-stat.com/documentation-eng';
  var $mystat;

  function __construct($settings=''){
    $this->settings = $settings;
    $this->mystat = new myStat();
  }

  function settings(){
    ob_start();
    $this->mystat->run('adminpanel');
  	$mystat = ob_get_contents();
		ob_end_clean();
    ee('CP/Alert')->makeInline('shared-form')
      ->asSuccess()
      ->cannotClose()
      ->addToBody($mystat)
      ->now();
    return Array();
  }

  function activate_extension(){
    $this->mystat->run('install');
    return true;
  }

  function update_extension($current = ''){
    $this->mystat->run('update');
    return true;
  }

  function disable_extension(){
    $this->mystat->run('uninstall');
    return true;
  }

  function addCodeSniff($final_template, $is_partial, $site_id){
    if(ee()->uri->config->_global_vars['template_type']=='404'){
      ob_start();
      $this->mystat->run((ee()->uri->segment(1)=='mystat' and ee()->uri->segment(2)=='ajax')?'ajax':'404');
    	$mystat = ob_get_contents();
  		ob_end_clean();
      return (ee()->uri->segment(1)=='mystat' and ee()->uri->segment(2)=='ajax')?$mystat:$final_template;
    }
    ob_start();
    if(!$is_partial){
      $this->mystat->run('code');
    }
  	$mystat = ob_get_contents();
		ob_end_clean();
    if(($pos=stripos($final_template,'</body>'))!==false){
      $final_template = substr($final_template,0,$pos).$mystat.substr($final_template,$pos);
    }elseif(($pos=stripos($final_template,'{html_close}'))!==false){
      $final_template = substr($final_template,0,$pos).$mystat.substr($final_template,$pos);
    }else{
      $final_template.= $mystat;
    }
    return $final_template;
  }
  
  function addMenuItem(){
    $name = strtolower(ee()->config->item('deft_lang'));
    if(!file_exists(dirname(__FILE__).'/language/'.$name.'/mystat_lang.php')){
      $name = 'english';
    }
    if(!file_exists(dirname(__FILE__).'/language/'.$name.'/mystat_lang.php')){
      $menu = 'My Statistics';
    }else{
      include_once(dirname(__FILE__).'/language/'.$name.'/mystat_lang.php');
      $menu = $lang['MY_STATISTICS'];
    }
    $url = isset($this->settings['link'])?$this->settings['link']:'admin.php?/cp/addons/settings/mystat';
    return '$(\'.nav-main-author\').append(\'<a href="'.$url.'">'.$menu.'</a>\');';
  }
  
}