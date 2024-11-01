<?php
if(!defined('MYSTAT_VERSION')){
  throw new Exception('File not exist 404');
}

class mystat_settings{
  
  protected $context;
  protected $param;

  public function __construct($context,$param){
    $this->context = $context;
    $this->param = $param;
  }

  public function getName(){
    return $this->context->__('Settings');
  }

  function isShow(){
    return $this->context->getCurrentRole('ADMIN');
  }

  public function getXML(){
    $report = Array();
    $ip = $this->context->getIP();
    $report['REPORT'] = Array(
      'TITLE' => $this->getName(),
      'SUBTITLE' => $this->context->__('Configuring the plugin'),
      'TRANSLATE' => Array(
        'SETTINGS_TITLE' => $this->context->__('Settings of plugin'),
        'SAVE_PERIOD' => $this->context->__('Storage time for visitor data'),
        'MONTH' => $this->context->__('Month'),
        'QUARTER' => $this->context->__('Quarter'),
        'FOUR_MONTH' => $this->context->__('Four months'),
        'HALF_YEAR' => $this->context->__('Half-year'),
        'YEAR' => $this->context->__('Year'),
        'TWO_YEARS' => $this->context->__('Two years'),
        'PLUGINROLE' => $this->context->__('Minimum access permissions to display statistics'),
        'PLUGINROLE_ADMIN' => $this->context->__('Administrator'),
        'PLUGINROLE_EDITOR' => $this->context->__('Editor'),
        'PLUGINROLE_USER' => $this->context->__('User'),
        'ERROR_IP' => $this->context->__('Entered an incorrect IP address.'),
        'CONFIRM_CHANGEPERIOD' => $this->context->__('Chosen retention period is less than it was previously installed. This may result in the removal of obsolete data. Continue?'),
        'CONFIRM_DELETEIP' => $this->context->__('Delete the IP address?'),
        'NOIP_TITLE' => $this->context->__('Do not keep statistics from specified IP addresses'),
        'NOIP_ADDIP' => $this->context->__('Add IP address'),
        'NOIP_IP' => $this->context->__('IP address'),
        'NOIP_YOURIP' => $this->context->__('Your IP'),
        'NOIP_MASK' => $this->context->__('Network mask'),
        'NOIP_ADD' => $this->context->__('Add'),
        'DEFAULT_REPORT' => $this->context->__('Page opens by default'),
        'CLICK_EVENT' => $this->context->__('Collect click information'),
        'USE_PROXY' => $this->context->__('Use proxy server for graphics (for China users)'),
        'WP_PLACE' => $this->context->__('Place of insert JS code'),
        'WP_PLACE_FOOTER' => $this->context->__('In footer widget'),
        'WP_PLACE_SHUTDOWN' => $this->context->__('After render page'),
        'SAVE' => $this->context->__('Save')
      ),
      'BUTTON_HIDE' => Array('EXPORT'=>true,'PERIOD'=>true),
      'PARAMETRS' => Array(
        'IP' => $ip,
        'SAVE_PERIOD' => (int)$this->context->getOption('mystatcleanday',120),
        'PLUGINROLE' => (string)$this->context->getRole(),
        'PLUGINROLELIST' => Array('ELEMENT' => (array)$this->context->getRoleAccess()),
        'IPADRESS' => Array(),
        'DEFAULT_REPORT' => $this->context->getOption('mystatdefaultpage','dashboard'),
        'CLICK_EVENT' => $this->context->getOption('mystatclickevent','true'),
        'USE_PROXY' => $this->context->getOption('mystatproxygoogle','false'),
        'WP_PLACE' => $this->context->getOption('mystatwpplace','wp_footer'),
      )
    );
    $ips = (array)json_decode($this->context->getOption('mystatipnotsave','[]'));
    if(sizeof($ips)>0){
      $report['REPORT']['PARAMETRS']['IPADRESS']['IP'] = $ips;
    }

    $xml = new DOMDocument('1.0', 'UTF-8');
    $xml->formatOutput = true;
    $xml->preserveWhiteSpace = false;
    $this->context->xmlStructureFromArray($xml,$report);
    return $xml->saveXML();
  }

  public function getAjax(){
    $ret = Array('success'=>true);
    $this->context->setRole($this->param['role']);
    $this->context->setOption('mystatcleanday',(int)$this->param['period']);
    $this->context->setOption('mystatipnotsave',json_encode((array)$this->param['ip']));
    $this->context->setRoleAccess((array)$this->param['rolelist']);
    $this->context->setOption('mystatdefaultpage',(string)$this->param['defaultreport']);
    $this->context->setOption('mystatclickevent',$this->param['clickevent']=='true'?'true':'false');
    $this->context->setOption('mystatproxygoogle',$this->param['proxygoogle']=='true'?'true':'false');
    if($this->context->getDriver()->getName()=='wordpress'){
      $this->context->setOption('mystatwpplace',$this->param['wpplace']=='wp_footer'?'wp_footer':'shutdown');
    }
    return $ret;
  }

}