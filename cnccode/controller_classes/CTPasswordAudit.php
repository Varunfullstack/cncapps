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
require_once($cfg['path_dbe'] . '/DBEJArchivedPassword.php');


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
        $passwords = new DBEJArchivedPassword($this);
        $passwords->getRows($this->dbeUser->getValue(DBEUser::passwordLevel));

        while ($passwords->fetchNext()) {

            $this->template->setVar(
                [
                    "customer"    => $passwords->getValue(DBEJArchivedPassword::customerName),
                    "username"    => $buPassword->decrypt($passwords->getValue(DBEJArchivedPassword::username)),
                    "password"    => $buPassword->decrypt($passwords->getValue(DBEJArchivedPassword::password)),
                    "notes"       => $buPassword->decrypt($passwords->getValue(DBEJArchivedPassword::notes)),
                    "URL"         => $buPassword->decrypt($passwords->getValue(DBEJArchivedPassword::URL)),
                    "serviceName" => $passwords->getValue(DBEJArchivedPassword::serviceName),
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
