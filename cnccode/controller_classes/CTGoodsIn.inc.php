<?php
/**
* Goods Inwards controller class
* CNC Ltd
*
* @access public
* @authors Karim Ahmed - Sweet Code Limited
*/
require_once($cfg['path_bu'].'/BUPurchaseOrder.inc.php');
require_once($cfg['path_bu'].'/BUGoodsIn.inc.php');
require_once($cfg['path_bu'].'/BUPDFPurchaseOrder.inc.php');
require_once($cfg['path_bu'].'/BUSupplier.inc.php');
require_once($cfg['path_ct'].'/CTCNC.inc.php');
require_once($cfg['path_gc'].'/DataSet.inc.php');
require_once($cfg['path_dbe'].'/DSForm.inc.php');
// Messages
define('CTGOODSIN_MSG_PURCHASEORDER_NOT_FND', 'Purchase Order not found');
define('CTGOODSIN_MSG_PORHEADID_NOT_PASSED', 'porheadID not passed');
define('CTGOODSIN_MSG_SEQNO_NOT_PASSED', 'sequence no not passed');
define('CTGOODSIN_MSG_ORDLINE_NOT_FND', 'order line not found');
// Actions
define('CTGOODSIN_ACT_DISP_SEARCH', 'dispSearch');
define('CTGOODSIN_ACT_RECEIVE', 'recieve');
// Page text
class CTGoodsIn extends CTCNC {
	var $dsDateRange='';
	var $buPurchaseOrder='';
	var $buGoodsIn='';
	var $dsPorhead='';
//	var $dsPorline='';
	var	$orderTypeArray=array(
		"I" => "Initial",
		"P" => "Part Received",
		"B" => "Both Initial & Part Received",
		"C" => "Completed",
		"A" => "Authorised"
	);
  /**
   * Dataset for Purchase Order record storage.
   *
   * @var     DSForm
   * @access  private
   */
	function CTGoodsIn($requestMethod,	$postVars, $getVars, $cookieVars, $cfg){
		$this->constructor($requestMethod, $postVars, $getVars, $cookieVars, $cfg);
	}
	function constructor($requestMethod,	$postVars, $getVars, $cookieVars, $cfg){
		parent::constructor($requestMethod,	$postVars, $getVars, $cookieVars, $cfg, "", "", "", "");
		$this->buPurchaseOrder=new BUPurchaseOrder($this);
		$this->buGoodsIn=new BUGoodsIn($this);
		$this->dsPorhead = new DSForm($this);
		$this->dsPorline = new DSForm($this);
		$this->dsPorline->copyColumnsFrom($this->buPurchaseOrder->dbeJPorline);
		$this->dsPorhead->copyColumnsFrom($this->buPurchaseOrder->dbeJPorhead);
	}
	/**
	* Route to function based upon action passed
	*/
	function defaultAction()
	{
		switch ($_REQUEST['action']){
			case CTCNC_ACT_SEARCH:
				$this->search();
				break;
			case CTGOODSIN_ACT_DISP_SEARCH:
				$this->displaySearchForm();
				break;
			case CTCNC_ACT_DISPLAY_GOODS_IN:
				$this->displayGoodsIn();
				break;
			case CTGOODSIN_ACT_RECEIVE:
				$this->receive();
				break;
			default:
				$this->displaySearchForm();
				break;
		}
	}
	/**
	* Run search based upon passed parameters
	* Display search form with results
	* @access private
	*/
	function search()
	{
		$this->setMethodName('search');
		// remove trailing spaces from params passed
		foreach ($_REQUEST as $key => $value){
			$_REQUEST[$key] = trim($value);
		}
		if(($_REQUEST['porheadID']!='') AND (!is_numeric($_REQUEST['porheadID']))){
			$this->setFormErrorMessage('Order no must be numeric');;
		}
		if ($this->getFormError() == 0){
			$this->buGoodsIn->search(
				$_REQUEST['supplierID'],
				$_REQUEST['porheadID'],
				'',
				'',
				'B',						// initial and part receieved only
				'',
				$this->dsPorhead
			);
		}
		if ($this->dsPorhead->rowCount() == 1){
			$this->dsPorhead->fetchNext();
			$urlNext =
				$this->buildLink($_SERVER['PHP_SELF'],
					array(
						'action'=>CTCNC_ACT_DISPLAY_GOODS_IN,
						'porheadID' => $this->dsPorhead->getValue('porheadID')
					)																												
				);
			header('Location: ' . $urlNext);
			exit;
		}
		else{
			$this->setAction(CTGOODSIN_ACT_DISP_SEARCH);
			$this->displaySearchForm();
		}
	}
	/**
	* Display the results of order search
	* @access private
	*/
	function displaySearchForm()
	{
		$this->setMethodName('displaySearchForm');
		$this->setTemplateFiles	('GoodsInSearch', 'GoodsInSearch.inc');
// Parameters
		$this->setPageTitle("Goods In");
		$submitURL =$this->buildLink($_SERVER['PHP_SELF'],array('action'=>CTCNC_ACT_SEARCH));
		$urlSupplierPopup =
			$this->buildLink(
				CTCNC_PAGE_SUPPLIER,
				array(
					'action' => CTCNC_ACT_DISP_SUPPLIER_POPUP,
					'htmlFmt' => CT_HTML_FMT_POPUP
				)
			);
		$this->dsPorhead->initialise();
		if($this->dsPorhead->rowCount()>0){
			$this->template->set_block('GoodsInSearch','orderBlock', 'orders');
			$supplierNameCol = $this->dsPorhead->columnExists('supplierName');
			$typeCol = $this->dsPorhead->columnExists('type');
			$customerNameCol = $this->dsPorhead->columnExists('customerName');
			$porheadIDCol = $this->dsPorhead->columnExists('porheadID');
			$supplierRefCol = $this->dsPorhead->columnExists('supplierRef');
			$printedCol = $this->dsPorhead->columnExists('printed');
			$ordheadIDCol = $this->dsPorhead->columnExists('ordheadID');
			while ($this->dsPorhead->fetchNext()){
				$goodsInURL = 
					$this->buildLink(
						$_SERVER['PHP_SELF'],
						array(
							'action'=>CTCNC_ACT_DISPLAY_GOODS_IN,
							'porheadID'=>$this->dsPorhead->getValue($porheadIDCol)
						)
					);
				$customerName = $this->dsPorhead->getValue($customerNameCol);
				$supplierName = $this->dsPorhead->getValue($supplierNameCol);
				$this->template->set_var(
					array(
						'listCustomerName' => $customerName,
						'listSupplierName' => $supplierName,
						'listGoodsInURL' => $goodsInURL,
						'listPorheadID' => $this->dsPorhead->getValue($porheadIDCol),
						'listOrderType' => $this->orderTypeArray[$this->dsPorhead->getValue($typeCol)],
						'listSupplierRef' => $this->dsPorhead->getValue($supplierRefCol)//,
					)
				);
				$this->template->parse('orders', 'orderBlock', true);
			}
		}
// search parameter section
		if ($_REQUEST['supplierID']!=''){
			$buSupplier = new BUSupplier($this);
			$buSupplier->getSupplierByID($_REQUEST['supplierID'], $dsSupplier);
			$supplierName = $dsSupplier->getValue('name');
		}
		else{
			$supplierName = '';
		}
		$this->template->set_var(
			array(
				'supplierName' => $supplierName,
				'porheadID' => $_REQUEST['porheadID'],
				'supplierID' => $_REQUEST['supplierID'],
				'submitURL' => $submitURL,
				'urlSupplierPopup' => $urlSupplierPopup
			)
		);
		$this->template->parse('CONTENTS', 	'GoodsInSearch', true);
		$this->parsePage();
	}
	/**
	* Display the results of order search
	* @access private
	*/
	function displayGoodsIn()
	{
		$this->setMethodName('displayGoodsIn');
		$dsPorhead = & $this->dsPorhead;
		$dsPorline = & $this->dsPorline;
		if ($_REQUEST['porheadID']==''){
			$this->displayFatalError(CTGOODSIN_MSG_PORHEADID_NOT_PASSED);
			return;
		}
		$this->buPurchaseOrder->getHeaderByID($_REQUEST['porheadID'], $dsPorhead);
		$dsPorhead->fetchNext();
		$this->buPurchaseOrder->getLinesByID($dsPorhead->getValue('porheadID'), $dsPorline);
		// determine whether we should be asking for serial no and warranty for any items on this
		// order. e.g. There is a sales order and addItem flag is set. 
		if ($dsPorhead->getValue('ordheadID') != 0){
			$buSalesOrder = new BUSalesOrder($this);
			$buSalesOrder->getOrdheadByID($dsPorhead->getValue('ordheadID'), $dsOrdhead);
			$addCustomerItems = ($dsOrdhead->getValue('addItem') == 'Y');
		}
		else{
			$addCustomerItems = FALSE;
		}
		if (!$this->getFormError()) {

			/*
			If the customer is an internal stock location then update the appropriate stock level
			*/
			if ($dsPorhead->getValue('supplierID') == CONFIG_SALES_STOCK_SUPPLIERID){
				$this->buGoodsIn->getInitialStockReceieveQtys(CONFIG_SALES_STOCK_CUSTOMERID, $dsPorline, $this->dsGoodsIn);
			}
			else if	($dsPorhead->getValue('supplierID') == CONFIG_MAINT_STOCK_SUPPLIERID){
				$this->buGoodsIn->getInitialStockReceieveQtys(CONFIG_MAINT_STOCK_CUSTOMERID, $dsPorline, $this->dsGoodsIn);
			}
			else{
				$this->buGoodsIn->getInitialReceieveQtys($dsPorline, $this->dsGoodsIn, $addCustomerItems);      
			}
		}
		$porheadID = $dsPorhead->getValue('porheadID');
		$orderType= $dsPorhead->getValue('type');
		$this->setPageTitle('Goods In');
		$this->setTemplateFiles	(	array('GoodsInDisplay' =>  'GoodsInDisplay.inc'));

		$urlReceive =
			$this->buildLink(
				$_SERVER['PHP_SELF'],
				array(
					'action'=>CTGOODSIN_ACT_RECEIVE,
					'porheadID'=>$porheadID
				)
			);

		$urlPurchaseOrder =
			$this->buildLink(
				CTCNC_PAGE_PURCHASEORDER,
				array(
					'action' => CTCNC_ACT_DISPLAY_PO,
					'porheadID'=> $porheadID
				)	
			);

		$this->template->set_var(
			array(
				'porheadID' => $porheadID,
				'supplierName' => $dsPorhead->getValue('supplierName'),
				'customerName' => $dsOrdhead->getValue('customerName'),
				'ordheadID' => $dsPorhead->getValue('ordheadID'),
				'customerID' => $dsOrdhead->getValue('customerID'),
				'urlReceive' => $urlReceive,
				'urlPurchaseOrder' => $urlPurchaseOrder
			)
		);

		if ($addCustomerItems){
			$this->buGoodsIn->getAllWarranties($dsWarranty);
		}
	
		$dsPorline->initialise();
		$this->dsGoodsIn->initialise();
		if ($this->dsGoodsIn->rowCount() > 0){
			$this->template->set_block('GoodsInDisplay','warrantyBlock', 'warranties'); // innermost first
			$this->template->set_block('GoodsInDisplay','orderLineBlock', 'orderLines');
			while ($this->dsGoodsIn->fetchNext()){
				$this->template->set_var(
					array(
						'description'=> Controller::htmlDisplayText($this->dsGoodsIn->getValue("description")),
						'sequenceNo' => $this->dsGoodsIn->getValue('sequenceNo'),
						'orderSequenceNo' => $this->dsGoodsIn->getValue('orderSequenceNo')
					)
				);
				$this->template->set_var(
					array(
						'qtyOrdered' => number_format($this->dsGoodsIn->getValue("qtyOrdered"), 1, '.', ''),
						'itemID' => $this->dsGoodsIn->getValue("itemID"),
						'partNo' => Controller::htmlDisplayText($this->dsGoodsIn->getValue("partNo")),
						'qtyOS' =>  number_format($this->dsGoodsIn->getValue("qtyOS"),1,'.',''),
						'qtyToReceive' => $this->dsGoodsIn->getValue("qtyToReceive"),
						'serialNo' => $this->dsGoodsIn->getValue("serialNo"),
						'requireSerialNo' => $this->dsGoodsIn->getValue("requireSerialNo"),
						'allowReceive' => $this->dsGoodsIn->getValue("allowReceive"),
						'renew' => $this->dsGoodsIn->getValue("renew") ? CT_CHECKED : '',
						'customerItemID' => $this->dsGoodsIn->getValue("customerItemID"),
					)
				);
				if ($this->dsGoodsIn->getValue('requireSerialNo')){
					$this->template->set_var('DISABLED', '');
					// There is a warranty drop-down for each line
					$dsWarranty->initialise();
					$thisWarrantyID = $this->dsGoodsIn->getValue('warrantyID');
					while ($dsWarranty->fetchNext()){
						$this->template->set_var(
							array(
								'warrantyDescription' => $dsWarranty->getValue('description'),
								'warrantyID' => $dsWarranty->getValue('warrantyID'),
								'warrantySelected' => ( $thisWarrantyID == $dsWarranty->getValue('warrantyID')) ? CT_SELECTED : ''
							)
						);
						$this->template->parse('warranties', 'warrantyBlock', true);
					} // while ($dsWarranty->fetchNext()

        }
				else{
					$this->template->set_var('DISABLED', 'disabled'); // no serial no or warranty
				}					

				if ($this->dsGoodsIn->getValue('allowReceive') == FALSE){
					$this->template->set_var('lineDisabled', 'disabled'); // entry of anything!
				}					
				else{
					$this->template->set_var('lineDisabled', '');
				}
				
				$this->template->parse('orderLines', 'orderLineBlock', true);
				$this->template->set_var('warranties', ''); // clear for next line
			} // while ($dsPorline->fetchNext())
		}// if ($dsPorline->rowCount() > 0)
		$this->template->parse('CONTENTS', 	'GoodsInDisplay', true);
		$this->parsePage();
	}
	/**
	* Perform receive
	* @access private
	*/
	function receive()
	{
		$dsGoodsIn = & $this->dsGoodsIn;
		$this->buGoodsIn->initialiseReceiveDataset($dsGoodsIn);
		if (!isset($_REQUEST['porheadID'])){
			$this->displayFatalError(CTGOODSIN_MSG_PORHEADID_NOT_PASSED);
		}
		if (!$dsGoodsIn->populateFromArray($_REQUEST['receive'])){
			$this->setFormErrorMessage('Quantitites entered must be numeric');
			$this->displayGoodsIn();
			exit;
		}
		if (!$this->buGoodsIn->validateQtys($dsGoodsIn)){
			$this->setFormErrorMessage('Quantitites to receive must not exceed outstanding quantities');
			$this->displayGoodsIn();
			exit;
		}
		if (!$this->buGoodsIn->validateSerialNos($dsGoodsIn)){
			$this->setFormErrorMessage('Please complete the serial numbers');
			$this->displayGoodsIn();
			exit;
		}
		if (!$this->buGoodsIn->validateWarranties($dsGoodsIn)){
			$this->setFormErrorMessage('Please select warranties for all items');
			$this->displayGoodsIn();
			exit;
		}
		
		$this->buGoodsIn->receive($_REQUEST['porheadID'], $this->userID, $dsGoodsIn);
		$this->buPurchaseOrder->getHeaderByID($_REQUEST['porheadID'], $dsPorhead);
		$urlNext =
		$this->buildLink(
			CTCNC_PAGE_PURCHASEORDER,
			array(
				'action'=>CTCNC_ACT_DISPLAY_PO,
				'porheadID' => $_REQUEST['porheadID']
			)																												
		);
		header('Location: ' . $urlNext);
		exit;
	}
}// end of class
?>