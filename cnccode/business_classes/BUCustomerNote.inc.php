<?php /**
 * Customer Note business class
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg["path_gc"] . "/Business.inc.php");
require_once($cfg["path_dbe"] . "/CNCMysqli.inc.php");

class BUCustomerNote extends Business
{
    var $dbeCallActType = "";

    /**
     * Constructor
     * @access Public
     */
    function __construct(&$owner)
    {
        parent::__construct($owner);
    }

    function updateNote(
        $customerID,
        $customerNoteID,
        $details,
        $ordheadID = false
    )
    {
        $this->setMethodName('updateNote');

        if ($customerNoteID) {
            $sql = "UPDATE customernote";
        } else {
            $sql = "INSERT INTO customernote";
        }

        $sql .= "
      SET
        cno_custno = $customerID,
        cno_details = '" . $this->db->real_escape_string($details) . "',
        cno_ordno = '" . $this->db->real_escape_string($ordheadID) . "',
        cno_modified_consno = " . $GLOBALS['auth']->is_authenticated() .
            ", cno_modified = NOW()";

        if (!$customerNoteID) {
            $sql .= ",cno_created_consno = " . $GLOBALS['auth']->is_authenticated() .
                ", cno_created = NOW()";
        }

        if ($customerNoteID) {
            $sql .= " WHERE cno_customernoteno = $customerNoteID";
        }

        if ($this->db->real_query($sql) === false) {
            echo($this->db->error);
        }

        if ($customerNoteID) {
            return $this->getNote($customerID, false, 'this', $customerNoteID);
        } else {
            return $this->getNote($customerID, false, 'last');

        }
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
        `cno_customernoteno`,
        `cno_custno`,
        `cno_created`,
        `cno_modified`,
        `cno_modified_consno`,
        `cno_details`,
        `cno_created_consno`,
        `cno_ordno`,
        `cns_name`
      FROM
        customernote
        JOIN
          consultant ON cns_consno = cno_modified_consno
      WHERE
        cno_custno = $customerID
      ORDER BY
        cno_created";

        $ret = $this->db->query($sql);

        return $ret;

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