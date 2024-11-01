<?php
if(!defined('MYSTAT_VERSION')){
  throw new Exception('File not exist 404');
}

class mystat_acceptLanguage{
  
  protected $context;
  protected $param;

  public function __construct($context,$param){
    $this->context = $context;
    $this->param = $param;
  }

  public function getName(){
    return $this->context->__('Visitors\' system language');
  }

  public function getXML(){
    $data = $this->context->getStat();
    $period = $this->context->getPeriod();
    $uniquser = $ind = Array();
    $notset = 0;
    foreach($data as $d){
      if($this->context->isUser($d)){
        if(!in_array($d->ip,$uniquser)){
          if($d->lang!=''){
            if(!array_key_exists($d->lang,$ind)){
              $ind[$d->lang] = 1;
            }else{
              $ind[$d->lang]+= 1;
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
        'LANGUAGE' => $title,
        '@LANGUAGE' => Array('count'=>$count, 'flag'=>$this->context->getLanguageFlag($title), 'name'=>$this->context->getLanguageName($title), 'name_en'=>$this->context->getLanguageName($title,'EN'))
      );
    }
    $report = Array();
    $report['REPORT'] = Array(
      'TITLE' => $this->getName(),
      'SUBTITLE' => $this->context->__('Visitor\'s default browser language'),
      'TRANSLATE' => Array(
        'ACCEPT_LANGUAGE' => $this->context->__('Visitor\'s language'),
        'USER' => $this->context->__('Visitors'),
        'COUNT_LANG' => $this->context->__('Total unique languages'),
        'NOLANGDETECT' => $this->context->__('Total unidentified languages')
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