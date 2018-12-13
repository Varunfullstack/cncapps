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
$something = null;
$buItemsNotYetReceived = new BUItemsNotYetReceived($something);


$sender_name = "System";
$sender_email = CONFIG_SALES_EMAIL;
$headers = "From: " . $sender_name . " <" . $sender_email . ">\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/html";


$result = $buItemsNotYetReceived->getItemsNotYetReceived();


if (!$outputToScreen) {
    ob_start();
}

?>
    <html>
    <head>
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
        These items have been ordered but have not been received by CNC / the customer.
        In the case of electronic licenses or renewals, these are likely to have gone direct to the customer but may
        still show on the report below.
    </P>
    <TABLE>
        <thead>
        <TR>
            <th>
                Purchase order
            </th>
            <th>
                Customer Name
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
            <th>
                Dispatched Date
            </th>
        </TR>
        </thead>
        <tbody>
        <?php
        foreach ($result as $itemNotYetReceived) {
            $style = "style='color:" . $itemNotYetReceived->color() . "'";
            ?>
            <TR <?= $style ?>>
                <TD>
                    <A href="http://cncapps/PurchaseOrder.php?action=display&porheadID=<?= $itemNotYetReceived->getPurchaseOrderId(
                    ); ?>"
                    ><?= $itemNotYetReceived->getPurchaseOrderId() ?></A>
                </TD>
                <td>
                    <?= $itemNotYetReceived->getCustomerName() ?>
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
                    <?= $requiredByDate ? $requiredByDate->format('d/m/Y') : 'N/A' ?>
                </td>
                <td>
                    <?= $itemNotYetReceived->getSupplierRef() ?>
                </td>
                <td>
                    <?= $itemNotYetReceived->getProjectName() ?>
                </td>
                <td>
                    <?= $itemNotYetReceived->getDispatchedDate() ? $itemNotYetReceived->getDispatchedDate()->format(
                        'd/m/Y'
                    ) : '' ?>
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

    $buMail = new BUMail($this);

    $toEmail = 'unreceivedpo@' . CONFIG_PUBLIC_DOMAIN;

    $hdrs = array(
        'From'         => CONFIG_SALES_EMAIL,
        'To'           => $toEmail,
        'Subject'      => 'Items not yet received',
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