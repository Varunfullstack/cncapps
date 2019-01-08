<?php /*
* Questionnaire table
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_gc"] . "/DBEntity.inc.php");

class DBEUtilityEmail extends DBEntity
{
    const utilityEmailID = "utilityEmailID";
    const firstPart = "firstPart";
    const lastPart = "lastPart";

    /**
     * calls constructor()
     * @access public
     * @return void
     * @param  void
     * @see constructor()
     */
    function __construct(&$owner)
    {
        parent::__construct($owner);
        $this->setTableName("utilityEmail");
        $this->addColumn(
            self::utilityEmailID,
            DA_ID,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::firstPart,
            DA_TEXT,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::lastPart,
            DA_TEXT,
            DA_NOT_NULL
        );
        $this->setAddColumnsOff();
        $this->setPK(0);
    }

    public function getRowsByEmail($email)
    {
        $this->setMethodName("getRowsByEmail");

        $domain = extractDomainFromEmail($email);
        $mailbox = extractMailboxNameFromEmail($email);

        $queryString =
            "SELECT " . $this->getDBColumnNamesAsString() .
            " FROM " . $this->getTableName() .
            " where firstPart = '" . $mailbox . "' and (lastPart = '*' or lastPart = '" . $domain . "') ";

        $this->setQueryString($queryString);
        $ret = (parent::getRows());
        return $ret;
    }
}

?>
