<?php
/**
 * Created by PhpStorm.
 * User: fizdalf
 * Date: 31/08/2018
 * Time: 12:42
 */

require_once($cfg['path_bu'] . '/BUSalesOrder.inc.php');
require_once($cfg['path_bu'] . '/BUDespatch.inc.php');
require_once($cfg["path_bu"] . "/BURenewal.inc.php");
require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_ct'] . '/CTDeliveryNotes.inc.php');
require_once($cfg['path_gc'] . '/DataSet.inc.php');
require_once($cfg['path_dbe'] . '/DSForm.inc.php');

class CTDirectDebitInvoicing extends CTCNC
{

    private $dsPrintRange;

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
            'accounts'
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

            case 'preview':
                $this->preview();
                break;
            case 'sendInvoices';
                $this->sendInvoices();
                break;
            default:
                $this->displayStuff();
                break;
        }
    }

    public function displayStuff()
    {
        $this->setMethodName('display thing');


        $urlSubmit = $this->buildLink(
            $_SERVER['PHP_SELF'],
            array(
                'action' => "sendInvoices"
            )
        );
        $this->setPageTitle('Unprinted Invoices');
        $this->setTemplateFiles(
            'DirectDebitInvoicing',
            'DirectDebitInvoicing'
        );
        $this->template->set_var(
            array(
                'urlSubmit' => $urlSubmit
            )
        );
        $this->template->parse(
            'CONTENTS',
            'DirectDebitInvoicing',
            true
        );
        $this->parsePage();

    }


    private function sendInvoices()
    {
        $buInvoice = new BUInvoice($this);
        // generate PDF invoices:
        $invoiceCount = $buInvoice->printDirectDebitInvoices(
            date('Y-m-01'),
            $_REQUEST['passphrase']
        );

        if ($invoiceCount == 0) {
            $this->setFormErrorMessage('There aren\'t any Un-sent invoices');

        } else {
            $this->setFormErrorMessage($invoiceCount . 'Invoices Sent');
        }

        $this->displayStuff();
    }

    private function preview()
    {
        // I need to pull the direct debits
        $buInvoice = new BUInvoice($this);

        $pdfFile =
            $buInvoice->trialPrintUnprintedInvoices(
                date('Y-m-01'),
                true
            );
        if ($pdfFile) {
            header('Pragma: public');
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename=invoices.pdf;');
            header('Content-Transfer-Encoding: binary');
            header('Content-Length: ' . filesize($pdfFile));
            readfile($pdfFile);
            unlink($pdfFile);
            exit;
        }
        http_response_code(400);
    }

}