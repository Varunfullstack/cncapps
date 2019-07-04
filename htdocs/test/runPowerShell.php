<?php

require_once __DIR__ . '/../../vendor/autoload.php';



function replaceLicenseName($licenseName){
    switch ($licenseName){
        case "O365_BUSINESS_PREMIUM":
        case "ATP_ENTERPRISE":
            return "Business Premium ATP";
            case
    }

-reseller-account:ATP_ENTERPRISE reseller-account:O365_BUSINESS_ESSENTIALS", Replacement:="Business Essentials ATP"
    -reseller-account:ENTERPRISEPACK reseller-account:ATP_ENTERPRISE", Replacement:="Business Enterprise ATP"
-reseller-account:ENTERPRISEPACK reseller-account:ATP_ENTERPRISE reseller-account:POWER_BI_STANDARD reseller-account:POWERAPPS_INDIVIDUAL_USER", Replacement:="Business Enterprise ATP"
    -reseller-account:O365_BUSINESS_PREMIUM reseller-account:ATP_ENTERPRISE", Replacement:="Business Enterprise ATP"
-reseller-account:ATP_ENTERPRISE reseller-account:Business Premium", Replacement:="Business Premium ATP"
    -Business Enterprise ATP reseller-account:POWER_BI_STANDARD reseller-account:POWERAPPS_INDIVIDUAL_USER", Replacement:="Business Enterprise ATP"
-SMB_BUSINESS_ESSENTIALS", Replacement:="Business Essentials"
    -O365_BUSINESS_ESSENTIALS", Replacement:="Business Essentials"
-SMB_BUSINESS_PREMIUM", Replacement:="Business Premium"
    -O365_BUSINESS_PREMIUM", Replacement:="Business Premium"
-STANDARDPACK", Replacement:="Exchange Online"
    -EXCHANGESTANDARD", Replacement:="Exchange Online"
-STANDARDPACK", Replacement:="Enterprise E1"
    -EXCHANGESTANDARD", Replacement:="Enterprise E1"
-STANDARDWOFFPACK", Replacement:="Enterprise E2"
    -ENTERPRISEPACK", Replacement:="Enterprise E3"
-ATP_ENTERPRISE", Replacement:="ATP"
    -POWER_BI_STANDARD", Replacement:="Power Bi"
-POWER_BI_PRO", Replacement:="Power Bi"
}

$output = shell_exec(
    "powershell.exe -executionpolicy bypass -NoProfile -command E:\Temp\o365export.ps1 -User 'cnc365admin@AdvancedMachiningT.onmicrosoft.com' -Password 'l0p533d$'"
);

$data = (array)json_decode($output);

// we are going to build an array from the data
$doneHeaders = false;
$headers = [];
$dataArray = [];
foreach ($data as $datum) {
    $values = [];
    foreach ($datum as $key => $value) {
        if (!$doneHeaders) {
            $headers[] = $key;
        }

        if($key == "Licenses"){
          foreach ($value as $licenseName){

          }
        }

        $values[] = $value;
    }
    if (!$doneHeaders) {
        $dataArray[] = $headers;
        $doneHeaders = true;
    }
    $dataArray[] = $values;
}

var_dump($dataArray);
exit;

$spreadSheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();


$writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadSheet);
$spreadSheet->getActiveSheet()->fromArray($dataArray);

$writer->save('e:\\Temp\\test.xlsx');
