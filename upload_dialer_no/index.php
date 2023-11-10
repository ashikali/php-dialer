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

function _moduleContent(&$smarty, $module_name) {
    require_once("modules/$module_name/configs/default.conf.php" );
    require_once("modules/$module_name/libs/paloSantoTelephone.class.php");
    require_once("libs/phpexcel/Classes/PHPExcel.php");
    require_once("libs/paloSantoGrid.class.php");

	
    load_language_module($module_name);
    
    //global variables
    global $arrConf;
    global $arrConfModule;
    $arrConf = array_merge($arrConf,$arrConfModule);

    //folder path for custom templates
    $base_dir = dirname($_SERVER['SCRIPT_FILENAME']);
    $templates_dir = (isset($arrConf['templates_dir'])) ? $arrConf['templates_dir'] : 'themes';
    $local_templates_dir = "$base_dir/modules/$module_name/".$templates_dir.'/'.$arrConf['theme'];
    $arrData = Array();

       // se conecta a la base
    $pDB = new paloDB($arrConf["dialer_dsn"]);
    if(!empty($pDB->errMsg)) {
        $smarty->assign("mb_message", _tr("Error when connecting to database")."<br/>".$pDB->errMsg);
    }

    global $arrLangModule;

    $pTele = new paloSantoTelephone($pDB,$arrLangModule);

    
    switch (trim(getParameter('action'))) {
    	case 'Upload':
    	    upload($smarty,$pTele);
	     break;
	case 'Save':
	     save($smarty,$pTele);
		break;
	case 'Cancel':
		$pTele->clear_file();
    	}
    return display_form($smarty, $module_name, $local_templates_dir,$pTele);
}

function display_form(&$smarty, &$module_name, &$local_templates_dir,&$pTele) {



    require_once "libs/paloSantoForm.class.php";

    $smarty->assign( Array(
        'MODULE_NAME'       =>  $module_name,
        'FILE'        =>  _tr("File"),
        'UPLOAD'      =>       _tr('Upload '),
	'SAVE'		    => _tr('Save    '),
	'CANCEL'	    => _tr('Cancel ')
    ));
    
    $oForm = new paloForm($smarty, array());
    $oGrid = new paloSantoGrid($smarty);
    $oGrid->setTotal(count($pTele->records));
    $oGrid->setLimit(count($pTele->records));
    $oGrid->enableExport();
    $html_filter = $oForm->fetchForm("$local_templates_dir/filter.tpl", _tr('Upload Mobile No'), $_POST);
    $oGrid->showFilter($html_filter);
    $oGrid->setURL(construirURL($_POST)); 
    $oGrid->setData($pTele->records);
    $oGrid->setColumns(Array( "Mobile No","Identity","Card No" ));
    $oGrid->setTitle("Upload Telephone");
    return $oGrid->fetchGrid();
}
function save(&$smarty,&$pTele)  {

	
	$result = $pTele->saveFile($_SESSION["upload_file"]);	
	$pTele->clear_file();
	if($result) {
        	$smarty->assign("mb_title", _tr('Success'));
        	$smarty->assign("mb_message", _tr('Successfully Saved and Scheduled'));
		return true;
	}
        $smarty->assign("mb_title", _tr('Validation Error'));
        $smarty->assign("mb_message", _tr('Failed to upload'));

}
function upload(&$smarty,&$pTele) {


    if (!preg_match('/.xls.?$/', $_FILES['excelfile']['name'])) {
        $smarty->assign("mb_title", _tr('Validation Error'));
        $smarty->assign("mb_message", _tr('Invalid file extension.- It must be xls'));
	return ;
    }
    if (!is_uploaded_file($_FILES['excelfile']['tmp_name'])) {
        $smarty->assign("mb_title", _tr('Error'));
        $smarty->assign("mb_message", _tr('Possible file upload attack. Filename') ." :". $_FILES['csvfile']['name']);
	return ;
    }

    $response = $pTele->uploadFile($_FILES['excelfile']['tmp_name']);

    if(!$response){
        $smarty->assign("mb_title", _tr('Error'));
        $smarty->assign("mb_message", $pTele->errMsg." :". $_FILES['excelfile']['name']);
    }


}
?>
