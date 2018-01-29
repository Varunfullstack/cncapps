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
$query =
    'SELECT poh_porno' .
    ' FROM porhead' .
    ' WHERE poh_ord_consno = 0';            // not ordered

$result = $db1->query($query);

if (!OUTPUT_TO_SCREEN) {
    ob_start();
}

?>
    <P>
        The following purchase orders are unprinted:
    </P>
    <TABLE>
        <TR>
            <TD>
                Purchase order
            </TD>
        </TR>
        <?php
        while ($i = $result->fetch_row()) {
            ?>
            <TR>
                <TD>
                    <A href="http://cncapps/PurchaseOrder.php?action=display&porheadID=<?php print $i[0] ?>"><?php print $i[0] ?></A>
                </TD>
            </TR>
            <?php
        }
        ?>
    </TABLE>
<?php
if (!OUTPUT_TO_SCREEN) {

    $body = ob_get_contents();
    ob_end_clean();
    $result->free();

    $buMail = new BUMail($this);

    $toEmail = 'unprintedpo@' . CONFIG_PUBLIC_DOMAIN;

    $hdrs = array(
        'From' => CONFIG_SALES_EMAIL,
        'To' => $toEmail,
        'Subject' => 'Unprinted Purchase Orders',
        'Date' => date("r"),
        'Content-Type' => 'text/html; charset=UTF-8'
    );

    $buMail->mime->setHTMLBody($body);

    $body = $buMail->mime->get();

    $hdrs = $buMail->mime->headers($hdrs);

    $buMail->putInQueue(
        CONFIG_SALES_EMAIL,
        $toEmail,
        $hdrs,
        $body
    );
}
?>