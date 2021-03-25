<?php
/**
 * Domain renewal controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */

use CNCLTD\Exceptions\APIException;

global $cfg;
require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_bu'] . '/BUPassword.inc.php');
require_once($cfg['path_bu'] . '/BUCustomer.inc.php');
require_once($cfg['path_dbe'] . '/DBECustomer.inc.php');
require_once($cfg['path_dbe'] . '/DSForm.inc.php');
require_once($cfg['path_dbe'] . '/DBEJPassword.php');
require_once($cfg['path_dbe'] . '/DBEPasswordService.inc.php');

class CTPassword extends CTCNC
{
    const CONST_PASSWORDS = 'passwords';
    const CONST_SERVICES='services';
    public static $passwordLevels = [
        ["level" => 0, "description" => "No Access"],
        ["level" => 1, "description" => "Helpdesk Access"],
        ["level" => 2, "description" => "Engineer Access"],
        ["level" => 3, "description" => "Senior Engineer Access"],
        ["level" => 4, "description" => "Team Lead Access"],
        ["level" => 5, "description" => "Management Access"]
    ];
    /** @var BUPassword */
    public $buPassword;

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
            "technical",
        ];
        if (!self::hasPermissions($roles)) {
            Header("Location: /NotAllowed.php");
            exit;
        }
        $this->setMenuId(104);
        $this->buPassword = new BUPassword($this);
    }

    /**
     * Route to function based upon action passed
     * @throws Exception
     */
    function defaultAction()
    {
        switch ($this->getAction()) {
            case self::CONST_PASSWORDS:
                switch($this->requestMethod){
                    case 'GET':
                        echo json_encode($this->getPasswords(),JSON_NUMERIC_CHECK);
                         break;
                     case 'POST':
                         echo json_encode($this->updatePassword());
                         break;
                    //  case 'PUT':
                    //      echo json_encode($this->updateItem());
                    //      break;
                     case 'DELETE':
                         echo json_encode($this->archivePassword());
                         break;
                }            
                break;
            case self::CONST_SERVICES:                
                echo json_encode($this->getPasswordServices(),JSON_NUMERIC_CHECK);
                break;
            case 'edit':
                $this->edit();
                break;
            case 'archive':
                echo json_encode($this->archivePassword());
                break;
            case 'generate':
                $this->generate();
                break;           
            default:
                $this->setTemplate();
                break;
        }
    }

    function setTemplate()
    {
        $this->setTemplateFiles(
            'PasswordList',
            'PasswordList.inc'
        );
        $this->setPageTitle('Passwords');

        //$this->loadReactScript('ItemListTypeAheadRenderer.js');
        $this->loadReactScript('PasswordComponent.js');
        $this->loadReactCSS('PasswordComponent.css');     
        $this->template->parse('CONTENTS', 'PasswordList');
        $this->parsePage();
    }
    /**
     * Called from sales order line to edit a renewal.
     * The page passes
     * ordheadID
     * sequenceNo (line)
     * renewalCustomerItemID (blank if renewal not created yet
     *
     *
     * @throws Exception
     * @throws Exception
     * @throws Exception
     * @throws Exception
     * @throws Exception
     * @throws Exception
     * @throws Exception
     * @throws Exception
     */
    function edit()
    {
        $this->setMethodName('edit');

        $dsPassword = new DSForm($this);
        $dbePassword = new dbePassword($this);
        $dsPassword->copyColumnsFrom($dbePassword);

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $passwordForm = $this->getParam('password')[1];
            $passwordForm['encrypted'] = 1;
            $passwordID = $passwordForm['passwordID'];


            if ($passwordID) {
                $dbePassword->getRow($passwordID);
                if ($this->dbeUser->getValue(DBEUser::passwordLevel) < $dbePassword->getValue(DBEPassword::level)) {
                    return;
                }
                if (!$this->dbeUser->getValue(DBEUser::salesPasswordAccess)) {
                    $passwordForm[DBEPassword::salesPassword] = $dbePassword->getValue(DBEPassword::salesPassword);
                }
                $previousPassword = $dbePassword->getValue(DBEPassword::password);
                $previousPasswordDecrypted = $this->buPassword->decrypt($previousPassword);
                $newPassword = $passwordForm['password'];
                if ($previousPassword && $previousPasswordDecrypted != $newPassword) {
                    $this->buPassword->archive(
                        $passwordID,
                        $this->dbeUser
                    );
                    $passwordForm['passwordID'] = null;
                }
            }
            $passwordForm[DBEPassword::username] = $this->buPassword->encrypt($passwordForm[DBEPassword::username]);
            $passwordForm[DBEPassword::password] = $this->buPassword->encrypt($passwordForm[DBEPassword::password]);
            $passwordForm[DBEPassword::notes] = $this->buPassword->encrypt($passwordForm[DBEPassword::notes]);
            $passwordForm[DBEPassword::URL] = $this->buPassword->encrypt($passwordForm[DBEPassword::URL]);
            $formError = (!$dsPassword->populateFromArray([$passwordForm]));

            if (!$formError) {
                $this->buPassword->updatePassword($dsPassword);

                $urlNext =
                    Controller::buildLink(
                        $_SERVER['PHP_SELF'],
                        array(
                            'action'     => 'list',
                            'customerID' => $dsPassword->getValue(DBEPassword::customerID)
                        )
                    );

                header('Location: ' . $urlNext);
                exit;
            }
        } else {
            $passwordID = null;
            if ($this->getParam('passwordID')) {                      // editing
                $passwordID = $this->getParam('passwordID');
                $this->buPassword->getPasswordByID(
                    $this->getParam('passwordID'),
                    $dsPassword
                );
                if ($this->dbeUser->getValue(DBEUser::passwordLevel) < $dsPassword->getValue(DBEPassword::level)) {
                    return;
                }
                $customerID = $dsPassword->getValue(DBEPassword::customerID);
            } else {                                               // create new record
                $dsPassword->setValue(
                    DBEPassword::passwordID,
                    null
                );
                $dsPassword->setValue(
                    DBEPassword::customerID,
                    $this->getParam('customerID')
                );
                $customerID = $this->getParam('customerID');
            }
        }


        $urlEdit =
            Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action'     => 'edit',
                    'ordheadID'  => $passwordID,
                    'customerID' => $customerID
                )
            );
        $this->setPageTitle('Edit Password');

        $this->setTemplateFiles(array('PasswordEdit' => 'PasswordEdit.inc'));

        $this->template->set_block(
            'PasswordEdit',
            'levelBlock',
            'levels'
        );

        $minLevel = 1;
        $userLevel = $this->dbeUser->getValue(DBEUser::passwordLevel);

        if (!$userLevel) {
            echo 'You cannot edit this password';
            exit;
        }

        for ($level = $minLevel; $level <= count(self::$passwordLevels) - 1; $level++) {

            $this->template->set_var(
                array(
                    'level'            => $level,
                    'levelSelected'    => $dsPassword->getValue(DBEPassword::level) == $level ? 'selected' : '',
                    'levelDescription' => self::$passwordLevels[$level]['description']
                )
            );
            $this->template->parse(
                'levels',
                'levelBlock',
                true
            );
        }

        $this->template->set_block(
            'PasswordEdit',
            'passwordServiceBlock',
            'passwordServices'
        );


        $dbePasswordServices = new DBEPasswordService($this);
        $dbePasswordServices->getNotInUseServices(
            $customerID,
            $passwordID
        );

        while ($dbePasswordServices->fetchNext()) {
            $passwordServiceID = $dbePasswordServices->getValue(DBEPasswordService::passwordServiceID);
            $this->template->setVar(
                [
                    "passwordServiceID"          => $passwordServiceID,
                    "selected"                   => $dsPassword->getValue(
                        DBEPassword::serviceID
                    ) == $passwordServiceID ? 'selected' : '',
                    "passwordServiceDescription" => $dbePasswordServices->getValue(DBEPasswordService::description),
                ]
            );

            $this->template->parse(
                'passwordServices',
                'passwordServiceBlock',
                true
            );
        }

        $this->template->set_var(
            array(
                'customerID'             => $dsPassword->getValue(DBEPassword::customerID),
                'passwordID'             => $dsPassword->getValue(DBEPassword::passwordID),
                DBEPassword::username    => $this->replaceQuatos($this->buPassword->decrypt($dsPassword->getValue(DBEPassword::username))),
                'usernameMessage'        => $dsPassword->getMessage(DBEPassword::username),
                'password'               => $this->replaceQuatos($this->buPassword->decrypt($dsPassword->getValue(DBEPassword::password))),
                'passwordMessage'        => $dsPassword->getMessage(DBEPassword::password),
                DBEPassword::notes       => $this->replaceQuatos($this->buPassword->decrypt($dsPassword->getValue(DBEPassword::notes))),
                'notesMessage'           => $dsPassword->getMessage(DBEPassword::notes),
                'urlEdit'                => $urlEdit,
                'URL'                    => $this->buPassword->decrypt($dsPassword->getValue(DBEPassword::URL)),
                'hasSalesPasswordAccess' => $this->dbeUser->getValue(DBEUser::salesPasswordAccess) ? 1 : 0,
                'salesPassword'          => $dsPassword->getValue(DBEPassword::salesPassword) ? 1 : 0,
                'salesPasswordChecked'   => $dsPassword->getValue(DBEPassword::salesPassword) ? 'checked' : 0,
                'error'                  => $this->getParam('error')
            )
        );

        $this->template->parse(
            'CONTENTS',
            'PasswordEdit',
            true
        );
        $this->parsePage();

    } // end search
    function replaceQuatos($str)
    {
        return str_replace("\"","&quot;",$str);
    }
    /**
     * generate a password
     *
     * @throws Exception
     */
    function generate()
    {
        $this->setMethodName('generate');

        $this->setPageTitle('New Password');

        $this->setTemplateFiles(array('PasswordGenerate' => 'PasswordGenerate.inc'));

        $this->template->parse(
            'CONTENTS',
            'PasswordGenerate',
            true
        );
        $this->parsePage();

    }

    /**
     * @param $a
     * @param $b
     * @return int|lt
     */
    function weirdStringComparison($a,
                                   $b
    )
    {
        $lenA = strlen($a);
        $lenB = strlen($b);

        if (!$lenA && $lenB) {
            return -1;
        }

        if ($lenA && !$lenB) {
            return 1;
        }

        if (!$lenA && !$lenB) {
            return 0;
        }

        $len = $lenA > $lenB ? $lenA : $lenB;
        $currentIdx = 0;
        while ($currentIdx < $len) {

            if (!isset($a[$currentIdx])) {
                return -1;
            }

            if (!isset($b[$currentIdx])) {
                return 1;
            }

            if ($comparison = $this->compareCharacter(
                $a[$currentIdx],
                $b[$currentIdx]
            )) {
                return $comparison;
            };
            $currentIdx++;
        }
        return 0;
    }

    function compareCharacter($ch1,
                              $ch2
    )
    {
        if (ctype_lower($ch1) && !ctype_lower($ch2)) {
            return -1;
        }

        if (!ctype_lower($ch1) && ctype_lower($ch2)) {
            return 1;
        }
        return strcmp(
            $ch1,
            $ch2
        );
    }
    //---------------------new 
    public function getPasswords()
    {
        $customerId=@$_REQUEST["customerId"];
        $showArchived=@$_REQUEST["showArchived"]=="true"?true:false;
        $showHigherLevel=@$_REQUEST["showHigherLevel"]=="true"?true:false;
        if(!$customerId)
            return $this->fail(APIException::badRequest,"Missing paramters");
        $dbeCustomer =  new DBECustomer($this);
        $dbeCustomer->getRow($customerId);        
        if ($dbeCustomer->getValue(DBECustomer::referredFlag) == 'Y') {
            return $this->fail(APIException::unAuthorized,"This customer is referred and access to passwords has been removed, please direct the customer to Sales if they require any further information.");
        }        
        $dsPassword = new DBEJPassword($this);
        $passwordLevel = $this->dbeUser->getValue(DBEUser::passwordLevel);
        if ($showHigherLevel) {
            $passwordLevel = 5;
        }
        $dsPassword->getRowsByCustomerIDAndPasswordLevel(
            $customerId,
            $passwordLevel,
            $showArchived,
            $this->dbeUser->getValue(DBEUser::salesPasswordAccess)
        );
        $passwords = [];    
        while ($dsPassword->fetchNext()) {    
                $notes = $this->buPassword->decrypt($dsPassword->getValue(DBEPassword::notes));
                $decryptedURL = $this->buPassword->decrypt($dsPassword->getValue(DBEPassword::URL));
                $URL = $decryptedURL;
    
                $userName = $this->buPassword->decrypt(
                    $dsPassword->getValue(DBEPassword::username)
                );
                $password = $this->buPassword->decrypt(
                    $dsPassword->getValue(DBEPassword::password)
                );
    
                if ($dsPassword->getValue(DBEPassword::level) > $this->dbeUser->getValue(DBEUser::passwordLevel)) {
                    $userName = null;
                    $password = null;
                    $weirdFields = null;
                }
    
                $passwords[] = [
                    "notes"               => $this->replaceQuatos($notes),                    
                    "serviceName"         => $dsPassword->getValue(DBEJPassword::serviceName),
                    "serviceID"           => $dsPassword->getValue(DBEJPassword::serviceID),
                    'passwordID'          => $dsPassword->getValue(DBEPassword::passwordID),
                    'customerID'          => $dsPassword->getValue(DBEPassword::customerID),
                    DBEPassword::username => $this->replaceQuatos($userName),
                    'password'            => $this->replaceQuatos($password),
                    //"weirdFields"         => $weirdFields,
                    'level'               => $dsPassword->getValue(DBEPassword::level),
                    'sortOrder'           => $dsPassword->getValue(DBEJPassword::sortOrder),
                    'URL'                 => $URL,
                    'archivedAt'          => $dsPassword->getValue(DBEPassword::archivedAt),
                    'archivedBy'          => $dsPassword->getValue(DBEPassword::archivedBy),
                ];
            }
    
    
            usort(
                $passwords,
                function ($a,
                          $b
                ) {
    
                    if (!$a[DBEJPassword::serviceID] && $b[DBEJPassword::serviceID]) {
                        return 1;
                    }
    
                    if (!$b[DBEJPassword::serviceID] && $a[DBEJPassword::serviceID]) {
                        return -1;
                    }
    
                    if ($a[DBEJPassword::sortOrder] != $b[DBEJPassword::sortOrder]) {
                        return $a[DBEJPassword::sortOrder] - $b[DBEJPassword::sortOrder];
                    }
    
                    return strcmp(
                        $a[DBEJPassword::notes],
                        $b[DBEJPassword::notes]
                    );
                }
            );
        return $this->success($passwords );
    }

    function getPasswordServices(){
        $customerID =@$_REQUEST["customerId"];
        $passwordID =@$_REQUEST["passwordId"]??null;
        if(! $customerID )
            return $this->fail(APIException::badRequest,"Missing Customer ID");

        $dbePasswordServices = new DBEPasswordService($this);        
        $dbePasswordServices->getNotInUseServices(
            $customerID,
            $passwordID
        );
        $services = [];
        while ($dbePasswordServices->fetchNext()) {            
            $services []= [
                "id"          => $dbePasswordServices->getValue(DBEPasswordService::passwordServiceID),
                "name" => $dbePasswordServices->getValue(DBEPasswordService::description),
                ]; 
        }
        return $this->success($services);
    }
    function updatePassword(){
        $body = $this->getBody();
        // if (!$body->passwordID)
        //     return $this->fail(APIException::badRequest, "missing data");
        $passwordID = $body->passwordID;
        $dbePassword = new DBEPassword($this);
        if ($passwordID) {
            $dbePassword->getRow($passwordID);
            if (!$dbePassword->rowCount)
                return $this->fail(APIException::notFound, "Not found");
        }
        if ($this->dbeUser->getValue(DBEUser::passwordLevel) < $dbePassword->getValue(DBEPassword::level)) {
            return $this->fail(APIException::unAuthorized, "unAuthorized");
        }
        if (!$this->dbeUser->getValue(DBEUser::salesPasswordAccess)) {
            $dbePassword->setValue(DBEPassword::salesPassword, $dbePassword->getValue(DBEPassword::salesPassword));
        }
        $previousPassword = $dbePassword->getValue(DBEPassword::password);
        $previousPasswordDecrypted = $this->buPassword->decrypt($previousPassword);
        $newPassword = $body->password;
        if ($previousPassword && $previousPasswordDecrypted != $newPassword) {
            $this->buPassword->archive(
                $passwordID,
                $this->dbeUser
            );
            $passwordID = null;
        }

        $dbePassword->setValue(DBEPassword::username, $this->buPassword->encrypt($body->username));
        $dbePassword->setValue(DBEPassword::password, $this->buPassword->encrypt($body->password));
        $dbePassword->setValue(DBEPassword::notes, $this->buPassword->encrypt($body->notes));
        $dbePassword->setValue(DBEPassword::URL, $this->buPassword->encrypt($body->URL));
        $dbePassword->setValue(DBEPassword::level, $body->level);
        $dbePassword->setValue(DBEPassword::serviceID, $body->serviceID);
        $dbePassword->setValue(DBEPassword::level, $body->level);
        $dbePassword->setValue(DBEPassword::customerID, $body->customerID);
        $dbePassword->setValue(DBEPassword::encrypted, 1);

        if ($passwordID)
        {
            $dbePassword->updateRow();
            return $this->success("updated");
        }
        else
        {
            $dbePassword->insertRow();
            return $this->success("Insert");
        }
         
    }
    function archivePassword()
    {
        $passwordID=$this->getParam('passwordID');
        if (!$this->buPassword->getPasswordByID(
            $passwordID,
            $dsPassword
        )) {
            return $this->fail(APIException::notFound,'PasswordID ' . $passwordID . ' not found');            
        }
        

        if ($dsPassword->getValue(DBEPassword::level) > $this->getDbeUser()->getValue(DBEUser::passwordLevel)) {            
            return $this->fail(APIException::unAuthorized,"Not enough level");
        } else {
            $this->buPassword->archive(
                $passwordID,
                $this->dbeUser
            );
        }
        return $this->success();
    }
}// end of class
