<?php /**
 * Handles access to the db_sequence database table
 *
 * @access public
 * @author Karim Ahmed
 */
require_once($cfg["path_gc"] . "/DBEntity.inc.php");

class DBEDBSequence extends DBEntity
{
const seqName = "SeqName";
const nextID = "NextID";

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
        $this->addColumn(self::seqName, DA_STRING, DA_NOT_NULL, "seq_name");
        $this->addColumn(self::nextID, DA_INTEGER, DA_NOT_NULL, "nextid");
        $this->setAddColumnsOff();
        $this->setPK(self::seqName);    // Primary key
    }

    function setSeqName($seqName)
    {
        $this->setValue(self::seqName, $seqName);
    }

    function getSeqName()
    {
        return $this->getValue(self::seqName);
    }

    function setNextID($nextID)
    {
        $this->setValue(self::nextID, $nextID);
    }

    function getNextID()
    {
        return $this->getValue(self::nextID);
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