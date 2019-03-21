<?php
/**
 * Expense controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_dbe'] . '/DBEIgnoredADDomain.inc.php');
require_once($cfg['path_bu'] . '/BUActivity.inc.php');

// Actions
class CTIgnoredADDomains extends CTCNC
{
    var $buActivity;

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
            "maintenance",
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
        switch ($_REQUEST['action']) {
            case 'delete':
                if (!isset($_REQUEST['id'])) {
                    http_response_code(400);
                    throw new Exception('ID is missing');
                }

                $DBEIgnoredADDomain = new DBEIgnoredADDomain($this);

                $DBEIgnoredADDomain->getRow($_REQUEST['id']);

                if (!$DBEIgnoredADDomain->rowCount) {
                    http_response_code(404);
                    exit;
                }
                $DBEIgnoredADDomain->setLogSQLOn();
                $DBEIgnoredADDomain->deleteRow();
                echo json_encode(["status" => "ok"]);
                break;
            case 'update':

                if (!isset($_REQUEST['id'])) {
                    throw new Exception('ID is missing');
                }

                $DBEIgnoredADDomain = new DBEIgnoredADDomain($this);

                $DBEIgnoredADDomain->getRow($_REQUEST['id']);

                if (!$DBEIgnoredADDomain->rowCount) {
                    http_response_code(404);
                    exit;
                }

                $DBEIgnoredADDomain->setValue(
                    DBEIgnoredADDomain::domain,
                    $_REQUEST['domain']
                );
                $DBEIgnoredADDomain->setValue(
                    DBEIgnoredADDomain::customerID,
                    $_REQUEST['customerID']
                );

                $DBEIgnoredADDomain->updateRow();
                echo json_encode(["status" => "ok"]);
                break;
            case 'create':
                $DBEIgnoredADDomain = new DBEIgnoredADDomain($this);

                $DBEIgnoredADDomain->setValue(
                    DBEIgnoredADDomain::domain,
                    $_REQUEST['domain']
                );
                $DBEIgnoredADDomain->setValue(
                    DBEIgnoredADDomain::customerID,
                    $_REQUEST['customerID']
                );

                $DBEIgnoredADDomain->insertRow();

                $customerString = null;
                if ($DBEIgnoredADDomain->getValue(DBEIgnoredADDomain::customerID)) {
                    $dbeCustomer = new DBECustomer($this);
                    $dbeCustomer->getRow($DBEIgnoredADDomain->getValue(DBEIgnoredADDomain::customerID));
                    $customerString = $dbeCustomer->getValue(DBECustomer::name);
                }

                echo json_encode(
                    [
                        "id"             => $DBEIgnoredADDomain->getValue(DBEIgnoredADDomain::ignoredADDomainID),
                        "domain"         => $DBEIgnoredADDomain->getValue(DBEIgnoredADDomain::domain),
                        "customerID"     => $DBEIgnoredADDomain->getValue(DBEIgnoredADDomain::customerID),
                        "customerString" => $customerString
                    ],
                    JSON_NUMERIC_CHECK
                );

                break;
            case 'getData':
                $DBEIgnoredADDomains = new DBEIgnoredADDomain($this);

                $DBEIgnoredADDomains->getRows();
                $data = [];
                while ($DBEIgnoredADDomains->fetchNext()) {
                    $customerString = null;
                    if ($DBEIgnoredADDomains->getValue(DBEIgnoredADDomain::customerID)) {
                        $dbeCustomer = new DBECustomer($this);
                        $dbeCustomer->getRow($DBEIgnoredADDomains->getValue(DBEIgnoredADDomain::customerID));
                        $customerString = $dbeCustomer->getValue(DBECustomer::name);
                    }
                    $data[] = [
                        "id"             => $DBEIgnoredADDomains->getValue(DBEIgnoredADDomain::ignoredADDomainID),
                        "domain"         => $DBEIgnoredADDomains->getValue(DBEIgnoredADDomain::domain),
                        "customerID"     => $DBEIgnoredADDomains->getValue(DBEIgnoredADDomain::customerID),
                        "customerString" => $customerString
                    ];
                }
                echo json_encode(
                    $data,
                    JSON_NUMERIC_CHECK
                );
                break;
            case 'displayForm':
            default:
                $this->displayForm();
                break;
        }
    }

    /**
     * Export expenses that have not previously been exported
     * @access private
     */
    function displayForm()
    {
        $this->setPageTitle('Ignored AD Domains');
        $this->setTemplateFiles(
            'IgnoredADDomains',
            'IgnoredADDomains'
        );

        $this->template->parse(
            'CONTENTS',
            'IgnoredADDomains',
            true
        );

        $URLDeleteItem = $this->buildLink(
            $_SERVER['PHP_SELF'],
            [
                'action' => 'delete'
            ]
        );

        $URLUpdateItem = $this->buildLink(
            $_SERVER['PHP_SELF'],
            [
                'action' => 'update'
            ]
        );

        $URLCreateItem = $this->buildLink(
            $_SERVER['PHP_SELF'],
            [
                'action' => 'create'
            ]
        );

        $URLGetData = $this->buildLink(
            $_SERVER['PHP_SELF'],
            [
                'action' => 'getData'
            ]
        );
        $this->template->setVar(
            [
                "URLDeleteItem" => $URLDeleteItem,
                "URLUpdateItem" => $URLUpdateItem,
                "URLAddItem"    => $URLCreateItem,
                "URLGetData"    => $URLGetData
            ]
        );

        $this->parsePage();
    }

    function resolveCalls()
    {
        $this->setMethodName('exportExpenseGenerate');
        $this->buActivity->initialiseResolveForm($this->dsIgnoredADDomains);
        if (!$this->dsIgnoredADDomains->populateFromArray($_REQUEST['IgnoredADDomains'])) {
            $this->setFormErrorOn();
            $this->displayForm(); //redisplay with errors
        } else {
            // do the resolving
            $filePath = $this->buActivity->resolveCalls($this->dsIgnoredADDomains);
            if ($filePath) {
                $this->setFormErrorMessage('Calls resolved and logged to ' . $filePath);
            } else {
                $this->setFormErrorMessage('No calls to resolve');
            }
            $this->displayForm();
        }
    }

    function parsePage()
    {
        $urlLogo = '';
        $this->template->set_var(
            array(
                'urlLogo' => $urlLogo,
                'txtHome' => 'Home'
            )
        );
        parent::parsePage();
    }
}// end of class
?>