<?php
if(!defined('MYSTAT_VERSION')){
  throw new Exception('File not exist 404');
}

class mystat_popular404{
  
  protected $context;
  protected $param;

  public function __construct($context,$param){
    $this->context = $context;
    $this->param = $param;
  }

  public function isShow(){
    return in_array($this->context->getDriver()->getName(),Array('joomla'))?false:true;
  }

  public function getName(){
    return $this->context->__('Popular error 404 pages');
  }

  public function getXML(){
    $data = $this->context->getStat();
    $period = $this->context->getPeriod();
    $ind = $rest = $ref = Array();
    foreach($data as $d){
      if($d->is404){
        if($this->context->isUser($d)){
          if(!isset($ind[$d->uri])){
            $rest[$d->uri]= Array('robot'=>$d->count,'user'=>0);
            $ind[$d->uri]= $d->count;
          }else{
            $ind[$d->uri]+=$d->count;
            $rest[$d->uri]['robot']+=$d->count;
          }
        }else{
          if(!isset($ind[$d->uri])){
            $rest[$d->uri]= Array('robot'=>0,'user'=>$d->count);
            $ind[$d->uri]= $d->count;
          }else{
            $ind[$d->uri]+=$d->count;
            $rest[$d->uri]['user']+=$d->count;
          }
        }
        if(!isset($ref[$d->uri])){
          $ref[$d->uri] = Array();
        }
        if($d->referer['url']!='' and !in_array($d->referer['url'],$ref[$d->uri])){
          $ref[$d->uri][] = $d->referer['url'];
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
        'URI' => $title,
        'COUNT' => $count,
        'USER' => $rest[$title]['user'],
        'ROBOT' => $rest[$title]['robot']
      );
      if(sizeof($ref[$title])>0){
        $indicator[sizeof($indicator)-1]['REFERRER'] = $ref[$title];
      }
    }
    $report = Array();
    $report['REPORT'] = Array(
      'TITLE' => $this->getName(),
      'SUBTITLE' => $this->context->__('Popular non-existent pages visited by users or robots'),
      'TRANSLATE' => Array(
        'MAINPAGE' => $this->context->__('Main page'),
        'URI' => $this->context->__('Page address'),
        'REFERRER' => $this->context->__('Referring pages'),
        'VIEW' => $this->context->__('Number of views'),
        'USERROBOT' => $this->context->__('Users / Robots'),
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