<?php

/**
 * HTML SCR Generation business class
 *
 * Generates a HTML SCR report.
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
class BUHTMLSCR extends BaseObject
{
    var $_buSite = '';
    var $_buActivity = '';
    var $_buCustomerItem = '';
    var $_dsCallActivity = '';
    var $_dsSite = '';
    var $_hasServerCareContract = '';
    var $_params = array();

    /**
     * Constructor
     * @param $owner
     * @param $activityID
     * @param $params
     */
    function __construct(&$owner, $activityID, &$params)
    {
        BaseObject::__construct($owner);
        $this->_buSite = new BUSite($this);
        $this->_buActivity = new BUActivity($this);
        $this->_buCustomerItem = new BUCustomerItem($this);

        $this->_buActivity->getActivityByID($_REQUEST['callActivityID'], $this->_dsCallActivity);
        $this->_buSite->getSiteByID(
            $this->_dsCallActivity->getValue('customerID'),
            $this->_dsCallActivity->getValue('siteNo'),
            $this->_dsSite
        );
        $this->_params = $params;

        $this->_hasServerCareContract = $this->_buCustomerItem->customerHasValidServerCareContract($this->_dsCallActivity->getValue('customerID'));

    }

    /**
     * Use the parameters passed in constructor to get generate SCR report and return an HTML file in a string.
     * @return String HTML
     */
    function generateFile()
    {

        $this->produceReport();

        return $this->template->get_var('output');
    }

    function produceReport()
    {
        // local refs
        $dsCallActivity = &$this->_dsCallActivity;
        $params = &$this->_params;
        $dsSite = &$this->_dsSite;


        // set up new html file template
        $this->template = new Template (EMAIL_TEMPLATE_DIR, "remove");

        $this->template->set_file(
            array(
                'page' => 'ServiceCallEmail.inc.html',
                'ServiceCallEmailNewEquipment' => 'ServiceCallEmailNewEquipment.inc.html',
                'ServiceCallEmailServerChecks' => 'ServiceCallEmailServerChecks.inc.html'
            )
        );

        $this->noteHead();

        if ($params['newSerialNumbers']) {
            $newSerialNumbers = $params['newSerialNumbers'];
        } else {
            $newSerialNumbers = 'None';
        }

        $this->template->set_var(
            array(
                'newSerialNumbers' => $newSerialNumbers,
                'reason' => $this->_dsCallActivity->getValue('reason')
            )
        );
        /*
        Only need rest if no server care contract
        */
        $numberOfServers = count($params['disk1Name']);

        if (!$this->_hasServerCareContract) {

            $this->template->set_block('ServiceCallEmailServerChecks', 'serverBlock', 'servers');

            for ($serverNumber = 1; $serverNumber <= $numberOfServers; $serverNumber++) {

                $this->template->set_var(
                    array(
                        'serverName' => $params['serverName'][$serverNumber],
                        'disk1Name' => $params['disk1Name'][$serverNumber],
                        'disk1Total' => $paramsm['disk1Total'][$serverNumber],
                        'disk1Free' => $params['disk1Free'][$serverNumber],
                        'disk2Name' => $params['disk2Name'][$serverNumber],
                        'disk2Total' => $params['disk2Total'][$serverNumber],
                        'disk2Free' => $params['disk2Free'][$serverNumber],
                        'disk3Name' => $params['disk3Name'][$serverNumber],
                        'disk3Total' => $params['disk3Total'][$serverNumber],
                        'disk3Free' => $params['disk3Free'][$serverNumber],
                        'antiVirusServerApp' => $params['antiVirusServerApp'][$serverNumber],
                        'antiVirusServerDAT' => $params['antiVirusServerDAT'][$serverNumber],
                        'antiVirusServerEng' => $params['antiVirusServerEng'][$serverNumber],
                        'antiVirusEmailApp' => $params['antiVirusEmailApp'][$serverNumber],
                        'antiVirusEmailDAT' => $params['antiVirusEmailDAT'][$serverNumber],
                        'antiVirusEmailEng' => $params['antiVirusEmailEng'][$serverNumber],
                        'backupApp' => $params['backupApp'][$serverNumber],
                        'backupLastResult' => $params['backupLastResult'][$serverNumber],
                        'isRaidArrayHealthy' => $params['isRaidArrayHealthy'][$serverNumber] ? $params['isRaidArrayHealthy'][$serverNumber] : 'No',
                        'isUPSOnline' => $params['isUPSOnline'][$serverNumber] ? $params['isUPSOnline'][$serverNumber] : 'No'
                    )
                );

                $this->template->parse('servers', 'serverBlock', true);

            } // end for

            $this->template->parse('serviceCallEmailServerChecks', 'ServiceCallEmailServerChecks', true);

        }// end has server contract
        else {
            $this->template->parse('serviceCallEmailServerChecks', '', true);
        }

        if ($params['newSerialNumbers']) {
            $this->template->parse('serviceCallEmailNewEquipment', 'ServiceCallEmailNewEquipment', true);
        } else {
            $this->template->parse('serviceCallEmailNewEquipment', '', true);
        }

        $this->template->parse('output', 'page', false);

    }

    /**
     *    Output the header.
     * This gets called once at the start of each page.
     * Where a statement spans pages it gets called many times for the same statement.
     *
     * @access private
     */
    function noteHead()
    {
        $dsCallActivity = &$this->_dsCallActivity;
        $dsSite = &$this->_dsSite;

        $this->template->set_var(
            array(
                'companyName' => $dsCallActivity->getValue('customerName'),
                'callActivityID' => $dsCallActivity->getValue('callActivityID'),
                'add1' => $dsSite->getValue(DBESite::add1),
                'add2' => $dsSite->getValue(DBESite::add2),
                'add3' => $dsSite->getValue(DBESite::add3),
                'town' => $dsSite->getValue(DBESite::town),
                'county' => $dsSite->getValue(DBESite::county),
                'postcode' => $dsSite->getValue(DBESite::postcode),
                'itSupportPhone' => CONFIG_IT_SUPPORT_PHONE,
                'phoneSystemSupportPhone'
                => CONFIG_PHONE_SYSTEM_SUPPORT_PHONE,
                'date' => date("l jS F Y", strtotime($dsCallActivity->getValue('date'))),
                'startTime' => $dsCallActivity->getValue('startTime'),
                'endTime' => $dsCallActivity->getValue('endTime'),
                'contactName' => $dsCallActivity->getValue('contactName'),
                'userName' => $dsCallActivity->getValue('userName')
            )

        );

    }

}// End of class
?>