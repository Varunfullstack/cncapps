<?php

use CNCLTD\Exceptions\JsonHttpException;
use CNCLTD\FeedbackTokenGenerator;

require_once '../config.inc.php';

if (empty($_REQUEST['serviceRequestId'])) {
    throw new JsonHttpException(400, 'Service Request Id is required');
}

global $db;
$feedbackTokenGenerator = new FeedbackTokenGenerator($db);
echo $feedbackTokenGenerator->getTokenForServiceRequestId($_REQUEST['serviceRequestId']);
