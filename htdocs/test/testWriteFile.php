<?php

require_once '../config.inc.php';

$twig->render(
    "@internal/emailAlmostFullAlertEmail.html.twig",
    [
        "contactFirstName" => "test",
        "mailboxes"        => []
    ]
);