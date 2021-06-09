<?php

/**
 * Standard Text controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */

use CNCLTD\Data\DBConnect;
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
      case "contracts":
        echo json_encode($this->getContracts(), JSON_NUMERIC_CHECK);
        exit;
      default:
        $this->displayForm();
    }
  }
  /**
   * Export expenses that have not previously been exported
   * @access private
   * @throws Exception     
   */
  function displayForm()
  {
    $this->setPageTitle('Service Contracts Ratio');
    $this->setTemplateFiles(
      'ContractAndNumbersReport',
      'ContractAndNumbersReport'
    );
    $this->template->parse(
      'CONTENTS',
      'ContractAndNumbersReport',
      true
    );
    $this->loadReactScript('ContractAndNumbersReportComponent.js');
    $this->loadReactCSS('ContractAndNumbersReportComponent.css');
    $this->parsePage();
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


  //--------------new 
  function getContracts()
  {
    $contracts = [];
    $db = $this->getContractAndNumberData();
    while ($db->next_record()) {
      $row = $db->Record;
      $contracts[] =
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
        );
    }
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
    $totalPrePay = DBConnect::fetchOne($query)["total"];
    return $this->success(["totalPrePay" => $totalPrePay, "contracts" => $contracts]);
  }
}// end of class
