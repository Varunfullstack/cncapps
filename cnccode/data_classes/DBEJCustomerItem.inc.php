<?php /*
* Customer Item join
* @authors Karim Ahmed
* @access public
*/
global $cfg;

use CNCLTD\Exceptions\ColumnOutOfRangeException;

require_once($cfg["path_dbe"] . "/DBECustomerItem.inc.php");

class DBEJCustomerItem extends DBECustomerItem
{

    const customerName       = "customerName";
    const siteDescription    = "siteDescription";
    const contractItemTypeID = "contractItemTypeID";
    const itemDescription    = "itemDescription";
    const itemNotes          = "itemNotes";
    const renewalTypeID      = "renewalTypeID";
    const partNo             = "partNo";
    const servercareFlag     = "servercareFlag";
    const invoiceFromDate    = "invoiceFromDate";
    const invoiceToDate      = "invoiceToDate";
    const invoiceFromDateYMD = "invoiceFromDateYMD";
    const invoiceToDateYMD   = "invoiceToDateYMD";
    const reoccurring        = "reocurring";

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
        $this->setAddColumnsOn();
        $this->addColumn(
            self::customerName,
            DA_STRING,
            DA_ALLOW_NULL,
            "cus_name"
        );
        $this->addColumn(
            self::siteDescription,
            DA_STRING,
            DA_ALLOW_NULL,
            "CONCAT_WS(', ', add_add1, add_town, add_postcode)"
        );
        $this->addColumn(
            self::contractItemTypeID,
            DA_ID,
            DA_ALLOW_NULL,
            "citem.itm_itemtypeno"
        );
        $this->addColumn(
            self::itemDescription,
            DA_STRING,
            DA_ALLOW_NULL,
            "citem.itm_desc"
        );
        $this->addColumn(
            self::itemNotes,
            DA_STRING,
            DA_ALLOW_NULL,
            "citem.notes"
        );
        $this->addColumn(
            self::renewalTypeID,
            DA_ID,
            DA_ALLOW_NULL,
            "citem.renewalTypeID"
        );
        $this->addColumn(
            self::partNo,
            DA_STRING,
            DA_ALLOW_NULL,
            "citem.itm_unit_of_sale"
        );
        $this->addColumn(
            self::servercareFlag,
            DA_INTEGER,
            DA_ALLOW_NULL,
            "citem.itm_servercare_flag"
        );
        $this->addColumn(
            self::invoiceFromDate,
            DA_DATE,
            DA_NOT_NULL,
            "DATE_FORMAT( DATE_ADD(custitem.installationDate, INTERVAL custitem.totalInvoiceMonths MONTH ), '%d/%m/%Y')"
        );
        $this->addColumn(
            self::invoiceToDate,
            DA_DATE,
            DA_NOT_NULL,
            "DATE_FORMAT( DATE_ADD(custitem.installationDate, INTERVAL custitem.totalInvoiceMonths + custitem.invoicePeriodMonths MONTH ), '%d/%m/%Y')"
        );
        $this->addColumn(
            self::invoiceFromDateYMD,
            DA_DATE,
            DA_NOT_NULL,
            "DATE_FORMAT( DATE_ADD(custitem.installationDate, INTERVAL custitem.totalInvoiceMonths MONTH ), '%Y-%m-%d') as invoiceFromDateYMD"
        );
        $this->addColumn(
            self::invoiceToDateYMD,
            DA_DATE,
            DA_NOT_NULL,
            "DATE_FORMAT( DATE_ADD(custitem.installationDate, INTERVAL custitem.totalInvoiceMonths + custitem.invoicePeriodMonths MONTH ), '%Y-%m-%d') as invoiceToDateYMD"
        );
        $this->addColumn(
            self::reoccurring,
            DA_BOOLEAN,
            DA_NOT_NULL,
            "itemtype.reoccurring"
        );
        $this->setAddColumnsOff();
    }

    function getRowsBySearchCriteria($customerID,
                                     $ordheadID,
                                     $startDate,
                                     $endDate,
                                     $itemText,
                                     $contractText,
                                     $serialNo,
                                     $renewalStatus,
                                     $row_limit = 1000
    )
    {
        $this->setMethodName('getRowsBySearchCriteria');
        $baseQuery = "SELECT {$this->getDBColumnNamesAsString()} FROM {$this->getTableName()} 
                        JOIN item AS citem ON cui_itemno = itm_itemno 
                        JOIN customer ON cui_custno = cus_custno 
                        JOIN address ON add_siteno = cui_siteno AND add_custno = cui_custno
                        left join itemtype on ity_itemtypeno = citem.itm_itemtypeno
                        ";
        $filters = [];
        if ($customerID != '') {
            $filters[] = $this->getDBColumnName(self::customerID) . "=" . $customerID;
        }
        if ($ordheadID != '') {
            $filters[] = $this->getDBColumnName(self::ordheadID) . "=" . $ordheadID;
        }
        if ($startDate != '') {
            $filters[] = $this->getDBColumnName(self::expiryDate) . ">= '" . mysqli_real_escape_string(
                    $this->db->link_id(),
                    $startDate
                ) . "'";
        }
        if ($endDate != '') {
            $filters[] = $this->getDBColumnName(self::expiryDate) . "<= '" . mysqli_real_escape_string(
                    $this->db->link_id(),
                    $endDate
                ) . "'";
        }
        if ($serialNo != '') {
            $filters[] = $this->getDBColumnName(self::serialNo) . " LIKE '%" . mysqli_real_escape_string(
                    $this->db->link_id(),
                    $serialNo
                ) . "%'";
        }
        if ($itemText != '') {
            $filters[] = " citem.itm_desc LIKE '%" . mysqli_real_escape_string(
                    $this->db->link_id(),
                    $itemText
                ) . "%'";
        }
        /*
        If searching on contract text, need to sub-query to match item descriptions
        on custitem_contract
        */
        if ($contractText != '' || $renewalStatus != '') {

            $baseQuery .= " LEFT JOIN custitem_contract
    ON cic_cuino = custitem.`cui_cuino`
  LEFT JOIN custitem AS contractCustomerItem
    ON cic_contractcuino = contractCustomerItem.`cui_cuino`
  LEFT JOIN item AS contractItem ON contractCustomerItem.cui_itemno = contractItem.`itm_itemno`
  ";
            if ($renewalStatus) {

                $filters[] = " contractCustomerItem.renewalStatus ='" . mysqli_real_escape_string(
                        $this->db->link_id(),
                        $renewalStatus
                    ) . "'";
            }
            if ($contractText) {
                $filters[] = " contractItem.itm_desc like '%" . mysqli_real_escape_string(
                        $this->db->link_id(),
                        $contractText
                    ) . "%'";
            }
        }
        if (count($filters)) {
            $baseQuery .= " where " . implode(" and ", $filters);
        }
        if ($row_limit) {
            $baseQuery .= " LIMIT 0," . $row_limit;
        }
        $this->setQueryString($baseQuery);
        return (parent::getRows());
    }

    function getRow($ID = null)
    {
        $this->setMethodName('getRow');
        $queryString = "SELECT {$this->getDBColumnNamesAsString()} FROM {$this->getTableName()} 
                JOIN item AS citem ON cui_itemno = itm_itemno 
                JOIN customer ON cui_custno = cus_custno 
                JOIN address ON add_siteno = cui_siteno AND add_custno = cui_custno
                join itemtype on ity_itemtypeno = citem.itm_itemtypeno
            WHERE {$this->getDBColumnName(self::customerItemID)}={$ID}";
        $this->setQueryString($queryString);
        return (parent::getRow());
    }

    function getRowsByColumn($column,
                             $sortColumn = ''
    )
    {
        $this->setMethodName("getRowsByColumn");
        if ($column == '') {
            $this->raiseError('Column not passed');
            return FALSE;
        }
        $ixColumn = $this->columnExists($column);
        if ($ixColumn == DA_OUT_OF_RANGE) {
            throw new ColumnOutOfRangeException($column);
        }
        $queryString = "SELECT {$this->getDBColumnNamesAsString()} FROM {$this->getTableName()} 
                JOIN item AS citem ON cui_itemno = itm_itemno 
                JOIN customer ON cui_custno = cus_custno 
                JOIN address ON add_siteno = cui_siteno AND add_custno = cui_custno
                join itemtype on ity_itemtypeno = citem.itm_itemtypeno
                WHERE {$this->getDBColumnName($ixColumn)}={$this->getFormattedValue($ixColumn)}";
        if ($sortColumn != '') {
            $ixSortColumn = $this->columnExists($sortColumn);
            if ($ixSortColumn == DA_OUT_OF_RANGE) {
                $this->raiseError("Sort Column " . $column . " out of range");
                return DA_OUT_OF_RANGE;
            } else {
                $queryString .= " ORDER BY " . $this->getDBColumnName($ixSortColumn);
            }
        }
        $this->setQueryString($queryString);
        return ($this->getRows());
    }

    function getItemsByContractID($customerItemId)
    {
        $queryString = "SELECT " . $this->getDBColumnNamesAsString() . " FROM
          custitem_contract
          JOIN custitem ON cic_cuino = cui_cuino 
          JOIN item AS citem ON cui_itemno = itm_itemno
          JOIN customer ON cui_custno = cus_custno
          JOIN address ON add_siteno = cui_siteno AND add_custno = cui_custno
          join itemtype on ity_itemtypeno = citem.itm_itemtypeno
       WHERE
          cic_contractcuino = $customerItemId

       ORDER BY 
        itm_desc";
        $this->setQueryString($queryString);
        return ($this->getRows());
    }

    /**
     * Get a list of server rows for given customer
     *
     * @return bool
     */
    function getServersByCustomerID()
    {
        $this->setMethodName('getServersByCustomerID');
        if ($this->getValue(self::customerID) == '') {
            $this->raiseError('customerID not set');
        }
        $this->setQueryString(
            "SELECT " . $this->getDBColumnNamesAsString() . " FROM " . $this->getTableName() . " JOIN item AS citem ON cui_itemno = itm_itemno
 			    JOIN customer ON cui_custno = cus_custno
 			    JOIN address ON add_siteno = cui_siteno AND add_custno = cui_custno
          JOIN custitem_contract cic ON cic.cic_cuino = custitem.cui_cuino
          JOIN custitem con ON con.cui_cuino = cic.cic_contractcuino
          JOIN item con_item ON con.cui_itemno = con_item.itm_itemno
          join itemtype on ity_itemtypeno = citem.itm_itemtypeno
      
 		   WHERE " . $this->getDBColumnName(self::customerID) . "=" . $this->getValue(
                self::customerID
            ) . " AND citem.itm_itemtypeno = " . CONFIG_SERVER_ITEMTYPEID . " AND con_item.itm_itemtypeno = " . CONFIG_SERVERCARE_ITEMTYPEID . " AND con.renewalStatus = 'R'" . " AND " . $this->getDBColumnName(
                self::serverName
            ) . " > ''
      ORDER BY citem.itm_desc, custitem.installationDate"
        );
        return (parent::getRows());
    }

    function getContractDescriptionsByCustomerItemID($customerItemID)
    {
        $db     = new dbSweetcode();
        $select = "SELECT
        GROUP_CONCAT( i.itm_desc ) as contracts
      FROM
        custitem_contract cic
        JOIN custitem c ON cic_contractcuino = c.cui_cuino
        JOIN item i ON i.itm_itemno = c.cui_itemno
        join itemtype on ity_itemtypeno = i.itm_itemno
      WHERE
       cic_cuino = $customerItemID";
        $db->query($select);
        $db->next_record();
        return $db->Record['contracts'];
    }

    public function getCountCustomerDirectDebitItems($customerID)
    {
        $db     = new dbSweetcode();
        $select = "SELECT COUNT(custitem.`cui_cuino`) as directDebitCount FROM custitem WHERE directDebitFlag = 'Y' AND `cui_custno` = $customerID";
        $db->query($select);
        if (!$db->num_rows()) {
            return 0;
        }
        $db->next_record();
        return $db->Record['directDebitCount'];
    }
}