<?
require_once($cfg["path_gc"]."/Business.inc.php");
require_once($cfg["path_dbe"]."/DBEPortalCustomerDocument.inc.php");

class BUPortalCustomerDocument extends Business{
	var $dbePortalCustomerDocument="";
  var $dbePortalCustomerDocumentWithoutFile="";
	var $dbeCallActivity="";

	function BUPortalCustomerDocument(&$owner){
		$this->constructor($owner);
	}
	function constructor(&$owner){
		parent::constructor($owner);
		$this->dbePortalCustomerDocument = new DBEPortalCustomerDocument($this);
    $this->dbePortalCustomerDocumentWithoutFile = new DBEPortalCustomerDocumentWithoutFile($this);
	}
	function updateDocument(&$dsData, $userfile ){
		$this->setMethodName('updateDocument');
    /**
    * Upload new document from local disk
    * @access private
    */
    $this->updateDataaccessObject($dsData, $this->dbePortalCustomerDocumentWithoutFile);

    /* file to add? */
    if ( $userfile[ 'name' ] != '' ){
      $this->dbePortalCustomerDocument->getRow( $this->dbePortalCustomerDocumentWithoutFile->getPKValue() );
      $this->dbePortalCustomerDocument->setValue ( 'file', fread ( fopen ( $userfile ['tmp_name'], 'rb' ), $userfile ['size'] ) );
      $this->dbePortalCustomerDocument->setValue ( 'filename', ( string ) $userfile ['name'] );
      $this->dbePortalCustomerDocument->setValue ( 'fileMimeType', ( string ) $userfile ['type'] );
      $this->dbePortalCustomerDocument->updateRow();
    }

		return TRUE;
	}

	function getDocumentByID($ID, &$dsResults)
	{
		$this->dbePortalCustomerDocument->setPKValue($ID);
		$this->dbePortalCustomerDocument->getRow();
		return ($this->getData($this->dbePortalCustomerDocument, $dsResults));
	}

	function getDocumentsByCustomerID($customerID, &$dsResults)
	{
    $this->dbePortalCustomerDocument->setValue( 'customerID', $customerID);
		$this->dbePortalCustomerDocument->getRowsByColumn( 'customerID', 'description' );
		return ($this->getData($this->dbePortalCustomerDocument, $dsResults));
	}

	function deleteDocument($ID){
		$this->setMethodName('deleteDocument');
		if ( $this->canDelete($ID) ){
			return $this->dbePortalCustomerDocument->deleteRow($ID);
		}
		else{
			return FALSE;
		}
	}

	function canDelete($ID){
	  return TRUE;
	}

}// End of class
?>