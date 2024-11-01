<?php
if(!defined('MYSTAT_VERSION')){
  throw new Exception('File not exist 404');
}

class mystat_robots{
  
  protected $context;
  protected $param;

  public function __construct($context,$param){
    $this->context = $context;
    $this->param = $param;
  }

  public function getName(){
    return $this->context->__('Robots and spiders');
  }

  public function getXML(){
    $data = $this->context->getStat();
    $period = $this->context->getPeriod();
    $uniquser = $ind = Array();
    $robotver = Array();
    $notset = 0;
    foreach($data as $d){
      if(!$this->context->isUser($d)){
        if(!in_array($d->ip,$uniquser)){
          if($d->browser!='' and $d->crawler==true){
            if(!array_key_exists($d->browser,$ind)){
              $ind[$d->browser] = 1;
            }else{
              $ind[$d->browser]+= 1;
            }
            if(!isset($robotver[$d->browser])){$robotver[$d->browser] = Array();}
            if(!in_array($d->version,Array('','0','0.0'))){
              if(!array_key_exists($d->version,$robotver[$d->browser])){
                $robotver[$d->browser][$d->version] = 1;
              }else{
                $robotver[$d->browser][$d->version]+= 1;
              }
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
        'ROBOT' => $title,
        'COUNT' => $count
      );
      if(sizeof($robotver[$title])>0){
        arsort($robotver[$title]);
        $indicator[sizeof($indicator)-1]['VERSION'] = array_values($robotver[$title]);
        $ver = array_keys($robotver[$title]);
        foreach($ver as $v){
          $indicator[sizeof($indicator)-1]['@VERSION'][] = Array('number'=>$v);
        }
      }
    }
    $report = Array();
    $report['REPORT'] = Array(
      'TITLE' => $this->getName(),
      'SUBTITLE' => $this->context->__('Robots and spiders visiting your site'),
      'TRANSLATE' => Array(
        'NAME_ROBOT' => $this->context->__('Robot name'),
        'UNIQ' => $this->context->__('Unique'),
        'VERSION' => $this->context->__('Version'),
        'COUNT_ROBOT' => $this->context->__('Total unique robots or spiders'),
        'NOROBOTDETECT' => $this->context->__('Total masking robots')
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