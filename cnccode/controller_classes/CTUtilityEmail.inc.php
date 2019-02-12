<?php
/**
 * Expense controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_dbe'] . '/DBEUtilityEmail.inc.php');
require_once($cfg['path_bu'] . '/BUActivity.inc.php');

// Actions
class CTUtilityEmail extends CTCNC
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

                $dbeUtilityEmail = new DBEUtilityEmail($this);

                $dbeUtilityEmail->getRow($_REQUEST['id']);

                if (!$dbeUtilityEmail->rowCount) {
                    http_response_code(404);
                    exit;
                }
                $dbeUtilityEmail->setLogSQLOn();
                $dbeUtilityEmail->deleteRow();
                echo json_encode(["status" => "ok"]);
                break;
            case 'update':

                if (!isset($_REQUEST['id'])) {
                    throw new Exception('ID is missing');
                }

                $dbeUtilityEmail = new DBEUtilityEmail($this);

                $dbeUtilityEmail->getRow($_REQUEST['id']);

                if (!$dbeUtilityEmail->rowCount) {
                    http_response_code(404);
                    exit;
                }

                $dbeUtilityEmail->setValue(
                    DBEUtilityEmail::firstPart,
                    $_REQUEST['firstPart']
                );
                $dbeUtilityEmail->setValue(
                    DBEUtilityEmail::lastPart,
                    $_REQUEST['lastPart']
                );

                $dbeUtilityEmail->updateRow();
                echo json_encode(["status" => "ok"]);
                break;
            case 'create':
                $dbeUtilityEmail = new DBEUtilityEmail($this);

                $dbeUtilityEmail->setValue(
                    DBEUtilityEmail::firstPart,
                    $_REQUEST['firstPart']
                );
                $dbeUtilityEmail->setValue(
                    DBEUtilityEmail::lastPart,
                    $_REQUEST['lastPart']
                );

                $dbeUtilityEmail->insertRow();

                echo json_encode(
                    [
                        "id"        => $dbeUtilityEmail->getValue(DBEUtilityEmail::utilityEmailID),
                        "firstPart" => $dbeUtilityEmail->getValue(DBEUtilityEmail::firstPart),
                        "lastPart"  => $dbeUtilityEmail->getValue(DBEUtilityEmail::lastPart)
                    ], JSON_NUMERIC_CHECK
                );

                break;
            case 'getData':
                $dbeUtilityEmails = new DBEUtilityEmail($this);

                $dbeUtilityEmails->getRows();
                $data = [];
                while ($dbeUtilityEmails->fetchNext()) {
                    $data[] = [
                        "id"        => $dbeUtilityEmails->getValue(DBEUtilityEmail::utilityEmailID),
                        "firstPart" => $dbeUtilityEmails->getValue(DBEUtilityEmail::firstPart),
                        "lastPart"  => $dbeUtilityEmails->getValue(DBEUtilityEmail::lastPart)
                    ];
                }
                echo json_encode($data, JSON_NUMERIC_CHECK);
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
        $this->setPageTitle('Utility Emails');
        $this->setTemplateFiles(
            'UtilityEmail',
            'UtilityEmail.inc'
        );

        $this->template->parse(
            'CONTENTS',
            'UtilityEmail',
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
        $this->buActivity->initialiseResolveForm($this->dsUtilityEmail);
        if (!$this->dsUtilityEmail->populateFromArray($_REQUEST['UtilityEmail'])) {
            $this->setFormErrorOn();
            $this->displayForm(); //redisplay with errors
        } else {
            // do the resolving
            $filePath = $this->buActivity->resolveCalls($this->dsUtilityEmail);
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