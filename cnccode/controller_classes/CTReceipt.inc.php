<?php
/**
 * Expense controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
global

use Intervention\Image\ImageManagerStatic;

$cfg;
require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_bu'] . '/BUExpense.inc.php');
require_once($cfg['path_bu'] . '/BUExpenseType.inc.php');
require_once($cfg['path_dbe'] . '/DSForm.inc.php');
require_once($cfg['path_dbe'] . '/DBEReceipt.php');

class CTReceipt extends CTCNC
{
    /** @var DSForm */
    public $dsExpenseExport;
    /** @var DSForm */
    public $dsSearchForm;
    /** @var DSForm */
    public $dsSearchResults;
    /** @var BUExpense */
    public $buExpense;
    /** @var DSForm */
    private $dsExpense;

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
            "technical",
            "sales"
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
        switch ($this->getAction()) {
            case 'show':
                $receiptID = $this->getParam('receiptID');
                if (!$receiptID) {
                    $this->displayFatalError('Receipt ID required');
                }
                $dbeReceipt = new DBEReceipt($this);
                $dbeReceipt->getRow($receiptID);
                $expenseID  = $dbeReceipt->getValue(DBEReceipt::expenseId);
                $dbeExpense = new DBEExpense($this);
                $dbeExpense->getRow($expenseID);
                $relatedActivityID = $dbeExpense->getValue(DBEExpense::callActivityID);
                $dbeCallActivity   = new DBECallActivity($this);
                $dbeCallActivity->getRow($relatedActivityID);
                if (!$this->dbeUser->getValue(DBEUser::isExpenseApprover) && $dbeCallActivity->getValue(
                        DBECallActivity::userID
                    ) != $this->userID) {
                    $this->displayFatalError('You cannot access this receipt');
                }
                // all good ...so we should show the file
                header("Content-Type: " . $dbeReceipt->getValue(DBEReceipt::fileMIMEType));
                header("Content-Disposition: inline;");
                readfile(RECEIPT_PATH . $dbeReceipt->getValue(DBEReceipt::filePath));
                break;
            case 'upload':
                $expenseID = $this->getParam('expenseID');
                try {

                    // Undefined | Multiple Files | $_FILES Corruption Attack
                    // If this request falls under any of them, treat it invalid.
                    if (!isset($_FILES['upfile']['error']) || is_array($_FILES['upfile']['error'])) {
                        throw new RuntimeException('Invalid parameters.');
                    }
                    // Check $_FILES['upfile']['error'] value.
                    switch ($_FILES['upfile']['error']) {
                        case UPLOAD_ERR_OK:
                            break;
                        case UPLOAD_ERR_NO_FILE:
                            throw new RuntimeException('No file sent.');
                        case UPLOAD_ERR_INI_SIZE:
                        case UPLOAD_ERR_FORM_SIZE:
                            throw new RuntimeException('Exceeded filesize limit. 1');
                        default:
                            throw new RuntimeException('Unknown errors.');
                    }
                    $receiptID = $this->upload($expenseID, $_FILES['upfile']['tmp_name']);
                    $response  = ["status" => "ok", "receiptId" => $receiptID];
                } catch (Exception $exception) {
                    $response = ["error" => $exception->getMessage()];
                    http_response_code(400);
                }
                echo json_encode($response);
                break;
            default:
                $this->displayFatalError('No valid action passed');
                exit;
                break;
        }
    }

    function upload($expenseID, $fileUploaded)
    {
        if (!$expenseID) {
            throw new RuntimeException('Expense ID required');
        }
        if (!$fileUploaded) {
            throw new RuntimeException('File is required');
        }
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($fileUploaded);
        if (false === $ext = array_search(
                $mimeType,
                array(
                    'jpg' => 'image/jpeg',
                    'pdf' => 'application/pdf',
                    'png' => 'image/png'
                ),
                true
            )) {
            throw new RuntimeException('Invalid type of file');
        }
        $fileName = uniqid('receipt') . "." . $ext;
        $filePath = RECEIPT_PATH . $fileName;
        if ($ext === "pdf") {
            if (filesize($fileUploaded) > 1024 * 1024) {
                throw new RuntimeException('The file is too big, max 1MB');
            }
            rename($fileUploaded, $filePath);
        }
        if ($ext === 'jpg' || $ext == 'png') {
            $image = ImageManagerStatic::make($fileUploaded);
            $image->resize(800, 800);
            $image->save($filePath);
        }
        $dbeReceipt = new DBEReceipt($this);
        $dbeReceipt->setValue(DBEReceipt::fileMIMEType, $mimeType);
        $dbeReceipt->setValue(DBEReceipt::filePath, $fileName);
        $dbeReceipt->setValue(DBEReceipt::expenseId, $expenseID);
        $dbeReceipt->insertRow();
        return $dbeReceipt->getPKValue();
    }
}
