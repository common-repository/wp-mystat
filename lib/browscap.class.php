<?php
if(!defined('MYSTAT_VERSION')){
  throw new Exception('File not exist 404');
}


class mystat_browscap{

  protected $cachedir = '';
  protected $newversion = false;

  public function getCacheDir(){
    return $this->cachedir;
  }

  public function setCacheDir($cache=''){
    if(file_exists($cache) and is_writable($cache)){
      $this->cachedir = $cache!=''?rtrim($cache,'/').'/':'';
    }
    return $this;
  }

  public function getBrowser($user_agent){
    $formatter = null;
    $pat = $this->getPatterns($user_agent);
    foreach($pat as $patterns){
      if(preg_match('/^(?:'.str_replace("\t",')|(?:',$this->pregQuote($patterns)).')$/i', $user_agent)){
        $pattern = strtok($patterns, "\t");
        while($pattern !== false){
          if(preg_match('/^'.$this->pregQuote($pattern).'$/i',$user_agent)){
            $formatter = $this->getSettings($pattern);
            break 2;
          }
          $pattern = strtok("\t");
        }
      }
    }
    return $formatter;
  }

  public function isNeedUpdate(){
    if(!file_exists($this->getCacheDir().'browscap.version')){
      return true;
    }
    $v = $this->getLastVersion();
    if($v===false){return false;}
    if($this->getCacheVersion()!=$v){
      return true;
    }
    return false;
  }

  public function getUpdate(){
    if(!$this->isNeedUpdate()){return;}
    $gzip = false;
    $url = 'http://my-stat.com/update/browscap_update.php?type=source';
    if(function_exists('gzopen')){
      $url = 'http://my-stat.com/update/browscap_update.php?type=gzip';
      $gzip = true;
    }
    $content = $this->loadFile($url);
    $f = fopen($this->getCacheDir().'temp.browscap','w+');
    fwrite($f,$content);
    fclose($f);
    if($gzip){
      $fh = gzopen($this->getCacheDir().'temp.browscap','r');
    }else{
      $fh = fopen($this->getCacheDir().'temp.browscap','r');
    }
    $f = null;
    while(($line=($gzip?gzgets($fh):fgets($fh))) !== false){
      if(substr($line,0,1)=='['){
        $file = substr(trim($line),1,-1);
        $nf = true;
      }else{
        $nf = false;
      }
      if($nf and file_exists($this->getCacheDir().$file)){
        @unlink($this->getCacheDir().$file);
      }
      if($nf){
        if(is_resource($f)){
          fclose($f);
        }
        $f = fopen($this->getCacheDir().$file,'w+');
      }else{
        fwrite($f,$line);
      }
    }
    if(is_resource($f)){
      fclose($f);
    }
    if($gzip){
      gzclose($fh);
    }else{
      fclose($fh);
    }
    unlink($this->getCacheDir().'temp.browscap');
  }

  protected function getLastVersion(){
    if($this->newversion!==false){return $this->newversion;}
    $v = $this->loadFile('http://my-stat.com/update/browscap_update.php?type=version');
    $v = (int)trim($v);
    if($v>1000){$this->newversion = $v;return $v;}
    return false;
  }

  public function getCacheVersion(){
    if(!file_exists($this->getCacheDir().'browscap.version')){
      return true;
    }
    $v = file_get_contents($this->getCacheDir().'browscap.version');
    return $v;
  }

  protected static function getPatternStart($pattern,$variants=false){
    $string = preg_replace('/^([^\*\?\s]*)[\*\?\s].*$/', '\\1', substr($pattern, 0, 32));
    $string = strtolower($string);
    if($variants === true){
      $pattern_starts = array();
      $len = strlen($string);
      for($i=$len;$i>=1;$i--){
        $pattern_starts[] = md5(substr($string, 0, $i));
      }
      $pattern_starts[] = md5('');
      return $pattern_starts;
    }
    return md5($string);
  }

  protected static function getPatternLength($pattern){
    return strlen(str_replace('*', '', $pattern));
  }

  protected function getCacheSubkey($string){
    return substr($string,0,2);
  }

  protected function getAllPatternCacheSubkeys(){
    $sub_keys = array();
    $chars = array('0','1','2','3','4','5','6','7','8','9','a','b','c','d','e','f');
    foreach($chars as $char_one){
      foreach($chars as $char_two){
        $sub_keys[] = $char_one.$char_two;
      }
    }
    return $sub_keys;
  }

  protected function getPatterns($user_agent){
    $starts = $this->getPatternStart($user_agent, true);
    $length = strlen($user_agent);
    $starts[] = str_repeat('z', 32);
    $pattern_arr = array();
    foreach($starts as $tmp_start){
      $tmp_sub_key = $this->getCacheSubkey($tmp_start);
      $file = $this->getCacheDir().'browscap.'.'patterns.'.$tmp_sub_key;
      if(file_exists($file)){
        $handle = fopen($file,'r');
        if($handle){
          $found = false;
          while(($buffer = fgets($handle)) !== false){
            $tmp_buffer = substr($buffer, 0, 32);
            if($tmp_buffer === $tmp_start){
              $len = (int)strstr(substr($buffer,33,4),' ',true);
              if($len <= $length){
                list(,,$patterns) = explode(' ', $buffer, 3);
                $pattern_arr[] = trim($patterns);
              }
              $found = true;
            }elseif($found === true){
              break;
            }
          }
          fclose($handle);
        }
      }
    }
    return $pattern_arr;
  }

  protected static function pregQuote($pattern){
    $pattern = preg_quote($pattern, '/');
    return str_replace(array('\*', '\?', '\\x'), array('.*', '.', '\\\\x'), $pattern);
  }

  protected function getSettings($pattern, $settings = array()){
    if(sizeof($settings) === 0){
      $settings['browser_name_regex']   = '/^' . $pattern . '$/';
      $settings['browser_name_pattern'] = $pattern;
    }
    $add_settings = $this->getIniPart($pattern);
    $parent_pattern = null;
    if(isset($add_settings['Parent'])){
      $parent_pattern = $add_settings['Parent'];
      if(isset($settings['Parent'])){
        unset($add_settings['Parent']);
      }
    }
    $settings += $add_settings;
    if($parent_pattern !== null){
      return $this->getSettings($parent_pattern, $settings);
    }
    return $settings;
  }

  protected function getIniPart($pattern){
    $pattern_hash = md5($pattern);
    $sub_key      = $this->getCacheSubkey($pattern_hash);
    $return = array();
    $file = $this->getCacheDir().'browscap.'.'iniparts.'.$sub_key;
    $handle = fopen($file,'r');
    if($handle){
      while(($buffer = fgets($handle)) !== false){
        if(substr($buffer, 0, 32) === $pattern_hash){
          $return = json_decode(substr($buffer, 32), true);
          break;
        }
      }
      fclose($handle);
    }
    return $return;
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
