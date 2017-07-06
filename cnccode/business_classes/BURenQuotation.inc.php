<?
/**
 * Contract renewal business class
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once ($cfg ["path_gc"] . "/Business.inc.php");
require_once ($cfg ["path_bu"] . "/BUCustomerItem.inc.php");
require_once ($cfg ["path_bu"] . "/BUSalesOrder.inc.php");
require_once ($cfg ["path_dbe"] . "/DBECustomerItem.inc.php");
require_once ($cfg ["path_dbe"] . "/DBEOrdline.inc.php");
require_once ($cfg ["path_dbe"] . "/DBERenQuotation.inc.php");
require_once ($cfg ["path_dbe"] . "/DBERenQuotationType.inc.php");
require_once ($cfg ["path_bu"] . "/BUMail.inc.php");

class BURenQuotation extends Business {
	var $dbeRenQuotation = "";
	/**
	 * Constructor
	 * @access Public
	 */
	function BURenQuotation(&$owner) {
		$this->constructor ( $owner );
	}
	function constructor(&$owner) {
		parent::constructor ( $owner );
		$this->dbeRenQuotation = new DBECustomerItem( $this );
		$this->dbeJRenQuotation = new DBEJRenQuotation ( $this );
	}
	function updateRenQuotation(&$dsData) {
		$this->setMethodName ( 'updateRenQuotation' );
		$this->updateDataaccessObject ( $dsData, $this->dbeRenQuotation );
		
		return TRUE;
	}
	function getRenQuotationByID($ID, &$dsResults)
	{
		$this->dbeJRenQuotation->setPKValue ( $ID );
		$this->dbeJRenQuotation->getRow ();
		return ($this->getData ( $this->dbeJRenQuotation, $dsResults ));
	}
	function getAll(&$dsResults, $orderBy = false)
	{
		$this->dbeJRenQuotation->getRows( $orderBy );
		return ($this->getData ( $this->dbeJRenQuotation, $dsResults ));
	}
	function deleteRenQuotation($ID) {
		$this->setMethodName ( 'deleteRenQuotation' );
		if ($this->canDeleteRenQuotation ( $ID )) {
			return $this->dbeRenQuotation->deleteRow ( $ID );
		}
		else{
			return FALSE;
		}
	}
	function createNewRenewal(
		$customerID,
		$siteNo = 0,
		$itemID,
		&$customerItemID,
		$salePrice,
		$costPrice,
		$qty
		)
	{
		// create a customer item
		$dbeCustomerItem = new DBECustomerItem ( $this );
		
		$dsCustomerItem = new DataSet ( $this );
		
		$dsCustomerItem->copyColumnsFrom ( $dbeCustomerItem );
		
		$dsCustomerItem->setUpdateModeInsert ();
		
		$dsCustomerItem->setValue ( 'customerItemID', 0 );
		$dsCustomerItem->setValue ( 'customerID', $customerID );
		$dsCustomerItem->setValue ( 'itemID', $itemID );
		$dsCustomerItem->setValue ( 'siteNo', $siteNo );
		
		$dsCustomerItem->post ();
		
		$buCustomerItem = new BUCustomerItem ( $this );
		$buCustomerItem->update ( $dsCustomerItem );
		
		$customerItemID = $dsCustomerItem->getPKValue ();
    
    $this->dbeRenQuotation->getRow( $customerItemID );
    
    $this->dbeRenQuotation->setValue ( 'customerItemID', $customerItemID );
    $this->dbeRenQuotation->setValue ( 'renQuotationTypeID', $renQuotationTypeID );
    $this->dbeRenQuotation->setValue ( 'startDate', date( 'Y-m-d') );
    $this->dbeRenQuotation->setValue ( 'qty', $qty );
    $this->dbeRenQuotation->setValue ( 'salePrice', $salePrice );
    $this->dbeRenQuotation->setValue ( 'costPrice', $costPrice );
    $this->dbeRenQuotation->setValue ( 'grantNumber', '' );

    $this->dbeRenQuotation->updateRow();
    
    return;
	}
	function emailRenewalsQuotationsDue()
	{
		$this->dbeJRenQuotation->getRenewalsDueRows();

    $buMail = new BUMail( $this );
	
		$toEmail = CONFIG_SALES_MANAGER_EMAIL;
		$senderEmail = CONFIG_SALES_EMAIL;
		
		$hdrs =
			array (
				'From' => $senderEmail,
				'To' => $toEmail,
				'Subject' => 'Quotation Renewals Due Today',
				'Date' => date ( "r" )
			);

		ob_start();?>
			<HTML>
				<BODY>
					<TABLE border="1">
						<tr>
							<td bgcolor="#999999">Customer</td>
							<td bgcolor="#999999">Service</td>
						</tr>
						<?php while( $this->dbeJRenQuotation->fetchNext() ){?>
						<tr>
							<td><?php echo $this->dbeJRenQuotation->getValue('customerName') ?></td>
							<td><?php echo $this->dbeJRenQuotation->getValue('itemDescription') ?></td>
						</tr>
						<?php } ?>
				</TABLE>
				</BODY>
			</HTML>
		<?php			

		$message = ob_get_contents ();
		ob_end_clean ();
			
    $buMail->mime->setHTMLBody ( $message );

    $body = $buMail->mime->get();

    $hdrs = $buMail->mime->headers ( $hdrs );

    $buMail->putInQueue(
      $senderEmail,
      $toEmail,
      $hdrs,
      $body
    );
		
	}	
	function emailRecentlyGeneratedQuotes()
	{
		$this->dbeJRenQuotation->getRecentQuotesRows();
	
    $buMail = new BUMail( $this );

		$toEmail = CONFIG_SALES_MANAGER_EMAIL;
		$senderEmail = CONFIG_SALES_EMAIL;
		
		$hdrs =
			array (
				'From' => $senderEmail,
				'To' => $toEmail,
				'Subject' => 'Quotation Renewals Generated in Past 2 Weeks',
				'Date' => date ( "r" )
			);

		ob_start();?>
			<HTML>
				<BODY>
					<TABLE border="1">
						<tr>
							<td bgcolor="#999999">Customer</td>
							<td bgcolor="#999999">Service</td>
						</tr>
						<?php while( $this->dbeJRenQuotation->fetchNext() ){?>
						<tr>
							<td><?php echo $this->dbeJRenQuotation->getValue('customerName') ?></td>
							<td><?php echo $this->dbeJRenQuotation->getValue('itemDescription') ?></td>
						</tr>
						<?php } ?>
				</TABLE>
				</BODY>
			</HTML>
		<?php			

		$message = ob_get_contents ();
		ob_end_clean ();
			
    $buMail->mime->setHTMLBody ( $message );

    $body = $buMail->mime->get();

    $hdrs = $buMail->mime->headers ( $hdrs );

    $buMail->putInQueue(
      $senderEmail,
      $toEmail,
      $hdrs,
      $body
    );
		
	}
	function createRenewalsQuotations( $customerItemIDs = false )
	{
		$buSalesOrder = new BUSalesOrder ( $this );
		
		$dbeRenQuotationUpdate = new DBECustomerItem( $this );
		
		$buInvoice = new BUInvoice ( $this );
		
    if ( !$customerItemIDs ){

        $this->dbeJRenQuotation->getRenewalsDueRows ();
      
    }
    else{
      // we have been passed an explicit list of renewal IDs
        $this->dbeJRenQuotation->getRenewalsByIDList ( $customerItemIDs );

    }
		
		$dbeJCustomerItem = new DBEJCustomerItem ( $this );
		
		$dbeOrdline = new DBEOrdline ( $this );
		
		$dbeOrdhead = new DBEOrdhead($this);
		
		$dbeRenQuotationType = new DBERenQuotationType($this);

		$dbeCustomer = new DBECustomer ( $this );
		 
		$previousCustomerID = 99999;
		
		while ( $this->dbeJRenQuotation->fetchNext () ) {
			
			if ($dbeJCustomerItem->getRow ( $this->dbeJRenQuotation->getValue ( 'customerItemID' ) )) {
				/*
				 * Group many renewals for same customer under one quote
				 */
				if ( $previousCustomerID != $dbeJCustomerItem->getValue ( 'customerID' ) ){
					/*
					 *  create order header
					 */
					$dbeCustomer->getRow ( $dbeJCustomerItem->getValue ( 'customerID' ) );
					$this->getData ( $dbeCustomer, $dsCustomer );

					$buSalesOrder->initialiseQuote ( $dsOrdhead, $dsOrdline, $dsCustomer );

					$line = -1;	// initialise sales order line seq

					$quotationIntroduction = 'Please find detailed below a quote for your ' . $this->dbeJRenQuotation->getValue( 'type') . ' renewal.';
					
					$dbeOrdhead->getRow( $dsOrdhead->getValue('ordheadID') );
					
					$dbeOrdhead->setValue('quotationSubject', $quotationSubject );
					$dbeOrdhead->setValue('quotationIntroduction', $quotationIntroduction );
	
					$dbeOrdhead->updateRow();
				}
				
				$line++;
						

				// renewal type comment line
				if ( $this->dbeJRenQuotation->getValue ( 'comment' ) ){
					$comment = $this->dbeJRenQuotation->getValue ( 'comment' );
				}
				else{
          $comment = $this->dbeJRenQuotation->getValue( 'type') . ' Renewal';
//					$comment = $quotationSubject;
				}
				
				$dbeOrdline->setValue ( 'lineType', 'C' );
				$dbeOrdline->setValue ( 'renewalCustomerItemID', '' );
				$dbeOrdline->setValue ( 'ordheadID', $dsOrdhead->getValue ( 'ordheadID' ) );
				$dbeOrdline->setValue ( 'customerID', $dsOrdhead->getValue ( 'customerID' ) );
				$dbeOrdline->setValue ( 'itemID', 0 );
				$dbeOrdline->setValue ( 'description', $comment );
				$dbeOrdline->setValue ( 'supplierID', '' );
				$dbeOrdline->setValue ( 'sequenceNo', $line );
				$dbeOrdline->setValue ( 'lineType', 'C' );
				$dbeOrdline->setValue ( 'qtyOrdered', 0 ); // default 1
				$dbeOrdline->setValue ( 'qtyDespatched', 0 );
				$dbeOrdline->setValue ( 'qtyLastDespatched', 0 );
				$dbeOrdline->setValue ( 'curUnitSale', 0 );
				$dbeOrdline->setValue ( 'curUnitCost', 0 );
				
				$dbeOrdline->insertRow ();

				$line ++;
				
				/*
				 * Get stock category from item table
				 */
				$buItem = new BUItem($this);
				$buItem->getItemByID($dbeJCustomerItem->getValue ( 'itemID' ), $dsItem );
				$dbeOrdline->setValue('stockcat', $dsItem->getValue('stockcat'));
				
				$dbeOrdline->setValue ( 'renewalCustomerItemID', $this->dbeJRenQuotation->getValue ( 'customerItemID' ) );
				$dbeOrdline->setValue ( 'ordheadID', $dsOrdhead->getValue ( 'ordheadID' ) );
				$dbeOrdline->setValue ( 'customerID', $dsOrdhead->getValue ( 'customerID' ) );
				$dbeOrdline->setValue ( 'itemID', $dbeJCustomerItem->getValue ( 'itemID' ) );
				$dbeOrdline->setValue ( 'description', $dbeJCustomerItem->getValue ( 'itemDescription' ) );
				$dbeOrdline->setValue ( 'supplierID', CONFIG_SALES_STOCK_SUPPLIERID );
				$dbeOrdline->setValue ( 'sequenceNo', $line );
				$dbeOrdline->setValue ( 'lineType', 'I' );
				$dbeOrdline->setValue ( 'qtyOrdered', $this->dbeJRenQuotation->getValue ( 'qty' ) );
				$dbeOrdline->setValue ( 'qtyDespatched', 0 );
				$dbeOrdline->setValue ( 'curUnitSale', $this->dbeJRenQuotation->getValue( 'salePrice' )  );
				$dbeOrdline->setValue ( 'curUnitCost', $this->dbeJRenQuotation->getValue( 'costPrice' )  );
								
				$dbeOrdline->insertRow ();
				/**
				 * add installation charge
				 */
				if ( $this->dbeJRenQuotation->getValue( 'addInstallationCharge') == 'Y' ){
					$line ++;
					
					/*
					 * Get stock category from item table
					 */
					$buItem = new BUItem($this);
					$buItem->getItemByID(	CONFIG_INSTALLATION_ITEMID, $dsItem );
					$dbeOrdline->setValue('stockcat', $dsItem->getValue('stockcat'));
					
					$dbeOrdline->setValue ( 'renewalCustomerItemID', 0 );
					$dbeOrdline->setValue ( 'ordheadID', $dsOrdhead->getValue ( 'ordheadID' ) );
					$dbeOrdline->setValue ( 'customerID', $dsOrdhead->getValue ( 'customerID' ) );
					$dbeOrdline->setValue ( 'itemID', CONFIG_INSTALLATION_ITEMID );
					$dbeOrdline->setValue ( 'description', 'Installation/Configuration' );
					$dbeOrdline->setValue ( 'supplierID', CONFIG_SALES_STOCK_SUPPLIERID );
					$dbeOrdline->setValue ( 'sequenceNo', $line );
					$dbeOrdline->setValue ( 'lineType', 'I' );
					$dbeOrdline->setValue ( 'qtyOrdered', 1 );
					$dbeOrdline->setValue ( 'qtyDespatched', 0 );
					$dbeOrdline->setValue ( 'qtyLastDespatched', 0 );
					$dbeOrdline->setValue ( 'curUnitCost', $dsItem->getValue('curUnitCost')  );
					$dbeOrdline->setValue ( 'curUnitCost', $dsItem->getValue('curUnitSale')  );
					
					$dbeOrdline->insertRow ();
				}
			
				
				
				// period comment line
				$line ++;
				$description =
					$this->dbeJRenQuotation->getValue ( 'grantNumber' ) . ' ' . 
				' Expires: ' . $this->dbeJRenQuotation->getValue ( 'nextPeriodStartDate' );
					$dbeOrdline->setValue ( 'lineType', 'C' );
				$dbeOrdline->setValue ( 'renewalCustomerItemID', '' );
				$dbeOrdline->setValue ( 'ordheadID', $dsOrdhead->getValue ( 'ordheadID' ) );
				$dbeOrdline->setValue ( 'customerID', $dsOrdhead->getValue ( 'customerID' ) );
				$dbeOrdline->setValue ( 'itemID', 0 );
				$dbeOrdline->setValue ( 'description', $description );
				$dbeOrdline->setValue ( 'supplierID', '' );
				$dbeOrdline->setValue ( 'sequenceNo', $line );
				$dbeOrdline->setValue ( 'lineType', 'C' );
				$dbeOrdline->setValue ( 'qtyOrdered', 0 ); // default 1
				$dbeOrdline->setValue ( 'qtyDespatched', 0 );
				$dbeOrdline->setValue ( 'qtyLastDespatched', 0 );
				$dbeOrdline->setValue ( 'curUnitSale', 0 );
				$dbeOrdline->setValue ( 'curUnitCost', 0 );
				
				$dbeOrdline->insertRow ();
				/*
				 * set generated date
				 */
				$dbeRenQuotationUpdate->setValue('customerItemID', $this->dbeJRenQuotation->getPKValue() );
				$dbeRenQuotationUpdate->getRow();
				$dbeRenQuotationUpdate->setValue( 'dateGenerated', date( CONFIG_MYSQL_DATE ) );
				$dbeRenQuotationUpdate->updateRow();
				
				$previousCustomerID = $dbeJCustomerItem->getValue ( 'customerID' );

//		Needs to be done at conversion to initial: $this->processQuotationRenewal( $dbeJCustomerItem->getValue ( 'customerItemID' ) );
			}
		}
	
    if ( $customerItemIDs ){
      return $dsOrdhead->getValue( 'ordheadID' );
    }
	}
	function processQuotationRenewal( $customerItemID )
	{
		$this->dbeRenQuotation->setValue ( 'customerItemID', $customerItemID );
		$this->dbeRenQuotation->getRowsByColumn ( 'customerItemID' );
		
		if ( $this->dbeRenQuotation->fetchNext () ){
			
			$this->dbeRenQuotation->addYearToStartDate(  $this->dbeRenQuotation->getPKValue()  );
		
		}
		
	}
		
	function isCompleted( $customerItemID )
	{
		$this->dbeRenQuotation->getRow ( $customerItemID );
		
		if
		(
			$this->dbeRenQuotation->getValue( 'startDate') != '0000-00-00' AND
      $this->dbeRenQuotation->getValue( 'startDate') != '' AND
			$this->dbeRenQuotation->getValue( 'qty') > 0 AND
			$this->dbeRenQuotation->getValue( 'salePrice') > 0 AND
			$this->dbeRenQuotation->getValue( 'costPrice') > 0
		){
			return true;
		}
		else{
			return FALSE;
		}
	
	}
} // End of class
?>