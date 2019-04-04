<?php
/**
 * Created by PhpStorm.
 * User: fizdalf
 * Date: 22/02/2019
 * Time: 11:46
 */


require_once("config.inc.php");
require_once($cfg["path_dbe"] . "/DBECustomer.inc.php");
require_once($cfg["path_dbe"] . "/DBEIgnoredADDomain.inc.php");
require_once($cfg["path_bu"] . "/BUMail.inc.php");
global $db;
$thing = null;
$dbeCustomer = new DBECustomer($thing);
$dbeCustomer->getActiveCustomers(true);
$customerIDs = [];
$fakeTable = "";


while ($dbeCustomer->fetchNext()) {
    if (!$dbeCustomer->getValue(DBECustomer::activeDirectoryName)) {
        continue;
    }
    if ($fakeTable) {
        $fakeTable .= " union all ";
    }
    $fakeTable .= " select " . $dbeCustomer->getValue(
            DBECustomer::customerID
        ) . " as customerID,  '" . $dbeCustomer->getValue(DBECustomer::activeDirectoryName) . "' as domainName";
}

if (!$fakeTable) {
    return;
}
//we are going to use this to add to the monitoring db
$dsn = 'mysql:host=' . LABTECH_DB_HOST . ';dbname=' . LABTECH_DB_NAME;
$options = [
    PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'
];
$labtechDB = new PDO(
    $dsn,
    LABTECH_DB_USERNAME,
    LABTECH_DB_PASSWORD,
    $options
);

$query = "select * from (SELECT 
  fakeTable.customerID, 
  clients.`Name` as customerName,
  computers.`Name` as computerName,
  fakeTable.domainName as expectedDomain,
  replace(computers.`Domain`, 'DC:', '') as reportedDomain
FROM
  ($fakeTable) fakeTable 
  LEFT JOIN clients 
    ON clients.`ExternalID` = fakeTable.customerID 
  LEFT JOIN computers 
    ON computers.`ClientID` = clients.`ClientID` 
    AND lower(REPLACE(computers.`Domain`, 'DC:', '')) <> lower(fakeTable.domainName)
  ) 
  result  
";

$dbeIgnoredADDomains = new DBEIgnoredADDomain($thing);

$dbeIgnoredADDomains->getRows(DBEIgnoredADDomain::customerID);
$ignoredWhere = "where result.reportedDomain is not null and result.reportedDomain <> '' ";
while ($dbeIgnoredADDomains->fetchNext()) {
    $ignoredWhere .= " and ";
    if ($dbeIgnoredADDomains->getValue(DBEIgnoredADDomain::customerID)) {
        $ignoredWhere .= " NOT (result.customerID = " . $dbeIgnoredADDomains->getValue(
                DBEIgnoredADDomain::customerID
            ) . " and result.reportedDomain = '" . $dbeIgnoredADDomains->getValue(DBEIgnoredADDomain::domain) . "') ";
    } else {
        $ignoredWhere .= " result.reportedDomain <> '" . $dbeIgnoredADDomains->getValue(
                DBEIgnoredADDomain::domain
            ) . "' ";
    }
}
$query .= $ignoredWhere;

$stm = $labtechDB->prepare($query);
$stm->execute([]);

$data = $stm->fetchAll();

$onScreen = isset($_GET['onScreen']);

if (!count($data)) {
    echo 'All clear';
    exit;
}


$template = new Template (
    EMAIL_TEMPLATE_DIR,
    "remove"
);

$template->set_file(
    'DomainVerificationEmail.html',
    'DomainVerificationEmail.html'
);

$template->setBlock(
    'DomainVerificationEmail.html',
    'computerBlock',
    'theseAreTheComputers'
);

foreach ($data as $datum) {
    $template->setVar(
        [
            "customerName"   => $datum['customerName'],
            "computerName"   => $datum['computerName'],
            "reportedDomain" => $datum['reportedDomain'],
            "expectedDomain" => $datum['expectedDomain'],
        ]
    );

    $template->parse(
        'theseAreTheComputers',
        'computerBlock',
        true
    );

}
$template->parse(
    'output',
    'DomainVerificationEmail.html',
    true
);
$body = $template->get_var('output');

echo $body;

$buMail = new BUMail($thing);

$buMail->mime->setHTMLBody($body);
$subject = "CWA Agents with mismatching domains";
$mime_params = array(
    'text_encoding' => '7bit',
    'text_charset'  => 'UTF-8',
    'html_charset'  => 'UTF-8',
    'head_charset'  => 'UTF-8'
);
$body = $buMail->mime->get($mime_params);
$toEmail = "cwaclientlocationcheck@cnc-ltd.co.uk";
$hdrs = array(
    'To'           => $toEmail,
    'From'         => CONFIG_SUPPORT_EMAIL,
    'Subject'      => $subject,
    'Content-Type' => 'text/html; charset=UTF-8'
);

$hdrs = $buMail->mime->headers($hdrs);

$sent = $buMail->putInQueue(
    CONFIG_SUPPORT_EMAIL,
    $toEmail,
    $hdrs,
    $body,
    true
);