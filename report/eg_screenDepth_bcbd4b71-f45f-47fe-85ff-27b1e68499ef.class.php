<?php
if(!defined('MYSTAT_VERSION')){
  throw new Exception('File not exist 404');
}

class mystat_screenDepth{
  
  protected $context;
  protected $param;

  public function __construct($context,$param){
    $this->context = $context;
    $this->param = $param;
  }

  public function getName(){
    return $this->context->__('Screen color depth');
  }

  public function getXML(){
    $data = $this->context->getStat();
    $period = $this->context->getPeriod();
    $uniquser = $ind = Array();
    foreach($data as $d){
      if($this->context->isUser($d)){
        if(!in_array($d->ip,$uniquser) and $d->screen->depth>0){
          if(!array_key_exists($d->screen->depth,$ind)){
            $ind[$d->screen->depth] = 1;
          }else{
            $ind[$d->screen->depth]+= 1;
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
      $color = '';
      switch($title){
        case 32:
          $color = $this->context->__('16.777.216 colour variations and 256 transparency gradations');
          break;
        default:
          $color = number_format(pow(2,$title),0,'.',' ').' '.$this->context->__('colour variations');
          break;
      }
      $indicator[] = Array(
        'DEPTH' => $title,
        'COLOR' => $color,
        'COUNT' => $count
      );
    }
    $report = Array();
    $report['REPORT'] = Array(
      'TITLE' => $this->getName(),
      'SUBTITLE' => $this->context->__('Memory size in bits assigned for storing the colour of one pixel and the real number of displayed colours and shades'),
      'TRANSLATE' => Array(
        'DEPTH' => $this->context->__('Pixel color depth on the screen'),
        'UNIQ' => $this->context->__('Unique'),
        'BPP' => $this->context->__('bits per pixel'),
        'COUNT_DEPTH' => $this->context->__('Total unique colour resolutions of the screen'),
        'MAX_DEPTH' => $this->context->__('Maximum screen color depth'),
        'MIN_DEPTH' => $this->context->__('Minimum screen color depth')
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