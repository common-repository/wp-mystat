<?php
if(!defined('MYSTAT_VERSION')){
  throw new Exception('File not exist 404');
}

class mystat_socialNetwork{
  
  protected $context;
  protected $param;

  public function __construct($context,$param){
    $this->context = $context;
    $this->param = $param;
  }

  public function getName(){
    return $this->context->__('Social networks');
  }

  public function getXML(){
    $data = $this->context->getStat();
    $period = $this->context->getPeriod();
    $ind = $ref = Array();
    foreach($data as $d){
      if($d->referer->type=='social'){
        if(!array_key_exists($d->referer->name,$ind)){
          $ind[$d->referer->name] = 1;
          $uri[$d->referer->name] = Array($d->uri);
        }else{
          $ind[$d->referer->name]+= 1;
          if(!in_array($d->uri,$uri[$d->referer->name])){
            $uri[$d->referer->name][] = $d->uri;
          }
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
        'ENGINE' => $title,
        'COUNT' => $count
      );
    }
    $report = Array();
    $report['REPORT'] = Array(
      'TITLE' => $this->getName(),
      'SUBTITLE' => $this->context->__('List of social media, from which the users clicked through to your site'),
      'TRANSLATE' => Array(
        'NAME' => $this->context->__('Name'),
        'VISIT' => $this->context->__('Clickthroughs')
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