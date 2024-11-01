<?php
if(!defined('MYSTAT_VERSION')){
  throw new Exception('File not exist 404');
}


class mystat_referer{
  
  private $file = 'referer.dat';
  private $dir = false;
  private $host = Array();

  public function setCache($dir){
    if(file_exists($dir)){
      $this->dir = $dir;
    }
    return $this;
  }

  public function setFile($file='referer.dat'){
    if(file_exists($this->dir.'/'.$file)){
      $this->file = $file;
    }
    return $this;
  }

  public function isNeedUpdate(){
    if($this->dir===false){return false;}
    $md5 = $this->loadFile('http://my-stat.com/update/referer.dat.md5');
    if(strlen($md5)==32 and (!file_exists($this->dir.'/'.$this->file) or $md5!=md5_file($this->dir.'/'.$this->file))){
      return true;
    }
    return false;
  }

  public function update(){
    if($this->isNeedUpdate()){
      $md5 = $this->loadFile('http://my-stat.com/update/referer.dat.md5');
      $file = $this->loadFile('http://my-stat.com/update/referer.dat');
      if($md5 != md5($file)){return false;}
      if(file_exists($this->dir.'/'.$this->file)){
        unlink($this->dir.'/'.$this->file);
      }
      $f = fopen($this->dir.'/'.$this->file,'w+');
      fwrite($f,$file);
      fclose($f);
    }
  }

  public function setHost($host=false){
    if($host===false){
      $this->host = Array();
    }else{
      $this->host[] = (string)$host;
    }
    return $this;
  }

  public function isConfigure(){
    return $this->dir!==false?true:false;
  }

  public function getParseReferer($ref){
    if($this->dir===false){return false;}
    if(!file_exists($this->dir.'/'.$this->file)){return false;}
    $ref = trim($ref);
    if($ref==''){return false;}
    $refParts = $this->parseUrl($ref);
    if(in_array($refParts['host'], $this->host)){
      return false;
    }
    $referer = $this->lookup($refParts['host'], $refParts['path']);
    if(!$referer){
      return false;
    }
    $searchTerm = false;
    if($referer['parameters']){
      foreach($referer['parameters'] as $parameter){
        $searchTerm = isset($refParts['query'][$parameter]) ? $refParts['query'][$parameter] : $searchTerm;
      }
    }
    return Array($referer['medium'], $referer['source'], $searchTerm);
  }

  protected function getFileContent(){
    if(file_exists($this->dir.'/'.$this->file.'.cache')){
      return json_decode(file_get_contents($this->dir.'/'.$this->file.'.cache'),true);
    }
    $arr = file_get_contents($this->dir.'/'.$this->file);
    $arr = json_decode(base64_decode($arr),true);
    $ret = Array();
    foreach($arr as $medium => $referers){
      foreach ($referers as $source => $referer){
        foreach ($referer['domains'] as $domain){
          $ret[$domain] = Array(
            'source'     => $source,
            'medium'     => $medium,
            'parameters' => isset($referer['parameters']) ? $referer['parameters'] : Array(),
          );
        }
      }
    }
    $f = fopen($this->dir.'/'.$this->file.'.cache','w+');
    fwrite($f,json_encode($ret));
    fclose($f);
    return $ret;
  }

  protected function parseUrl($url){
    if($url == ''){
      return false;
    }
    $parts = parse_url($url);
    if(isset($parts['query'])){
      parse_str($parts['query'],$output);
      $parts['query'] = $output;
    }else{
      $parts['query'] = Array();
    }
    if(isset($parts['fragment'])){
      parse_str($parts['fragment'],$output);
      $parts['query'] = array_merge($output,isset($parts['query'])?$parts['query']:Array());
    }
    if(!isset($parts['scheme']) || !in_array(strtolower($parts['scheme']), Array('http', 'https'))){
      return false;
    }
    return array_merge(Array('path' => '/'), $parts);
  }

  protected function lookup($host,$path){
    $referer = $this->lookupPath($host, $path);
    if($referer){
      return $referer;
    }
    return $this->lookupHost($host);
  }

  protected function lookupPath($host, $path){
    $referer = $this->lookupHost($host, $path);
    if($referer){
      return $referer;
    }
    $path = substr($path, 0, strrpos($path, '/'));
    if(!$path){
      return false;
    }
    return $this->lookupPath($host, $path);
  }

  protected function lookupHost($host, $path = null){
    do{
      $arr = $this->getFileContent();
      $referer = isset($arr[$host.$path])?$arr[$host.$path]:null;
      $host = substr($host, strpos($host, '.') + 1);
    }while(!$referer && substr_count($host, '.') > 0);
    return $referer;
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
