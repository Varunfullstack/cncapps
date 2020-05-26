<?php
/**
 * Created by PhpStorm.
 * User: fizdalf
 * Date: 22/08/2018
 * Time: 10:39
 */
global $cfg;
require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_bu'] . '/BUActivity.inc.php');

class CTCreateSalesRequest extends CTCNC
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
        $roles = [
            SALES_PERMISSION,
        ];
        $this->setMenuId(304);
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

        switch ($this->getAction()) {
            case 'createSalesRequest':
                if (!isset($_REQUEST['customerID'])) {
                    echo json_encode(["error" => "Customer ID is missing"]);
                    http_response_code(400);
                    exit;
                }
                $customerID = $_REQUEST['customerID'];

                if (!isset($_REQUEST['message'])) {
                    echo json_encode(["error" => "Message is missing"]);
                    http_response_code(400);
                    exit;
                }
                $message = $_REQUEST['message'];
                if (!isset($_REQUEST['type'])) {
                    echo json_encode(["error" => "Type is missing"]);
                    http_response_code(400);
                    exit;
                }
                $type = $_REQUEST['type'];
                $files = @$_FILES['file'];
                try {
                    $this->createSalesRequest($customerID, $message, $type, $files);
                } catch (\Exception $exception) {
                    echo json_encode(["error" => $exception->getMessage()]);
                    http_response_code(400);
                    exit;
                }
                echo json_encode(["status" => "ok"]);
                break;
            default:
                $this->showPage();
                break;
        }
    }

    function createSalesRequest($customerID, $message, $type, $files = null)
    {
        $buActivity = new BUActivity($this);
        $buActivity->sendSalesRequest(null, $message, $type, true, $customerID, $files);
    }

    /**
     * @throws Exception
     */
    function showPage()
    {
        $this->setTemplateFiles(
            'CreateSalesRequest',
            'CreateSalesRequest'
        );

        $this->setPageTitle('Create Sales Request');

        $this->template->parse(
            'CONTENTS',
            'CreateSalesRequest',
            true
        );
        $this->parsePage();


    }
}