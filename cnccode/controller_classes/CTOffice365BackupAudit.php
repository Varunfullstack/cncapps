<?php
/**
 * Created by PhpStorm.
 * User: fizdalf
 * Date: 25/07/2018
 * Time: 12:33
 */

global $cfg;
require_once($cfg['path_bu'] . '/BUContact.inc.php');
require_once($cfg['path_bu'] . '/BUHeader.inc.php');
require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_dbe'] . '/DSForm.inc.php');
require_once $cfg['path_dbe'] . '/DBEJContactAudit.php';

class CTOffice365BackupAudit extends CTCNC
{
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
            "sales",
        ];
        if (!self::hasPermissions($roles)) {
            Header("Location: /NotAllowed.php");
            exit;
        }
    }

    /**
     * Route to function based upon action passed
     * @throws Exception
     */
    function defaultAction()
    {
        switch ($this->getAction()) {
            case 'getData':
                return $this->getData();
                break;
            default:
                $this->displaySearchForm();
        }
    }

    function getData()
    {
        $draw = $_REQUEST['draw'];
        $columns = $_REQUEST['columns'];
        $search = $_REQUEST['search'];
        $order = $_REQUEST['order'];
        $offset = $_REQUEST['start'];
        $limit = $_REQUEST['length'];

        $columnsNames = [
            "customerName",
            "contractID",
            "office365BackupUsers",
            "contractUsers",
            "createdAt",
        ];
        $columnsDefinition = [
            "customerName"         => "customer.cus_name",
            "contractID"           => "custitem.cui_cuino",
            "office365BackupUsers" => "users",
            "contractUsers"        => "currentUsers",
            "createdAt"            => "createdAt",
        ];

        /** @var dbSweetcode $db */
        global $db;
        $countQuery = "select count(*) from contractUsersLog left join custItem ON contractId = custItem.cui_cuino LEFT JOIN customer
    ON custitem.`cui_custno` = customer.`cus_custno`";
        $totalCountResult = $db->query($countQuery);
        $totalCount = $totalCountResult->fetch_row()[0];
        $defaultQuery = "select 
customer.cus_name as customerName,
contractID,
users as office365BackupUsers,
currentUsers as contractUsers,
createdAt
from contractUsersLog left join custItem ON contractId = custItem.cui_cuino LEFT JOIN customer
    ON custitem.`cui_custno` = customer.`cus_custno` where 1 = 1 ";
        $columnSearch = [];
        $parameters = [];
        foreach ($columns as $column) {
            if (!isset($columnsDefinition[$column['data']])) {
                continue;
            }

            if ($column['search']['value']) {
                $columnSearch[] = $columnsDefinition[$column['data']] . " like ?";
                $parameters[] = [
                    "type"  => "s",
                    "value" => "%" . $column['search']['value'] . "%"
                ];
            }
        }

        if (count($columnSearch)) {
            $wherePart = " and " . implode(" and ", $columnSearch);
            $defaultQuery .= $wherePart;
            $countQuery .= $wherePart;
        }

        $startDate = @$_REQUEST['startDate'];
        $endDate = @$_REQUEST['endDate'];

        if ($startDate) {
            $defaultQuery .= " and createdAt >= ? ";
            $parameters[] = ["type" => "s", "value" => $startDate];

        }
        if ($endDate) {
            $defaultQuery .= " and createdAt <= ? ";
            $parameters[] = ["type" => "s", "value" => $endDate];
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

        $countResult = $db->preparedQuery(
            $countQuery,
            $parameters
        );
        $filteredCount = $countResult->fetch_row()[0];

        $defaultQuery .= " limit ?,?";
        $parameters[] = ["type" => "i", "value" => $offset];
        $parameters[] = ["type" => "i", "value" => $limit];
        $result = $db->preparedQuery(
            $defaultQuery,
            $parameters
        );
        $data = array_map(
            function ($row) {
                return [
                    "customerName"         => $row['customerName'],
                    "contractID"           => $row['contractID'],
                    "office365BackupUsers" => $row['office365BackupUsers'],
                    "contractUsers"        => $row['contractUsers'],
                    "createdAt"            => $row['createdAt'],
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
    }

    /**
     * Display the initial form that prompts the employee for details
     * @access private
     * @throws Exception
     * @throws Exception
     * @throws Exception
     */
    function displaySearchForm()
    {
        $this->setMethodName('displaySearchForm');
        $this->setTemplateFiles(
            'CustomerSearch',
            'Office365BackupAudit'
        );
// Parameters
        $this->setPageTitle("Office 365 Backup Audit");
        $this->template->parse(
            'CONTENTS',
            'CustomerSearch',
            true
        );
        $this->parsePage();
    }
}
