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
require_once($cfg['path_bu'] . '/BUCustomer.inc.php');

class CTReviewList extends CTCNC
{

    /** @var BUCustomer */
    public $buCustomer;

    function __construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg)
    {
        parent::__construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg);
        $roles = [
            'sales'
        ];
        if (!self::hasPermissions($roles)) {
            Header("Location: /NotAllowed.php");
            exit;
        }

    }

    /**
     * Route to function based upon action passed
     * @throws Exception
     */
    function defaultAction()
    {
        $this->displayReviewList();
    }

    /**
     * Displays list of customers to review
     *
     * @throws Exception
     */
    function displayReviewList()
    {

        $this->setMethodName('displayReviewList');

        $this->setTemplateFiles('CustomerReviewList', 'CustomerReviewList.inc');

        $this->template->set_block('CustomerReviewList', 'reviewBlock', 'reviews');

        $this->buCustomer = new BUCustomer($this);

        if ($this->getParam('sortColumn')) {
            $sortColumn = $this->getParam('sortColumn');
        } else {
            $sortColumn = false;
        }
        $dsCustomer = new DataSet($this);
        if ($this->buCustomer->getDailyCallList($this, $dsCustomer, $sortColumn)) {

            $buUser = new BUUser($this);

            while ($dsCustomer->fetchNext()) {

                $linkURL =
                    Controller::buildLink(
                        'CustomerCRM.php',
                        array(
                            'action'     => 'displayEditForm',
                            'customerID' => $dsCustomer->getValue(DBECustomer::customerID)
                        )
                    );

                if ($dsCustomer->getValue(DBECustomer::reviewUserID)) {
                    $dsUser = new DataSet($this);
                    $buUser->getUserByID($dsCustomer->getValue(DBECustomer::reviewUserID), $dsUser);
                    $user = $dsUser->getValue(DBEUser::name);
                } else {
                    $user = false;
                }

                $reportUrl =
                    Controller::buildLink(
                        'ReviewList.php',
                        array()
                    );
                $this->template->set_var(

                    array(
                        'customerName' => $dsCustomer->getValue(DBECustomer::name),
                        'reviewDate'   => (new DateTime($dsCustomer->getValue(DBECustomer::reviewDate)))->format(
                            'd/m/Y'
                        ),
                        'reviewTime'   => $dsCustomer->getValue(DBECustomer::reviewTime),
                        'reviewAction' => substr($dsCustomer->getValue(DBECustomer::reviewAction), 0, 50),
                        'reviewUser'   => $user,
                        'linkURL'      => $linkURL,
                        'reportURL'    => $reportUrl
                    )

                );
                $this->template->parse('reviews', 'reviewBlock', true);
            }
            $this->template->parse('CONTENTS', 'CustomerReviewList', true);
        }
        $this->parsePage();
    }
}
