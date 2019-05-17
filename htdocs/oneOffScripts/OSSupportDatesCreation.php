<?php

require_once("../config.inc.php");
require_once($cfg['path_dbe'] . '/DBEOSSupportDates.php');

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
];

function findDatesForOS($osName, $datesData)
{
    foreach ($datesData as $datesDatum) {
        if (strpos($datesDatum['name'], $osName) == -1) {
            continue;
        }

        return $datesDatum;
    }
    return null;
}

foreach ($labtechData as $labtechDatum) {

    $foundDate = findDatesForOS($labtechDatum['name'], $datesData);
    if (!$foundDate) {
        continue;
    }

    $dbeOSSupportDates = new DBEOSSupportDates($this);
    $dbeOSSupportDates->setValue(DBEOSSupportDates::name, $labtechDatum['name']);
    $dbeOSSupportDates->setValue(DBEOSSupportDates::version, $labtechDatum['version']);
    $dbeOSSupportDates->setValue(DBEOSSupportDates::availabilityDate, $foundDate['availabilityDate']);
    $dbeOSSupportDates->setValue(DBEOSSupportDates::endOfLifeDate, $foundDate['endOfLifeDate']);
    $dbeOSSupportDates->insertRow();

}