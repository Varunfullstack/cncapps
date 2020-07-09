<?php
/**
 * Created by PhpStorm.
 * User: fizdalf
 * Date: 22/08/2018
 * Time: 10:39
 */

use Twig\Environment;

global $cfg;
require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_bu'] . '/BUActivity.inc.php');
require_once($cfg['path_bu'] . '/BUExpense.inc.php');
require_once($cfg ["path_dbe"] . "/DBEJCallActivity.php");

class CTExpenseDashboard extends CTCNC
{
    function __construct($requestMethod,
                         $postVars,
                         $getVars,
                         $cookieVars,
                         $cfg
    )
    {
        parent::__construct(
            $requestMethod,
            $postVars,
            $getVars,
            $cookieVars,
            $cfg
        );
        $this->setMenuId(1001);
    }

    /**
     * Route to function based upon action passed
     * @throws Exception
     */
    function defaultAction()
    {

        switch ($this->getAction()) {
            case "getExpensesData" :
                // we have to retrieve the data for the user + if the user is someones approver

                $queryString = 'SELECT
  expense.`exp_expenseno` AS id,
  CONCAT(
    consultant.`firstName`,
    " ",
    consultant.`lastName`
  ) AS staffName,
       
  consultant.`cns_consno` AS userId,
  exp_callactivityno AS activityId,
  callactivity.`caa_problemno` AS serviceRequestId,
  caa_date as `dateSubmitted`,
  expensetype.`ext_desc` AS expenseTypeDescription,
  expense.`exp_expensetypeno` AS expenseTypeId,
  expense.`exp_value` AS `value`,
  project.`description` AS projectDescription,
  project.`projectID` AS projectId,
  expense.approvedDate,
       customer.cus_name as customerName,
  CONCAT(
    approver.`firstName`,
    " ",
    approver.`lastName`
  ) AS approverName,
  IF(
    expense.`approvedBy` is not null,
    "Approved",
    IF(
      expense.`deniedReason` is not null,
      "Denied",
      "Pending"
    )
  ) AS status,
       callactivity.caa_consno = ? as isSelf,
       receipt.id as receiptId,
       expensetype.receiptRequired,
         ((SELECT
        1
      FROM
        consultant globalApprovers
      WHERE globalApprovers.globalExpenseApprover
        AND globalApprovers.cns_consno = ?) = 1 or consultant.`expenseApproverID` = ?) as isApprover
FROM
  expense
  LEFT JOIN `callactivity`
    ON `callactivity`.`caa_callactivityno` = expense.`exp_callactivityno`
  LEFT JOIN consultant
    ON callactivity.`caa_consno` = consultant.`cns_consno`
      left join receipt on receipt.expenseId = expense.exp_expenseno
  LEFT JOIN `expensetype`
    ON `expensetype`.`ext_expensetypeno` = expense.`exp_expensetypeno`
  LEFT JOIN problem
    ON problem.`pro_problemno` = callactivity.`caa_problemno`
  left join ordhead on pro_linked_ordno = ordhead.odh_ordno
      left join project on project.ordHeadID = ordhead.odh_ordno
  LEFT JOIN consultant approver
    ON approver.`cns_consno` = expense.`approvedBy`
   left join customer on pro_custno = customer.cus_custno
WHERE 
      caa_endtime and caa_endtime is not null and
      (
    callactivity.`caa_consno` = ?
    OR consultant.`expenseApproverID` = ?
    OR ((SELECT 1 FROM consultant globalApprovers WHERE globalApprovers.globalExpenseApprover AND globalApprovers.cns_consno = ?) = 1 AND consultant.`activeFlag` = "Y")
  )
  AND exp_exported_flag <> "Y" ';

                $offset = $_REQUEST['start'];
                $limit = $_REQUEST['length'];

                $parameters = [
                    ["type" => "i", "value" => $this->userID],
                    ["type" => "i", "value" => $this->userID],
                    ["type" => "i", "value" => $this->userID],
                    ["type" => "i", "value" => $this->userID],
                    ["type" => "i", "value" => $this->userID],
                    ["type" => "i", "value" => $this->userID],
                ];
                /** @var dbSweetcode $db */
                global $db;
                $countResult = $db->preparedQuery(
                    $queryString,
                    $parameters
                );
                $totalCount = $countResult->num_rows;

                $search = $_REQUEST['search']['value'];
                $filteredCount = $totalCount;
                if ($search) {
                    $queryString .= " and (CONCAT(
    consultant.`firstName`,
    \" \",
    consultant.`lastName`
  )  like ? or problem.`pro_problemno` like ? or expensetype.`ext_desc` like ? or project.`description` like ? or cus_name like ?) ";
                    $parameters[] = ["type" => "s", "value" => "%" . $search . "%"];
                    $parameters[] = ["type" => "s", "value" => "%" . $search . "%"];
                    $parameters[] = ["type" => "s", "value" => "%" . $search . "%"];
                    $parameters[] = ["type" => "s", "value" => "%" . $search . "%"];
                    $parameters[] = ["type" => "s", "value" => "%" . $search . "%"];
                    $countResult = $db->preparedQuery(
                        $queryString,
                        $parameters
                    );
                    $filteredCount = $countResult->num_rows;

                }

                $columns = $_REQUEST['columns'];
                $order = $_REQUEST['order'];
                $orderItems = [];
                foreach ($order as $orderItem) {
                    $orderItems[] = mysqli_real_escape_string(
                        $db->link_id(),
                        "{$columns[$orderItem['column']]['name']} {$orderItem['dir']}"
                    );
                }
                if (count($orderItems)) {
                    $queryString .= " order by " . implode(', ', $orderItems);
                }
//                echo json_encode($_REQUEST, JSON_NUMERIC_CHECK);
//                exit;
                $queryString .= " limit ?, ?";
                $parameters[] = ["type" => "i", "value" => $offset];
                $parameters[] = ["type" => "i", "value" => $limit];
                $result = $db->preparedQuery(
                    $queryString,
                    $parameters
                );
                $overtimes = $result->fetch_all(MYSQLI_ASSOC);
                echo json_encode(
                    [
                        "draw"            => $_REQUEST['draw'],
                        "recordsTotal"    => $totalCount,
                        "recordsFiltered" => $filteredCount,
                        "data"            => $overtimes
                    ],
                    JSON_NUMERIC_CHECK
                );
                break;
            case "getOvertimeData" :
                // we have to retrieve the data for the user + if the user is someones approver

                $queryString = 'SELECT
  caa_date as dateSubmitted,
  caa_callactivityno as activityId,
       caa_starttime as startTime,
       caa_endtime as endTime,
       callacttype.cat_desc as activityType,
  caa_problemno as serviceRequestId,
  consultant.cns_name as staffName,
  consultant.`cns_consno` AS userId,
  project.`description` AS projectDescription,
  project.`projectID` AS projectId,
  approver.cns_name as approverName,
       getOvertime(caa_callactivityno) as overtimeDuration,
       customer.cus_name as customerName,
       submitAsOvertime,
  IF(
    callactivity.`overtimeApprovedBy` is not null,
    "Approved",
    IF(
      callactivity.`overtimeDeniedReason` is not null,
      "Denied",
      "Pending"
    )
  ) AS `status`,
  callactivity.`overtimeApprovedDate` as approvedDate,
       callactivity.caa_consno = ? as isSelf,
       ((SELECT
        1
      FROM
        consultant globalApprovers
      WHERE globalApprovers.globalExpenseApprover
        AND globalApprovers.cns_consno = ?) = 1 or consultant.`expenseApproverID` = ?) as isApprover,
       overtimeDurationApproved,
       ((caa_endtime > overtimeStartTime and caa_endtime <= overtimeEndTime ) OR (caa_starttime >= overtimeStartTime and caa_starttime < overtimeEndTime) ) and not isBankHoliday(callactivity.caa_date) as inHours
FROM
  callactivity
  JOIN problem
    ON pro_problemno = caa_problemno
  JOIN callacttype
    ON caa_callacttypeno = cat_callacttypeno AND callacttype.engineerOvertimeFlag = \'Y\'
  JOIN customer
    ON pro_custno = cus_custno
  JOIN consultant 
    ON caa_consno = cns_consno
      left join ordhead on pro_linked_ordno = ordhead.odh_ordno
      left join project on project.ordHeadID = ordhead.odh_ordno
  left join consultant approver
    ON approver.`cns_consno` = callactivity.`overtimeApprovedBy`
  join headert
    on headert.`headerID` = 1
WHERE 
      caa_endtime 
  and caa_endtime is not null and
      (caa_status = \'C\'
    OR caa_status = \'A\'
          )
  AND caa_ot_exp_flag = \'N\'
  and submitAsOvertime
  AND getOvertime(caa_callactivityno) * 60 >= `minimumOvertimeMinutesRequired`
  AND (caa_endtime <> caa_starttime)
  AND (
    callactivity.`caa_consno` = ?
    OR consultant.`expenseApproverID` = ?
    OR (
      (SELECT
        1
      FROM
        consultant globalApprovers
      WHERE globalApprovers.globalExpenseApprover
        AND globalApprovers.cns_consno = ?) = 1
    )
  )
';

                $offset = $_REQUEST['start'];
                $limit = $_REQUEST['length'];

                $parameters = [
                    ["type" => "i", "value" => $this->userID],
                    ["type" => "i", "value" => $this->userID],
                    ["type" => "i", "value" => $this->userID],
                    ["type" => "i", "value" => $this->userID],
                    ["type" => "i", "value" => $this->userID],
                    ["type" => "i", "value" => $this->userID],
                ];
                /** @var dbSweetcode $db */
                global $db;
                $countResult = $db->preparedQuery(
                    $queryString,
                    $parameters
                );
                $totalCount = $countResult->num_rows;

                $search = $_REQUEST['search']['value'];
                $filteredCount = $totalCount;
                if ($search) {
                    $queryString .= " and (consultant.cns_name like ? or problem.`pro_problemno` like ? or  project.`description` like ? or cus_name like ?) ";
                    $parameters[] = ["type" => "s", "value" => "%" . $search . "%"];
                    $parameters[] = ["type" => "s", "value" => "%" . $search . "%"];
                    $parameters[] = ["type" => "s", "value" => "%" . $search . "%"];
                    $parameters[] = ["type" => "s", "value" => "%" . $search . "%"];
                    $countResult = $db->preparedQuery(
                        $queryString,
                        $parameters
                    );
                    $filteredCount = $countResult->num_rows;
                }

                $columns = $_REQUEST['columns'];
                $order = $_REQUEST['order'];
                $orderItems = [];
                foreach ($order as $orderItem) {
                    $orderItems[] = mysqli_real_escape_string(
                        $db->link_id(),
                        "{$columns[$orderItem['column']]['name']} {$orderItem['dir']}"
                    );
                }
                if (count($orderItems)) {
                    $queryString .= " order by " . implode(', ', $orderItems);
                }

                $queryString .= " limit ?, ?";
                $parameters[] = ["type" => "i", "value" => $offset];
                $parameters[] = ["type" => "i", "value" => $limit];

                $result = $db->preparedQuery(
                    $queryString,
                    $parameters
                );
                $overtimes = $result->fetch_all(MYSQLI_ASSOC);

                echo json_encode(
                    [
                        "draw"            => $_REQUEST['draw'],
                        "recordsTotal"    => $totalCount,
                        "recordsFiltered" => $filteredCount,
                        "data"            => $overtimes
                    ],
                    JSON_NUMERIC_CHECK
                );
                break;
            case "approveExpense":
                if (!$this->isExpenseApprover()) {
                    Header("Location: /NotAllowed.php");
                    exit;
                }
                $expenseId = @$_REQUEST['id'];
                try {
                    $this->processExpense($expenseId);
                    $response = ["status" => 'ok'];
                } catch (Exception $exception) {
                    http_response_code(400);
                    $response = ["error" => $exception->getMessage()];
                }
                echo json_encode($response, JSON_NUMERIC_CHECK);
                break;
            case "denyExpense":
                if (!$this->isExpenseApprover()) {
                    Header("Location: /NotAllowed.php");
                    exit;
                }
                $expenseId = @$_REQUEST['id'];
                $denyReason = @$_REQUEST['denyReason'];
                try {
                    $this->processExpense($expenseId, true, $denyReason);
                    $response = ["status" => 'ok'];
                } catch (Exception $exception) {
                    http_response_code(400);
                    $response = ["error" => $exception->getMessage()];
                }
                echo json_encode($response, JSON_NUMERIC_CHECK);
                break;
            case 'deleteExpense':
                if (!$this->isExpenseApprover()) {
                    Header("Location: /NotAllowed.php");
                    exit;
                }
                $expenseId = @$_REQUEST['id'];
                try {
                    $this->deleteExpense($expenseId);
                    $response = ["status" => 'ok'];
                } catch (Exception $exception) {
                    http_response_code(400);
                    $response = ["error" => $exception->getMessage()];
                }
                echo json_encode($response, JSON_NUMERIC_CHECK);
                break;
            case "approveOvertime":
                if (!$this->isExpenseApprover()) {
                    Header("Location: /NotAllowed.php");
                    exit;
                }
                $activityId = @$_REQUEST['id'];
                $overtimeDurationApproved = @$_REQUEST['overtimeDurationApproved'];
                try {
                    $this->processOvertime($activityId, false, null, false, $overtimeDurationApproved);
                    $response = ["status" => 'ok'];
                } catch (Exception $exception) {
                    http_response_code(400);
                    $response = ["error" => $exception->getMessage()];
                }
                echo json_encode($response, JSON_NUMERIC_CHECK);
                break;
            case "denyOvertime":
                if (!$this->isExpenseApprover()) {
                    Header("Location: /NotAllowed.php");
                    exit;
                }
                $activityId = @$_REQUEST['id'];
                $denyReason = @$_REQUEST['denyReason'];
                try {
                    $this->processOvertime($activityId, true, $denyReason);
                    $response = ["status" => 'ok'];
                } catch (Exception $exception) {
                    http_response_code(400);
                    $response = ["error" => $exception->getMessage()];
                }
                echo json_encode($response, JSON_NUMERIC_CHECK);
                break;
            case "deleteOvertime":
                if (!$this->isExpenseApprover()) {
                    Header("Location: /NotAllowed.php");
                    exit;
                }
                $activityId = @$_REQUEST['id'];
                try {
                    $this->processOvertime($activityId, false, null, true);
                    $response = ["status" => 'ok'];
                } catch (Exception $exception) {
                    http_response_code(400);
                    $response = ["error" => $exception->getMessage()];
                }
                echo json_encode($response, JSON_NUMERIC_CHECK);
                break;
            case "getExpensesRunningTotalData":
                /** @var dbSweetcode $db */
                global $db;
                if (!$this->isExpenseApprover()) {
                    Header("Location: /NotAllowed.php");
                    exit;
                }
                $expenseQuery = "SELECT * FROM 
(SELECT
  consultant.`cns_name` AS staffName,
  (SELECT
    SUM(exp_value)
  FROM
    expense
    LEFT JOIN callactivity
      ON `callactivity`.`caa_callactivityno` = expense.`exp_callactivityno`
  WHERE callactivity.`caa_consno` = consultant.`cns_consno`
    AND caa_date BETWEEN DATE_FORMAT(NOW(), '%Y')
    AND NOW()
    AND exp_exported_flag <> \"N\"
    AND expense.`approvedBy` IS NOT NULL) AS YTD,
  b.*
FROM
  consultant
  LEFT JOIN
    (SELECT
      callactivity.`caa_consno` AS staffId,
      SUM(
        IF(
          expense.`approvedBy` IS NOT NULL,
          expense.`exp_value`,
          0
        )
      ) AS approvedValue,
      SUM(
        IF(
          expense.`approvedBy` IS NULL
          AND expense.`deniedReason` IS NULL,
          expense.`exp_value`,
          0
        )
      ) AS pendingValue
    FROM
      expense
      LEFT JOIN `callactivity`
        ON `callactivity`.`caa_callactivityno` = expense.`exp_callactivityno`
    WHERE caa_endtime
      AND caa_endtime IS NOT NULL
      AND expense.`exp_exported_flag` <> 'Y'
    GROUP BY staffId) b
    ON b.staffId = consultant.`cns_consno`
WHERE (
    consultant.`expenseApproverID` = ?
    OR
    (SELECT
      1
    FROM
      consultant globalApprovers
    WHERE globalApprovers.globalExpenseApprover
      AND globalApprovers.cns_consno = ?) = 1
  ) 
  AND consultant.`activeFlag` = \"Y\") a WHERE YTD IS NOT NULL OR approvedValue IS NOT NULL OR pendingValue IS NOT NULL ORDER BY staffName";

                $result = $db->preparedQuery(
                    $expenseQuery,
                    [["type" => "i", "value" => $this->userID], ["type" => "i", "value" => $this->userID]]
                );
                $expenses = $result->fetch_all(MYSQLI_ASSOC);
                echo json_encode($expenses, JSON_NUMERIC_CHECK);
                break;
            case "getOvertimeRunningTotalData":
                /** @var dbSweetcode $db */
                global $db;
                if (!$this->isExpenseApprover()) {
                    Header("Location: /NotAllowed.php");
                    exit;
                }
                $overtimeQuery = "SELECT
  *
FROM
  (SELECT
    consultant.`cns_name` AS staffName,
    (SELECT
      SUM(overtimeDurationApproved)
    FROM
      callactivity
    WHERE caa_date BETWEEN DATE_FORMAT(NOW(), '%Y')
      AND NOW()
      AND callactivity.`overtimeApprovedBy` IS NOT NULL
      AND (caa_status = 'C'
        OR caa_status = 'A')
      AND caa_ot_exp_flag = 'Y'
      AND submitAsOvertime
      AND callactivity.`caa_consno` = consultant.`cns_consno`) AS YTD,
    b.*
  FROM
    consultant
    LEFT JOIN
      (SELECT
        callactivity.caa_consno AS staffId,
        SUM(
          IF(
            callactivity.`overtimeApprovedBy` IS NOT NULL,
            overtimeDurationApproved,
            0
          )
        ) AS approvedValue,
        SUM(
          IF(
            callactivity.`overtimeDeniedReason` IS NULL
            AND callactivity.`overtimeApprovedBy` IS NULL,
            getOvertime (caa_callactivityno),
            0
          )
        ) AS pendingValue
      FROM
        callactivity
        JOIN problem
          ON pro_problemno = caa_problemno
        JOIN callacttype
          ON caa_callacttypeno = cat_callacttypeno
          AND callacttype.engineerOvertimeFlag = 'Y'
        JOIN headert
          ON headert.`headerID` = 1
      WHERE caa_endtime
        AND caa_endtime IS NOT NULL
        AND (caa_status = 'C'
          OR caa_status = 'A')
        AND caa_ot_exp_flag = 'N'
        AND getOvertime (caa_callactivityno) * 60 >= `minimumOvertimeMinutesRequired`
      GROUP BY staffId) b
      ON b.staffId = consultant.`cns_consno`
  WHERE (
      consultant.`expenseApproverID` = ?
      OR
      (SELECT
        1
      FROM
        consultant globalApprovers
      WHERE globalApprovers.globalExpenseApprover
        AND globalApprovers.cns_consno = ?) = 1
    )
    AND consultant.`activeFlag` = \"Y\") a
WHERE YTD IS NOT NULL
  OR approvedValue IS NOT NULL
  OR pendingValue IS NOT NULL
ORDER BY staffName";
                $result = $db->preparedQuery(
                    $overtimeQuery,
                    [["type" => "i", "value" => $this->userID], ["type" => "i", "value" => $this->userID]]
                );
                $overtimes = $result->fetch_all(MYSQLI_ASSOC);
                echo json_encode($overtimes, JSON_NUMERIC_CHECK);
                break;
            case 'runningTotals':
                if (!$this->isExpenseApprover()) {
                    Header("Location: /NotAllowed.php");
                    exit;
                }
                global $twig;

                $this->setTemplateFiles(
                    array('ChangeLog' => 'About.inc')
                );


                $this->template->set_var(
                    'changeLog',
                    $twig->render('@internal/expenseDashboard/runningTotals.html.twig', [])
                );

                $this->template->parse('CONTENTS', 'ChangeLog', true);
                $this->parsePage();
                break;
            default:
                $this->displayReport();
                break;
        }
    }

    /**
     * @param $id
     * @param bool $deny
     * @param null $denyReason
     * @throws Exception
     */
    function processExpense($id, $deny = false, $denyReason = null)
    {
        $dbeExpense = $this->checkProcessExpense($id);
        if ($deny) {
            if (!$denyReason) {
                throw new Exception('Please provide a deny reason');
            }
            $dbeExpense->setValue(DBEExpense::deniedReason, $denyReason);
            $this->sendDeniedExpenseEmail($dbeExpense);
        } else {
            $dbeExpense->setValue(DBEExpense::approvedBy, $this->userID);
            $dbeExpense->setValue(DBEExpense::approvedDate, (new DateTime())->format(DATE_MYSQL_DATETIME));
        }
        $dbeExpense->updateRow();
    }

    /**
     * @param $id
     * @return DBEExpense
     * @throws Exception
     */
    function checkProcessExpense($id)
    {
        if (!$id) {
            throw new Exception('Please provide the id of the expense to approve');
        }

        $dbeExpense = new DBEExpense($this);
        $dbeExpense->getRow($id);
        if (!$dbeExpense->rowCount()) {
            throw new Exception('Could not find any expenses with the provided ID');
        }

        if ($dbeExpense->getValue(DBEExpense::exportedFlag) == 'Y') {
            throw new Exception('This expense has already been exported');
        }

        if ($dbeExpense->getValue(DBEExpense::deniedReason) || $dbeExpense->getValue(DBEExpense::approvedBy)) {
            throw new Exception('This expense has already been processed');
        }
        $dbeCallActivity = new DBECallActivity($this);
        $dbeCallActivity->getRow($dbeExpense->getValue(DBEExpense::callActivityID));

        if ($dbeCallActivity->getValue(DBECallActivity::userID) == $this->userID) {
            throw new Exception('You cannot process your own expenses');
        }

        $dbeUser = new DBEUser($this);
        $dbeUser->getRow($dbeCallActivity->getValue(DBECallActivity::userID));
        if (!$this->dbeUser->getValue(DBEUser::globalExpenseApprover) && $dbeUser->getValue(
                DBEUser::expenseApproverID
            ) != $this->userID) {
            throw new Exception('You are not allowed to process this expense');
        }
        return $dbeExpense;
    }

    /**
     * @param $dbeExpense DBEExpense
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    function sendDeniedExpenseEmail($dbeExpense)
    {
        /** @var Environment */
        global $twig;
        $activityId = $dbeExpense->getValue(DBEExpense::callActivityID);
        $dbeCallActivity = new DBEJCallActivity($this);
        $dbeCallActivity->getRow($activityId);
        $toEmail = $dbeCallActivity->getValue(DBEJCallActivity::userAccount) . "@" . CONFIG_PUBLIC_DOMAIN;
        $fromEmail = $this->dbeUser->getValue(DBEUser::username) . "@" . CONFIG_PUBLIC_DOMAIN;
        $dbeExpenseType = new DBEExpenseType($this);
        $dbeExpenseType->getRow($dbeExpense->getValue(DBEExpense::expenseTypeID));
        $buMail = new BUMail($this);

        $body = $twig->render(
            '@internal/deniedExpenseEmail.html.twig',
            [
                "expense" => [
                    "type"         => $dbeExpenseType->getValue(DBEExpenseType::description),
                    "value"        => number_format($dbeExpense->getValue(DBEExpense::value), 2),
                    "deniedReason" => $dbeExpense->getValue(DBEExpense::deniedReason)
                ]
            ]
        );

        $subject = "Your expense request has been denied";

        $hdrs = array(
            'From'         => $fromEmail,
            'To'           => $toEmail,
            'Subject'      => $subject,
            'Date'         => date("r"),
            'Content-Type' => 'text/html; charset=UTF-8'
        );

        $buMail->mime->setHTMLBody($body);

        $mime_params = array(
            'text_encoding' => '7bit',
            'text_charset'  => 'UTF-8',
            'html_charset'  => 'UTF-8',
            'head_charset'  => 'UTF-8'
        );
        $body = $buMail->mime->get($mime_params);

        $hdrs = $buMail->mime->headers($hdrs);

        $buMail->putInQueue(
            $fromEmail,
            $toEmail,
            $hdrs,
            $body
        );

    }

    /**
     * @param $id
     * @throws Exception
     */
    function deleteExpense($id)
    {
        $dbeExpense = $this->checkProcessExpense($id);
        $dbeExpense->deleteRow();
    }

    /**
     * @param $activityId
     * @param bool $deny
     * @param null $denyReason
     * @param bool $isDeleted
     * @param null $overtimeDurationApproved
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     * @throws Exception
     */
    private function processOvertime($activityId,
                                     $deny = false,
                                     $denyReason = null,
                                     $isDeleted = false,
                                     $overtimeDurationApproved = null
    )
    {
        $dbeCallActivity = $this->checkProcessOvertime($activityId);
        if ($deny || $isDeleted) {
            if ($isDeleted) {
                $denyReason = 'DELETED';
                $dbeCallActivity->setValue(DBECallActivity::overtimeExportedFlag, 'Y');
            }
            if (!$denyReason) {
                throw new Exception('Please provide a deny reason');
            }
            $dbeCallActivity->setValue(DBECallActivity::overtimeDeniedReason, $denyReason);
            if (!$isDeleted) {
                $this->sendDeniedOvertimeEmail($dbeCallActivity);
            }
        } else {
            $dbeCallActivity->setValue(DBECallActivity::overtimeApprovedBy, $this->userID);
            $dbeCallActivity->setValue(
                DBECallActivity::overtimeApprovedDate,
                (new DateTime())->format(DATE_MYSQL_DATETIME)
            );
            $overtimeApprovedValue = $overtimeDurationApproved;
            if (!$overtimeApprovedValue) {
                $buExpense = new BUExpense($this);
                $overtimeApprovedValue = number_format($buExpense->calculateOvertime($activityId), 2, '.', '');
            }
            $dbeCallActivity->setValue(
                DBECallActivity::overtimeDurationApproved,
                $overtimeApprovedValue
            );
        }
        $dbeCallActivity->updateRow();
    }

    /**
     * @param $activityId
     * @return DBECallActivity
     * @throws Exception
     */
    function checkProcessOvertime($activityId)
    {
        if (!$activityId) {
            throw new Exception('Please provide the id of the overtime to approve');
        }

        $dbeCallActivity = new DBECallActivity($this);
        $dbeCallActivity->getRow($activityId);
        if (!$dbeCallActivity->rowCount()) {
            throw new Exception('Could not find any overtime related activity with the provided ID');
        }

        if ($dbeCallActivity->getValue(DBECallActivity::overtimeExportedFlag) == 'Y') {
            throw new Exception('This overtime has already been exported');
        }

        if ($dbeCallActivity->getValue(DBECallActivity::overtimeDeniedReason) || $dbeCallActivity->getValue(
                DBECallActivity::overtimeApprovedBy
            )) {
            throw new Exception('This overtime has already been processed');
        }

        if ($dbeCallActivity->getValue(DBECallActivity::userID) == $this->userID) {
            throw new Exception('You cannot process your own expenses');
        }

        $dbeUser = new DBEUser($this);
        $dbeUser->getRow($dbeCallActivity->getValue(DBECallActivity::userID));
        if (!$this->dbeUser->getValue(DBEUser::globalExpenseApprover) && $dbeUser->getValue(
                DBEUser::expenseApproverID
            ) != $this->userID) {
            throw new Exception('You are not allowed to process this overtime');
        }
        return $dbeCallActivity;
    }

    /**
     * @param $dbeCallActivity DBECallActivity
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    function sendDeniedOvertimeEmail($dbeCallActivity)
    {
        /** @var Environment */
        global $twig;
        $dbeJCallactivity = new DBEJCallActivity($this);
        $dbeJCallactivity->getRow($dbeCallActivity->getValue(DBECallActivity::callActivityID));
        $toEmail = $dbeJCallactivity->getValue(DBEJCallActivity::userAccount) . "@" . CONFIG_PUBLIC_DOMAIN;
        $fromEmail = $this->dbeUser->getValue(DBEUser::username) . "@" . CONFIG_PUBLIC_DOMAIN;
        $buMail = new BUMail($this);
        $buExpense = new BUExpense($this);
        $dsHeader = new DataSet($this);
        $buHeader = new BUHeader($this);
        $buHeader->getHeader($dsHeader);

        $affectedUser = new DBEUser($this);
        $affectedUser->getRow($dbeJCallactivity->getValue(DBECallActivity::userID));


        $body = $twig->render(
            '@internal/deniedExpenseEmail.html.twig',
            [
                "overtime" => [
                    "customerName" => $dbeJCallactivity->getValue(DBEJCallActivity::customerName),
                    "duration"     => number_format(
                        $buExpense->calculateOvertime(
                            $dbeJCallactivity->getValue(DBEJCallActivity::callActivityID)
                        ),
                        2
                    ),
                    "date"         => (new DateTime($dbeJCallactivity->getValue(DBECallActivity::date)))->format(
                        'd-m-Y'
                    ),
                    "deniedReason" => $dbeCallActivity->getValue(DBECallActivity::overtimeDeniedReason),
                ]
            ]
        );

        $subject = "Your overtime request has been denied";

        $hdrs = array(
            'From'         => $fromEmail,
            'To'           => $toEmail,
            'Subject'      => $subject,
            'Date'         => date("r"),
            'Content-Type' => 'text/html; charset=UTF-8'
        );

        $buMail->mime->setHTMLBody($body);

        $mime_params = array(
            'text_encoding' => '7bit',
            'text_charset'  => 'UTF-8',
            'html_charset'  => 'UTF-8',
            'head_charset'  => 'UTF-8'
        );
        $body = $buMail->mime->get($mime_params);

        $hdrs = $buMail->mime->headers($hdrs);

        $buMail->putInQueue(
            $fromEmail,
            $toEmail,
            $hdrs,
            $body
        );

    }

    /**
     * @throws Exception
     */
    function displayReport()
    {

        $this->setMethodName('displayReport');

        $this->setTemplateFiles(
            'ExpenseDashboard',
            'ExpenseDashboard'
        );

        $this->setPageTitle('Expenses/Overtime Dashboard');
        /** @var dbSweetcode */
        global $db;

        $userExpensesQuery = 'SELECT
  sum(if(expense.approvedBy is not null, expense.exp_value, 0)) as approved,
       sum(if(expense.approvedBy is null and expense.deniedReason is null, expense.exp_value, 0)) as pending
FROM
  expense
  LEFT JOIN `callactivity`
    ON `callactivity`.`caa_callactivityno` = expense.`exp_callactivityno`
WHERE 
      caa_endtime and caa_endtime is not null and
          callactivity.`caa_consno` = ?
  AND exp_exported_flag <> "Y"';

        $statement = $db->preparedQuery($userExpensesQuery, [["type" => "i", "value" => $this->userID]]);
        $expenseSummary = $statement->fetch_assoc();


        $useOvertimeQuery = 'SELECT sum(if(callactivity.overtimeApprovedBy is not null, overtimeDurationApproved, 0)) as approved,
       sum(if(callactivity.overtimeApprovedBy is null and callactivity.overtimeDeniedReason is null,
              getOvertime(caa_callactivityno), 0))                                              as pending
FROM callactivity
         JOIN callacttype
              ON caa_callacttypeno = cat_callacttypeno AND callacttype.engineerOvertimeFlag = \'Y\'
         JOIN consultant
              ON caa_consno = cns_consno
         join headert
              on headert.`headerID` = 1
WHERE caa_endtime
  and caa_endtime is not null
  and (caa_status = \'C\'
    OR caa_status = \'A\')
  AND caa_ot_exp_flag = \'N\'
  and submitAsOvertime
  AND (
    (
      caa_callacttypeno = 22
      AND (
        isBankHoliday (caa_date)
        OR
        WEEKDAY(caa_date) IN (5,6)
        OR 
          caa_starttime < overtimeStartTime 
          OR `caa_endtime` > `overtimeEndTime`
      )
    )
    OR caa_callacttypeno <> 22
  )
  AND getOvertime(caa_callactivityno) * 60 >= `minimumOvertimeMinutesRequired`
  AND caa_endtime <> caa_starttime
  AND callactivity.`caa_consno` = ?';

        $statement = $db->preparedQuery($useOvertimeQuery, [["type" => "i", "value" => $this->userID]]);
        $overtimeSummary = $statement->fetch_assoc();

        $isApprover = $this->dbeUser->getValue(DBEUser::isExpenseApprover) || $this->dbeUser->getValue(
                DBEUser::globalExpenseApprover
            );

        $this->template->setVar(
            [
                'approvedExpenseValue'  => $expenseSummary['approved'],
                'pendingExpenseValue'   => $expenseSummary['pending'],
                'approvedOvertimeValue' => $overtimeSummary['approved'],
                'pendingOvertimeValue'  => $overtimeSummary['pending'],
                'runningTotalsLink'     => $isApprover ? '<a href="?action=runningTotals" target="_blank">Running Totals</a>' : null,
            ]
        );

        $this->template->parse(
            'CONTENTS',
            'ExpenseDashboard',
            true
        );
        $this->parsePage();
    }
}