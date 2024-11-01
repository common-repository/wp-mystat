<?php
if(!defined('MYSTAT_VERSION')){
  throw new Exception('File not exist 404');
}

class mystat_operatingSystem{
  
  protected $context;
  protected $param;

  public function __construct($context,$param){
    $this->context = $context;
    $this->param = $param;
  }

  public function getName(){
    return $this->context->__('Operating systems');
  }

  public function getXML(){
    $data = $this->context->getStat();
    $period = $this->context->getPeriod();
    $uniquser = $ind = Array();
    $osver = $osbit = Array();
    $notset = 0;
    foreach($data as $d){
      if($this->context->isUser($d)){
        if(!in_array($d->ip,$uniquser)){
          if($d->osname!='' and $d->osname!='unknown'){
            if(!array_key_exists($d->osname,$ind)){
              $ind[$d->osname] = 1;
            }else{
              $ind[$d->osname]+= 1;
            }
            if(!isset($osver[$d->osname])){$osver[$d->osname] = Array();}
            if($d->osver=='unknown'){$d->osver=$this->context->__('Unidentified');}
            if(!in_array($d->osver,Array('','0','0.0'))){
              if(!array_key_exists($d->osver,$osver[$d->osname])){
                $osver[$d->osname][$d->osver] = 1;
              }else{
                $osver[$d->osname][$d->osver]+= 1;
              }
            }
            if(!isset($osbit[$d->osname])){$osbit[$d->osname] = Array();}
            if(!array_key_exists($d->osbit,$osbit[$d->osname])){
              $osbit[$d->osname][$d->osbit] = 1;
            }else{
              $osbit[$d->osname][$d->osbit]+= 1;
            }
          }else{
            $notset++;
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
      $indicator[] = Array(
        'BROWSER' => $title,
        'COUNT' => $count,
        '@BROWSER' => Array(
          'flag' => $this->context->getOSFlag($title)
        )
      );
      if(sizeof($osver[$title])>0){
        arsort($osver[$title]);
        $indicator[sizeof($indicator)-1]['VERSION'] = array_values($osver[$title]);
        $ver = array_keys($osver[$title]);
        foreach($ver as $v){
          $indicator[sizeof($indicator)-1]['@VERSION'][] = Array('number'=>$v);
        }
      }
      if(sizeof($osbit[$title])>0){
        arsort($osbit[$title]);
        $indicator[sizeof($indicator)-1]['BITS'] = array_values($osbit[$title]);
        $ver = array_keys($osbit[$title]);
        foreach($ver as $v){
          $indicator[sizeof($indicator)-1]['@BITS'][] = Array('number'=>$v);
        }
      }
    }
    $report = Array();
    $report['REPORT'] = Array(
      'TITLE' => $this->getName(),
      'SUBTITLE' => $this->context->__('Rating of operating systems, their versions and bit-counts'),
      'TRANSLATE' => Array(
        'NAME_OS' => $this->context->__('Name of the operating system'),
        'UNIQ' => $this->context->__('Unique'),
        'VERSION' => $this->context->__('Version'),
        'PLATFORM' => $this->context->__('System bit-count'),
        'BITS' => $this->context->__('bits'),
        'COUNT_OS' => $this->context->__('Total unique operating systems'),
        'NOOSDETECT' => $this->context->__('Total unidentified operating systems')
      ),
      'INDICATORS' => Array(
        'CURRENT_PAGE' => $page,
        'PER_PAGE' => $perpage,
        'NOTSET' => $notset
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