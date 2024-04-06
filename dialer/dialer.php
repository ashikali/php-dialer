#!/usr/bin/php
<?php 
require_once "libs/init.php";
require_once "phpagi-asmanager.php" ;

class Dialer extends Common_Utils {

	var $ami;
	function Dialer() {

                $this->db_conn();
                $this->init_log("dialer.log","Dialer");
		$manager = Array( "username" => "cti_service","secret" => "cti_service" );
		$this->ami = new AGI_AsteriskManager(NULL,$manager);
		$this->ami->connect();
		$this->ami->Events("Off");
		
	}
	function clear_pending() {

		$query = "UPDATE callouts SET status='Q' WHERE status='P'";
		$this->db->genQuery($query);

	}
	function check_concurrent() {

		$query = "SELECT id FROM callouts WHERE status='P'";
		$records = $this->db->fetchTable($query);
		return count($records);

	}
	function main() {
		while(true) {

			$day  = date("D");
			$hour = date("H");
			$concurrent = $this->check_concurrent();

			#if( in_array($day,$this->dial_days) and in_array($hour,$this->dial_times) and $concurrent < 1 ) {
			if( $concurrent < 1 ) {

				if(DIALER_LOG)
					$this->log->log("Concurrent Checking Finished...taking records for dialing");
			        					
				$sql= "SELECT id,number_to_dial
				       FROM   callouts  
				       WHERE 
				              status='Q' and scheduled_on <= now()  ORDER BY id LIMIT {$this->concurrent}";
				if(DIALER_LOG)
					$this->log->log($sql);
				$results =  $this->db->fetchTable($sql,true);
				if(DIALER_LOG)
					$this->log->log("<<<<<<<<<<<<<<<".count($results).">>>>>>>>>>>");
				foreach($results as $in => $number){
                                                
					 $originate = [ "Channel" => "local/DIAL@dialer-context",
                                                       "Exten" => "ANSWERED",
                                                       "Context" => "dialer-context",
                                                       "Priority" => "1",
                                                       "CallerID" => "Emergency<asdf>",
                                                       "Variable" => "dial_number={$number['number_to_dial']},ref_no={$number['id']}",
                                                       "Async" => "True" ];
                                         $res = $this->ami->Originate($originate);
					 if($res["Response"] == "Success") {
						$sql= "UPDATE callouts SET status='P',dialed_on=now() WHERE id={$number['id']}";
						$this->db->genQuery($sql);
					}
				if(DIALER_LOG)
					$this->log->log("Dialing {$number['number_to_dial']}  => {$res["Response"]}");
				}
			}
			sleep(5);
		}

	}

} 

$dialer = new Dialer();
$dialer->clear_pending();
$dialer->main();

/*
 function Originate($channel,
                       $exten=NULL, $context=NULL, $priority=NULL,
                       $application=NULL, $data=NULL,
                       $timeout=NULL, $callerid=NULL, $variable=NULL, $account=NULL, $async=NULL, $actionid=NULL)
array(2) {
  ["Response"]=>
  string(7) "Success"
  ["Message"]=>
  string(29) "Originate successfully queued"
}


Q in queue
P taken for process
F Finished

$dial_days = Array( "Sun","Mon","Tue","Wed","Thu");
$dial_times =  Array( "8","9","10","11","12","13","14","15","16","17");
$concurrent = 5;

id
uniqueid
ticket_no
created_on    
closed_on     
number_to_dial
tries         
scheduled_on  
status        
message       
question1     
question2     


*/

?>
