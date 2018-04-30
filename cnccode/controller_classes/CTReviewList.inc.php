<?php
/**
 * Home controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_bu'] . '/BUUser.inc.php');
require_once($cfg['path_bu'] . '/BUCustomerNew.inc.php');

class CTReviewList extends CTCNC
{

    function __construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg)
    {
        parent::__construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg);
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

        $this->displayReviewList();

    }

    /**
     * Displays list of customers to review
     *
     */
    function displayReviewList()
    {

        $this->setMethodName('displayReviewList');

        $this->setTemplateFiles('CustomerReviewList', 'CustomerReviewList.inc');

        $this->template->set_block('CustomerReviewList', 'reviewBlock', 'reviews');

        $this->buCustomer = new BUCustomer($this);

        if (isset($_REQUEST['sortColumn'])) {
            $sortColumn = $_REQUEST['sortColumn'];
        } else {
            $sortColumn = false;
        }

        if ($this->buCustomer->getDailyCallList($dsCustomer, $sortColumn)) {

            $buUser = new BUUser($this);

            while ($dsCustomer->fetchNext()) {

                $linkURL =
                    $this->buildLink(
                        'CustomerCRM.php',
                        array(
                            'action' => 'displayEditForm',
                            'customerID' => $dsCustomer->getValue('customerID')
                        )
                    );

                if ($dsCustomer->getValue('reviewUserID')) {
                    $buUser->getUserByID($dsCustomer->getValue('reviewUserID'), $dsUser);
                    $user = $dsUser->getValue('name');
                } else {
                    $user = false;
                }

                $reportUrl =
                    $this->buildLink(
                        'ReviewList.php',
                        array()
                    );
                $this->template->set_var(

                    array(
                        'customerName' => $dsCustomer->getValue('name'),
                        'reviewDate' => $dsCustomer->getValue('reviewDate'),
                        'reviewTime' => $dsCustomer->getValue('reviewTime'),
                        'reviewAction' => substr($dsCustomer->getValue('reviewAction'), 0, 50),
                        'reviewUser' => $user,
                        'linkURL' => $linkURL,
                        'reportURL' => $reportUrl
                    )

                );

                $this->template->parse('reviews', 'reviewBlock', true);

            }

            $this->template->parse('CONTENTS', 'CustomerReviewList', true);

        }

        $this->parsePage();

    }

}// end of class
?>