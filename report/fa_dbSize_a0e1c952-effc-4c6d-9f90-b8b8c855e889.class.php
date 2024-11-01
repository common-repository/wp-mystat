<?php
if(!defined('MYSTAT_VERSION')){
  throw new Exception('File not exist 404');
}

class mystat_dbSize{
  
  protected $context;

  public function __construct($context,$param){
    $this->context = $context;
  }

  public function getName(){
    return $this->context->__('Reporting database size');
  }

  public function getXML(){
    $data = $this->context->getDbSize();
    $period = $this->context->getPeriod();
    $ind = Array();
    $dayofweek = Array(
      $this->context->__('Sunday'),
      $this->context->__('Monday'),
      $this->context->__('Tuesday'),
      $this->context->__('Wednesday'),
      $this->context->__('Thursday'),
      $this->context->__('Friday'),
      $this->context->__('Saturday')
    );
    foreach($data as $d){
      $ind[date('Y-m-d',strtotime($d['date']))] = Array(
        'name'=>sprintf('%s, %s',date($this->context->__('m-d, Y'),strtotime($d['date'])),$dayofweek[(int)date('w',strtotime($d['date']))]),
        'count' => $d['size'],
        'text' => $this->formatSize($d['size']),
        'timestamp' => strtotime($d['date']),
        'holiday' => in_array((int)date('w',strtotime($d['date'])),Array(0,6))?true:false
      );
    }
    $ind = array_reverse($ind,true);
    $report = Array();
    $indicator = Array();
    foreach($ind as $in){
      $indicator[] = Array(
        'DATE' => $in['name'],
        'HOLIDAY' => (isset($in['holiday']) and $in['holiday'])?true:false,
        'NAME' => $in['text'],
        'TIMESTAMP' => $in['timestamp'],
        'COUNT' => $in['count']
      );
    }
    $report['REPORT'] = Array(
      'TITLE' => $this->getName(),
      'SUBTITLE' => $this->context->__('Size of data used for storing the site visiting statistics'),
      'TRANSLATE' => Array(
        'BYTE' => $this->context->__('Bytes'),
      ),
      'INDICATORS' => Array()
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

  private function formatSize($size,$sh=false){
    if($size <= 0){return 0;}
    $short = Array('Bytes','KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB','BB','GeB');
    $sizes = array(
      $this->context->__('Bytes'),
      $this->context->__('Kilobytes'),
      $this->context->__('Megabytes'),
      $this->context->__('Gigabytes'),
      $this->context->__('Terabytes'),
      $this->context->__('Petabytes'),
      $this->context->__('Exabytes'),
      $this->context->__('Zettabytes'),
      $this->context->__('Yottabytes'),
      $this->context->__('Brontobytes'),
      $this->context->__('Geopbytes')
    );  
    return (number_format(round($size/pow(1024, ($i = floor(log($size, 1024)))), 2),2,'.',' ') . ' ' . ($sh?$short[$i]:$sizes[$i]));
  }


}