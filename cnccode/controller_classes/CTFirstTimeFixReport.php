<?php
/**
 * Created by PhpStorm.
 * User: fizdalf
 * Date: 07/08/2018
 * Time: 9:42
 */
global $cfg;
require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_bu'] . '/BUUser.inc.php');

class CTFirstTimeFixReport extends CTCNC
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
        if (!$this->isUserSDManager()) {
            Header("Location: /NotAllowed.php");
            exit;
        }
        $this->setMenuId(206);
    }

    /**
     * @throws Exception
     */
    public function defaultAction()
    {


        switch (@$this->getAction()) {

            case 'fetchData':
                $startDate = null;
                if (@$this->getParam('startDate')) {
                    $startDate = DateTime::createFromFormat(
                        DATE_MYSQL_DATE,
                        $this->getParam('startDate')
                    );
                }
                $endDate = null;
                if (@$this->getParam('endDate')) {
                    $endDate = DateTime::createFromFormat(
                        DATE_MYSQL_DATE,
                        $this->getParam('endDate')
                    );
                }
                echo json_encode(
                    $this->getFirstTimeFixData(
                        @$this->getParam('customerID'),
                        @$this->getParam('engineerID'),
                        $startDate,
                        $endDate
                    )
                );
                break;
            case  'search':
            default:
                $this->search();
        }
    }

    private function getFirstTimeFixData($customerID = null,
                                         $engineerID = null,
                                         DateTime $startDate = null,
                                         DateTime $endDate = null
    )
    {
        global $db;
        $query = "SELECT 
  CONCAT(
    engineer.`firstName`,
    ' ',
    engineer.`lastName`
  ) AS name,
  engineer.cns_consno as engineerId,
  SUM(
    COALESCE(
      (SELECT 
        1 
      FROM
        callactivity 
      WHERE callactivity.caa_problemno = problem.pro_problemno 
        AND callactivity.caa_callacttypeno = 8 
        AND TIME_TO_SEC(
          TIMEDIFF(
            callactivity.caa_starttime,
            initial.caa_endtime
          )
        ) <= (5 * 60) 
        AND callactivity.`caa_consno` = engineer.`cns_consno` 
      LIMIT 1),
      0
    )
  ) AS attemptedFirstTimeFix,
  SUM(
    COALESCE(
      (SELECT 
        1 
      FROM
        problem test 
        JOIN callactivity initial 
          ON initial.caa_problemno = test.pro_problemno 
          AND initial.caa_callacttypeno = 51 
        JOIN callactivity remoteSupport 
          ON remoteSupport.caa_problemno = test.pro_problemno 
          AND remoteSupport.caa_callacttypeno = 8 
        JOIN callactivity fixedActivity 
          ON fixedActivity.caa_problemno = test.pro_problemno 
          AND fixedActivity.caa_callacttypeno = 57 
      WHERE test.pro_problemno = problem.`pro_problemno` 
        AND (test.pro_status = 'F' OR test.pro_status = 'C')
        AND remoteSupport.caa_consno = engineer.`cns_consno` 
        AND fixedActivity.caa_consno = engineer.`cns_consno` 
        AND TIME_TO_SEC(
          TIMEDIFF(
            remoteSupport.caa_starttime,
            initial.caa_endtime
          )
        ) <= (5 * 60) 
        AND TIME_TO_SEC(
          TIMEDIFF(
            fixedActivity.caa_starttime,
            remoteSupport.caa_endtime
          )
        ) <= (5 * 60) 
      LIMIT 1),
      0
    )
  ) AS firstTimeFix,
  SUM(1) AS totalRaised 
FROM
  problem 
  JOIN callactivity initial 
    ON initial.caa_problemno = problem.pro_problemno 
    AND initial.caa_callacttypeno = 51 
  JOIN consultant engineer 
    ON initial.`caa_consno` = engineer.`cns_consno` 
   JOIN custitem
    ON pro_contract_cuino = custitem.`cui_cuino`
    AND cui_expiry_date >= NOW()
    AND renewalStatus <> 'D'
    AND declinedFlag <> 'Y'
    AND custitem.`cui_itemno` IN (6915, 14535)
WHERE problem.`pro_custno` <> 282 
  AND problem.raiseTypeId = 3
  and problem.pro_priority < 4
  AND engineer.`teamID` = 1 ";
        if ($customerID) {
            $query .= " and pro_custno = " . $customerID;
        }
        if ($engineerID) {
            $query .= " and engineer.`cns_consno` = " . $engineerID;
        }
        if ($startDate) {
            $query .= " and initial.caa_date >= '" . $startDate->format('Y-m-d') . "'";
        }
        if ($endDate) {
            $query .= " and initial.caa_date <= '" . $endDate->format('Y-m-d') . "'";
        }
        $query .= " GROUP BY engineer.`cns_consno` 
ORDER BY engineer.firstName";
        $result = $db->query($query);
        $totalRaised    = 0;
        $totalAttempted = 0;
        $totalAchieved  = 0;
        $data           = [
            "engineers"      => [],
            "totalRaised"    => 0,
            "totalAttempted" => 0,
            "totalAchieved"  => 0
        ];
        while ($row = $result->fetch_assoc()) {
            $data["engineers"][] = [
                'id'                    => $row['engineerId'],
                'name'                  => $row['name'],
                'firstTimeFix'          => $row['firstTimeFix'],
                'attemptedFirstTimeFix' => $row['attemptedFirstTimeFix'],
                'totalRaised'           => $row['totalRaised']
            ];
            $data['totalRaised']    += $row['totalRaised'];
            $data['totalAttempted'] += $row['attemptedFirstTimeFix'];
            $data['totalAchieved']  += $row['firstTimeFix'];
        }
        $data['firstTimeFixAttemptedPct'] = $data['totalRaised'] > 0 ? round(
            ($data['totalAttempted'] / $data['totalRaised']) * 100
        ) : 'N/A';
        $data['firstTimeFixAchievedPct']  = $data['totalRaised'] > 0 ? round(
            ($data['totalAchieved'] / $data['totalRaised']) * 100
        ) : 'N/A';
        $data['phonedThroughRequests']    = $data['totalRaised'];
        return $data;
    }

  
    /**
     * @throws Exception
     */
    private function search()
    {

        $this->setTemplateFiles(
            array(
                'FirstTimeFixReport' => 'FirstTimeFixReport'
            )
        );
//        $this->loadReactScript('SpinnerHolderComponent.js');
//        $this->loadReactCSS('SpinnerHolderComponent.css');
        $hdUsers = (new BUUser($this))->getUsersByTeamLevel(1);
        $this->template->set_block(
            'FirstTimeFixReport',
            'userBlock',
            'hdUsers'
        );
        $this->loadReactScript('FirstTimeFixReportComponent.js');
        $this->loadReactCSS('FirstTimeFixReportComponent.css');
        foreach ($hdUsers as $user) {


            $this->template->set_var(
                array(
                    'userName' => $user['userName'],
                    'userID'   => $user['cns_consno']
                )
            );
            $this->template->parse(
                'hdUsers',
                'userBlock',
                true
            );
        }
        $fetchURL = $urlSubmit = Controller::buildLink(
            $_SERVER ['PHP_SELF'],
            array('action' => "fetchData")
        );
        $customerPopupURL = Controller::buildLink(
            CTCNC_PAGE_CUSTOMER,
            array(
                'action'  => CTCNC_ACT_DISP_CUST_POPUP,
                'htmlFmt' => CT_HTML_FMT_POPUP
            )
        );
        $this->setPageTitle('First Time Fix Report');
        $this->template->set_var(
            array(
                'customerPopupURL' => $customerPopupURL,
                'fetchDataURL'     => $fetchURL
            )
        );
        $this->template->parse(
            'CONTENTS',
            'FirstTimeFixReport',
            true
        );
        $this->parsePage();

    }
}