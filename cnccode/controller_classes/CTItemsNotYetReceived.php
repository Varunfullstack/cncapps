<?php
/**
 * Created by PhpStorm.
 * User: fizdalf
 * Date: 05/12/2018
 * Time: 12:43
 */
require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_bu'] . '/BUItemsNotYetReceived.php');

class CTItemsNotYetReceived extends CTCNC
{
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
            "sales",
            "technical"
        ];
        if (!self::hasPermissions($roles)) {
            Header("Location: /NotAllowed.php");
            exit;
        }
        $this->buItemsNotYetReceived = new BUItemsNotYetReceived($this);
    }

    /**
     * Route to function based upon action passed
     */
    function defaultAction()
    {
        switch ($_REQUEST['action']) {

            default:
                $this->displayContractAndNumbersReport();
                break;
        }
    }

    function displayContractAndNumbersReport()
    {

        $this->setPageTitle("Service Contracts Ratio");

        $this->setTemplateFiles(
            'ItemsNotYetReceived',
            'ItemsNotYetReceived'
        );

        $itemsNotYetReceived = $this->buItemsNotYetReceived->getItemsNotYetReceived();


        $this->template->set_block(
            'ItemsNotYetReceived',
            'notYetReceivedItemBlock',
            'notYetReceivedItems'
        );

        foreach ($itemsNotYetReceived as $item) {

            $purchaseOrderLink = "/PurchaseOrder.php?action=display&porheadID=" . $item->getPurchaseOrderId();
            $style = "style='color:" . $item->color() . "'";

            $this->template->set_var(
                [
                    "style"             => $style,
                    "purchaseOrderLink" => $purchaseOrderLink,
                    "purchaseOrderId"   => $item->getPurchaseOrderId(),
                    "customerName"      => $item->getCustomerName(),
                    "itemDescription"   => $item->getItemDescription(),
                    "supplierName"      => $item->getSupplierName(),
                    "direct"            => $item->getDirect(),
                    "purchaseOrderDate" => $this->getDateOrNA($item->getPurchaseOrderDate()),
                    "futureDate"        => $this->getDateOrNA($item->getFutureDate()),
                    "requiredByDate"    => $this->getDateOrNA($item->getPurchaseOrderRequiredBy()),
                    "supplierRef"       => $item->getSupplierRef(),
                    "projectName"       => $item->getProjectName(),
                    "dispatchedDate"    => $this->getDateOrNA($item->getDispatchedDate())
                ]
            );

            $this->template->parse(
                'notYetReceivedItems',
                'notYetReceivedItemBlock',
                true
            );
        }


        $this->template->parse(
            'CONTENTS',
            'ItemsNotYetReceived',
            true
        );


        $this->parsePage();
    }

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
?>