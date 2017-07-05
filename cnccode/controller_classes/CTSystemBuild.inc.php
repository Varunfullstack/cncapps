<?php
/**
* System Build controller class
* CNC Ltd
*
* @access public
* @authors Karim Ahmed - Sweet Code Limited
*/
require_once($cfg['path_bu'].'/BUSalesOrder.inc.php');
require_once($cfg['path_bu'].'/BUPDFSystemBuild.inc.php');
require_once($cfg['path_ct'].'/CTCNC.inc.php');
// Messages
define('CTSYSTEMBUILD_MSG_ORDER_NOT_FND', 'Sales Order not found');
// Actions
define('CTSYSTEMBUILD_ACT_SELECT', 'select');
define('CTSYSTEMBUILD_ACT_GENERATE', 'generate');
class CTSystemBuild extends CTCNC {
	function CTSystemBuild($requestMethod,	$postVars, $getVars, $cookieVars, $cfg){
		$this->constructor($requestMethod, $postVars, $getVars, $cookieVars, $cfg);
	}
	function constructor($requestMethod,	$postVars, $getVars, $cookieVars, $cfg){
		parent::constructor($requestMethod,	$postVars, $getVars, $cookieVars, $cfg, "", "", "", "");
		$this->buSalesOrder=new BUSalesOrder($this);
	}
	/**
	* Route to function based upon action passed
	*/
	function defaultAction()
	{
		switch ($_REQUEST['action']){
			case CTSYSTEMBUILD_ACT_GENERATE:
				$this->generate();
				break;
			case CTSYSTEMBUILD_ACT_SELECT:
				$this->select();
				break;
			default:
				$this->select();
				break;
		}
	}
	/**
	* Display search form
	* @access private
	*/
	function select()
	{
		$this->setMethodName('select');
		$urlSubmit = $this->buildLink(
				$_SERVER['PHP_SELF'],
				array(
					'action' => CTSYSTEMBUILD_ACT_GENERATE
				)
			);

		$this->setPageTitle('System Build Report');
		$this->setTemplateFiles	('SystemBuild',  'SystemBuild.inc');
		$this->template->set_var(
			array(
				'ordheadID' => Controller::htmlDisplayText($_REQUEST['ordheadID']),
				'urlSubmit' => $urlSubmit
			)
		);
		// display results
		$this->template->parse('CONTENTS', 	'SystemBuild', true);
		$this->parsePage();
	}
	function generate(){
		$this->setMethodName('generate');
		if ($_REQUEST['ordheadID'] == ''){
			$this->setFormErrorMessage('Sales Order No Required');
			$this->select();
			exit();
		}
		if (!is_numeric($_REQUEST['ordheadID'])){
			$this->setFormErrorMessage('Sales Order Must Be Numeric');
			$this->select();
			exit();
		}
		$buSalesOrder = new BUSalesOrder($this);
		if (!$buSalesOrder->getOrdheadByID($_REQUEST['ordheadID'], $dsOrdhead)){
			$this->setFormErrorMessage('Sales Order Not Found');
			$this->select();
			exit();
		}
		$buSalesOrder->getOrderWithCustomerName($_REQUEST['ordheadID'], $dsOrdhead, $dsOrdline, $dsDeliveryContact);
		// generate PDF report:
		$buPDFSystemBuild = new BUPDFSystemBuild(
			$this,
			$dsOrdhead,
			$dsOrdline,
			$dsDeliveryContact
		);
		$pdfFile = $buPDFSystemBuild->generateFile();
		header('Pragma: public');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Content-Type: application/pdf');
		header('Content-Disposition: attachment; filename=systembuild.pdf;' );
		header('Content-Transfer-Encoding: binary');
		header('Content-Length: '.filesize($pdfFile));
		readfile($pdfFile);
		unlink($pdfFile);
		exit();
	}
}// end of class
?>