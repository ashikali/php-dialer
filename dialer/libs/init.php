<?php
$palo_libs = "/var/www/html/libs" ;
$ast_libs = "/var/lib/asterisk/agi-bin" ;
set_include_path(get_include_path() . PATH_SEPARATOR . $palo_libs . PATH_SEPARATOR . $ast_libs);

require_once "paloSantoDB.class.php";
require_once "Log.php";

$dsn = "mysql://asterisk:asterisk@localhost/dialer";

$dial_days = Array("Sat","Sun","Mon","Tue","Wed","Thu","Fri");
$dial_times =  Array("9","10","11","12","13","14","15","16","17","18","19","20","21");
$concurrent = 10;

define('DIALER_LOG',TRUE);

class Common_Utils {

        var $db,$log;
        function db_conn() {
                global $dsn,$dial_days,$dial_times,$concurrent,$launch_ivr;
                $this->db = new paloDB($dsn);
		$this->dial_days = $dial_days;
		$this->dial_times = $dial_times;
		$this->concurrent = $concurrent;
        }
        function init_log($file_name,$name) {

                 $log_dir = "/var/lib/asterisk/agi-bin/dialer/logs/";
                 $log_conf = array('append' => true,'timeFormat' => '%X %x','mode' => "0644") ;
                 $this->log = Log::singleton("file", $log_dir.$file_name,$name,$log_conf);
        }
	
}
