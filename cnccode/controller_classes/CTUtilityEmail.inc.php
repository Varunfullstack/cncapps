<?php
/**
 * Expense controller class
 * CNC Ltd
 *
 * @access public
 * @authors Mustafa Taha
 */

use CNCLTD\Exceptions\APIException;

require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_dbe'] . '/DBEUtilityEmail.inc.php');
require_once($cfg['path_bu'] . '/BUActivity.inc.php');

// Actions
class CTUtilityEmail extends CTCNC
{
    /** @var BUActivity */
    public $buActivity;
    public $dsUtilityEmail;
    const CONST_EMAILS='emails';
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
        $this->setMenuId(217);
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
            case self::CONST_EMAILS:
                switch ($this->requestMethod) {
                    case 'GET':
                        echo  json_encode($this->getEmails(),JSON_NUMERIC_CHECK);
                        break;
                    case 'POST':
                         echo  json_encode($this->addEmail(),JSON_NUMERIC_CHECK);
                         break;
                    case 'PUT':
                        echo  json_encode($this->updateEmail(),JSON_NUMERIC_CHECK);
                         break;
                    case 'DELETE':
                          echo  json_encode($this->deleteEmail(),JSON_NUMERIC_CHECK);
                          break;                    
                }
                exit;  
            default:
                $this->displayList();
                break;
        }
    }
 
    /**
     * Display list of Emails
     * @access private
     * @throws Exception
     */
    function displayList()
    {
        //--------new 
        $this->setPageTitle('Utility Emails');
        $this->setTemplateFiles(
            array('UtilityEmail' => 'UtilityEmail.inc')
        );
        $this->loadReactScript('UtilityEmailsComponent.js');
        $this->loadReactCSS('UtilityEmailsComponent.css');
        $this->template->parse(
            'CONTENTS',
            'UtilityEmail',
            true
        );
        $this->parsePage();      
    }
    function getEmails(){        
        $dbeUtilityEmails = new DBEUtilityEmail($this);

        $dbeUtilityEmails->getRows();
        $data = [];
        while ($dbeUtilityEmails->fetchNext()) {
            $data[] = [
                "id"        => $dbeUtilityEmails->getValue(DBEUtilityEmail::utilityEmailID),
                "firstPart" => $dbeUtilityEmails->getValue(DBEUtilityEmail::firstPart),
                "lastPart"  => $dbeUtilityEmails->getValue(DBEUtilityEmail::lastPart)
            ];
        }
        return $this->success($data);
    }
    function updateEmail()
    {
        $body=$this->getBody(true);
        $id=@$body["id"];
        if (!$id) {
            return $this->fail(APIException::badRequest,"Missing parameters");
        }
        $dbeUtilityEmail = new DBEUtilityEmail($this);
        $dbeUtilityEmail->getRow($id);
        if (!$dbeUtilityEmail->rowCount) {
            return $this->fail(APIException::notFound,"Not found");
        }

        $dbeUtilityEmail->setValue(
            DBEUtilityEmail::firstPart,
            $body['firstPart']
        );
        $dbeUtilityEmail->setValue(
            DBEUtilityEmail::lastPart,
            $body['lastPart']
        );

        $dbeUtilityEmail->updateRow();
        return $this->success();
    }
    function addEmail(){
        $body=$this->getBody();
        $dbeUtilityEmail = new DBEUtilityEmail($this);
        $dbeUtilityEmail->setValue(
            DBEUtilityEmail::firstPart,
            $body->firstPart
        );
        $dbeUtilityEmail->setValue(
            DBEUtilityEmail::lastPart,
            $body->lastPart
        );

        $dbeUtilityEmail->insertRow();
        return $this->success();                    
    }
    function deleteEmail(){
        $id=@$_REQUEST["id"];
        if (!$id) {
            return $this->fail(APIException::badRequest);
        }
        $dbeUtilityEmail = new DBEUtilityEmail($this);
        $dbeUtilityEmail->getRow($id);
        if (!$dbeUtilityEmail->rowCount) {
            return $this->fail(APIException::notFound);
        }
        $dbeUtilityEmail->setLogSQLOn();
        $dbeUtilityEmail->deleteRow();
        return $this->success();
    }

}// end of class
