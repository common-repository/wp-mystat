<?php
if(!defined('MYSTAT_VERSION')){
  throw new Exception('File not exist 404');
}

class mystat_screenResolution{
  
  protected $context;
  protected $param;

  public function __construct($context,$param){
    $this->context = $context;
    $this->param = $param;
  }

  public function getName(){
    return $this->context->__('Screen resolutions');
  }

  public function getXML(){
    $data = $this->context->getStat();
    $period = $this->context->getPeriod();
    $uniquser = $ind = Array();
    $undefine = 0;
    foreach($data as $d){
      if($this->context->isUser($d)){
        if(!in_array($d->ip,$uniquser)){
          if((int)$d->screen->width>0 and (int)$d->screen->height>0){
            if(!array_key_exists($d->screen->width.'x'.$d->screen->height,$ind)){
              $ind[$d->screen->width.'x'.$d->screen->height] = 1;
            }else{
              $ind[$d->screen->width.'x'.$d->screen->height]+= 1;
            }
          }else{
            $undefine++;
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
      $scr = preg_split('/x/i',$title);
      $indicator[] = Array(
        'WIDTH' => $scr[0],
        'HEIGHT' => $scr[1],
        'COUNT' => $count
      );
    }
    $report = Array();
    $report['REPORT'] = Array(
      'TITLE' => $this->getName(),
      'SUBTITLE' => $this->context->__('Width and height of the visitor\'s screens in pixels'),
      'TRANSLATE' => Array(
        'RESOLUTION' => $this->context->__('Screen sizes in pixels'),
        'UNIQ' => $this->context->__('Unique'),
        'COUNT_RESOLUTION' => $this->context->__('Total unique screen resolutions'),
        'MAX_RESOLUTION' => $this->context->__('Maximum screen resolution'),
        'MIN_RESOLUTION' => $this->context->__('Minimum screen resolution'),
        'NORESOLUTIONDETECT' => $this->context->__('Total unidentified resolutions')
      ),
      'INDICATORS' => Array(
        'CURRENT_PAGE' => $page,
        'PER_PAGE' => $perpage,
        'NOTSET' => $undefine
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