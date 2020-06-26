<?php

/**
 * My Account controller class
 * CNC Ltd
 *
 * @access public
 * @authors Mustafa Taha
 */
global $cfg;
require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg ['path_bu'] . '/BUServiceRequestReport.inc.php');
require_once($cfg ['path_bu'] . '/BUCustomer.inc.php');
require_once($cfg ['path_dbe'] . '/DSForm.inc.php');

class CTSRSource extends CTCNC
{
    public $dsPrintRange;
    public $dsSearchForm;
    public $dsResults;
    public $buServiceRequestReport;

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
        if (!$this->isUserSDManager()) {
            Header("Location: /NotAllowed.php");
            exit;
        }
        $this->setMenuId(224);
        $this->buServiceRequestReport = new BUServiceRequestReport ($this);
    }

    /**
     * Route to function based upon action passed
     * @throws Exception
     */
    function defaultAction()
    {
        switch ($this->getAction()) {
            case "searchSR":
                echo json_encode($this->search());
                exit;
            break;          
            default:
                $this->setTemplate();
        }
    }

    function search()
    {
        $this->setMethodName('search');
        $dsSearchForm = new DSForm($this);
        $this->buServiceRequestReport->initialiseSearchForm($dsSearchForm);
        $body = json_decode(file_get_contents('php://input'));

        if (!$body->fromDate) {
            $dsSearchForm->setUpdateModeUpdate();
            $dsSearchForm->setValue(
                BUServiceRequestReport::searchFormFromDate,
                date(
                    'Y-m-d',
                    strtotime("-1 year")
                )
            );
            $dsSearchForm->post();
        } else
            $dsSearchForm->setValue(
                BUServiceRequestReport::searchFormFromDate,
                date(
                    'Y-m-d',
                    strtotime($body->fromDate)
                )
            );

        if (!$body->toDate) {
            $dsSearchForm->setUpdateModeUpdate();
            $dsSearchForm->setValue(
                BUServiceRequestReport::searchFormToDate,
                date('Y-m-d')
            );
            $dsSearchForm->post();
        } else $dsSearchForm->setValue(
            BUServiceRequestReport::searchFormToDate,
            date(
                'Y-m-d',
                strtotime($body->toDate)
            )
        );
        $dsSearchForm->setValue(
            BUServiceRequestReport::searchFormCustomerID,
            $body->customerID
        );
        $results = $this->buServiceRequestReport->search($dsSearchForm);         
        return $results->fetch_all(MYSQLI_ASSOC);            
         
    }
    function setTemplate()
    {

        $this->setMethodName('search');   
        $this->setPageTitle('Service Request Source');
        $this->setTemplateFiles(
            'SRSource',
            'SRSource.inc'
        );
      
        $this->template->parse(
            'CONTENTS',
            'SRSource',
            true
        );
        $this->parsePage();

    } 
   
}
