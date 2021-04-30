<?php /**
 *
 *
 * @access public
 * @authors Mustafa Taha
 */
global $cfg;
require_once($cfg["path_gc"] . "/Business.inc.php");
require_once($cfg["path_dbe"] . "/DBEProblemRaiseType.inc.php");

class BUProblemRaiseType extends Business
{
    private const EMAIL    = 'Email';
    const         EMAILID  = 1;
    private const PORTAL   = 'Portal';
    const         PORTALID = 2;
    private const PHONE    = 'Phone';
    const         PHONEID  = 3;
    private const ONSITE   = 'On site';
    const         ONSITEID = 4;
    private const ALERT    = 'Alert';
    const         ALERTID  = 5;
    private const SALES    = 'Sales';
    const         SALESID  = 6;
    private const MANUAL   = 'Manual';
    const         MANUALID = 7;

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

    function getAll(&$dsResults)
    {
        $this->dbeProblemRaiseType->getRows('description');
        return ($this->getData($this->dbeProblemRaiseType, $dsResults));
    }

}// End of class
?>