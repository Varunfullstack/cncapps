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
     * @throws Exception
     */
    function defaultAction()
    {
        switch ($this->getAction()) {

            default:
                $this->displayContractAndNumbersReport();
                break;
        }
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

        $itemsNotYetReceived = $this->buItemsNotYetReceived->getItemsNotYetReceived();


        $this->template->set_block(
            'ItemsNotYetReceived',
            'notYetReceivedItemBlock',
            'notYetReceivedItems'
        );

        foreach ($itemsNotYetReceived as $item) {

            $purchaseOrderLink = "/PurchaseOrder.php?action=display&porheadID=" . $item->getPurchaseOrderId();
            $style = "class='" . $item->color() . "'";

            $serviceRequestURL = Controller::buildLink(
                'Activity.php',
                [
                    'action'    => 'displayLastActivity',
                    'problemID' => $item->getServiceRequestID()
                ]
            );

            $serviceRequestLink = "<a href='" . $serviceRequestURL . "' target='_blank'>" . $item->getServiceRequestID(
                ) . "</a>";

            $salesOrderURL =
                Controller::buildLink(
                    "SalesOrder.php",
                    [
                        "action"    => "displaySalesOrder",
                        "ordheadID" => $item->getSalesOrderId()
                    ]
                );

            $salesOrderLink = "<a href='" . $salesOrderURL . "' target='_blank'>" . $item->getSalesOrderId() . "</a>";

            $projectLink = "";

            if ($item->getProjectID()) {

                $projectURL =
                    Controller::buildLink(
                        "Project.php",
                        [
                            "projectID" => $item->getProjectID(),
                            "action"    => "edit"
                        ]

                    );

                $projectLink = "<a href='" . $projectURL . "' target='_blank'>" . $item->getProjectName() . "</a>";
            }


            $this->template->set_var(
                [
                    "style"             => $style,
                    "purchaseOrderLink" => $purchaseOrderLink,
                    "purchaseOrderId"   => $item->getPurchaseOrderId(),
                    "customerName"      => $item->getCustomerName(),
                    "itemDescription"   => $item->getItemDescription(),
                    "supplierName"      => $item->getSupplierName(),
                    "orderedQty"        => $item->getOrderedQuantity(),
                    "direct"            => $item->getDirect(),
                    "purchaseOrderDate" => $this->getDateOrNA($item->getPurchaseOrderDate()),
                    "expectedOn"        => $this->getDateOrNA($item->getExpectedOn()),
                    "futureDate"        => $this->getDateOrNA($item->getFutureDate()),
                    "requiredByDate"    => $this->getDateOrNA($item->getPurchaseOrderRequiredBy()),
                    "supplierRef"       => $item->getSupplierRef(),
                    "color"             => $item->color(),
                    "projectLink"       => $projectLink,
                    "salesOrderLink"    => $salesOrderLink,
                    "SRLink"            => $serviceRequestLink
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
