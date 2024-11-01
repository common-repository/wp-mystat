<?php
if(!defined('MYSTAT_VERSION')){
  throw new Exception('File not exist 404');
}

class mystat_domainNames{
  
  protected $context;
  protected $param;

  public function __construct($context,$param){
    $this->context = $context;
    $this->param = $param;
  }

  public function getName(){
    return $this->context->__('Domain names');
  }

  public function getXML(){
    $data = $this->context->getStat();
    $ind = Array();
    $urobot = $uuser = Array();
    foreach($data as $d){
      if(!$this->context->isUser($d)){
        if(!array_key_exists($d->host,$ind)){
          $ind[$d->host] = Array(
            'robot' =>Array(
              'count' => Array(
                'with' => $d->www?$d->count:0,
                'out' => $d->www?0:$d->count
              ),
              'uniq' => Array(
                'with' => $d->www?1:0,
                'out' => $d->www?0:1
              )
            ),
            'user' => Array(
              'count' => Array(
                'with' => 0,
                'out' => 0
              ),
              'uniq' => Array(
                'with' => 0,
                'out' => 0
              )
            )
          );
          $urobot[$d->host][] = $d->ip;
        }else{
          if(!array_key_exists($d->host,$urobot) or !in_array($d->ip,$urobot[$d->host])){
            $ind[$d->host]['robot']['uniq']['with']+= $d->www?1:0;
            $ind[$d->host]['robot']['uniq']['out']+= $d->www?0:1;
            $urobot[$d->host][] = $d->ip;
          }
          $ind[$d->host]['robot']['count']['with']+= $d->www?$d->count:0;
          $ind[$d->host]['robot']['count']['out']+= $d->www?0:$d->ount;
        }
      }else{
        if(!array_key_exists($d->host,$ind)){
          $ind[$d->host] = Array(
            'user' =>Array(
              'count' => Array(
                'with' => $d->www?$d->count:0,
                'out' => $d->www?0:$d->count
              ),
              'uniq' => Array(
                'with' => $d->www?1:0,
                'out' => $d->www?0:1
              )
            ),
            'robot' => Array(
              'count' => Array(
                'with' => 0,
                'out' => 0
              ),
              'uniq' => Array(
                'with' => 0,
                'out' => 0
              )
            )
          );
          $uuser[$d->host][] = $d->ip;
        }else{
          if(!array_key_exists($d->host,$uuser) or !in_array($d->ip,$uuser[$d->host])){
            $ind[$d->host]['user']['uniq']['with']+= $d->www?1:0;
            $ind[$d->host]['user']['uniq']['out']+= $d->www?0:1;
            $uuser[$d->host][] = $d->ip;
          }
          $ind[$d->host]['user']['count']['with']+= $d->www?$d->count:0;
          $ind[$d->host]['user']['count']['out']+= $d->www?0:$d->count;
        }
      }
    }
    foreach($ind as $host=>$el){
      $indicator[] = Array(
        'HOST' => $host,
        'USER' => Array(
          'UNIQ' => $el['user']['uniq']['out'],
          'COUNT' => $el['user']['count']['out']
        ),
        'ROBOT' => Array(
          'UNIQ' => $el['robot']['uniq']['out'],
          'COUNT' => $el['robot']['count']['out']
        )
      );
      $indicator[] = Array(
        'HOST' => 'www.'.$host,
        'USER' => Array(
          'UNIQ' => $el['user']['uniq']['with'],
          'COUNT' => $el['user']['count']['with']
        ),
        'ROBOT' => Array(
          'UNIQ' => $el['robot']['uniq']['with'],
          'COUNT' => $el['robot']['count']['with']
        )
      );
    }
    $report = Array();
    $report['REPORT'] = Array(
      'TITLE' => $this->getName(),
      'SUBTITLE' => $this->context->__('Visits and scans by domains'),
      'TRANSLATE' => Array(
        'HOST' => $this->context->__('Domain name'),
        'VIEW' => $this->context->__('Page views'),
        'UNIQ' => $this->context->__('Unique'),
        'USER' => $this->context->__('Visitors'),
        'ROBOT' => $this->context->__('Robots and spiders'),
      ),
      'INDICATORS' => Array(
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