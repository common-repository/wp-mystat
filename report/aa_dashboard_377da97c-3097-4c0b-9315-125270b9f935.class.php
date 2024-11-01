<?php
if(!defined('MYSTAT_VERSION')){
  throw new Exception('File not exist 404');
}

class mystat_dashboard{
  
  protected $context;

  public function __construct($context,$param){
    $this->context = $context;
  }

  public function getName(){
    return $this->context->__('Summary statistics');
  }

  public function getXML(){
    $data = $this->context->getStat();
    $period = $this->context->getPeriod();
    $ind = Array();
    $country = Array();
    $country_notset = 0;
    $referer = Array(
      'search' => 0,
      'mail' => 0,
      'social' => 0,
      'other' => 0,
      'direct' => 0
    );
    $mobile = Array('desktop'=>0,'mobile'=>0,'tablet'=>0);
    if(date('Y-m-d',$period['start'])==date('Y-m-d',$period['end'])){
      $uniqhash = Array();
      for($i=0;$i<24;$i++){$ind[$i] = Array('name'=>sprintf($this->context->__('from %d to %d hours'),$i,($i+1)),'timestamp' => strtotime(date('Y-m-d',$period['start']).' '.$i.':0:0'),'uniq'=>0,'count'=>0,'countrobot'=>0,'robot'=>0);}
      foreach($data as $d){
        if(!in_array($d->ip,$uniqhash)){
          if(!$this->context->isUser($d)){
            $ind[(int)date('G',$d->created_at)]['robot']+=1;
          }else{
            $ind[(int)date('G',$d->created_at)]['uniq']+=1;
            if($d->country!='AA' and $d->country!=''){
              if(!isset($country[$d->country])){$country[$d->country] = 0;}
              $country[$d->country]++;
            }else{
              $country_notset++;
            }
            if($d->mobile and $d->tablet){
              $mobile['tablet']++;
            }elseif($d->mobile){
              $mobile['mobile']++;
            }else{
              $mobile['desktop']++;
            }
            if($d->referer['url']=='' or !preg_match('/^http(s)?\:\/\/(.*)/i',$d->referer['url'])){
              $referer['direct']++;
            }elseif($d->referer['type']=='search'){
              $referer['search']++;
            }elseif($d->referer['type']=='mail'){
              $referer['mail']++;
            }elseif($d->referer['type']=='social'){
              $referer['social']++;
            }else{
              $referer['other']++;
            }
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
            if($d->country!='AA' and $d->country!=''){
              if(!isset($country[$d->country])){$country[$d->country] = 0;}
              $country[$d->country]++;
            }else{
              $country_notset++;
            }
            if($d->mobile and $d->tablet){
              $mobile['tablet']++;
            }elseif($d->mobile){
              $mobile['mobile']++;
            }else{
              $mobile['desktop']++;
            }
            if($d->referer->url=='' or !preg_match('/^http(s)?\:\/\/(.*)/i',$d->referer->url)){
              $referer['direct']++;
            }elseif($d->referer->type=='search'){
              $referer['search']++;
            }elseif($d->referer->type=='mail'){
              $referer['mail']++;
            }elseif($d->referer->type=='social'){
              $referer['social']++;
            }else{
              $referer['other']++;
            }
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
    $nt = $na = Array();
    foreach($country as $k=>$v){
      $nt[] = $k;
      $na[] = Array('count'=>$v, 'name'=>$this->context->getCountryName($k), 'name_en'=>$this->context->getCountryName($k,'EN'));
    }
    $data = $this->context->getStat(Array('start'=>strtotime(date('Y-m-d 00:00:00',$this->context->getDriver()->getTime(false))),'end'=>strtotime(date('Y-m-d 23:59:59',$this->context->getDriver()->getTime(false)))));
    $online = $onlinerobot = 0;
    $visitor_today = 0;
    $visitor_yesterday = 0;
    $robot_today = 0;
    $robot_yesterday = 0;
    $uniqonline = $uniqtoday = $uniqyesterday = Array();
    foreach($data as $d){
      if($d->updated_at>$this->context->getDriver()->getTime(false)-(15*60)){
        if(!in_array($d->ip,$uniqonline)){
          if(!$this->context->isUser($d)){
            $onlinerobot++;
          }else{
            $online++;
          }
          $uniqonline[] = $d->ip;
        }
      }
      if(!in_array($d->ip,$uniqtoday)){
        if($this->context->isUser($d)){
          $visitor_today++;
        }else{
          $robot_today++;
        }
        $uniqtoday[] = $d->ip;
      }
    }
    $data = $this->context->getStat(Array('start'=>strtotime(date('Y-m-d 00:00:00',$this->context->getDriver()->getTime(false)).' -1 days'),'end'=>strtotime(date('Y-m-d 23:59:59',$this->context->getDriver()->getTime(false)).' -1 days')));
    foreach($data as $d){
      if(!in_array($d->ip,$uniqyesterday)){
        if($this->context->isUser($d)){
          $visitor_yesterday++;
        }else{
          $robot_yesterday++;
        }
        $uniqyesterday[] = $d->ip;
      }
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
    $country = Array(
      'NOTSET' => $country_notset
    );
    if(sizeof($nt)>0){
      $country['COUNTRY'] = $nt;
      $country['@COUNTRY'] = $na;
    }
    $report['REPORT'] = Array(
      'TITLE' => $this->getName(),
      'SUBTITLE' => $this->context->__('Short summary on various indicators of your site visiting statistics'),
      'TRANSLATE' => Array(
        'ONLINE' => $this->context->__('Users visiting the site now'),
        'ROBOTONLINE' => $this->context->__('Now your site is scanned (by robots or spiders)'),
        'DETAIL' => $this->context->__('Details'),
        'VIEW' => $this->context->__('Page views'),
        'UNIQ' => $this->context->__('Unique'),
        'USER' => $this->context->__('Visitors'),
        'ROBOT' => $this->context->__('Robots and spiders'),
        'GEOGRAPHI' => $this->context->__('Geography of visitors'),
        'UNIQTODAY' => $this->context->__('Unique visitors today'),
        'UNIQYESTERDAY' => $this->context->__('Unique visitors yesterday'),
        'ROBOTTODAY' => $this->context->__('Unique robots or spiders today'),
        'ROBOTYESTERDAY' => $this->context->__('Unique robots or spiders yesterday'),
        'REFERER' => $this->context->__('Sources of visitors'),
        'PLATFORM' =>  $this->context->__('User platforms'),
        'PLATFORM_MOBILE' =>  $this->context->__('Mobile devices'),
        'PLATFORM_TABLET' =>  $this->context->__('Tablet computers'),
        'PLATFORM_DESKTOP' =>  $this->context->__('Desktop computers'),
        'REFERER_SEARCH' =>  $this->context->__('Search engines'),
        'REFERER_SOCIAL' =>  $this->context->__('Social networks'),
        'REFERER_EMAIL' =>  $this->context->__('Mail services'),
        'REFERER_OTHER' =>  $this->context->__('Other sites'),
        'REFERER_DIRECT' =>  $this->context->__('Direct clickthroughs')
      ),
      'INDICATORS' => Array(),
      'COUNTRIS' => $country,
      'ONLINE' => $online,
      'ONLINEROBOT' => $onlinerobot,
      'VISITTODAY' => $visitor_today,
      'VISITYESTERDAY' => $visitor_yesterday,
      'ROBOTTODAY' => $robot_today,
      'ROBOTYESTERDAY' => $robot_yesterday,
      'PLATFORMS' => Array(
        'MOBILE' => $mobile['mobile'],
        'TABLET' => $mobile['tablet'],
        'DESKTOP' => $mobile['desktop']
      ),
      'REFERER' => Array(
        'SEARCH' => $referer['search'],
        'EMAIL' => $referer['mail'],
        'SOCIAL' => $referer['social'],
        'OTHER' => $referer['other'],
        'DIRECT' => $referer['direct']
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