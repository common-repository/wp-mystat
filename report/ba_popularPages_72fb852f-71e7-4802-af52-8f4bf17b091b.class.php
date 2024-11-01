<?php
if(!defined('MYSTAT_VERSION')){
  throw new Exception('File not exist 404');
}

class mystat_popularPages{
  
  protected $context;
  protected $param;

  public function __construct($context,$param){
    $this->context = $context;
    $this->param = $param;
  }

  public function getName(){
    return $this->context->__('Popular pages');
  }
  
  public function getXML(){
    $data = $this->context->getStat();
    $period = $this->context->getPeriod();
    $ind = Array();
    foreach($data as $d){
      if($this->context->isUser($d)){
        if(!isset($ind[$d->uri])){
          $ind[$d->uri]=$d->count;
        }else{
          $ind[$d->uri]+=$d->count;
        }
      }
    }
    arsort($ind);
    $page = isset($this->param['page'])?(int)$this->param['page']:1;
    $perpage = 30;
    if($page<1){$page=1;}
    if($page>ceil(sizeof($ind)/$perpage)){$page=ceil(sizeof($ind)/$perpage);}
    $indicator = Array();
    foreach($ind as $uri=>$count){
      $indicator[] = Array(
        'URI' => $uri,
        'COUNT' => $count
      );
    }
    $report = Array();
    $report['REPORT'] = Array(
      'TITLE' => $this->getName(),
      'SUBTITLE' => $this->context->__('Popular addresses of pages visited by users'),
      'TRANSLATE' => Array(
        'MAINPAGE' => $this->context->__('Main page'),
        'URI' => $this->context->__('Page address'),
        'VIEW' => $this->context->__('Number of views'),
        'TOTALREQUEST' => $this->context->__('Total number of page views'),
        'TOTALUNIQURI' => $this->context->__('Number of unique page addresses')
      ),
      'INDICATORS' => Array(
        'CURRENT_PAGE' => $page,
        'PER_PAGE' => $perpage
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