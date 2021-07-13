<?php
/**
 * Expense controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
global $cfg;
require_once($cfg['path_ct'] . '/CTCNC.inc.php');

// Actions
class CTOffice365StorageReports extends CTCNC
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
        $roles = REPORTS_PERMISSION;
        if (!self::hasPermissions($roles)) {
            Header("Location: /NotAllowed.php");
            exit;
        }
        $this->setMenuId(503);
    }

    function delete()
    {
        $this->defaultAction();
    }

    /**
     * Route to function based upon action passed
     * @throws Exception
     * @throws Exception
     */
    function defaultAction()
    {
        switch ($this->getAction()) {

            case 'getData':

                $customerID = $this->getParam('customerId');
                $startDate = $this->getParam('startDate');
                $endDate = $this->getParam('endDate');
                try {
                    $data = $this->getData($customerID, $startDate, $endDate);
                } catch (Exception $exception) {
                    $data = ['error' => $exception->getMessage()];
                    http_response_code(400);
                }
                echo json_encode($data, JSON_NUMERIC_CHECK);
                break;
            case 'displayForm':
            default:
                $this->displayForm();
                break;
        }
    }

    function getData($customerID, $startDateString, $endDateString)
    {
        if (!$customerID || !$startDateString || !$endDateString) {
            throw new Exception('Parameters customerId, startDate and endDate are all mandatory');
        }

        $startDate = DateTime::createFromFormat(DATE_MYSQL_DATE, $startDateString);
        if (!$startDate) {
            throw new Exception('Parameter startDate does not have the correct format: YYYY-MM-DD');
        }

        $endDate = DateTime::createFromFormat(DATE_MYSQL_DATE, $endDateString);
        if (!$endDate) {
            throw new Exception('Parameter endDate does not have the correct format: YYYY-MM-DD');
        }

        global $db;

        $query = "SELECT
  a.date,
  a.totalOneDriveStorageUsed,
  a.totalEmailStorageUsed,
  a.totalSiteStorageUsed
FROM
  customerOffice365StorageStats a
  LEFT JOIN customer
    ON customer.`cus_custno` = a.customerId
    WHERE a.customerId = ? AND a.date BETWEEN ? AND ? ";

        $statement = $db->preparedQuery(
            $query,
            [
                ["type" => 'i', "value" => $customerID],
                ["type" => 's', "value" => $startDate->format(DATE_MYSQL_DATE)],
                ["type" => 's', "value" => $endDate->format(DATE_MYSQL_DATE)],
            ]
        );

        return $statement->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Export expenses that have not previously been exported
     * @access private
     * @throws Exception
     * @throws Exception
     * @throws Exception
     * @throws Exception
     * @throws Exception
     */
    function displayForm()
    {
        $this->setPageTitle('Office 365 Storage Reports');
        $this->setTemplateFiles(
            'Office365StorageReport',
            'Office365StorageReports'
        );

        $this->template->parse(
            'CONTENTS',
            'Office365StorageReport',
            true
        );

        $this->parsePage();
    }
}
