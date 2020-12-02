<?php
require_once __DIR__ . '/../htdocs/config.inc.php';
global $db;
$fetchServiceRequestsToChange = '
select problem.pro_problemno as serviceRequestId, callactivity.reason as initialReason  from
                problem
                    LEFT JOIN callactivity
                    ON callactivity.`caa_problemno` = problem.`pro_problemno`
                        AND callactivity.`caa_callacttypeno` = 51
            WHERE problem.`pro_status` IN ("I", "P", "F")
';
$db->query($fetchServiceRequestsToChange);
$serviceRequestsToUpdate = $db->fetchAll();
foreach ($serviceRequestsToUpdate as $serviceRequestToUpdate) {
    $db->preparedQuery(
        "update problem set emailSubjectSummary = ? where problem.pro_problemno = ?",
        [
            [
                "type"  => "s",
                "value" => substr(strip_tags($serviceRequestsToUpdate['initialReason']), 0, 50)
            ],
            [
                "type"  => "i",
                "value" => $serviceRequestsToUpdate['serviceRequestId']
            ]
        ]
    );
}