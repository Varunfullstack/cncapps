<?php
/**
 * Expense controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */

use CNCLTD\Business\BUActivity;

global $cfg;

use CNCLTD\Exceptions\APIException;

require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_dbe'] . '/DBEOffice365License.php');


// Actions
class CTOffice365Licenses extends CTCNC
{
    /** @var BUActivity */
    public $buActivity;
    public $dsOffice365License;
    const CONST_LICENSES = "licenses";

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
        $this->setMenuId(221);
        $this->buActivity = new BUActivity($this);
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
            case self::CONST_LICENSES:
                switch ($this->requestMethod) {
                    case 'GET':
                        echo json_encode($this->getLicneses(), JSON_NUMERIC_CHECK);
                        break;
                    case 'POST':
                        echo json_encode($this->addLicense(), JSON_NUMERIC_CHECK);
                        break;
                    case 'PUT':
                        echo json_encode($this->updateLicense(), JSON_NUMERIC_CHECK);
                        break;
                    case 'DELETE':
                        echo json_encode($this->deleteLicense(), JSON_NUMERIC_CHECK);
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
        //--------new
        $this->setPageTitle('Office 365 Licenses');
        $this->setTemplateFiles(
            array('Office365Licenses' => 'Office365Licenses')
        );
        $this->loadReactScript('Office365LicensesComponent.js');
        $this->loadReactCSS('Office365LicensesComponent.css');
        $this->template->parse(
            'CONTENTS',
            'Office365Licenses',
            true
        );
        $this->parsePage();
    }

    function getLicneses()
    {
        $dbeOffice365Licenses = new DBEOffice365License($this);
        $dbeOffice365Licenses->getRows(DBEOffice365License::replacement);
        $data = [];
        while ($dbeOffice365Licenses->fetchNext()) {
            $data[] = [
                "id"                    => $dbeOffice365Licenses->getValue(DBEOffice365License::id),
                "replacement"           => $dbeOffice365Licenses->getValue(DBEOffice365License::replacement),
                "license"               => $dbeOffice365Licenses->getValue(DBEOffice365License::license),
                "mailboxLimit"          => $dbeOffice365Licenses->getValue(DBEOffice365License::mailboxLimit),
                "reportOnSpareLicenses" => $dbeOffice365Licenses->getValue(
                    DBEOffice365License::reportOnSpareLicenses
                ),
                "includesDefender"      => $dbeOffice365Licenses->getValue(
                    DBEOffice365License::includesDefender
                ),
                "includesOffice"        => $dbeOffice365Licenses->getValue(
                    DBEOffice365License::includesOffice
                )
            ];
        }
        return $this->success($data);
    }

    function addLicense()
    {
        $body                = $this->getBody();
        $dbeOffice365License = new DBEOffice365License($this);
        $dbeOffice365License->setValue(
            DBEOffice365License::replacement,
            $body->replacement
        );
        $dbeOffice365License->setValue(
            DBEOffice365License::mailboxLimit,
            $body->mailboxLimit
        );
        $dbeOffice365License->setValue(DBEOffice365License::license, $body->license);
        $dbeOffice365License->setValue(
            DBEOffice365License::reportOnSpareLicenses,
            $body->reportOnSpareLicenses
        );
        $dbeOffice365License->setValue(
            DBEOffice365License::includesDefender,
            $body->includesDefender
        );
        $dbeOffice365License->setValue(
            DBEOffice365License::includesOffice,
            $body->includesOffice
        );
        $dbeOffice365License->insertRow();
        return $this->success();
    }

    function updateLicense()
    {
        $body = $this->getBody();
        $id = @$body->id;
        if (!$id) {
            return $this->fail(APIException::badRequest);
        }
        $dbeOffice365License = new DBEOffice365License($this);
        $dbeOffice365License->getRow($id);
        if (!$dbeOffice365License->rowCount) {
            return $this->fail(APIException::notFound);
        }
        $dbeOffice365License->setValue(
            DBEOffice365License::replacement,
            $body->replacement
        );
        $dbeOffice365License->setValue(
            DBEOffice365License::mailboxLimit,
            $body->mailboxLimit
        );
        $dbeOffice365License->setValue(DBEOffice365License::license, $body->license);
        $dbeOffice365License->setValue(
            DBEOffice365License::reportOnSpareLicenses,
            $body->reportOnSpareLicenses
        );
        $dbeOffice365License->setValue(
            DBEOffice365License::includesDefender,
            $body->includesDefender
        );
        $dbeOffice365License->setValue(
            DBEOffice365License::includesOffice,
            $body->includesOffice
        );
        $dbeOffice365License->updateRow();
        return $this->success();
    }

    function deleteLicense()
    {
        $id = @$_REQUEST["id"];
        if (!$id) {
            return $this->fail(APIException::badRequest);
        }
        $dbeOffice365License = new DBEOffice365License($this);
        $dbeOffice365License->getRow($id);
        if (!$dbeOffice365License->rowCount) {
            return $this->fail(APIException::notFound);
        }
        $dbeOffice365License->deleteRow();
        return $this->success();
    }
}// end of class
