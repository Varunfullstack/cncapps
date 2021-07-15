<?php
global $cfg;

use CNCLTD\Business\BURenContract;
use CNCLTD\Utils;

require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_bu'] . '/BURenQuotation.inc.php');
require_once($cfg['path_bu'] . '/Burencontract.php');
require_once($cfg['path_bu'] . '/BURenBroadband.inc.php');
require_once($cfg['path_bu'] . '/BURenDomain.inc.php');
require_once($cfg['path_bu'] . '/BURenHosting.inc.php');

class CTRenewalsDashboard extends CTCNC
{
    const CONST_RENEWALS      = 'renewals';
    const CONST_REN_CONTRACT  = 'renContract';
    const CONST_REN_BROADBAND = 'renBroadband';
    const CONST_REN_DOMAIN    = 'renDomain';
    const CONST_REN_HOSTING   = 'renHosting';

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
            $cfg,
            false
        );
        $roles = [RENEWALS_PERMISSION, TECHNICAL_PERMISSION];
        if (!self::hasPermissions($roles)) {
            Header("Location: /NotAllowed.php");
            exit;
        }
        $this->setMenuId(601);
    }


    /**
     * Route to function based upon action passed
     * @throws Exception
     */
    function defaultAction()
    {
        $this->requestMethod;
        switch ($this->getAction()) {
            case self::CONST_RENEWALS:
                echo json_encode($this->getRenewalsData());
                break;
            case self::CONST_REN_CONTRACT:
                echo json_encode($this->getRenContractData());
                break;
            case self::CONST_REN_BROADBAND:
                echo json_encode($this->getRenBroadbandData());
                break;
            case self::CONST_REN_DOMAIN:
                echo json_encode($this->getRenDomainData());
                break;
            case self::CONST_REN_HOSTING:
                echo json_encode($this->getRenHostingData());
                break;
            default:
                $this->setTemplate();
                break;
        }
    }

    function setTemplate()
    {

        $this->setPageTitle('Renewals Dashboard');
        $this->setTemplateFiles(
            array('RenewalsDashboard' => 'RenewalsDashboard.rct')
        );
        $this->loadReactScript('RenewalsDashboardComponent.js');
        $this->loadReactCSS('RenewalsDashboardComponent.css');
        $this->template->parse(
            'CONTENTS',
            'RenewalsDashboard',
            true
        );
        $this->parsePage();
    }

    /**
     * Display list of types
     * @access private
     * @throws Exception
     */
    function getRenewalsData()
    {
        $this->setMethodName('getRenewalsData');
        //order customerName
        $dsRenQuotation = new DataSet($this);
        $buRenQuotation = new BURenQuotation($this);
        $buRenQuotation->getAll(
            $dsRenQuotation,
            'customerName'
        );
        $data = [];
        while ($dsRenQuotation->fetchNext()) {
            $data [] = [
                'customerItemID'      => $dsRenQuotation->getValue(DBEJRenQuotation::customerItemID),
                'orderId'             => $dsRenQuotation->getValue(DBEJRenQuotation::ordheadID),
                'customerName'        => $dsRenQuotation->getValue(DBEJRenQuotation::customerName),
                'itemDescription'     => $dsRenQuotation->getValue(DBEJRenQuotation::itemDescription),
                'startDate'           => $dsRenQuotation->getValue(DBEJRenQuotation::startDate),
                'nextPeriodStartDate' => $this->getYMDDateFromDMY(
                    $dsRenQuotation->getValue(DBEJRenQuotation::nextPeriodStartDate)
                ),
                'nextPeriodEndDate'   => $this->getYMDDateFromDMY(
                    $dsRenQuotation->getValue(DBEJRenQuotation::nextPeriodEndDate)
                ),
                'latestQuoteSent'     => $dsRenQuotation->getValue(DBEJRenQuotation::latestQuoteSent),
                'comments'            => substr(
                    $dsRenQuotation->getValue(DBEJRenQuotation::customerItemNotes),
                    0,
                    30
                ),
                'costAnnum'           => $dsRenQuotation->getValue(DBEJRenQuotation::costPrice),
                'saleAnnum'           => $dsRenQuotation->getValue(DBEJRenQuotation::salePrice),
            ];
        }
        return $data;
    }

    /**
     * get ren contracts
     * @access private
     * @throws Exception
     */
    function getRenContractData()
    {
        $this->setMethodName('getRenContractData');
        $dsRenContract = new DataSet($this);
        $buRenContract = new BURenContract($this);
        $buRenContract->getAll(
            $dsRenContract,
            $this->getParam('orderBy')
        );
        $data = [];
        while ($dsRenContract->fetchNext()) {
            $data [] = array(
                'customerItemID'  => $dsRenContract->getValue(DBEJRenContract::customerItemID),
                'customerName'    => $dsRenContract->getValue(DBEJRenContract::customerName),
                'itemDescription' => $dsRenContract->getValue(DBEJRenContract::itemDescription),
                'invoiceFromDate' => $dsRenContract->getValue(DBEJRenContract::invoiceFromDateYMD),
                'invoiceToDate'   => $dsRenContract->getValue(DBEJRenContract::invoiceToDateYMD),
                'quantity'        => $dsRenContract->getValue(DBEJRenContract::users),
                'notes'           => $dsRenContract->getValue(DBEJRenContract::notes),
                'costAnnum'       => $dsRenContract->getValue(DBEJContract::curUnitCost),
                'saleAnnum'       => $dsRenContract->getValue(DBEJContract::curUnitSale),
            );
        }
        return $data;
    }

    /**
     * get Ren Broadband
     * @access private
     * @throws Exception
     */
    function getRenBroadbandData()
    {
        $this->setMethodName('getRenBroadbandData');
        $dsRenBroadband = new DataSet($this);
        $buRenBroadband = new BURenBroadband($this);
        $buRenBroadband->getAll(
            $dsRenBroadband,
            $this->getParam('orderBy')
        );
        $data = [];
        while ($dsRenBroadband->fetchNext()) {
            $data [] = array(
                'customerItemID'    => $dsRenBroadband->getValue(DBEJRenBroadband::customerItemID),
                'customerName'      => $dsRenBroadband->getValue(DBEJRenBroadband::customerName),
                'itemDescription'   => $dsRenBroadband->getValue(DBEJRenBroadband::itemDescription),
                'ispID'             => $dsRenBroadband->getValue(DBEJRenBroadband::ispID),
                'adslPhone'         => $dsRenBroadband->getValue(DBEJRenBroadband::adslPhone),
                'salePricePerMonth' => $dsRenBroadband->getValue(DBEJRenBroadband::salePricePerMonth),
                'costPricePerMonth' => $dsRenBroadband->getValue(DBEJRenBroadband::costPricePerMonth),
                'invoiceFromDate'   => $dsRenBroadband->getValue(DBEJRenBroadband::invoiceFromDateYMD),
                'invoiceToDate'     => $dsRenBroadband->getValue(DBEJRenBroadband::invoiceToDateYMD),
            );
        }
        return $data;
    }

    /**
     * get ren domain data
     * @access private
     * @throws Exception
     */
    function getRenDomainData()
    {
        $this->setMethodName('getRenDomainData');
        $dsRenDomain = new DataSet($this);
        $buRenDomain = new BURenDomain($this);
        $buRenDomain->getAll(
            $dsRenDomain,
            $this->getParam('orderBy')
        );
        $data = [];
        if ($dsRenDomain->rowCount() > 0) {
            while ($dsRenDomain->fetchNext()) {
                $data [] = array(
                    'customerItemID'  => $dsRenDomain->getValue(DBEJCustomerItem::customerItemID),
                    'customerName'    => $dsRenDomain->getValue(DBEJCustomerItem::customerName),
                    'itemDescription' => $dsRenDomain->getValue(DBEJCustomerItem::itemDescription),
                    'domain'          => $dsRenDomain->getValue(DBEJCustomerItem::notes),
                    'invoiceFromDate' => $dsRenDomain->getValue(DBEJCustomerItem::invoiceFromDateYMD),
                    'invoiceToDate'   => $dsRenDomain->getValue(DBEJCustomerItem::invoiceToDateYMD),
                    'costAnnum'       => $dsRenDomain->getValue(DBEJCustomerItem::costPrice),
                    'saleAnnum'       => $dsRenDomain->getValue(DBEJCustomerItem::salePrice),
                );
            }
        }
        return $data;
    }

    /**
     * Get Hosting data
     * @access private
     * @throws Exception
     */
    function getRenHostingData()
    {
        $this->setMethodName('getRenHostingData');
        $buRenHosting = new BURenHosting($this);
        $dsRenHosting = new DataSet($this);
        $buRenHosting->getAll(
            $dsRenHosting,
            $this->getParam('orderBy')
        );
        $data = [];
        if ($dsRenHosting->rowCount() > 0) {
            while ($dsRenHosting->fetchNext()) {
                $data [] = array(
                    'customerItemID'  => $dsRenHosting->getValue(DBEJRenHosting::customerItemID),
                    'customerName'    => $dsRenHosting->getValue(DBEJRenHosting::customerName),
                    'itemDescription' => $dsRenHosting->getValue(DBEJRenHosting::itemDescription),
                    'invoiceFromDate' => $dsRenHosting->getValue(DBEJRenHosting::invoiceFromDateYMD),
                    'invoiceToDate'   => $dsRenHosting->getValue(DBEJRenHosting::invoiceToDateYMD),
                    'notes'           => Controller::htmlTextArea(
                        $dsRenHosting->getValue(DBEJRenHosting::internalNotes)
                    ),
                    'costAnnum'       => $dsRenHosting->getValue(DBEJRenHosting::curUnitCost),
                    'saleAnnum'       => $dsRenHosting->getValue(DBEJRenHosting::curUnitSale),
                );
            }
        }
        return $data;
    }

    /**
     * @param string|null $dateString
     * @return string|null
     */
    private function getYMDDateFromDMY(?string $dateString): ?string
    {
        if (!$dateString) {
            return "";
        }
        $dateTime = DateTime::createFromFormat(
            'd/m/Y',
            $dateString
        );
        if (!$dateTime) {
            return "";
        }
        return Utils::dateTimeToString(
            $dateTime,
            DATE_MYSQL_DATE
        );
    }
}
