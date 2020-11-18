<?php
use CNCLTD\LoggerCLI;
require_once(__DIR__ . "/../htdocs/config.inc.php");
global $cfg;

$logName = 'UnprintedPurchaseOrdersEmail';
$logger = new LoggerCLI($logName);

// increasing execution time to infinity...
ini_set('max_execution_time', 0);

if (!is_cli()) {
    echo 'This script can only be ran from command line';
    exit;
}
// Script example.php
$shortopts = "d";
$longopts = [];
$options = getopt($shortopts, $longopts);
$debugMode = false;
if (isset($options['d'])) {
    $debugMode = true;
}
require_once($cfg ["path_bu"] . "/BUMail.inc.php");

$thing = null;
define(
    'OUTPUT_TO_SCREEN',
    false
);

if (!$db1 = mysqli_connect(
    DB_HOST,
    DB_USER,
    DB_PASSWORD
)) {
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
  customer.cus_name,
  supplier.`sup_name`
FROM
  porhead 
  LEFT JOIN ordhead 
    ON porhead.`poh_ordno` = ordhead.`odh_ordno` 
  LEFT JOIN customer 
    ON ordhead.`odh_custno` = customer.`cus_custno` 
    LEFT JOIN supplier 
    ON supplier.`sup_suppno` = porhead.`poh_suppno`
WHERE poh_ord_consno is null ";


$result = $db1->query($query);

if (!OUTPUT_TO_SCREEN) {
    ob_start();
}

?>
    <P>
        The following purchase orders are have not been ordered:
    </P>
    <TABLE>
        <thead>
        <tr>
            <th>
                Purchase order
            </th>
            <th>
                Customer
            </th>
            <th>
                Supplier
            </th>
        </tr>
        </thead>
        <?php
        while ($i = $result->fetch_row()) {
            ?>
            <TR>
                <TD>
                    <A href="<?= SITE_URL ?>/PurchaseOrder.php?action=display&porheadID=<?php print $i[0] ?>"><?php print $i[0] ?></A>
                </TD>
                <td>
                    <?= $i[1] ?>
                </td>
                <td>
                    <?= $i[2] ?>
                </td>
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

    $buMail = new BUMail($thing);

    $toEmail = 'unprintedpo@' . CONFIG_PUBLIC_DOMAIN;

    $hdrs = array(
        'From'         => CONFIG_SALES_EMAIL,
        'To'           => $toEmail,
        'Subject'      => 'Unprinted Purchase Orders',
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