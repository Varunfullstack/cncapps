<?php
/**
 * notify certain CNC users about outstanding support calls
 *
 * called as scheduled task at given time every day
 *
 * @authors Karim Ahmed - Sweet Code Limited
 */

require_once("config.inc.php");
require_once($cfg ["path_bu"] . "/BUMail.inc.php");
require_once($cfg["path_bu"] . '/BUItemsNotYetReceived.php');
$outputToScreen = isset($_GET['toScreen']);
$thing = null;
$buItemsNotYetReceived = new BUItemsNotYetReceived($thing);


$sender_name = "System";
$sender_email = CONFIG_SALES_EMAIL;
$headers = "From: " . $sender_name . " <" . $sender_email . ">\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/html";


$result = $buItemsNotYetReceived->getItemsNotYetReceived();


usort(
    $result,
    function (\CNCLTD\ItemNotYetReceived $a,
              \CNCLTD\ItemNotYetReceived $b
    ) {

        if ($a->getCustomerName() > $b->getCustomerName()) {
            return 1;
        }

        if ($a->getCustomerName() < $b->getCustomerName()) {
            return -1;
        }

        if ($a->getPurchaseOrderRequiredBy() > $b->getPurchaseOrderRequiredBy()) {
            return -1;
        }

        if ($a->getPurchaseOrderRequiredBy() < $b->getPurchaseOrderRequiredBy()) {
            return 1;
        }

        return 0;
    }
);

if (!$outputToScreen) {
    ob_start();
}


?>
    <html lang="uk">
    <head>
        <title>Items not yet received</title>
        <meta http-equiv="Content-Type"
              content="text/html; charset=utf-8"
        />
        <style>
            BODY, P, TD, TH {
                font-family: Arial, Helvetica, sans-serif;
                font-size: 10pt;
            }

            table th {
                text-align: left;
            }

        </style>
    </head>
    <body>
    <P>
        <span style="color:red;background-color: red">R</span> Not ordered yet.
        <span style="color: orange;background-color: orange">A</span> Ordered but not
        received. <span style="color: black;background-color: black">B</span> Ordered and
        received. <span style="color: green;background-color: green">G</span> Entire purchase order received.
    </P>
    <TABLE>
        <thead>
        <TR>
            <th>
                PO
            </th>
            <th>
                SO
            </th>
            <th>
                SR
            </th>
            <th>
                Customer Name
            </th>
            <th>
                Qty
            </th>
            <th>
                Description
            </th>
            <th>
                Supplier
            </th>
            <th>
                Delivered To
            </th>
            <th>
                Ordered On
            </th>
            <th>
                Visit Booked For
            </th>
            <th>
                Required By
            </th>
            <th>
                Supplier Ref
            </th>
            <th>
                Project Name
            </th>
        </TR>
        </thead>
        <tbody>
        <?php
        foreach ($result as $itemNotYetReceived) {

            $style = "style='color:" . $itemNotYetReceived->color() . "'";

            $purchaseOrderURL = "http://cncapps/PurchaseOrder.php?action=display&porheadID=" . $itemNotYetReceived->getPurchaseOrderId(
                );

            $purchaseOrderLink = "<a href='$purchaseOrderURL'>" . $itemNotYetReceived->getPurchaseOrderId() . "</a>";

            $salesOrderURL = "http://cncapps/SalesOrder.php?action=displaySalesOrder&ordheadID=" . $itemNotYetReceived->getSalesOrderId(
                );

            $salesOrderLink = "<a href='" . $salesOrderURL . "'>" . $itemNotYetReceived->getSalesOrderId() . "</a>";

            $projectLink = "";

            if ($itemNotYetReceived->getProjectID()) {

                $projectURL = "http://cncapps/Project.php?projectID=" . $itemNotYetReceived->getProjectID(
                    ) . "&action=edit";
                $projectLink = "<a href='" . $projectURL . "'>" . $itemNotYetReceived->getProjectName() . "</a>";
            }

            $serviceRequestLink = "";
            if ($itemNotYetReceived->getServiceRequestID()) {
                $serviceRequestURL = "http://cncapps/Activity.php?problemID=" . $itemNotYetReceived->getServiceRequestID(
                    ) . "&action=displayLastActivity";
                $serviceRequestLink = "<a href='" . $serviceRequestURL . "'>" . $itemNotYetReceived->getServiceRequestID(
                    ) . "</a>";
            }


            ?>
            <TR <?= $style ?>>
                <TD>
                    <?= $purchaseOrderLink ?>
                </TD>
                <td>
                    <?= $salesOrderLink ?>
                </td>
                <td>
                    <?= $serviceRequestLink ?>
                </td>
                <td>
                    <?= $itemNotYetReceived->getCustomerName() ?>
                </td>
                <td>
                    <?= $itemNotYetReceived->getOrderedQuantity() ?>
                </td>
                <td>
                    <?= $itemNotYetReceived->getItemDescription() ?>
                </td>
                <td>
                    <?= $itemNotYetReceived->getSupplierName() ?>
                </td>
                <td>
                    <?= $itemNotYetReceived->getDirect() ?>
                </td>
                <td>
                    <?= $itemNotYetReceived->getPurchaseOrderDate() ? $itemNotYetReceived->getPurchaseOrderDate(
                    )->format('d/m/Y') : '' ?>
                </td>
                <td>
                    <?= $itemNotYetReceived->getFutureDate() ? $itemNotYetReceived->getFutureDate()->format(
                        'd/m/Y'
                    ) : 'N/A' ?>
                </td>
                <td>
                    <?= ($requiredByDate = $itemNotYetReceived->getPurchaseOrderRequiredBy()) ? $requiredByDate->format(
                        'd/m/Y'
                    ) : 'N/A' ?>
                </td>
                <td>
                    <?= $itemNotYetReceived->getSupplierRef() ?>
                </td>
                <td>
                    <?= $projectLink ?>
                </td>

            </TR>
            <?php
        }
        ?>
        </tbody>
    </TABLE>
    </body>
    </html>
<?php
if (!$outputToScreen) {

    $body = ob_get_contents();
    ob_end_clean();

    $buMail = new BUMail($thing);

    $toEmail = 'unreceivedpo@' . CONFIG_PUBLIC_DOMAIN;

    $hdrs = array(
        'From'         => CONFIG_SALES_EMAIL,
        'To'           => $toEmail,
        'Subject'      => 'Purchase Order Status Report',
        'Date'         => date("r"),
        'Content-Type' => 'text/html; charset=UTF-8'
    );

    $buMail->mime->setHTMLBody($body);

    $mime_params = array(
        'text_encoding' => '7bit',
        'text_charset'  => 'UTF-8',
        'html_charset'  => 'UTF-8',
        'head_charset'  => 'UTF-8'
    );
    $body = $buMail->mime->get($mime_params);

    $hdrs = $buMail->mime->headers($hdrs);

    $buMail->putInQueue(
        CONFIG_SALES_EMAIL,
        $toEmail,
        $hdrs,
        $body
    );
}
?>