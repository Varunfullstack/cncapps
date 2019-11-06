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
require_once($cfg['path_dbe'] . '/DBEItemType.inc.php');

// Actions
class CTItemType extends CTCNC
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
            "maintenance",
        ];
        if (!self::hasPermissions($roles)) {
            Header("Location: /NotAllowed.php");
            exit;
        }
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

                $DBEItemType = new DBEItemType($this);

                $DBEItemType->getRow($this->getParam('id'));

                if (!$DBEItemType->rowCount) {
                    http_response_code(404);
                    exit;
                }
                $DBEItemType->deleteRow();
                echo json_encode(["status" => "ok"]);
                break;
            case 'update':

                if (!$this->getParam('id')) {
                    throw new Exception('ID is missing');
                }

                $DBEItemType = new DBEItemType($this);

                $DBEItemType->getRow($this->getParam('id'));

                if (!$DBEItemType->rowCount) {
                    http_response_code(404);
                    exit;
                }

                $DBEItemType->setValue(
                    DBEItemType::replacement,
                    $this->getParam('replacement')
                );
                $DBEItemType->setValue(
                    DBEItemType::mailboxLimit,
                    $this->getParam('mailboxLimit')
                );

                $DBEItemType->setValue(DBEItemType::license, $this->getParam('license'));
                $DBEItemType->setValue(
                    DBEItemType::reportOnSpareLicenses,
                    !!$this->getParam('reportOnSpareLicenses')
                );

                $DBEItemType->updateRow();
                echo json_encode(["status" => "ok"]);
                break;
            case 'create':
                $DBEItemType = new DBEItemType($this);

                $DBEItemType->setValue(
                    DBEItemType::replacement,
                    $this->getParam('replacement')
                );
                $DBEItemType->setValue(
                    DBEItemType::mailboxLimit,
                    $this->getParam('mailboxLimit')
                );

                $DBEItemType->setValue(DBEItemType::license, $this->getParam('license'));
                $DBEItemType->setValue(
                    DBEItemType::reportOnSpareLicenses,
                    !!$this->getParam('reportOnSpareLicenses')
                );
                $DBEItemType->insertRow();

                echo json_encode(
                    [
                        "id"                    => $DBEItemType->getValue(DBEItemType::id),
                        "replacement"           => $DBEItemType->getValue(DBEItemType::replacement),
                        "license"               => $DBEItemType->getValue(DBEItemType::license),
                        "mailboxLimit"          => $DBEItemType->getValue(DBEItemType::mailboxLimit),
                        "reportOnSpareLicenses" => $DBEItemType->getValue(
                            DBEItemType::reportOnSpareLicenses
                        )
                    ],
                    JSON_NUMERIC_CHECK
                );

                break;
            case 'getData':
                $DBEItemTypes = new DBEItemType($this);

                $DBEItemTypes->getRows(DBEItemType::description);
                $data = [];
                while ($DBEItemTypes->fetchNext()) {
                    $data[] = [
                        "itemTypeID"  => $DBEItemTypes->getValue(DBEItemType::itemTypeID),
                        "description" => $DBEItemTypes->getValue(DBEItemType::description),
                        "active"      => $DBEItemTypes->getValue(DBEItemType::active),
                        "reocurring"  => $DBEItemTypes->getValue(DBEItemType::reocurring),
                        "stockcat"    => $DBEItemTypes->getValue(DBEItemType::stockcat),
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
        $this->setPageTitle('Item Types');
        $this->setTemplateFiles(
            'ItemType',
            'ItemTypes'
        );

        $this->template->parse(
            'CONTENTS',
            'ItemType',
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
