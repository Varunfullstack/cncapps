<?php
/**
 * Expense controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
global $cfg;
require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_dbe'] . '/DBEIgnoredADDomain.inc.php');


// Actions
class CTIgnoredADDomains extends CTCNC
{
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
        $this->setMenuId(218);
    }


    function delete()
    {
        if (!$this->getParam('id')) {
            http_response_code(400);
            throw new Exception('ID is missing');
        }
        $DBEIgnoredADDomain = new DBEIgnoredADDomain($this);
        $DBEIgnoredADDomain->getRow($this->getParam('id'));
        if (!$DBEIgnoredADDomain->rowCount) {
            http_response_code(404);
            exit;
        }
        $DBEIgnoredADDomain->deleteRow();
        echo json_encode(["status" => "ok"]);
    }

    function update()
    {
        if (!$this->getParam('id')) {
            throw new Exception('ID is missing');
        }
        $DBEIgnoredADDomain = new DBEIgnoredADDomain($this);
        $DBEIgnoredADDomain->getRow($this->getParam('id'));
        if (!$DBEIgnoredADDomain->rowCount) {
            http_response_code(404);
            exit;
        }
        $DBEIgnoredADDomain->setValue(
            DBEIgnoredADDomain::domain,
            $this->getParam('domain')
        );
        $DBEIgnoredADDomain->setValue(
            DBEIgnoredADDomain::customerID,
            $this->getParam('customerID')
        );
        $DBEIgnoredADDomain->updateRow();
        echo json_encode(["status" => "ok"]);
    }

    /**
     * Route to function based upon action passed
     * @throws Exception
     * @throws Exception
     */
    function defaultAction()
    {
        switch ($this->getAction()) {
            case 'create':
                $DBEIgnoredADDomain = new DBEIgnoredADDomain($this);
                $DBEIgnoredADDomain->setValue(
                    DBEIgnoredADDomain::domain,
                    $this->getParam('domain')
                );
                $DBEIgnoredADDomain->setValue(
                    DBEIgnoredADDomain::customerID,
                    $this->getParam('customerID')
                );
                $DBEIgnoredADDomain->insertRow();
                $customerString = null;
                if ($DBEIgnoredADDomain->getValue(DBEIgnoredADDomain::customerID)) {
                    $dbeCustomer = new DBECustomer($this);
                    $dbeCustomer->getRow($DBEIgnoredADDomain->getValue(DBEIgnoredADDomain::customerID));
                    $customerString = $dbeCustomer->getValue(DBECustomer::name);
                }
                echo json_encode(
                    [
                        "id"             => $DBEIgnoredADDomain->getValue(DBEIgnoredADDomain::ignoredADDomainID),
                        "domain"         => $DBEIgnoredADDomain->getValue(DBEIgnoredADDomain::domain),
                        "customerID"     => $DBEIgnoredADDomain->getValue(DBEIgnoredADDomain::customerID),
                        "customerString" => $customerString
                    ],
                    JSON_NUMERIC_CHECK
                );
                break;
            case 'getData':
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
                echo json_encode(
                    $data,
                    JSON_NUMERIC_CHECK
                );
                break;
            case 'displayForm':
            default:
                $this->displayForm();
                break;
        }
    }

    /**
     * Export expenses that have not previously been exported
     * @access private
     * @throws Exception
     * @throws Exception
     * @throws Exception
     * @throws Exception
     * @throws Exception
     */
    function displayForm()
    {
        $this->setPageTitle('Ignored AD Domains');
        $this->setTemplateFiles(
            'IgnoredADDomains',
            'IgnoredADDomains'
        );
        $this->template->parse(
            'CONTENTS',
            'IgnoredADDomains',
            true
        );
        $URLDeleteItem = $this->buildLink(
            $_SERVER['PHP_SELF'],
            [
                'action' => 'delete'
            ]
        );
        $URLUpdateItem = $this->buildLink(
            $_SERVER['PHP_SELF'],
            [
                'action' => 'update'
            ]
        );
        $URLCreateItem = $this->buildLink(
            $_SERVER['PHP_SELF'],
            [
                'action' => 'create'
            ]
        );
        $URLGetData    = $this->buildLink(
            $_SERVER['PHP_SELF'],
            [
                'action' => 'getData'
            ]
        );
        $this->template->setVar(
            [
                "URLDeleteItem" => $URLDeleteItem,
                "URLUpdateItem" => $URLUpdateItem,
                "URLAddItem"    => $URLCreateItem,
                "URLGetData"    => $URLGetData
            ]
        );
        $this->parsePage();
    }
}// end of class
