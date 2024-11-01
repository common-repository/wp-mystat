<?php
if(!defined('MYSTAT_VERSION')){
  throw new Exception('File not exist 404');
}

class mystat_siteUsage{
  
  protected $context;

  public function __construct($context,$param){
    $this->context = $context;
  }

  public function getName(){
    return $this->context->__('Site traffic');
  }

  public function getXML(){
    $data = $this->context->getStat();
    $period = $this->context->getPeriod();
    $ind = Array();
    if(date('Y-m-d',$period['start'])==date('Y-m-d',$period['end'])){
      $uniqhash = Array();
      for($i=0;$i<24;$i++){$ind[$i] = Array('name'=>sprintf($this->context->__('from %d to %d hours'),$i,($i+1)),'timestamp' => strtotime(date('Y-m-d',$period['start']).' '.$i.':0:0'),'uniq'=>0,'count'=>0,'countrobot'=>0,'robot'=>0);}
      foreach($data as $d){
        if(!in_array($d->ip,$uniqhash)){
          if(!$this->context->isUser($d)){
            $ind[(int)date('G',$d->created_at)]['robot']+=1;
          }else{
            $ind[(int)date('G',$d->created_at)]['uniq']+=1;
          }
          $uniqhash[] = $d->ip;
        }
        if(!$this->context->isUser($d)){
          $ind[(int)date('G',$d->created_at)]['countrobot']+=$d->count;
        }else{
          $ind[(int)date('G',$d->created_at)]['count']+=$d->count;
        }
      }
    }else{
      $dayofweek = Array(
        $this->context->__('Sunday'),
        $this->context->__('Monday'),
        $this->context->__('Tuesday'),
        $this->context->__('Wednesday'),
        $this->context->__('Thursday'),
        $this->context->__('Friday'),
        $this->context->__('Saturday')
      );
      $datediff = floor($period['end']/(60*60*24)) - floor($period['start']/(60*60*24));
      for($i=0;$i<=$datediff;$i++){
        $ind[date('Y-m-d',strtotime(date('Y-m-d',$period['start']).' '.date('H:i:s',strtotime((time()+$this->context->getDriver()->getGMT())*60*60)).' +'.$i.' days'))] = Array(
          'name'=>sprintf('%s, %s',date($this->context->__('m-d, Y'),strtotime(date('Y-m-d',$period['start']).' '.date('H:i:s',strtotime((time()+$this->context->getDriver()->getGMT())*60*60)).' +'.$i.' days')),$dayofweek[(int)date('w',strtotime(date('Y-m-d',$period['start']).' '.date('H:i:s',strtotime((time()+$this->context->getDriver()->getGMT())*60*60)).' +'.$i.' days'))]),
          'timestamp' => strtotime(date('Y-m-d',$period['start']).' '.date('H:i:s',strtotime((time()+$this->context->getDriver()->getGMT())*60*60)).' +'.$i.' days'),
          'uniq'=>0,
          'count'=>0,
          'countrobot'=>0,
          'robot'=>0,
          'holiday' => in_array((int)date('w',strtotime(date('Y-m-d',$period['start']).' '.date('H:i:s',strtotime((time()+$this->context->getDriver()->getGMT())*60*60)).' +'.$i.' days')),Array(0,6))?true:false
        );
      }
      $prev = false;
      $uniqhash = Array();
      foreach($data as $d){
        if($prev==false or $prev != date('Y-m-d',$d->created_at)){
          $prev = date('Y-m-d',$d->created_at);
          $uniqhash = Array();
        }
        if(!in_array($d->ip,$uniqhash)){
          if(!$this->context->isUser($d)){
            $ind[date('Y-m-d',$d->created_at)]['robot']+=1;
          }else{
            $ind[date('Y-m-d',$d->created_at)]['uniq']+=1;
          }
          $uniqhash[] = $d->ip;
        }
        if(!$this->context->isUser($d)){
          $ind[date('Y-m-d',$d->created_at)]['countrobot']+=$d->count;
        }else{
          $ind[date('Y-m-d',$d->created_at)]['count']+=$d->count;
        }
      }
      $ind = array_reverse($ind,true);
    }
    $report = Array();
    $indicator = Array();
    foreach($ind as $in){
      $indicator[] = Array(
        'NAME' => $in['name'],
        'TIMESTAMP' => $in['timestamp'],
        'HOLIDAY' => (isset($in['holiday']) and $in['holiday'])?true:false,
        'USER' => Array(
          'UNIQ' => $in['uniq'],
          'VIEW' => $in['count']
        ),
        'ROBOT' => Array(
          'UNIQ' => $in['robot'],
          'VIEW' => $in['countrobot']
        )
      );
    }
    $report['REPORT'] = Array(
      'TITLE' => $this->getName(),
      'SUBTITLE' => $this->context->__('Information on visits to your site'),
      'TRANSLATE' => Array(
        'VIEW' => $this->context->__('Page views'),
        'UNIQ' => $this->context->__('Unique'),
        'USER' => $this->context->__('Visitors'),
        'ROBOT' => $this->context->__('Robots and spiders'),
        'AVERAGE' => date('Y-m-d',$period['start'])==date('Y-m-d',$period['end'])?$this->context->__('Average per hour'):$this->context->__('Average per day'),
        'TOTAL' => $this->context->__('Total'),
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


}