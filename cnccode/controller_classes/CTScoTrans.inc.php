<?
/**
* SCO transaction controller class
* CNC Ltd
*
* @access public
* @authors Karim Ahmed - Sweet Code Limited
*/
require_once($cfg['path_gc'].'/DataSet.inc.php');
require_once($cfg['path_bu'].'/BUScoTrans.inc.php');
require_once($cfg['path_gc'].'/Controller.inc.php');
class CTScoTrans extends Controller{
	var $buScoTrans='';
	function CTScoTrans($requestMethod,	$postVars, $getVars, $cookieVars, $cfg){
		$this->constructor($requestMethod, $postVars, $getVars, $cookieVars, $cfg);
	}
	function constructor($requestMethod,	$postVars, $getVars, $cookieVars, $cfg){
		$this->Controller($requestMethod,	$postVars, $getVars, $cookieVars, $cfg, "", "", "", "");
		$this->buScoTrans=new BUScoTrans($this);
	}
	function initialProcesses(){
		$this->retrieveHTMLVars();
	}
	/**
	* Route to function based upon action passed
	*/
	function defaultAction()
	{
		$this->process();
	}
	/**
	* Display the initial form that prompts the employee for details
	* @access private
	*/
	function process()
	{
		$this->setMethodName('process');
		$this->buScoTrans->processTransactionsIn();
		$this->buScoTrans->processTransactionsOut();
		$this->buScoTrans->processSCOOrders();
	}
}// end of class
?>