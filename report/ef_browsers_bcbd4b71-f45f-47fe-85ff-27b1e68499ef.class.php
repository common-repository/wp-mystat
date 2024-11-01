<?php
if(!defined('MYSTAT_VERSION')){
  throw new Exception('File not exist 404');
}

class mystat_browsers{
  
  protected $context;
  protected $param;

  public function __construct($context,$param){
    $this->context = $context;
    $this->param = $param;
  }

  public function getName(){
    return $this->context->__('Browsers');
  }

  public function getXML(){
    $data = $this->context->getStat();
    $period = $this->context->getPeriod();
    $uniquser = $ind = Array();
    $browserver = Array();
    $notset = 0;
    foreach($data as $d){
      if($this->context->isUser($d)){
        if(!in_array($d->ip,$uniquser)){
          if($d->browser!='' and $d->browser!='Default Browser'){
            if(!array_key_exists((!!$d->tor?'[TOR]':'').$d->browser,$ind)){
              $ind[(!!$d->tor?'[TOR]':'').$d->browser] = 1;
            }else{
              $ind[(!!$d->tor?'[TOR]':'').$d->browser]+= 1;
            }
            if(!isset($browserver[(!!$d->tor?'[TOR]':'').$d->browser])){$browserver[(!!$d->tor?'[TOR]':'').$d->browser] = Array();}
            if(!in_array($d->version,Array('','0','0.0'))){
              if(!array_key_exists($d->version,$browserver[(!!$d->tor?'[TOR]':'').$d->browser])){
                $browserver[(!!$d->tor?'[TOR]':'').$d->browser][$d->version] = 1;
              }else{
                $browserver[(!!$d->tor?'[TOR]':'').$d->browser][$d->version]+= 1;
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
        'BROWSER' => substr($title,0,5)=='[TOR]'?substr($title,5):$title,
        'COUNT' => $count,
        '@BROWSER' => Array(
          'flag' => $this->context->getBrowserFlag(substr($title,0,5)=='[TOR]'?'tor':$title),
          'tor' => substr($title,0,5)=='[TOR]'
        )
      );
      if(sizeof($browserver[$title])>0){
        arsort($browserver[$title]);
        $indicator[sizeof($indicator)-1]['VERSION'] = array_values($browserver[$title]);
        $ver = array_keys($browserver[$title]);
        foreach($ver as $v){
          $indicator[sizeof($indicator)-1]['@VERSION'][] = Array('number'=>$v);
        }
      }
    }
    $report = Array();
    $report['REPORT'] = Array(
      'TITLE' => $this->getName(),
      'SUBTITLE' => $this->context->__('Rating of browsers and their versions used by visitors'),
      'TRANSLATE' => Array(
        'NAME_BROWSER' => $this->context->__('Browser name'),
        'TOR_NETWORK' => $this->context->__('Tor network'),
        'UNIQ' => $this->context->__('Unique'),
        'VERSION' => $this->context->__('Version'),
        'COUNT_BROWSER' => $this->context->__('Total unique browsers'),
        'NOBROWSERDETECT' => $this->context->__('Total unidentified browsers')
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