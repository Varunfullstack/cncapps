<?php /**
 *
 *
 * @access public
 * @authors Mustafa Taha
 */
require_once($cfg["path_gc"] . "/Business.inc.php");
require_once($cfg["path_dbe"] . "/DBEProblemRaiseType.inc.php");
 
class BUProblemRaiseType extends Business
{
    const EMAIL='Email';
    const PORTAL='Portal';
    const PHONE='Phone';
    const ONSITE='On site';
    const ALERT='Alert';
    const SALES='Sales';
    const MANUAL='Manual';

    var $dbeProblemRaiseType = "";

    /**
     * Constructor
     * @access Public
     * @param $owner
     */
    function __construct(&$owner)
    {
        parent::__construct($owner);
        $this->dbeProblemRaiseType = new DBEProblemRaiseType($this);
    }

    function getProblemRaiseTypeById($ID, &$dsResults)
    {
        $this->dbeProblemRaiseType->setPKValue($ID);
        $this->dbeProblemRaiseType->getRow();
        return ($this->getData($this->dbeProblemRaiseType, $dsResults));
    }

    function getProblemRaiseTypeByName($description)
    {
        global $db;
        $sql="select * from ProblemRaiseType where description ='$description'";
        $db->query($sql); 
        $db->next_record();
        return $db->Record;
     }

    function getAll(&$dsResults)
    {
        $this->dbeProblemRaiseType->getRows('description');
        return ($this->getData($this->dbeProblemRaiseType, $dsResults));
    }

}// End of class
?>