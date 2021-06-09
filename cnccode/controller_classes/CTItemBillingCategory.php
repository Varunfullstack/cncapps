<?php
global $cfg;

use CNCLTD\Exceptions\APIException;

require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_dbe'] . '/DBEItemBillingCategory.php');


class CTItemBillingCategory extends CTCNC
{
    function __construct(
        $requestMethod,
        $postVars,
        $getVars,
        $cookieVars,
        $cfg
    ) {
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
            case 'json':
                switch ($this->requestMethod) {
                    case 'GET':
                        echo  json_encode($this->getItemBillingCategory(), JSON_NUMERIC_CHECK);
                        break;
                    case 'POST':
                        echo  json_encode($this->addItemBillingCategory(), JSON_NUMERIC_CHECK);
                        break;
                    case 'PUT':
                        echo  json_encode($this->updateItemBillingCategory(), JSON_NUMERIC_CHECK);
                        break;
                    case 'DELETE':
                        echo  json_encode($this->deleteItemBillingCategory(), JSON_NUMERIC_CHECK);
                        break;
                    default:
                        # code...
                        break;
                }
                exit;

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
     * Display list of types
     * @access private
     * @throws Exception
     */
    function displayForm()
    {
        $this->setPageTitle('Item Billing Category');
        $this->setTemplateFiles(
            array('form' => 'ItemBillingCategories')
        );
        $this->loadReactScript('ItemBillingCategoryComponent.js');
        $this->loadReactCSS('ItemBillingCategoryComponent.css');
        $this->template->parse(
            'CONTENTS',
            'form',
            true
        );
        $this->parsePage();
    }
    
    function getItemBillingCategory()
    {
        $DBEItemBillingCategory = new DBEItemBillingCategory($this);
        $DBEItemBillingCategory->getRows(); // DBEItemBillingCategory::sortOrder
        $data = [];
        while ($DBEItemBillingCategory->fetchNext()) {
            $data[] = [
                "id"              => $DBEItemBillingCategory->getValue(DBEItemBillingCategory::id),
                "name"            => $DBEItemBillingCategory->getValue(DBEItemBillingCategory::name),
                "arrearsBilling"  => $DBEItemBillingCategory->getValue(DBEItemBillingCategory::arrearsBilling),
            ];
        }
        return $this->success($data);
    }

    function addItemBillingCategory()
    {
        $body = $this->getBody();
        $DBEItemBillingCategory = new DBEItemBillingCategory($this);
        $DBEItemBillingCategory->setValue(DBEItemBillingCategory::name, $body->name);
        $DBEItemBillingCategory->setValue(DBEItemBillingCategory::arrearsBilling, $body->arrearsBilling);
        $DBEItemBillingCategory->insertRow();
        return $this->success();
    }

    function updateItemBillingCategory()
    {
        $body = $this->getBody();
        if (!isset($body->id))
            return $this->fail(APIException::badRequest, "Bad Request");

        $DBEItemBillingCategory = new DBEItemBillingCategory($this);
        $DBEItemBillingCategory->getRow($body->id);

        if (!$DBEItemBillingCategory->rowCount)
            return $this->fail(APIException::notFound, "Not Found");

        $DBEItemBillingCategory->setValue(DBEItemBillingCategory::name, $body->name);
        $DBEItemBillingCategory->setValue(DBEItemBillingCategory::arrearsBilling, $body->arrearsBilling);
        $DBEItemBillingCategory->updateRow();
        return $this->success();
    }

    function deleteItemBillingCategory()
    {
        $id = @$_REQUEST['id'];

        if (!$id)
            return $this->fail(APIException::notFound, "Id is Missing");

        $DBEItemBillingCategory = new DBEItemBillingCategory($this);
        $DBEItemBillingCategory->getRow($id);
        if (!$DBEItemBillingCategory->rowCount) {
            return $this->fail(APIException::notFound, "Not Found");
        }
        $DBEItemBillingCategory->deleteRow();
        return $this->success();
    }
}
