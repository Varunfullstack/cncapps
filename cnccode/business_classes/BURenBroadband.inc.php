<?
/**
 * Broadband renewal business class
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once ($cfg ["path_gc"] . "/Business.inc.php");
require_once ($cfg ["path_bu"] . "/BUCustomerItem.inc.php");
require_once ($cfg ["path_bu"] . "/BUSalesOrder.inc.php");
require_once ($cfg ["path_bu"] . "/BUItem.inc.php");
require_once ($cfg ["path_dbe"] . "/DBECustomerItem.inc.php");
require_once ($cfg ["path_dbe"] . "/DBEOrdline.inc.php");
require_once ($cfg ["path_dbe"] . "/DBERenBroadband.inc.php");
require_once ($cfg ["path_bu"] . "/BUMail.inc.php");

class BURenBroadband extends Business {
	var $dbeRenBroadband = "";
	/**
	 * Constructor
	 * @access Public
	 */
	function BURenBroadband(&$owner) {
		$this->constructor ( $owner );
	}
	function constructor(&$owner) {
		parent::constructor ( $owner );
		$this->dbeRenBroadband = new DBECustomerItem( $this );
		$this->dbeJRenBroadband = new DBEJRenBroadband( $this );
	}
	function updateRenBroadband(&$dsData) {
		$this->setMethodName ( 'updateRenBroadband' );
		$this->updateDataaccessObject ( $dsData, $this->dbeRenBroadband );
		
		return TRUE;
	}
	function getRenBroadbandByID($ID, &$dsResults)
	{
		$this->dbeJRenBroadband->setPKValue ( $ID );
		$this->dbeJRenBroadband->getRow ();
		return ($this->getData ( $this->dbeJRenBroadband, $dsResults ));
	}
	function getAll(&$dsResults, $orderBy = false)
	{
		$this->dbeJRenBroadband->getRows ( $orderBy );
		return ($this->getData ( $this->dbeJRenBroadband, $dsResults ));
	}
	function deleteRenBroadband($ID) {
		$this->setMethodName ( 'deleteRenBroadband' );
		if ($this->canDeleteRenBroadband ( $ID )) {
			return $this->dbeRenBroadband->deleteRow ( $ID );
		}
		else{
			return FALSE;
		}
	}
	/**
	 *	canDeleteRenBroadband
	 * Only allowed if type has no activities
	 */
	function canDeleteRenBroadband($ID) {
		$dbeRenBroadband = new DBERenBroadband ( $this );
		// validate no activities of this type
		$dbeRenBroadband->setValue ( 'customerItemID', $ID );
		if ($dbeRenBroadband->countRowsByColumn ( 'customerItemID' ) < 1) {
			return TRUE;
		}
		else{
			return FALSE;
		}
	}
	function createNewRenewal(
		$customerID,
		$siteNo = 0,
		$itemID,
		&$customerItemID
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
    
    return;
		
	}
	function emailRenewalsSalesOrdersDue()
	{
		$this->dbeJRenBroadband->getRenewalsDueRows ();
    
    $buMail = new BUMail( $this );
		
		$toEmail = CONFIG_SALES_MANAGER_EMAIL;
		$senderEmail = CONFIG_SALES_EMAIL;
		
		$hdrs =
			array (
				'From' => $senderEmail,
				'To' => $toEmail,
				'Subject' => 'Broadband Renewals Due Today',
				'Date' => date ( "r" )
			);

		ob_start();?>
			<HTML>
				<BODY>
					<TABLE border="1" bgcolor="#FFFFFF">
						<tr bordercolor="#333333" bgcolor="#CCCCCC">
							<td bordercolor="#000000">Customer</td>
							<td>Service</td>
						</tr>
						<?php while( $this->dbeJRenBroadband->fetchNext() ){?>
						<tr>
							<td><?php echo $this->dbeJRenBroadband->getValue('customerName') ?></td>
							<td><?php echo $this->dbeJRenBroadband->getValue('itemDescription') ?></td>
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
	function createRenewalsSalesOrders( $customerItemIDs = false )
	{
		$buSalesOrder = new BUSalesOrder ( $this );
		
		$buInvoice = new BUInvoice ( $this );

    if ( $customerItemIDs ){

      $this->dbeJRenBroadband->getRenewalsRowsByID ($customerItemIDs);
      
    }	
    else{
      
		  $this->dbeJRenBroadband->getRenewalsDueRows ();

    }		
    
		$dbeJCustomerItem = new DBEJCustomerItem ( $this );
		
		$dbeOrdline = new DBEOrdline ( $this );

		$dbeCustomer = new DBECustomer ( $this );
		 
		$previousCustomerID = 99999;

		$createdSalesOrder = 0;
		
		while ( $this->dbeJRenBroadband->fetchNext () ) {
			
			$createdSalesOrder++;
			
			if ($dbeJCustomerItem->getRow ( $this->dbeJRenBroadband->getValue ( 'customerItemID' ) )) {
				/*
				 * Group many renewals for same customer under one sales order
				 */
				if ( $previousCustomerID != $dbeJCustomerItem->getValue ( 'customerID' ) ){
					/*
					 * Create an invoice from each sales order (unless this is the first iteration)
					 */
					if ( $previousCustomerID != 99999 ){
						
            if ( !$renewalIDs ){
              /*
               * Finalise previous sales order and create an invoice
               */
              $buSalesOrder->setStatusCompleted ( $dsOrdhead->getValue ( 'ordheadID' ) );
              
              $buSalesOrder->getOrderByOrdheadID ( $dsOrdhead->getValue ( 'ordheadID' ), $dsOrdhead, $dsOrdline );
						  $buInvoice->createInvoiceFromOrder ( $dsOrdhead, $dsOrdline );
            }
					}
					
					/*
					 *  create new sales order header
					 */
					$dbeCustomer->getRow ( $dbeJCustomerItem->getValue ( 'customerID' ) );
					$this->getData ( $dbeCustomer, $dsCustomer );

					$buSalesOrder->InitialiseOrder( $dsOrdhead, $dsOrdline, $dsCustomer );

					$line = -1;	// initialise sales order line seq

				}
				
				$line++;
								
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
				
				
				/*
				 *  Phone number comment line
				 */
				if ($this->dbeJRenBroadband->getValue ( 'adslPhone' )) {
					$description = $this->dbeJRenBroadband->getValue ( 'adslPhone' ) . '. ';
					$dbeOrdline->setValue ( 'description', $description );
					$dbeOrdline->insertRow ();
				}
				
			
				// item line
				$line ++;

				/*
				 * Get stock category from item table
				 */
				$buItem = new BUItem($this);
				$buItem->getItemByID($dbeJCustomerItem->getValue ( 'itemID' ), $dsItem );
				$dbeOrdline->setValue('stockcat', $dsItem->getValue('stockcat'));
				
				$dbeOrdline->setValue ( 'renewalCustomerItemID', $this->dbeJRenBroadband->getValue ( 'customerItemID' ) );
				$dbeOrdline->setValue ( 'ordheadID', $dsOrdhead->getValue ( 'ordheadID' ) );
				$dbeOrdline->setValue ( 'customerID', $dsOrdhead->getValue ( 'customerID' ) );
				$dbeOrdline->setValue ( 'itemID', $dbeJCustomerItem->getValue ( 'itemID' ) );
				$dbeOrdline->setValue ( 'description', $dbeJCustomerItem->getValue ( 'itemDescription' ) );
				$dbeOrdline->setValue ( 'supplierID', CONFIG_SALES_STOCK_SUPPLIERID );
				$dbeOrdline->setValue ( 'sequenceNo', $line );
				$dbeOrdline->setValue ( 'lineType', 'I' );
				$dbeOrdline->setValue ( 'qtyOrdered', 1 ); // default 1
				$dbeOrdline->setValue ( 'qtyDespatched', 1 );
				$dbeOrdline->setValue ( 'qtyLastDespatched', 1 );
				$dbeOrdline->setValue ( 'curUnitSale', $this->dbeJRenBroadband->getValue ( 'salePricePerMonth' ) * $this->dbeJRenBroadband->getValue ( 'invoicePeriodMonths' ) );
				$dbeOrdline->setValue ( 'curUnitCost', $this->dbeJRenBroadband->getValue ( 'costPricePerMonth' ) * $this->dbeJRenBroadband->getValue ( 'invoicePeriodMonths' ) );
				
				$dbeOrdline->insertRow ();
				
				// period comment line
				$line ++;
				$description = $this->dbeJRenBroadband->getValue ( 'invoiceFromDate' ) . ' to ' . $this->dbeJRenBroadband->getValue ( 'invoiceToDate' );
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
				 * Update total months invoiced on renewal record
				 */
				$this->dbeRenBroadband->getRow ( $this->dbeJRenBroadband->getValue ( 'customerItemID' ) );
				$this->dbeRenBroadband->setValue(
					'totalInvoiceMonths',
					$this->dbeJRenBroadband->getValue('totalInvoiceMonths') +
					$this->dbeJRenBroadband->getValue('invoicePeriodMonths')
				);
				$this->dbeRenBroadband->updateRow ();

				$previousCustomerID = $dbeJCustomerItem->getValue ( 'customerID' );
			}
		}
		/*
		 * Finalise last sales order and create an invoice
		 */
		if ($createdSalesOrder && !$renewalIDs ){
			$buSalesOrder->setStatusCompleted ( $dsOrdhead->getValue ( 'ordheadID' ) );
			
			$buSalesOrder->getOrderByOrdheadID ( $dsOrdhead->getValue ( 'ordheadID' ), $dsOrdhead, $dsOrdline );
			
			$buInvoice->createInvoiceFromOrder ( $dsOrdhead, $dsOrdline );
		}		
    /*
    If created from list of IDs then there will only be one customer and order
    and the caller will want to redirect to sales order page. 
    */
    if ( $renewalIDs ){
      return $dsOrdhead->getValue ( 'ordheadID' );
    }
	}
	function isCompleted( $customerItemID )
	{
		$this->dbeRenBroadband->getRow ( $customerItemID );
		
		$ret = false;
		
		if
		(
			$this->dbeRenBroadband->getValue( 'installationDate') &&
			$this->dbeRenBroadband->getValue( 'invoicePeriodMonths')
			){
			$ret = true;
		
		}
		
		return $ret;
	
	}
	function sendEmailTo( $ID, $emailAddress )
	{
		$dbeJRenBroadband = new DBEJRenBroadband($this);
		$dbeJRenBroadband->setValue( 'customerItemID', $ID );
		$dbeJRenBroadband->getRow();

    $buMail = new BUMail( $this );
		
		$toEmail = $emailAddress;
		$senderEmail = CONFIG_SALES_EMAIL;
				
		$hdrs =
			array (
				'From' => $senderEmail,
				'To' => $toEmail,
				'Subject' => 'Broadband details',
				'Date' => date ( "r" )
			);

		ob_start();?>
		
			<HTML>
				<BODY>
					<TABLE>
						<tr>
							<td>Customer</td>
							<td><?php echo $dbeJRenBroadband->getValue('customerName') ?></td>
						</tr>
						<tr>
							<td>Service</td>
							<td><?php echo $dbeJRenBroadband->getValue('itemDescription') ?></td>
						</tr>
						<tr>
							<td>ispID</td>
							<td><?php echo $dbeJRenBroadband->getValue('ispID') ?></td>
						</tr>
				
						<tr>
							<td>ADSL Phone</td>
							<td><?php echo $dbeJRenBroadband->getValue('adslPhone') ?></td>
						</tr>
						<tr>
							<td>MAC Code</td>
							<td><?php echo $dbeJRenBroadband->getValue('macCode') ?></td>
						</tr>
						<tr>
							<td>Reference</td>
							<td><?php echo $dbeJRenBroadband->getValue('reference') ?></td>
						</tr>
						<tr>
							<td>Default Gateway</td>
							<td><?php echo $dbeJRenBroadband->getValue('defaultGateway') ?></td>
						</tr>
						<tr>
							<td>Network Address</td>
							<td><?php echo $dbeJRenBroadband->getValue('networkAddress') ?></td>
						</tr>
						<tr>
							<td>Subnet Mask</td>
							<td><?php echo $dbeJRenBroadband->getValue('subnetMask') ?></td>
						</tr>
						<tr>
							<td valign="top">Router IP Address</td>
							<td><?php echo Controller::htmlDisplayText($dbeJRenBroadband->getValue('routerIPAddress'), 1) ?></td>
						</tr>
						<tr>
							<td>User Name</td>
							<td><?php echo $dbeJRenBroadband->getValue('userName') ?></td>
						</tr>
						<tr>
							<td>Password</td>
							<td><?php echo $dbeJRenBroadband->getValue('password') ?></td>
						</tr>
						<tr>
							<td>eta Date</td>
							<td><?php echo $dbeJRenBroadband->getValue('etaDate') ?></td>
						</tr>
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
	
} // End of class
?>