<?php
global $cfg;
require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_dbe'] . '/DBECustomerLeadStatus.php');

class CTLeadStatusTypes extends CTCNC
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
        $roles = MAINTENANCE_PERMISSION;
        if (!self::hasPermissions($roles)) {
            Header("Location: /NotAllowed.php");
            exit;
        }
        $this->setMenuId(811);

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
            case 'top':
            case 'bottom':
            case 'down':
            case 'up':
                $this->changeOrder();
                echo json_encode(["status" => "ok"]);
                break;
            case 'delete':
                if (!$this->getParam('id')) {
                    http_response_code(400);
                    throw new Exception('ID is missing');
                }

                $dBECustomerLeadStatus = new DBECustomerLeadStatus($this);

                $dBECustomerLeadStatus->getRow($this->getParam('id'));

                if (!$dBECustomerLeadStatus->rowCount) {
                    http_response_code(404);
                    exit;
                }
                $dBECustomerLeadStatus->deleteRow();
                echo json_encode(["status" => "ok"]);
                break;
            case 'update':

                if (!$this->getParam('id')) {
                    throw new Exception('ID is missing');
                }

                $dBECustomerLeadStatus = new DBECustomerLeadStatus($this);

                $dBECustomerLeadStatus->getRow($this->getParam('id'));

                if (!$dBECustomerLeadStatus->rowCount) {
                    http_response_code(404);
                    exit;
                }

                $dBECustomerLeadStatus->setValue(
                    DBECustomerLeadStatus::name,
                    $this->getParam('name')
                );
                $dBECustomerLeadStatus->setValue(
                    DBECustomerLeadStatus::appearOnScreen,
                    json_decode($this->getParam('appearOnScreen'))
                );
                $dBECustomerLeadStatus->updateRow();
                echo json_encode(["status" => "ok"]);
                break;
            case 'create':
                $dBECustomerLeadStatus = new DBECustomerLeadStatus($this);
                $dBECustomerLeadStatus->setValue(
                    DBECustomerLeadStatus::name,
                    $this->getParam('name')
                );
                $dBECustomerLeadStatus->setValue(
                    DBECustomerLeadStatus::appearOnScreen,
                    json_decode($this->getParam('appearOnScreen'))
                );
                $dBECustomerLeadStatus->setValue(
                    DBECustomerLeadStatus::sortOrder,
                    $dBECustomerLeadStatus->getNextSortOrder()
                );
                $dBECustomerLeadStatus->insertRow();
                echo json_encode(
                    [
                        "id"             => $dBECustomerLeadStatus->getValue(DBECustomerLeadStatus::id),
                        "name"           => $dBECustomerLeadStatus->getValue(DBECustomerLeadStatus::name),
                        "appearOnScreen" => $dBECustomerLeadStatus->getValue(DBECustomerLeadStatus::appearOnScreen),
                        "sortOrder"      => $dBECustomerLeadStatus->getValue(DBECustomerLeadStatus::sortOrder)
                    ],
                    JSON_NUMERIC_CHECK
                );

                break;
            case 'getData':
                $dbeCustomerLeadStatus = new DBECustomerLeadStatus($this);
                $dbeCustomerLeadStatus->getRows(DBECustomerLeadStatus::sortOrder);
                $data = [];
                while ($dbeCustomerLeadStatus->fetchNext()) {
                    $data[] = [
                        "id"             => $dbeCustomerLeadStatus->getValue(DBECustomerLeadStatus::id),
                        "name"           => $dbeCustomerLeadStatus->getValue(DBECustomerLeadStatus::name),
                        "appearOnScreen" => $dbeCustomerLeadStatus->getValue(DBECustomerLeadStatus::appearOnScreen),
                        "sortOrder"      => $dbeCustomerLeadStatus->getValue(DBECustomerLeadStatus::sortOrder)
                    ];
                }
                echo json_encode($data, JSON_NUMERIC_CHECK);
                break;
            case 'searchName':
                $term = '';
                if (isset($_REQUEST['term'])) {
                    $term = $_REQUEST['term'];
                }
                $dbeCustomerLeadStatus = new DBECustomerLeadStatus($this);
                $dbeCustomerLeadStatus->getRows(DBECustomerLeadStatus::name);
                $data = [];
                while ($dbeCustomerLeadStatus->fetchNext()) {
                    if (preg_match(
                        '/.*' . $term . '.*/i',
                        $dbeCustomerLeadStatus->getValue(DBECustomerLeadStatus::name)
                    )) {
                        $data[] = [
                            "name" => $dbeCustomerLeadStatus->getValue(DBECustomerLeadStatus::name),
                            "id"   => $dbeCustomerLeadStatus->getValue(DBECustomerLeadStatus::id),
                        ];
                    }
                }
                echo json_encode($data);
                break;
            case 'displayForm':
            default:
                $this->displayForm();
                break;
        }
    }

    private function changeOrder()
    {
        $itemId = $this->getParam('itemID');
        if (!$itemId) {
            return;
        }
        $dbeItemType = new DBECustomerLeadStatus($this);
        switch ($this->action) {
            case 'top':
                $dbeItemType->moveItemToTop($itemId);
                break;
            case 'bottom':
                $dbeItemType->moveItemToBottom($itemId);
                break;
            case 'down':
                $dbeItemType->moveItemDown($itemId);
                break;
            case 'up':
                $dbeItemType->moveItemUp($itemId);
                break;
            default:
                throw new UnexpectedValueException('value not expected');
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
        $this->setPageTitle('Lead Status');
        $this->setTemplateFiles(
            'LeadStatus',
            'LeadStatusTypes'
        );

        $this->template->parse(
            'CONTENTS',
            'LeadStatus',
            true
        );
        $this->loadReactScript('LeadStatusTypesComponent.js');
        $this->loadReactCSS('LeadStatusTypesComponent.css');

        // $URLDeleteItem = Controller::buildLink(
        //     $_SERVER['PHP_SELF'],
        //     [
        //         'action' => 'delete'
        //     ]
        // );

        // $URLUpdateItem = Controller::buildLink(
        //     $_SERVER['PHP_SELF'],
        //     [
        //         'action' => 'update'
        //     ]
        // );

        // $URLCreateItem = Controller::buildLink(
        //     $_SERVER['PHP_SELF'],
        //     [
        //         'action' => 'create'
        //     ]
        // );

        // $URLGetData = Controller::buildLink(
        //     $_SERVER['PHP_SELF'],
        //     [
        //         'action' => 'getData'
        //     ]
        // );
        // $this->template->setVar(
        //     [
        //         "URLDeleteItem" => $URLDeleteItem,
        //         "URLUpdateItem" => $URLUpdateItem,
        //         "URLAddItem"    => $URLCreateItem,
        //         "URLGetData"    => $URLGetData
        //     ]
        // );

        $this->parsePage();
    }

    function update()
    {
        $this->defaultAction();
    }
}
