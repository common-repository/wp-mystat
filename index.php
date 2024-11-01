<?php
if(!defined('MYSTAT_VERSION')){
  define('MYSTAT_VERSION','3.12');
}
require_once(dirname(__FILE__).'/lib/mystat.class.php');
if(!defined('MYSTAT_NOT_RUN')){
  global $mystat;
  $mystat = new myStat();
  $mystat->run();
}
