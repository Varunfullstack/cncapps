<?php /**
 * Specialised Dataset decendent that has an array of form messages
 *
 * This is an experimental class to stop the silly convention of creating and
 * populating new <columnname>Message columns when form errors are generated from
 * populateFromArray()
 *
 * The overriden setFormMessage sets a value in a new member array: message():
 * The new getFormMessage() returns a value from message():
 *
 * @access public
 * @author Karim Ahmed
 */
require_once($cfg["path_gc"] . "/DataSet.inc.php");

class DSForm extends DataSet
{
    var $message = array();        // array of message rows

    /**
     * DSForm constructor.
     * @param $owner
     */
    function __construct(&$owner)
    {
        parent::__construct($owner);
    }

    /*
    * @desc Overriden method: Set a form error message for the column specified
    * @access private
    * @return void
    * @param variant $ixPassedColumn Column for which to set message
    * @param string $message Message
    */
    function setMessage($ixPassedColumn,
                        $message
    )
    {
        var_dump('here');
        try {
            throw new Exception('test');
        } catch (\Exception $exception) {
            var_dump($exception->getTraceAsString());
        }
        $ixColumn = $this->columnExists($ixPassedColumn);
        if ($ixColumn != DA_OUT_OF_RANGE) {
            $this->message[$ixColumn] = $message;
        } else {
            $this->raiseError("setMessage(): Column " . $ixPassedColumn . " out of range");
        }
    }

    /*
    * @desc Get a form error message for a column
    * @access public
    * @return string $message
    * @param variant $ixPassedColumn Column for which to set message
    */
    function getMessage($ixPassedColumn)
    {
        $ixColumn = $this->columnExists($ixPassedColumn);
        if ($ixColumn != DA_OUT_OF_RANGE) {
            return ($this->message[$ixColumn]);
        } else {
            $this->raiseError("getMessage(): Column " . $ixPassedColumn . " out of range");
        }
    }
}

?>