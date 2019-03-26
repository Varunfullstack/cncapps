<?php /**
 * Domain renewal business class
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg ["path_gc"] . "/Business.inc.php");
require_once($cfg ["path_dbe"] . "/DBEPassword.inc.php");


class BUPassword extends Business
{
    public $dbePassword;
    public $dbeBasePassword;

    /**
     * Constructor
     * @access Public
     */
    function __construct(&$owner)
    {
        parent::__construct($owner);
        $this->dbePassword = new DBEPassword ($this);
    }

    function updatePassword(&$dsData)
    {
        $this->setMethodName('updatePassword');
        $this->updateDataAccessObject(
            $dsData,
            $this->dbePassword
        );

        return TRUE;
    }

    function getPasswordByID($ID,
                             &$dsResults
    )
    {
        $this->dbePassword->getRow($ID);
        return ($this->getData(
            $this->dbePassword,
            $dsResults
        ));
    }

    function getRowsByCustomerIDAndPasswordLevel($customerID,
                                                 $passwordLevel,
                                                 &$dsResults,
                                                 $orderBy = false
    )
    {
        $this->dbePassword->getRowsByCustomerIDAndPasswordLevel(
            $customerID,
            $passwordLevel
        );
        return ($this->getData(
            $this->dbePassword,
            $dsResults
        ));
    }

    function archive($passwordID,
                     DBEUser $archivingUser
    )
    {
        $passwordItem = new DBEPassword($this);

        $passwordItem->getRow($passwordID);
        $passwordItem->setValue(
            DBEPassword::archivedAt,
            (new DateTime())->format(COMMON_MYSQL_DATETIME)
        );
        $passwordItem->setValue(
            DBEPassword::archivedBy,
            $archivingUser->getValue(DBEUser::name)
        );
        $passwordItem->updateRow();
    }

    function delete($passwordID)
    {
        $this->dbePassword->deleteRow($passwordID);
    }

    /*
    Return a new password from a list of words with a random special char on the end
    */
    function initialiseSearchForm(&$dsData)
    {
        $dsData = new DSForm($this);
        $dsData->addColumn(
            'customerID',
            DA_STRING,
            DA_ALLOW_NULL
        );
        $dsData->setValue(
            'customerID',
            ''
        );
    }

    public function getArchivedRowsByPasswordLevel($passwordLevel,
                                                   $dsResults
    )
    {
        $this->dbePassword->getArchivedRowsByPasswordLevel(
            $passwordLevel
        );
        return ($this->getData(
            $this->dbePassword,
            $dsResults
        ));
    }

    public function decrypt($data)
    {
        if (!$data) {
            return null;
        }

        return \CNCLTD\Encryption::decrypt(
            PASSWORD_ENCRYPTION_PRIVATE_KEY,
            PASSWORD_PASSPHRASE,
            $data
        );
    }

    public function encrypt($data)
    {
        if (!$data) {
            return null;
        }

        return \CNCLTD\Encryption::encrypt(
            PASSWORD_ENCRYPTION_PUBLIC_KEY,
            $data
        );
    }

    public function getArchivedRowsByCustomer($customerID,
                                              $dsResults
    )
    {
        $this->dbePassword->getArchivedRowsByCustomerID(
            $customerID
        );
        return ($this->getData(
            $this->dbePassword,
            $dsResults
        ));
    }

} // End of class
?>