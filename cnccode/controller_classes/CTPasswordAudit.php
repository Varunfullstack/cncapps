<?php
/**
 * Created by PhpStorm.
 * User: fizdalf
 * Date: 25/07/2018
 * Time: 12:33
 */

require_once($cfg['path_bu'] . '/BUPassword.inc.php');
require_once($cfg['path_bu'] . '/BUHeader.inc.php');
require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_dbe'] . '/DBECustomer.inc.php');
require_once($cfg['path_dbe'] . '/DBEPasswordService.inc.php');


class CTPasswordAudit extends CTCNC
{
    /**
     * Dataset for contact record storage.
     *
     * @var     DSForm
     * @access  private
     */
    var $dsContact = '';

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
            "sales",
            "technical"
        ];
        if (!self::hasPermissions($roles)) {
            Header("Location: /NotAllowed.php");
            exit;
        }
    }

    /**
     * Route to function based upon action passed
     */
    function defaultAction()
    {
        switch ($_REQUEST['action']) {
            default:
                $this->displaySearchForm();
        }
    }

    /**
     * Display the initial form that prompts the employee for details
     * @access private
     */
    function displaySearchForm()
    {
        $this->setMethodName('displaySearchForm');
        $this->setTemplateFiles(
            'PasswordAudit',
            'PasswordAudit'
        );
// Parameters
        $this->setPageTitle("Password Audit Log");

        $this->template->setBlock(
            'PasswordAudit',
            'PasswordsBlock',
            'passwords'
        );


        $buPassword = new BUPassword($this);
        $passwords = new DataSet($this);
        $buPassword->getArchivedRowsByPasswordLevel(
            $this->dbeUser->getValue(DBEUser::passwordLevel),
            $passwords
        );


        $customerCache = [];
        $serviceNameCache = [];

        $dbeCustomer = new DBECustomer($this);
        $dbePasswordService = new DBEPasswordService($this);
        while ($passwords->fetchNext()) {

            if (!isset($customerCache[$passwords->getValue(DBEPassword::customerID)])) {
                $dbeCustomer->getRow($passwords->getValue(DBEPassword::customerID));
                $customerCache[$passwords->getValue(DBEPassword::customerID)] = $dbeCustomer->getValue(
                    DBECustomer::name
                );
            }

            if (!isset($serviceNameCache[$passwords->getValue(DBEPassword::serviceID)])) {

                $dbePasswordService->getRow($passwords->getValue(DBEPassword::serviceID));
                $serviceNameCache[$passwords->getValue(DBEPassword::serviceID)] = $dbePasswordService->getValue(
                    DBEPasswordService::description
                );
            }

            $this->template->setVar(
                [
                    "customer"    => $customerCache[$passwords->getValue(DBEPassword::customerID)],
                    "username"    => $buPassword->decrypt($passwords->getValue(DBEPassword::username)),
                    "password"    => $buPassword->decrypt($passwords->getValue(DBEPassword::password)),
                    "notes"       => $buPassword->decrypt($passwords->getValue(DBEPassword::notes)),
                    "URL"         => $buPassword->decrypt($passwords->getValue(DBEPassword::URL)),
                    "serviceName" => $serviceNameCache[$passwords->getValue(DBEPassword::serviceID)],
                ]
            );

            $this->template->parse(
                'passwords',
                'PasswordsBlock',
                true
            );
        }

        $this->template->parse(
            'CONTENTS',
            'PasswordAudit',
            true
        );
        $this->parsePage();
    }
}// end of class
