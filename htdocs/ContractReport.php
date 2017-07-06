<?php
require_once("config.inc.php");
require_once($cfg['path_ct'].'/CTCNC.inc.php');

require_once( $_SERVER['DOCUMENT_ROOT'] . '/.config.php' );
require_once( CONFIG_PATH_CNC_CLASSES .	'contract_report.php' );

page_open(
	array(
		'sess' => PHPLIB_CLASSNAME_SESSION,
		'auth' => PHPLIB_CLASSNAME_AUTH,
		'perm' => PHPLIB_CLASSNAME_PERM,
		''
	)
);

header("Cache-control: private");

class CTContractReport extends CTCNC {
	function CTContractReport($requestMethod,	$postVars, $getVars, $cookieVars, $cfg){
		$this->constructor($requestMethod, $postVars, $getVars, $cookieVars, $cfg);
	}
	function constructor($requestMethod,	$postVars, $getVars, $cookieVars, $cfg){
		parent::constructor($requestMethod,	$postVars, $getVars, $cookieVars, $cfg, "", "", "", "");
	}
	function defaultAction()
	{
//		$this->checkPermissions(PHPLIB_PERM_TECHNICAL);
		$business = & new CNC_ContractReport();
		$_REQUEST['show_edit']=0;
		$_REQUEST['show_fields']=1;
		$_REQUEST['show_filters']=1;
		$_REQUEST['show_page_views']=1;
		$_REQUEST['show_order_by']=1;
//		$_REQUEST['where_statement'] =
//			'customer.cus_custno<>'.CONFIG_SALES_STOCK_CUSTOMERID . CR .
//			'AND custitem.cui_expiry_date <> "0000-00-00"';

//		$_REQUEST['page_view.page_view_id']=2;
		$_REQUEST['edit_url']='CustomerItem.php?action=displayRenewalContract&customerItemID=';
		$this->setMethodName('defaultAction');
// Parameters
		$this->setPageTitle("Contract Report");
		ob_start();
		require( CONFIG_PATH_SC_HTML .	'page_list.php' );

		$contents = ob_get_contents();

		ob_end_clean();
		$this->setTemplateFiles('');
		$this->template->set_var( 'CONTENTS', $contents);
		$this->parsePage();
	}
}

GLOBAL $cfg;
$ctContractReport= new CTContractReport(
	$_SERVER['REQUEST_METHOD'],
	$_POST,
	$_GET,
	$_COOKIE,
	$cfg
);

$ctContractReport->execute();

page_close();
?>