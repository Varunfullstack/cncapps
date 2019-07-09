<?php /*
* Questionnaire table
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_gc"] . "/DBEntity.inc.php");

class DBEOffice365License extends DBEntity
{
    const id = "id";
    const replacement = "replacement";
    const specificity = "specificity";
    const licensesJSON = "licensesJSON";
    const mailboxLimit = "mailboxLimit";

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
            self::specificity,
            DA_INTEGER,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::licensesJSON,
            DA_JSON_ARRAY,
            DA_NOT_NULL
        );

        $this->addColumn(
            self::mailboxLimit,
            DA_INTEGER,
            DA_ALLOW_NULL
        );

        $this->setAddColumnsOff();
        $this->setPK(0);
    }

    public function getRowForLicenses(array $value)
    {
        $licenseWhere = null;

        $specificity = 0;
        foreach ($value as $license) {
            if (!preg_match('/:(.*)/', $license, $matches)) {
                return;
            }

            $specificity++;
            if ($licenseWhere) {
                $licenseWhere .= " and ";
            }
            $licenseWhere .= $this->getDBColumnName(self::licensesJSON) . " like  '%$matches[1]%' ";
        }

        if (!$specificity) {
            throw new Exception('Empty Array??');
        }

        $this->setQueryString(
            "SELECT " . $this->getDBColumnNamesAsString() .
            " FROM " . $this->getTableName() .
            " WHERE " . $licenseWhere . " and specificity = " . $specificity
        );
        $this->getRows();
        $this->fetchFirst();
        $this->resetQueryString();
    }
}

?>
