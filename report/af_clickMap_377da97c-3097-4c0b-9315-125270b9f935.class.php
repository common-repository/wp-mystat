<?php
if(!defined('MYSTAT_VERSION')){
  throw new Exception('File not exist 404');
}

class mystat_clickmap{
  
  protected $context;

  public function __construct($context,$param){
    $this->context = $context;
  }

  public function getName(){
    return $this->context->__('Click-through map');
  }
  
  function isShow(){
    return $this->context->getOption('mystatclickevent','true')=='true';
  }

  public function getXML(){
    $data = $this->context->getClickStat();
    $period = $this->context->getPeriod();
    $report = Array();
    $report['REPORT'] = Array(
      'TITLE' => $this->getName(),
      'SUBTITLE' => $this->context->__('Time of downloading the page on the client\'s side'),
      'TRANSLATE' => Array(
        'LOADPAGE' => $this->context->__('Time of downloading the pages'),
        'VIEW' => $this->context->__('Number of views'),
        'AVERAGE' => $this->context->__('Average time of downloading the pages')
      ),
      'INDICATORS' => Array(
        'TOTAL_TIME' => $all_sum
      )
    );
    $xml = new DOMDocument('1.0', 'UTF-8');
    $xml->formatOutput = true;
    $xml->preserveWhiteSpace = false;
    $this->context->xmlStructureFromArray($xml,$report);
    return $xml->saveXML();
  }


}