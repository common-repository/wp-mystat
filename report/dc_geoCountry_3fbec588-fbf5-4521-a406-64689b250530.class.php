<?php
if(!defined('MYSTAT_VERSION')){
  throw new Exception('File not exist 404');
}

class mystat_geoCountry{
  
  protected $context;
  protected $param;

  public function __construct($context,$param){
    $this->context = $context;
    $this->param = $param;
  }

  public function getName(){
    return $this->context->__('Countries of visitors');
  }

  public function getXML(){
    $data = $this->context->getStat();
    $period = $this->context->getPeriod();
    $ind = $uniqhash = Array();
    $notset = 0;
    foreach($data as $d){
      if($this->context->isUser($d)){
        if($d->country!='' and $d->country!='AA'){
          if(!in_array($d->ip,$uniqhash)){
            $ind[$d->country] = (isset($ind[$d->country])?$ind[$d->country]:0)+1;
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
      $indicator[] = Array(
        'COUNTRY' => $title,
        '@COUNTRY' => Array('count'=>$count, 'flag'=>$this->context->getCountryFlag($title), 'name'=>$this->context->getCountryName($title), 'name_en'=>$this->context->getCountryName($title,'EN'))
      );
    }
    $report = Array();
    $report['REPORT'] = Array(
      'TITLE' => $this->getName(),
      'SUBTITLE' => $this->context->__('List of countries, from which your site is visited'),
      'TRANSLATE' => Array(
        'COUNTRY' => $this->context->__('Country'),
        'UNIQ' => $this->context->__('Unique visitors'),
        'NOCONTRYDETECT' => $this->context->__('Visitors with unidentified country')
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