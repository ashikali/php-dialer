<?php
/* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Elastix version 0.5                                                  |f
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
  $Id: new_campaign.php $ */

class paloSantoReportsBreak {

    private $_DB;
    var $errMsg;
    
    function paloSantoReportsBreak(&$pDB)     {

        // Se recibe como parámetro una referencia a una conexión paloDB
        if (is_object($pDB)) {
            $this->_DB =& $pDB;
            $this->errMsg = $this->_DB->errMsg;
        } else {
            $dsn = (string)$pDB;
            $this->_DB = new paloDB($dsn);

            if (!$this->_DB->connStatus) {
                $this->errMsg = $this->_DB->errMsg;
            }
        }
    }

    function getReportesBreak($fecha_init,$fecha_end,$status_choosed,$limit,$offset)  {


	$fecha_init .= " 00:00:00";
	$fecha_end  .= " 23:59:59";

        $query1 = "SELECT id,number_to_dial,identity,card_no,scheduled_on,dialed_on,message,tries,
			  IF(status='F','Finished',IF(status='P','Dialing','In Queue')),answer1
		   FROM callouts 
		   WHERE scheduled_on >= '$fecha_init' AND scheduled_on <= '$fecha_end'";
        $query2 = "SELECT id,number_to_dial,identity,card_no,scheduled_on,dialed_on,message,tries,
			  IF(status='F','Finished',IF(status='P','Dialing','In Queue')),answer1
		   FROM finished_callouts 
		   WHERE  scheduled_on >= '$fecha_init' AND scheduled_on <= '$fecha_end'";

	if(!empty($status_choosed) ) {
		$query1 .= " AND status='{$status_choosed}'";
		$query2 .= " AND status='{$status_choosed}'";
	}

	$query = "($query1) UNION ALL ($query2) ORDER BY scheduled_on" ;

	if(!empty($limit)) 
               $query  .= " LIMIT $limit OFFSET {$offset}"; 


        $recordset = $this->_DB->fetchTable($query);
        if (!is_array($recordset)) {
            $this->errMsg = $this->_DB->errMsg;
            return NULL;
        }


        return $recordset;
    }

    
}
?>
