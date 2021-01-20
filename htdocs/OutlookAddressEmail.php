<?php
/**
 * Email with Outlook address file
 *
 * called as scheduled task at given time
 *
 * @authors Karim Ahmed - Sweet Code Limited
 */

require_once("config.inc.php");

require_once($cfg["path_bu"] . "/BUMail.inc.php");

$thing = null;
define('EMAIL_FROM_USER', 'sales@' . CONFIG_PUBLIC_DOMAIN);
define('EMAIL_SUBJECT', 'Outlook address file');
define('FORMAT_MYSQL_UK_DATE', '%e/%c/%Y');
define('FORMAT_MYSQL_UK_DATETIME', '%e/%c/%Y %h:%m');

if (!$db2 = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD)) {
    echo 'Could not connect to mysql host ' . DB_HOST;
    exit;
}
mysqli_select_db($db2, DB_NAME);
$query =
    '(SELECT
	con_title AS Title,
	con_last_name AS LastName,
	con_first_name AS FirstName,
	cus_name AS Company,
	add_add1 AS BusinessStreet,
	add_add2 AS BusinessStreet2,
	add_add3  AS BusinessStreet3,
	add_town AS BusinessCity,
	add_county AS BusinessState,
	add_postcode AS BusinessPostalCode,
	add_phone AS BusinessPhone,
	con_phone AS BusinessPhone2,
	con_mobile_phone As `Mobile Phone`,
	con_fax AS BusinessFax,
	con_email AS `E-mail Address`,
	"Customer" AS Categories,
	concat(con_first_name," ",con_last_name) AS `E-mail Display Name`
FROM contact
JOIN address ON
	(con_siteno = add_siteno	AND con_custno = add_custno)
JOIN customer ON
	con_custno = cus_custno
WHERE
	con_mailflag1 =  "Y" AND
	con_custno <> 0
)
UNION
(SELECT
	con_title AS Title,
	con_last_name AS LastName,
	con_first_name AS FirstName,
	sup_name AS Company,
	sup_add1 AS BusinessStreet,
	sup_add2 AS BusinessStreet2,
	"" AS BusinessStreet3,
	sup_town AS BusinessCity,
	sup_county AS BusinessState,
	sup_postcode AS BusinessPostalCode,
	sup_phone AS BusinessPhone,
	con_phone AS BusinessPhone2,
	con_mobile_phone AS `Mobile Phone`,
	con_fax AS BusinessFax,
	con_email AS `E-mail Address`,
	"Supplier" AS Categories,
	concat( con_first_name," ",con_last_name) AS `E-mail Display Name`
FROM contact
JOIN supplier ON
	con_suppno = sup_suppno
WHERE
	mailshot AND
	con_suppno <> 0
)
ORDER BY Company, LastName';
if (!$result = mysqli_query($db2, $query)) {
    echo mysqli_error($db2);
}

ob_start()
?>
    <HTML>
    <style type="text/css">
        <!--
        .style1 {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 9px;
        }

        -->
    </style>
    <BODY class="style1">
    <P>Outlook import file attached</P>
    </BODY>
    </HTML>
<?php

$html = ob_get_contents();
ob_end_clean();

$crlf = "\n";

/*
Build CSV attachment
*/
$result = mysqli_query($db2, $query);

$numfields = mysqli_num_fields($result);

$csv_attachment = '';

for ($i = 0; $i < $numfields; $i++) {
    $csv_attachment .= mysqli_fetch_field_direct($result, $i)->name . ',';
}

$csv_attachment = substr($csv_attachment, 0, strlen($csv_attachment) - 1) . $crlf;

while ($row = mysqli_fetch_assoc($result)) // Data
{
    $row_string = '';

    foreach ($row as $key => $column) {

        $column = trim($column);

        $row_string .= '"';
        /*
        Ensure phone numbers have a space
        */
        if (
            (
                strpos($key, 'phone') !== false or
                strpos($key, 'Phone') !== false or
                strpos($key, 'fax') !== false or
                strpos($key, 'Fax') !== false
            )
            && strpos($column, ' ') === false
        ) {
            /*
            Split after 2nd
            */
            if (strpos($column, '01') == 0 or strpos($column, '07') == 0) {
                $column = substr($column, 0, 1) . ' ' . substr($column, 2, strlen($column) - 1);

            } else {
                /*
                Split after 3rd
                */
                $column = substr($column, 0, 2) . ' ' . substr($column, 3, strlen($column) - 1);
            }

        }
        $row_string .= $column . '",';

    }

    $csv_attachment .= substr($row_string, 0, strlen($row_string) - 1) . $crlf;

}

$buMail = new BUMail($thing);

$buMail->mime->setHTMLBody($html);

$buMail->mime->addAttachment($csv_attachment, 'text/csv', 'addresses.csv', false);

$mime_params = array(
    'text_encoding' => '7bit',
    'text_charset'  => 'UTF-8',
    'html_charset'  => 'UTF-8',
    'head_charset'  => 'UTF-8'
);
$body = $buMail->mime->get($mime_params);

$toEmail = CONFIG_SALES_EMAIL;

$hdrs = array(
    'To'           => $toEmail,
    'From'         => CONFIG_SALES_EMAIL,
    'Subject'      => 'Outlook Address File',
    'Content-Type' => 'text/html; charset=UTF-8'
);

$hdrs = $buMail->mime->headers($hdrs);

$result = $buMail->putInQueue(
    CONFIG_SALES_EMAIL,
    $toEmail,
    $hdrs,
    $body
);
header('Location:/index.php');
?>