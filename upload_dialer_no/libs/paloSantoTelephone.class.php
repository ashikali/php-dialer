<?php
/* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Elastix version 1.0                                                  |
  | http://www.elastix.org                                               |
  +----------------------------------------------------------------------+
  | Copyright (c) 2006 Palosanto Solutions S. A.                         |
  +----------------------------------------------------------------------+
  | Cdla. Nueva Kennedy Calle E 222 y 9na. Este                          |
  | Telfs. 2283-268, 2294-440, 2284-356                                  |
  | Guayaquil - Ecuador                                                  |
  | http://www.palosanto.com                                             |
  +----------------------------------------------------------------------+
  | The contents of this file are subject to the General Public License  |
  | (GPL) Version 2 (the "License"); you may not use this file except in |
  | compliance with the License. You may obtain a copy of the License at |
  | http://www.opensource.org/licenses/gpl-license.php                   |
  |                                                                      |
  | Software distributed under the License is distributed on an "AS IS"  |
  | basis, WITHOUT WARRANTY OF ANY KIND, either express or implied. See  |
  | the License for the specific language governing rights and           |
  | limitations under the License.                                       |
  +----------------------------------------------------------------------+
  | The Original Code is: Elastix Open Source.                           |
  | The Initial Developer of the Original Code is PaloSanto Solutions    |
  +----------------------------------------------------------------------+
  $Id: index.php,v 1.1 2008/01/30 15:55:57 a_villacis Exp $ */
class paloSantoTelephone
{
    private $_DB;
    var $errMsg,$records,$lang;
    
    function __construct(&$pDB,&$lang)     {

        // Se recibe como parámetro una referencia a una conexión paloDB
        if (is_object($pDB)) {
            $this->_DB =& $pDB;
            $this->errMsg = $this->_DB->errMsg;
	    $this->lang = $lang;
        } else {
            $dsn = (string)$pDB;
            $this->_DB = new paloDB($dsn);

            if (!$this->_DB->connStatus) {
                $this->errMsg = $this->_DB->errMsg;
                // debo llenar alguna variable de error
            } else {
                // debo llenar alguna variable de error
            }
        }
    }

   function uploadFile($file) {


       $response = $this->parseExcel($file);

       if(count($this->records) > 0 ) {

		$tmp_file = "/tmp/".uniqid().".xlsx";	
		if(move_uploaded_file($file,$tmp_file))
			$_SESSION["upload_file"] = $tmp_file;
       		return true;
       }else{

	  $this->errMsg = "Error: Unable to recognize file record";
	  return false;

       }

   }

  function check_mobile($number) {

	if(preg_match("/^966/",$number))
			$number = "00".$number;

	if(!preg_match("/^0/",$number))
			$number = "0".$number;

	if(!preg_match("/^[0-9]+$/",$number))
			return false; 

	return $number;

  }

  function parseExcel($file)  {


	if(!file_exists($file))
		return false;

	$worksheets = Array();$all_records = Array();
	$filetype = PHPExcel_IOFactory::identify($file);	
	$objReader = PHPExcel_IOFactory::createReader($filetype);
	$objPHPExcel = $objReader->load($file);
	
	
	foreach ($objPHPExcel->getWorksheetIterator() as $worksheet) {
	    $worksheets[] = $worksheet->toArray();
	}

	foreach($worksheets as $s_in => $records ){

		foreach($records as $in => $record) {
			if($temp = $this->check_mobile($records[$in][0])){
				$records[$in][0] = $temp; //mobile no. 
				$all_records[] = $records[$in];
			}
		}

	}
	$this->records = &$all_records;
	return true;

  }

  function saveFile($file) {
  
	$response = $this->parseExcel($file);
	if(!$response)
		return false;


	foreach($this->records as $in => $record ) {

		$query = "INSERT INTO callouts(number_to_dial,identity,card_no,scheduled_on)
                                  VALUES('{$record[0]}','{$record[1]}','{$record[2]}',now())";
		$this->_DB->genQuery($query);


	}

	return true;

   }
   function clear_file() {

	$file = $_SESSION["upload_file"];
	unlink($file);
	unset($_SESSION["upload_file"]);
		

   }

}
    
