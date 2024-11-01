<?php
if(!defined('MYSTAT_VERSION')){
  throw new Exception('File not exist 404');
}

class mystat_pageViewPerUser{
  
  protected $context;

  public function __construct($context,$param){
    $this->context = $context;
  }

  public function getName(){
    return $this->context->__('Page views per visitor');
  }

  public function getXML(){
    $data = $this->context->getStat();
    $period = $this->context->getPeriod();
    $ind = Array();
    $uniqhash = Array();
    foreach($data as $d){
      if($this->context->isUser($d)){
        if(!array_key_exists((string)$d->ip,$uniqhash)){
          $uniqhash[(string)$d->ip] = $d->count;
        }else{
          $uniqhash[(string)$d->ip]+= $d->count;
        }
      }
    }
    asort($uniqhash);
    $all_sum=0;
    $cnt = Array(0,0,0,0,0,0,0,0,0,0);
    foreach($uniqhash as $ip=>$count){
      if($count==1){$cnt[0]++;}
      elseif($count==2){$cnt[1]++;}
      elseif($count==3){$cnt[2]++;}
      elseif($count==4){$cnt[3]++;}
      elseif($count==5){$cnt[4]++;}
      elseif($count>5 and $count<10){$cnt[5]++;}
      elseif($count>=10 and $count<=20){$cnt[6]++;}
      elseif($count>20 and $count<=50){$cnt[7]++;}
      elseif($count>50 and $count<=100){$cnt[8]++;}
      elseif($count>100){$cnt[9]++;};
      $all_sum+=$count;
    }
    $report = Array();
    $indicator = Array();
    $indicator[] = Array(
      'NAME' => $this->context->__('1 page'),
      'COUNT' => $cnt[0]
    );
    $indicator[] = Array(
      'NAME' => $this->context->__('2 pages'),
      'COUNT' => $cnt[1]
    );
    $indicator[] = Array(
      'NAME' => $this->context->__('3 pages'),
      'COUNT' => $cnt[2]
    );
    $indicator[] = Array(
      'NAME' => $this->context->__('4 pages'),
      'COUNT' => $cnt[3]
    );
    $indicator[] = Array(
      'NAME' => $this->context->__('5 pages'),
      'COUNT' => $cnt[4]
    );
    $indicator[] = Array(
      'NAME' => $this->context->__('from 6 to 9 pages'),
      'COUNT' => $cnt[5]
    );
    $indicator[] = Array(
      'NAME' => $this->context->__('from 10 to 20 pages'),
      'COUNT' => $cnt[6]
    );
    $indicator[] = Array(
      'NAME' => $this->context->__('from 21 to 50 pages'),
      'COUNT' => $cnt[7]
    );
    $indicator[] = Array(
      'NAME' => $this->context->__('from 51 to 100 pages'),
      'COUNT' => $cnt[8]
    );
    $indicator[] = Array(
      'NAME' => $this->context->__('over 100 pages'),
      'COUNT' => $cnt[9]
    );
    $report['REPORT'] = Array(
      'TITLE' => $this->getName(),
      'SUBTITLE' => $this->context->__('Number of users that viewed the stated number of pages'),
      'TRANSLATE' => Array(
        'VIEW' => $this->context->__('Page views'),
        'UNIQ' => $this->context->__('Unique visitors'),
        'AVERAGE' => $this->context->__('Average number of views per user')
      ),
      'INDICATORS' => Array(
        'TOTAL_VIEW' => $all_sum,
        'ALL_USER' => sizeof($uniqhash)
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