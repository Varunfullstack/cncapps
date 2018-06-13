<?php
/**
 * Created by PhpStorm.
 * User: fizdalf
 * Date: 13/06/2018
 * Time: 8:40
 */

require_once("config.inc.php");

function processEmail()
{
    if (!isset($_POST['form'])) {
        return ["valid" => true];
    }

    $firstElement = reset($_POST['form']);
    $contactID = key($firstElement);
    $firstElement = reset($firstElement);
    if ($firstElement['email'] == "") {
        return ["valid" => true];
    }

    $db = new dbSweetcode();        // select from callactivity/call

    $query = "select count(con_contno) as count from contact where con_email = ? and con_contno <> ?";

    $parameters = [
        [
            'type'  => 's',
            'value' => $firstElement['email']
        ],
        [
            'type'  => 'd',
            'value' => $contactID
        ],
    ];

    $result = $db->preparedQuery($query, $parameters);
    $data = $result->fetch_assoc();
    if ($data['count'] > 0) {
        return ["valid" => false];
    }

    return ["valid" => true];
}

$result = processEmail();

echo json_encode($result);