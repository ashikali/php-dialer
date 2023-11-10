<?php
  /* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Elastix version 2.0.0-54                                               |
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
  $Id: index.php,v 1.1 2010-12-02 08:12:41 Alberto Santos asantos.palosanto.com Exp $ */
//include elastix framework
require_once "libs/paloSantoForm.class.php";
require_once "libs/paloSantoDB.class.php";
require_once "libs/paloSantoGrid.class.php";
require_once "libs/misc.lib.php";


function _moduleContent(&$smarty, $module_name) {  

    include_once "modules/$module_name/configs/default.conf.php";
    include_once "modules/$module_name/libs/paloSantoReportsBreak.class.php";

    global $arrLang;
    global $arrConf;
    $arrConf = array_merge($arrConf,$arrConfModule);
    // Obtengo la ruta del template a utilizar para generar el filtro.
    $base_dir = dirname($_SERVER['SCRIPT_FILENAME']);
    $templates_dir=(isset($arrConf['templates_dir']))?$arrConf['templates_dir']:'themes';
    $local_templates_dir="$base_dir/modules/$module_name/".$templates_dir.'/'.$arrConf['theme'];

    // Obtengo el idioma actual utilizado en la aplicacion.
    $Language = get_language();
    // Include language file for EN, then for local, and merge the two.
    $arrLangModule = NULL;
    include_once("modules/$module_name/lang/en.lang");
    $arrLangModule_file="modules/$module_name/lang/$Language.lang";
    if (file_exists("$base_dir/$arrLangModule_file")) {
        $arrLanEN = $arrLangModule;
        include_once($arrLangModule_file);
        $arrLangModule = array_merge($arrLanEN, $arrLangModule);
    }
    $arrLang = array_merge($arrLang, $arrLangModule);


    // Abrir conexión a la base de datos
    $pDB = new paloDB($arrConf["dialer_dsn"]);
    if (!is_object($pDB->conn) || $pDB->errMsg!="") {
        $smarty->assign("mb_title", _tr("Error"));
        $smarty->assign("mb_message", _tr("Error when connecting to database")." ".$pDB->errMsg);
        return NULL;
    }

    // Cadenas estáticas a asignar
    $smarty->assign(array(
        "btn_consultar" =>  _tr('query'),
        "module_name"   =>  $module_name,
    ));


    //actions
    $action = getAction();
    $content = "";

    switch($action){
        default:
            $content = reportReportsBreak($smarty, $module_name, $local_templates_dir, $pDB, $arrConf, $arrLangModule, $arrLang);
            break;
    }
    return $content;
}

function reportReportsBreak($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf, $arrLangModule, $arrLang)
{
    // Obtener rango de fechas de consulta. Si no existe, se asume día de hoy
    $sFechaInicio = date('d M Y');
    if (isset($_GET['txt_fecha_init'])) $sFechaInicio = $_GET['txt_fecha_init'];
    if (isset($_POST['txt_fecha_init'])) $sFechaInicio = $_POST['txt_fecha_init'];
    $sFechaFinal = date('d M Y');
    if (isset($_GET['txt_fecha_end'])) $sFechaFinal = $_GET['txt_fecha_end'];
    if (isset($_POST['txt_fecha_end'])) $sFechaFinal = $_POST['txt_fecha_end'];

    $oReportsBreak = new paloSantoReportsBreak($pDB);

    $comboStatus = Array(""  => "All",
			 "Q" => "In Queue",
			 "P" => "Dialing",
			 "F" => "Finished" 
			);
    $status_choosed = "";
    if (isset($_GET['status_choosed'])) $status_choosed  = $_GET['status_choosed'];
    if (isset($_POST['status_choosed'])) $status_choosed =  $_POST['status_choosed'];
    if (!in_array($status_choosed,array_keys($comboStatus))) $status_choosed = "";
	
    $arrFilterExtraVars = array(
        "txt_fecha_init"    => $sFechaInicio,
        "txt_fecha_end"     => $sFechaFinal,
	"status_choosed"        => $status_choosed
    );

    $arrFormElements = createFieldFilter($comboStatus );
    $oFilterForm = new paloForm($smarty, $arrFormElements);
    // Validación de las fechas recogidas
    if (!$oFilterForm->validateForm($arrFilterExtraVars)) {
        $smarty->assign("mb_title", _tr("Validation Error"));
        $arrErrores=$oFilterForm->arrErroresValidacion;
        $strErrorMsg = '<b>'._tr('The following fields contain errors').'</b><br/>';
        foreach($arrErrores as $k => $v) {
            $strErrorMsg .= "$k, ";
        }
        $smarty->assign("mb_message", $strErrorMsg);

        $arrFilterExtraVars = array(
            "txt_fecha_init"    => date('d M Y'),
            "txt_fecha_end"     => date('d M Y'),
            "status_choosed"     => $status_choosed
        );        
    }

    // Obtener fechas en formato yyyy-mm-dd
    $sFechaInicio = translateDate($arrFilterExtraVars['txt_fecha_init']);
    $sFechaFinal = translateDate($arrFilterExtraVars['txt_fecha_end']);


    $arrColumnas = array(

	#	ag.number,ag.name,qlog.time,qlog.queuename,qlog.callid,qlog.data2,qlog.agent,qlog.event	
        _tr('id'),
        _tr('Mobile No'),
        _tr('Identity'),
        _tr('Card No'),
        _tr('Scheduled On' ),
        _tr('Last Dial' ),
        _tr('Message' ),
        _tr('Tries'),
        _tr('Status' ),
        _tr('Answer' )
    );
	
    $oGrid = new paloSantoGrid($smarty);
    $bExportando = $oGrid->isExportAction(); //existed in misc library
    $offset = 0;$limit = NULL;
    $datosBreaks = $oReportsBreak->getReportesBreak($sFechaInicio, $sFechaFinal,$status_choosed,$limit,$offset);
    $totalBreaks = count($datosBreaks);
	

    $limit = 50;	
    if($bExportando) $limit = $totalBreaks;
    $oGrid->setLimit($limit);
    $oGrid->setTotal($totalBreaks);
    $offset = $oGrid->calculateOffset();
    if(!$bExportando)
	    $arrData = $oReportsBreak->getReportesBreak($sFechaInicio, $sFechaFinal,$status_choosed,$limit,$offset);
    else
	$arrData = &$datosBreaks;


    $htmlFilter = $oFilterForm->fetchForm("$local_templates_dir/filter.tpl", "", $arrFilterExtraVars);
    //begin grid parameters
    $oGrid->enableExport();   //enable export.
    $oGrid->showFilter($htmlFilter);
    $oGrid->setURL(construirURL($arrFilterExtraVars));
    $oGrid->setData($arrData);
    $oGrid->setColumns($arrColumnas);
    $oGrid->setTitle(_tr("Dialer Report"));
    $oGrid->pagingShow(true);
    $oGrid->setNameFile_Export(_tr("Dialer Report"));
    $smarty->assign("SHOW", _tr("Show"));
    return  $oGrid->fetchGrid();
}

function createFieldFilter(&$comboStatus) {


    
    $arrFormElements = array
    (
        "txt_fecha_init"  => array
        (
            "LABEL"                     => _tr('Start Date'),
            "REQUIRED"                  => "yes",
            "INPUT_TYPE"                => "DATE",
            "INPUT_EXTRA_PARAM"         => "",
            "VALIDATION_TYPE"           => "ereg",
            "VALIDATION_EXTRA_PARAM"    => "^[[:digit:]]{1,2}[[:space:]]+[[:alnum:]]{3}[[:space:]]+[[:digit:]]{4}$"
        ),
        "txt_fecha_end"  => array
        (
            "LABEL"                     => _tr('End Date'),
            "REQUIRED"                  => "yes",
            "INPUT_TYPE"                => "DATE",
            "INPUT_EXTRA_PARAM"         => "",
            "VALIDATION_TYPE"           => "ereg",
            "VALIDATION_EXTRA_PARAM"    => "^[[:digit:]]{1,2}[[:space:]]+[[:alnum:]]{3}[[:space:]]+[[:digit:]]{4}$"
        ),
	'status_choosed'     =>  array(
            'LABEL'                     =>  _tr('Status'),
            'REQUIRED'                  =>  'no',
            'INPUT_TYPE'                =>  'SELECT',
            'INPUT_EXTRA_PARAM'         =>  $comboStatus,
            'VALIDATION_TYPE'           =>  'ereg',
            'VALIDATION_EXTRA_PARAM'    =>  '^[FQP]$'
        ),
	
    );
    return $arrFormElements;
}

function getAction() {

    return "report"; 

}

?>
