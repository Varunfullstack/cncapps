<?php
/**
 * Expense controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */

use CNCLTD\Business\BUActivity;

global $cfg;
require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_dbe'] . '/DBEUtilityEmail.inc.php');

// Actions
class CTUtilityEmail extends CTCNC
{
    /** @var BUActivity */
    public $buActivity;

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
        if (!self::isSdManager()) {
            Header("Location: /NotAllowed.php");
            exit;
        }
        $this->setMenuId(217);
        $this->buActivity = new BUActivity($this);
    }

    /**
     * Route to function based upon action passed
     * @throws Exception
     * @throws Exception
     */
    function defaultAction()
    {
        switch ($this->getAction()) {
            case 'delete':
                if (!$this->getParam('id')) {
                    http_response_code(400);
                    throw new Exception('ID is missing');
                }
                $dbeUtilityEmail = new DBEUtilityEmail($this);
                $dbeUtilityEmail->getRow($this->getParam('id'));
                if (!$dbeUtilityEmail->rowCount) {
                    http_response_code(404);
                    exit;
                }
                $dbeUtilityEmail->setLogSQLOn();
                $dbeUtilityEmail->deleteRow();
                echo json_encode(["status" => "ok"]);
                break;
            case 'update':
                if (!$this->getParam('id')) {
                    throw new Exception('ID is missing');
                }
                $dbeUtilityEmail = new DBEUtilityEmail($this);
                $dbeUtilityEmail->getRow($this->getParam('id'));
                if (!$dbeUtilityEmail->rowCount) {
                    http_response_code(404);
                    exit;
                }
                $dbeUtilityEmail->setValue(
                    DBEUtilityEmail::firstPart,
                    $this->getParam('firstPart')
                );
                $dbeUtilityEmail->setValue(
                    DBEUtilityEmail::lastPart,
                    $this->getParam('lastPart')
                );
                $dbeUtilityEmail->updateRow();
                echo json_encode(["status" => "ok"]);
                break;
            case 'create':
                $dbeUtilityEmail = new DBEUtilityEmail($this);
                $dbeUtilityEmail->setValue(
                    DBEUtilityEmail::firstPart,
                    $this->getParam('firstPart')
                );
                $dbeUtilityEmail->setValue(
                    DBEUtilityEmail::lastPart,
                    $this->getParam('lastPart')
                );
                $dbeUtilityEmail->insertRow();
                echo json_encode(
                    [
                        "id"        => $dbeUtilityEmail->getValue(DBEUtilityEmail::utilityEmailID),
                        "firstPart" => $dbeUtilityEmail->getValue(DBEUtilityEmail::firstPart),
                        "lastPart"  => $dbeUtilityEmail->getValue(DBEUtilityEmail::lastPart)
                    ],
                    JSON_NUMERIC_CHECK
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
     * @throws Exception
     * @throws Exception
     * @throws Exception
     * @throws Exception
     * @throws Exception
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
        $URLDeleteItem = Controller::buildLink(
            $_SERVER['PHP_SELF'],
            [
                'action' => 'delete'
            ]
        );
        $URLUpdateItem = Controller::buildLink(
            $_SERVER['PHP_SELF'],
            [
                'action' => 'update'
            ]
        );
        $URLCreateItem = Controller::buildLink(
            $_SERVER['PHP_SELF'],
            [
                'action' => 'create'
            ]
        );
        $URLGetData    = Controller::buildLink(
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
