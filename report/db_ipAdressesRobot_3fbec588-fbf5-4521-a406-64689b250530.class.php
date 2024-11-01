<?php
if(!defined('MYSTAT_VERSION')){
  throw new Exception('File not exist 404');
}

class mystat_ipAdressesRobot{
  
  protected $context;
  protected $param;

  public function __construct($context,$param){
    $this->context = $context;
    $this->param = $param;
  }

  public function getName(){
    return $this->context->__('IP addresses of robots and spiders');
  }

  public function getXML(){
    $data = $this->context->getStat();
    $period = $this->context->getPeriod();
    $ind = $ref = Array();
    foreach($data as $d){
      if(!$this->context->isUser($d)){
        if(!array_key_exists(long2ip($d->ip),$ind)){
          $ind[long2ip($d->ip)] = $d->count;
        }else{
          $ind[long2ip($d->ip)]+= $d->count;
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
        'IP' => $title,
        'COUNT' => $count
      );
    }
    $report = Array();
    $report['REPORT'] = Array(
      'TITLE' => $this->getName(),
      'SUBTITLE' => $this->context->__('Network addresses of robots and spiders'),
      'TRANSLATE' => Array(
        'IP' => $this->context->__('IP address'),
        'VIEW' => $this->context->__('Page views')
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

  public function getAjax(){
    $ret = Array('success'=>false);
    if(isset($this->param['ip']) and preg_match('/^(([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\.){3}([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])$/i',$this->param['ip'])){
      $ret['success'] = true;
      $ret['whois'] = $this->whois($this->param['ip']);
    }
    return $ret;
  }

  function whois($ip,$url='whois.arin.net',$ret=Array()){
    $sock = fsockopen($url, 43, $errno, $errstr);
    if(!$sock){
      return Array();
    }else{
      fputs ($sock, $ip."\r\n");
      while(!feof($sock)){
        $t = trim(fgets($sock, 128));
        preg_match("|ReferralServer:[\s]*whois://(.*)$|i", $t, $out);
        if(!empty($out[1])){
          fclose ($sock);
          return $this->whois($ip,$out[1],$ret);
        }
        if($t!='' and !in_array(substr($t,0,1),Array('%','/','#'))){
          $t = preg_split('/:/',$t);
          if(sizeof($t)>1){
            $name = trim(array_shift($t));
            $value = trim(join(':',$t));
            if(in_array($name,Array('created','last-modified','Updated','RegDate'))){
              $value = date('d-m-Y H:i:s',strtotime($value));
            }
            if(!in_array($name,Array('Comment'))){
              $ret[$name] = $value;
            }
          }
        }
      }
      fclose ($sock);
      return $ret;
    }
  }

}