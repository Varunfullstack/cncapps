<?php /**
 * Domain renewal business class
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */

use CNCLTD\Encryption;

global $cfg;
require_once($cfg ["path_gc"] . "/Business.inc.php");
require_once($cfg ["path_dbe"] . "/DBEPassword.inc.php");


class BUPassword extends Business
{
    const searchFormCustomerID = 'customerID';
    /** @var DBEPassword */
    public $dbePassword;

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

//    function getRowsByCustomerIDAndPasswordLevel($customerID,
//                                                 $passwordLevel,
//                                                 &$dsResults
//    )
//    {
//        $this->dbePassword->getRowsByCustomerIDAndPasswordLevel(
//            $customerID,
//            $passwordLevel
//        );
//        return ($this->getData(
//            $this->dbePassword,
//            $dsResults
//        ));
//    }

    /**
     * @param $passwordID
     * @param DBEUser $archivingUser
     * @throws Exception
     */
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
            self::searchFormCustomerID,
            DA_STRING,
            DA_ALLOW_NULL
        );
        $dsData->setValue(
            self::searchFormCustomerID,
            null
        );
    }

    /**
     * @param $data
     * @return mixed|null
     * @throws Exception
     */
    public function decrypt($data)
    {
        if (!$data) {
            return null;
        }

        return Encryption::decrypt(
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

        return Encryption::encrypt(
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