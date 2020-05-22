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
            case "getData":
                $daysAgo = null;
                if ($this->getParam('daysAgo')) {
                    $daysAgo = $this->getParam('daysAgo');
                }
                $data = $this->getData($daysAgo);
                echo json_encode($data);
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
//        $daysAgo = 7;
//        if ($this->getParam('daysAgo')) {
//            $daysAgo = $this->getParam('daysAgo');
//        }
//        $itemsNotYetReceived = $this->buItemsNotYetReceived->getItemsNotYetReceived($daysAgo);

//
//        $this->template->set_block(
//            'ItemsNotYetReceived',
//            'notYetReceivedItemBlock',
//            'notYetReceivedItems'
//        );

//        foreach ($itemsNotYetReceived as $item) {
//
//            $this->template->set_var(
//                [
//                    "style"                 => $style,
//                    "purchaseOrderLink"     => $purchaseOrderLink,
//                    "purchaseOrderId"       => $item->getPurchaseOrderId(),
//                    "customerName"          => $item->getCustomerName(),
//                    "itemDescription"       => $item->getItemDescription(),
//                    "supplierName"          => $item->getSupplierName(),
//                    "orderedQty"            => $item->getOrderedQuantity(),
//                    "direct"                => $item->getDirect(),
//                    "purchaseOrderDate"     => $this->getDateOrNA($item->getPurchaseOrderDate()),
//                    "purchaseOrderDateSort" => $item->getPurchaseOrderDate() ? $item->getPurchaseOrderDate()->format(
//                        DATE_MYSQL_DATE
//                    ) : null,
//                    "expectedOn"            => $purchaseOrderLineLink,
//                    "expectedOnSort"        => $item->getExpectedOn() ? $item->getExpectedOn()->format(
//                        DATE_MYSQL_DATE
//                    ) : null,
//                    "futureDate"            => $this->getDateOrNA($item->getFutureDate()),
//                    "futureDateSort"        => $item->getFutureDate() ? $item->getFutureDate()->format(
//                        DATE_MYSQL_DATE
//                    ) : null,
//                    "requiredByDate"        => $item->getPurchaseOrderRequiredBy() ? $item->getPurchaseOrderRequiredBy(
//                    )->format('d/m/Y') : 'TBC',
//                    "requiredByDateSort"    => $item->getPurchaseOrderRequiredBy() ? $item->getPurchaseOrderRequiredBy(
//                    )->format(DATE_MYSQL_DATE) : null,
//                    "requiredByColor"       => $item->getRequiredByColorClass(
//                    ) ? "class='{$item->getRequiredByColorClass()}'" : null,
//                    "supplierRef"           => $item->getSupplierRef(),
//                    "color"                 => $item->color(),
//                    "expectedColor"         => $item->getExpectedColorClass(
//                    ) ? "class='{$item->getExpectedColorClass()}'" : null,
//                    "projectLink"           => $projectLink,
//                    "salesOrderLink"        => $salesOrderLink,
//                    "SRLink"                => $serviceRequestLink
//                ]
//            );
//
//            $this->template->parse(
//                'notYetReceivedItems',
//                'notYetReceivedItemBlock',
//                true
//            );
//        }


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
