<?php
/**
 * Created by PhpStorm.
 * User: fizdalf
 * Date: 25/01/2019
 * Time: 11:28
 */

use CNCLTD\Exceptions\APIException;

require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_dbe'] . '/DBEStarterLeaverQuestion.php');
require_once($cfg['path_dbe'] . '/DBECustomer.inc.php');
require_once($cfg['path_dbe'] . '/DSForm.inc.php');

class CTStarterLeaverManagement extends CTCNC
{
    /** @var DSForm */
    public $dsStandardText;
    const CONST_CUSTOMERS="customers";
    const CONST_CUSTOMER_QUESTIONS="customerQuestions";
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


        if (!$this->isStarterLeaverManger()) {
            Header("Location: /NotAllowed.php");
            exit;
        }
        $this->dsStandardText = new DSForm($this);
        $this->dsStandardText->copyColumnsFrom(new DBEStarterLeaverQuestion($this));
        $this->setMenuId(114);
    }

    /**
     * Route to function based upon action passed
     * @throws Exception
     * @throws Exception
     */
    function defaultAction()
    {

        if ($this->dbeUser->getValue(DBEUser::starterLeaverQuestionManagementFlag) != 'Y') {
            $this->displayFatalError('You do not have the permissions required for the requested operation');
        }


        switch ($this->getAction()) {
            case self::CONST_CUSTOMERS:
                echo json_encode($this->getCustomersHaveQuestions(),JSON_NUMERIC_CHECK);                
                exit;
            case self::CONST_CUSTOMER_QUESTIONS:
                 switch ($this->requestMethod) {
                    case 'GET':
                        echo json_encode($this->getCustomerQuestions(),JSON_NUMERIC_CHECK);                                       
                        break;   
                    case 'PUT':
                        echo json_encode($this->updateCustomerQuestion(),JSON_NUMERIC_CHECK);                                       
                        break; 
                    case 'POST':
                        echo json_encode($this->addCustomerQuestion(),JSON_NUMERIC_CHECK);                                       
                        break;    
                    case 'DELETE':
                        echo json_encode($this->deleteCustomerQuestion(),JSON_NUMERIC_CHECK);                                       
                        break;                  
                    default:
                        # code...
                        break;
                }
                exit;
            
            default:
                $this->displayList();
                break;
        }
    }

    

    /**
     * Display list of types
     * @access private
     * @throws Exception
     */
    function displayList()
    {   
        $this->setPageTitle('Starter Leaver Management');
        $this->setTemplateFiles(
            array('StarterLeaverManagement' => 'StarterLeaverManagement')
        );
        $this->loadReactScript('StarterLeaverManagementComponent.js');
        $this->loadReactCSS('StarterLeaverManagementComponent.css');
        $this->template->parse(
            'CONTENTS',
            'StarterLeaverManagement',
            true
        );
        $this->parsePage();
    }
    function getCustomersHaveQuestions()
    {
        $data = [];
        $dbeStarterLeaverQuestion = new DBEStarterLeaverQuestion($this);
        $customers = $dbeStarterLeaverQuestion->getCustomers();

        foreach ($customers as $customer) {
            $data[] =
                [
                    "customerID" => $customer['customerID'],
                    "customerName" => $customer['customerName'],
                    "starter"  => $customer['starters'],
                    "leaver"   => $customer['leavers'],
                ];
        }
        return $this->success($data);
    }
    /**
     * @throws Exception
     */
    private function getCustomerQuestions()
    {
        $this->setMethodName('getCustomerQuestions');
        $customerID = @$_REQUEST["customerID"];
        if (!$customerID) {
            return $this->fail(APIException::notFound, "Customer ID is missing");
        }
        $dbeCustomer = new DBECustomer($this);
        $dbeCustomer->getRow($customerID);
        $type = null;
        if ($this->getParam('type')) {
            $type = $this->getParam('type');
        }

        $dbeStarterLeaverQuestion = new DBEStarterLeaverQuestion($this);

        $dbeStarterLeaverQuestion->getRowsByCustomerID(
            $customerID,
            null,
            $type
        );
        $questions = [];
        while ($dbeStarterLeaverQuestion->fetchNext()) {
            $questionID = $dbeStarterLeaverQuestion->getValue(DBEStarterLeaverQuestion::questionID);
            $questions[] =
                [
                    "questionID" => $questionID,
                    DBEStarterLeaverQuestion::customerID => $dbeStarterLeaverQuestion->getValue(
                        DBEStarterLeaverQuestion::customerID
                    ),
                    DBEStarterLeaverQuestion::formType   => $dbeStarterLeaverQuestion->getValue(
                        DBEStarterLeaverQuestion::formType
                    ),
                    DBEStarterLeaverQuestion::name       => $dbeStarterLeaverQuestion->getValue(
                        DBEStarterLeaverQuestion::name
                    ),
                    DBEStarterLeaverQuestion::required   => +$dbeStarterLeaverQuestion->getValue(
                        DBEStarterLeaverQuestion::required
                    ),
                    DBEStarterLeaverQuestion::multi      => +$dbeStarterLeaverQuestion->getValue(
                        DBEStarterLeaverQuestion::multi
                    ),
                    DBEStarterLeaverQuestion::options    => json_decode(
                        $dbeStarterLeaverQuestion->getValue(
                            DBEStarterLeaverQuestion::options
                        )
                    ),
                    DBEStarterLeaverQuestion::label      => $dbeStarterLeaverQuestion->getValue(
                        DBEStarterLeaverQuestion::label
                    ),
                    DBEStarterLeaverQuestion::type       => $dbeStarterLeaverQuestion->getValue(
                        DBEStarterLeaverQuestion::type
                    ),
                    DBEStarterLeaverQuestion::sortOrder       => $dbeStarterLeaverQuestion->getValue(
                        DBEStarterLeaverQuestion::sortOrder
                    ),
                ];
        }
        return $this->success($questions);
    }
    function updateCustomerQuestion(){
        $questionData=$this->getBody(true);
        if (!$questionData) {
            return $this->fail(APIException::badRequest,'Question array is not set');           
        }

        if (!$questionData["questionID"]) {            
            return $this->fail(APIException::badRequest,'Question ID is missing');           
        }
        $questionID   = $questionData["questionID"];  
        $dbeStarterLeaverQuestion = new DBEStarterLeaverQuestion($this);

        $dbeStarterLeaverQuestion->getRow($questionID);

        $dbeStarterLeaverQuestion->setValue(
            DBEStarterLeaverQuestion::formType,
            $questionData[DBEStarterLeaverQuestion::formType]
        );
        $dbeStarterLeaverQuestion->setValue(
            DBEStarterLeaverQuestion::name,
            $questionData[DBEStarterLeaverQuestion::name]
        );

        $dbeStarterLeaverQuestion->setValue(
            DBEStarterLeaverQuestion::required,
            $questionData[DBEStarterLeaverQuestion::required]
        );

        $dbeStarterLeaverQuestion->setValue(
            DBEStarterLeaverQuestion::multi,
            $questionData[DBEStarterLeaverQuestion::multi]
        );

        $dbeStarterLeaverQuestion->setValue(
            DBEStarterLeaverQuestion::options,
            $questionData[DBEStarterLeaverQuestion::options]
        );

        $dbeStarterLeaverQuestion->setValue(
            DBEStarterLeaverQuestion::label,
            $questionData[DBEStarterLeaverQuestion::label]
        );

        $dbeStarterLeaverQuestion->setValue(
            DBEStarterLeaverQuestion::type,
            $questionData[DBEStarterLeaverQuestion::type]
        );
        $dbeStarterLeaverQuestion->setValue(
            DBEStarterLeaverQuestion::sortOrder,
            $questionData[DBEStarterLeaverQuestion::sortOrder]
        );
         
            $dbeStarterLeaverQuestion->updateRow();
        
        return $this->success();
    }
    function addCustomerQuestion(){
        $questionData=$this->getBody(true);
        if (!$questionData) {
            return $this->fail(APIException::badRequest,'Question array is not set');           
        } 
        $dbeStarterLeaverQuestion = new DBEStarterLeaverQuestion($this); 
        $dbeStarterLeaverQuestion->setValue(
        DBEStarterLeaverQuestion::sortOrder,
        $dbeStarterLeaverQuestion->getNextSortOrder($questionData[DBEStarterLeaverQuestion::customerID])
        );
        $dbeStarterLeaverQuestion->setValue(
            DBEStarterLeaverQuestion::formType,
            $questionData[DBEStarterLeaverQuestion::formType]
        );
        $dbeStarterLeaverQuestion->setValue(
            DBEStarterLeaverQuestion::name,
            $questionData[DBEStarterLeaverQuestion::name]
        );

        $dbeStarterLeaverQuestion->setValue(
            DBEStarterLeaverQuestion::required,
            $questionData[DBEStarterLeaverQuestion::required]
        );

        $dbeStarterLeaverQuestion->setValue(
            DBEStarterLeaverQuestion::multi,
            $questionData[DBEStarterLeaverQuestion::multi]
        );

        $dbeStarterLeaverQuestion->setValue(
            DBEStarterLeaverQuestion::options,
            $questionData[DBEStarterLeaverQuestion::options]
        );

        $dbeStarterLeaverQuestion->setValue(
            DBEStarterLeaverQuestion::label,
            $questionData[DBEStarterLeaverQuestion::label]
        );

        $dbeStarterLeaverQuestion->setValue(
            DBEStarterLeaverQuestion::type,
            $questionData[DBEStarterLeaverQuestion::type]
        );
        $dbeStarterLeaverQuestion->setValue(
            DBEStarterLeaverQuestion::customerID,
            $questionData[DBEStarterLeaverQuestion::customerID]
        );
      
        
        $dbeStarterLeaverQuestion->insertRow();
        return $this->success();
    }
    function deleteCustomerQuestion(){
        $questionID = @$_REQUEST["questionID"];
        if (!$questionID)
            return $this->fail(APIException::notFound, "not found");
        $dbeStarterLeaverQuestion = new DBEStarterLeaverQuestion($this);

        $exists = $dbeStarterLeaverQuestion->getRow($questionID);

        if (!$exists) {
            return $this->fail(APIException::notFound, "not found");
        }
        $dbeStarterLeaverQuestion->deleteRow();
        return $this->success();
    }
}