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
    const CONST_SERVICES  = 'services';
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
                switch ($this->requestMethod) {
                    case 'GET':
                        echo json_encode($this->getPasswords());
                        break;
                    case 'POST':
                        echo json_encode($this->updatePassword());
                        break;
                    case 'DELETE':
                        echo json_encode($this->archivePassword());
                        break;
                }
                break;
            case self::CONST_SERVICES:
                echo json_encode($this->getPasswordServices(), JSON_NUMERIC_CHECK);
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
        $this->loadReactScript('PasswordComponent.js');
        $this->loadReactCSS('PasswordComponent.css');
        $this->template->parse('CONTENTS', 'PasswordList');
        $this->parsePage();
    }


    function replaceQuotes($str)
    {
        return str_replace("\"", "&quot;", $str);
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

    public function getPasswords()
    {
        $customerId      = @$_REQUEST["customerId"];
        $showArchived    = @$_REQUEST["showArchived"] == "true";
        $showHigherLevel = @$_REQUEST["showHigherLevel"] == "true";
        if (!$customerId) return $this->fail(APIException::badRequest, "Missing paramters");
        $dbeCustomer = new DBECustomer($this);
        $dbeCustomer->getRow($customerId);
        if ($dbeCustomer->getValue(DBECustomer::referredFlag) == 'Y') {
            return $this->fail(
                APIException::unAuthorized,
                "This customer is referred and access to passwords has been removed, please direct the customer to Sales if they require any further information."
            );
        }
        $dsPassword    = new DBEJPassword($this);
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
            $notes        = $this->buPassword->decrypt($dsPassword->getValue(DBEPassword::notes));
            $decryptedURL = $this->buPassword->decrypt($dsPassword->getValue(DBEPassword::URL));
            $URL          = $decryptedURL;
            $userName     = $this->buPassword->decrypt(
                $dsPassword->getValue(DBEPassword::username)
            );
            $password     = $this->buPassword->decrypt(
                $dsPassword->getValue(DBEPassword::password)
            );
            if ($dsPassword->getValue(DBEPassword::level) > $this->dbeUser->getValue(DBEUser::passwordLevel)) {
                $userName = null;
                $password = null;
            }
            $passwords[] = [
                "notes"               => $notes,
                "serviceName"         => $dsPassword->getValue(DBEJPassword::serviceName),
                "serviceID"           => +$dsPassword->getValue(DBEJPassword::serviceID),
                'passwordID'          => +$dsPassword->getValue(DBEPassword::passwordID),
                'customerID'          => +$dsPassword->getValue(DBEPassword::customerID),
                DBEPassword::username => $userName,
                'password'            => $password,
                'salesPassword'       => $dsPassword->getValue(DBEPassword::salesPassword),
                'level'               => $dsPassword->getValue(DBEPassword::level),
                'sortOrder'           => +$dsPassword->getValue(DBEJPassword::sortOrder),
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
        return $this->success($passwords);
    }

    function getPasswordServices()
    {
        $customerID = @$_REQUEST["customerId"];
        $passwordID = @$_REQUEST["passwordId"] ?? null;
        if (!$customerID) return $this->fail(APIException::badRequest, "Missing Customer ID");
        $dbePasswordServices = new DBEPasswordService($this);
        $dbePasswordServices->getNotInUseServices(
            $customerID,
            $passwordID
        );
        $services = [];
        while ($dbePasswordServices->fetchNext()) {
            $services [] = [
                "id"   => $dbePasswordServices->getValue(DBEPasswordService::passwordServiceID),
                "name" => $dbePasswordServices->getValue(DBEPasswordService::description),
            ];
        }
        return $this->success($services);
    }

    function updatePassword()
    {
        $body        = $this->getBody();
        $passwordID  = $body->passwordID;
        $dbePassword = new DBEPassword($this);
        if ($passwordID) {
            $dbePassword->getRow($passwordID);
            if (!$dbePassword->rowCount) return $this->fail(APIException::notFound, "Not found");
        }
        if ($this->dbeUser->getValue(DBEUser::passwordLevel) < $dbePassword->getValue(DBEPassword::level)) {
            return $this->fail(APIException::unAuthorized, "unAuthorized");
        }
        if (!$this->dbeUser->getValue(DBEUser::salesPasswordAccess)) {
            $dbePassword->setValue(DBEPassword::salesPassword, $dbePassword->getValue(DBEPassword::salesPassword));
        }
        $previousPassword          = $dbePassword->getValue(DBEPassword::password);
        $previousPasswordDecrypted = $this->buPassword->decrypt($previousPassword);
        $newPassword               = $body->password;
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
        $dbePassword->setValue(DBEPassword::salesPassword, $body->salesPassword);
        if ($passwordID) {
            $dbePassword->updateRow();
            return $this->success("updated");
        } else {
            $dbePassword->insertRow();
            return $this->success("Insert");
        }

    }

    function archivePassword()
    {
        $passwordID = $this->getParam('passwordID');
        if (!$this->buPassword->getPasswordByID(
            $passwordID,
            $dsPassword
        )) {
            return $this->fail(APIException::notFound, 'PasswordID ' . $passwordID . ' not found');
        }
        if ($dsPassword->getValue(DBEPassword::level) > $this->getDbeUser()->getValue(DBEUser::passwordLevel)) {
            return $this->fail(APIException::unAuthorized, "Not enough level");
        } else {
            $this->buPassword->archive(
                $passwordID,
                $this->dbeUser
            );
        }
        return $this->success();
    }
}
