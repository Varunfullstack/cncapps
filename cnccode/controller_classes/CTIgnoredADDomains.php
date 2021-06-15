<?php
/**
 * Expense controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
global $cfg;

use CNCLTD\Exceptions\APIException;

require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_dbe'] . '/DBEIgnoredADDomain.inc.php');


// Actions
class CTIgnoredADDomains extends CTCNC
{
    var $buActivity;
    public $dsIgnoredADDomains;
    const CONST_DOMAINS = 'domains';
    function __construct(
        $requestMethod,
        $postVars,
        $getVars,
        $cookieVars,
        $cfg
    ) {
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
        $this->setMenuId(218);
    }

    /**
     * Route to function based upon action passed
     * @throws Exception
     * @throws Exception
     */
    function defaultAction()
    {
        switch ($this->getAction()) {
            case self::CONST_DOMAINS:
                switch ($this->requestMethod) {
                    case 'GET':
                        echo  json_encode($this->getDomains(), JSON_NUMERIC_CHECK);
                        break;
                    case 'POST':
                        echo  json_encode($this->addDomain(), JSON_NUMERIC_CHECK);
                        break;
                    case 'PUT':
                        echo  json_encode($this->updateDomain(), JSON_NUMERIC_CHECK);
                        break;
                    case 'DELETE':
                        echo  json_encode($this->deleteDomain(), JSON_NUMERIC_CHECK);
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
        $this->setPageTitle('Ignored AD Domains');
        $this->setTemplateFiles(
            array('IgnoredADDomains' => 'IgnoredADDomains')
        );
        $this->loadReactScript('IgnoredADDomainsComponent.js');
        $this->loadReactCSS('IgnoredADDomainsComponent.css');
        $this->template->parse(
            'CONTENTS',
            'IgnoredADDomains',
            true
        );
        $this->parsePage();
    }

    function getDomains()
    {
        $DBEIgnoredADDomains = new DBEIgnoredADDomain($this);
        $DBEIgnoredADDomains->getRows();
        $data = [];
        while ($DBEIgnoredADDomains->fetchNext()) {
            $customerString = null;
            if ($DBEIgnoredADDomains->getValue(DBEIgnoredADDomain::customerID)) {
                $dbeCustomer = new DBECustomer($this);
                $dbeCustomer->getRow($DBEIgnoredADDomains->getValue(DBEIgnoredADDomain::customerID));
                $customerString = $dbeCustomer->getValue(DBECustomer::name);
            }
            $data[] = [
                "id"             => $DBEIgnoredADDomains->getValue(DBEIgnoredADDomain::ignoredADDomainID),
                "domain"         => $DBEIgnoredADDomains->getValue(DBEIgnoredADDomain::domain),
                "customerID"     => $DBEIgnoredADDomains->getValue(DBEIgnoredADDomain::customerID),
                "customerString" => $customerString
            ];
        }
        return $this->success($data);
    }

    function updateDomain()
    {
        $body = $this->getBody();
        $id = @$body->id;
        if (!$id) {
            return $this->fail(APIException::notFound);
        }
        $DBEIgnoredADDomain = new DBEIgnoredADDomain($this);
        $DBEIgnoredADDomain->getRow($id);
        if (!$DBEIgnoredADDomain->rowCount) {
            return $this->fail(APIException::notFound);
        }

        $DBEIgnoredADDomain->setValue(
            DBEIgnoredADDomain::domain,
            $body->domain
        );
        $DBEIgnoredADDomain->setValue(
            DBEIgnoredADDomain::customerID,
            $body->customerID
        );

        $DBEIgnoredADDomain->updateRow();
        return $this->success();
    }

    function addDomain()
    {
        $body = $this->getBody();
        $DBEIgnoredADDomain = new DBEIgnoredADDomain($this);
        $DBEIgnoredADDomain->setValue(
            DBEIgnoredADDomain::domain,
            $body->domain
        );
        $DBEIgnoredADDomain->setValue(
            DBEIgnoredADDomain::customerID,
            $body->customerID
        );

        $DBEIgnoredADDomain->insertRow();
        return $this->success();
    }

    function deleteDomain()
    {
        $id = @$_REQUEST["id"];
        if (!$id) {
            return $this->fail(APIException::badRequest);
        }
        $DBEIgnoredADDomain = new DBEIgnoredADDomain($this);
        $DBEIgnoredADDomain->getRow($this->getParam('id'));
        if (!$DBEIgnoredADDomain->rowCount) {
            return $this->fail(APIException::notFound);
        }
        $DBEIgnoredADDomain->deleteRow();
        return $this->success();
    }
}// end of class
