<?php
require_once("config.inc.php");
require_once($cfg['path_ct'].'/CTCNC.inc.php');
require_once( $_SERVER['DOCUMENT_ROOT'] . '/.config.php' );

require_once( CONFIG_PATH_CNC_CLASSES .	'customer.php' );

page_open(
	array(
		'sess' => PHPLIB_CLASSNAME_SESSION,
		'auth' => PHPLIB_CLASSNAME_AUTH,
		'perm' => PHPLIB_CLASSNAME_PERM,
		''
	)
);

header("Cache-control: private");

class CTCustomers extends CTCNC {
	function __construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg){
		$this->constructor($requestMethod, $postVars, $getVars, $cookieVars, $cfg);
	}
	function constructor($requestMethod,	$postVars, $getVars, $cookieVars, $cfg){
		parent::constructor($requestMethod,	$postVars, $getVars, $cookieVars, $cfg, "", "", "", "");
	}
	function defaultAction()
	{
//		$this->checkPermissions(PHPLIB_PERM_TECHNICAL);
		$business = & new CNC_Customer();
		$class_name = get_class( $business );
		$display_fields_tag 	= $class_name . '_display_fields';

		$_REQUEST['show_edit']=1;
		$_REQUEST['show_fields']=1;
		$_REQUEST['show_order_by']=1;

		if ( !isset( $_SESSION[$display_fields_tag] ) ){
			$_REQUEST['display_fields'] =
				array(
					'customer.cus_name' => 1
				);
		}

		$_REQUEST['filter'] = array('customer.modifyDate' => '0000-00-00 00:00:00');

		$_REQUEST['show_filters']=1;
		$_REQUEST['show_page_views']=0;
		$_REQUEST['edit_url']='Customer.php?action=dispEdit&customerID=';
		$this->setMethodName('defaultAction');
// Parameters
		$this->setPageTitle("Customers");
		ob_start();
//		$_REQUEST['where_statement'] = 'ordhead.odh_type IN (\'I\',\'P\',\'C\')';
//		$_REQUEST['order_by'] = 'customer.cus_name';
		require( CONFIG_PATH_SC_HTML .	'page_list.php' );

		$contents = ob_get_contents();

		ob_end_clean();
		$this->setTemplateFiles('');
		$this->template->set_var( 'CONTENTS', $contents);
		$this->parsePage();
	}
}

GLOBAL $cfg;
$ctCustomers= new CTCustomers(
	$_SERVER['REQUEST_METHOD'],
	$_POST,
	$_GET,
	$_COOKIE,
	$cfg
);

$ctCustomers->execute();

page_close();
?>