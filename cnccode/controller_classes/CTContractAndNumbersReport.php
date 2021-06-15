<?php
/**
 * Standard Text controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */

use Syonix\ChangelogViewer\Factory\ViewerFactory;

global $cfg;
require_once($cfg['path_ct'] . '/CTCNC.inc.php');

class CTContractAndNumbersReport extends CTCNC
{
    function __construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg)
    {
        if (!self::hasPermissions(REPORTS_PERMISSION)) {
            Header("Location: /NotAllowed.php");
            exit;
        }
        parent::__construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg);
    }

    /**
     * Route to function based upon action passed
     * @throws Exception
     */
    function defaultAction()
    {
        switch ($this->getAction()) {
            case 'csvContractAndNumbersReport':
                $this->csvContractAndNumbersReport();
                break;
            default:
                $this->displayContractAndNumbersReport();
        }
    }

    function csvContractAndNumbersReport()
    {
        $csv_export = '';
        $db         = $this->getContractAndNumberData();
        $headersSet = false;
        while ($db->next_record()) {
            $row = $db->Record;
            if (!$headersSet) {
                foreach (array_keys($row) as $key) {
                    if (!is_numeric($key)) {
                        $csv_export .= $key . ';';
                    }
                }
            }
            $this->template->set_var(
                array(
                    'customerName'                => $row["customerName"],
                    'serviceDeskProduct'          => $row['serviceDeskProduct'],
                    'serviceDeskUsers'            => $row['serviceDeskUsers'],
                    'serviceDeskContract'         => $row['serviceDeskContract'],
                    'serviceDeskCostPerUserMonth' => $row['serviceDeskCostPerUserMonth'],
                    'serverCareProduct'           => $row['serverCareProduct'],
                    'virtualServers'              => $row['virtualServers'],
                    'physicalServers'             => $row['physicalServers'],
                    'serverCareContract'          => $row['serverCareContract']
                )
            );
            $this->template->parse(
                'contracts',
                'contractItemBlock',
                true
            );

        }
    }

    private function getContractAndNumberData()
    {
        global $db; //PHPLib DB object
        $queryString = "SELECT
  `cus_custno`,
  cus_name AS customerName,
  serviceDeskProduct,
  COALESCE(serviceDeskUsers,0) AS serviceDeskUsers,
  COALESCE(serviceDeskContract,0) AS serviceDeskContract,
  COALESCE(serviceDeskCostPerUserMonth,0) AS serviceDeskCostPerUserMonth,
  serverCareProduct,
  COALESCE(virtualServers,0) AS virtualServers,
  COALESCE(physicalServers,0) AS physicalServers,
  COALESCE(serverCareContract,0) AS serverCareContract,
  concat('M ',coalesce(mainCount, 0),', SV ',coalesce(supervisorCount,0),', S ', coalesce(supportCount, 0),', D ', coalesce(delegateCount, 0),', N ', coalesce(noLevelCount, 0),', T ', coalesce(totalCount, 0) ) as supportedUsers,
  actualSupportedUsersCount > serviceDeskUsers as moreUsersThanExpected 
FROM
  customer
  LEFT JOIN
  (SELECT
     `cui_custno`                      AS customerId,
     itm_desc                          AS serviceDeskProduct,
     sum(custitem.`cui_users`)              AS serviceDeskUsers,
     sum(round(custitem.cui_sale_price, 0)) AS serviceDeskContract,
     sum(ROUND(
         custitem.cui_sale_price / custitem.cui_users / 12,
         2
     ))                                 AS serviceDeskCostPerUserMonth
   FROM
     custitem
     LEFT JOIN item
       ON item.`itm_itemno` = custitem.`cui_itemno`
   WHERE itm_desc LIKE '%servicedesk%'
         AND itm_discontinued <> 'Y'
         AND custitem.`declinedFlag` <> 'Y' 
      group by custitem.`cui_custno` 
      ) AS test1
    ON test1.customerId = customer.`cus_custno`
  LEFT JOIN
  (SELECT
  custitem.`cui_custno` AS customerId,
  item.`itm_desc` as serverCareProduct,
  SUM(
    ROUND(custitem.`cui_sale_price`, 0)
  ) AS serverCareContract,
  SUM(physicalServers) AS physicalServers,
  SUM(virtualServers) AS virtualServers
FROM
  custitem
  LEFT JOIN item
    ON item.`itm_itemno` = custitem.`cui_itemno`
  LEFT JOIN
    (SELECT
      custitem_contract.cic_contractcuino,
      SUM(
        serverItem.`itm_desc` NOT LIKE '%virtual%'
      ) AS physicalServers,
      SUM(
        serverItem.`itm_desc` LIKE '%virtual%'
      ) AS virtualServers
    FROM
      custitem_contract
      LEFT JOIN custitem AS servers
        ON custitem_contract.`cic_cuino` = servers.cui_cuino
      LEFT JOIN item AS serverItem
        ON servers.cui_itemno = serverItem.`itm_itemno`
    GROUP BY custitem_contract.cic_contractcuino) b
    ON b.`cic_contractcuino` = cui_cuino
WHERE item.`itm_desc` LIKE '%servercare%'
  AND item.itm_discontinued <> 'Y'
  AND custitem.`declinedFlag` <> 'Y'
GROUP BY custitem.`cui_custno`) test2
    ON customer.cus_custno = test2.customerId
left join (
    select 
  contact.`con_custno`,
  sum(contact.`supportLevel` = 'main') as mainCount,
  SUM(
    contact.`supportLevel` = 'supervisor'
  ) AS supervisorCount,
  SUM(
    contact.`supportLevel` = 'support'
  ) AS supportCount,
  SUM(
    contact.`supportLevel` = 'delegate'
  ) AS delegateCount,
  SUM(
    contact.`supportLevel` is null
  ) AS noLevelCount,
  sum(1) as totalCount,
  sum(contact.supportLevel is not null) as actualSupportedUsersCount
from
  contact 
where contact.active 
GROUP BY con_custno 
) supportUsers on supportUsers.con_custno = customer.cus_custno
WHERE serviceDeskProduct IS NOT NULL OR serverCareProduct IS NOT NULL
ORDER BY cus_name   ";
        $db->query($queryString);
        return $db;
    }

    /**
     * @throws Exception
     */
    function displayContractAndNumbersReport()
    {
        $this->setMenuId(504);
        $this->setPageTitle("Service Contracts Ratio");
        $this->setTemplateFiles(
            'ContractAndNumbersReport',
            'ContractAndNumbersReport'
        );
        $db = $this->getContractAndNumberData();
        $this->template->set_block(
            'ContractAndNumbersReport',
            'contractItemBlock',
            'contracts'
        );
        while ($db->next_record()) {
            $row = $db->Record;
            $this->template->set_var(
                array(
                    'customerName'                => $row["customerName"],
                    'serviceDeskProduct'          => $row['serviceDeskProduct'],
                    'serviceDeskUsers'            => $row['serviceDeskUsers'],
                    'serviceDeskContract'         => $row['serviceDeskContract'],
                    'serviceDeskCostPerUserMonth' => $row['serviceDeskCostPerUserMonth'],
                    'serverCareProduct'           => $row['serverCareProduct'],
                    'virtualServers'              => $row['virtualServers'],
                    'physicalServers'             => $row['physicalServers'],
                    'serverCareContract'          => $row['serverCareContract'],
                    'supportedUsers'              => $row['supportedUsers'],
                    'moreThanExpectedClass'       => $row['moreUsersThanExpected'] ? "red" : null
                )
            );
            $this->template->parse(
                'contracts',
                'contractItemBlock',
                true
            );
        }
        /** @var $db dbSweetcode */ global $db;
        $query = "SELECT
  COUNT(*) AS total
FROM
  custitem
  JOIN customer
    ON customer.`cus_custno` = custitem.`cui_custno`
    JOIN contact ON contact.`con_custno` = customer.cus_custno AND contact.`active`
WHERE cui_itemno = 4111
  AND cui_expiry_date >= NOW()
  AND renewalStatus <> 'D'
  AND declinedFlag <> 'Y'";
        $db->query($query);
        $db->next_record(MYSQLI_ASSOC);
        $this->template->setVar('totalPrePaySupportUsers', $db->Record['total']);
        $this->template->parse(
            'CONTENTS',
            'ContractAndNumbersReport',
            true
        );
        $this->parsePage();
        exit;
    }

    /**
     * Display list of types
     * @access private
     * @throws Exception
     */
    function displayList()
    {
        $this->setMethodName('displayList');
        $this->setPageTitle('About');
        $this->setTemplateFiles(
            array('ChangeLog' => 'About.inc')
        );
        $changelog = ViewerFactory::createMarkdownHtmlViewer(__DIR__ . '/../../CHANGELOG.md')->frame(false)->styles(
            false
        )->downloadLinks(false)
//            ->modal(true)
            ->scripts(false)->build();
        $this->template->set_var('changeLog', $changelog);
        $this->template->parse('CONTENTS', 'ChangeLog', true);
        $this->parsePage();
    }
}// end of class
