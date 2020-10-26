<?php

require_once '../config.inc.php';

if (empty($_REQUEST['serviceRequestId'])) {
    throw new \CNCLTD\Exceptions\JsonHttpException(400, 'Service Request Id is required');
}

global $db;
$feedbackTokenGenerator = new \CNCLTD\FeedbackTokenGenerator($db);
echo $feedbackTokenGenerator->getTokenForServiceRequestId($_REQUEST['serviceRequestId']);
