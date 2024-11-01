<?php
if(!defined('MYSTAT_VERSION')){
  throw new Exception('File not exist 404');
}

class mystat_refDomain{
  
  protected $context;
  protected $param;

  public function __construct($context,$param){
    $this->context = $context;
    $this->param = $param;
  }

  public function getName(){
    return $this->context->__('Referring domains');
  }

  public function getXML(){
    $data = $this->context->getStat();
    $period = $this->context->getPeriod();
    $ind = $ref = $uri = Array();
    foreach($data as $d){
      if($this->context->isUser($d)){
        if(preg_match('/^http(s)?\:\/\/(.*)/i',$d->referer->url)){
          preg_match("/(^http[s]?:\/\/)?(www\.)?.*?([^\/]+)/i",$d->referer->url, $matches);
          $host = $matches[3];
          if(!array_key_exists($host,$ind)){
            $ind[$host] = $d->count;
            if($this->context->isUser($d)){
              $ref[$host] = Array('user'=>1,'robot'=>0);
            }else{
              $ref[$host] = Array('user'=>0,'robot'=>1);
            }
            $uri[$host] = Array($d->uri);
          }else{
            $ind[$host]+= $d->count;
            if($this->context->isUser($d)){
              $ref[$host]['user']+= 1;
            }else{
              $ref[$host]['robot']+= 1;
            }
            if(!in_array($d->uri,$uri[$host])){
              $uri[$host][] = $d->uri;
            }
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
        'HOST' => $title,
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
      'SUBTITLE' => $this->context->__('Rating of popular domains, from which the users clicked through to your site'),
      'TRANSLATE' => Array(
        'MAINPAGE' => $this->context->__('Main page'),
        'HOST' => $this->context->__('Domain name'),
        'SITELINK' => $this->context->__('Clickthrough pages'),
        'VIEW' => $this->context->__('Number of views'),
        'USER' => $this->context->__('Visitors'),
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