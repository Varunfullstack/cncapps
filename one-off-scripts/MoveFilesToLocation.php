<?php
require __DIR__ . '/../htdocs/config.inc.php';
global $cfg;
function moveFilesFromDirToDir($srcDir, $destinationDir)
{
    if (!file_exists($destinationDir)) {
        mkdir($destinationDir, 0777, true);
    }
    if (!is_dir($destinationDir)) {
        throw new Exception("Destination directory {$destinationDir} is not a directory!");
    }
    if (!is_writable($destinationDir)) {
        throw new Exception("Destination directory {$destinationDir} is not a writable!");
    }
    if (!($handle = opendir($srcDir))) {
        return;
    }
    while (false !== ($file = readdir($handle))) {
        if (in_array($file, [".", ".."])) {
            continue;
        }
        $fullFilePath = $srcDir . DIRECTORY_SEPARATOR . $file;
        if (is_file($fullFilePath)) {
            rename($fullFilePath, $destinationDir . DIRECTORY_SEPARATOR . $file);
        } elseif (is_dir($fullFilePath)) {
            $sourceSubfolder = $fullFilePath . DIRECTORY_SEPARATOR;
            moveFilesFromDirToDir(
                $sourceSubfolder,
                $destinationDir . DIRECTORY_SEPARATOR . $file . DIRECTORY_SEPARATOR
            );
        }
    }
    rmdir($srcDir);
    closedir($handle);
}

$oldReceiptPath = BASE_DRIVE . '/receipts/';
moveFilesFromDirToDir($oldReceiptPath, RECEIPT_PATH);
$oldQuotesPath = BASE_DRIVE . "/htdocs/quotes/";
moveFilesFromDirToDir($oldQuotesPath, QUOTES_DIR);
$oldPdfTemp = BASE_DRIVE . "/htdocs/pdfTemp/";
moveFilesFromDirToDir($oldQuotesPath, PDF_TEMP_DIR);
$oldServiceRequestDocumentsPath = BASE_DRIVE . '/serviceRequestsDocuments/';
moveFilesFromDirToDir($oldServiceRequestDocumentsPath, INTERNAL_DOCUMENTS_FOLDER);
$oldDeliveryNotesFolder = BASE_DRIVE . "/htdocs/delivery_notes";
moveFilesFromDirToDir($oldDeliveryNotesFolder, DELIVERY_NOTES_DIR);