<?php

require_once __DIR__ . '/../../vendor/autoload.php';




$output = shell_exec(
    "powershell.exe -executionpolicy bypass -NoProfile -command E:\Temp\o365export.ps1 -User 'admin@stephenrimmer.onmicrosoft.com' -Password 'C4j0l3dUNfl4nk$#1'"
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
