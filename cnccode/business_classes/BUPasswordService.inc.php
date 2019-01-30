<?php /**
 * Call further action business class
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg["path_gc"] . "/Business.inc.php");
require_once($cfg["path_dbe"] . "/DBEPasswordService.inc.php");
require_once($cfg["path_dbe"] . "/DBEPassword.inc.php");

class BUPasswordService extends Business
{
    /** @var DBEPasswordService */
    public $dbePasswordService;

    /**
     * Constructor
     * @access Public
     * @param $owner
     */
    function __construct(&$owner)
    {
        parent::__construct($owner);
        $this->dbePasswordService = new DBEPasswordService($this);
    }

    function updatePasswordService(&$dsData)
    {
        $this->setMethodName('updatePasswordService');
        $this->updateDataAccessObject(
            $dsData,
            $this->dbePasswordService
        );
        return TRUE;
    }

    function getPasswordServiceByID($ID,
                                    &$dsResults
    )
    {
        $this->dbePasswordService->setPKValue($ID);
        $this->dbePasswordService->getRow();
        return ($this->getData(
            $this->dbePasswordService,
            $dsResults
        ));
    }

    function getAllTypes(&$dsResults)
    {
        $this->dbePasswordService->getRows();
        return ($this->getData(
            $this->dbePasswordService,
            $dsResults
        ));
    }

    function deletePasswordService($ID)
    {
        $this->setMethodName('deletePasswordService');
        $dbePassword = new DBEPassword($this);
        $dbePassword->setValue(
            DBEPassword::serviceID,
            $ID
        );
        $dbePassword->getRowsByColumn(DBEPassword::serviceID);

        if ($dbePassword->rowCount) {
            throw new Exception('Cannot delete because this service is in use');
        }
        return $this->dbePasswordService->deleteRow($ID);
    }

    public function getPasswordServiceByTypeID(int $passwordServiceTypeID,
                                               &$dsResults
    )
    {
        $this->dbePasswordService->getRowsByTypeID($passwordServiceTypeID);
        return ($this->getData(
            $this->dbePasswordService,
            $dsResults
        ));
    }
}// End of class
?>