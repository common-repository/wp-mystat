<?php
if(!defined('MYSTAT_VERSION')){
  throw new Exception('File not exist 404');
}

class mystat_defaultpage{
  
  protected $context;
  protected $param;

  public function __construct($context,$param){
    $this->context = $context;
    $this->param = $param;
  }

  public function getXML(){
    $param = isset($this->param['code'][2])?(array)json_decode($this->param['code'][2]):Array();
    $report = Array();
    $report['REPORT'] = Array(
      'TRANSLATE' => Array(
        'ACCESSDENY' => $this->context->__('Access limited'),
        'CODEFAIL' => $this->context->__('Detailed statistics is available only to the full version users.'),
        'BUYFULL' => $this->context->__('PURCHASE FULL VERSION'),
        'ENTERCODE' => $this->context->__('ENTER PURCHASE CODE'),
        'OR' => $this->context->__('or'),
        'DELETEDOMAIN' => $this->context->__('Domain to be deleted'),
        'BUYCODE' => $this->context->__('Purchase code'),
        'CHECKBUTTON' => $this->context->__('Check code'),
        'FAILCODE' => $this->context->__('The code you entered is invalid!'),
        'CODEFIND' => $this->context->__('How do I find out my purchase code?'),
        'DATAEXPIRE' => $this->context->__('License validity term expired on "{date}". You need to repurchase the full version.'),
        'DELETEDOMAIN' => $this->context->__('Delete domain'),
        'MAXDOMAIN' => $this->context->__('Your license allows for installation on no more than "{max}" site(s). To include this site into the licensed list, please, enter the number of valid purchase code and state the site, the license to which will no longer apply.')
      ),
      'CODE' => $this->param['code'][0],
      'MAXLICENSE' => isset($this->param['code'][1])?$this->param['code'][1]:1,
      'PARAMS' => Array(
        'PARAM' => $param
      )
    );
    $xml = new DOMDocument('1.0', 'UTF-8');
    $xml->formatOutput = true;
    $xml->preserveWhiteSpace = false;
    $this->context->xmlStructureFromArray($xml,$report);
    return $xml->saveXML();
  }

  public function getAjax(){
    $ret = Array('success'=>false);
    if(isset($this->param['uuid']) and preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',$this->param['uuid'])){
      $code = $this->context->isAs($this->param['uuid'],((isset($this->param['domain']) and $this->param['domain']!='')?$this->param['domain']:''));
      $code = preg_split('/:/',$code);
      $ret['code'] = $code[0];
      $ret['maxlicense'] = isset($code[1])?$code[1]:1;
      $ret['param'] = isset($code[2])?(array)json_decode($code[2]):Array();
      $ret['success'] = true;
      if(in_array($code[0],Array(base64_decode('T0s='),base64_decode('Q0hBTkdFRE9NQUlO')))){
        $this->context->saveAs($this->param['uuid']);
      }elseif($code[0]==base64_decode('RkFJTA==')){
        $ret['success'] = false;
      }
    }
    return $ret;
  }

}