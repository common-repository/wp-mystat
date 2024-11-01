<?php
if(!defined('MYSTAT_VERSION')){
  throw new Exception('File not exist 404');
}

class mystat_visitorDevices{
  
  protected $context;
  protected $param;

  public function __construct($context,$param){
    $this->context = $context;
    $this->param = $param;
  }

  public function getName(){
    return $this->context->__('Visitor devices');
  }

  public function getXML(){
    $data = $this->context->getStat();
    $period = $this->context->getPeriod();
    $uniquser = $ind = Array();
    $notset = 0;
    foreach($data as $d){
      if($this->context->isUser($d)){
        if(!in_array($d->ip,$uniquser)){
          if($d->device=='' or $d->device=='unknown'){
            $notset++;
          }else{
            $key = trim(preg_replace('/general/i','',strpos($d->device_name,'unknown')===false?$d->device_name:$d->device));
            $ind[$key] = array_key_exists($key,$ind)?$ind[$key]+1:1;
          }
          $uniquser[] = $d->ip;
        }
      }
    }
    arsort($ind);
    $page = isset($this->param['page'])?(int)$this->param['page']:1;
    $perpage = 30;
    if($page<1){$page=1;}
    if($page>ceil(sizeof($ind)/$perpage)){$page=ceil(sizeof($ind)/$perpage);}
    $indicator = Array();
    foreach($ind as $title=>$count){
      $indicator[] = Array(
        'DEVICE' => $title,
        '@DEVICE' => Array('count'=>$count, 'flag'=>$this->context->getDeviceFlag($title))
      );
    }
    $report = Array();
    $report['REPORT'] = Array(
      'TITLE' => $this->getName(),
      'SUBTITLE' => $this->context->__('Devices from which visitors searched your site'),
      'TRANSLATE' => Array(
        'DEVICE_NAME' => $this->context->__('Device name'),
        'USER' => $this->context->__('Visitors'),
        'COUNT_DEVICE' => $this->context->__('Total unique devices'),
        'NODEVICEDETECT' => $this->context->__('Total unidentified devices')
      ),
      'INDICATORS' => Array(
        'CURRENT_PAGE' => $page,
        'PER_PAGE' => $perpage,
        'NOTSET' => $notset
      )
    );
    if(sizeof($indicator)>0){
      $report['REPORT']['INDICATORS']['INDICATOR'] = $indicator;
    }
    $xml = new DOMDocument('1.0', 'UTF-8');
    $xml->formatOutput = true;
    $xml->preserveWhiteSpace = false;
    $this->context->xmlStructureFromArray($xml,$report);
    return $xml->saveXML();
  }


}