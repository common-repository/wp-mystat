<?php
if(!defined('MYSTAT_VERSION')){
  throw new Exception('File not exist 404');
}

class mystat_link404{
  
  protected $context;
  protected $param;

  public function __construct($context,$param){
    $this->context = $context;
    $this->param = $param;
  }

  public function getName(){
    return $this->context->__('Links to 404 pages');
  }

  public function isShow(){
    return in_array($this->context->getDriver()->getName(),Array('joomla'))?false:true;
  }

  public function getXML(){
    $data = $this->context->getStat();
    $period = $this->context->getPeriod();
    $ind = $rest = $ref = Array();
    foreach($data as $d){
      if($d->is404 and $d->referer->url!=''){
        if(!isset($ind[$d->referer->url])){
          $ind[$d->referer->url]= Array($d->uri);
        }else{
          if(!in_array($d->uri,$ind[$d->referer->url])){
            $ind[$d->referer->url][] = $d->uri;
          }
        }
      }
    }
    $page = isset($this->param['page'])?(int)$this->param['page']:1;
    $perpage = 30;
    if($page<1){$page=1;}
    if($page>ceil(sizeof($ind)/$perpage)){$page=ceil(sizeof($ind)/$perpage);}
    $indicator = Array();
    foreach($ind as $title=>$count){
      $indicator[] = Array(
        'REFERRER' => $title
      );
      if(sizeof($count)>0){
        $indicator[sizeof($indicator)-1]['URI'] = $count;
      }
    }
    $report = Array();
    $report['REPORT'] = Array(
      'TITLE' => $this->getName(),
      'SUBTITLE' => $this->context->__('Pages referring to non-existent pages or files'),
      'TRANSLATE' => Array(
        'MAINPAGE' => $this->context->__('Main page'),
        'NOTFOUNDURI' => $this->context->__('Addresses of non-existent pages'),
        'REFERRER' => $this->context->__('Referring page'),
        'VIEW' => $this->context->__('Number of views'),
        'USERROBOT' => $this->context->__('Users / Robots'),
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