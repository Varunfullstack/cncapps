<?php
/**
 * Staff Availablity controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_bu'] . '/BUStaffAvailable.inc.php');
// Actions
define('CTSTAFF_AVAILABLE_ACT_DISPLAY_LIST', 'list');
define('CTSTAFF_AVAILABLE_ACT_UPDATE', 'update');

class CTStaffAvailable extends CTCNC
{
    var $buStaffAvailable = '';

    function __construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg)
    {
        parent::__construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg);
        $this->buStaffAvailable = new BUStaffAvailable($this);
    }

    /**
     * Route to function based upon action passed
     */
    function defaultAction()
    {
        $this->checkPermissions(PHPLIB_PERM_ACCOUNTS);
        switch ($_REQUEST['action']) {
            case CTSTAFF_AVAILABLE_ACT_UPDATE:
                $this->update();
                break;
            case CTSTAFF_AVAILABLE_ACT_DISPLAY_LIST:
            default:
                $this->displayList();
                break;
        }
    }

    /**
     * Display list of staffAvailables
     * @access private
     */
    function displayList()
    {
        $this->setMethodName('displayList');
        $this->setPageTitle('Staff Availability');

        $this->setTemplateFiles(
            array('StaffAvailableList' => 'StaffAvailableList.inc')
        );

        $this->buStaffAvailable->createRecordsForToday($dsStaffAvailable);

        $this->buStaffAvailable->getAllStaffAvailable($dsStaffAvailable);

        $urlUpdate =
            $this->buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action' => CTSTAFF_AVAILABLE_ACT_UPDATE
                )
            );

        $this->template->set_var(
            array('urlCreate' => $urlCreate)
        );

        if ($dsStaffAvailable->rowCount() > 0) {

            $this->template->set_block('StaffAvailableList', 'staffAvailableBlock', 'staffAvailables');

            while ($dsStaffAvailable->fetchNext()) {

                $this->template->set_var(
                    array(
                        'staffAvailableID' => $dsStaffAvailable->getValue('staffAvailableID'),
                        'firstName' => Controller::htmlDisplayText($dsStaffAvailable->getValue('firstName')),
                        'lastName' => Controller::htmlDisplayText($dsStaffAvailable->getValue('lastName')),
                        'amChecked' => $dsStaffAvailable->getValue('am') > 0 ? CT_CHECKED : '',
                        'pmChecked' => $dsStaffAvailable->getValue('pm') > 0 ? CT_CHECKED : '',
                        'urlUpdate' => $urlUpdate
                    )
                );

                $this->template->parse('staffAvailables', 'staffAvailableBlock', true);

            }//while $dsStaffAvailable->fetchNext()
        }
        $this->template->parse('CONTENTS', 'StaffAvailableList', true);
        $this->parsePage();
    }

    /**
     * Update details
     *
     * The data comes from the form in an array
     *
     * array(
     *    staffavailableID => value
     *    am => value,
     *    pm =>value
     * )
     *
     * @access private
     */
    function update()
    {
        $this->setMethodName('update');

        $this->buStaffAvailable->updateStaffAvailable($_REQUEST['staffAvailable']);

        $urlNext =
            $this->buildLink($_SERVER['PHP_SELF'],
                array(
                    'action' => CTSTAFF_AVAILABLE_ACT_DISPLAY_LIST
                )
            );
        header('Location: ' . $urlNext);
    }
}// end of class
?>