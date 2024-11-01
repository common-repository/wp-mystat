<?php
if(!defined('MYSTAT_VERSION')){
  throw new Exception('File not exist 404');
}

class mystat_searchQuery{
  
  protected $context;
  protected $param;

  public function __construct($context,$param){
    $this->context = $context;
    $this->param = $param;
  }

  public function getName(){
    return $this->context->__('Search phrases');
  }

  public function getXML(){
    $data = $this->context->getStat();
    $period = $this->context->getPeriod();
    $indicator = Array();
    $notdetect = 0;
    foreach($data as $d){
      if($d->referer->type=='search'){
        if($d->referer->query==''){
          $notdetect++;
        }else{
          $indicator[] = Array(
            'QUERY' => $d->referer->query,
            'URI' => $d->uri,
            'ENGINE' => $d->referer->name,
            'DATE' => date($this->context->__('m-d, Y'),$d->created_at)
          );
        }
      }
    }
    $indicator = array_reverse($indicator,true);
    $page = isset($this->param['page'])?(int)$this->param['page']:1;
    $perpage = 30;
    if($page<1){$page=1;}
    if($page>ceil(sizeof($indicator)/$perpage)){$page=ceil(sizeof($indicator)/$perpage);}
    $report = Array();
    $report['REPORT'] = Array(
      'TITLE' => $this->getName(),
      'SUBTITLE' => $this->context->__('Search phrases, by which your site has been found'),
      'TRANSLATE' => Array(
        'QUERY' => $this->context->__('Search phrase'),
        'DETECTQUERY' => $this->context->__('Total search phrases'),
        'NOTDETECTQUERY' => $this->context->__('Unidentified search phrases'),
        'PAGE_FOUND' => $this->context->__('Page found')
      ),
      'INDICATORS' => Array(
        'NOT_DETECTED' => $notdetect,
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