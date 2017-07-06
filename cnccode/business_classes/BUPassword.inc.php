<?
/**
 * Domain renewal business class
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once ($cfg ["path_gc"] . "/Business.inc.php");
require_once ($cfg ["path_dbe"] . "/DBEPassword.inc.php");
require_once ($cfg ["path_dbe"] . "/DBEBasePassword.inc.php");


class BUPassword extends Business {
	var $dbePassword = "";
	/**
	 * Constructor
	 * @access Public
	 */
	function BUPassword(&$owner) {
		$this->constructor ( $owner );
	}
	function constructor(&$owner) {
		parent::constructor ( $owner );
		$this->dbePassword = new DBEPassword ( $this );
    $this->dbeBasePassword = new DBEBasePassword ( $this );
	}
	function updatePassword(&$dsData) {
		$this->setMethodName ( 'updatePassword' );
		$this->updateDataaccessObject ( $dsData, $this->dbePassword );
		
		return TRUE;
	}
	function getPasswordByID($ID, &$dsResults)
	{
		$this->dbePassword->getRow($ID);
		return ($this->getData ( $this->dbePassword, $dsResults ));
	}
	function getRowsByCustomerID($customerID, &$dsResults, $orderBy = false )
	{
    $this->dbePassword->setValue( 'customerID', $customerID );
		$this->dbePassword->getRowsByColumn( 'customerID', 'service' );
		return ($this->getData ( $this->dbePassword, $dsResults ));
	}
  function delete( $passwordID )
  {
    $this->dbePassword->deleteRow( $passwordID );
  }
  /*
  Return a new password from a list of words with a random special char on the end
  */
  function generatePassword()
  {
    $specialChars = array('!','@','#','$','%','&','*','?');
    
    $this->dbeBasePassword->getRandomRow();
    return $this->dbeBasePassword->getValue( 'passwordString' ) .  $specialChars[ array_rand( $specialChars ) ] ;
  }
  
  function initialiseSearchForm(&$dsData) {
    $dsData = new DSForm( $this );
    $dsData->addColumn( 'customerID', DA_STRING, DA_ALLOW_NULL );
    $dsData->setValue( 'customerID', '' );
  }
  
} // End of class
?>