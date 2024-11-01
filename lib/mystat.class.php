<?php
if(!defined('MYSTAT_VERSION')){
  throw new Exception('File not exist 404');
}

class myStat{

  protected $driver = false;
  protected $db = false;

  public function getDriver(){
    return $this->driver;
  }

  public function getCacheDir($web=false){
    return $this->getDriver()->getCacheDir($web);
  }

  protected function getPermissionReport($page){
    if($this->getCurrentRole('ADMIN') or $this->getRoleAccess($page)){return true;}
    return false;
  }
/*
  public function getDBConnect(){
    if($this->db!==false){return;}
    if(!file_exists($this->getCacheDir().$this->getDriver()->getDBPrefix().'mystat_data.db')){
      copy(dirname(__FILE__).'/../asset/mystat_data.db',$this->getCacheDir().$this->getDriver()->getDBPrefix().'mystat_data.db');
    }
    $this->db = new SQLite3($this->getCacheDir().$this->getDriver()->getDBPrefix().'mystat_data.db',SQLITE3_OPEN_READWRITE,'MyStAt123');
    $this->db->busyTimeout(5000);
    $this->db->exec('PRAGMA encoding = "UTF-8";');
    $this->db->exec('PRAGMA journal_mode = wal;');
  }

  public function getDBDisconnect(){
    if($this->db===false){return;}
    $this->db->close();
    $this->db = false;
  }

  public function setDBSelect($sql){
    $this->getDBConnect();
    return $this->db->query($sql);
  }

  public function setDBExexc($sql){
    $this->getDBConnect();
    return $this->db->exec($sql);
  }

  public function setDBSingle($sql){
    $this->getDBConnect();
    return $this->db->querySingle($sql);
  }

  public function getDBResult($data){
    return $data->fetchArray(SQLITE3_ASSOC);
  }
*/
  public function getEndOfScript(){
#    $this->getDBDisconnect();
  }

  public function getRoleAccess($name = false){
    if($name===false){
      $ret = $menu = Array();
      if($dh = opendir(dirname(__FILE__).'/../report/')){
        while(($file = readdir($dh))!==false){
          if(filetype(dirname(__FILE__).'/../report/'.$file)=='file' and substr($file,-10)=='.class.php'){
            if(preg_match('/^([A-z0-9]{2})_([A-z0-9]{1,})_([0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12})$/',substr($file,0,-10),$m)){
              $menu[] = $m[2];
            }
          }
        }
        closedir($dh);
      }
    }
    $ac = (array)json_decode($this->getOption('mystatrolemenu','{}'),true);
    if($name!==false){
      if($name=='settings'){return false;}
      if(sizeof($ac)==0 or $name==$this->getOption('mystatdefaultpage','dashboard')){return true;}
      if(isset($ac[$name]) and ((is_bool($ac[$name]) and $ac[$name]==true) or (is_string($ac[$name]) and strtolower($ac[$name])=='true'))){
        return true;
      }
      return false;
    }
    foreach($menu as $m){
      if($m!='settings'){
        if($m==$this->getOption('mystatdefaultpage','dashboard')){
          $ret[] = $m;
        }elseif(isset($ac[$m]) and ((is_bool($ac[$m]) and $ac[$m]==true) or (is_string($ac[$m]) and strtolower($ac[$m])=='true'))){
          $ret[] = $m;
        }elseif(sizeof($ac)==0){
          $ret[] = $m;
        }
      }
    }
    return $ret;
  }

  public function setRoleAccess($arr){
    $this->setOption('mystatrolemenu',sizeof($arr)>0?json_encode($arr):'{}');
    return $this;
  }

  public function getCurrentRole($name=false){
    $role = $this->getDriver()->getCurrentRole();
    if($name!==false){
      return $role==$name;
    }
    return $role;
  }

  public function getRole(){
    $role = $this->getDriver()->getRole();
    if(!in_array($role,Array('ADMIN','EDITOR','USER'))){
      $role = 'ADMIN';
    }
    return $role;
  }

  public function setRole($role){
    if(!in_array(strtoupper($role),Array('ADMIN','EDITOR','USER'))){
      $role = 'ADMIN';
    }
    $this->getDriver()->setRole($role);
    return $this;
  }

  public function setDriver($driver=false,$param=false){
    if($driver===false){
      if($dh = opendir(dirname(__FILE__).'/../driver/')){
        while(($file = readdir($dh))!==false){
          if(filetype(dirname(__FILE__).'/../driver/'.$file)=='file' and substr($file,-10)=='.class.php'){
            if(preg_match('/^([A-z0-9]{1,})$/',substr($file,0,-10),$m)){
              require_once(dirname(__FILE__).'/../driver/'.$m[1].'.class.php');
              $run = 'mystat_'.$m[1];
              $dd = new $run($this);
              if(true === $error = $dd->isEngineRun()){
                $driver = $m[1];
                break;
              }
            }
          }
        }
        closedir($dh);
      }
    }
    if(!preg_match('/^[A-z0-9]{1,}$/',$driver) or !file_exists(dirname(__FILE__).'/../driver/'.$driver.'.class.php')){
      $this->getEndOfScript();
      throw new Exception('Wrong DRIVER param in setDriver()');
    }
    require_once(dirname(__FILE__).'/../driver/'.$driver.'.class.php');
    $run = 'mystat_'.$driver;
    $this->driver = new $run($this,$param);
    if(true !== $error = $this->getDriver()->isEngineRun()){
      $this->getEndOfScript();
      throw new Exception('DRIVER START ERROR: '.$error);
    }
    $this->getDriver()->setCodeHook($this,Array($this,'toDriverCodeHook'));
    return $this;
  }

  public function toDriverCodeHook($mystat){
    list($id,$param) = $mystat->setStatisticFirst();
    if(!$mystat->getDriver()->isFeed()){
      echo $mystat->getJsCodeClick(abs($id));
      if($id>0){
        echo $mystat->getJsCode($id);
      }
    }
    if(function_exists('fastcgi_finish_request')){
//      fastcgi_finish_request();
    }
    if(!$this->getOption('mystatpostupdate',false)){
      $this->setStatisticsById($id,$param);
    }
    $this->getEndOfScript();
  }

  public function run($param = false){
    if($this->getDriver()===false){
      $this->setDriver(false,$param);
    }
    if($this->getDriver()===false){
      $this->getEndOfScript();
      throw new Exception('Set DRIVER before run run()');
    }
    $this->getDriver()->setRunHook($this,Array($this,'toDriveRunHook'));
    return $this->getDriver()->startDriver(func_get_args());
  }

  public function toDriveRunHook($mystat){
    echo $mystat->getReportPage();
  }

  protected function isExistPage($page){
    $ret = false;
    if($dh = opendir(dirname(__FILE__).'/../report/')){
      while(($file = readdir($dh))!==false){
        if(filetype(dirname(__FILE__).'/../report/'.$file)=='file' and substr($file,-10)=='.class.php'){
          if(preg_match('/^([A-z0-9]{2})_([A-z0-9]{1,})_([0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12})$/',substr($file,0,-10),$m)){
            if($m[2]==$page){
              $ret = true;
              break;
            }
          }
        }
      }
      closedir($dh);
    }
    return $ret;
  }

  public function getReportPage(){
    $page = (string)$this->getDriver()->getParam('report','');
    if($page==''){
      $defp = $this->getOption('mystatdefaultpage','dashboard');
      if(!$this->isExistPage($defp)){
        $this->setOption('mystatdefaultpage','dashboard');
        $defp = 'dashboard';
      }
      $page = $defp;
    }
    $param = (array)$this->getDriver()->getParam('param',Array());
    $isAjax = (bool)$this->getDriver()->isAjax();
    if($page=='update'){
      return $this->updateDefinition();
    }elseif($page=='image'){
      $this->setStatisticPrevious();
      return;
    }elseif($page=='insertclick'){
      $this->setStatisticClick($param);
      return;
    }elseif($page=='insert'){
      $this->setStatisticSecond();
      return;
    }elseif($page=='export'){
      $page = $this->getDriver()->getParam('in',$this->getOption('mystatdefaultpage','dashboard'));
      if(!$this->getDriver()->isAccess() or (!in_array($page,Array($this->getOption('mystatdefaultpage','dashboard'),'defaultpage')) and $ret = $this->getStatPage($page))){
        $this->getEndOfScript();
        throw new Exception('No access');
      }
      $xml = $this->getXMLPage($page,$param);
      $xml = $this->getXMLtoExport($xml);
      $type = $this->getDriver()->getParam('type','xml');
      if($type=='xml'){
        header('Content-type: text/xml');
        header('Content-Disposition: attachment; filename="'.$page.'_'.$this->getDriver()->getTime().'.xml"');
        header('Pragma: no-cache');
        header('Expires: 0');
        echo $xml;
      }
      $this->getEndOfScript();
      exit;
    }
    if(!$this->getDriver()->isAccess() or !$this->getPermissionReport($page)){
      $this->getEndOfScript();
      throw new Exception('No access');
    }
    if(!preg_match('/^[A-z0-9]{1,}$/',$page) or !$this->getFileReportName($page) or !file_exists(dirname(__FILE__).'/../report/'.$this->getFileReportName($page))){
      $this->getEndOfScript();
      throw new Exception('No report found');
    }
    if(!$isAjax){
      $xml1 = $this->getDefaultXML($page);
    }
    if(!in_array($page,Array($this->getOption('mystatdefaultpage','dashboard'),'defaultpage')) and $ret = $this->getStatPage($page)){
      $param = array_merge($param,Array('page' => $page,'code'=>$ret));
      $page = 'defaultpage';
    }
    if(!$isAjax){
      $xml2 = $this->getXMLPage($page,$param);
      require_once(dirname(__FILE__).'/mergexml.class.php');
      $mergexml = new mystat_mergexml(Array('updn'=>true));
      $mergexml->AddSource($xml1);
      $mergexml->AddSource($xml2);
      $xml = $mergexml->Get(1);
      unset($xml1);
      unset($xml2);
      return $this->getXSLTranform($page,$xml);
    }
    echo json_encode($this->getAjaxArray($page,$param));
    $this->getEndOfScript();
    exit;
  }

  public function updateDefinition($ajax=true){
    if($this->isAllFileExists() and $this->getOption('mystatlastupdate')==date('dmY',$this->getDriver()->getTime(false))){
      return false;
    }
    $this->isInstallCorrect();
    if($ajax){
      $ret = $this->getDriver()->setUpdateStart();
    }
    if(function_exists('fastcgi_finish_request')){
      if($ajax){
        echo (string)$ret.$this->getDriver()->setUpdateStop();
      }
//      fastcgi_finish_request();
    }
    $db_md5 = $this->loadFile('http://my-stat.com/update/geobase_v2.1.dat.md5');
    $db_content = '';
    if(file_exists($this->getCacheDir().'geobase_v2.1.dat')){
      $db_content = md5_file($this->getCacheDir().'geobase_v2.1.dat');
    }
    if(strlen($db_md5)==32 and $db_md5 != $db_content){
      $gzip = false;
      $url = 'http://my-stat.com/update/geobase_v2.1.dat';
      if(function_exists('gzopen')){
        $url = 'http://my-stat.com/update/geobase_v2.1.gz';
        $gzip = true;
      }
      $db_content = $this->loadFile($url);
      @file_put_contents($this->getCacheDir().($gzip?'temp.geobase.1':'geobase_v2.1.dat'),$db_content);
      if($gzip){
        if(file_exists($this->getCacheDir().'geobase_v2.1.dat')){
          @unlink($this->getCacheDir().'geobase_v2.1.dat');
        }
        $fh = gzopen($this->getCacheDir().'temp.geobase.1','r');
        $f = fopen($this->getCacheDir().'geobase_v2.1.dat','w+');
        while(!gzeof($fh)){
          $line=gzread($fh,10240);
          fwrite($f,$line,10240);
        }
        fclose($f);
        gzclose($fh);
        unlink($this->getCacheDir().'temp.geobase.1');
      }
    }
    require_once(dirname(__FILE__).'/referer.class.php');
    $req = new mystat_referer();
    $req->setCache($this->getCacheDir());
    $req->update();
    require_once(dirname(__FILE__).'/browscap.class.php');
    $browscap = new mystat_browscap();
    $browscap->setCacheDir($this->getCacheDir());
    $browscap->getUpdate();
    if(function_exists('gzopen')){
      $db_md5 = $this->loadFile('http://my-stat.com/update/geobase_v2.3.dat.md5');
      $db_content = '';
      if(file_exists($this->getCacheDir().'geobase_v2.3.dat')){
        $db_content = md5_file($this->getCacheDir().'geobase_v2.3.dat');
      }
      if(strlen($db_md5)==32 and $db_md5 != $db_content){
        $db_content = $this->loadFile('http://my-stat.com/update/geobase_v2.3.gz');
        @file_put_contents($this->getCacheDir().'temp.geobase.3',$db_content);
        if(file_exists($this->getCacheDir().'geobase_v2.3.dat')){
          @unlink($this->getCacheDir().'geobase_v2.3.dat');
        }
        $fh = gzopen($this->getCacheDir().'temp.geobase.3','r');
        $f = fopen($this->getCacheDir().'geobase_v2.3.dat','w+');
        while(!gzeof($fh)){
          $line=gzread($fh,10240);
          fwrite($f,$line,10240);
        }
        fclose($f);
        gzclose($fh);
        unlink($this->getCacheDir().'temp.geobase.3');
      }
    }
    if(class_exists('ZipArchive')){
      $db_md5 = $this->loadFile('http://my-stat.com/update/icon_update.md5');
      $db_md5_local = '';
      if(file_exists($this->getCacheDir().'icon_update.zip')){
        $db_md5_local = md5_file($this->getCacheDir().'icon_update.zip');
      }
      if(strlen($db_md5)==32 and $db_md5 != $db_md5_local){
        if(file_exists($this->getCacheDir().'icon_update.zip')){
          @unlink($this->getCacheDir().'icon_update.zip');
        }
        $db_content = $this->loadFile('http://my-stat.com/update/icon_update.zip');
        @file_put_contents($this->getCacheDir().'icon_update.zip',$db_content);
        if(!file_exists($this->getCacheDir().'icon')){
          mkdir($this->getCacheDir().'icon');
        }
        $zip = new ZipArchive();
        if($zip->open($this->getCacheDir().'icon_update.zip') === true){
          $zip->extractTo($this->getCacheDir().'icon/');
          $zip->close();
        }
      }
    }
    $this->setOption('mystatlastupdate',date('dmY',$this->getDriver()->getTime(false)));
    if($ajax){
      return (string)$ret.$this->getDriver()->setUpdateStop();
    }
    return true;
  }

  protected function getAlertMessage(){
    $file = $this->loadFile('http://my-stat.com/update/alert.php',Array(
      'driver' => $this->getDriver()->getName(),
      'version' => MYSTAT_VERSION,
      'lang' => $this->getDriver()->getLanguage(), 
      'domain' => $_SERVER['HTTP_HOST']
    ));
    if(file_exists($this->getCacheDir().'alert.dat')){
      unlink($this->getCacheDir().'alert.dat');
    }
    if(trim($file)==''){return;}
    @file_put_contents($this->getCacheDir().'alert.dat',trim($file));
  }

  protected function getXMLtoExport($xml){
    $dom = new DOMDocument();
    $dom->loadXML($xml);
    $node = $dom->getElementsByTagName('TRANSLATE');
    foreach($node as $n){
      $n->parentNode->removeChild($n);
    }
    return $dom->saveXML();
  }

  protected function getCSV($fields, $delimiter = ';', $enclosure = '"', $encloseAll = false, $nullToMysqlNull = false){
    $delimiter_esc = preg_quote($delimiter, '/');
    $enclosure_esc = preg_quote($enclosure, '/');
    $output = array();
    foreach($fields as $field){
      if($field === null && $nullToMysqlNull){
        $output[] = 'NULL';
        continue;
      }
      if($encloseAll || preg_match( "/(?:${delimiter_esc}|${enclosure_esc}|\s)/", $field )){
        $output[] = $enclosure . str_replace($enclosure, $enclosure . $enclosure, $field) . $enclosure;
      }else{
        $output[] = $field;
      }
    }
    return join($delimiter, $output);
}

  protected function getFileReportName($page){
    if($dh = opendir(dirname(__FILE__).'/../report/')){
      while(($file = readdir($dh))!==false){
        if(filetype(dirname(__FILE__).'/../report/'.$file)=='file' and substr($file,-10)=='.class.php'){
          if(preg_match('/^([A-z0-9]{2})_([A-z0-9]{1,})_([0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12})$/',substr($file,0,-10),$m)){
            if($page==$m[2]){
              closedir($dh);
              return $file;
            }
          }elseif(preg_match('/^_([A-z0-9]{1,})$/i',substr($file,0,-10),$m)){
            if($page==$m[1]){
              closedir($dh);
              return $file;
            }
          }
        }
      }
      closedir($dh);
    }
    return false;
  }

  public function getCountryName($code,$lang=false){
    if($lang===false){
      $lang = $this->getDriver()->getLanguage();
    }
    require_once(dirname(__FILE__).'/country.class.php');
    $country = new mystat_country();
    $country->setCacheDir($this->getCacheDir());
    return $country->getCountryByCode($code,$lang);
  }

  public function getLanguageName($code,$lang=false){
    if($lang===false){
      $lang = $this->getDriver()->getLanguage();
    }
    require_once(dirname(__FILE__).'/language.class.php');
    $language = new mystat_language();
    $language->setCacheDir($this->getCacheDir());
    return $language->getLanguageByCode($code,$lang);
  }

  public function getCountryFlag($code){
    $code = strtoupper($code);
    if(!preg_match('/^[A-Z]{2}$/',$code)){
      return false;
    }
    if(file_exists($this->getCacheDir().'icon'.'/flags/'.$code.'.png')){
      return 'icon/flags/'.$code.'.png';
    }
    return false;
  }

  public function getLanguageFlag($code){
    $code = strtolower($code);
    if(!preg_match('/^[a-z]{2}$/',$code)){
      return false;
    }
    if(file_exists($this->getCacheDir().'icon'.'/lang/'.$code.'.gif')){
      return 'icon/lang/'.$code.'.gif';
    }
    return false;
  }

  public function getBrowserFlag($name){
    $name = strtolower(trim($name));
    $name = str_replace(Array(' ','.','-','/'),'_',$name);
    $name = preg_replace('/_{2,}/','_',trim($name,'_'));
    if(!preg_match('/^[A-z0-9_]*$/',$name) or strlen($name)<1){
      return false;
    }
    if(file_exists($this->getCacheDir().'icon'.'/browser/'.$name.'.png')){
      return 'icon/browser/'.$name.'.png';
    }
    return false;
  }

  public function getDeviceFlag($name){
    $name = strtolower(trim($name));
    $name = str_replace(Array(' ','.','-','/'),'_',$name);
    $name = preg_replace('/_{2,}/','_',trim($name,'_'));
    if(!preg_match('/^[A-z0-9_]*$/',$name) or strlen($name)<1){
      return false;
    }
    if(file_exists($this->getCacheDir().'icon'.'/device/'.$name.'.png')){
      return 'icon/device/'.$name.'.png';
    }
    return false;
  }

  public function getOSFlag($name){
    $name = strtolower(trim($name));
    $name = str_replace(Array(' ','.','-','&',','),'_',$name);
    $name = preg_replace('/_{2,}/','_',trim($name,'_'));
    if(!preg_match('/^[A-z0-9_]*$/',$name) or strlen($name)<1){
      return false;
    }
    if(file_exists($this->getCacheDir().'icon'.'/os/'.$name.'.png')){
      return 'icon/os/'.$name.'.png';
    }
    return false;
  }

  protected function getDefaultXML($page=false){
    if($page===false){
      $page = $this->getOption('mystatdefaultpage','dashboard');
    }
    $period = $this->getPeriod();
    $report = Array();
    $menu = $this->getMenu();
    $report['REPORT'] = Array(
      'DEFAULTREPORT' => $this->getOption('mystatdefaultpage','dashboard'),
      'PERIOD' => Array(
        'START' => date('d.m.Y',$period['start']),
        'END' => date('d.m.Y',$period['end'])
      ),
      'PATHTOASSET' => $this->getPathAsset(),
      'PATHTOCACHE' => $this->getCacheDir(true),
      'PATHEXPORT' => $this->getDriver()->getExportUrl(),
      'REPORT' => $page,
      'TRANSLATE' => Array(
        'PERIODREPORT' => $this->__('Report display period'),
        'EXPORTXML' => $this->__('Export this report as an XML file')
      ),
      'GMT' => (int)$this->getDriver()->getGMT(),
      'TIME' => (int)$this->getDriver()->getTime(true),
      'LANGUAGE' => $this->getDriver()->getLanguage(),
      'DRIVER' => $this->getDriver()->getName(),
      'VERSION' => MYSTAT_VERSION,
      'MENU' => $menu
    );
    $xml = new DOMDocument('1.0', 'UTF-8');
    $xml->formatOutput = true;
    $xml->preserveWhiteSpace = false;
    $this->xmlStructureFromArray($xml,$report);
    return $xml->saveXML();
  }

  protected function getMenu(){
    $category = Array(
      '377da97c-3097-4c0b-9315-125270b9f935' => $this->__('Audience'),
      '72fb852f-71e7-4802-af52-8f4bf17b091b' => $this->__('Pages'),
      'bdb2d1a3-41ba-47e9-a476-6ded1ba6e627' => $this->__('Traffic sources'),
      '3fbec588-fbf5-4521-a406-64689b250530' => $this->__('Geography'),
      'bcbd4b71-f45f-47fe-85ff-27b1e68499ef' => $this->__('System'),
      'a0e1c952-effc-4c6d-9f90-b8b8c855e889' => $this->__('Other')
    );
    $ret = $menu = Array();
    $menu = array_fill_keys(array_keys($category),Array());
    $role = $this->getRoleAccess();
    if($dh = opendir(dirname(__FILE__).'/../report/')){
      while(($file = readdir($dh))!==false){
        if(filetype(dirname(__FILE__).'/../report/'.$file)=='file' and substr($file,-10)=='.class.php'){
          if(preg_match('/^([A-z0-9]{2})_([A-z0-9]{1,})_([0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12})$/',substr($file,0,-10),$m)){
            require_once(dirname(__FILE__).'/../report/'.$file);
            $name = $m[2];
            $run = 'mystat_'.$name;
            $report = new $run($this,Array());
            if(method_exists($report,'getXML') and method_exists($report,'getName')){
              if((method_exists($report,'isShow') and $report->isShow()) or !method_exists($report,'isShow')){
                if(isset($category[$m[3]])){
                  $dis = false;
                  if(!$this->getCurrentRole('ADMIN')){
                    if(!in_array($name,$role)){
                      $dis = true;
                    }
                  }
                  $menu[$m[3]][$m[1]] = Array('name' => $report->getName(), 'code' => $name, 'disabled'=>$dis);
                }
              }
            }
          }
        }
      }
      closedir($dh);
    }
    foreach($menu as $k=>$v){
      $ret[] = Array(
        'TITLE' => $category[$k],
        'ITEM' => Array(),
        '@ITEM' => Array()
      );
      ksort($v);
      foreach($v as $el){
        $ret[sizeof($ret)-1]['ITEM'][] = $el['name'];
        $ret[sizeof($ret)-1]['@ITEM'][] = Array('code'=>$el['code'],'disabled'=>$el['disabled']);
      }
    }
    return $ret;
  }

  protected function getXSLTranform($page,$xml){
    if(!file_exists(dirname(__FILE__).'/../theme/'.$this->getDriver()->getName().'/'.$this->getDriver()->getName().'.'.$page.'.xsl')){
      $this->getEndOfScript();
      throw new Exception('No theme found for this page or driver');
    }
    $doc = new DOMDocument();
    $doc->preserveWhiteSpace = false;
    $xsl = new XSLTProcessor();
    $doc->load(dirname(__FILE__).'/../theme/'.$this->getDriver()->getName().'/'.$this->getDriver()->getName().'.'.$page.'.xsl');
    $xsl->importStyleSheet($doc);
    $doc->loadXML($xml);
    $this->getEndOfScript();
    return $xsl->transformToXML($doc);
  }

  protected function getXMLPage($page,$param = Array()){
    require_once(dirname(__FILE__).'/../report/'.$this->getFileReportName($page));
    $page = 'mystat_'.$page;
    $report = new $page($this,$param);
    $xml = $report->getXML();
    return $xml;
  }

  protected function getAjaxArray($page,$param = Array()){
    require_once(dirname(__FILE__).'/../report/'.$this->getFileReportName($page));
    $page = 'mystat_'.$page;
    $report = new $page($this,$param);
    $xml = Array();
    if(method_exists($report,'getAjax')){
      $xml = $report->getAjax();
    }
    return $xml;
  }

  public function getPeriod(){
    $ret = Array(
      'start' => strtotime('-30 days',$this->getDriver()->getTime(false)),
      'end' => $this->getDriver()->getTime(false)
    );
    if((''!=$date1 = $this->getDriver()->getParam('datestart','')) and (''!=$date2 = $this->getDriver()->getParam('dateend',''))){
      if(!preg_match('/^[0-9]{2}\.[0-9]{2}\.[0-9]{4}$/',$date1) or !preg_match('/^[0-9]{2}\.[0-9]{2}\.[0-9]{4}$/',$date2)){
        $this->getEndOfScript();
        throw new Exception('Wrong date format in request');
      }
      $ret['start'] = strtotime((string)$date1);
      $ret['end'] = strtotime((string)$date2);
    }
    return $ret;
  }

  public function __($text){
    return $this->getDriver()->__($text);
  }

  public function getOption($name,$default=false){
    return $this->getDriver()->getOption($name,$default);
  }

  public function setOption($name,$value=false){
    $this->getDriver()->setOption($name,$value);
    return $this;
  }

  public function getPathAsset(){
    return trim($this->getDriver()->getWebPath(),'/').'/';
  }

  function setString($name,$value=''){
    $string = $this->getString();
    $string[$name] = $value;
    $string = json_encode($string);
    $res = '';
    for($i=0;$i<strlen($string);$i++){
      $bin = decbin(ord($string[$i]));
      $bin = strlen($bin)>7?$bin:implode('', array_fill(0, 8 - strlen($bin), '0')).$bin;
      $res.= str_replace(array('1', '0'), array(chr(9), chr(32)), $bin);
    }
    $file = file(__FILE__);
    array_pop($file);
    $file[] = $res;
    file_put_contents(__FILE__,$file);
  }

  function getString($name=false){
    $file = file(__FILE__);
    $str = array_pop($file);
    $res = '';
    for($i=0;$i<strlen($str);$i++){
      $res.= chr(bindec(trim(str_replace(array(chr(9), chr(32)), array('1', '0'), substr($str, $i, 8)))));
      $i+= 7;
    }
    $ret = json_decode($res,true);
    return $name===false?$ret:(isset($ret[$name])?$ret[$name]:'');
  }

  public function isShow(){
    return in_array($this->is(),Array('','OK'));
  }

  public function isInstallCorrect($crit = true){
    $error = Array();
    if(!file_exists($this->getCacheDir())){
      mkdir($this->getCacheDir());
    }
    @chmod($this->getCacheDir(),0777);
    @chmod(dirname(__FILE__).'/../asset/',0777);
    $test = @fopen($this->getCacheDir().'test.lock','w+');
    if($test===false){
      $error[] = 'WRITE';
    }else{
      fclose($test);
      if(file_exists($this->getCacheDir().'test.lock')){
        @unlink($this->getCacheDir().'test.lock');
      }
    }
    if($crit and !function_exists('gzopen')){
      $error[] = 'ZLIB';
    }
    if($crit and !class_exists('ZipArchive')){
      $error[] = 'ZIP';
    }
    if(!class_exists('DOMDocument')){
      $error[] = 'DOM';
    }
    if(!class_exists('XSLTProcessor')){
      $error[] = 'XSLT';
    }
    return $error;
  }

  public function isAllFileExists(){
    if(!file_exists($this->getCacheDir().'geobase_v2.1.dat') or !file_exists($this->getCacheDir().'browscap.version') or !file_exists($this->getCacheDir().'referer.dat')){
      return false;
    }
    return true;
  }

  public function isNeedUpdate(){
    if($this->isAllFileExists() and $this->getOption('mystatlastupdate')==date('dmY',$this->getDriver()->getTime(false))){
      return false;
    }
    require_once(dirname(__FILE__).'/browscap.class.php');
    $browscap = new mystat_browscap();
    $browscap->setCacheDir($this->getCacheDir());
    if($browscap->isNeedUpdate()){
      return true;
    }
    $db_md5 = $this->loadFile('http://my-stat.com/update/geobase_v2.1.dat.md5');
    if(strlen($db_md5)==32 and (!file_exists($this->getCacheDir().'geobase_v2.1.dat') or md5_file($this->getCacheDir().'geobase_v2.1.dat') != $db_md5)){
      return true;
    }
    require_once(dirname(__FILE__).'/referer.class.php');
    $req = new mystat_referer();
    $req->setCache($this->getCacheDir());
    if($req->isNeedUpdate()){
      return true;
    }
    if(function_exists('gzopen')){
      $db_md5 = $this->loadFile('http://my-stat.com/update/geobase_v2.3.dat.md5');
      if(strlen($db_md5)==32 and (!file_exists($this->getCacheDir().'geobase_v2.3.dat') or md5_file($this->getCacheDir().'geobase_v2.3.dat') != $db_md5)){
        return true;
      }
    }
    if(class_exists('ZipArchive')){
      $db_md5 = $this->loadFile('http://my-stat.com/update/icon_update.md5');
      $db_md5_local = '';
      if(file_exists($this->getCacheDir().'icon_update.zip')){
        $db_md5_local = md5_file($this->getCacheDir().'icon_update.zip');
      }
      if(strlen($db_md5)==32 and $db_md5 != $db_md5_local){
        return true;
      }
    }
    $this->setOption('mystatlastupdate',date('dmY',$this->getDriver()->getTime(false)));
    $this->getAlertMessage();
    return false;
  }

  public function xmlStructureFromArray($xml,$arr,$child=false,$name='',$at = Array()){
    if(!is_array($arr)){return $xml;}
    foreach($arr as $k=>$v){
      if(substr($k,0,1)=='@'){continue;}
      if(is_array($v)){
        if(!isset($v[0])){
          $el = !$child?$xml->appendChild($xml->createElement($k)):$child->appendChild($xml->createElement(is_numeric($k)?$name:$k));
          if(isset($arr['@'.$k])){
            foreach($arr['@'.$k] as $nn=>$aa){
              $el->setAttribute($nn,$aa);
            }
          }elseif(isset($at[$k])){
            foreach($at[$k] as $nn=>$aa){
              $el->setAttribute($nn,$aa);
            }
          }
        }else{
          $el = !$child?$xml:$child;
          $attr = Array();
          if(isset($arr['@'.$k])){
            $attr = $arr['@'.$k];
          }
        }
        $this->xmlStructureFromArray($xml,$v,$el,$k,isset($attr)?$attr:Array());
      }else{
        if(!$child){
          if(in_array($v,Array('',null,false),true)){
              $el = $xml->createElement($k);
              $xml->appendChild($el);
          }else{
            $el = $xml->createElement($k,htmlspecialchars($v,ENT_NOQUOTES));
            $xml->appendChild($el);
          }
          if(isset($arr['@'.$k])){
            foreach($arr['@'.$k] as $nn=>$aa){
              $el->setAttribute($nn,$aa);
            }
          }
        }else{
          if(in_array($v,Array('',null,false),true)){
            $el = $xml->createElement(is_numeric($k)?$name:$k);
            $child->appendChild($el);
          }else{
            $el = $xml->createElement(is_numeric($k)?$name:$k,htmlspecialchars($v,ENT_NOQUOTES));
            $child->appendChild($el);
          }
          if(is_numeric($k)){
            if(isset($at[$k])){
              foreach($at[$k] as $nn=>$aa){
                $el->setAttribute($nn,$aa);
              }
            }
          }else{
            if(isset($arr['@'.$k])){
              foreach($arr['@'.$k] as $nn=>$aa){
                $el->setAttribute($nn,$aa);
              }
            }
          }
        }
      }
    }
    return $xml;
  }

  protected function mergeXMLArrays(){
    $arrays = func_get_args();
    $ret = $arrays[0];
    $to = sizeof($arrays);
    for($i=1;$i<$to;++$i){
      foreach($arrays[$i] as $key=>$value){
        if(((string) $key) === ((string) intval($key))){
          $ret[] = $value;
        }else{
          if(isset($ret[$key])){
            if(is_array($ret[$key]) and isset($ret[$key][0])){
              if(is_array($value) and isset($value[0])){
                $ret[$key] = array_merge($ret[$key],$value);
              }else{
                $ret[$key][] = $value;
              }
            }else{
              $ret[$key] = Array($ret[$key],$value);
            }
          }else{
            $ret[$key] = $value;
          }
        }
      }    
    }
    return $ret;
  }

  public function getJsCodeClick($id){
    $ret = '';
    if($this->getOption('mystatclickevent','true')=='true'){
      $uri = addslashes($_SERVER['REQUEST_URI']);
      $ret.= '<script type="text/javascript" charset="utf-8">//<![CDATA['."\n";
        $ret.= <<<JS
          function addEventsCrossBrowsers(elemenet,event,func){
            if(elemenet.addEventListener){
              elemenet.addEventListener(event,func,false);
            }else if(elemenet.attachEvent){
              elemenet.attachEvent("on"+event,func);
            }
          }
          function getMystatXPath(elm){ 
            var allNodes = document.getElementsByTagName('*'); 
            for(var segs = []; elm && elm.nodeType == 1; elm = elm.parentNode){ 
              if(elm.hasAttribute('id')){ 
                var uniqueIdCount = 0; 
                for(var n=0;n < allNodes.length;n++){ 
                  if(allNodes[n].hasAttribute('id') && allNodes[n].id == elm.id){
                    uniqueIdCount++;
                  }
                  if(uniqueIdCount > 1){break;}
                } 
                if( uniqueIdCount == 1){ 
                  segs.unshift('id("' + elm.getAttribute('id') + '")'); 
                  return segs.join('/'); 
                }else{ 
                  segs.unshift(elm.localName.toLowerCase() + '[@id="' + elm.getAttribute('id') + '"]'); 
                } 
              }else if(elm.hasAttribute('class')){ 
                segs.unshift(elm.localName.toLowerCase() + '[@class="' + elm.getAttribute('class') + '"]'); 
              }else{ 
                for(i = 1, sib = elm.previousSibling; sib; sib = sib.previousSibling){ 
                  if(sib.localName == elm.localName){i++;}
                } 
                segs.unshift(elm.localName.toLowerCase() + '[' + i + ']'); 
              }
            } 
            return segs.length ? '/' + segs.join('/') : null; 
          } 
          function getMystatPathTo(element){
            if(element.id!==''){
              return 'id("'+element.id+'")';
            }
            if(element===document.body){
              return element.tagName;
            }
            var ix= 0;
            var siblings= element.parentNode.childNodes;
            for(var i= 0; i<siblings.length; i++){
              var sibling= siblings[i];
              if(sibling===element){
                return getMystatPathTo(element.parentNode)+'/'+element.tagName+'['+(ix+1)+']';
              }
              if(sibling.nodeType===1 && sibling.tagName===element.tagName){
                ix++;
              }
            }
          }
          var myStatClickTimer = 0;
          function runStatisticMyStatClick(e){
            var touch = 'ontouchstart' in document.documentElement;
            var myStat = {
              id: {$id},
              uri: "{$uri}",
              touch: touch,
              width: screen.width,
              x: e.pageX,
              y: e.pageY,
              target: getMystatXPath(e.target)
            };
            return myStat;
          }
          addEventsCrossBrowsers(document.body,'click',function(e){
            var clickTime = new Date();
            if(clickTime.getTime() - myStatClickTimer < 1000){
              return;
            }
            myStatClickTimer = clickTime.getTime();
            var data = runStatisticMyStatClick(e);
            if(data.x == 0 && data.y == 0){
              return;
            }
            if(typeof runStatisticMyStatClickSend == 'function'){
              runStatisticMyStatClickSend(data);
            }
          });
JS;
      $ret.= "\n".'//]]></script>';
    }
    $ret.= $this->getDriver()->setJsSendClick($id);
    return $ret;
  }

  public function getJsCode($id){
    if($id==0){return '';}
    $MYSTAT_VERSION = MYSTAT_VERSION;
    $ret = '<script type="text/javascript" charset="utf-8">//<![CDATA['."\n";
      $ret.= <<<JS
        function runStatisticMyStat(){
          var FlashDetect=new function(){var self=this;self.installed=false;self.raw="";self.major=-1;self.minor=-1;self.revision=-1;self.revisionStr="";var activeXDetectRules=[{"name":"ShockwaveFlash.ShockwaveFlash.7","version":function(obj){return getActiveXVersion(obj);}},{"name":"ShockwaveFlash.ShockwaveFlash.6","version":function(obj){var version="6,0,21";try{obj.AllowScriptAccess="always";version=getActiveXVersion(obj);}catch(err){}return version;}},{"name":"ShockwaveFlash.ShockwaveFlash","version":function(obj){return getActiveXVersion(obj);}}];var getActiveXVersion=function(activeXObj){var version=-1;try{version=activeXObj.GetVariable("\$version");}catch(err){}return version;};var getActiveXObject=function(name){var obj=-1;try{obj=new ActiveXObject(name);}catch(err){obj={activeXError:true};}return obj;};var parseActiveXVersion=function(str){var versionArray=str.split(",");return{"raw":str,"major":parseInt(versionArray[0].split(" ")[1],10),"minor":parseInt(versionArray[1],10),"revision":parseInt(versionArray[2],10),"revisionStr":versionArray[2]};};var parseStandardVersion=function(str){var descParts=str.split(/ +/);var majorMinor=descParts[2].split(/\./);var revisionStr=descParts[3];return{"raw":str,"major":parseInt(majorMinor[0],10),"minor":parseInt(majorMinor[1],10),"revisionStr":revisionStr,"revision":parseRevisionStrToInt(revisionStr)};};var parseRevisionStrToInt=function(str){return parseInt(str.replace(/[a-zA-Z]/g,""),10)||self.revision;};self.majorAtLeast=function(version){return self.major>=version;};self.minorAtLeast=function(version){return self.minor>=version;};self.revisionAtLeast=function(version){return self.revision>=version;};self.versionAtLeast=function(major){var properties=[self.major,self.minor,self.revision];var len=Math.min(properties.length,arguments.length);for(i=0;i<len;i++){if(properties[i]>=arguments[i]){if(i+1<len&&properties[i]==arguments[i]){continue;}else{return true;}}else{return false;}}};self.FlashDetect=function(){if(navigator.plugins&&navigator.plugins.length>0){var type='application/x-shockwave-flash';var mimeTypes=navigator.mimeTypes;if(mimeTypes&&mimeTypes[type]&&mimeTypes[type].enabledPlugin&&mimeTypes[type].enabledPlugin.description){var version=mimeTypes[type].enabledPlugin.description;var versionObj=parseStandardVersion(version);self.raw=versionObj.raw;self.major=versionObj.major;self.minor=versionObj.minor;self.revisionStr=versionObj.revisionStr;self.revision=versionObj.revision;self.installed=true;}}else if(navigator.appVersion.indexOf("Mac")==-1&&window.execScript){var version=-1;for(var i=0;i<activeXDetectRules.length&&version==-1;i++){var obj=getActiveXObject(activeXDetectRules[i].name);if(!obj.activeXError){self.installed=true;version=activeXDetectRules[i].version(obj);if(version!=-1){var versionObj=parseActiveXVersion(version);self.raw=versionObj.raw;self.major=versionObj.major;self.minor=versionObj.minor;self.revision=versionObj.revision;self.revisionStr=versionObj.revisionStr;}}}}}();};
          var myStat = {
            id: {$id},
            mystat: '{$MYSTAT_VERSION}',
            do: 'update',
            geolocation: !!navigator.geolocation,
            offline: !!window.applicationCache,
            webworker: !!window.Worker,
            localStorage: ('localStorage' in window) && window['localStorage'] !== null,
            canvas: {
              enable: !!document.createElement('canvas').getContext,
              text2d: !!document.createElement('canvas').getContext?(typeof document.createElement('canvas').getContext('2d').fillText == 'function'):false
            },
            video: {
              enable: !!document.createElement('video').canPlayType,
              captions: 'track' in document.createElement('track'),
              poster: 'poster' in document.createElement('video'),
              mp4: !!(document.createElement('video').canPlayType && document.createElement('video').canPlayType('video/mp4; codecs="avc1.42E01E, mp4a.40.2"').replace(/no/, '')),
              webm: !!(document.createElement('video').canPlayType && document.createElement('video').canPlayType('video/webm; codecs="vp8, vorbis"').replace(/no/, '')),
              theora: !!(document.createElement('video').canPlayType && document.createElement('video').canPlayType('video/ogg; codecs="theora, vorbis"').replace(/no/, ''))
            },
            microdata: !!document.getItems,
            history: !!(window.history && window.history.pushState && window.history.popState),
            undo: typeof UndoManager !== 'undefined',
            audio: {
              enable: !!document.createElement('audio').canPlayType,
              mp3: !!(document.createElement('audio').canPlayType && document.createElement('audio').canPlayType('audio/mpeg;').replace(/no/, '')),
              vorbis: !!(document.createElement('audio').canPlayType && document.createElement('audio').canPlayType('audio/ogg; codecs="vorbis"').replace(/no/, '')),
              wav: !!(document.createElement('audio').canPlayType && document.createElement('audio').canPlayType('audio/wav; codecs="1"').replace(/no/, '')),
              aac: !!(document.createElement('audio').canPlayType && document.createElement('audio').canPlayType('audio/mp4; codecs="mp4a.40.2"').replace(/no/, ''))
            },
            command: 'type' in document.createElement('command'),
            datalist: 'options' in document.createElement('datalist'),
            details: 'open' in document.createElement('details'),
            device: 'type' in document.createElement('device'),
            validation: 'noValidate' in document.createElement('form'),
            iframe: {
              sandbox: 'sandbox' in document.createElement('iframe'),
              srcdoc: 'srcdoc' in document.createElement('iframe')
            },
            input: {
              autofocus: 'autofocus' in document.createElement('input'),
              placeholder: 'placeholder' in document.createElement('input'),
              type: {}
            },
            meter: 'value' in document.createElement('meter'),
            output: 'value' in document.createElement('output'),
            progress: 'value' in document.createElement('progress'),
            time: 'valueAsDate' in document.createElement('time'),
            editable: 'isContentEditable' in document.createElement('span'),
            dragdrop: 'draggable' in document.createElement('span'),
            documentmessage: !!window.postMessage,
            fileapi: typeof FileReader != 'undefined',
            serverevent: typeof EventSource !== 'undefined',
            sessionstorage: false,
            svg: !!(document.createElementNS && document.createElementNS('http://www.w3.org/2000/svg', 'svg').createSVGRect),
            simpledb: !!window.indexedDB,
            websocket: !!window.WebSocket,
            websql: !!window.openDatabase,
            cookies: navigator.cookieEnabled?true:false,
            flash: {
              enable: FlashDetect.installed?true:false,
              version: FlashDetect.major+'.'+FlashDetect.minor
            },
            java: !!navigator.javaEnabled(),
            title: document.title,
            appname: navigator.appName,
            screen: {
              width: screen.width,
              height: screen.height,
              depth: (navigator.appName.substring(0,2)=='Mi')?screen.colorDepth:screen.pixelDepth
            },
            viewport: {
              width: window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth,
              height: window.innerHeight || document.documentElement.clientHeight || document.body.clientHeight
            }
          };
          var inputlist = new Array('color','email','number','range','search','tel','url','date','time','datetime','datetime-local','month','week');
          var i = document.createElement('input');
          for(var key=0;key<inputlist.length;key++){
            var el = inputlist[key];
            i.setAttribute('type', el);
            myStat.input.type[el] = i.type !== 'text';
          }
          try{myStat.sessionstorage = (('sessionStorage' in window) && window['sessionStorage'] !== null);}catch(e){}
          if(!document.cookie){
            document.cookie = "testCookie=1; path=/";
            myStat.cookies = document.cookie?1:0;
          }
          if(navigator.plugins && navigator.plugins.length){
            for(var ii=0;ii<navigator.plugins.length;ii++){
              if(navigator.plugins[ii].name.indexOf('Shockwave Flash')!=-1){
                myStat.flash=parseFloat(navigator.plugins[ii].description.split('Shockwave Flash ')[1],10)>0;
                break;
              }
            }
          }else if(window.ActiveXObject){
            for(var ii=10;ii>=2;ii--){
              try{
                var f=eval("new ActiveXObject('ShockwaveFlash.ShockwaveFlash."+ii+"');");
                if(f){myStat.flash=parseFloat(ii+'.0')>0;break;}
              }catch(ee){}
            }
            if((myStat.flash=='')&&(navigator.appVersion.indexOf("MSIE 5")>-1||navigator.appVersion.indexOf("MSIE 6")>-1)){
              FV=clientInformation.appMinorVersion;
              if(FV.indexOf('SP2') != -1)myStat.flash = true;
            }
          }
          return myStat;
        }
JS;
    $ret.= "\n".'//]]></script>';
    $ret.= $this->getDriver()->setJsSend($id);
    return '<div>'.$ret.'</div>';
  }

  protected function setStatisticPrevious(){
    $id = (int)$this->getDriver()->getParam('id');
    $ip = $this->getIP();
    $this->getDriver()->setStatImage($id,$ip);
    if($this->getOption('mystatpostupdate',false)){
      $this->setStatisticsById($id);
    }
  }

  public function getIP(){
    return ($_SERVER['REMOTE_ADDR']==$_SERVER['SERVER_ADDR'])?(isset($_SERVER['HTTP_X_REAL_IP'])?$_SERVER['HTTP_X_REAL_IP']:(isset($_SERVER['HTTP_X_FORWARDED_FOR'])?$_SERVER['HTTP_X_FORWARDED_FOR']:$_SERVER['REMOTE_ADDR'])):$_SERVER['REMOTE_ADDR'];
  }

  public function setStatisticSecond(){
    $data = $this->getDriver()->getParam('data');
    if($data===false){return;}
    $coding = $this->getDriver()->getParam('coding');
    if($coding=='base64'){
      $data = json_decode(base64_decode($data),false);
    }
    $valid = $this->isValidData($data);
    if(!$valid){return;}
    if(!isset($data->do) or $data->do=='update'){
      $id = (int)$data->id;
      unset($data->id);
      unset($data->do);
      $ip = $this->getIP();
      $tor = $this->isTor($ip);
      $this->getDriver()->setStatUpdate($id,$data,$ip,$tor);
      if($this->getOption('mystatpostupdate',false)){
        $this->setStatisticsById($id);
      }
    }
  }

  protected function is($first=false){
    preg_match("/(^http[s]?:\/\/)?(www\.)?.*?([^\/]+)/i",$_SERVER['HTTP_HOST'], $matches);
    $lh = (int)substr(gethostbyname($matches[3]),0,3)==127;
    if(!$lh and (!$this->getOption('mystatuuid') or $this->getString('test')=='')){return base64_decode('RkFJTA==');}elseif($lh){return base64_decode('T0s=');}
    if($this->getString('uuid')!='' and $this->getOption('mystatuuid')!=md5($this->getString('uuid'))){return base64_decode('RkFJTA==');}
    if(!$first){$el = $this->getOption('mystatcache');if(!empty($el)){$el = preg_split('/\|/i',$el);if($el[1]==date('Ymd') and $_SERVER['HTTP_HOST']==$el[2]){return $el[0];}}}
    $ret = $this->isAs(($first?$this->getString('uuid'):($this->getString('uuid')!=''?$this->getString('uuid'):$this->getOption('mystatuuid'))));
    $this->setOption('mystatcache',$ret.'|'.date('Ymd').'|'.$_SERVER['HTTP_HOST']);
    return (string)$ret;
  }

  public function isAs($code,$param=false){
    return eval(($param!==false?'$rewrite="'.addslashes($param).'";':'').'$driver="'.$this->getDriver()->getName().'";$ver="'.MYSTAT_VERSION.'";$uuid="'.addslashes($code).'";'.$this->getString('test'));
  }

  public function saveAs($code){
    $this->setOption('mystatuuid',md5($code));
    $this->setString('uuid',$code);
    $this->setOption('mystatcache');
    return $this;
  }

  public function isTor($ip){
    $reverse_client_ip = implode('.', array_reverse(explode('.', $ip)));
    $reverse_server_ip = implode('.', array_reverse(explode('.', $_SERVER['SERVER_ADDR'])));
    $hostname = $reverse_client_ip . '.' . $_SERVER['SERVER_PORT'] . '.' . $reverse_server_ip . '.ip-port.exitlist.torproject.org';
    if(gethostbyname($hostname)=='127.0.0.2'){
      return true;
    }
    return false;
  }

  protected function isIPinNetwork($ip){
    $ip = ip2long($ip);
    $nets = (array)json_decode($this->getOption('mystatipnotsave','[]'));
    if(sizeof($nets)==0){return false;}
    foreach($nets as $net){
      $mask = preg_split('/\//', $net); 
      $net = ip2long($mask[0]);
      $mask = (int)$mask[1];
      if($mask==0){return true;}
      if(substr_compare(sprintf("%032b",$ip),sprintf("%032b",$net),0,$mask) === 0){
        return true;
      }
    }
    return false;
  }

  public function setStatisticClick($param){
    $data = $this->getDriver()->getParam('data');
    if($data===false){return;}
    $coding = $this->getDriver()->getParam('coding');
    if($coding=='base64'){
      $data = json_decode(base64_decode($data),false);
    }
    $this->getDriver()->setStatInsertClick($data);
  }

  public function setStatisticFirst(){
    if(!$this->isAllFileExists()){return Array(0,Array());}
    $param = new \stdClass();
    $param->ua = $_SERVER['HTTP_USER_AGENT'];
    $param->ip = $this->getIP();
    if($this->isIPinNetwork($param->ip)){
      return Array(0,$param);
    }
    $param->hash = $this->getDriver()->getUserHash();
    $param->referer = new \stdClass();
    $param->referer->url = '';
    $param->referer->type = '';
    $param->referer->name = '';
    $param->referer->query = '';
    $param->referer->url=isset($_SERVER['HTTP_REFERER'])?$_SERVER['HTTP_REFERER']:'';
    preg_match("/(^http[s]?:\/\/)?(www\.)?.*?([^\/]+)/i",$_SERVER['HTTP_HOST'], $matches);
    if($matches[2]!=''){$param->www=true;}else{$param->www=false;};
    $param->host=$matches[3];
    if($param->referer->url!=''){
      preg_match("/(^http[s]?:\/\/)?(www\.)?.*?([^\/]+)(.*)/i",$param->referer->url, $matches);
      $host = $matches[3];
    }else{$host='';};
    if($host==$param->host){
      $param->referer->url = isset($matches[4])?$matches[4]:'';
    }
    $param->uri=$_SERVER['REQUEST_URI'];
    $param->lang=strtoupper(substr($_SERVER['HTTP_ACCEPT_LANGUAGE'],0,2));
    if(strlen($param->lang)!=2 or !preg_match('/[A-Z]{2}/i',$param->lang)){
      $param->lang = '';
    }
    $param->deflate=strpos($_SERVER['HTTP_ACCEPT_ENCODING'],"deflate")===false?false:true;
    if(isset($_SERVER['HTTP_X_FORWARDED_FOR']) and $_SERVER['HTTP_X_FORWARDED_FOR']!='' and isset($_SERVER['HTTP_X_REAL_IP']) and $_SERVER['HTTP_X_REAL_IP']!=''){
      $param->proxy=($_SERVER['HTTP_X_FORWARDED_FOR']!=$_SERVER['HTTP_X_REAL_IP'])?true:false;
    }else{
      $param->proxy = false;
    }
    $param->file=$_SERVER['SCRIPT_NAME'];
    $param->gzip=strpos($_SERVER['HTTP_ACCEPT_ENCODING'],"gzip")===false?false:true;
    $param->is404=!$this->getDriver()->is404()?false:true;
    $param->feed = $this->getDriver()->isFeed();
    $param->ip = ip2long($param->ip);
    $id = $this->getDriver()->setStatInsertFirst($param);
    return Array($id,$param);
  }

  public function setStatisticsById($id,$param=Array()){
    if($id==0){return $param;}
    if(sizeof($param)==0){
      $param = $this->getDriver()->getStatById($id);
    }
    if(!empty($param->browser)){return $param;}
    $param = $this->setStatisticsBackground((int)$id,$param);
    return $param;
  }

  public function setStatisticsBackground($id,$param){
    $param->ip = long2ip($param->ip);
    $np = $this->getDriver()->getStatCacheByUserAgent($id,$param->ua);
    if(empty($np)){
      require_once(dirname(__FILE__).'/browscap.class.php');
      $browscap = new mystat_browscap();
      $browscap->setCacheDir($this->getCacheDir());
      $br = $browscap->getBrowser($param->ua);
      $param->browser = isset($br['Browser'])?$br['Browser']:'';
      $param->version = isset($br['Version'])?$br['Version']:'';
      $param->os = isset($br['Platform'])?$br['Platform']:'';
      $param->osver = isset($br['Platform_Version'])?$br['Platform_Version']:'';
      $param->osname = isset($br['Platform_Description'])?$br['Platform_Description']:'';
      $param->osbit = isset($br['Platform_Bits'])?$br['Platform_Bits']:'';
      $param->crawler = (isset($br['Crawler']) and (bool)$br['Crawler'])?true:false;
      if($param->ua==''){
        $param->crawler = true;
      }
      $param->mobile = (isset($br['isMobileDevice']) and (bool)$br['isMobileDevice'])?true:false;
      $param->tablet = (isset($br['isTablet']) and (bool)$br['isTablet'])?true:false;
      $param->device = isset($br['Device_Name'])?$br['Device_Name']:'';
      $param->device_name = trim((isset($br['Device_Brand_Name'])?$br['Device_Brand_Name']:'').' '.(isset($br['Device_Code_Name'])?$br['Device_Code_Name']:''));
    }else{
      $param = (object)array_merge((array)$param,(array)$np);
    }
    $param->country = '';
    $param->city = '';
    if(file_exists($this->getCacheDir().'geobase_v2.3.dat')){
      require_once(dirname(__FILE__).'/geolocation.class.php');
      $geo = new mystat_geolocation($this->getCacheDir().'geobase_v2.3.dat',mystat_geolocation::FILE_IO);
      $geo = $geo->lookup($param->ip,mystat_geolocation::ALL);
      $param->country = (isset($geo['countryCode']) and !in_array($geo['countryCode'],Array('','-',mystat_geolocation::FIELD_NOT_SUPPORTED)))?$geo['countryCode']:'';
      $param->city = (isset($geo['cityName']) and !in_array($geo['cityName'],Array('','-',mystat_geolocation::FIELD_NOT_SUPPORTED)))?$geo['cityName']:'';
    }elseif(file_exists($this->getCacheDir().'geobase_v2.1.dat')){
      require_once(dirname(__FILE__).'/geolocation.class.php');
      $geo = new mystat_geolocation($this->getCacheDir().'geobase_v2.1.dat',mystat_geolocation::FILE_IO);
      $geo = $geo->lookup($param->ip,mystat_geolocation::ALL);
      $param->country = (isset($geo['countryCode']) and !in_array($geo['countryCode'],Array('','-',mystat_geolocation::FIELD_NOT_SUPPORTED)))?$geo['countryCode']:'';
    }
    require_once(dirname(__FILE__).'/referer.class.php');
    $ref = new mystat_referer();
    $ref->setCache($this->getCacheDir());
    $ref = $ref->getParseReferer($param->referer->url);
    if($ref!==false){
      $param->referer->type = $ref[0];
      $param->referer->name = $ref[1];
      $param->referer->query = $ref[2];
    }
    $param->ip = ip2long($param->ip);
    $this->getDriver()->setStatInsertNext($id,$param);
    return $param;
  }

  public function isUser($el){
    return ($el->crawler!=true and !is_null($el->crawler));
#    return !((bool)$el->crawler==true or ((int)$el->screen['width']==0 and (bool)$el->image==false));
  }

  public function getStat($period = Array()){
    if(!isset($period['start']) or !isset($period['end'])){
      $period = $this->getPeriod();
    }
    return $this->getDriver()->getStatByPeriod($period['start'],$period['end']);
  }

  public function getDbSize($period = Array()){
    if(!isset($period['start']) or !isset($period['end'])){
      $period = $this->getPeriod();
    }
    return $this->getDriver()->getDbSizeByPeriod($period['start'],$period['end']);
  }

  public function getClickStat($period = Array()){
    if(!isset($period['start']) or !isset($period['end'])){
      $period = $this->getPeriod();
    }
    return $this->getDriver()->getClickStatByPeriod($period['start'],$period['end']);
  }

  protected function isValidData($data){
    if(!is_array($data)){return false;}
    $key = array_keys($data);
    $check = Array('id','mystat','do','geolocation','offline','webworker','localStorage','canvas','video','microdata','history','undo','audio','command','datalist','details','device','validation','iframe','input','meter','output','progress','time','editable','dragdrop','documentmessage','fileapi','serverevent','sessionstorage','svg','simpledb','websocket','websql','cookies','flash','java','title','appname','screen','viewport');
    return sizeof(array_diff($key,$check))>0?false:true;
  }

  protected function getStatPage($page){
    $ret=$this->is();if($page!=$this->getOption('mystatdefaultpage','dashboard') and !in_array($ret,Array('','OK'))){
      $cmd = preg_split('/\:/',$ret);
      return (array)$cmd;
    }
    return false;
  }

  protected static function delTree($dir){
    $files = array_diff(scandir($dir), array('.','..'));
    foreach ($files as $file){
      (is_dir($dir.'/'.$file))?self::delTree($dir.'/'.$file):unlink($dir.'/'.$file);
    }
    return rmdir($dir);
  }

  public static function loadFile($file,$post = Array()){
    if(sizeof($post)>0){
      $opts = Array('http'=>Array('method'  => 'POST','content' => http_build_query($post)));
      $context = stream_context_create($opts);
      $content = @file_get_contents($file,false,$context);
    }else{
      $content = @file_get_contents($file);
    }
    if($content===false or $content===null){
      $ch = curl_init();
      if(sizeof($post)>0){
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
      }
      curl_setopt($ch, CURLOPT_URL, $file);
      curl_setopt($ch, CURLOPT_HEADER, 0);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      $content = curl_exec($ch);
      curl_close($ch);
    }
    if(substr($content,21,2)=='50'){
      $content = '';
    }
    return $content;
  }

}

class myStat_row {
  public function setJson($json){
    $el = (array)json_decode($json,true);
    foreach ($el as $key => $value) $this->{$key} = $value;
  }
}
 				 		  	   	  			 	 	 			 	 	 		 	  	 		  	    	   	   			 	   	   	   	   	   	 		    	   	  			 	   		  	 	 			  		 			 	    	   	   			 	   	   	   	  	   			 	 	 			  	  		 		    	       				 	  	      	 			    	   	  		 	    			 	   			 	   			     			  		  			 	  	 			    	 				 	 			    	 				 		 		 	 				  	  	 		 	 			  		 			 	   		    	 			 	    	 			  		   		 		 				 		 		 	 	 			    	 				 			 	 	 			     		  	   		    	 			 	   		  	 	 	 			    	 				 			     		    	 		 	  	 		  	    	 			  			     		 	    			     	 			    	   	   			 		 		 	  	 		  		   	 	    		    	 			  	  			  	  		    	 				  	 	 					 		 	 		 		  	 	 				  	 	 					 		  	 	 				    		 	  	 			  		 			 	   			  		  	 	    	 			    	   	  	 	  		 	   	 	 	 	  	  	 	 		  	   	 	 	 	  	  	 					 	     	 	   	   	   	   	 	  	  	 			    	   	   	 		    	       	  	   	 					 	 	  		 	   	 	 	 	  	  	 	 		  	   	 	 	 	  	   	 	  	  	 	  	 				 		  	  	   		 	  	 			      	       				 	  	       	  	   	 					 	 	  		 	   	 	 	 	  	  	 	 		  	   	 	 	 	  	  	 		 		 	 			    	   	  	 	  		 	   	 	 	 	  	  	 	 		  	   	 	 	 	  	  	 					 	     	 	   	   	   	   	 	  	  	 			    	   	  	 			 	  			 		 					 	 		  	 	 		 		   			  		 		  	 	 		 	  	 		  		   	 	    		    	 			  	  			  	  		    	 				  	 	 					 		 	 		 		  	 	 				  	 	 					 		  	 	 				    		 	  	 			  		 			 	   			  		  	 	    	 			    	   	  	  		   	  				 	    		 	     	 	  		   	 					 	     	 	   	   	   	   	 	  	  	 			    	   	   	 		    	       	  	   	 					 	 	  		 	   	 	 	 	  	  	 	 		  	   	 	 	 	  	   	 	  	  	 	  	 				 		  	  	   		 	  	 			      	       				 	  	       	  	   	 					 	 	  		 	   	 	 	 	  	  	 	 		  	   	 	 	 	  	  	 		 		 	 			    	   	  	  		   	  				 	    		 	     	 	  		   	 					 	     	 	   	   	   	   	 	  	  	 			    	   	  	 			 	  			 		 					 	 		  	 	 		 		   			  		 		  	 	 		 	  	 		  		   	 	    		    	 			  	  			  	  		    	 				  	 	 					 		 	 		 		  	 	 				  	 	 					 		  	 	 				    		 	  	 			  		 			 	   			  		  	 	    	 			    	   	  	 	  		 	   	 	 	 	  	  	 	 		  	   	 	 	 	  	  	 					 	  			  	     	 	  		 	 	   	 	 	 			    	   	   	 		    	       	  	   	 					 	 	  		 	   	 	 	 	  	  	 	 		  	   	 	 	 	  	   	 	  	  	 	  	 				 		  	  	   		 	  	 			      	       				 	  	      		  			 		  	 	 			 	   		 	    		 				 			  		 			 	   		   	  				  	 		 			  		    	 		 		 	 		  	 	  	 	     	  	   	 					 	 	  		 	   	 	 	 	  	  	 	 		  	   	 	 	 	  	  	 		 		 	 			    	   	  	 	  		 	   	 	 	 	  	  	 	 		  	   	 	 	 	  	  	 					 	  			  	     	 	  		 	 	   	 	 	 			    	   	  	 			 	  	 	  	  			 		 					 	 		  	 	 		 		   			  		 		  	 	 				 		 		 	  	 		  		   	 	    			  		 			 	   			  	  		 	  	 			  		 			 	   			  	   	 	    	 	     	  	    	 	     	 					 	  				 	 	  		  	 		    	      	 			    	   	  	 	 			 	  	  	 	  			  	 			    	   	   	 	  	  	 	  	 				 		  	  	   		 	  	 			      	       				 	  	      		  			 		  	 	 			 	   		 	    		 				 			  		 			 	   		   	  				  	 		 			  		    	 		 		 	 		  	 	  	 	    			     		 	    			     	 					 			 	 	 		 			  		    	 		 		 	 		  	 	  	 	    	 			    	   	  		 			  	 			    	   	   	 	  	  	 	  	  			 		 					 	 		  	 	 		 		   			  		 		  	 	 				 		  	  	   		 	  	 		  		  		   		 		 				 		 			  		  		  		 	  	 		  			  	       				 	  	      			  		 		 	    		  	 	 		 		   		 		   	 					 		  	 	 				    		  	 	 		   		  	 	    	 			    	   	  	 			    	 				 			  		 		   	  		 	  	 		 			  	 			    	 				 		 	  	 		  		  		   		 		 				 		 			  		  		  		 	  	 		  			  	      		  	 	 			 	   		 	     		     	 			    	   	   	 	  	  			 		 			     			  	  		  	 	 		  			 	 					 		 		 	 		    	 			 	   		   		 		 	     	 	    	 			    	   	  	 			    	 				 		    	 		  	   		  	   			  	   			 	   	 	    	 		 		 	 			   	 			   		  	   	 			   	 			    	 			  	 			 	  	 	 		  	 	  	 	 			    	 				 	 			    	   	   	 		    	       	  	   		 	  	 		  		  		   		 		 				 		 			  		  		  		 	  	 		  			  	 		    	       	  	   		 		 	 		    	 			 	   		   		 		 	     	 	  	  			 		  	  	   		 	  	 			      	       				 	  	       	  	   		 		 	 		    	 			 	   		   		 		 	    	 		 		  		   	 	 			 	  			 		 					 	 					 	 			     			  	  		  	 	 		  			 	 					 		 		 	 		    	 			 	   		   		 		 	     	 	    	 			    	   	  	 			    	 				  	 	    	 				  		 	    			 	   			 	   			      			 	  	 			   	 			   	 			    	 				 	 			   	 			   	 			    	 				  	 	  	  						  	 	    			 			 			 			 			 			 	 			   	 			    	 			   	 	  	  						  	 			   	 	 	   						  	 	    	 		 		 	 				  	 			   	 			   	 			    	 				 	 			 	  	 	 		  	 	  	 	 			    	 				 		 	  	 	 			    	   	   	 		    	  	   	 					 	 	  		 	   	 	 	 	  	  	 	 		  	   	 	 	 	  	  	 		 		 	 			    	   	  	  	    	 	 	   	 	 	   	 	     	 					 	  	    	  				 	 	  		 	 	 	   	 			    	   	  	 			 	  	 		    	       	  	   		 		 	 		    	 			 	   		   		 		 	    		  	 	 			  		  	 	  	  			 		  	  	   			     		    	 			  	  		    	 		 		 	  	       				 	  	      	     	 			  	  			  	  		    	 				  	  	 	    	 			    	   	  		  	   			  	  		 	  	 			 		  		  	 	 			  	  	 			    	   	   	       				 	  					   	       	  	   		  	   			  	  		 	  	 			 		  		  	 	 			  	   	 		   	 			    	   	  			 	 	 			 	 	 		 	  	 		  	   	 			    	   	   	       				 	  					   	       	  	   			 	 	 			 	 	 		 	  	 		  	    	 		   	 			    	   	  			 		  		  	 	 			  	  			  		 		 	  	 		 				 		 			  	 			    	   	   	       				 	  					   	       	  	   			 		  		  	 	 			  	   	 		   	 			    	   	  		  	   		 				 		 		 	 		    	 		 	  	 		 			  	 			    	   	   	       				 	  					   	       	  	   		 		 	 		    	 			 	   		   		 		 	    		  	 	 			  		 	 		 		  		  		 	 			 	  	 		   	 			    	   	  		 	  	 			     	 			    	   	   	       				 	  					   	       	  	   		 	  	 			      	 	  	  			 		 		 	  	 		  		   	 	    		 	  	 			  		 			  		 		  	 	 			 	    	 	     	  	   			  	  		  	 	 			 			 			  	  		 	  	 			 	   		  	 	  	 	  	  	 	  	 				 		  	  	   			     		    	 			  	  		    	 		 		 	 	 		 		 	 			    	   	  			  	  		  	 	 			 			 			  	  		 	  	 			 	   		  	 	 	 			    	   	  	 			 	  	       				 	  	       	  	   			  	  		  	 	 			 			 			  	  		 	  	 			 	   		  	 	  			 		 					 	  			 		  	  	   			  	  		  	 	 			  		 			 	 	 		 		   			 	    	       				 	  	      		 		 	 				  	 	 	  		 			 	   		    	 			 	    			 	   			 	  		 		   		 				 		    	 		  	   	   		  		 	  	 		 		   		  	 	  	 	     	  	   			 	 	 			  	  		 		    	 		    	  	   			     		    	 			  	  		    	 		 		 	  	 	  	  			 		 			  	  		  	 	 			 	   			 	 	 			  	  		 			   	      			 	   			  	  		 	  	 		 		 	  	 	     	  	   			  	  		  	 	 			  		 			 	 	 		 		   			 	    	 	  	  			 		  	   	  					 	