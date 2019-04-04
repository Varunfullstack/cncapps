<?php /**
 * SCO transactions business class
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg["path_gc"] . "/Business.inc.php");
require_once($cfg["path_dbe"] . "/DBEScoTrans.inc.php");
require_once($cfg["path_dbe"] . "/DBEOrdheadSCO.inc.php");
require_once($cfg["path_dbe"] . "/DBEOrdlineSCO.inc.php");
require_once($cfg["path_dbe"] . "/DBEJOrdlineSCO.inc.php");
require_once($cfg["path_dbe"] . "/DBEOrdhead.inc.php");
require_once($cfg["path_dbe"] . "/DBEOrdline.inc.php");

class BUScoTrans extends Business
{
    var $dbeScoTrans = "";
    var $logfile = "";

    /**
     * Constructor
     * @access Public
     * @param $owner
     */
    function __construct(&$owner)
    {
        parent::__construct($owner);
        $this->dbeScoTrans = new DBEScoTrans($this);
    }

    /**
     * Read transactions from the DBEScoTrans entity and create a transaction text file
     * @return bool : Sucess
     * @access public
     */
    function writeLog($message)
    {
        $this->logfile = fopen(LOG_DIR . 'transaction.txt', "ab");
        if ($this->logfile == FALSE) {
            die ('Could not open log file ' . LOG_DIR . 'transaction.txt');
        }
        $string = date('d-m-Y H:i:s') . '|' . $this->getMethodName() . '|' . $message . chr(10) . chr(13);
        fwrite($this->logfile, $string, strlen($string));
        fclose($this->logfile);
    }

    /**
     * Read transactions from the DBEScoTrans entity and create a transaction text file
     * @return bool : Sucess
     * @access public
     */
    function processTransactionsOut()
    {
        $this->setMethodName('processTransactionsOut');
//		$this->writeLog('Start');
        $this->dbeScoTrans->getRows();
        $ret = ($this->getData($this->dbeScoTrans, $dsScoTrans));
        if ($dsScoTrans->fetchNext()) {
            $fileName = SCO_TRANSACTION_IN_DIR . '/' . date('YmdH:i:s');
            $pointer = fopen("ftp://" . SCO_USER . ":" . SCO_PASSWORD . "@" . SCO_HOST . "/" . $fileName, "w");
            if (!$pointer) {
                $this->writeLog('Unable to create file ' . $fileName . ' on ' . SCO_HOST);
            } else {
                do {
                    fwrite(
                        $pointer,
                        $dsScoTrans->getValue(DBEScoTrans::statement) . ";\n"
                    );
                    // Remove DB row
                    $this->dbeScoTrans->setValue(DBEScoTrans::scoTransID, $dsScoTrans->getValue(DBEScoTrans::scoTransID));
                    $this->dbeScoTrans->deleteRow();
                    $this->writeLog('Created ' . $fileName . ':' . $dsScoTrans->getValue(DBEScoTrans::statement) . ' on SCO BOX');
                } while ($dsScoTrans->fetchNext());
                fclose($pointer);
            }
        }
        return $ret;
    }

    /**
     * Read transaction files from the remote SCO box and run statements against mySQL server
     * @return bool : Sucess
     * @access public
     */
    function processTransactionsIn()
    {
        $this->setMethodName('processTransactionsIn');
        $ftp = ftp_connect(SCO_HOST);
        if ($ftp) {
            if (ftp_login($ftp, SCO_USER, SCO_PASSWORD)) {
                // Process list of transaction files on remote box
                $fileNames = ftp_nlist($ftp, SCO_TRANSACTION_OUT_DIR);
                foreach ($fileNames as $fileName) {
                    if ((basename($fileName) != '.') & (basename($fileName) != '..')) {  // exclude current & parent directory
                        $ftpURL = "ftp://" . SCO_USER . ":" . SCO_PASSWORD . "@" . SCO_HOST . '/' . $fileName;
                        $pointer = fopen($ftpURL, "rb");
                        if ($pointer == FALSE) {
                            $this->writeLog('Could not open remote file ' . $ftpURL);
                        } else {
                            $this->writeLog('Processing file ' . $ftpURL);
                            while (!feof($pointer)) {
                                $statement = rtrim(fgets($pointer, 2000));
                                if ($statement != '') {                            // I dont understand why some lines are blank!!
                                    $this->dbeScoTrans->setQueryString($statement);
                                    $this->dbeScoTrans->runQuery();
                                    $this->dbeScoTrans->resetQueryString();
                                    $this->writeLog($statement);
                                }
                            }
                            fclose($pointer);
                            ftp_delete($ftp, $fileName);
                        }
                    }
                }
            } else {
                $this->writeLog('Could not log in to ftp server as user ' . SCO_USER);
            }
            ftp_close($ftp);
        } else {
            $this->writeLog('Could not connect to ftp server ' . SCO_HOST);
        }
    }

    /**
     * This function checks for new orders from the SCO box and insert them into the ordhead and
     * ordline tables on the new server.
     *
     * orders from the SCO box come across into ordhead_sco and ordline_sco with PK values from the
     * informix table in the ordno fields.
     *
     * STAGE 1:
     *    It is then necessary to convert these into orders on ordhead and ordline with the correct
     * PK values for the new system.
     * the newOrdheadID field on is initially set to zero which indicates it has not yet been processed.
     * Each of these rows is processed and for each a new row is generated on ordhead. The new PK value
     * is then set on ordhead_sco as a cross reference.
     *
     * STAGE 2:
     *    We must SELECT all rows from ordline_sco where new field, processedFlag is zero. An inner join
     * to ordhead_sco will pick up newOrdheadID.
     * For each row, insert into ordline table using newOrdheadID then update ordline_sco.processedFlag
     * to 1 to indicate it has been processed.
     *
     * @access public
     */
    function processSCOOrders()
    {
        $this->setMethodName('processSCOOrders');
        /*
        Process the header tables
        */
        $dbeOrdheadSCORead = new DBEOrdheadSCO($this);
        $dbeOrdheadSCOUpdate = new DBEOrdheadSCO($this); // so as not to interfer with looping
        $dbeOrdhead = new DBEOrdhead($this);
        $dbeOrdheadSCORead->getNonProcessedRows();
        while ($dbeOrdheadSCORead->fetchNext()) {
            for ($ixCol = 0; $ixCol < $dbeOrdheadSCORead->_colCount; $ixCol++) {
                $ixThisColumn = $dbeOrdhead->columnExists($dbeOrdheadSCORead->getName($ixCol));
                if ($ixThisColumn != -1) { // column exists
                    $dbeOrdhead->setValue($ixThisColumn, $dbeOrdheadSCORead->getValue($ixCol));
                }
            }
            $dbeOrdhead->setValue('ordheadID', 0); // new id on ordhead table
            $dbeOrdhead->insertRow();
            // then update ordhead_sco with new ID generated so that we can map SCO IDs to new system IDs
            $dbeOrdheadSCOUpdate->getRow($dbeOrdheadSCORead->getPKValue()); // new id on ordhead table
            $dbeOrdheadSCOUpdate->setValue('newOrdheadID', $dbeOrdhead->getPKValue()); // new id on ordhead table
            $dbeOrdheadSCOUpdate->updateRow();
        }
        /*
        Process the order lines tables
        */
        $dbeJOrdlineSCO = new DBEJOrdlineSCO($this); // NOTE: THIS IS A JOIN
        $dbeOrdlineSCO = new DBEOrdlineSCO($this);
        $dbeOrdline = new DBEOrdline($this);
        $dbeJOrdlineSCO->getNonProcessedRows();
        while ($dbeJOrdlineSCO->fetchNext()) {
            for ($ixCol = 0; $ixCol < $dbeJOrdlineSCO->_colCount; $ixCol++) {
                $ixThisColumn = $dbeOrdline->columnExists($dbeJOrdlineSCO->getName($ixCol));
                if ($ixThisColumn != -1) { // column exists
                    $dbeOrdline->setValue($ixThisColumn, $dbeJOrdlineSCO->getValue($ixCol));
                }
            }
            // set new ordhead ID and insert row
            $dbeOrdline->setValue('ordheadID', $dbeJOrdlineSCO->getValue('newOrdheadID'));
            $dbeOrdline->insertRow();

            // flag line as processed
            $dbeOrdlineSCO->setProcessedFlag(
                $dbeJOrdlineSCO->getValue('ordheadID'),
                $dbeJOrdlineSCO->getValue('sequenceNo')
            );
        }
    }
}// End of class
?>