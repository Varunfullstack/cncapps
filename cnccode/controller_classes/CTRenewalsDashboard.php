<?php
global $cfg;
require_once($cfg["path_dbe"] . "/DBConnect.php");
require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_bu'] . '/BURenQuotation.inc.php');
require_once($cfg['path_bu'] . '/BURenContract.inc.php');
require_once($cfg['path_bu'] . '/BURenBroadband.inc.php');
require_once($cfg['path_bu'] . '/BURenDomain.inc.php');
require_once($cfg['path_bu'] . '/BURenHosting.inc.php');

class CTRenewalsDashboard extends CTCNC
{
    const CONST_RENEWALS='renewals';
    const CONST_REN_CONTRACT='renContract';
    const CONST_REN_BROADBAND='renBroadband';
    const CONST_REN_DOMAIN='renDomain';
    const CONST_REN_HOSTING='renHosting';
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
        $data=[];
        if ($dsRenQuotation->rowCount()) {            

            while ($dsRenQuotation->fetchNext()) {                
                $latestQuoteSent = null;
                if ($dsRenQuotation->getValue(DBEJRenQuotation::latestQuoteSent)) {
                    $latestQuoteSent = DateTime::createFromFormat(
                        'Y-m-d H:i:s',
                        $dsRenQuotation->getValue(
                            DBEJRenQuotation::latestQuoteSent
                        )
                    );
                }
                $sent = false;
                if ($dsRenQuotation->getValue(DBEJRenQuotation::latestQuoteSent) && $latestQuoteSent) {
                    $sent = true;
                }
                $ordheadID=$dsRenQuotation->getValue(DBEJRenQuotation::ordheadID);
                $data []=
                    [
                        'customerItemID'     =>$dsRenQuotation->getValue(DBEJRenQuotation::customerItemID),
                        'orderId'            =>$dsRenQuotation->getValue(DBEJRenQuotation::ordheadID),
                        'customerName'        => $dsRenQuotation->getValue(DBEJRenQuotation::customerName),
                        'itemDescription'     => $dsRenQuotation->getValue(DBEJRenQuotation::itemDescription),                        
                        'startDate'           => Controller::dateYMDtoDMY( $dsRenQuotation->getValue(DBEJRenQuotation::startDate)),
                        'nextPeriodStartDate' => Controller::dateYMDtoDMY(
                            $dsRenQuotation->getValue(DBEJRenQuotation::nextPeriodStartDate)
                        ),
                        'nextPeriodEndDate'   => Controller::dateYMDtoDMY(
                            $dsRenQuotation->getValue(DBEJRenQuotation::nextPeriodEndDate)
                        ),                                                                        
                        'sentQuotationColor'  => !$ordheadID ? 'white' : ($sent ? "#B2FFB2" : "#F5AEBD"),
                        'latestQuoteSent'     => $latestQuoteSent ? $latestQuoteSent->format('d/m/Y H:i:s') : null,
                        'comments'            => substr(
                            $dsRenQuotation->getValue(DBEJRenQuotation::customerItemNotes),
                            0,
                            30
                        ),
                        ];                
            }//while $dsRenQuotation->fetchNext()
        }
        return  $data;
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
        $data=[];
        if ($dsRenContract->rowCount() > 0) {            
            while ($dsRenContract->fetchNext()) {             
                    $data []=
                    array(
                        'customerItemID' => $dsRenContract->getValue(DBEJRenContract::customerItemID),
                        'customerName'    => $dsRenContract->getValue(DBEJRenContract::customerName),
                        'itemDescription' => $dsRenContract->getValue(DBEJRenContract::itemDescription),
                        'invoiceFromDate' => Controller::dateYMDtoDMY(
                            $dsRenContract->getValue(DBEJRenContract::invoiceFromDate)
                        ),
                        'invoiceToDate'   => Controller::dateYMDtoDMY(
                            $dsRenContract->getValue(DBEJRenContract::invoiceToDate)
                        ),
                        'quantity'        => $dsRenContract->getValue(DBEJRenContract::users),
                        'notes'           => Controller::dateYMDtoDMY($dsRenContract->getValue(DBEJRenContract::notes)),
                        'costAnnum'       => utf8MoneyFormat(
                            UK_MONEY_FORMAT,
                            $dsRenContract->getValue(DBEJContract::curUnitCost)
                        ),
                        'saleAnnum'       => utf8MoneyFormat(
                            UK_MONEY_FORMAT,
                            $dsRenContract->getValue(DBEJContract::curUnitSale)
                        )                         
                    );
            }
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
        $data=[];
        if ($dsRenBroadband->rowCount() > 0) {            
            while ($dsRenBroadband->fetchNext()) {
               
                $data []=                
                    array(
                        'customerItemID'    => $dsRenBroadband->getValue(DBEJRenBroadband::customerItemID),
                        'customerName'      => $dsRenBroadband->getValue(DBEJRenBroadband::customerName),
                        'itemDescription'   => $dsRenBroadband->getValue(DBEJRenBroadband::itemDescription),
                        'ispID'             => $dsRenBroadband->getValue(DBEJRenBroadband::ispID),
                        'adslPhone'         => $dsRenBroadband->getValue(DBEJRenBroadband::adslPhone),
                        'salePricePerMonth' => $dsRenBroadband->getValue(DBEJRenBroadband::salePricePerMonth),
                        'costPricePerMonth' => $dsRenBroadband->getValue(DBEJRenBroadband::costPricePerMonth),
                        'invoiceFromDate'   => Controller::dateYMDtoDMY(
                            $dsRenBroadband->getValue(DBEJRenBroadband::invoiceFromDate)
                        ),
                        'invoiceToDate'     => Controller::dateYMDtoDMY(
                            $dsRenBroadband->getValue(DBEJRenBroadband::invoiceToDate)
                        ),                         
                    );
            }
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
        $data=[];
        if ($dsRenDomain->rowCount() > 0) {            
            while ($dsRenDomain->fetchNext()) {
                 $data []=
                    array(
                        'customerItemID'  => $dsRenDomain->getValue(DBEJCustomerItem::customerItemID),
                        'customerName'    => $dsRenDomain->getValue(DBEJCustomerItem::customerName),
                        'itemDescription' => $dsRenDomain->getValue(DBEJCustomerItem::itemDescription),
                        'domain'          => $dsRenDomain->getValue(DBEJCustomerItem::notes),
                        'invoiceFromDate' => Controller::dateYMDtoDMY(
                            $dsRenDomain->getValue(DBEJCustomerItem::invoiceFromDate)
                        ),
                        'invoiceToDate'   => Controller::dateYMDtoDMY(
                            $dsRenDomain->getValue(DBEJCustomerItem::invoiceToDate)
                        ),
                    );
            }
        }        
        return  $data;
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
        $data=[];
        if ($dsRenHosting->rowCount() > 0) {            
            while ($dsRenHosting->fetchNext()) {                
                $data []=
                    array(
                        'customerItemID'  => $dsRenHosting->getValue(DBEJRenHosting::customerItemID),
                        'customerName'    => $dsRenHosting->getValue(DBEJRenHosting::customerName),
                        'itemDescription' => $dsRenHosting->getValue(DBEJRenHosting::itemDescription),
                        'invoiceFromDate' => Controller::dateYMDtoDMY(
                            $dsRenHosting->getValue(DBEJRenHosting::invoiceFromDate)
                        ),
                        'invoiceToDate'   => Controller::dateYMDtoDMY(
                            $dsRenHosting->getValue(DBEJRenHosting::invoiceToDate)
                        ),     
                        'notes'                      => Controller::htmlTextArea(
                            $dsRenHosting->getValue(DBEJRenHosting::internalNotes)
                        )                   
                    );
            }
        }        
        return $data;
    }
}
