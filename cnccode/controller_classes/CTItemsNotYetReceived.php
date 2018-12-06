<?php
/**
 * Created by PhpStorm.
 * User: fizdalf
 * Date: 05/12/2018
 * Time: 12:43
 */
require_once($cfg['path_ct'] . '/CTCNC.inc.php');

class CTItemsNotYetReceived extends CTCNC
{
    /**
     * Dataset for item record storage.
     *
     * @var     DSForm
     * @access  private
     */
    var $dsItem = '';

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
        $roles = [
            "sales",
            "technical"
        ];
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
        switch ($_REQUEST['action']) {

            default:
                $this->displayContractAndNumbersReport();
                break;
        }
    }

    function displayContractAndNumbersReport()
    {

        $this->setPageTitle("Service Contracts Ratio");

        $this->setTemplateFiles(
            'ItemsNotYetReceived',
            'ItemsNotYetReceived'
        );


        $db = $this->getContractAndNumberData();


        $this->template->set_block(
            'ItemsNotYetReceived',
            'contractItemBlock',
            'contracts'
        );

        while ($db->next_record()) {
            $row = $db->Record;
            $this->template->set_var(
                array(
                    'customerName'                => $row["customerName"],
                    'serviceDeskProduct'          => $row['serviceDeskProduct'],
                    'serviceDeskUsers'            => $row['serviceDeskUsers'],
                    'serviceDeskContract'         => $row['serviceDeskContract'],
                    'serviceDeskCostPerUserMonth' => $row['serviceDeskCostPerUserMonth'],
                    'serverCareProduct'           => $row['serverCareProduct'],
                    'virtualServers'              => $row['virtualServers'],
                    'physicalServers'             => $row['physicalServers'],
                    'serverCareContract'          => $row['serverCareContract']

                )
            );

            $this->template->parse(
                'contracts',
                'contractItemBlock',
                true
            );
        }


        $this->template->parse(
            'CONTENTS',
            'ItemsNotYetReceived',
            true
        );


        $this->parsePage();
    }


}// end of class
?>