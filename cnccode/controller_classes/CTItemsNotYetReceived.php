<?php
/**
 * Created by PhpStorm.
 * User: fizdalf
 * Date: 05/12/2018
 * Time: 12:43
 */
global $cfg;
require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_bu'] . '/BUItemsNotYetReceived.php');

class CTItemsNotYetReceived extends CTCNC
{
    const GET_ORDERS_WITHOUT_SR_DATA = "GET_ORDERS_WITHOUT_SR_DATA";
    const GET_DATA                   = "getData";
    /**
     * Dataset for item record storage.
     *
     * @var     DSForm
     * @access  private
     */
    var $dsItem = '';
    /** @var BUItemsNotYetReceived */
    private $buItemsNotYetReceived;

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
            SALES_PERMISSION,
        ];
        $this->setMenuId(309);
        if (!self::hasPermissions($roles)) {
            Header("Location: /NotAllowed.php");
            exit;
        }
        $this->buItemsNotYetReceived = new BUItemsNotYetReceived($this);
    }

    /**
     * Route to function based upon action passed
     * @throws Exception
     */
    function defaultAction()
    {
        switch ($this->getAction()) {
            case self::GET_DATA:
                $daysAgo = null;
                if ($this->getParam('daysAgo')) {
                    $daysAgo = $this->getParam('daysAgo');
                }
                $data = $this->getData($daysAgo);
                echo json_encode($data);
                break;
            case self::GET_ORDERS_WITHOUT_SR_DATA:
                global $db;
                $db->query(
                    "SELECT
  ordline.odl_ordno AS salesOrderId,
  odl_desc AS itemLineDescription
FROM
  ordline
  LEFT JOIN ordhead
    ON ordhead.odh_ordno = ordline.`odl_ordno`
  LEFT JOIN problem
    ON pro_linked_ordno = ordline.odl_ordno
  LEFT JOIN item
    ON odl_itemno = item.itm_itemno
WHERE (
    (
      odl_type = 'I'
      AND (
        (
          item.renewalTypeID
          AND (
            ordline.odl_renewal_cuino IS NULL
            OR ordline.odl_renewal_cuino = 0
          )
        )
        OR ordline.odl_desc LIKE '%labour%'
      )
    )
    OR (
      odl_type = 'C'
      AND ordline.odl_desc LIKE '%labour%'
    )
  )
  AND problem.pro_problemno IS NULL
  AND odh_type IN ('I', 'P')
GROUP BY ordline.`odl_ordno`
"
                );
                $data = $db->fetchAll();
                echo json_encode(["status" => "ok", "data" => $data]);
                break;
            default:
                $this->displayContractAndNumbersReport();
                break;
        }
    }

    private function getData($daysAgo)
    {
        return $this->buItemsNotYetReceived->getItemsNotYetReceived($daysAgo);
    }

    /**
     * @throws Exception
     */
    function displayContractAndNumbersReport()
    {

        $this->setPageTitle("Purchase Order Status Report");
        $this->setTemplateFiles(
            'ItemsNotYetReceived',
            'ItemsNotYetReceived'
        );
        $this->loadReactScript('SalesOrdersWithoutSRComponent.js');
        $this->template->parse(
            'CONTENTS',
            'ItemsNotYetReceived',
            true
        );
        $this->parsePage();
    }

    /**
     * @param DateTimeInterface $date
     * @return string
     */
    private function getDateOrNA($date)
    {
        if (!$date) {
            return 'N/A';
        }
        return $date->format(
            'd/m/Y'
        );
    }
}// end of class
