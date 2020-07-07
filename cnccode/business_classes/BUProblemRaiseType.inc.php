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
    const EMAILID=1;
    const PORTAL='Portal';
    const PORTALID=2;
    const PHONE='Phone';
    const PHONEID=3;
    const ONSITE='On site';
    const ONSITEID=4;
    const ALERT='Alert';
    const ALERTID=5;
    const SALES='Sales';
    const SALESID=6;
    const MANUAL='Manual';
    const MANUALID=7;

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