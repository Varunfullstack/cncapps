<?php /*
* Call activity table
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_gc"] . "/DBEntity.inc.php");

class DBECallActType extends DBEntity
{
    const callActTypeID = "callActTypeID";
    const description = "description";
    const oohMultiplier = "oohMultiplier";
    const itemID = "itemID";
    const maxHours = "maxHours";
    const minHours = "minHours";
    const customerEmailFlag = "customerEmailFlag";
    const requireCheckFlag = "requireCheckFlag";
    const allowExpensesFlag = "allowExpensesFlag";
    const allowReasonFlag = "allowReasonFlag";
    const allowActionFlag = "allowActionFlag";
    const allowFinalStatusFlag = "allowFinalStatusFlag";
    const reqReasonFlag = "reqReasonFlag";
    const reqActionFlag = "reqActionFlag";
    const reqFinalStatusFlag = "reqFinalStatusFlag";
    const allowSCRFlag = "allowSCRFlag";
    const curValueFlag = "curValueFlag";
    const travelFlag = "travelFlag";
    const activeFlag = "activeFlag";
    const showNotChargeableFlag = "showNotChargeableFlag";
    const engineerOvertimeFlag = "engineerOvertimeFlag";
    const onSiteFlag = "onSiteFlag";
    const portalDisplayFlag = "portalDisplayFlag";
    const visibleInSRFlag = "visibleInSRFlag";

    /**
     * calls constructor()
     * @access public
     * @return void
     * @param  void
     * @see constructor()
     */
    function __construct(&$owner)
    {
        parent::__construct($owner);
        $this->setTableName("callacttype");
        $this->addColumn(self::callActTypeID, DA_ID, DA_NOT_NULL, "cat_callacttypeno");
        $this->addColumn(self::description, DA_STRING, DA_NOT_NULL, "cat_desc");
        $this->addColumn(self::oohMultiplier, DA_FLOAT, DA_ALLOW_NULL, "cat_ooh_multiplier");
        $this->addColumn(self::itemID, DA_INTEGER, DA_ALLOW_NULL, "cat_itemno");
        $this->addColumn(self::maxHours, DA_FLOAT, DA_ALLOW_NULL, "cat_max_hours");
        $this->addColumn(self::minHours, DA_FLOAT, DA_ALLOW_NULL, "cat_min_hours");
        $this->addColumn(self::customerEmailFlag, DA_YN, DA_NOT_NULL);// send emails to customers
        $this->addColumn(self::requireCheckFlag,
                         DA_YN,
                         DA_NOT_NULL,
                         "cat_req_check_flag");        // rquires checking before sales order
        $this->addColumn(self::allowExpensesFlag,
                         DA_YN,
                         DA_NOT_NULL,
                         "cat_allow_exp_flag");    // allow expenses against this type
        $this->addColumn(self::allowReasonFlag,
                         DA_YN,
                         DA_NOT_NULL,
                         "cat_problem_flag");                // allow problem notes
        $this->addColumn(self::allowActionFlag, DA_YN, DA_NOT_NULL, "cat_action_flag");
        $this->addColumn(self::allowFinalStatusFlag, DA_YN, DA_NOT_NULL, "cat_resolve_flag");
        $this->addColumn(self::reqReasonFlag,
                         DA_YN,
                         DA_NOT_NULL,
                         "cat_r_problem_flag");    // whether these notepads are required
        $this->addColumn(self::reqActionFlag, DA_YN, DA_NOT_NULL, "cat_r_action_flag");
        $this->addColumn(self::reqFinalStatusFlag, DA_YN, DA_NOT_NULL, "cat_r_resolve_flag");
        $this->addColumn(self::allowSCRFlag, DA_YN, DA_NOT_NULL);
        $this->addColumn(self::curValueFlag,
                         DA_YN,
                         DA_NOT_NULL);                                                        // is this activity type a currency value
        $this->addColumn(self::travelFlag, DA_YN, DA_NOT_NULL);            // is this a travel activity?
        $this->addColumn(self::activeFlag, DA_YN, DA_NOT_NULL);            // is	this an active activity?
        $this->addColumn(self::showNotChargeableFlag,
                         DA_YN,
                         DA_NOT_NULL);            // show charagable text on activity emails?
        $this->addColumn(self::engineerOvertimeFlag, DA_YN, DA_NOT_NULL);            // Allow engineer overtime
        $this->addColumn(self::onSiteFlag, DA_YN, DA_NOT_NULL, "cat_on_site_flag");
        $this->addColumn(self::portalDisplayFlag, DA_YN, DA_NOT_NULL, "cat_portal_display_flag");
        $this->addColumn(self::visibleInSRFlag, DA_YN, DA_NOT_NULL, 'cat_visible_in_sr_flag');
        $this->setAddColumnsOff();
        $this->setPK(0);
    }

    function getActiveAndVisibleRows($onlyVisibleInSR = false)
    {
        $statement =
            "SELECT " . $this->getDBColumnNamesAsString() .
            " FROM " . $this->getTableName() .
            " WHERE activeFlag = 'Y'" .
            ($onlyVisibleInSR ? ' and cat_visible_in_sr_flag = "Y" ' : '') .
            " ORDER BY cat_desc";
        $this->setQueryString($statement);
        $ret = (parent::getRows());
    }

}

class DBEJCallActType extends DBECallActType
{
    /**
     * calls constructor()
     * @access public
     * @return void
     * @param  void
     * @see constructor()
     */
    function __construct(&$owner)
    {
        parent::__construct($owner);
        $this->setAddColumnsOn();
        $this->addColumn("itemDescription", DA_STRING, DA_NOT_NULL, "itm_desc");        // linked item
        $this->addColumn("itemSalePrice", DA_STRING, DA_NOT_NULL, "itm_sstk_price");
        $this->setPK(0);
        $this->setAddColumnsOff();
    }

    function getRow($pkValue = false)
    {

        if ($pkValue) {
            $this->setPKValue($pkValue);
        }

        $statement =
            "SELECT " . $this->getDBColumnNamesAsString() .
            " FROM " . $this->getTableName() .
            " LEFT JOIN item ON itm_itemno = cat_itemno" .
            " WHERE " . $this->getPKWhere();
        $this->setQueryString($statement);
        $ret = (parent::getRow());
    }

    function getActiveRows()
    {
        $statement =
            "SELECT " . $this->getDBColumnNamesAsString() .
            " FROM " . $this->getTableName() .
            " LEFT JOIN item ON itm_itemno = cat_itemno" .
            " WHERE activeFlag = 'Y'" .
            " ORDER BY cat_desc";
        $this->setQueryString($statement);
        $ret = (parent::getRows());
    }
}

?>