<?php /**
 * Call stats business class
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg["path_gc"] . "/Business.inc.php");
require_once($cfg["path_gc"] . "/DSForm.inc.php");
require_once($cfg["path_dbe"] . "/DBECallActivity.inc.php");
require_once($cfg["path_dbe"] . "/DBEJCallActivity.php");

class BUCallStats extends Business
{
    var $dbeJCallActivity = "";

    /**
     * Constructor
     * @access Public
     * @param $owner
     */
    function __construct(&$owner)
    {
        parent::__construct($owner);
        $this->dbeJCallActivity = new DBEJCallActivity($this);
    }

    /**
     * initialise values for input of date range
     * @return DataSet &$dsData results
     * @access public
     */
    function initialiseDataset(&$dsData)
    {
        $this->setMethodName('initialiseDataset');
        $dsData = new DSForm($this);
        $dsData->addColumn('startDate', DA_DATE, DA_ALLOW_NULL);
        $dsData->addColumn('endDate', DA_DATE, DA_ALLOW_NULL);
        $dsData->setValue('startDate', '');
        $dsData->setValue('endDate', '');
    }

    /**
     * Get rows for given date range
     * @parameter date $startDate
     * @parameter date $endDate
     * @return DataSet &$dsResults results
     * @access public
     */
    function getStatsByDateRange($startDate, $endDate, &$dsResults)
    {
        $this->setMethodName('getStatsByDateRange');
        if ($startDate == '') {
            $this->raiseError('startDate not passed');
        }
        if ($endDate == '') {
            $endDate = $startDate;
        }
        $this->dbeJCallActivity->getRowsByDateRange($startDate, $endDate);
        return ($this->getData($this->dbeJCallActivity, $dsResults));
    }
}// End of class
?>