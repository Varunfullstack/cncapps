<?php
/**
* Expense controller class
* CNC Ltd
*
* @access public
* @authors Karim Ahmed - Sweet Code Limited
*/
require_once($cfg['path_ct'].'/CTCNC.inc.php');
class CTGSCBalances extends CTCNC {
	function CTGSCBalances($requestMethod,	$postVars, $getVars, $cookieVars, $cfg){
		$this->constructor($requestMethod, $postVars, $getVars, $cookieVars, $cfg);
	}
	function constructor($requestMethod,	$postVars, $getVars, $cookieVars, $cfg){
		parent::constructor($requestMethod,	$postVars, $getVars, $cookieVars, $cfg, "", "", "", "");
	}
	/**
	* Route to function based upon action passed
	*/
	function defaultAction()
	{
		$this->setPageTitle('GSC Balances');
		// direct all output to buffer
		ob_start(); 
		// put contents
		include('gsc_balances.php');
		$contents = ob_get_contents();
		ob_clean();
		$this->template->set_var('CONTENTS',  $contents);
		$this->parsePage();
	}
}// end of class
?>