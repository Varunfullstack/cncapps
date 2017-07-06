<?
/**
 * Domain renewal business class
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once ($cfg ["path_gc"] . "/Business.inc.php");
require_once ($cfg ["path_bu"] . "/BUCustomerItem.inc.php");
require_once ($cfg ["path_bu"] . "/BUSalesOrder.inc.php");
require_once ($cfg ["path_dbe"] . "/DBECustomerItem.inc.php");
require_once ($cfg ["path_dbe"] . "/DBEOrdline.inc.php");
require_once ($cfg ["path_dbe"] . "/DBERenDomain.inc.php");
require_once ($cfg ["path_dbe"] . "/DBEArecord.inc.php");
require_once ($cfg ["path_bu"] . "/BUMail.inc.php");

class BURenDomain extends Business {
	var $dbeRenDomain = "";
  var $dbeArecord = "";
	/**
	 * Constructor
	 * @access Public
	 */
	function BURenDomain(&$owner) {
		$this->constructor ( $owner );
	}
	function constructor(&$owner) {
		parent::constructor ( $owner );
		$this->dbeRenDomain = new DBECustomerItem ( $this );
    $this->dbeArecord = new DBEArecord( $this );
		$this->dbeJRenDomain = new DBEJRenDomain ( $this );
	}
	function updateRenDomain(&$dsData) {
		$this->setMethodName ( 'updateRenDomain' );
		$this->updateDataaccessObject ( $dsData, $this->dbeRenDomain );
		
		return TRUE;
	}
  function getArecordById($ID, &$dsResults)
  {
    $this->dbeArecord->getRow ( $ID );
    return ($this->getData ( $this->dbeArecord, $dsResults ));
  }
  function updateArecord(&$dsData) {
    $this->setMethodName ( 'updateArecord' );
    $this->updateDataaccessObject ( $dsData, $this->dbeArecord );
    
    return TRUE;
  }
  function deleteArecord( $arecordID )
  {
    $this->dbeArecord->deleteRow( $arecordID );
  }
	function getRenDomainByID($ID, &$dsResults)
	{
		$this->dbeJRenDomain->setPKValue ( $ID );
		$this->dbeJRenDomain->getRow ();
		return ($this->getData ( $this->dbeJRenDomain, $dsResults ));
	}
	function getAll(&$dsResults, $orderBy = false )
	{
		$this->dbeJRenDomain->getRows ( $orderBy );
		return ($this->getData ( $this->dbeJRenDomain, $dsResults ));
	}
	function deleteRenDomain($ID) {
		$this->setMethodName ( 'deleteRenDomain' );
		if ($this->canDeleteRenDomain ( $ID )) {
			return $this->dbeRenDomain->deleteRow ( $ID );
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
		
	// create a renewal
	}
	function getRenewalIDByCustomerItemID( $customerItemID ) 
	{

		$this->dbeRenDomain->setValue ( 'customerItemID', $customerItemID );
		$this->dbeRenDomain->getRowsByColumn ( 'customerItemID' );
		$this->dbeRenDomain->fetchNext ();
		
		return ($this->dbeRenDomain->getPKValue ());
	
	}
	function emailRenewalsSalesOrdersDue()
	{
		$this->dbeJRenDomain->getRenewalsDueRows();
	
    $buMail = new BUMail( $this );

		$toEmail = CONFIG_SALES_MANAGER_EMAIL;
		$senderEmail = CONFIG_SALES_EMAIL;
		
		$hdrs =
			array (
				'From' => $senderEmail,
				'To' => $toEmail,
				'Subject' => 'Domain Renewals Due Today',
				'Date' => date ( "r" )
			);

		ob_start();?>
			<HTML>
				<BODY>
					<TABLE border="1">
						<tr>
							<td bgcolor="#999999">Customer</td>
							<td bgcolor="#999999">Service</td>
							<td bgcolor="#999999">Domain</td>
							<td bgcolor="#999999">Expires</td>
						</tr>
						<?php while( $this->dbeJRenDomain->fetchNext() ){?>
						<tr>
							<td><?php echo $this->dbeJRenDomain->getValue('customerName') ?></td>
							<td><?php echo $this->dbeJRenDomain->getValue('itemDescription') ?></td>
							<td><?php echo $this->dbeJRenDomain->getValue('notes') ?></td>
							<td><?php echo $this->dbeJRenDomain->getValue('invoiceToDate') ?></td>
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
	function createRenewalsSalesOrders($renewalIDs = false)
	{
		$buSalesOrder = new BUSalesOrder ( $this );
		
		$buInvoice = new BUInvoice ( $this );
		
    if ( $renewalIDs ){

      $this->dbeJRenDomain->getRenewalsRowsByID ($renewalIDs );
      
    }  
    else{
      
      $this->dbeJRenDomain->getRenewalsDueRows ();

    }    
		
		$dbeRenDomainUpdate = new DBECustomerItem( $this );
		
		$dbeJCustomerItem = new DBEJCustomerItem ( $this );
		
		$dbeCustomer = new DBECustomer ( $this );
		
		$dbeOrdline = new DBEOrdline ( $this );
		
		$previousCustomerID = 99999;
		
		while ( $this->dbeJRenDomain->fetchNext () ) {
			
			if ($dbeJCustomerItem->getRow ( $this->dbeJRenDomain->getValue ( 'customerItemID' ) )) {
				/*
				 * Group many domains for same customer under one sales order
				 */
				if ( $previousCustomerID != $dbeJCustomerItem->getValue ( 'customerID' ) ){
					/*
					 *  create order header
					 */
					$dbeCustomer->getRow ( $dbeJCustomerItem->getValue ( 'customerID' ) );
					$this->getData ( $dbeCustomer, $dsCustomer );
          
          if ( $renewalIDs ){
            $buSalesOrder->InitialiseQuote( $dsOrdhead, $dsOrdline, $dsCustomer );
          }
          else{
            $buSalesOrder->InitialiseOrder( $dsOrdhead, $dsOrdline, $dsCustomer );
          }

					$line = -1;	// initialise sales order line seq
				}
				// period comment line
				$line ++;
				$description = $this->dbeJRenDomain->getValue ( 'notes' );
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
				
				
				$line++;
				/*
				 * Get stock category from item table
				 */
				$buItem = new BUItem($this);
				$buItem->getItemByID($dbeJCustomerItem->getValue ( 'itemID' ), $dsItem );
				$dbeOrdline->setValue('stockcat', $dsItem->getValue('stockcat'));
				
				$dbeOrdline->setValue ( 'renewalCustomerItemID', $this->dbeJRenDomain->getValue ( 'customerItemID' ) );
				$dbeOrdline->setValue ( 'ordheadID', $dsOrdhead->getValue ( 'ordheadID' ) );
				$dbeOrdline->setValue ( 'customerID', $dsOrdhead->getValue ( 'customerID' ) );
				$dbeOrdline->setValue ( 'itemID', $dbeJCustomerItem->getValue ( 'itemID' ) );
				$dbeOrdline->setValue ( 'description', $dbeJCustomerItem->getValue ( 'itemDescription' ) );
				$dbeOrdline->setValue ( 'supplierID', CONFIG_SALES_STOCK_SUPPLIERID );
				$dbeOrdline->setValue ( 'sequenceNo', $line );
				$dbeOrdline->setValue ( 'lineType', 'I' );
				$dbeOrdline->setValue ( 'qtyOrdered', 1 ); // default 1
				$dbeOrdline->setValue ( 'qtyDespatched', 0 );
				$dbeOrdline->setValue ( 'qtyLastDespatched', 0 );
				$dbeOrdline->setValue ( 'curUnitSale', ($dsItem->getValue ( 'curUnitSale' ) / 12) * $this->dbeJRenDomain->getValue ( 'invoicePeriodMonths' ) );
				$dbeOrdline->setValue ( 'curUnitCost', ($dsItem->getValue ( 'curUnitCost' ) / 12) * $this->dbeJRenDomain->getValue ( 'invoicePeriodMonths' ) );
				
				$dbeOrdline->insertRow ();
				
				// period comment line
				$line ++;
				$description = $this->dbeJRenDomain->getValue ( 'invoiceFromDate' ) . ' to ' . $this->dbeJRenDomain->getValue ( 'invoiceToDate' );
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
				$dbeRenDomainUpdate->setValue('customerItemID', $this->dbeJRenDomain->getPKValue() );
				$dbeRenDomainUpdate->getRow();
				$dbeRenDomainUpdate->setValue('totalInvoiceMonths',
					$this->dbeJRenDomain->getValue('totalInvoiceMonths') +
					$this->dbeJRenDomain->getValue('invoicePeriodMonths')
				);
				$dbeRenDomainUpdate->updateRow();
				
				$previousCustomerID = $dbeJCustomerItem->getValue ( 'customerID' );
				
			}

		}
    /* there will only be one order in this case */
    if ( $renewalIDs ){
      
      return $dsOrdhead->getValue ( 'ordheadID' );
    }
	
	}
		
	function isCompleted( $customerItemID )
	{
		$ID = $this->getRenewalIDByCustomerItemID ( $customerItemID );
		
		if ( $ID ){

			$this->dbeRenDomain->getRow ( $ID );
		
		}
		else{
			$this->raiseError( 'Renewal row not found');
		}
		
		if
		(
			$this->dbeRenDomain->getValue( 'installationDate') &&
			$this->dbeRenDomain->getValue( 'invoicePeriodMonths')
			){
			$ret = true;
		
		}
		
		return $ret;
	
	}
} // End of class
?>