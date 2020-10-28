<?php /**
 * Customer Note business class
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg["path_gc"] . "/Business.inc.php");
require_once($cfg["path_dbe"] . "/CNCMysqli.inc.php");
require_once($cfg["path_dbe"] . "/DBECustomerNote.inc.php");


class BUCustomerNote extends Business
{
    var $dbeCallActType = "";

    /**
     * Constructor
     * @access Public
     * @param $owner
     */
    function __construct(&$owner)
    {
        parent::__construct($owner);
    }

    function updateNote(
        $details,
        $customerNoteID = null,
        $customerID = null,
        $lastUpdatedDateTimeString = null
    )
    {
        $this->setMethodName('updateNote');

        $dbeCustomerNote = new DBECustomerNote($this);
        $isNewNote = !$customerNoteID || $customerNoteID == -1;
        $nowDateTimeString = (new DateTime())->format(DATE_MYSQL_DATETIME);
        if (!$isNewNote) {
            $dbeCustomerNote->getRow($customerNoteID);
            // if it's an update we have to check the last updated date time and if it's lower throw an error
            if (!$lastUpdatedDateTimeString || $dbeCustomerNote->getValue(
                    DBECustomerNote::modifiedAt
                ) > $lastUpdatedDateTimeString) {
                throw new \CNCLTD\Exceptions\JsonHttpException(
                    400, "The note has been modified by someone else", [
                           "errorCode"           => 1002,
                           "lastUpdatedDateTime" => $dbeCustomerNote->getValue(DBECustomerNote::modifiedAt)
                       ]
                );
            }
        } else {
            $dbeCustomerNote->setValue(DBECustomerNote::customerID, $customerID);
            $dbeCustomerNote->setValue(DBECustomerNote::createdUserID, $GLOBALS['auth']->is_authenticated());
            $dbeCustomerNote->setValue(DBECustomerNote::created, $nowDateTimeString);
        }
        $dbeCustomerNote->setValue(DBECustomerNote::details, $details);
        $dbeCustomerNote->setValue(DBECustomerNote::modifiedUserID, $GLOBALS['auth']->is_authenticated());
        $dbeCustomerNote->setValue(DBECustomerNote::modifiedAt, $nowDateTimeString);

        if (!$isNewNote) {
            $dbeCustomerNote->updateRow();
        } else {
            $dbeCustomerNote->insertRow();
        }
        return $this->getNoteByID($dbeCustomerNote->getValue(DBECustomerNote::customerNoteID));
    }

    function getNoteByID($noteId)
    {
        $this->setMethodName('getNotesByCustomerID');

        $sql = "
      SELECT
        `cno_customernoteno` as id,
        `cno_custno` as customerId,
        `cno_created` as createdAt,
        `cno_modified` as modifiedAt,
        `cno_modified_consno` as modifiedById,
        `cno_details` as note,
        `cno_created_consno` as createdById,
        `cns_name` as modifiedByName
      FROM
        customernote
        JOIN
          consultant ON cns_consno = cno_modified_consno
      WHERE
        cno_customernoteno = ?
      ORDER BY
        cno_created desc";

        $statement = $this->db->prepare($sql);
        $statement->bind_param('i', $noteId);
        $statement->execute();
        $result = $statement->get_result();

        return $result->fetch_assoc();
    }

    function getNote(
        $customerID,
        $created,
        $noteIdentifier,
        $customerNoteID = false,
        $ordheadID = false
    )
    {
        $this->setMethodName('getNote');

        switch ($noteIdentifier) {

            case 'this':
                $sql = "
          SELECT * FROM customernote
          JOIN consultant ON cns_consno = cno_modified_consno
          WHERE cno_customernoteno = $customerNoteID";
                break;

            case 'next':
                $sql = "
          SELECT * FROM customernote
          JOIN consultant ON cns_consno = cno_modified_consno
          WHERE cno_custno = $customerID
          AND cno_created > '$created'
          ORDER BY cno_created
          LIMIT 0,1";
                break;

            case 'previous':
                $sql = "
          SELECT * FROM customernote
          JOIN consultant ON cns_consno = cno_modified_consno
          WHERE cno_custno = $customerID
          AND cno_created < '$created'
          ORDER BY cno_created DESC
          LIMIT 0,1";
                break;

            case 'first':
                $sql = "
          SELECT * FROM customernote
          JOIN consultant ON cns_consno = cno_modified_consno
          WHERE cno_custno = $customerID
          ORDER BY cno_created
          LIMIT 0,1";
                break;

            case 'last':
                $sql = "
          SELECT * FROM customernote
          JOIN consultant ON cns_consno = cno_modified_consno
          WHERE cno_custno = $customerID
          ORDER BY cno_created DESC
          LIMIT 0,1";
                break;

            case 'salesOrder':
                $sql = "
          SELECT * FROM customernote
          JOIN consultant ON cns_consno = cno_modified_consno
          WHERE cno_ordno = $ordheadID";

                break;

        } // end switch

        $ret = $this->db->query($sql)->fetch_object();

        return $ret;

    }

    function getNotesByCustomerID(
        $customerID
    )
    {
        $this->setMethodName('getNotesByCustomerID');

        $sql = "
      SELECT
        `cno_customernoteno` as id,
        `cno_custno` as customerId,
        `cno_created` as createdAt,
        `cno_modified` as modifiedAt,
        `cno_modified_consno` as modifiedById,
        `cno_details` as note,
        `cno_created_consno` as createdById,
        `cns_name` as modifiedByName
      FROM
        customernote
        JOIN
          consultant ON cns_consno = cno_modified_consno
      WHERE
        cno_custno = ?
      ORDER BY
        cno_created desc";

        $statement = $this->db->prepare($sql);
        $statement->bind_param('i', $customerID);
        $statement->execute();
        $result = $statement->get_result();

        return $result->fetch_all(MYSQLI_ASSOC);

    } // end function getnotesbycustomerid

    function deleteNote($customerNoteID)
    {
        $this->setMethodName('updateNote');

        $sql = "DELETE FROM customernote
            WHERE cno_customernoteno = $customerNoteID";

        if ($this->db->real_query($sql) === false) {
            echo($this->db->error);
        }

    }// end delete

}// End of class
?>