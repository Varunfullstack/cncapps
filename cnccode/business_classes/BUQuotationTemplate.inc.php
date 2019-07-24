<?php /**
 * Call further action business class
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg["path_gc"] . "/Business.inc.php");
require_once($cfg["path_dbe"] . "/DBEQuotationTemplate.inc.php");
require_once($cfg["path_dbe"] . "/DBEPassword.inc.php");

class BUQuotationTemplate extends Business
{
    /** @var DBEQuotationTemplate */
    public $dbeQuotationTemplate;

    /**
     * Constructor
     * @access Public
     * @param $owner
     */
    function __construct(&$owner)
    {
        parent::__construct($owner);
        $this->dbeQuotationTemplate = new DBEQuotationTemplate($this);
    }

    function updateQuotationTemplate(&$dsData)
    {
        $this->setMethodName('updateQuotationTemplate');
        $this->updateDataAccessObject(
            $dsData,
            $this->dbeQuotationTemplate
        );
        return TRUE;
    }

    function getQuotationTemplateByID($ID,
                                      &$dsResults
    )
    {
        $this->dbeQuotationTemplate->setPKValue($ID);
        $this->dbeQuotationTemplate->getRow();
        return ($this->getData(
            $this->dbeQuotationTemplate,
            $dsResults
        ));
    }

    function getAllTypes(&$dsResults)
    {
        $this->dbeQuotationTemplate->getRows();
        return ($this->getData(
            $this->dbeQuotationTemplate,
            $dsResults
        ));
    }

    function deleteQuotationTemplate($ID)
    {
        $this->setMethodName('deleteQuotationTemplate');
        return $this->dbeQuotationTemplate->deleteRow($ID);
    }
}// End of class
?>