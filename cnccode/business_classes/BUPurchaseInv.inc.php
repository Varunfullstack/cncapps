<?php
/**
 * PurchaseInv business class
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once ($cfg ["path_gc"] . "/Business.inc.php");
require_once ($cfg ["path_dbe"] . "/DBEJPorhead.inc.php");
require_once ($cfg ["path_dbe"] . "/DBEPorhead.inc.php");
require_once ($cfg ["path_dbe"] . "/DBEPorline.inc.php");
require_once ($cfg ["path_dbe"] . "/DBEPorlineTotals.inc.php");
require_once ($cfg ["path_dbe"] . "/DBEJPorline.inc.php");
require_once ($cfg ["path_dbe"] . "/DBEItem.inc.php");
require_once ($cfg ['path_dbe'] . '/DBEPurchaseInv.inc.php');
require_once ($cfg ['path_dbe'] . '/DBEPaymentTerms.inc.php');
require_once ($cfg ["path_dbe"] . "/DBEStockcat.inc.php");
require_once ($cfg ["path_dbe"] . "/DBECustomerItem.inc.php");
require_once ($cfg ["path_bu"] . "/BUSalesOrder.inc.php");
require_once ($cfg ["path_dbe"] . "/DBEWarranty.inc.php");
require_once ($cfg ["path_bu"] . "/BUPurchaseOrder.inc.php");
require_once ($cfg ["path_bu"] . "/BURenewal.inc.php");
require_once ($cfg ["path_func"] . "/Common.inc.php");
class BUPurchaseInv extends Business {
	var $dbePorhead = '';
	var $dbePorline = '';
	var $dbePurchaseInv = '';
	var $dbeItem = '';
	var $dbeStockcat = '';
	var $purchaseInvoiceNo = '';
	var $purchaseInvoiceDate = '';
	var $buPurchaseOrder = '';
	var $dbeCustomerItem = '';
	var $userID = '';
	/**
	 * Constructor
	 * @access Public
	 */
	function BUPurchaseInv(&$owner) {
		$this->constructor ( $owner );
	}
	function constructor(&$owner) {
		parent::constructor ( $owner );
		$this->dbePorhead = new DBEPorhead ( $this );
		$this->dbePorline = new DBEPorline ( $this );
		$this->dbeJPorline = new DBEJPorline ( $this );
		$this->dbeJPorhead = new DBEJPorhead ( $this );
	}
	/**
	 * search for all rows other than authorised or supplier is an internal stock location
	 * (Sales or Maint stock)
	 */
	function search($supplierID, $porheadID, $supplierRef, $lineText, &$dsResults) {
		$this->setMethodName ( 'search' );
		$dbeJPorhead = new DBEJPorhead ( $this );
		if ($porheadID != '') { // get one row
			$dbeJPorhead->setValue ( 'porheadID', $porheadID );
			$dbeJPorhead->getPurchaseInvoiceRow ( $porheadID );
			$ret = ($this->getData ( $dbeJPorhead, $dsResults ));
		}
		else{																		// use search criteria passed
			$ret= $dbeJPorhead->getRowsBySearchCriteria(
				trim($supplierID),
				trim($ordheadID),
				'',							
				trim($supplierRef),
				trim($lineText),
				'', //fromdate
'', //todate
				'',
				'PI'
			);
			$dbeJPorhead->initialise ();
			$dsResults = $dbeJPorhead;
		}
		return $ret;
	}
	function getInitialValues(&$dsPorhead, &$dsPorline, &$dsPurchaseInv, $addCustomerItems) {
		$this->initialiseDataset ( $dsPurchaseInv );
		$dbeItem = new DBEItem ( $this );
		$dsPorline->initialise ();
		$sequenceNo = 0;
		
		$dsPorline->initialise ();
		while ( $dsPorline->fetchNext () ) {
			$itemID = $dsPorline->getValue ( 'itemID' );
			if ($dsPorhead->getValue ( 'directDeliveryFlag' ) == 'Y') {
				$qtyOS = $dsPorline->getValue ( 'qtyOrdered' ) - $dsPorline->getValue ( 'qtyReceived' );
			}
			else{
				$qtyOS = $dsPorline->getValue ( 'qtyReceived' ) - $dsPorline->getValue ( 'qtyInvoiced' );
			}
			// skip if nothing outstanding for this order line
			if ($qtyOS <= 0) {
				continue;
			}
			$dbeItem->getRow ( $itemID );
			/*
			if this item requires a serial number and
			this purchase order requires customer items adding
			and delivery method is direct (no goods inwards)
			then put each item on a separate line and prompt for s/n and warrany
			*/
			if (
				($dbeItem->getValue('serialNoFlag') == 'Y') &
				($dsPorhead->getValue('directDeliveryFlag') == 'Y') &
				$addCustomerItems
			){					// we split each item out onto a separate line
				for($i = 1; $i <= $qtyOS; $i ++) {
					$sequenceNo ++;
					$dsPurchaseInv->setUpdateModeInsert ();
					$dsPurchaseInv->setValue ( 'description', $dsPorline->getValue ( 'itemDescription' ) );
					$dsPurchaseInv->setValue ( 'orderSequenceNo', $dsPorline->getValue ( 'sequenceNo' ) ); // the PO sequence no
					$dsPurchaseInv->setValue ( 'sequenceNo', $sequenceNo ); // the line sequence no
					$dsPurchaseInv->setValue ( 'qtyOrdered', 1 );
					$dsPurchaseInv->setValue ( 'qtyOS', 1 ); // not invoiced
					$dsPurchaseInv->setValue ( 'curPOUnitCost', $dsPorline->getValue ( 'curUnitCost' ) ); // PO unit cost
					$dsPurchaseInv->setValue ( 'qtyToInvoice', 0 );
					$dsPurchaseInv->setValue ( 'curInvUnitCost', $dsPorline->getValue ( 'curUnitCost' ) ); // Invoice cost
					$dsPurchaseInv->setValue ( 'curInvTotalCost', 0 ); // Invoice cost
					$dsPurchaseInv->setValue ( 'curVAT', 0 ); // VAT
					$dsPurchaseInv->setValue ( 'itemID', $itemID );
					$dsPurchaseInv->setValue ( 'partNo', $dsPorline->getValue ( 'partNo' ) );
					$dsPurchaseInv->setValue ( 'serialNo', '' );
					$dsPurchaseInv->setValue ( 'requireSerialNo', TRUE ); // Prompt for SN and warranty
					$dsPurchaseInv->setValue ( 'renew', FALSE );
					$dsPurchaseInv->setValue ( 'warrantyID', $dbeItem->getValue ( 'warrantyID' ) );
;
					$dsPurchaseInv->post ();
				}
			}
			else{ // no serial no and waranty so lump all together on one line
				$sequenceNo ++;
				$dsPurchaseInv->setUpdateModeInsert ();
				$dsPurchaseInv->setValue ( 'description', $dsPorline->getValue ( 'itemDescription' ) );
				$dsPurchaseInv->setValue ( 'orderSequenceNo', $dsPorline->getValue ( 'sequenceNo' ) ); // the PO sequence no
				$dsPurchaseInv->setValue ( 'sequenceNo', $sequenceNo ); // the line sequence no
				$dsPurchaseInv->setValue ( 'qtyOrdered', $dsPorline->getValue ( 'qtyOrdered' ) );
				$dsPurchaseInv->setValue ( 'qtyOS', $qtyOS ); // not invoiced
				$dsPurchaseInv->setValue ( 'curPOUnitCost', $dsPorline->getValue ( 'curUnitCost' ) ); // PO unit cost
				$dsPurchaseInv->setValue ( 'qtyToInvoice', 0 );
				$dsPurchaseInv->setValue ( 'curInvUnitCost', $dsPorline->getValue ( 'curUnitCost' ) ); // Invoice cost
				$dsPurchaseInv->setValue ( 'curInvTotalCost', 0 ); // Invoice cost
				$dsPurchaseInv->setValue ( 'curVAT', 0 ); // VAT
				$dsPurchaseInv->setValue ( 'itemID', $itemID );
				$dsPurchaseInv->setValue ( 'partNo', $dsPorline->getValue ( 'partNo' ) );
				$dsPurchaseInv->setValue ( 'serialNo', '' );
				$dsPurchaseInv->setValue ( 'requireSerialNo', FALSE ); // No SN or warranty
				$dsPurchaseInv->setValue ( 'renew', FALSE );
				$dsPurchaseInv->setValue ( 'warrantyID', '' );
				$dsPurchaseInv->post ();
			}
		}
	}
	function initialiseDataset(&$dsPurchaseInv)
	{
		$this->setMethodName ( 'initialiseDataset' );
		$dsPurchaseInv = new DataSet ( $this );
		$dsPurchaseInv->addColumn ( 'description', DA_STRING, DA_ALLOW_NULL );
		$dsPurchaseInv->addColumn ( 'sequenceNo', DA_INTEGER, DA_ALLOW_NULL ); // the line seqence no
		$dsPurchaseInv->addColumn ( 'orderSequenceNo', DA_INTEGER, DA_ALLOW_NULL ); // the PO sequence no
		$dsPurchaseInv->addColumn ( 'qtyOrdered', DA_INTEGER, DA_ALLOW_NULL );
		$dsPurchaseInv->addColumn ( 'qtyOS', DA_INTEGER, DA_ALLOW_NULL ); // not invoiced
		$dsPurchaseInv->addColumn ( 'curPOUnitCost', DA_FLOAT, DA_ALLOW_NULL ); // PO unit cost
		$dsPurchaseInv->addColumn ( 'curPOUnitCost', DA_FLOAT, DA_ALLOW_NULL ); // PO unit cost
		$dsPurchaseInv->addColumn ( 'qtyToInvoice', DA_INTEGER, DA_ALLOW_NULL );
		$dsPurchaseInv->addColumn ( 'curInvUnitCost', DA_FLOAT, DA_ALLOW_NULL ); // Invoice cost
		$dsPurchaseInv->addColumn ( 'curInvTotalCost', DA_FLOAT, DA_ALLOW_NULL ); // Invoice cost
		$dsPurchaseInv->addColumn ( 'curVAT', DA_FLOAT, DA_ALLOW_NULL ); // VAT amount
		$dsPurchaseInv->addColumn ( 'itemID', DA_ID, DA_ALLOW_NULL );
		$dsPurchaseInv->addColumn ( 'partNo', DA_INTEGER, DA_ALLOW_NULL );
		$dsPurchaseInv->addColumn ( 'serialNo', DA_STRING, DA_ALLOW_NULL );
		$dsPurchaseInv->addColumn ( 'renew', DA_INTEGER, DA_ALLOW_NULL );
		$dsPurchaseInv->addColumn ( 'requireSerialNo', DA_INTEGER, DA_ALLOW_NULL );
		$dsPurchaseInv->addColumn ( 'customerItemID', DA_ID, DA_ALLOW_NULL );
		$dsPurchaseInv->addColumn ( 'warrantyID', DA_INTEGER, DA_ALLOW_NULL );
	}
	function validateQtys(&$dsPurchaseInv) {
		$this->setMethodName ( 'validateQtys' );
		$ret = TRUE;
		$dsPurchaseInv->initialise ();
		while ( $dsPurchaseInv->fetchNext () ) {
			if ($dsPurchaseInv->getValue ( 'qtyOS' ) < $dsPurchaseInv->getValue ( 'qtyToInvoice' )) {
				$ret = FALSE;
				break;
			}
		}
		return $ret;
	}
	function validatePrices(&$dsPurchaseInv) {
		$this->setMethodName ( 'validatePrices' );
		$ret = TRUE;
		$dsPurchaseInv->initialise ();
		while ( $dsPurchaseInv->fetchNext () ) {
			if (
				($dsPurchaseInv->getValue('curInvUnitCost') > 99999) OR
				($dsPurchaseInv->getValue('curInvUnitCost') < 0)
			){
				$ret = FALSE;
				break;
			}
			if (
				($dsPurchaseInv->getValue('curVAT') > 99999) OR
				($dsPurchaseInv->getValue('curVAT') < 0)
			){
				$ret = FALSE;
				break;
			}
		}
		return $ret;
	}
	function validateSerialNos(&$dsPurchaseInv) {
		$this->setMethodName ( 'validateSerialNos' );
		$ret = TRUE;
		$dsPurchaseInv->initialise ();
		while ( $dsPurchaseInv->fetchNext () ) {
			if (
				($dsPurchaseInv->getValue('qtyToInvoice') > 0) &
				($dsPurchaseInv->getValue('serialNo')=='') &
				($dsPurchaseInv->getValue('requireSerialNo'))
			){ 
				$ret = FALSE;
				break;
			}
		}
		return $ret;
	}
	function validateWarranties(&$dsPurchaseInv) {
		$this->setMethodName ( 'validateWarranties' );
		$ret = TRUE;
		$dsPurchaseInv->initialise ();
		while ( $dsPurchaseInv->fetchNext () ) {
			if (
				($dsPurchaseInv->getValue('qtyToInvoice') > 0) &
				($dsPurchaseInv->getValue('warrantyID') == '') &
				($dsPurchaseInv->getValue('requireSerialNo'))
			){ 
				$ret = FALSE;
				break;
			}
		}
		return $ret;
	}
  /**
  * Validate that renewals have been created and minimum information has been entered
  */
  function renewalsNotCompleted( $dsOrdline ){

    $this->setMethodName ( 'validateRenewals' );

    $ret = false;

    $dbeItem = new DBEItem( $this );
    
    while ( $dsOrdline->fetchNext() ){
        
      $dbeItem->getRow( $dsOrdline->getValue( 'itemID' ) );
      
      if ( $dbeItem->getValue( 'renewalTypeID') > 0 ){    
            
        if (!$dsOrdline->getValue( 'renewalCustomerItemID')){
  
          $ret = 'You have not created all of the renewals';
          
        }
        else {
          if ( !$buRenewal ){
              
            $buRenewal = new BURenewal( $this );
  
          }
          $buRenewalObject =
            $buRenewal->getRenewalBusinessObject(
              $dbeItem->getValue('renewalTypeID'),
              $page
          );
          
          if ( !$buRenewalObject->isCompleted( $dsOrdline->getValue( 'renewalCustomerItemID') ) ){
  
            $ret = 'You have not completed all of the renewal information required';
            
            
          }
          
        } // end else
        
      } // end if is a renewal line
      
    }

    return $ret;  
  }
	function invoiceNoIsUnique($purchaseInvoiceNo, $porheadID)
	{
		$this->setMethodName ( 'invoiceNoIsUnique' );
		$this->buPurchaseOrder = new BUPurchaseOrder ( $this );
		$this->buPurchaseOrder->getHeaderByID(
			$porheadID,
			$dsPorhead
		);
		require_once ($GLOBALS ['cfg'] ['path_dbe'] . '/DBEPurchaseInv.inc.php');
		$dbePurchaseInv = new DBEPurchaseInv ( $this );
		return $dbePurchaseInv->countRowsBySupplierInvNo ( $dsPorhead->getValue ( 'supplierID' ), $purchaseInvoiceNo ) == 0;
	}
	/**
	 * Update with purchase invoice info.
	 * 1) Update stock levels on item 
	 * 2) Create customer items (where appropriate)
	 *
	 * Some of the customer item fields in the old system were being filled in depending upon the delivery
	 * method selected (direct, hand, etc). Because we are creating customer items at goods in we
	 * don't have some of the info to hand.
	 *
	 * @param Integer porheadID purchase order number
	 * @param Dataset $dsGoodsIn Dataset of recieved items
	 */
	function update($porheadID, $purchaseInvoiceNo, $purchaseInvoiceDate, &$dsPurchaseInv, $userID) {
		$this->setMethodName ( 'update' );
		$this->userID = $userID;
		$this->purchaseInvoiceNo = $purchaseInvoiceNo;
		$this->purchaseInvoiceDate = $purchaseInvoiceDate;
		$this->buPurchaseOrder = new BUPurchaseOrder ( $this );
		$this->buPurchaseOrder->getHeaderByID ( $porheadID, $dsPorhead );
		$this->dbeItem = new DBEItem ( $this );
		$this->dbeStockcat = new DBEStockcat ( $this );
		$this->dbePurchaseInv = new DBEPurchaseInv ( $this );
		$ordheadID = $dsPorhead->getValue ( 'ordheadID' );
		if ($ordheadID != 0) {
			$buSalesOrder = new BUSalesOrder ( $this );
			$buSalesOrder->getOrderByOrdheadID ( $ordheadID, $dsOrdhead, $dsOrdline );
    }
		$this->dbePorline = new DBEPorline ( $this );
		$this->dbePorhead = new DBEPorhead ( $this );
		
		/*
		 Loop through lines, and for each update the purchase order appropriately
		*/
		$dsPurchaseInv->initialise ();
		while ( $dsPurchaseInv->fetchNext () ) {
			if ($dsPurchaseInv->getValue ( 'qtyToInvoice' ) <= 0) {
				continue;
			}
			$this->postPurchaseInvoiceLine ( $dsPurchaseInv, $dsPorhead, $dsOrdhead );
			$this->updateOrderLineQtys ( $porheadID, $dsPurchaseInv, $dsPorhead->getValue ( 'directDeliveryFlag' ) );
			// Unlike in the UNIX system, stock levels don't get updated BUT customer items need to be generated
			// if direct delivery and the requireSerialNo is TRUE
			if (
				($dsPorhead->getValue('directDeliveryFlag') == 'Y') &
				($dsPurchaseInv->getValue('requireSerialNo') == TRUE)
			){
				$this->createCustomerItem ( $dsPurchaseInv, $dsPorhead, $dsOrdhead );
			}
		} //dsPurchaseInv->fetchNext()
		$this->updatePOStatus ( $porheadID );

		// if all purchase orders for this sales order are now authorised and sales order status is initial...
		if (
			($this->dbePorhead->countNonAuthorisedRowsBySO($ordheadID) == 0) & 
			($dsOrdhead->getValue('type') == 'I')
		){
			// if delivery is NOT an internal stock location
			if (! common_isAnInternalStockLocation ( $dsOrdhead->getValue ( 'customerID' ) )) {
				// if all purchase orders for this sales order are direct delivery then create invoices and set sales order status to completed
				if ($this->dbePorhead->countNonDirectRowsBySO ( $ordheadID ) == 0) {
					$dbePaymentTerms = new DBEPaymentTerms ( $this );
					$dbePaymentTerms->getRow ( $dsOrdhead->getValue ( 'paymentTermsID' ) );
					if ($dbePaymentTerms->getValue ( 'automaticInvoiceFlag' ) == 'Y') {
						$buInvoice = new BUInvoice ( $this );
						$buInvoice->createInvoiceFromOrder ( $dsOrdhead, $dsOrdline );
						$buSalesOrder->setStatusCompleted ( $ordheadID );
					}
				}
			}
			else{
				$buSalesOrder->setStatusCompleted ( $ordheadID ); // delivery to internal stock location so no invoices were created
			}
		}
	}
	/**
	 * Update the puchase order line qty invoiced and qty received, if direct delivery
	 */
	function updateOrderLineQtys($porheadID, &$dsPurchaseInv, $directDeliveryFlag) {
		
		// update qtys on porline
		$this->dbePorline->setValue ( 'porheadID', $porheadID );
		$this->dbePorline->setValue ( 'sequenceNo', $dsPurchaseInv->getValue ( 'orderSequenceNo' ) );
		$this->dbePorline->getRow ();
		$this->dbePorline->setValue ( 'qtyInvoiced', $this->dbePorline->getValue ( 'qtyInvoiced' ) + $dsPurchaseInv->getValue ( 'qtyToInvoice' ) );
		// we update the received qty here for Direct Delivery because there is no goods in process to do it for us
		if ($directDeliveryFlag == 'Y') {
			$this->dbePorline->setValue ( 'qtyReceived', $this->dbePorline->getValue ( 'qtyReceived' ) + $dsPurchaseInv->getValue ( 'qtyToInvoice' ) );
		}
		$this->dbePorline->updateRow ();
	}
	
	function updatePOStatus($porheadID) {
		// update status on purchase order header
		$dbePorhead = & $this->dbePorhead;
		$dbePorlineTotals = new DBEPorlineTotals ( $this );
		$dbePorlineTotals->setValue ( 'porheadID', $porheadID );
		$dbePorlineTotals->getRow ();
		$qtyOrd = $dbePorlineTotals->getValue ( 'qtyOrdered' );
		$qtyRec = $dbePorlineTotals->getValue ( 'qtyReceived' );
		$qtyInv = $dbePorlineTotals->getValue ( 'qtyInvoiced' );
		
		$dbePorhead->getRow ( $porheadID );
		
		if ($qtyRec == 0) {
			$dbePorhead->setValue ( 'type', 'I' );
		}
		elseif ($qtyOrd - $qtyRec > 0){
			$dbePorhead->setValue ( 'type', 'P' );
		}
		elseif ($qtyRec - $qtyInv == 0){
			$dbePorhead->setValue ( 'type', 'A' );
		}
		elseif ($qtyOrd - $qtyRec == 0){
			$dbePorhead->setValue ( 'type', 'C' );
		}
		if ($dbePorhead->getValue ( 'type' ) != 'I') { // no need to update if already Initial
			$dbePorhead->updateRow ();
		}
	}
	
	function postPurchaseInvoiceLine(&$dsPurchaseInv, &$dsPorhead, &$dsOrdhead) {
		$this->dbeItem->getRow ( $dsPurchaseInv->getValue ( 'itemID' ) );
		$this->dbeStockcat->getRow ( $this->dbeItem->getValue ( 'stockcat' ) );
		/*
		Depending upon the sales order customerID, see whether the item is 
		being purchased for an internal stock location or for a customer
		*/
		switch ($dsOrdhead->getValue ( 'customerID' )) {
			case CONFIG_MAINT_STOCK_CUSTOMERID :
				$nominalCode = $this->dbeStockcat->getValue ( 'purMaintStk' );
				break;
			case CONFIG_SALES_STOCK_CUSTOMERID :
				$nominalCode = $this->dbeStockcat->getValue ( 'purSalesStk' );
				break;
			case CONFIG_ASSET_STOCK_CUSTOMERID :
				$nominalCode = $this->dbeStockcat->getValue ( 'purAsset' );
				break;
			case CONFIG_OPERATING_STOCK_CUSTOMERID :
				$nominalCode = $this->dbeStockcat->getValue ( 'purOper' );
				break;
			default :
				$nominalCode = $this->dbeStockcat->getValue ( 'purCust' ); // purchased for a customer
				break;
		}
		$this->dbePurchaseInv->setValue ( 'type', 'PI' );
		$this->dbePurchaseInv->setValue ( 'date', $this->purchaseInvoiceDate );
		$this->dbePurchaseInv->setValue ( 'ref', $this->purchaseInvoiceNo );
		$this->dbePurchaseInv->setValue ( 'accRef', $dsPorhead->getValue ( 'supplierID' ) );
		$this->dbePurchaseInv->setValue ( 'nomRef', $nominalCode );
		$this->dbePurchaseInv->setValue ( 'dept', '0' );
		$this->dbePurchaseInv->setValue ( 'details', 'P' . str_pad ( $dsPorhead->getValue ( 'porheadID' ), 6, '0', STR_PAD_LEFT ) );
		$netAmnt = $dsPurchaseInv->getValue ( 'qtyToInvoice' ) * $dsPurchaseInv->getValue ( 'curInvUnitCost' );
		$this->dbePurchaseInv->setValue ( 'netAmnt', $netAmnt );
		$this->dbePurchaseInv->setValue ( 'taxCode', $dsPorhead->getValue ( 'vatCode' ) );
		$this->dbePurchaseInv->setValue ( 'taxAmnt', $dsPurchaseInv->getValue ( 'curVAT' ) );
		$this->dbePurchaseInv->setValue ( 'printed', 'N' );
		$this->dbePurchaseInv->insertRow ();
	}
	function createCustomerItem(&$dsPurchaseInv, &$dsPorhead, &$dsOrdhead)
	{
		$this->setMethodName ( 'createCustomerItem' );
		if (! is_object ( $this->dbeCustomerItem )) {
			$this->dbeCustomerItem = new DBECustomerItem ( $this );
		}
		$dbeCustomerItem = &$this->dbeCustomerItem; // easy to use ref!
		$dbeCustomerItem->setValue ( 'customerItemID', 0 );
		$dbeCustomerItem->setValue ( 'customerID', $dsOrdhead->getValue ( 'customerID' ) );
		$dbeCustomerItem->setValue ( 'siteNo', $dsOrdhead->getValue ( 'delSiteNo' ) );
		$dbeCustomerItem->setValue ( 'itemID', $dsPurchaseInv->getValue ( 'itemID' ) );
		$dbeCustomerItem->setValue ( 'userID', $this->userID );
		$dbeCustomerItem->setValue ( 'despatchDate', date ( 'Y-m-d' ) );
		$dbeCustomerItem->setValue ( 'ordheadID', $dsPorhead->getValue ( 'ordheadID' ) );
		$dbeCustomerItem->setValue ( 'porheadID', $dsPorhead->getValue ( 'porheadID' ) );
		$dbeCustomerItem->setValue ( 'sOrderDate', $dsOrdhead->getValue ( 'date' ) );
		$dbeCustomerItem->setValue ( 'curUnitSale', '' ); // redundant I think
		$dbeCustomerItem->setValue ( 'curUnitCost', '' ); // redundant
		$stockcat = $this->dbeItem->getValue ( 'stockcat' );
		if (($stockcat == 'M') or ($stockcat == 'R')) {
			$dbeCustomerItem->setValue ( 'expiryDate', date ( 'Y-m-d', strtotime ( '+ 1 year' ) ) );
		}
		else if($dsPurchaseInv->getValue('renew') == TRUE) {
			// bug 245: Add warranty years to current date to calculate expiry date.
			$dbeWarranty = new DBEWarranty ( $this );
			$dbeWarranty->getRow ( $dsPurchaseInv->getValue ( 'warrantyID' ) );
			$dbeCustomerItem->setValue ( 'expiryDate', date ( 'Y-m-d', strtotime ( '+ ' . $dbeWarranty->getValue ( 'years' ) . ' year' ) ) );
		}
		$dbeCustomerItem->setValue ( 'warrantyID', $dsPurchaseInv->getValue ( 'warrantyID' ) );
		$dbeCustomerItem->setValue ( 'serialNo', $dsPurchaseInv->getValue ( 'serialNo' ) );
    /*
    @todo: update for many-to-many

		$dbeCustomerItem->setValue ( 'contractID', null );
    */
		$dbeCustomerItem->insertRow ();
	}
} // End of class
?>