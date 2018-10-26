<?php

$contents = file_get_contents("htdocs/email_templates/DirectDebitInvoiceEmail.html");

if (preg_match(
    '/[^\x00-\x7F]/',
    $contents,
    $matches
)) {
    var_dump($matches);
} else {
    echo 'no match';
}