<?php
if(!defined('MYSTAT_VERSION')){
  throw new Exception('File not exist 404');
}

class mystat_timeLoad{
  
  protected $context;

  public function __construct($context,$param){
    $this->context = $context;
  }

  public function getName(){
    return $this->context->__('Time of downloading the pages');
  }
  
  public function getXML(){
    $data = $this->context->getStat();
    $period = $this->context->getPeriod();
    $all_sum=0;
    $cnt = Array(0,0,0,0,0,0,0,0,0,0,0,0);
    foreach($data as $d){
      if($this->context->isUser($d) and $d->time_load>0){
        if($d->time_load<=1){$cnt[0]+=$d->count;}
        elseif($d->time_load>1 and $d->time_load<=2){$cnt[1]+=$d->count;}
        elseif($d->time_load>2 and $d->time_load<=3){$cnt[2]+=$d->count;}
        elseif($d->time_load>3 and $d->time_load<=4){$cnt[3]+=$d->count;}
        elseif($d->time_load>4 and $d->time_load<=5){$cnt[4]+=$d->count;}
        elseif($d->time_load>5 and $d->time_load<=8){$cnt[5]+=$d->count;}
        elseif($d->time_load>8 and $d->time_load<=10){$cnt[6]+=$d->count;}
        elseif($d->time_load>10 and $d->time_load<=15){$cnt[7]+=$d->count;}
        elseif($d->time_load>15 and $d->time_load<=30){$cnt[8]+=$d->count;}
        elseif($d->time_load>30 and $d->time_load<=45){$cnt[9]+=$d->count;}
        elseif($d->time_load>45 and $d->time_load<=60){$cnt[10]+=$d->count;}
        elseif($d->time_load>60){$cnt[11]+=$d->count;}
        $all_sum+=$d->time_load;
      }
    }
    $report = Array();
    $indicator = Array();
    $indicator[] = Array(
      'NAME' => $this->context->__('Less than one second'),
      'COUNT' => $cnt[0]
    );
    $indicator[] = Array(
      'NAME' => '1-2 '.$this->context->__('seconds '),
      'COUNT' => $cnt[1]
    );
    $indicator[] = Array(
      'NAME' => '2-3 '.$this->context->__('seconds '),
      'COUNT' => $cnt[2]
    );
    $indicator[] = Array(
      'NAME' => '3-4 '.$this->context->__('seconds '),
      'COUNT' => $cnt[3]
    );
    $indicator[] = Array(
      'NAME' => '4-5 '.$this->context->__('seconds'),
      'COUNT' => $cnt[4]
    );
    $indicator[] = Array(
      'NAME' => '5-8 '.$this->context->__('seconds'),
      'COUNT' => $cnt[5]
    );
    $indicator[] = Array(
      'NAME' => '8-10 '.$this->context->__('seconds'),
      'COUNT' => $cnt[6]
    );
    $indicator[] = Array(
      'NAME' => '10-15 '.$this->context->__('seconds'),
      'COUNT' => $cnt[7]
    );
    $indicator[] = Array(
      'NAME' => '15-30 '.$this->context->__('seconds'),
      'COUNT' => $cnt[8]
    );
    $indicator[] = Array(
      'NAME' => '30-45 '.$this->context->__('seconds'),
      'COUNT' => $cnt[9]
    );
    $indicator[] = Array(
      'NAME' => '45-60 '.$this->context->__('seconds'),
      'COUNT' => $cnt[10]
    );
    $indicator[] = Array(
      'NAME' => $this->context->__('Over a minute'),
      'COUNT' => $cnt[11]
    );
    $report['REPORT'] = Array(
      'TITLE' => $this->getName(),
      'SUBTITLE' => $this->context->__('Time of downloading the page on the client\'s side'),
      'TRANSLATE' => Array(
        'LOADPAGE' => $this->context->__('Time of downloading the pages'),
        'VIEW' => $this->context->__('Number of views'),
        'AVERAGE' => $this->context->__('Average time of downloading the pages')
      ),
      'INDICATORS' => Array(
        'TOTAL_TIME' => $all_sum
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