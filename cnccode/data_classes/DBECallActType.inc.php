<?php /*
* Call activity table
* @authors Karim Ahmed
* @access public
*/
global $cfg;

use CNCLTD\SortableDBE;

require_once($cfg["path_gc"] . "/DBEntity.inc.php");

class DBECallActType extends DBEntity
{
    use SortableDBE;

    const callActTypeID                    = "callActTypeID";
    const description                      = "description";
    const oohMultiplier                    = "oohMultiplier";
    const itemID                           = "itemID";
    const maxHours                         = "maxHours";
    const minHours                         = "minHours";
    const customerEmailFlag                = "customerEmailFlag";
    const requireCheckFlag                 = "requireCheckFlag";
    const curValueFlag                     = "curValueFlag";
    const travelFlag                       = "travelFlag";
    const activeFlag                       = "activeFlag";
    const engineerOvertimeFlag             = "engineerOvertimeFlag";
    const onSiteFlag                       = "onSiteFlag";
    const portalDisplayFlag                = "portalDisplayFlag";
    const activityNotesRequired            = "activityNotesRequired";
    const visibleInSRFlag                  = "visibleInSRFlag";
    const catRequireCNCNextActionCNCAction = "catRequireCNCNextActionCNCAction";
    const catRequireCustomerNoteCNCAction  = "catRequireCustomerNoteCNCAction";
    const catRequireCNCNextActionOnHold    = "catRequireCNCNextActionOnHold";
    const catRequireCustomerNoteOnHold     = "catRequireCustomerNoteOnHold";
    const minMinutesAllowed                = "minMinutesAllowed";
    const orderNum                         = "orderNum";

    /**
     * calls constructor()
     * @access public
     * @param void
     * @return void
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
        $this->addColumn(
            self::requireCheckFlag,
            DA_YN,
            DA_NOT_NULL,
            "cat_req_check_flag"
        );        // rquires checking before sales order
        $this->addColumn(
            self::curValueFlag,
            DA_YN,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::activityNotesRequired,
            DA_YN,
            DA_NOT_NULL
        );
        $this->addColumn(self::travelFlag, DA_YN, DA_NOT_NULL);            // is this a travel activity?
        $this->addColumn(self::activeFlag, DA_YN, DA_NOT_NULL);            // is	this an active activity?
        $this->addColumn(self::engineerOvertimeFlag, DA_YN, DA_NOT_NULL);            // Allow engineer overtime
        $this->addColumn(self::onSiteFlag, DA_YN, DA_NOT_NULL, "cat_on_site_flag");
        $this->addColumn(self::portalDisplayFlag, DA_YN, DA_NOT_NULL, "cat_portal_display_flag");
        $this->addColumn(self::visibleInSRFlag, DA_YN, DA_NOT_NULL, 'cat_visible_in_sr_flag');
        $this->addColumn(
            self::catRequireCNCNextActionCNCAction,
            DA_INTEGER,
            DA_ALLOW_NULL,
            'catRequireCNCNextActionCNCAction'
        );
        $this->addColumn(
            self::catRequireCustomerNoteCNCAction,
            DA_INTEGER,
            DA_ALLOW_NULL,
            'catRequireCustomerNoteCNCAction'
        );
        $this->addColumn(
            self::catRequireCNCNextActionOnHold,
            DA_INTEGER,
            DA_ALLOW_NULL,
            'catRequireCNCNextActionOnHold'
        );
        $this->addColumn(self::catRequireCustomerNoteOnHold, DA_INTEGER, DA_ALLOW_NULL, 'catRequireCustomerNoteOnHold');
        $this->addColumn(self::minMinutesAllowed, DA_INTEGER, DA_NOT_NULL, 'minMinutesAllowed');
        $this->addColumn(self::orderNum, DA_INTEGER, DA_NOT_NULL, 'orderNum');
        $this->setAddColumnsOff();
        $this->setPK(0);
    }

    function getActiveAndVisibleRows($onlyVisibleInSR = false)
    {
        $statement = "SELECT " . $this->getDBColumnNamesAsString() . " FROM " . $this->getTableName(
            ) . " WHERE activeFlag = 'Y'" . ($onlyVisibleInSR ? ' and cat_visible_in_sr_flag = "Y" ' : '') . " ORDER BY cat_desc";
        $this->setQueryString($statement);
        $ret = (parent::getRows());
    }

    protected function getSortOrderForItem($id)
    {
        $this->getRow($id);
        return $this->getValue(self::orderNum);
    }

    protected function getSortOrderColumnName()
    {
        return $this->getDBColumnName(self::orderNum);
    }

    protected function getDB()
    {
        global $db;
        return $db;
    }
}

?>