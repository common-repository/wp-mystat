<?php
if(!defined('MYSTAT_VERSION')){
  throw new Exception('File not exist 404');
}

class mystat_referrer{
  
  protected $context;
  protected $param;

  public function __construct($context,$param){
    $this->context = $context;
    $this->param = $param;
  }

  public function getName(){
    return $this->context->__('Referring addresses');
  }

  public function getXML(){
    $data = $this->context->getStat();
    $period = $this->context->getPeriod();
    $uniquser = $ind = $ref = $uri = Array();
    $direct = 0;
    foreach($data as $d){
      if(preg_match('/^http(s)?\:\/\/(.*)/i',$d->referer->url)){
        if(!array_key_exists($d->referer->url,$ind)){
          $ind[$d->referer->url] = $d->count;
          if($this->context->isUser($d)){
            $ref[$d->referer->url] = Array('user'=>1,'robot'=>0);
          }else{
            $ref[$d->referer->url] = Array('user'=>0,'robot'=>1);
          }
          $uri[$d->referer->url] = Array($d->uri);
        }else{
          $ind[$d->referer->url]+= $d->count;
          if($this->context->isUser($d)){
            $ref[$d->referer->url]['user']+= 1;
          }else{
            $ref[$d->referer->url]['robot']+= 1;
          }
          if(!in_array($d->uri,$uri[$d->referer->url])){
            $uri[$d->referer->url][] = $d->uri;
          }
        }
      }else{
        if($this->context->isUser($d)){
          if(!in_array($d->ip,$uniquser)){
            $uniquser[] = $d->ip;
            $direct++;
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
        'REFERRER' => $title,
        'COUNT' => $count,
        'USER' => $ref[$title]['user'],
        'ROBOT' => $ref[$title]['robot']
      );
      if(sizeof($uri[$title])>0){
        asort($uri[$title]);
        $indicator[sizeof($indicator)-1]['URI'] = $uri[$title];
      }
    }
    $report = Array();
    $report['REPORT'] = Array(
      'TITLE' => $this->getName(),
      'SUBTITLE' => $this->context->__('Rating of popular page addresses, from which the users clicked through to your site'),
      'TRANSLATE' => Array(
        'MAINPAGE' => $this->context->__('Main page'),
        'URI' => $this->context->__('Page address'),
        'SITELINK' => $this->context->__('Clickthrough pages'),
        'USER' => $this->context->__('Visitors'),
        'VIEW' => $this->context->__('Number of views'),
        'DIRECTVISIT' => $this->context->__('Direct visits'),
        'REFERRERVISIT' => $this->context->__('Clickthroughs from sites')
      ),
      'INDICATORS' => Array(
        'DIRECT_VISIT' => $direct,
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