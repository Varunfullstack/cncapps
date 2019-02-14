<?php
/**
 * Created by PhpStorm.
 * User: fizdalf
 * Date: 14/02/2019
 * Time: 11:26
 */

use PhpOffice\PhpWord\PhpWord;

require_once("config.inc.php");

\PhpOffice\PhpWord\Settings::setOutputEscapingEnabled(true);
$templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor(
    __DIR__ . '/PDF-resources/Client Meeting Notes Template.docx'
);

$templateProcessor->setValue(
    'clientName',
    'Manolo nuevo!'
);
$templateProcessor->setValue(
    'reviewMeetingDate',
    (new DateTime())->format(DATE_RFC1036)
);

$templateProcessor->saveAs('temp.docx');
$output = shell_exec('"c:\Program Files\LibreOffice\program\soffice.exe" --headless --convert-to pdf temp.docx');

echo $output;
exit;
$rendererName = \PhpOffice\PhpWord\Settings::PDF_RENDERER_DOMPDF;
$rendererLibraryPath = realpath(__DIR__ . '/../vendor/dompdf/dompdf');
\PhpOffice\PhpWord\Settings::setPdfRenderer(
    $rendererName,
    $rendererLibraryPath
);
$writer = new \PhpOffice\PhpWord\Writer\PDF($phpWord);
$writer->save('test.pdf');


