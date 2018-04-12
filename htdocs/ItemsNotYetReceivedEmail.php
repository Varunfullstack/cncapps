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


define('OUTPUT_TO_SCREEN', false);

if (!$db1 = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD)) {
    echo 'Could not connect to mysql host ' . DB_HOST;
    exit;
}
$db1->select_db(DB_NAME);


$sender_name = "System";
$sender_email = CONFIG_SALES_EMAIL;
$headers = "From: " . $sender_name . " <" . $sender_email . ">\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/html";

/*
Unprinted purchase orders email to Gary
*/
$query = "SELECT 
  porhead.`poh_porno`,
  customer.`cus_name`,
  item.`itm_desc`,
  supplier.`sup_name` 
FROM
  porline 
  LEFT JOIN porhead 
    ON porline.pol_porno = porhead.`poh_porno` 
  LEFT JOIN item 
    ON item.`itm_itemno` = porline.`pol_itemno` 
  LEFT JOIN ordhead 
    ON porhead.`poh_ordno` = ordhead.`odh_ordno` 
  LEFT JOIN customer 
    ON ordhead.`odh_custno` = customer.`cus_custno` 
  LEFT JOIN supplier 
    ON supplier.`sup_suppno` = porhead.`poh_suppno` 
WHERE pol_qty_ord <> pol_qty_rec
AND (poh_type = 'I'
    OR poh_type = 'P')
AND (
poh_ord_consno IS NOT NULL
OR poh_ord_consno <> 0
  ) 
  AND item.`itm_itemno` <> 1491
AND item.`itm_desc` NOT LIKE '%labour%'";


$result = $db1->query($query);

if (!OUTPUT_TO_SCREEN) {
    ob_start();
}

?>
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
        </TR>
        </thead>
        <tbody>
        <?php
        while ($i = $result->fetch_row()) {
            ?>
            <TR>
                <TD>
                    <A href="http://cncapps/PurchaseOrder.php?action=display&porheadID=<?php print $i[0] ?>"><?php print $i[0] ?></A>
                </TD>
                <td>
                    <?= $i[1] ?>
                </td>
                <td>
                    <?= $i[2] ?>
                </td>
                <td>
                    <?= $i[3] ?>
                </td>
            </TR>
            <?php
        }
        ?>
        </tbody>
    </TABLE>
<?php
if (!OUTPUT_TO_SCREEN) {

    $body = ob_get_contents();
    ob_end_clean();
    $result->free();

    $buMail = new BUMail($this);

    $toEmail = 'unreceivedpo@' . CONFIG_PUBLIC_DOMAIN;

    $hdrs = array(
        'From' => CONFIG_SALES_EMAIL,
        'To' => $toEmail,
        'Subject' => 'Ordered items not yet received',
        'Date' => date("r"),
        'Content-Type' => 'text/html; charset=UTF-8'
    );

    $buMail->mime->setHTMLBody($body);

    $mime_params = array(
        'text_encoding' => '7bit',
        'text_charset' => 'UTF-8',
        'html_charset' => 'UTF-8',
        'head_charset' => 'UTF-8'
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