<?php
/**
 * Email request business class
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg ["path_gc"] . "/Business.inc.php");
require_once($cfg ["path_gc"] . "/Controller.inc.php");
require_once($cfg ["path_bu"] . "/BUActivity.inc.php");

class BUImportRequests extends Business
{

    var $buActivity = '';

    var $updateDb = false;

    private $errors = array();

    function __construct(&$owner)
    {
        parent::__construct($owner);
        $this->buActivity = new BUActivity($this);
        $this->updateDb = new dbSweetcode;
    }

    private function logError($errorString)
    {
        $this->errors[] = $errorString;
    }

    public function createServiceRequests()
    {
        $db = new dbSweetcode();

        echo "Start Import<BR/>";

        $processedMessages = 0;
        /*
        Putting a limit on this means that if the process gets behind it will process in batches
        instead of putting a big load on the server.
        */
        $sql = "
      SELECT
        *
      FROM
        automated_request
      WHERE
        importedFlag = 'N'
        AND importErrorFound = 'N'
      ORDER BY
        automatedRequestId
      LIMIT 15";

        $db->query($sql);

        $toDelete = [];

        while ($db->next_record()) {

            $automatedRequestID = $db->Record['automatedRequestID'];
            echo 'Start processing ' . $db->Record['automatedRequestID'] . "<BR/>";

            $errorString = '';
            if ($this->processMessage($db->Record, $errorString)) {      // error string returned

                echo $automatedRequestID . " processed successfully<BR/>";

                $toDelete[] = $db->Record['automatedRequestID'];

                $processedMessages++;
            } else {
                echo $db->Record['automatedRequestID'] . " failed<BR/>";
                if ($db->Record['importErrorFound'] == 'N') {
                    $this->logError($db->Record['automatedRequestID'] . ' failed: ' . $errorString);
                    $this->setImportErrorFound($db->Record['automatedRequestID']);
                }
            }

        } // end while

        echo $processedMessages . " requests imported<BR/>";

        if (count($toDelete)) {
            echo 'Deleting successfully imported requests';

            $sql = "delete FROM automated_request
                    WHERE automatedRequestId in ($toDelete)";
            $db->query($sql);
        }


        echo "End<BR/>";

        return $processedMessages;

    }

    private function setImportedFlag($id)
    {
        $sql = "
      UPDATE
        automated_request
        
      SET
        importedFlag = 'Y',
        importDateTime = NOW() 
        
      WHERE
        automatedRequestID = $id";

        $this->updateDb->query($sql);
    }

    private function setImportErrorFound($id)
    {
        $sql = "
      UPDATE
        automated_request
      SET
        importErrorFound = 'Y'
      WHERE
        automatedRequestID = $id";

        $this->updateDb->query($sql);
    }

    protected function processMessage($record, &$errorString)
    {

        $processed = false;

        return $this->buActivity->processAutomaticRequest($record, $errorString);

    }
    /**
     * Get the problemID from the subject string
     *
     * @param mixed $subject
     */

} // End of class
?>