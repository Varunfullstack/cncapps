<?php
/**
 * Created by PhpStorm.
 * User: fizdalf
 * Date: 22/11/2018
 * Time: 9:19
 */
global $cfg;
require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_dbe'] . '/DBECustomerItem.inc.php');

class CTRenewalsUpdate extends CTCNC
{
    const UPDATE_AUTO_GENERATE_CONTRACT_INVOICE = "updateAutoGenerateContractInvoice";
    const UPDATE_USERS                          = "updateUsers";
    const GET_DATA                              = 'getData';

    function __construct($requestMethod,
                         $postVars,
                         $getVars,
                         $cookieVars,
                         $cfg
    )
    {
        parent::__construct(
            $requestMethod,
            $postVars,
            $getVars,
            $cookieVars,
            $cfg
        );
        $roles = [
            SALES_PERMISSION
        ];
        if (!self::hasPermissions($roles)) {
            Header("Location: /NotAllowed.php");
            exit;
        }
        $this->setMenuId(310);
    }

    /**
     * Route to function based upon action passed
     * @throws Exception
     */
    function defaultAction()
    {
        switch ($this->getAction()) {
            case self::GET_DATA:
                $draw              = $_REQUEST['draw'];
                $columns           = $_REQUEST['columns'];
                $search            = $_REQUEST['search'];
                $order             = $_REQUEST['order'];
                $offset            = $_REQUEST['start'];
                $limit             = $_REQUEST['length'];
                $columnsNames      = [
                    "contractName",
                    "customerName",
                    "itemBillingCategoryName",
                    "numberOfUsers",
                    "invoicePeriodMonths",
                    'nextInvoicePeriod',
                    'autoGenerateInvoice',
                    "directDebit"
                ];
                $columnsDefinition = [
                    "contractName"            => "item.`itm_desc`",
                    "customerName"            => "customer.`cus_name`",
                    "itemBillingCategoryName" => "itemBillingCategory.name",
                    "invoicePeriodMonths"     => "custitem.invoicePeriodMonths",
                    'nextInvoicePeriod'       => 'DATE_ADD(`installationDate`, INTERVAL `totalInvoiceMonths` MONTH )',
                    'autoGenerateInvoice'     => "autoGenerateContractInvoice",
                    "directDebit"             => "directDebitFlag",
                ];
                /** @var dbSweetcode $db */ global $db;
                $countQuery       = "select count(*) from custitem left join item ON itm_itemno = cui_itemno LEFT JOIN customer
    ON custitem.`cui_custno` = customer.`cus_custno`
  LEFT JOIN itemBillingCategory
    ON item.`itemBillingCategoryId` = itemBillingCategory.id where declinedFlag = 'N'  AND renewalTypeID = 2";
                $totalCountResult = $db->query($countQuery);
                $totalCount       = $totalCountResult->fetch_row()[0];
                $defaultQuery     = "SELECT
  custitem.`cui_cuino` AS contractID,
  item.`itm_desc` AS contractName,
  customer.`cus_name` AS customerName,
  itemBillingCategory.name AS itemBillingCategoryName,
  custitem.`cui_users` AS numberOfUsers,
       directDebitFlag = 'Y' as directDebit,
       autoGenerateContractInvoice = 'Y' as autoGenerateContractInvoice, 
       DATE_FORMAT( DATE_ADD(`installationDate`, INTERVAL `totalInvoiceMonths` MONTH ), '%d/%m/%Y') as invoiceFromDate,
DATE_FORMAT(
 				DATE_SUB(
 					DATE_ADD(`installationDate`, INTERVAL `totalInvoiceMonths` + `invoicePeriodMonths` MONTH ),
 					INTERVAL 1 DAY
 				)
 				, '%d/%m/%Y') as invoiceToDate,
       invoicePeriodMonths
FROM
  custitem
  LEFT JOIN item
    ON itm_itemno = cui_itemno  
  LEFT JOIN customer
    ON custitem.`cui_custno` = customer.`cus_custno`
  LEFT JOIN itemBillingCategory
    ON item.`itemBillingCategoryId` = itemBillingCategory.id
WHERE declinedFlag = 'N'
  AND renewalTypeID = 2 ";
                $columnSearch     = [];
                $parameters       = [];
                foreach ($columns as $column) {
                    if (!isset($columnsDefinition[$column['data']])) {
                        continue;
                    }
                    if ($column['search']['value']) {
                        $columnSearch[] = $columnsDefinition[$column['data']] . " like ?";
                        $parameters[]   = [
                            "type"  => "s",
                            "value" => "%" . $column['search']['value'] . "%"
                        ];
                    }
                }
                if (count($columnSearch)) {
                    $wherePart    = " and " . implode(" and ", $columnSearch);
                    $defaultQuery .= $wherePart;
                    $countQuery   .= $wherePart;
                }
                $orderBy = [];
                if (count($order)) {
                    foreach ($order as $orderItem) {
                        if (!isset($columnsNames[(int)$orderItem['column']])) {
                            continue;
                        }
                        $orderBy[] = $columnsDefinition[$columnsNames[(int)$orderItem['column']]] . " " . mysqli_real_escape_string(
                                $db->link_id(),
                                $orderItem['dir']
                            );
                    }
                    if (count($orderBy)) {
                        $defaultQuery .= (" order by " . implode(' , ', $orderBy));
                    }
                }
                $countResult   = $db->preparedQuery(
                    $countQuery,
                    $parameters
                );
                $filteredCount = $countResult->fetch_row()[0];
                $defaultQuery  .= " limit ?,?";
                $parameters[]  = ["type" => "i", "value" => $offset];
                $parameters[]  = ["type" => "i", "value" => $limit];
                $result        = $db->preparedQuery(
                    $defaultQuery,
                    $parameters
                );
                $data          = array_map(
                    function ($row) {
                        return [
                            "contractID"                  => $row['contractID'],
                            "contractName"                => $row['contractName'],
                            "customerName"                => $row['customerName'],
                            "itemBillingCategoryName"     => $row['itemBillingCategoryName'],
                            "numberOfUsers"               => $row['numberOfUsers'],
                            "invoicePeriodMonths"         => $row['invoicePeriodMonths'],
                            "invoiceFromDate"             => $row['invoiceFromDate'],
                            "invoiceToDate"               => $row['invoiceToDate'],
                            "directDebit"                 => $row['directDebit'],
                            "autoGenerateContractInvoice" => $row['autoGenerateContractInvoice']
                        ];
                    },
                    $result->fetch_all(MYSQLI_ASSOC)
                );
                echo json_encode(
                    [
                        "draw"            => $draw,
                        "recordsTotal"    => $totalCount,
                        "recordsFiltered" => $filteredCount,
                        "data"            => $data
                    ]
                );
                break;
            case self::UPDATE_USERS:
                $dbeCustomerItem = $this->getCustomerItemFromRequest();
                $data            = $this->getJSONData();
                $dbeCustomerItem->setValue(DBECustomerItem::users, $data['users']);
                $dbeCustomerItem->setValue(
                    DBECustomerItem::curUnitCost,
                    $data['users'] * $dbeCustomerItem->getValue(
                        DBECustomerItem::costPricePerMonth
                    ) * 12
                );
                $dbeCustomerItem->setValue(
                    DBECustomerItem::curUnitSale,
                    $data['users'] * $dbeCustomerItem->getValue(
                        DBECustomerItem::salePricePerMonth
                    ) * 12
                );
                $dbeCustomerItem->updateRow();
                echo json_encode(["status" => "ok"]);
                break;
            case self::UPDATE_AUTO_GENERATE_CONTRACT_INVOICE:
                $dbeCustomerItem = $this->getCustomerItemFromRequest();
                $data            = $this->getJSONData();
                $dbeCustomerItem->setValue(DBECustomerItem::autoGenerateContractInvoice, $data['status'] ? 'Y' : 'N');
                $dbeCustomerItem->updateRow();
                echo json_encode(["status" => "ok"]);
                break;
            default:
                $this->displayList();
                break;
        }
    }

    /**
     * @throws Exception
     */
    private function displayList()
    {
        $this->setPageTitle('Renewals Update');
        $this->setTemplateFiles(
            array('RenewalsUpdate' => 'RenewalsUpdate')
        );
        $this->template->parse(
            'CONTENTS',
            'RenewalsUpdate',
            true
        );
        $this->parsePage();
    }

    private function checkContractIdIsSet(): void
    {
        $data = $this->getJSONData();
        if (!isset($data['contractID'])) {
            echo json_encode(["error" => "contractID is mandatory"]);
            http_response_code(400);
            exit;
        }
    }

    /**
     * @return DBECustomerItem
     */
    private function getCustomerItemFromRequest(): DBECustomerItem
    {
        $this->checkContractIdIsSet();
        $data            = $this->getJSONData();
        $contractID      = $data['contractID'];
        $dbeCustomerItem = new DBECustomerItem($this);
        $dbeCustomerItem->getRow($contractID);
        if (!$dbeCustomerItem->rowCount()) {
            echo json_encode(["error" => "Contract not found"]);
            http_response_code(400);
            exit;
        }
        return $dbeCustomerItem;
    }
}