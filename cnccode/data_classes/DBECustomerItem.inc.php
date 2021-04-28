<?php /*
* Item table
* @authors Karim Ahmed
* @access public
*/
global $cfg;
require_once($cfg["path_dbe"] . "/DBCNCEntity.inc.php");

class DBECustomerItem extends DBCNCEntity
{
    const customerItemID                                 = "customerItemID";
    const customerID                                     = "customerID";
    const siteNo                                         = "siteNo";
    const itemID                                         = "itemID";
    const warrantyID                                     = "warrantyID";
    const userID                                         = "userID";
    const serialNo                                       = "serialNo";
    const serverName                                     = "serverName";
    const despatchDate                                   = "despatchDate";
    const ordheadID                                      = "ordheadID";
    const porheadID                                      = "porheadID";
    const curUnitSale                                    = "curUnitSale";
    const curUnitCost                                    = "curUnitCost";
    const sOrderDate                                     = "sOrderDate";
    const users                                          = "users";
    const expiryDate                                     = "expiryDate";
    const curGSCBalance                                  = "curGSCBalance";
    const renewalStatus                                  = "renewalStatus";
    const customerItemNotes                              = "customerItemNotes";
    const slaResponseHours                               = "slaResponseHours";
    const months                                         = "months";
    const salePricePerMonth                              = "salePricePerMonth";
    const adslPhone                                      = "adslPhone";
    const macCode                                        = "macCode";
    const reference                                      = "reference";
    const defaultGateway                                 = "defaultGateway";
    const networkAddress                                 = "networkAddress";
    const subnetMask                                     = "subnetMask";
    const routerIPAddress                                = "routerIPAddress";
    const hostingUserName                                = "hostingUserName";
    const userName                                       = "userName";
    const password                                       = "password";
    const etaDate                                        = "etaDate";
    const installationDate                               = "installationDate";
    const initialContractLength                          = 'initialContractLength';
    const costPricePerMonth                              = "costPricePerMonth";
    const ispID                                          = "ispID";
    const dualBroadbandFlag                              = "dualBroadbandFlag";
    const dnsCompany                                     = "dnsCompany";
    const ipCurrentNo                                    = "ipCurrentNo";
    const mx                                             = "mx";
    const secureServer                                   = "secureServer";
    const vpns                                           = "vpns";
    const oma                                            = "oma";
    const owa                                            = "owa";
    const remotePortal                                   = "remotePortal";
    const smartHost                                      = "smartHost";
    const preparationRecords                             = "preparationRecords";
    const assignedTo                                     = "assignedTo";
    const initialSpeedTest                               = "initialSpeedTest";
    const preMigrationNotes                              = "preMigrationNotes";
    const postMigrationNotes                             = "postMigrationNotes";
    const docsUpdatedAndChecksCompleted                  = "docsUpdatedAndChecksCompleted";
    const invoicePeriodMonths                            = "invoicePeriodMonths";
    const totalInvoiceMonths                             = "totalInvoiceMonths";
    const declinedFlag                                   = "declinedFlag";
    const bandwidthAllowance                             = "bandwidthAllowance";
    const notes                                          = "notes";
    const hostingCompany                                 = "hostingCompany";
    const osPlatform                                     = "osPlatform";
    const domainNames                                    = "domainNames";
    const controlPanelUrl                                = "controlPanelUrl";
    const ftpAddress                                     = "ftpAddress";
    const ftpUsername                                    = "ftpUsername";
    const wwwAddress                                     = "wwwAddress";
    const websiteDeveloper                               = "websiteDeveloper";
    const secondsiteLocationPath                         = "secondsiteLocationPath";
    const secondsiteServerDriveLetters                   = "secondsiteServerDriveLetters";
    const secondsiteStorageUsedGb                        = "secondsiteStorageUsedGb";
    const secondsiteValidationSuspendUntilDate           = "secondsiteValidationSuspendUntilDate";
    const secondsiteSuspendedDate                        = "secondsiteSuspendedDate";
    const secondsiteSuspendedByUserID                    = "secondsiteSuspendedByUserID";
    const offsiteReplicationValidationSuspendedUntilDate = "offsiteReplicationValidationSuspendedUntilDate";
    const offsiteReplicationSuspendedByUserID            = "offsiteReplicationSuspendedByUserID";
    const offsiteReplicationSuspendedDate                = "offsiteReplicationSuspendedDate";
    const secondsiteImageDelayDays                       = "secondsiteImageDelayDays";
    const secondsiteImageDelayDate                       = "secondsiteImageDelayDate";
    const secondsiteImageDelayUserID                     = "secondsiteImageDelayUserID";
    const secondsiteLocalExcludeFlag                     = "secondsiteLocalExcludeFlag";
    const dateGenerated                                  = "dateGenerated";
    const startDate                                      = "startDate";
    const qty                                            = "qty";
    const salePrice                                      = "salePrice";
    const costPrice                                      = "costPrice";
    const comment                                        = "comment";
    const grantNumber                                    = "grantNumber";
    const renQuotationTypeID                             = "renQuotationTypeID";
    const internalNotes                                  = "internalNotes";
    const autoGenerateContractInvoice                    = "autoGenerateContractInvoice";
    const secondSiteReplicationPath                      = "secondSiteReplicationPath";
    const secondSiteReplicationExcludeFlag               = "secondSiteReplicationExcludeFlag";
    const officialOrderNumber                            = "officialOrderNumber";
    const directDebitFlag                                = "directDebitFlag";
    const transactionType                                = "transactionType";
    const bypassCWAAgentCheck                            = "bypassCWAAgentCheck";
    const PATCH_MANAGEMENT_ITEM_ID                       = 17124;


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
        $this->setTableName("custitem");
        $this->addColumn(
            self::customerItemID,
            DA_ID,
            DA_NOT_NULL,
            "custitem.cui_cuino"
        );
        $this->addColumn(
            self::customerID,
            DA_INTEGER,
            DA_NOT_NULL,
            "custitem.cui_custno"
        );
        $this->addColumn(
            self::siteNo,
            DA_INTEGER,
            DA_NOT_NULL,
            "custitem.cui_siteno"
        );
        $this->addColumn(
            self::itemID,
            DA_INTEGER,
            DA_NOT_NULL,
            "custitem.cui_itemno"
        );
        $this->addColumn(
            self::warrantyID,
            DA_INTEGER,
            DA_NOT_NULL,
            "custitem.cui_man_contno"
        );
        $this->addColumn(
            self::userID,
            DA_INTEGER,
            DA_ALLOW_NULL,
            "custitem.cui_consno"
        );
        $this->addColumn(
            self::serialNo,
            DA_STRING,
            DA_ALLOW_NULL,
            "custitem.cui_serial"
        );
        $this->addColumn(
            self::serverName,
            DA_STRING,
            DA_ALLOW_NULL,
            "custitem.cui_cust_ref"
        );
        $this->addColumn(
            self::despatchDate,
            DA_DATE,
            DA_ALLOW_NULL,
            "custitem.cui_desp_date"
        );
        $this->addColumn(
            self::ordheadID,
            DA_INTEGER,
            DA_ALLOW_NULL,
            "custitem.cui_ordno"
        );
        $this->addColumn(
            self::porheadID,
            DA_INTEGER,
            DA_ALLOW_NULL,
            "custitem.cui_porno"
        );
        $this->addColumn(
            self::curUnitSale,
            DA_FLOAT,
            DA_ALLOW_NULL,
            "custitem.cui_sale_price"
        );
        $this->addColumn(
            self::curUnitCost,
            DA_FLOAT,
            DA_ALLOW_NULL,
            "custitem.cui_cost_price"
        );
        $this->addColumn(
            self::sOrderDate,
            DA_DATE,
            DA_ALLOW_NULL,
            "custitem.cui_ord_date"
        );
        $this->addColumn(
            self::users,
            DA_INTEGER,
            DA_ALLOW_NULL,
            "custitem.cui_users"
        );
        $this->addColumn(
            self::expiryDate,
            DA_DATE,
            DA_ALLOW_NULL,
            "custitem.cui_expiry_date"
        ); // only has a value if this is a contract customer item
        $this->addColumn(
            self::curGSCBalance,
            DA_FLOAT,
            DA_ALLOW_NULL,
            "custitem.curGSCBalance"
        );        // only has a value if this is a contract customer item
        $this->addColumn(
            self::renewalStatus,
            DA_STRING,
            DA_ALLOW_NULL,
            "custitem.renewalStatus"
        );      // R=Renewed, D=Renewal Declined
        $this->addColumn(
            self::customerItemNotes,
            DA_MEMO,
            DA_ALLOW_NULL,
            "custitem.itemNotes"
        );
        $this->addColumn(
            self::slaResponseHours,
            DA_INTEGER,
            DA_ALLOW_NULL,
            "custitem.cui_sla_response_hours"
        );
        $this->addColumn(
            self::months,
            DA_INTEGER,
            DA_ALLOW_NULL,
            'custitem.months'
        );
        $this->addColumn(
            self::salePricePerMonth,
            DA_FLOAT,
            DA_ALLOW_NULL,
            "custitem.salePricePerMonth"
        );
        $this->addColumn(
            self::adslPhone,
            DA_STRING,
            DA_ALLOW_NULL,
            "custitem.adslPhone"
        );
        $this->addColumn(
            self::macCode,
            DA_STRING,
            DA_ALLOW_NULL,
            "custitem.macCode"
        );
        $this->addColumn(
            self::reference,
            DA_STRING,
            DA_ALLOW_NULL,
            "custitem.reference"
        );
        $this->addColumn(
            self::defaultGateway,
            DA_STRING,
            DA_ALLOW_NULL,
            "custitem.defaultGateway"
        );
        $this->addColumn(
            self::networkAddress,
            DA_STRING,
            DA_ALLOW_NULL,
            "custitem.networkAddress"
        );
        $this->addColumn(
            self::subnetMask,
            DA_STRING,
            DA_ALLOW_NULL,
            "custitem.subnetMask"
        );
        $this->addColumn(
            self::routerIPAddress,
            DA_STRING,
            DA_ALLOW_NULL,
            "custitem.routerIPAddress"
        );
        $this->addColumn(
            self::hostingUserName,
            DA_STRING,
            DA_ALLOW_NULL,
            "custitem.hostingUserName"
        );
        $this->addColumn(
            self::userName,
            DA_STRING,
            DA_ALLOW_NULL,
            "custitem.userName"
        );
        $this->addColumn(
            self::password,
            DA_STRING,
            DA_ALLOW_NULL,
            "custitem.password"
        );
        $this->addColumn(
            self::etaDate,
            DA_DATE,
            DA_ALLOW_NULL,
            "custitem.etaDate"
        );
        $this->addColumn(
            self::installationDate,
            DA_DATE,
            DA_ALLOW_NULL,
            "custitem.installationDate"
        );
        $this->addColumn(
            self::costPricePerMonth,
            DA_FLOAT,
            DA_ALLOW_NULL,
            "custitem.costPricePerMonth"
        );
        $this->addColumn(
            self::ispID,
            DA_STRING,
            DA_ALLOW_NULL,
            "custitem.ispID"
        );
        $this->addColumn(
            self::dualBroadbandFlag,
            DA_YN,
            DA_ALLOW_NULL,
            "custitem.dualBroadbandFlag"
        );
        $this->addColumn(
            self::dnsCompany,
            DA_STRING,
            DA_ALLOW_NULL,
            "custitem.dnsCompany"
        );
        $this->addColumn(
            self::ipCurrentNo,
            DA_STRING,
            DA_ALLOW_NULL,
            "custitem.ipCurrentNo"
        );
        $this->addColumn(
            self::mx,
            DA_STRING,
            DA_ALLOW_NULL,
            "custitem.mx"
        );
        $this->addColumn(
            self::secureServer,
            DA_STRING,
            DA_ALLOW_NULL,
            "custitem.secureServer"
        );
        $this->addColumn(
            self::vpns,
            DA_STRING,
            DA_ALLOW_NULL,
            "custitem.vpns"
        );
        $this->addColumn(
            self::oma,
            DA_STRING,
            DA_ALLOW_NULL,
            "custitem.oma"
        );
        $this->addColumn(
            self::owa,
            DA_STRING,
            DA_ALLOW_NULL,
            "custitem.owa"
        );
        $this->addColumn(
            self::remotePortal,
            DA_STRING,
            DA_ALLOW_NULL,
            "custitem.remotePortal"
        );
        $this->addColumn(
            self::smartHost,
            DA_STRING,
            DA_ALLOW_NULL,
            "custitem.smartHost"
        );
        $this->addColumn(
            self::preparationRecords,
            DA_STRING,
            DA_ALLOW_NULL,
            "custitem.preparationRecords"
        );
        $this->addColumn(
            self::assignedTo,
            DA_STRING,
            DA_ALLOW_NULL,
            "custitem.assignedTo"
        );
        $this->addColumn(
            self::initialSpeedTest,
            DA_STRING,
            DA_ALLOW_NULL,
            "custitem.initialSpeedTest"
        );
        $this->addColumn(
            self::preMigrationNotes,
            DA_MEMO,
            DA_ALLOW_NULL,
            "custitem.preMigrationNotes"
        );
        $this->addColumn(
            self::postMigrationNotes,
            DA_MEMO,
            DA_ALLOW_NULL,
            "custitem.postMigrationNotes"
        );
        $this->addColumn(
            self::docsUpdatedAndChecksCompleted,
            DA_STRING,
            DA_ALLOW_NULL,
            "custitem.docsUpdatedAndChecksCompleted"
        );
        $this->addColumn(
            self::invoicePeriodMonths,
            DA_INTEGER,
            DA_ALLOW_NULL,
            "custitem.invoicePeriodMonths"
        );
        $this->addColumn(
            self::totalInvoiceMonths,
            DA_INTEGER,
            DA_ALLOW_NULL,
            "custitem.totalInvoiceMonths"
        );
        $this->addColumn(
            self::declinedFlag,
            DA_YN,
            DA_ALLOW_NULL,
            "custitem.declinedFlag"
        );
        $this->addColumn(
            self::bandwidthAllowance,
            DA_STRING,
            DA_ALLOW_NULL,
            "custitem.bandwidthAllowance"
        );
        $this->addColumn(
            self::notes,
            DA_MEMO,
            DA_ALLOW_NULL,
            'custitem.notes'
        );
        $this->addColumn(
            self::hostingCompany,
            DA_STRING,
            DA_ALLOW_NULL,
            "custitem.hostingCompany"
        );
        $this->addColumn(
            self::osPlatform,
            DA_STRING,
            DA_ALLOW_NULL,
            "custitem.osPlatform"
        );
        $this->addColumn(
            self::domainNames,
            DA_STRING,
            DA_ALLOW_NULL,
            "custitem.domainNames"
        );
        $this->addColumn(
            self::controlPanelUrl,
            DA_STRING,
            DA_ALLOW_NULL,
            "custitem.controlPanelUrl"
        );
        $this->addColumn(
            self::ftpAddress,
            DA_STRING,
            DA_ALLOW_NULL,
            "custitem.ftpAddress"
        );
        $this->addColumn(
            self::ftpUsername,
            DA_STRING,
            DA_ALLOW_NULL,
            "custitem.ftpUsername"
        );
        $this->addColumn(
            self::wwwAddress,
            DA_STRING,
            DA_ALLOW_NULL,
            "custitem.wwwAddress"
        );
        $this->addColumn(
            self::websiteDeveloper,
            DA_STRING,
            DA_ALLOW_NULL,
            "custitem.websiteDeveloper"
        );
        $this->addColumn(
            self::secondsiteLocationPath,
            DA_STRING,
            DA_ALLOW_NULL,
            "custitem.secondsiteLocationPath"
        );
        $this->addColumn(
            self::secondSiteReplicationPath,
            DA_STRING,
            DA_ALLOW_NULL,
            "custitem.secondSiteReplicationPath"
        );
        $this->addColumn(
            self::secondsiteServerDriveLetters,
            DA_STRING,
            DA_ALLOW_NULL,
            "custitem.secondsiteServerDriveLetters"
        );
        $this->addColumn(
            self::secondsiteStorageUsedGb,
            DA_STRING,
            DA_ALLOW_NULL,
            "custitem.secondsiteStorageUsedGb"
        );
        $this->addColumn(
            self::secondsiteValidationSuspendUntilDate,
            DA_DATE,
            DA_ALLOW_NULL,
            "custitem.secondsiteValidationSuspendUntilDate"
        );
        $this->addColumn(
            self::offsiteReplicationValidationSuspendedUntilDate,
            DA_DATE,
            DA_ALLOW_NULL,
            "custitem.offsiteReplicationValidationSuspendedUntilDate"
        );
        $this->addColumn(
            self::secondsiteSuspendedDate,
            DA_DATE,
            DA_ALLOW_NULL,
            "custitem.secondsiteSuspendedDate"
        );
        $this->addColumn(
            self::offsiteReplicationSuspendedDate,
            DA_DATE,
            DA_ALLOW_NULL,
            "custitem.offsiteReplicationSuspendedDate"
        );
        $this->addColumn(
            self::secondsiteSuspendedByUserID,
            DA_ID,
            DA_ALLOW_NULL,
            "custitem.secondsiteSuspendedByUserID"
        );
        $this->addColumn(
            self::offsiteReplicationSuspendedByUserID,
            DA_ID,
            DA_ALLOW_NULL,
            "custitem.offsiteReplicationSuspendedByUserID"
        );
        $this->addColumn(
            self::secondsiteImageDelayDays,
            DA_INTEGER,
            DA_ALLOW_NULL,
            "custitem.secondsiteImageDelayDays"
        );
        $this->addColumn(
            self::secondsiteImageDelayDate,
            DA_DATE,
            DA_ALLOW_NULL,
            "custitem.secondsiteImageDelayDate"
        );
        $this->addColumn(
            self::secondsiteImageDelayUserID,
            DA_ID,
            DA_ALLOW_NULL,
            "custitem.secondsiteImageDelayUserID"
        );
        $this->addColumn(
            self::secondsiteLocalExcludeFlag,
            DA_YN,
            DA_ALLOW_NULL,
            "custitem.secondsiteLocalExcludeFlag"
        );
        $this->addColumn(
            self::dateGenerated,
            DA_DATE,
            DA_ALLOW_NULL,
            "custitem.dateGenerated"
        );
        $this->addColumn(
            self::startDate,
            DA_DATE,
            DA_ALLOW_NULL,
            "custitem.startDate"
        );
        $this->addColumn(
            self::qty,
            DA_INTEGER,
            DA_ALLOW_NULL,
            "custitem.qty"
        );
        $this->addColumn(
            self::salePrice,
            DA_FLOAT,
            DA_ALLOW_NULL,
            "custitem.salePrice"
        );
        $this->addColumn(
            self::costPrice,
            DA_FLOAT,
            DA_ALLOW_NULL,
            "custitem.costPrice"
        );
        $this->addColumn(
            self::comment,
            DA_STRING,
            DA_ALLOW_NULL,
            "custitem.comment"
        );
        $this->addColumn(
            self::grantNumber,
            DA_STRING,
            DA_ALLOW_NULL,
            "custitem.grantNumber"
        );
        $this->addColumn(
            self::renQuotationTypeID,
            DA_ID,
            DA_NOT_NULL,
            'custitem.renQuotationTypeID'
        );
        $this->addColumn(
            self::internalNotes,
            DA_MEMO,
            DA_ALLOW_NULL,
            'custitem.cui_internal_notes'
        );
        $this->addColumn(
            self::autoGenerateContractInvoice,
            DA_YN,
            DA_ALLOW_NULL,
            "custitem.autoGenerateContractInvoice"
        );
        $this->addColumn(
            self::secondSiteReplicationExcludeFlag,
            DA_YN,
            DA_NOT_NULL,
            'custitem.secondSiteReplicationExcludeFlag'
        );
        $this->addColumn(
            self::initialContractLength,
            DA_INTEGER,
            DA_NOT_NULL,
            'custitem.initialContractLength'
        );
        $this->addColumn(
            self::officialOrderNumber,
            DA_TEXT,
            DA_ALLOW_NULL,
            'custitem.officialOrderNumber'
        );
        $this->addColumn(
            self::directDebitFlag,
            DA_YN,
            DA_ALLOW_NULL,
            "custitem.directDebitFlag"
        );
        $this->addColumn(
            self::transactionType,
            DA_STRING,
            DA_NOT_NULL,
            'custitem.transactionType'
        );
        $this->addColumn(
            self::bypassCWAAgentCheck,
            DA_BOOLEAN,
            DA_NOT_NULL,
            'custitem.bypassCWAAgentCheck',
            0
        );
        $this->setPK(0);
        $this->setAddColumnsOff();
    }

    function getRowsByCustomerAndItemID($customerID,
                                        $itemID,
                                        bool $ignoreDeclined = false
    )
    {
        $this->setMethodName('getRowsByCustomerAndItemID');
        if ($customerID == '') {
            $this->raiseError('customerID not set');
        }
        if ($itemID == '') {
            $this->raiseError('itemID not set');
        }
        $queryString = "SELECT {$this->getDBColumnNamesAsString()} 
            FROM {$this->getTableName()} 
            WHERE {$this->getDBColumnName(self::customerID)}={$customerID} 
              AND {$this->getDBColumnName(self::itemID)}={$itemID}";
        if ($ignoreDeclined) {
            $queryString .= " and {$this->getDBColumnName(self::declinedFlag)} <> 'Y' and {$this->getDBColumnName(self::renewalStatus)} = 'R'";
        }
        $this->setQueryString($queryString);
        return (parent::getRows());
    }

    function getCountByCustomerAndItemID($customerID,
                                         $itemID,
                                         $onlyActive = true
    )
    {
        $this->setMethodName('getRowsByCustomerAndItemID');
        if ($customerID == '') {
            $this->raiseError('customerID not set');
        }
        if ($itemID == '') {
            $this->raiseError('itemID not set');
        }
        global $db;
        $query = "SELECT count(*) as count FROM {$this->getTableName()} WHERE
                                          {$this->getDBColumnName(self::customerID)}={$customerID} 
                                      AND {$this->getDBColumnName(self::itemID)}={$itemID} 
                                      ";
        if ($onlyActive) {
            $query .= " and {$this->getDBColumnName(self::declinedFlag)} <> 'Y' and {$this->getDBColumnName(self::renewalStatus)} = 'R'";
        }
        $db->query($query);
        if (!$db->num_rows()) {
            return 0;
        }
        $db->next_record(MYSQLI_ASSOC);
        return $db->Record['count'];
    }

    function search($customerID,
                    $itemID
    )
    {
        $this->setMethodName('getRowsByCustomerAndItemID');
        if ($customerID == '') {
            $this->raiseError('customerID not set');
        }
        if ($itemID == '') {
            $this->raiseError('itemID not set');
        }
        $this->setQueryString(
            "SELECT " . $this->getDBColumnNamesAsString() . " FROM " . $this->getTableName(
            ) . " WHERE " . $this->getDBColumnName(
                self::customerID
            ) . "=" . $customerID . " AND " . $this->getDBColumnName(self::itemID) . "=" . $itemID
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
            "SELECT " . $this->getDBColumnNamesAsString() . " FROM " . $this->getTableName(
            ) . " WHERE " . $this->getDBColumnName(
                self::customerID
            ) . "=" . $customerID . " AND " . $this->getDBColumnName(
                self::itemID
            ) . "=" . CONFIG_DEF_PREPAY_ITEMID . " AND " . $this->getDBColumnName(self::renewalStatus) . "<> 'D'"
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
            "SELECT " . $this->getDBColumnNamesAsString() . " FROM " . $this->getTableName(
            ) . " WHERE " . $this->getDBColumnName(
                self::expiryDate
            ) . "<= DATE_ADD(NOW(), INTERVAL " . $days . " DAY)" . " AND " . $this->getDBColumnName(
                self::expiryDate
            ) . ">= NOW()" . " AND " . $this->getDBColumnName(self::renewalStatus) . "= ''"
        );
        return (parent::getRows());
    }

    function addYearToStartDate($customerItemID)
    {
        $statement = "
      UPDATE " . $this->getTableName() . " SET startDate = DATE_ADD( `startDate`, INTERVAL 1 YEAR ),
      dateGenerated = null
        WHERE cui_cuino = $customerItemID;";
        $this->setQueryString($statement);
        return $this->runQuery();

    }

    function removeContractFromCustomerItems($contractID,
                                             $customerItemIDs
    )
    {
        $statement = "DELETE FROM
        custitem_contract
      WHERE
        cic_cuino IN ( " . implode(
                ',',
                $customerItemIDs
            ) . ")
        AND cic_contractcuino = $contractID";
        return $this->db->query($statement);

    }

    function addContractToCustomerItems($contractID,
                                        $customerItemIDs
    )
    {

        foreach ($customerItemIDs as $customerItemID) {

            $this->addContract(
                $customerItemID,
                $contractID
            );

        }

    }

    function addContract($customerItemID,
                         $contractID
    )
    {
        $statement = "SELECT
        cic_contractcuino
      FROM
        custitem_contract
      WHERE
        cic_cuino = $customerItemID
        AND cic_contractcuino = $contractID";
        $this->db->query($statement);
        if (!$this->db->next_record()) {

            $statement = "INSERT INTO
          custitem_contract
        SET
          cic_cuino = $customerItemID,
          cic_contractcuino = $contractID";
            $this->db->query($statement);
        }

    }

    function updateContract($customerItemID,
                            $contractIDs = []
    )
    {

        $existingContractIDs = $this->getExistingContractIDs($customerItemID);
        /*
        Remove any contracts that no longer exist in list
        */
        foreach ($existingContractIDs as $existingContractID) {

            if (!in_array(
                $existingContractID,
                $contractIDs
            )) {
                $this->deleteContract(
                    $customerItemID,
                    $existingContractID
                );
            }
        }
        /*
        Add new contracts
        */
        foreach ($contractIDs as $contractID) {
            if (!in_array(
                $contractID,
                $existingContractIDs
            )) {
                $this->addContract(
                    $customerItemID,
                    $contractID
                );
            }
        }
    }

    function getExistingContractIDs($customerItemID)
    {
        $statement = "SELECT
        cic_contractcuino
      FROM
        custitem_contract
      WHERE
        cic_cuino = $customerItemID";
        $this->db->query($statement);
        $existingContractIDs = array();
        while ($row = $this->db->next_record()) {
            $existingContractIDs[] = $this->db->Record['cic_contractcuino'];
        }
        return $existingContractIDs;
    }

    function deleteContract($customerItemID,
                            $contractID
    )
    {
        $statement = "DELETE FROM
        custitem_contract
      WHERE
        cic_cuino = $customerItemID
        AND cic_contractcuino = $contractID";
        return $this->db->query($statement);

    }

    public function getPatchManagementContractForCustomer($customerId)
    {
        $this->getRowsByCustomerAndItemID($customerId, self::PATCH_MANAGEMENT_ITEM_ID, true);
    }

}
