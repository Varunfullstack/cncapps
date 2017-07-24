<?php /*
* Item table
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_dbe"] . "/DBCNCEntity.inc.php");

class DBECustomerItem extends DBCNCEntity
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
        $this->setTableName("custitem");
        $this->addColumn("customerItemID", DA_ID, DA_NOT_NULL, "custitem.cui_cuino");
        $this->addColumn("customerID", DA_INTEGER, DA_NOT_NULL, "custitem.cui_custno");
        $this->addColumn("siteNo", DA_INTEGER, DA_NOT_NULL, "custitem.cui_siteno");
        $this->addColumn("itemID", DA_INTEGER, DA_NOT_NULL, "custitem.cui_itemno");
        $this->addColumn("warrantyID", DA_INTEGER, DA_NOT_NULL, "custitem.cui_man_contno");
        $this->addColumn("userID", DA_INTEGER, DA_ALLOW_NULL, "custitem.cui_consno");
        $this->addColumn("serialNo", DA_STRING, DA_ALLOW_NULL, "custitem.cui_serial");
        $this->addColumn("serverName", DA_STRING, DA_ALLOW_NULL, "custitem.cui_cust_ref");
        $this->addColumn("despatchDate", DA_DATE, DA_ALLOW_NULL, "custitem.cui_desp_date");
        $this->addColumn("ordheadID", DA_INTEGER, DA_ALLOW_NULL, "custitem.cui_ordno");
        $this->addColumn("porheadID", DA_INTEGER, DA_ALLOW_NULL, "custitem.cui_porno");
        $this->addColumn("curUnitSale", DA_FLOAT, DA_ALLOW_NULL, "custitem.cui_sale_price");
        $this->addColumn("curUnitCost", DA_FLOAT, DA_ALLOW_NULL, "custitem.cui_cost_price");
        $this->addColumn("sOrderDate", DA_DATE, DA_ALLOW_NULL, "custitem.cui_ord_date");
        $this->addColumn("users", DA_INTEGER, DA_ALLOW_NULL, "custitem.cui_users");
        $this->addColumn("expiryDate", DA_DATE, DA_ALLOW_NULL, "custitem.cui_expiry_date"); // only has a value if this is a contract customer item
        $this->addColumn("curGSCBalance", DA_FLOAT, DA_ALLOW_NULL, "custitem.curGSCBalance");        // only has a value if this is a contract customer item
        $this->addColumn("renewalStatus", DA_STRING, DA_ALLOW_NULL, "custitem.renewalStatus");      // R=Renewed, D=Renewal Declined
        $this->addColumn("customerItemNotes", DA_MEMO, DA_ALLOW_NULL, "custitem.itemNotes");
        $this->addColumn("slaResponseHours", DA_INTEGER, DA_ALLOW_NULL, "custitem.cui_sla_response_hours");

        /* broadband */
        $this->addColumn("months", DA_INTEGER, DA_ALLOW_NULL, 'custitem.months');
        $this->addColumn("salePricePerMonth", DA_FLOAT, DA_ALLOW_NULL, "custitem.salePricePerMonth");
        $this->addColumn("adslPhone", DA_STRING, DA_ALLOW_NULL, "custitem.adslPhone");
        $this->addColumn("macCode", DA_STRING, DA_ALLOW_NULL, "custitem.macCode");
        $this->addColumn("reference", DA_STRING, DA_ALLOW_NULL, "custitem.reference");
        $this->addColumn("defaultGateway", DA_STRING, DA_ALLOW_NULL, "custitem.defaultGateway");
        $this->addColumn("networkAddress", DA_STRING, DA_ALLOW_NULL, "custitem.networkAddress");
        $this->addColumn("subnetMask", DA_STRING, DA_ALLOW_NULL, "custitem.subnetMask");
        $this->addColumn("routerIPAddress", DA_STRING, DA_ALLOW_NULL, "custitem.routerIPAddress");
        $this->addColumn("hostingUserName", DA_STRING, DA_ALLOW_NULL, "custitem.hostingUserName");
        $this->addColumn("userName", DA_STRING, DA_ALLOW_NULL, "custitem.userName");
        $this->addColumn("password", DA_STRING, DA_ALLOW_NULL, "custitem.password");
        $this->addColumn("etaDate", DA_DATE, DA_ALLOW_NULL, "custitem.etaDate");
        $this->addColumn("installationDate", DA_DATE, DA_ALLOW_NULL, "custitem.installationDate");
        $this->addColumn("costPricePerMonth", DA_FLOAT, DA_ALLOW_NULL, "custitem.costPricePerMonth");
        $this->addColumn("ispID", DA_STRING, DA_ALLOW_NULL, "custitem.ispID");
        $this->addColumn("dualBroadbandFlag", DA_YN, DA_ALLOW_NULL, "custitem.dualBroadbandFlag");
        $this->addColumn("dnsCompany", DA_STRING, DA_ALLOW_NULL, "custitem.dnsCompany");
        $this->addColumn("ipCurrentNo", DA_STRING, DA_ALLOW_NULL, "custitem.ipCurrentNo");
        $this->addColumn("mx", DA_STRING, DA_ALLOW_NULL, "custitem.mx");
        $this->addColumn("secureServer", DA_STRING, DA_ALLOW_NULL, "custitem.secureServer");
        $this->addColumn("vpns", DA_STRING, DA_ALLOW_NULL, "custitem.vpns");
        $this->addColumn("oma", DA_STRING, DA_ALLOW_NULL, "custitem.oma");
        $this->addColumn("owa", DA_STRING, DA_ALLOW_NULL, "custitem.owa");
        $this->addColumn("remotePortal", DA_STRING, DA_ALLOW_NULL, "custitem.remotePortal");
        $this->addColumn("smartHost", DA_STRING, DA_ALLOW_NULL, "custitem.smartHost");
        $this->addColumn("preparationRecords", DA_STRING, DA_ALLOW_NULL, "custitem.preparationRecords");
        $this->addColumn("assignedTo", DA_STRING, DA_ALLOW_NULL, "custitem.assignedTo");
        $this->addColumn("initialSpeedTest", DA_STRING, DA_ALLOW_NULL, "custitem.initialSpeedTest");
        $this->addColumn("preMigrationNotes", DA_MEMO, DA_ALLOW_NULL, "custitem.preMigrationNotes");
        $this->addColumn("postMigrationNotes", DA_MEMO, DA_ALLOW_NULL, "custitem.postMigrationNotes");
        $this->addColumn("docsUpdatedAndChecksCompleted", DA_STRING, DA_ALLOW_NULL, "custitem.docsUpdatedAndChecksCompleted");
        $this->addColumn("invoicePeriodMonths", DA_INTEGER, DA_ALLOW_NULL, "custitem.invoicePeriodMonths");
        $this->addColumn("totalInvoiceMonths", DA_INTEGER, DA_ALLOW_NULL, "custitem.totalInvoiceMonths");
        $this->addColumn("declinedFlag", DA_YN, DA_ALLOW_NULL, "custitem.declinedFlag");
        $this->addColumn("bandwidthAllowance", DA_STRING, DA_ALLOW_NULL, "custitem.bandwidthAllowance");

        /* contract */
        $this->addColumn("notes", DA_MEMO, DA_ALLOW_NULL, 'custitem.notes');
        $this->addColumn("hostingCompany", DA_STRING, DA_ALLOW_NULL, "custitem.hostingCompany");
        $this->addColumn("osPlatform", DA_STRING, DA_ALLOW_NULL, "custitem.osPlatform");
        $this->addColumn("domainNames", DA_STRING, DA_ALLOW_NULL, "custitem.domainNames");
        $this->addColumn("controlPanelUrl", DA_STRING, DA_ALLOW_NULL, "custitem.controlPanelUrl");
        $this->addColumn("ftpAddress", DA_STRING, DA_ALLOW_NULL, "custitem.ftpAddress");
        $this->addColumn("ftpUsername", DA_STRING, DA_ALLOW_NULL, "custitem.ftpUsername");
        $this->addColumn("wwwAddress", DA_STRING, DA_ALLOW_NULL, "custitem.wwwAddress");
        $this->addColumn("websiteDeveloper", DA_STRING, DA_ALLOW_NULL, "custitem.websiteDeveloper");
        $this->addColumn("secondsiteLocationPath", DA_STRING, DA_ALLOW_NULL, "custitem.secondsiteLocationPath");
        $this->addColumn("secondsiteServerDriveLetters", DA_STRING, DA_ALLOW_NULL, "custitem.secondsiteServerDriveLetters");
        $this->addColumn("secondsiteStorageUsedGb", DA_STRING, DA_ALLOW_NULL, "custitem.secondsiteStorageUsedGb");
        $this->addColumn("secondsiteValidationSuspendUntilDate", DA_DATE, DA_ALLOW_NULL, "custitem.secondsiteValidationSuspendUntilDate");

        $this->addColumn("secondsiteSuspendedDate", DA_DATE, DA_ALLOW_NULL, "custitem.secondsiteSuspendedDate");

        $this->addColumn("secondsiteSuspendedByUserID", DA_ID, DA_ALLOW_NULL, "custitem.secondsiteSuspendedByUserID");

        $this->addColumn("secondsiteImageDelayDays", DA_INTEGER, DA_ALLOW_NULL, "custitem.secondsiteImageDelayDays");

        $this->addColumn("secondsiteImageDelayDate", DA_DATE, DA_ALLOW_NULL, "custitem.secondsiteImageDelayDate");

        $this->addColumn("secondsiteImageDelayUserID", DA_ID, DA_ALLOW_NULL, "custitem.secondsiteImageDelayUserID");

        $this->addColumn("secondsiteLocalExcludeFlag", DA_YN, DA_ALLOW_NULL, "custitem.secondsiteLocalExcludeFlag");
        /* domain */
        $this->addColumn("dateGenerated", DA_STRING, DA_ALLOW_NULL, "custitem.dateGenerated");
        /* quotation */
        $this->addColumn("startDate", DA_DATE, DA_ALLOW_NULL, "custitem.startDate");
        $this->addColumn("qty", DA_INTEGER, DA_ALLOW_NULL, "custitem.qty");
        $this->addColumn("salePrice", DA_FLOAT, DA_ALLOW_NULL, "custitem.salePrice");
        $this->addColumn("costPrice", DA_FLOAT, DA_ALLOW_NULL, "custitem.costPrice");
        $this->addColumn("comment", DA_STRING, DA_ALLOW_NULL, "custitem.comment");
        $this->addColumn("grantNumber", DA_STRING, DA_ALLOW_NULL, "custitem.grantNumber");
        $this->addColumn("renQuotationTypeID", DA_ID, DA_NOT_NULL, 'custitem.renQuotationTypeID');

        $this->addColumn("internalNotes", DA_MEMO, DA_ALLOW_NULL, 'custitem.cui_internal_notes');

        $this->addColumn("autoGenerateContractInvoice", DA_YN, DA_ALLOW_NULL, "custitem.autoGenerateContractInvoice");

        $this->setPK(0);
        $this->setAddColumnsOff();
    }

    function getRowsByCustomerAndItemID($customerID, $itemID)
    {
        $this->setMethodName('getRowsByCustomerAndItemID');
        if ($customerID == '') {
            $this->raiseError('customerID not set');
        }
        if ($itemID == '') {
            $this->raiseError('itemID not set');
        }
        $this->setQueryString(
            "SELECT " . $this->getDBColumnNamesAsString() .
            " FROM " . $this->getTableName() .
            " WHERE " . $this->getDBColumnName('customerID') . "=" . $customerID .
            " AND " . $this->getDBColumnName('itemID') . "=" . $itemID
        );
        return (parent::getRows());
    }

    function search($customerID, $itemID)
    {
        $this->setMethodName('getRowsByCustomerAndItemID');
        if ($customerID == '') {
            $this->raiseError('customerID not set');
        }
        if ($itemID == '') {
            $this->raiseError('itemID not set');
        }
        $this->setQueryString(
            "SELECT " . $this->getDBColumnNamesAsString() .
            " FROM " . $this->getTableName() .
            " WHERE " . $this->getDBColumnName('customerID') . "=" . $customerID .
            " AND " . $this->getDBColumnName('itemID') . "=" . $itemID
        );
        return (parent::getRows());
    }

    function getGSCRow($customerID)
    {
        $this->setMethodName('getGSCRow');
        if ($customerID == '') {
            $this->raiseError('customerID not set');
        }
        $this->setQueryString(
            "SELECT " . $this->getDBColumnNamesAsString() .
            " FROM " . $this->getTableName() .
            " WHERE " . $this->getDBColumnName('customerID') . "=" . $customerID .
            " AND " . $this->getDBColumnName('itemID') . "=" . CONFIG_DEF_PREPAY_ITEMID .
            " AND " . $this->getDBColumnName('renewalStatus') . "<> 'D'"
        );
        return (parent::getRow());
    }

    /**
     * Get expiry rows by days
     *
     * @param unknown_type $days
     * @return unknown
     */
    function getExpiryRowsByDays($days)
    {
        $this->setMethodName('getExpiryRowsByDays');
        if ($days == '') {
            $this->raiseError('days not set');
        }
        $this->setQueryString(
            "SELECT " . $this->getDBColumnNamesAsString() .
            " FROM " . $this->getTableName() .
            " WHERE " . $this->getDBColumnName('expiryDate') . "<= DATE_ADD(NOW(), INTERVAL " . $days . " DAY)" .
            " AND " . $this->getDBColumnName('expiryDate') . ">= NOW()" .
            " AND " . $this->getDBColumnName('renewalStatus') . "= ''"
        );
        return (parent::getRows());
    }

    /* Update for new many-to-many
    function setRowsToContractID( $contractID, $customerItemIDArray )
    {

       $this->setMethodName('setRowsToContractID');

       if ( !$customerItemIDArray ){
         $this->raiseError('$customerItemIDArray  not set');
       }

       $this->setQueryString(
         "UPDATE " . $this->getTableName().
         " SET ". $this->getDBColumnName('contractID') . " = " . $contractID .
         " WHERE ". $this->getDBColumnName('customerItemID') . " IN ( ". implode( ',', $customerItemIDArray ) . ")"
       );

      return $this->runQuery();
    }
    */
    function addYearToStartDate($customerItemID)
    {
        $statement =
            "
      UPDATE " . $this->getTableName() .
            " SET startDate = DATE_ADD( `startDate`, INTERVAL 1 YEAR ),
      dateGenerated = '0000-00-00'
        WHERE cui_cuino = $customerItemID;";

        $this->setQueryString($statement);
        return $this->runQuery();

    }

    function removeContractFromCustomerItems($contractID, $customerItemIDs)
    {
        $statement =
            "DELETE FROM
        custitem_contract
      WHERE
        cic_cuino IN ( " . implode(',', $customerItemIDs) . ")
        AND cic_contractcuino = $contractID";

        return $this->db->query($statement);

    }

    function addContractToCustomerItems($contractID, $customerItemIDs)
    {

        foreach ($customerItemIDs as $customerItemID) {

            $this->addContract($customerItemID, $contractID);

        }

    }

    function updateContract($customerItemID, $contractIDs = false)
    {

        $existingContractIDs = $this->getExistingContractIDs($customerItemID);
        /*
        Remove any contracts that no longer exist in list
        */
        foreach ($existingContractIDs as $existingContractID) {

            if (!in_array($existingContractID, $contractIDs)) {
                $this->deleteContract($customerItemID, $existingContractID);
            }
        }
        /*
        Add new contracts
        */
        foreach ($contractIDs as $contractID) {
            if (!in_array($contractID, $existingContractIDs)) {
                $this->addContract($customerItemID, $contractID);
            }
        }
    }

    function getExistingContractIDs($customerItemID)
    {
        $statement =
            "SELECT
        cic_contractcuino
      FROM
        custitem_contract
      WHERE
        cic_cuino = $customerItemID";

        $result = $this->db->query($statement);

        $existingContractIDs = array();

        while ($row = $this->db->next_record()) {
            $existingContractIDs[] = $this->db->Record['cic_contractcuino'];
        }
        return $existingContractIDs;
    }

    function deleteContract($customerItemID, $contractID)
    {
        $statement =
            "DELETE FROM
        custitem_contract
      WHERE
        cic_cuino = $customerItemID
        AND cic_contractcuino = $contractID";

        return $this->db->query($statement);

    }

    function addContract($customerItemID, $contractID)
    {
        $statement =
            "SELECT
        cic_contractcuino
      FROM
        custitem_contract
      WHERE
        cic_cuino = $customerItemID
        AND cic_contractcuino = $contractID";

        $this->db->query($statement);
        if (!$this->db->next_record()) {

            $statement =
                "INSERT INTO
          custitem_contract
        SET
          cic_cuino = $customerItemID,
          cic_contractcuino = $contractID";

            $this->db->query($statement);
        }

    }

}

?>
