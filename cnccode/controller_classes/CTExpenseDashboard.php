<?php
/**
 * Created by PhpStorm.
 * User: fizdalf
 * Date: 22/08/2018
 * Time: 10:39
 */

require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_bu'] . '/BUActivity.inc.php');
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
        $roles = [
            "technical",
            "sales"
        ];
        if (!self::hasPermissions($roles)) {
            Header("Location: /NotAllowed.php");
            exit;
        }
    }

    /**
     * Route to function based upon action passed
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
  expense.`dateSubmitted`,
  expensetype.`ext_desc` AS expenseTypeDescription,
  expense.`exp_expensetypeno` AS expenseTypeId,
  expense.`exp_value` AS `value`,
  project.`description` AS projectDescription,
  project.`projectID` AS projectId,
  expense.approvedDate,
  CONCAT(
    approver.`firstName`,
    " ",
    approver.`lastName`
  ) AS approverName,
  IF(
    expense.`approvedBy`,
    "Approved",
    IF(
      expense.`deniedReason`,
      "Denied",
      "Pending"
    )
  ) AS status
FROM
  expense
  LEFT JOIN `callactivity`
    ON `callactivity`.`caa_callactivityno` = expense.`exp_callactivityno`
  LEFT JOIN consultant
    ON callactivity.`caa_consno` = consultant.`cns_consno`
  LEFT JOIN `expensetype`
    ON `expensetype`.`ext_expensetypeno` = expense.`exp_expensetypeno`
  LEFT JOIN problem
    ON problem.`pro_problemno` = callactivity.`caa_problemno`
  LEFT JOIN project
    ON project.`projectID` = problem.`pro_projectno`
  LEFT JOIN consultant approver
    ON approver.`cns_consno` = expense.`approvedBy`
WHERE (
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
  )  like ? or problem.`pro_problemno` like ? or expensetype.`ext_desc` like ? or project.`description` like ?) ";
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
                $data = $result->fetch_all(MYSQLI_ASSOC);
                echo json_encode(
                    [
                        "draw"            => $_REQUEST['draw'],
                        "recordsTotal"    => $totalCount,
                        "recordsFiltered" => $filteredCount,
                        "data"            => $data
                    ],
                    JSON_NUMERIC_CHECK
                );
                break;
            default:
                $this->displayReport();
                break;
        }
    }

    function displayReport()
    {

        $this->setMethodName('displayReport');

        $this->setTemplateFiles(
            'ExpenseDashboard',
            'ExpenseDashboard'
        );

        $this->setPageTitle('Expenses/Overtime Dashboard');

        $this->template->parse(
            'CONTENTS',
            'ExpenseDashboard',
            true
        );
        $this->parsePage();
    }

    /**
     * @param DBEExpense|DataSet $dsExpense
     * @return string
     */
    private function getExpenseStatus($dsExpense)
    {
        return $dsExpense->getValue(
            DBEExpense::approvedDate
        ) ? "Approved" : ($dsExpense->getValue(DBEExpense::deniedReason) ? "Denied" : "Pending");
    }
}