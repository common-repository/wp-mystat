<?php
if(!defined('MYSTAT_VERSION')){
  throw new Exception('File not exist 404');
}


class mystat_country{
  
  protected $cachedir = '';
  protected $country = Array();

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
    $file = $this->loadFile('http://my-stat.com/update/country/'.strtolower($lang).'.dat');
    $f = fopen($this->getCacheDir().'translate.country.'.strtolower($lang).'.cache','w+');
    fwrite($f,$file);
    fclose($f);
  }

  protected function loadToCache($lang){
    if(!file_exists($this->getCacheDir().'translate.country.'.strtolower($lang).'.cache')){
      return false;
    }
    if(isset($this->country[strtoupper($lang)])){
      return false;
    }
    $line = file($this->getCacheDir().'translate.country.'.strtolower($lang).'.cache');
    $this->country[strtoupper($lang)] = Array();
    foreach($line as $l){
      $el = preg_split('/\:/',trim($l));
      $this->country[strtoupper($lang)][$el[0]] = $el[3];
    }
  }

  public function getCountryByCode($code,$lang='EN'){
    if(!file_exists($this->getCacheDir().'translate.country.en.cache')){
      $this->getFileToCache('en');
    }
    if(!file_exists($this->getCacheDir().'translate.country.'.strtolower($lang).'.cache')){
      $this->getFileToCache($lang);
    }
    $this->loadToCache('en');
    $this->loadToCache($lang);
    return isset($this->country[strtoupper($lang)][strtoupper($code)])?$this->country[strtoupper($lang)][strtoupper($code)]:(isset($this->country['EN'][strtoupper($code)])?$this->country['EN'][strtoupper($code)]:strtoupper($code));
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
