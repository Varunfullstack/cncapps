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
        if (!self::isSdManager()) {
            Header("Location: /NotAllowed.php");
            exit;
        }
        $this->setMenuId(221);
        $this->buActivity = new BUActivity($this);
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
                    DBEOffice365License::replacement,
                    $this->getParam('replacement')
                );
                $dbeOffice365License->setValue(
                    DBEOffice365License::mailboxLimit,
                    $this->getParam('mailboxLimit')
                );
                $dbeOffice365License->setValue(DBEOffice365License::license, $this->getParam('license'));
                $dbeOffice365License->setValue(
                    DBEOffice365License::reportOnSpareLicenses,
                    json_decode($this->getParam('reportOnSpareLicenses'))
                );
                $dbeOffice365License->setValue(
                    DBEOffice365License::includesDefender,
                    json_decode($this->getParam('includesDefender'))
                );
                $dbeOffice365License->setValue(
                    DBEOffice365License::includesOffice,
                    json_decode($this->getParam('includesOffice'))
                );
                $dbeOffice365License->updateRow();
                echo json_encode(["status" => "ok"]);
                break;
            case 'create':
                $dbeOffice365License = new DBEOffice365License($this);
                $dbeOffice365License->setValue(
                    DBEOffice365License::replacement,
                    $this->getParam('replacement')
                );
                $dbeOffice365License->setValue(
                    DBEOffice365License::mailboxLimit,
                    $this->getParam('mailboxLimit')
                );
                $dbeOffice365License->setValue(DBEOffice365License::license, $this->getParam('license'));
                $dbeOffice365License->setValue(
                    DBEOffice365License::reportOnSpareLicenses,
                    (bool)$this->getParam('reportOnSpareLicenses')
                );
                $dbeOffice365License->setValue(
                    DBEOffice365License::includesDefender,
                    (bool)$this->getParam('includesDefender')
                );
                $dbeOffice365License->setValue(
                    DBEOffice365License::includesDefender,
                    (bool)$this->getParam('includesOffice')
                );
                $dbeOffice365License->insertRow();
                echo json_encode(
                    [
                        "id"                    => $dbeOffice365License->getValue(DBEOffice365License::id),
                        "replacement"           => $dbeOffice365License->getValue(DBEOffice365License::replacement),
                        "license"               => $dbeOffice365License->getValue(DBEOffice365License::license),
                        "mailboxLimit"          => $dbeOffice365License->getValue(DBEOffice365License::mailboxLimit),
                        "reportOnSpareLicenses" => $dbeOffice365License->getValue(
                            DBEOffice365License::reportOnSpareLicenses
                        ),
                        "includesDefender"      => $dbeOffice365License->getValue(
                            DBEOffice365License::includesDefender
                        ),
                        "includesOffice"        => $dbeOffice365License->getValue(DBEOffice365License::includesOffice),
                    ],
                    JSON_NUMERIC_CHECK
                );
                break;
            case 'getData':
                $dbeOffice365Licenses = new DBEOffice365License($this);
                $dbeOffice365Licenses->getRows(DBEOffice365License::replacement);
                $data = [];
                while ($dbeOffice365Licenses->fetchNext()) {
                    $data[] = [
                        "id"                    => $dbeOffice365Licenses->getValue(DBEOffice365License::id),
                        "replacement"           => $dbeOffice365Licenses->getValue(DBEOffice365License::replacement),
                        "license"               => $dbeOffice365Licenses->getValue(DBEOffice365License::license),
                        "mailboxLimit"          => $dbeOffice365Licenses->getValue(DBEOffice365License::mailboxLimit),
                        "reportOnSpareLicenses" => $dbeOffice365Licenses->getValue(
                            DBEOffice365License::reportOnSpareLicenses
                        ),
                        "includesDefender"      => $dbeOffice365Licenses->getValue(
                            DBEOffice365License::includesDefender
                        ),
                        "includesOffice"        => $dbeOffice365Licenses->getValue(
                            DBEOffice365License::includesOffice
                        )
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
        $this->setPageTitle('Office 365 Licenses');
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

    function update()
    {
        $this->defaultAction();
    }

//    function parsePage()
//    {
//        $urlLogo = '';
//        $this->template->set_var(
//            array(
//                'urlLogo' => $urlLogo,
//                'txtHome' => 'Home'
//            )
//        );
//        parent::parsePage();
//    }
}// end of class
