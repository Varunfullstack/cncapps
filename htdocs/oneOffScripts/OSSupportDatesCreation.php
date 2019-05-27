<?php

require_once("../config.inc.php");
require_once($cfg['path_dbe'] . '/DBEOSSupportDates.php');
$thing = null;
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

$labtechQuery = $labtechDB->query(
    'SELECT computers.`OS` as name, computers.`Version` as version  FROM computers GROUP BY OS, VERSION '
);

$labtechData = $labtechQuery->fetchAll(PDO::FETCH_ASSOC);

$datesData = [
    ["name" => "Microsoft Windows 7", "availabilityDate" => "22/02/2011", "endOfLifeDate" => "12/01/2020"],
    ["name" => "Microsoft Windows Server 2003 R2", "availabilityDate" => "06/12/2005", "endOfLifeDate" => "24/04/2015"],
    ["name" => "Microsoft Windows Server 2003", "availabilityDate" => "24/04/2003", "endOfLifeDate" => "24/04/2015"],
    [
        "name"             => "Windows Small Business Server 2003",
        "availabilityDate" => "09/10/2003",
        "endOfLifeDate"    => "24/04/2015"
    ],
    ["name" => "Microsoft Windows Server 2008 R2", "availabilityDate" => "22/10/2009", "endOfLifeDate" => "14/01/2020"],
    ["name" => "Microsoft Windows Server 2008", "availabilityDate" => "27/02/2008", "endOfLifeDate" => "14/01/2020"],
    ["name" => "Microsoft® Windows Server® 2008", "availabilityDate" => "27/02/2008", "endOfLifeDate" => "14/01/2020"],
    [
        "name"             => "Microsoft Windows® Small Business Server 2011",
        "availabilityDate" => "13/12/2010",
        "endOfLifeDate"    => "14/01/2020"
    ],
    ["name" => "Microsoft Windows 8.1", "availabilityDate" => "27/08/2013", "endOfLifeDate" => "10/01/2023"],
    ["name" => "Microsoft Windows 8", "availabilityDate" => "25/10/2012", "endOfLifeDate" => "10/01/2023"],
    ["name" => "Microsoft Windows XP", "availabilityDate" => "24/08/2001", "endOfLifeDate" => "08/04/2014"],
    ["name" => "Microsoft® Windows Vista", "availabilityDate" => "08/11/2006", "endOfLifeDate" => "11/04/2017"],
    ["name" => "Microsoft Windows Server 2012", "availabilityDate" => "30/10/2012", "endOfLifeDate" => "10/10/2023"],
    ["name" => "Microsoft Windows Server 2012 R2", "availabilityDate" => "25/11/2013", "endOfLifeDate" => "10/10/2023"],
    ["name" => "Microsoft Windows Server 2016", "availabilityDate" => "15/10/2016", "endOfLifeDate" => "12/01/2027"],
    ["name" => "Microsoft Windows Server 2019", "availabilityDate" => "13/11/2018", "endOfLifeDate" => "09/01/2029"],

];

function findDatesForOS($osName, $datesData)
{
    foreach ($datesData as $datesDatum) {
        if (strpos($osName, $datesDatum['name']) === FALSE) {
            continue;
        }

        return $datesDatum;
    }
    return null;
}

foreach ($labtechData as $labtechDatum) {
    echo '<div>';
    echo 'looking at: ' . $labtechDatum['name'];
    echo '</div>';
    $foundDate = findDatesForOS($labtechDatum['name'], $datesData);

    if (!$foundDate) {
        echo '<div>';
        echo 'No Date found for this OS';
        echo '</div>';
        continue;
    }
    echo '<div>';
    echo 'We have found dates for this OS ';
    echo '<pre>';
    var_dump($foundDate);
    echo '</pre>';
    echo '</div>';

    $availabilityDate = (DateTime::createFromFormat('d/m/Y', $foundDate['availabilityDate']))->format('Y-m-d');
    $endOfLifeDate = (DateTime::createFromFormat('d/m/Y', $foundDate['endOfLifeDate']))->format('Y-m-d');
    $version = null;
    if (preg_match('/^\d+\.\d+\.\d+/', $labtechDatum['version'], $matches)) {
        $version = $matches[0];
    }

    if (!$version) {
        continue;
    }

    $dbeOSSupportDates = new DBEOSSupportDates($thing);
    $dbeOSSupportDates->setValue(DBEOSSupportDates::name, $labtechDatum['name']);
    $dbeOSSupportDates->setValue(DBEOSSupportDates::version, $version);
    $dbeOSSupportDates->setValue(DBEOSSupportDates::availabilityDate, $availabilityDate);
    $dbeOSSupportDates->setValue(DBEOSSupportDates::endOfLifeDate, $endOfLifeDate);
    $dbeOSSupportDates->insertRow();

}