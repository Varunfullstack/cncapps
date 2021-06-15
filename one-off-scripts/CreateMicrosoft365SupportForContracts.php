<?php
include __DIR__ . '/../htdocs/config.inc.php';
global $cfg;
require_once($cfg['path_dbe'] . '/DBECustomerItem.inc.php');
$query = "SELECT
  cus_custno,
  cus_name,
  cui_cuino
FROM
  customer
  JOIN PASSWORD
    ON password.`pas_custno` = customer.`cus_custno`
    AND password.`serviceID` = 10 AND archivedAt IS NULL
  JOIN
    (SELECT
      contracts.`cui_custno`,
      contracts.`cui_cuino`,
      SUM(
        contractCoveredItem.`cui_itemno` = 18338
      ) AS officeInContract
    FROM
      custitem contracts
      JOIN item contractItem
        ON contracts.`cui_itemno` = contractItem.`itm_itemno`
        AND contractItem.`itm_itemtypeno` = 55
        AND itm_discontinued = 'N'
      JOIN custitem_contract
        ON contracts.`cui_cuino` = custitem_contract.`cic_contractcuino`
      JOIN custitem contractCoveredItem
        ON custitem_contract.`cic_cuino` = contractCoveredItem.`cui_cuino`
    WHERE contracts.`declinedFlag` = 'N'
    GROUP BY contracts.`cui_custno`) serverCareContract
    ON serverCareContract.cui_custno = customer.`cus_custno`
    AND officeInContract = 0";
/** @var $db dbSweetcode */
global $db;
$result = $db->query($query);
$thing  = null;
foreach ($result->fetch_all(MYSQLI_ASSOC) as $row) {
    $contractId   = $row['cui_cuino'];
    $customerId   = $row['cus_custno'];
    $customerItem = new DBECustomerItem($thing);
    $customerItem->setValue(DBECustomerItem::siteNo, 0);
    $customerItem->setValue(DBECustomerItem::bypassCWAAgentCheck, true);
    $customerItem->setValue(DBECustomerItem::itemID, 18338);
    $customerItem->setValue(DBECustomerItem::customerItemID, null);
    $customerItem->setValue(DBECustomerItem::customerID, $customerId);
    $customerItem->insertRow();
    $customerItemId = $customerItem->getPKValue();
    $customerItem->addContract($customerItemId, $contractId);
}