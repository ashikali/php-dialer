#!/usr/bin/php
<?php 
set_include_path(get_include_path().PATH_SEPARATOR.'/var/www/html/');
require_once "libs/paloSantoDB.class.php";
require_once "Log.php";

$dsn = "mysql://asterisk:asterisk@localhost/GDialer";

$pDB = new paloDB($dsn);

function init_log($file_name,$name) {

        $log_dir = "/var/lib/asterisk/agi-bin/GDialer/log/";
        $log_conf = array('append' => true,'timeFormat' => '%X %x','mode' => "0644") ;
        return Log::singleton("file", $log_dir.$file_name,$name,$log_conf);
}

$log = init_log("clear_pending.log","clear");


function clear_pending() {

	global $pDB,$log;

	$sql = "SELECT *  
		FROM callouts  
		WHERE status='P' 
		AND TIMESTAMPDIFF(MINUTE,dialed_on,now()) > 14";
		
	$records = $pDB->fetchTable($sql);
	if(is_array($records) and count($records) > 0 ){
		$log->log(print_r($records,true));
		$sql = "UPDATE callouts SET status='Q' 
			WHERE status='P' AND TIMESTAMPDIFF(MINUTE,dialed_on,now()) > 14";
		$pDB->genQuery($sql);
	}

}
clear_pending();
/*
 select TIMESTAMPDIFF(minute,dialed_on,now()) from callouts where id=370442; */



?>
