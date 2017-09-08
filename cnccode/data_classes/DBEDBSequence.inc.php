<?php /**
 * Handles access to the db_sequence database table
 *
 * @access public
 * @author Karim Ahmed
 */
require_once($cfg["path_gc"] . "/DBEntity.inc.php");

class DBEDBSequence extends DBEntity
{
    /**
     * Constructor
     * @access public
     * @see constructor()
     * @return bool
     */
    function __construct(&$owner)
    {
        parent::__construct($owner);
        $this->setTableName("db_sequence");
        // Create the columns with their DB alias'
        $this->addColumn("SeqName", DA_STRING, DA_NOT_NULL, "seq_name");
        $this->addColumn("NextID", DA_INTEGER, DA_NOT_NULL, "nextid");
        $this->setAddColumnsOff();
        $this->setPK("SeqName");    // Primary key
    }

    function setSeqName($seqName)
    {
        $this->setValue("SeqName", $seqName);
    }

    function getSeqName()
    {
        return $this->getValue("SeqName");
    }

    function setNextID($nextID)
    {
        $this->setValue("NextID", $nextID);
    }

    function getNextID()
    {
        return $this->getValue("NextID");
    }

    /**
     * Insert row into dataset
     * We don't use the inherited method cause the PK is not an integer
     * @return bool
     * @access public
     */
    function insertRow()
    {
        if ($this->getPKValue() == "") {
            $this->raiseError("No PK set");
        }
        if ($this->getNextID() == "") {
            $this->raiseError("NextID not set");
        }
        $this->setQueryString(
            "INSERT INTO " . $this->getTableName() .
            "(" .
            $this->getDBColumnNamesAsString() .
            ")VALUES(" .
            $this->getColumnValuesAsString() .
            ")"
        );
        return (parent::insertRow());
    }
}// End of class
?>