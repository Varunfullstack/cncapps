<?php
/**
 * Contract Cost report business class
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg["path_gc"] . "/Business.inc.php");
require_once($cfg["path_functions"] . "/activity.inc.php");

class BUContractCosts extends Business
{
    /**
     * Constructor
     * @access Public
     */
    function __construct(&$owner)
    {
        parent::__construct($owner);
        $this->dbeJCallActivity = new DBEJCallActivity($this);
        $this->dbeCallActivitySearch = new DBECallActivitySearch($this);
    }

    function initialiseSearchForm(&$dsData)
    {
        $dsData = new DSForm($this);
        $dsData->addColumn('customerID', DA_STRING, DA_ALLOW_NULL);
        $dsData->addColumn('fromDate', DA_DATE, DA_ALLOW_NULL);
        $dsData->addColumn('toDate', DA_DATE, DA_ALLOW_NULL);
        $dsData->setValue('customerID',
                          '');                                                                            // all
        $dsData->setValue('fromDate', date('y-m-d', strtotime('-5 days')));    // 5 days ago
        $dsData->setValue('toDate',
                          '');                                                                                    // today
    }

    function search(&$dsSearchForm, &$dsResults)
    {
        $this->dbeCallActivitySearch->getRowsBySearchCriteria(
            trim($dsSearchForm->getValue('customerID')),
            trim($dsSearchForm->getValue('fromDate')),
            trim($dsSearchForm->getValue('toDate'))
        );
        $this->dbeCallActivitySearch->fetchNext();

        $dsResults->replicate($this->dbeCallActivitySearch);        // into a dataset for return
    }

}// End of class
?>