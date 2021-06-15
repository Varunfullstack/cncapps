<?php
/**
 * Expense Type controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */

use CNCLTD\Exceptions\APIException;

require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_bu'] . '/BUExpenseType.inc.php');
require_once($cfg['path_dbe'] . '/DSForm.inc.php');
require_once($cfg['path_dbe'] . '/DBECallActType.inc.php');
// Actions
define('CTEXPENSETYPE_ACT_DISPLAY_LIST', 'expenseTypeList');
define('CTEXPENSETYPE_ACT_CREATE', 'createExpenseType');
define('CTEXPENSETYPE_ACT_EDIT', 'editExpenseType');
define('CTEXPENSETYPE_ACT_DELETE', 'deleteExpenseType');
define('CTEXPENSETYPE_ACT_UPDATE', 'updateExpenseType');

class CTExpenseType extends CTCNC
{
    const CONST_TYPES='types';
    const CONST_EXPENSE_ACTIVITY_TYPES='expenseActivityTypes';
    /** @var DSForm */
    public $dsExpenseType;
    /** @var BUExpenseType */
    public $buExpenseType;

    function __construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg)
    {
        parent::__construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg);
        $roles = MAINTENANCE_PERMISSION;
        if (!self::hasPermissions($roles)) {
            Header("Location: /NotAllowed.php");
            exit;
        }
        $this->setMenuId(804);
        $this->buExpenseType = new BUExpenseType($this);
        $this->dsExpenseType = new DSForm($this);
        $this->dsExpenseType->copyColumnsFrom($this->buExpenseType->dbeExpenseType);
    }

    /**
     * Route to function based upon action passed
     * @throws Exception
     */
    function defaultAction()
    {
        $this->checkPermissions(MAINTENANCE_PERMISSION);
        switch ($this->getAction()) {
            case self::CONST_TYPES:
                switch ($this->requestMethod) {
                    case 'GET':
                        echo  json_encode($this->getTypes(),JSON_NUMERIC_CHECK);
                        break;
                    case 'POST':
                        echo  json_encode($this->addType(),JSON_NUMERIC_CHECK);
                        break;
                    case 'PUT':
                        echo  json_encode($this->updateType(),JSON_NUMERIC_CHECK);
                        break;
                    case 'DELETE':
                        echo  json_encode($this->deleteType(),JSON_NUMERIC_CHECK);
                        break;
                    default:
                        # code...
                        break;
                }
                exit;                 
            case self::CONST_EXPENSE_ACTIVITY_TYPES:
                echo  json_encode($this->getActivitiesForExpenseType(@$_REQUEST['id']),JSON_NUMERIC_CHECK);
                break;           
            case CTEXPENSETYPE_ACT_DISPLAY_LIST:
                echo  json_encode($this->getTypes(),JSON_NUMERIC_CHECK);
                break;
            default:
                $this->setTemplate();
                break;
        }
    }

    /**
     * Display list of types
     * @access private
     * @throws Exception
     */
    function setTemplate()
    {
        $this->setMethodName('displayList');
        $this->setPageTitle('Expense Types');
        $this->setTemplateFiles(
            array('ExpenseTypeList' => 'ExpenseTypeList.inc')
        );
        $this->loadReactScript('ExpenseTypeComponent.js');
        $this->loadReactCSS('ExpenseTypeComponent.css');      
        $this->template->parse('CONTENTS', 'ExpenseTypeList', true);
        $this->parsePage();
    }

    private function getActivitiesForExpenseType($expenseTypeID)
    {
        global $db;
        $result = $db->preparedQuery(
            'select activityTypeID  from expenseTypeActivityAvailability where expenseTypeID = ?',
            [["type" => "i", "value" => $expenseTypeID]]
        );

        $selectedActivitiesArray = $result->fetch_all(MYSQLI_ASSOC);
        return array_map(
            function ($activityArray) { return $activityArray['activityTypeID']; },
            $selectedActivitiesArray
        );
    }

   
    

    private function updateActivitiesForExpenseType($newActivities, $expenseTypeID)
    {

        $currentActivities = $this->getActivitiesForExpenseType($expenseTypeID);

        $toAddActivities = array_reduce(
            $newActivities,
            function ($acc, $newActivity) use (&$currentActivities) {
                $foundKey = array_search($newActivity, $currentActivities, false);
                if ($foundKey !== false) {
                    // we have an activity that was there already
                    array_splice($currentActivities, $foundKey, 1);
                    return $acc;
                }

                $acc[] = $newActivity;
                return $acc;
            },
            []
        );
        global $db;

        if (count($currentActivities)) {

            $toDeleteQuestionMarks = implode(
                ',',
                array_map(function () { return '?'; }, $currentActivities)
            );
            $toDeleteParams = array_map(
                function ($toDeleteActivityID) { return ["type" => "i", "value" => $toDeleteActivityID]; },
                $currentActivities
            );

            $result = $db->preparedQuery(
                "delete from expenseTypeActivityAvailability where activityTypeID in ($toDeleteQuestionMarks)",
                $toDeleteParams
            );
        }

        if (count($toAddActivities)) {
            $toAddQuestionMarks = implode(
                ',',
                array_map(function () { return '(?,?)'; }, $toAddActivities)
            );
            $toAddParams = array_reduce(
                $toAddActivities,
                function ($acc, $toAddActivityID) use ($expenseTypeID) {
                    $acc[] = ["type" => "i", "value" => $expenseTypeID];
                    $acc[] = ["type" => "i", "value" => $toAddActivityID];
                    return $acc;
                },
                []
            );
            $result = $db->preparedQuery(
                "insert into expenseTypeActivityAvailability values $toAddQuestionMarks",
                $toAddParams
            );
        }
        return true;
    }

    
    //---------------new
    function getTypes(){
        $dsExpenseType = new DataSet($this);
        $this->buExpenseType->getAllTypes($dsExpenseType);        
        $data=[];
        if ($dsExpenseType->rowCount() > 0) {            
            while ($dsExpenseType->fetchNext()) {
                $expenseTypeID = $dsExpenseType->getValue(DBEExpenseType::expenseTypeID); 
                $canDelete=false;
                if ( $this->buExpenseType->canDeleteExpenseType( $expenseTypeID)) {
                    $canDelete=true;
                }                              
                $data []=
                    array(
                        'expenseTypeID' => $expenseTypeID,
                        'description'   => Controller::htmlDisplayText(
                            $dsExpenseType->getValue(DBEExpenseType::description)
                        ),
                        'canDelete'     =>$canDelete,
                        'taxable'            => $dsExpenseType->getValue(DBEExpenseType::taxable) ,
                        'approvalRequired'   => $dsExpenseType->getValue(DBEExpenseType::approvalRequired) ,
                        'receiptRequired'    => $dsExpenseType->getValue(DBEExpenseType::receiptRequired),
                        'mileageFlag'        => $dsExpenseType->getValue(DBEExpenseType::mileageFlag)=='Y'?1:0,
                        'vatFlag'            => $dsExpenseType->getValue(DBEExpenseType::vatFlag)=='Y'?1:0,
                        'maximumAutoApprovalAmount' => $dsExpenseType->getValue(DBEExpenseType::maximumAutoApprovalAmount),
                        //"activityTypes"  => $this->getActivitiesForExpenseType($expenseTypeID)

                    ); 
            }
        }
        return  $this->success($data);
    }
    function addType(){

        $this->setMethodName('addType'); 

        $body=$this->getBody();
        $dbeExpenseType= new DBEExpenseType($this);
        $dbeExpenseType->setValue(DBEExpenseType::description, $body->description);
        $dbeExpenseType->getRowByColumn(DBEExpenseType::description);
        if($dbeExpenseType->rowCount>0)
            return $this->fail(APIException::conflict,"Conflicted name");
        $dbeExpenseType= new DBEExpenseType($this);  
        $dbeExpenseType->setValue(DBEExpenseType::description, $body->description);
        $dbeExpenseType->setValue(DBEExpenseType::taxable, $body->taxable);
        $dbeExpenseType->setValue(DBEExpenseType::approvalRequired, $body->approvalRequired);
        $dbeExpenseType->setValue(DBEExpenseType::receiptRequired, $body->receiptRequired);
        $dbeExpenseType->setValue(DBEExpenseType::mileageFlag, $body->mileageFlag);
        $dbeExpenseType->setValue(DBEExpenseType::vatFlag, $body->vatFlag);
        $dbeExpenseType->setValue(DBEExpenseType::maximumAutoApprovalAmount, $body->maximumAutoApprovalAmount);
        $dbeExpenseType->insertRow(); 
        if(isset($body->activityTypes))     
        $this->updateActivitiesForExpenseType(
            $body->activityTypes,
            $dbeExpenseType->getValue(DBEExpenseType::expenseTypeID)
        );
        return $this->success();
 
    }
    function updateType(){
        $this->setMethodName('updateType'); 
        $body=$this->getBody();
        $dbeExpenseType= new DBEExpenseType($this);
        $dbeExpenseType->setValue(DBEExpenseType::description, $body->description);
        $dbeExpenseType->getRow($body->expenseTypeID);                
        $dbeExpenseType->setValue(DBEExpenseType::description, $body->description);
        $dbeExpenseType->setValue(DBEExpenseType::taxable, $body->taxable);
        $dbeExpenseType->setValue(DBEExpenseType::approvalRequired, $body->approvalRequired);
        $dbeExpenseType->setValue(DBEExpenseType::receiptRequired, $body->receiptRequired);
        $dbeExpenseType->setValue(DBEExpenseType::mileageFlag, $body->mileageFlag);
        $dbeExpenseType->setValue(DBEExpenseType::vatFlag, $body->vatFlag);
        $dbeExpenseType->setValue(DBEExpenseType::maximumAutoApprovalAmount, $body->maximumAutoApprovalAmount);
        //return;
        $dbeExpenseType->updateRow(); 
        if(isset($body->activityTypes))     
        $this->updateActivitiesForExpenseType(
            $body->activityTypes,
            $dbeExpenseType->getValue(DBEExpenseType::expenseTypeID)
        );
        return $this->success();
    }
    function deleteType(){
        $this->setMethodName('updateType');
        $id=@$_REQUEST["id"];
        if($this->buExpenseType->deleteExpenseType($id))                       
            return $this->success();
        else
            return $this->fail(APIException::conflict,"Item can't delete");
    }
}
