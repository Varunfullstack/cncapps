<?php /*
* Questionnaire table
* @authors Karim Ahmed
* @access public
*/
global $cfg;
require_once($cfg["path_gc"] . "/DBEntity.inc.php");

class DBEOffice365License extends DBEntity
{
    const id                    = "id";
    const replacement           = "replacement";
    const license               = "license";
    const mailboxLimit          = "mailboxLimit";
    const reportOnSpareLicenses = "reportOnSpareLicenses";
    const includesDefender      = 'includesATP';
    const includesOffice        = 'includesOffice';

    /**
     * calls constructor()
     * @access public
     * @param void
     * @return void
     * @see constructor()
     */
    function __construct(&$owner)
    {
        parent::__construct($owner);
        $this->setTableName("office365License");
        $this->addColumn(
            self::id,
            DA_ID,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::replacement,
            DA_TEXT,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::license,
            DA_STRING,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::mailboxLimit,
            DA_INTEGER,
            DA_ALLOW_NULL
        );
        $this->addColumn(
            self::reportOnSpareLicenses,
            DA_BOOLEAN,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::includesDefender,
            DA_BOOLEAN,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::includesOffice,
            DA_BOOLEAN,
            DA_NOT_NULL
        );
        $this->setAddColumnsOff();
        $this->setPK(0);
    }

    public function getRowForLicense($license)
    {
        if (!preg_match('/:(.*)/', $license, $matches)) {
            return;
        }
        $licenseWhere = $this->getDBColumnName(self::license) . " =  '$matches[1]' ";
        $this->setQueryString(
            "SELECT " . $this->getDBColumnNamesAsString() . " FROM " . $this->getTableName() . " WHERE " . $licenseWhere
        );
        $this->getRows();
        $this->fetchFirst();
        $this->resetQueryString();
    }
}

?>
