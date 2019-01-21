<?php /**
 * Call further action business class
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg["path_gc"] . "/Business.inc.php");
require_once($cfg["path_dbe"] . "/DBEPasswordService.inc.php");

class BUPasswordService extends Business
{
    /** @var DBEPasswordService  */
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