<?php
/**
 * Expense controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_dbe'] . '/DBEOffice365License.php');
require_once($cfg['path_bu'] . '/BUActivity.inc.php');

// Actions
class CTOffice365Licenses extends CTCNC
{
    /** @var BUActivity */
    public $buActivity;
    public $dsOffice365License;

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

                $dbeOffice365License = new DBEOffice365License($this);

                $dbeOffice365License->getRow($this->getParam('id'));

                if (!$dbeOffice365License->rowCount) {
                    http_response_code(404);
                    exit;
                }
                $dbeOffice365License->setLogSQLOn();
                $dbeOffice365License->deleteRow();
                echo json_encode(["status" => "ok"]);
                break;
            case 'update':

                if (!$this->getParam('id')) {
                    throw new Exception('ID is missing');
                }

                $dbeOffice365License = new DBEOffice365License($this);

                $dbeOffice365License->getRow($this->getParam('id'));

                if (!$dbeOffice365License->rowCount) {
                    http_response_code(404);
                    exit;
                }

                $dbeOffice365License->setValue(
                    DBEOffice365License::firstPart,
                    $this->getParam('firstPart')
                );
                $dbeOffice365License->setValue(
                    DBEOffice365License::lastPart,
                    $this->getParam('lastPart')
                );

                $dbeOffice365License->updateRow();
                echo json_encode(["status" => "ok"]);
                break;
            case 'create':
                $dbeOffice365License = new DBEOffice365License($this);

                $dbeOffice365License->setValue(
                    DBEOffice365License::firstPart,
                    $this->getParam('firstPart')
                );
                $dbeOffice365License->setValue(
                    DBEOffice365License::lastPart,
                    $this->getParam('lastPart')
                );

                $dbeOffice365License->insertRow();

                echo json_encode(
                    [
                        "id"        => $dbeOffice365License->getValue(DBEOffice365License::id),
                        "firstPart" => $dbeOffice365License->getValue(DBEOffice365License::firstPart),
                        "lastPart"  => $dbeOffice365License->getValue(DBEOffice365License::lastPart)
                    ],
                    JSON_NUMERIC_CHECK
                );

                break;
            case 'getData':
                $dbeOffice365Licenses = new DBEOffice365License($this);

                $dbeOffice365Licenses->getRows();
                $data = [];
                while ($dbeOffice365Licenses->fetchNext()) {
                    $data[] = [
                        "id"        => $dbeOffice365Licenses->getValue(DBEOffice365License::id),
                        "firstPart" => $dbeOffice365Licenses->getValue(DBEOffice365License::firstPart),
                        "lastPart"  => $dbeOffice365Licenses->getValue(DBEOffice365License::lastPart)
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
            'Office365License',
            'Office365Licenses'
        );

        $this->template->parse(
            'CONTENTS',
            'Office365License',
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

        $URLGetData = Controller::buildLink(
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
