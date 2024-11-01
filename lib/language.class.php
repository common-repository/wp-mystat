<?php
if(!defined('MYSTAT_VERSION')){
  throw new Exception('File not exist 404');
}


class mystat_language{
  
  protected $cachedir = '';
  protected $language = Array();

  public function getCacheDir(){
    return $this->cachedir;
  }

  public function setCacheDir($cache=''){
    if(file_exists($cache) and is_writable($cache)){
      $this->cachedir = $cache!=''?rtrim($cache,'/').'/':'';
    }
    return $this;
  }

  protected function getFileToCache($lang){
    $file = $this->loadFile('http://my-stat.com/update/language/'.strtolower($lang).'.dat');
    $f = fopen($this->getCacheDir().'translate.language.'.strtolower($lang).'.cache','w+');
    fwrite($f,$file);
    fclose($f);
  }

  protected function loadToCache($lang){
    if(!file_exists($this->getCacheDir().'translate.language.'.strtolower($lang).'.cache')){
      return false;
    }
    if(isset($this->language[strtoupper($lang)])){
      return false;
    }
    $line = file($this->getCacheDir().'translate.language.'.strtolower($lang).'.cache');
    $this->language[strtoupper($lang)] = Array();
    foreach($line as $l){
      $el = preg_split('/\:/',trim($l));
      $this->language[strtoupper($lang)][$el[0]] = $el[1];
    }
  }

  public function getLanguageByCode($code,$lang='EN'){
    if(!file_exists($this->getCacheDir().'translate.language.en.cache')){
      $this->getFileToCache('en');
    }
    if(!file_exists($this->getCacheDir().'translate.language.'.strtolower($lang).'.cache')){
      $this->getFileToCache($lang);
    }
    $this->loadToCache('en');
    $this->loadToCache($lang);
    return isset($this->language[strtoupper($lang)][strtoupper($code)])?$this->language[strtoupper($lang)][strtoupper($code)]:(isset($this->language['EN'][strtoupper($code)])?$this->language['EN'][strtoupper($code)]:strtoupper($code));
  }

  protected function loadFile($file){
    $content = @file_get_contents($file); 
    if($content===false or $content===null){
      $ch = curl_init();
  	  curl_setopt($ch, CURLOPT_URL, $file);
  	  curl_setopt($ch, CURLOPT_HEADER, 0);
  	  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  	  $content = curl_exec($ch);
  	  curl_close($ch);
    }
    return $content;
  }

}
