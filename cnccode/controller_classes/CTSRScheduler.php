<?php
/**
 * Standard Text controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
global $cfg;

use CNCLTD\Exceptions\ColumnOutOfRangeException;
use RRule\RRule;

require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_dbe'] . '/DBESRScheduler.php');
require_once($cfg['path_dbe'] . '/DBECustomer.inc.php');
require_once($cfg['path_dbe'] . '/DBEContact.inc.php');
require_once($cfg['path_bu'] . '/BUSite.inc.php');

class CTSRScheduler extends CTCNC
{
    private $cache = [];

    function __construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg)
    {
        parent::__construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg);
        if (!self::isSdManager()) {
            Header("Location: /NotAllowed.php");
            exit;
        }
        $this->setMenuId(205);
    }

    function delete()
    {
        $this->defaultAction();
    }

    /**
     * Route to function based upon action passed
     * @throws Exception
     */
    function defaultAction()
    {
        switch ($this->getAction()) {
            case
            'delete':
                if (!$this->getParam('id')) {
                    http_response_code(400);
                    throw new Exception('ID is missing');
                }
                $toDeleteItem = new DBESRScheduler($this);
                $toDeleteItem->getRow($this->getParam('id'));
                if (!$toDeleteItem->rowCount) {
                    http_response_code(404);
                    exit;
                }
                $toDeleteItem->deleteRow();
                echo json_encode(["status" => "ok"]);
                break;
            case 'update':
                if (!$this->getParam('id')) {
                    throw new Exception('ID is missing');
                }
                $toUpdateItem = new DBESRScheduler($this);
                $toUpdateItem->getRow($this->getParam('id'));
                if (!$toUpdateItem->rowCount) {
                    http_response_code(404);
                    exit;
                }
                $toUpdateItem->setValue(DBESRScheduler::customerId, $this->getParam('customerId'));
                $toUpdateItem->setValue(DBESRScheduler::rruleString, $this->getParam('rruleString'));
                $toUpdateItem->setValue(DBESRScheduler::contactId, $this->getParam('contactId'));
                $toUpdateItem->setValue(DBESRScheduler::siteNo, $this->getParam('siteNo'));
                $toUpdateItem->setValue(DBESRScheduler::priority, $this->getParam('priority'));
                $toUpdateItem->setValue(DBESRScheduler::linkedSalesOrderId, $this->getParam('linkedSalesOrderId'));
                $toUpdateItem->setValue(
                    DBESRScheduler::hideFromCustomer,
                    (bool)json_decode($this->getParam('hideFromCustomer'))
                );
                $toUpdateItem->setValue(DBESRScheduler::teamId, $this->getParam('teamId'));
                $toUpdateItem->setValue(DBESRScheduler::details, $this->getParam('details'));
                $toUpdateItem->setValue(DBESRScheduler::internalNotes, $this->getParam('internalNotes'));
                $toUpdateItem->setValue(DBESRScheduler::updatedBy, $this->userID);
                $toUpdateItem->setValue(DBESRScheduler::updatedAt, (new DateTime())->format(DATE_MYSQL_DATE));
                $toUpdateItem->setValue(DBESRScheduler::emailSubjectSummary, $this->getParam('emailSubjectSummary'));
                $toUpdateItem->setValue(DBESRScheduler::assetName, $this->getParam('assetName'));
                $toUpdateItem->setValue(DBESRScheduler::assetTitle, $this->getParam('assetTitle'));
                $toUpdateItem->setValue(DBESRScheduler::emptyAssetReason, $this->getParam('emptyAssetReason'));
                // before we update we want to test the rule
                try {
                    $rrule = new RRule($this->getParam('rruleString'));
                } catch (Exception $exception) {
                    echo json_encode(["status" => "error", "error" => $exception->getMessage()]);
                    http_response_code(400);
                    exit;
                }
                $toUpdateItem->updateRow();
                echo json_encode(["status" => "ok"]);
                break;
            case 'checkSalesOrder':
                $data = json_decode(file_get_contents('php://input'), true);
                if (!$data) {
                    echo json_encode(["status" => "error", "error" => "Data is missing"]);
                    http_response_code(400);
                    exit;
                }
                $customerId    = $data['customerId'];
                $salesOrderId  = $data['salesOrderId'];
                $dbeSalesOrder = new DBEOrdhead($this);
                $dbeSalesOrder->getRow($salesOrderId);
                $answer = $dbeSalesOrder->getValue(DBEOrdhead::customerID) == $customerId;
                echo json_encode(["status" => "ok", "data" => $answer]);
                exit;
            case 'create':
                $newItem = new DBESRScheduler($this);
                $newItem->setValue(DBESRScheduler::customerId, $this->getParam('customerId'));
                $newItem->setValue(DBESRScheduler::rruleString, $this->getParam('rruleString'));
                $newItem->setValue(DBESRScheduler::contactId, $this->getParam('contactId'));
                $newItem->setValue(DBESRScheduler::siteNo, $this->getParam('siteNo'));
                $newItem->setValue(DBESRScheduler::priority, $this->getParam('priority'));
                $newItem->setValue(DBESRScheduler::hideFromCustomer, (bool)$this->getParam('hideFromCustomer'));
                $newItem->setValue(DBESRScheduler::teamId, $this->getParam('teamId'));
                $newItem->setValue(DBESRScheduler::details, $this->getParam('details'));
                $newItem->setValue(DBESRScheduler::internalNotes, $this->getParam('internalNotes'));
                $newItem->setValue(DBESRScheduler::linkedSalesOrderId, $this->getParam('linkedSalesOrderId'));
                $newItem->setValue(DBESRScheduler::assetName, $this->getParam('assetName'));
                $newItem->setValue(DBESRScheduler::assetTitle, $this->getParam('assetTitle'));
                $newItem->setValue(DBESRScheduler::emptyAssetReason, $this->getParam('emptyAssetReason'));
                $newItem->setValue(DBESRScheduler::createdBy, $this->userID);
                $newItem->setValue(DBESRScheduler::updatedBy, $this->userID);
                $newItem->setValue(DBESRScheduler::createdAt, (new DateTime())->format(DATE_MYSQL_DATETIME));
                $newItem->setValue(DBESRScheduler::updatedAt, (new DateTime())->format(DATE_MYSQL_DATETIME));
                $newItem->setValue(DBESRScheduler::emailSubjectSummary, $this->getParam('emailSubjectSummary'));
                try {
                    $rrule = new RRule($this->getParam('rruleString'));
                } catch (Exception $exception) {
                    echo json_encode(["status" => "error", "error" => $exception->getMessage()]);
                    http_response_code(400);
                    exit;
                }
                $newItem->insertRow();
                $toReturn = $this->populateSRSchedulerObjectFromDB($newItem);
                echo json_encode(
                    $toReturn,
                    JSON_NUMERIC_CHECK
                );
                break;
            case 'getData':
                $dbeSrScheduler = new DBESRScheduler($this);
                $dbeSrScheduler->getRows();
                $result = [];
                while ($dbeSrScheduler->fetchNext()) {
                    $result[] = $this->populateSRSchedulerObjectFromDB($dbeSrScheduler);
                }
                $draw    = $_REQUEST['draw'];
                $order   = $_REQUEST['order'];
                $columns = $_REQUEST['columns'];
                if (count($order)) {
                    usort(
                        $result,
                        function ($item1, $item2) use ($order, $columns) {
                            $idx = 0;
                            do {
                                $orderItem  = $order[$idx];
                                $columnIdx  = $orderItem['column'];
                                $columnName = $columns[$columnIdx]['name'];
                                if (!array_key_exists($columnName, $item1)) {
                                    throw new ColumnOutOfRangeException($columnName);
                                }
                                $comparison = $item1[$columnName] <=> $item2[$columnName];
                                if ($orderItem['dir'] == 'desc') {
                                    $comparison = -$comparison;
                                }
                                $idx++;
                            } while ($comparison === 0 && $idx < count($order));
                            return $comparison;
                        }
                    );
                }
                echo json_encode(
                    [
                        "draw"            => $draw,
                        "recordsTotal"    => count($result),
                        "recordsFiltered" => count($result),
                        "data"            => $result
                    ],
                    JSON_NUMERIC_CHECK
                );
                break;
            case 'displayList':
            default:
                $this->displayList();
                break;
        }
    }

    /**
     * @param DataSet|DBESRScheduler $dbeSrScheduler
     * @return array|mixed
     */
    private function populateSRSchedulerObjectFromDB($dbeSrScheduler)
    {
        $row                    = $dbeSrScheduler->jsonSerialize();
        $customerId             = $dbeSrScheduler->getValue(DBESRScheduler::customerId);
        $row['customerName']    = $this->getAssociatedObjectData(
            $customerId,
            'customers',
            function ($id) {
                $dbeCustomer = new DBECustomer($this);
                $dbeCustomer->getRow($id);
                return $dbeCustomer->getValue(DBECustomer::name);
            }
        );
        $siteNo                 = $dbeSrScheduler->getValue(DBESRScheduler::siteNo);
        $siteKey                = "$customerId-$siteNo";
        $row['siteDescription'] = $this->getAssociatedObjectData(
            $siteKey,
            'sites',
            function ($id) use ($customerId, $siteNo) {
                $buSite = new BUSite($this);
                $buSite->getSiteByID($customerId, $siteNo, $dsResult);
                return $dsResult->getValue(DBESite::add1) . ' ' . $dsResult->getValue(
                        DBESite::town
                    ) . ' ' . $dsResult->getValue(DBESite::postcode);
            }
        );
        $contactId              = $dbeSrScheduler->getValue(DBESRScheduler::contactId);
        $row['contactName']     = $this->getAssociatedObjectData(
            $contactId,
            'contacts',
            function ($id) {
                $dbeContact = new DBEContact($this);
                $dbeContact->getRow($id);
                return $dbeContact->getValue(
                        DBEContact::firstName
                    ) . " " . $dbeContact->getValue(DBEContact::lastName);
            }
        );
        $createdById            = $dbeSrScheduler->getValue(DBESRScheduler::createdBy);
        $getUserNameCB          = function ($id) {
            $dbeUser = new DBEUser($this);
            $dbeUser->getRow($id);
            return $dbeUser->getValue(DBEUser::name);
        };
        $row['createdByName']   = $this->getAssociatedObjectData(
            $createdById,
            'users',
            $getUserNameCB
        );
        $updatedById            = $dbeSrScheduler->getValue(DBESRScheduler::updatedBy);
        $row['updatedByName']   = $this->getAssociatedObjectData(
            $updatedById,
            'users',
            $getUserNameCB
        );
        return $row;
    }

    private function getAssociatedObjectData($id, $collectionName, callable $getDataFunction)
    {
        if (!isset($this->cache[$collectionName])) {
            $this->cache[$collectionName] = [];
        }
        $collection = $this->cache[$collectionName];
        if (!isset($collection[$id])) {
            $collection[$id] = call_user_func($getDataFunction, $id);
        }
        return $collection[$id];
    }

    /**
     * Display list of types
     * @access private
     * @throws Exception
     */
    function displayList()
    {
        $this->setMethodName('displayList');
        $this->setPageTitle('SR Scheduler');
        $this->setTemplateFiles(
            array('SRSchedulerList' => 'SRSchedulerList')
        );
        $this->loadReactScript('AssetPickerComponent.js');
//        $this->loadReactCSS('AssetPickerComponent.css');
        $this->template->parse('CONTENTS', 'SRSchedulerList', true);
        $this->parsePage();
    }

    function update()
    {
        $this->defaultAction();
    }
}// end of class
