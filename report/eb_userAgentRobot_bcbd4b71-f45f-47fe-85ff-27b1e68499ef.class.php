<?php
if(!defined('MYSTAT_VERSION')){
  throw new Exception('File not exist 404');
}

class mystat_userAgentRobot{
  
  protected $context;
  protected $param;

  public function __construct($context,$param){
    $this->context = $context;
    $this->param = $param;
  }

  public function getName(){
    return $this->context->__('User-Agent robots and spiders');
  }

  public function getXML(){
    $data = $this->context->getStat();
    $period = $this->context->getPeriod();
    $uniquser = $ind = Array();
    $notset = 0;
    foreach($data as $d){
      if(!$this->context->isUser($d)){
        if(!in_array($d->ip,$uniquser)){
          if(trim($d->ua)!=''){
            if(!array_key_exists($d->ua,$ind)){
              $ind[$d->ua] = 1;
            }else{
              $ind[$d->ua]+= 1;
            }
          }else{
            $notset++;
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
        'USERAGENT' => $title,
        'COUNT' => $count
      );
    }
    $report = Array();
    $report['REPORT'] = Array(
      'TITLE' => $this->getName(),
      'SUBTITLE' => $this->context->__('Identification line sent by robots or spiders'),
      'TRANSLATE' => Array(
        'USER_AGENT' => $this->context->__('User-Agent line'),
        'ROBOT' => $this->context->__('Robots and spiders'),
        'COUNT_UA' => $this->context->__('Total unique User-Agents'),
        'NOUADETECT' => $this->context->__('Total unidentified User-Agents')
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