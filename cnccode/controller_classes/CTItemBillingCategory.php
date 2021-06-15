<?php
global $cfg;
require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_dbe'] . '/DBEItemBillingCategory.php');


class CTItemBillingCategory extends CTCNC
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
        $this->setMenuId(807);
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

                $dbeItemBillingCategory = new DBEItemBillingCategory($this);

                $dbeItemBillingCategory->getRow($this->getParam('id'));

                if (!$dbeItemBillingCategory->rowCount) {
                    http_response_code(404);
                    exit;
                }
                $dbeItemBillingCategory->deleteRow();
                echo json_encode(["status" => "ok"]);
                break;
            case 'update':

                if (!$this->getParam('id')) {
                    throw new Exception('ID is missing');
                }

                $dbeItemBillingCategory = new DBEItemBillingCategory($this);

                $dbeItemBillingCategory->getRow($this->getParam('id'));

                if (!$dbeItemBillingCategory->rowCount) {
                    http_response_code(404);
                    exit;
                }

                $dbeItemBillingCategory->setValue(
                    DBEItemBillingCategory::name,
                    $this->getParam('name')
                );
                $dbeItemBillingCategory->setValue(
                    DBEItemBillingCategory::arrearsBilling,
                    json_decode($this->getParam('arrearsBilling'))
                );
                $dbeItemBillingCategory->updateRow();
                echo json_encode(["status" => "ok"]);
                break;
            case 'create':
                $dbeItemBillingCategory = new DBEItemBillingCategory($this);

                $dbeItemBillingCategory->setValue(
                    DBEItemBillingCategory::name,
                    $this->getParam('name')
                );
                $dbeItemBillingCategory->setValue(
                    DBEItemBillingCategory::arrearsBilling,
                    json_decode($this->getParam('arrearsBilling'))
                );
                $dbeItemBillingCategory->insertRow();

                echo json_encode(
                    [
                        "id"             => $dbeItemBillingCategory->getValue(DBEItemBillingCategory::id),
                        "name"           => $dbeItemBillingCategory->getValue(DBEItemBillingCategory::name),
                        "arrearsBilling" => $dbeItemBillingCategory->getValue(DBEItemBillingCategory::arrearsBilling)
                    ],
                    JSON_NUMERIC_CHECK
                );

                break;
            case 'getData':
                $dbeItemBillingCategories = new DBEItemBillingCategory($this);
                $dbeItemBillingCategories->getRows(DBEItemBillingCategory::name);
                $data = [];
                while ($dbeItemBillingCategories->fetchNext()) {
                    $data[] = [
                        "id"             => $dbeItemBillingCategories->getValue(DBEItemBillingCategory::id),
                        "name"           => $dbeItemBillingCategories->getValue(DBEItemBillingCategory::name),
                        "arrearsBilling" => $dbeItemBillingCategories->getValue(DBEItemBillingCategory::arrearsBilling)
                    ];
                }
                echo json_encode($data, JSON_NUMERIC_CHECK);
                break;
            case 'searchName':
                $term = '';
                if (isset($_REQUEST['term'])) {
                    $term = $_REQUEST['term'];
                }
                $dbeItemBillingCategories = new DBEItemBillingCategory($this);
                $dbeItemBillingCategories->getRows(DBEItemBillingCategory::name);
                $data = [];
                while ($dbeItemBillingCategories->fetchNext()) {
                    if (preg_match(
                        '/.*' . $term . '.*/i',
                        $dbeItemBillingCategories->getValue(DBEItemBillingCategory::name)
                    )) {
                        $data[] = [
                            "name" => $dbeItemBillingCategories->getValue(DBEItemBillingCategory::name),
                            "id"   => $dbeItemBillingCategories->getValue(DBEItemBillingCategory::id),
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
        $this->setPageTitle('Item Billing Category');
        $this->setTemplateFiles(
            'ItemBillingCategory',
            'ItemBillingCategories'
        );

        $this->template->parse(
            'CONTENTS',
            'ItemBillingCategory',
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
}
