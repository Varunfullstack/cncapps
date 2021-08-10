<?php /*
* Renewal quotation table
* @authors Karim Ahmed
* @access public
*/
global $cfg;
require_once($cfg["path_dbe"] . "/DBECustomerItem.inc.php");

class DBEJRenQuotation extends DBECustomerItem
{
    const customerName = "customerName";
    const siteName = "siteName";
    const itemDescription = "itemDescription";
    const itemTypeDescription = "itemTypeDescription";
    const itemID = "itemID";
    const type = "type";
    const addInstallationCharge = "addInstallationCharge";
    const nextPeriodStartDateYMD = "nextPeriodStartDateYMD";
    const nextPeriodStartDate = "nextPeriodStartDate";
    const nextPeriodEndDateYMD = "nextPeriodEndDateYMD";
    const nextPeriodEndDate = "nextPeriodEndDate";
    const latestQuoteSent = "latestQuoteSent";
    const itemTypeId = 'itemTypeId';

    function __construct(&$owner)
    {
        parent::__construct($owner);
        $this->setAddColumnsOn();
        $this->addColumn(
            self::customerName,
            DA_STRING,
            DA_NOT_NULL,
            "cus_name"
        );
        $this->addColumn(
            self::siteName,
            DA_STRING,
            DA_NOT_NULL,
            "CONCAT(add_add1, ' ', add_town, ' ' , add_postcode)"
        );
        $this->addColumn(
            self::itemDescription,
            DA_STRING,
            DA_NOT_NULL,
            "itm_desc"
        );
        $this->addColumn(
            self::itemTypeDescription,
            DA_STRING,
            DA_NOT_NULL,
            "ity_desc"
        );
        $this->addColumn(
            self::itemID,
            DA_ID,
            DA_NOT_NULL,
            "itm_itemno"
        );
        $this->addColumn(
            self::type,
            DA_STRING,
            DA_NOT_NULL,
            "renQuotationType.description"
        );
        $this->addColumn(
            self::addInstallationCharge,
            DA_YN,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::nextPeriodStartDateYMD,
            DA_DATE,
            DA_NOT_NULL,
            "DATE_FORMAT( DATE_ADD(`startDate`, INTERVAL 1 YEAR ), '%Y-%m-%d') as nextPeriodStartDateYMD"
        );
        $this->addColumn(
            self::nextPeriodStartDate,
            DA_DATE,
            DA_NOT_NULL,
            "DATE_FORMAT( DATE_ADD(`startDate`, INTERVAL 1 YEAR ), '%d/%m/%Y')"
        );
        $this->addColumn(
            self::nextPeriodEndDateYMD,
            DA_DATE,
            DA_NOT_NULL,
            "DATE_FORMAT(         
           DATE_ADD(`startDate`, INTERVAL 2 YEAR )
           , '%Y-%m-%d') as nextPeriodEndDateYMD"
        );
        $this->addColumn(
            self::nextPeriodEndDate,
            DA_DATE,
            DA_NOT_NULL,
            "DATE_FORMAT( 				
 					DATE_ADD(`startDate`, INTERVAL 2 YEAR )
 					, '%d/%m/%Y')"
        );

        $this->addColumn(
            self::latestQuoteSent,
            DA_DATE,
            DA_ALLOW_NULL,
            "(select max(sentDateTime) from quotation where ordheadID = cui_ordno group by ordheadID) as latestQuoteSent"
        );

        $this->addColumn(
            self::itemTypeId,
            DA_INTEGER,
            DA_ALLOW_NULL,
            'itm_itemtypeno'
        );

        $this->setAddColumnsOff();
    }

    function addYearToStartDate($customerItemID)
    {
        $statement =
            "
      UPDATE " . $this->getTableName() .
            " SET startDate = DATE_ADD( `startDate`, INTERVAL 1 YEAR ),
      dateGenerated = null
        WHERE cui_cuino = $customerItemID;";

        $this->setQueryString($statement);
        return $this->runQuery();

    }

    function getRow($pkValue = null)
    {
        $statement =
            "SELECT " . $this->getDBColumnNamesAsString() .
            " FROM " . $this->getTableName() .
            " 	JOIN item ON  itm_itemno = cui_itemno
          JOIN itemtype ON  ity_itemtypeno = itm_itemtypeno
				  JOIN customer ON  cus_custno = cui_custno
          JOIN address ON  add_custno = cui_custno AND add_siteno = cui_siteno
				  LEFT JOIN renQuotationType ON  renQuotationType.renQuotationTypeID = custitem.renQuotationTypeID
				WHERE " . $this->getPKWhere();


        $this->setQueryString($statement);
        $ret = (parent::getRow());
    }

    function getRows($sortColumn = false,
                     $orderDirection = 'ASC'
    )
    {

        $statement =
            "SELECT " . $this->getDBColumnNamesAsString() .
            " FROM " . $this->getTableName() .
            " JOIN item ON  itm_itemno = cui_itemno
        JOIN itemtype ON  ity_itemtypeno = itm_itemtypeno
			  JOIN customer ON  cus_custno = cui_custno
        JOIN address ON  add_custno = cui_custno AND add_siteno = cui_siteno
			LEFT JOIN renQuotationType ON  renQuotationType.renQuotationTypeID = custitem.renQuotationTypeID
			WHERE
        declinedFlag = 'N'
        AND renewalTypeID = 3";

        if ($sortColumn) {
            $statement .= " ORDER BY $sortColumn";
            $statement .= $orderDirection === 'ASC' ? ' asc' : ' desc';
        } else {
            $statement .= " ORDER BY cus_name";
        }
        $this->setQueryString($statement);
        $ret = (parent::getRows());
    }

    function getRowsByCustomerID($customerID)
    {

        $statement =
            "SELECT " . $this->getDBColumnNamesAsString() .
            " FROM " . $this->getTableName() .
            " JOIN item ON  itm_itemno = cui_itemno
      JOIN itemtype ON  ity_itemtypeno = itm_itemtypeno
			JOIN customer ON  cus_custno = cui_custno
      JOIN address ON  add_custno = cui_custno AND add_siteno = cui_siteno
			JOIN renQuotationType ON  renQuotationType.renQuotationTypeID = custitem.renQuotationTypeID
			WHERE declinedFlag = 'N'
				AND cui_custno = $customerID		
        AND renewalTypeID = 3
			ORDER BY cus_name";

        $this->setQueryString($statement);
        $ret = (parent::getRows());
    }

    function getRowsBySalesOrderID($salesOrderID)
    {
        $statement =
            "SELECT " . $this->getDBColumnNamesAsString() .
            " FROM " . $this->getTableName() .
            " JOIN item ON  itm_itemno = cui_itemno
      JOIN itemtype ON  ity_itemtypeno = itm_itemtypeno
			JOIN customer ON  cus_custno = cui_custno
      JOIN address ON  add_custno = cui_custno AND add_siteno = cui_siteno
			JOIN renQuotationType ON  renQuotationType.renQuotationTypeID = custitem.renQuotationTypeID
			WHERE declinedFlag = 'N'
				AND cui_ordno = $salesOrderID		
        AND renewalTypeID = 3
			ORDER BY cus_name";

        $this->setQueryString($statement);
        $ret = (parent::getRows());
    }

    /**
     * Get all renewals due in 1 months time
     *
     * i.e. Start date plus 11 months
     */
    function getRenewalsDueRows()
    {

        $statement =
            "
			SELECT " . $this->getDBColumnNamesAsString() .
            " FROM " . $this->getTableName() .
            " JOIN item ON  itm_itemno = cui_itemno
      JOIN itemtype ON  ity_itemtypeno = itm_itemtypeno
			JOIN customer ON  cus_custno = cui_custno
      JOIN address ON  add_custno = cui_custno AND add_siteno = cui_siteno
			JOIN renQuotationType ON  renQuotationType.renQuotationTypeID = custitem.renQuotationTypeID
			WHERE
        CURDATE() >= ( DATE_ADD(`startDate`, INTERVAL 11 MONTH) )
			  AND dateGenerated IS NULL
		    AND declinedFlag = 'N'
        AND renewalTypeID = 3 and directDebitFlag <> 'Y' and item.itm_itemtypeno <> 57";
        $statement .= " ORDER BY cui_custno,  custitem.renQuotationTypeID,  ity_desc ASC";

        $this->setQueryString($statement);
        $ret = (parent::getRows());
    }

    /**
     * Get all renewals in the passed array of IDs
     *
     */
    function getRenewalsByIDList($customerItemIDs)
    {

        $commaListOfIDs = implode(
            ',',
            $customerItemIDs
        );

        $statement =
            "
      SELECT " . $this->getDBColumnNamesAsString() .
            " FROM " . $this->getTableName() .
            " JOIN item ON  itm_itemno = cui_itemno
      JOIN itemtype ON  ity_itemtypeno = itm_itemtypeno
      JOIN customer ON  cus_custno = cui_custno
      JOIN address ON  add_custno = cui_custno AND add_siteno = cui_siteno
      JOIN renQuotationType ON  renQuotationType . renQuotationTypeID = custitem . renQuotationTypeID
      WHERE cui_cuino IN(" . $commaListOfIDs . ")
    AND renewalTypeID = 3 and directDebitFlag <> 'Y'
      ORDER BY cui_custno,  custitem . renQuotationTypeID
     ";

        $this->setQueryString($statement);
        $ret = (parent::getRows());
    }

    /**
     * Get all renewals where quote has been generated last 2 weeks
     *
     * i.e. Start date plus 11 months
     *
     */
    function getRecentQuotesRows()
    {

        $statement =
            "
			SELECT " . $this->getDBColumnNamesAsString() .
            " FROM " . $this->getTableName() .
            " JOIN item ON  itm_itemno = cui_itemno
      JOIN itemtype ON  ity_itemtypeno = itm_itemtypeno
			JOIN customer ON  cus_custno = cui_custno
      JOIN address ON  add_custno = cui_custno AND add_siteno = cui_siteno
			JOIN renQuotationType ON  renQuotationType . renQuotationTypeID = custitem . renQuotationTypeID
			WHERE dateGenerated > DATE_SUB(CURDATE(), INTERVAL 2 WEEK )
		 AND declinedFlag = 'N'
    AND renewalTypeID = 3 and item.itm_itemtypeno <> 57
		 ORDER BY cui_custno
		 ";

        $this->setQueryString($statement);
        $ret = (parent::getRows());
    }

}

?>
