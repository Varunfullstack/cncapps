<?php
/**
 * Standard Text controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */

global $cfg;
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
                $toUpdateItem->setValue(
                    DBESRScheduler::hideFromCustomer,
                    !!json_decode($this->getParam('hideFromCustomer'))
                );
                $toUpdateItem->setValue(DBESRScheduler::teamId, $this->getParam('teamId'));
                $toUpdateItem->setValue(DBESRScheduler::details, $this->getParam('details'));
                $toUpdateItem->setValue(DBESRScheduler::internalNotes, $this->getParam('internalNotes'));
                $toUpdateItem->setValue(DBESRScheduler::updatedBy, $this->userID);
                $toUpdateItem->setValue(DBESRScheduler::updatedAt, (new DateTime())->format(DATE_MYSQL_DATE));

                // before we update we want to test the rule

                try {
                    $rrule = new \RRule\RRule($this->getParam('rruleString'));
                } catch (\Exception $exception) {
                    echo json_encode(["status" => "error", "error" => $exception->getMessage()]);
                    http_response_code(400);
                    exit;
                }

                $toUpdateItem->updateRow();
                echo json_encode(["status" => "ok"]);
                break;
            case 'create':
                $newItem = new DBESRScheduler($this);
                $newItem->setValue(DBESRScheduler::customerId, $this->getParam('customerId'));
                $newItem->setValue(DBESRScheduler::rruleString, $this->getParam('rruleString'));
                $newItem->setValue(DBESRScheduler::contactId, $this->getParam('contactId'));
                $newItem->setValue(DBESRScheduler::siteNo, $this->getParam('siteNo'));
                $newItem->setValue(DBESRScheduler::priority, $this->getParam('priority'));
                $newItem->setValue(DBESRScheduler::hideFromCustomer, !!$this->getParam('hideFromCustomer'));
                $newItem->setValue(DBESRScheduler::teamId, $this->getParam('teamId'));
                $newItem->setValue(DBESRScheduler::details, $this->getParam('details'));
                $newItem->setValue(DBESRScheduler::internalNotes, $this->getParam('internalNotes'));
                $newItem->setValue(DBESRScheduler::createdBy, $this->userID);
                $newItem->setValue(DBESRScheduler::updatedBy, $this->userID);
                $newItem->setValue(DBESRScheduler::createdAt, (new DateTime())->format(DATE_MYSQL_DATETIME));
                $newItem->setValue(DBESRScheduler::updatedAt, (new DateTime())->format(DATE_MYSQL_DATETIME));

                try {
                    $rrule = new \RRule\RRule($this->getParam('rruleString'));
                } catch (\Exception $exception) {
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
                echo json_encode(
                    $result,
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
        $row = $dbeSrScheduler->jsonSerialize();
        $customerId = $dbeSrScheduler->getValue(DBESRScheduler::customerId);
        $row['customerName'] = $this->getAssociatedObjectData(
            $customerId,
            'customers',
            function ($id) {
                $dbeCustomer = new DBECustomer($this);
                $dbeCustomer->getRow($id);
                return $dbeCustomer->getValue(DBECustomer::name);
            }
        );
        $siteNo = $dbeSrScheduler->getValue(DBESRScheduler::siteNo);
        $siteKey = "$customerId-$siteNo";
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

        $contactId = $dbeSrScheduler->getValue(DBESRScheduler::contactId);
        $row['contactName'] = $this->getAssociatedObjectData(
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
        $createdById = $dbeSrScheduler->getValue(DBESRScheduler::createdBy);
        $getUserNameCB = function ($id) {
            $dbeUser = new DBEUser($this);
            $dbeUser->getRow($id);
            return $dbeUser->getValue(DBEUser::name);
        };

        $row['createdByName'] = $this->getAssociatedObjectData(
            $createdById,
            'users',
            $getUserNameCB
        );

        $updatedById = $dbeSrScheduler->getValue(DBESRScheduler::updatedBy);

        $row['updatedByName'] = $this->getAssociatedObjectData(
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


        $this->template->parse('CONTENTS', 'SRSchedulerList', true);
        $this->parsePage();
    }

    function update()
    {
        $this->defaultAction();
    }
}// end of class