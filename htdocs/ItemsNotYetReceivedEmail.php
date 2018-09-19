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


$outputToScreen = isset($_GET['toScreen']);

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
  customer.`cus_name`,
  item.`itm_desc`,
  supplier.`sup_name`,
  IF(
    poh_direct_del = 'N',
    'CNC',
    'Direct'
  ) AS direct,
  poh_ord_date,
  (SELECT 
    MIN(ca.caa_date) 
  FROM
    callactivity ca 
    LEFT JOIN callacttype cat 
      ON cat.cat_callacttypeno = ca.caa_callacttypeno 
  WHERE ca.caa_problemno = problem.`pro_problemno` 
    AND ca.caa_date >= NOW()) AS futureDate,
    poh_required_by
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
  LEFT JOIN problem 
    ON problem.`pro_linked_ordno` = porhead.`poh_ordno` 
WHERE pol_qty_ord <> pol_qty_rec 
  AND (poh_type = 'I' 
    OR poh_type = 'P') 
  AND poh_ord_consno IS NOT NULL 
  AND poh_ord_consno <> 0 
  and poh_required_by is not null and poh_required_by <> '0000-00-00'
  order by poh_required_by asc 
";


$result = $db1->query($query);

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
        </TR>
        </thead>
        <tbody>
        <?php
        while ($i = $result->fetch_row()) {
            $style = "";
            if ($i[7]) {
                $startDate = new DateTime();
                $requiredByDate = DateTime::createFromFormat(
                    'Y-m-d',
                    $i[7]
                );
                $diff = $startDate->diff($requiredByDate);
                if ((int)$diff->format('%a') < 7) {
                    $style = "style='color:red'";
                }
            }
            ?>
            <TR <?= $style ?>>
                <TD>
                    <A href="http://cncapps/PurchaseOrder.php?action=display&porheadID=<?php print $i[0] ?>" ><?php print $i[0] ?></A>
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
                <td>
                    <?= $i[4] ?>
                </td>
                <td>
                    <?= (new DateTime($i[5]))->format('d/m/Y') ?>
                </td>
                <td>
                    <?= $i[6] ? (new DateTime($i[6]))->format('d/m/Y') : 'N/A' ?>
                </td>
                <td>
                    <?= $requiredByDate ? $requiredByDate->format('d/m/Y') : 'N/A' ?>
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
    $result->free();

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