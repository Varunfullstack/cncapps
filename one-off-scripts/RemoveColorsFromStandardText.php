<?php
require '../htdocs/config.inc.php';
global $cfg;
require($cfg['path_dbe'] . '/DBEStandardText.inc.php');
$thing         = null;
$standardTexts = new DBEStandardText($thing);
$standardTexts->getRows();
while ($standardTexts->fetchNext()) {
    $toUpdateStandardText = new DBEStandardText($thing);
    $toUpdateStandardText->getRow($standardTexts->getValue(DBEStandardText::stt_standardtextno));
    $text    = $toUpdateStandardText->getValue(DBEStandardText::stt_text);
    $newText = preg_replace('/(<[^>]+) style=".*?"/i', '$1', $text);
    $toUpdateStandardText->setValue(DBEStandardText::stt_text, $newText);
    $toUpdateStandardText->updateRow();
}
