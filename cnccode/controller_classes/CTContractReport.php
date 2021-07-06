<?php
/**
 * Standard Text controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
global $cfg;
require_once($cfg['path_ct'] . '/CTCNC.inc.php');

class CTContractReport extends CTCNC
{
    function __construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg)
    {
        parent::__construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg);
        $this->setMenuId(306);
    }

    /**
     * Route to function based upon action passed
     * @throws Exception
     */
    function defaultAction()
    {
        switch ($this->getAction()) {
            case 'getData':
                $draw    = $_REQUEST['draw'];
                $columns = $_REQUEST['columns'];
                $search  = $_REQUEST['search'];
                $order   = $_REQUEST['order'];
                $offset  = $_REQUEST['start'];
                $limit   = $_REQUEST['length'];
                $columnsNames      = [
                    'contractId',
                    'users',
                    'customerName',
                    'siteAddress',
                    'itemDescription',
                    'renewalStatus',
                    'initialContractLength',
                    'startDate',
                    'expiryDate',
                    'value',
                    'balance',
                    'topUp',
                ];
                $columnsDefinition = [
                    'contractId'            => 'contractId',
                    'users'                 => 'users',
                    'customerName'          => 'customerName',
                    'siteAddress'           => 'siteAddress',
                    'itemDescription'       => 'itemDescription',
                    'renewalStatus'         => 'renewalStatus',
                    'initialContractLength' => 'initialContractLength',
                    'startDate'             => 'startDate',
                    'expiryDate'            => 'expiryDate',
                    'value'                 => 'value',
                    'balance'               => 'balance',
                    'topUp'                 => 'topUp',
                ];
                $columnsTypes = [
                    'startDate'  => "date",
                    'expiryDate' => "date"
                ];
                /** @var dbSweetcode $db */ global $db;
                $countQuery       = "select count(*) FROM custitem
         JOIN customer ON customer.cus_custno = custitem.cui_custno
         JOIN item ON item.itm_itemno = custitem.cui_itemno
         JOIN address ON address.add_siteno = custitem.cui_siteno AND address.add_custno = custitem.cui_custno
WHERE 1 = 1
  AND customer.cus_custno <> 2511
  AND item.renewalTypeID IS NOT NULL
  AND item.renewalTypeID <> 0";
                $totalCountResult = $db->query($countQuery);
                $totalCount       = $totalCountResult->fetch_row()[0];
                $defaultQuery     = "
select * from (
SELECT custitem.cui_cuino                                             as 'contractId',
       custitem.cui_users                                             as 'users',
       customer.cus_name                                              as 'customerName',
       address.add_add1                                               as 'siteAddress',
       item.itm_desc                                                  as 'itemDescription',
       custitem.installationDate                                      as 'startDate',
       getContractExpiryDate(installationDate, initialContractLength) as 'expiryDate',
       custitem.cui_sale_price                                        as 'value',
       custitem.curGSCBalance                                         as 'balance',
       customer.gscTopUpAmount                                        as 'topUp', 
       custitem.renewalStatus                                         as 'renewalStatus',
       custitem.initialContractLength                                 as 'initialContractLength'
FROM custitem
         JOIN customer ON customer.cus_custno = custitem.cui_custno
         JOIN item ON item.itm_itemno = custitem.cui_itemno
         JOIN address ON address.add_siteno = custitem.cui_siteno AND address.add_custno = custitem.cui_custno
WHERE 1 = 1
  AND customer.cus_custno <> 2511
  AND item.renewalTypeID IS NOT NULL
  AND item.renewalTypeID <> 0 ) a where 1 = 1 ";
                $columnSearch     = [];
                $parameters       = [];
                foreach ($columns as $column) {
                    if (!isset($columnsDefinition[$column['data']])) {
                        continue;
                    }
                    if ($column['search']['value']) {
                        if (!isset($columnsTypes[$column['data']])) {
                            $columnSearch[] = $columnsDefinition[$column['data']] . " like ?";
                            $parameters[]   = [
                                "type"  => "s",
                                "value" => "%" . $column['search']['value'] . "%"
                            ];
                        } elseif ($columnsTypes[$column['data']] == 'date') {
                            $columnSearch[] = $columnsDefinition[$column['data']] . " = ?";
                            $parameters[]   = [
                                "type"  => "s",
                                "value" => $column['search']['value']
                            ];
                        }


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
                $defaultQuery .= " limit ?,?";
                $parameters[] = ["type" => "i", "value" => $offset];
                $parameters[] = ["type" => "i", "value" => $limit];
                $result       = $db->preparedQuery(
                    $defaultQuery,
                    $parameters
                );
                $data         = $result->fetch_all(MYSQLI_ASSOC);
                echo json_encode(
                    [
                        "draw"            => +$draw,
                        "recordsTotal"    => +$totalCount,
                        "recordsFiltered" => $filteredCount,
                        "data"            => $data
                    ]
                );
                break;
            default:
                $this->displayList();
                break;
        }
    }

    /**
     * Display list of types
     * @access private
     * @throws Exception
     */
    function displayList()
    {
//        $this->setPageTitle('Contract Report');
//        $this->setTemplateFiles(
//            array('ChangeLog' => 'About.inc')
//        );
//        global $twig;
//        $html = $twig->render('@internal/contractReport/contractReport.html.twig');
//
//        $this->template->set_var('changelog', $html);
//        $this->template->debug = true;
//        $this->template->parse('CONTENTS', 'ChangeLog', true);
//        $this->parsePage();
        $this->setMethodName('displayList');
        $this->setPageTitle('Contract Report');
        $this->setTemplateFiles(
            array('ChangeLog' => 'About.inc')
        );
        global $twig;
        $changelog = $twig->render('@internal/contractReport/contractReport.html.twig');
        $this->template->set_var('changeLog', $changelog);
        $this->template->parse('CONTENTS', 'ChangeLog', true);
        $this->parsePage();
    }
}
