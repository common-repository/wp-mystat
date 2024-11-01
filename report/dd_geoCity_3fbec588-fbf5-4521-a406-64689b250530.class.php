<?php
if(!defined('MYSTAT_VERSION')){
  throw new Exception('File not exist 404');
}

class mystat_geoCity{
  
  protected $context;
  protected $param;

  public function __construct($context,$param){
    $this->context = $context;
    $this->param = $param;
  }

  public function getName(){
    return $this->context->__('Cities of visitors');
  }

  public function isShow(){
    return file_exists($this->context->getCacheDir().'geobase_v2.3.dat');
  }

  public function getXML(){
    $data = $this->context->getStat();
    $period = $this->context->getPeriod();
    $ind = $uniqhash = Array();
    $notset = 0;
    foreach($data as $d){
      if($this->context->isUser($d)){
        if($d->city!='' and $d->city!='-'){
          if(!in_array($d->ip,$uniqhash)){
            $ind[$d->country.'_'.$d->city] = (isset($ind[$d->city])?$ind[$d->city]:0)+1;
            $uniqhash[] = $d->ip;
          }
        }else{
          $notset+= 1;
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
      preg_match('/^([A-z]{2})_(.+)$/i',$title,$el);
      $indicator[] = Array(
        'CITY' => $el[2],
        '@CITY' => Array('count'=>$count, 'country'=>$el[1], 'flag'=>$this->context->getCountryFlag($el[1]), 'name'=>$this->context->getCountryName($el[1]), 'name_en'=>$this->context->getCountryName($el[1],'EN'))
      );
    }
    $report = Array();
    $report['REPORT'] = Array(
      'TITLE' => $this->getName(),
      'SUBTITLE' => $this->context->__('List of countries, from which your site is visited'),
      'TRANSLATE' => Array(
        'CITY' => $this->context->__('City'),
        'UNIQ' => $this->context->__('Unique visitors'),
        'NOCITYDETECT' => $this->context->__('Visitors with unidentified city')
      ),
      'INDICATORS' => Array(
        'NOTSET' => $notset,
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