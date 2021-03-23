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
                $data = $this->buItemsNotYetReceived->getOrdersWithoutSR();
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
