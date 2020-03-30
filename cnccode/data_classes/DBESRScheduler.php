<?php /*
* Questionnaire table
* @authors Karim Ahmed
* @access public
*/
global $cfg;
require_once($cfg["path_gc"] . "/DBEntity.inc.php");

class DBESRScheduler extends DBEntity implements JsonSerializable
{
    const id = "id";
    const customerId = "customerId";
    const rruleString = "rruleString";
    const contactId = "contactId";
    const siteNo = "siteNo";
    const priority = "priority";
    const hideFromCustomer = "hideFromCustomer";
    const teamId = "teamId";
    const details = "details";
    const internalNotes = "internalNotes";
    const createdBy = 'createdBy';
    const updatedBy = "updatedBy";
    const createdAt = "createdAt";
    const updatedAt = "updatedAt";

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
        $this->setTableName("srScheduler");
        $this->addColumn(
            self::id,
            DA_ID,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::customerId,
            DA_ID,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::rruleString,
            DA_TEXT,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::contactId,
            DA_ID,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::siteNo,
            DA_ID,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::priority,
            DA_INTEGER,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::hideFromCustomer,
            DA_BOOLEAN,
            DA_NOT_NULL,
            null,
            false
        );
        $this->addColumn(
            self::teamId,
            DA_ID,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::details,
            DA_TEXT,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::internalNotes,
            DA_TEXT,
            DA_ALLOW_NULL
        );

        $this->addColumn(
            self::createdBy,
            DA_ID,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::updatedBy,
            DA_ID,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::createdAt,
            DA_DATETIME,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::updatedAt,
            DA_DATETIME,
            DA_NOT_NULL
        );
        $this->setAddColumnsOff();
        $this->setPK(0);
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        return [
            DBESRScheduler::id               => $this->getValue(DBESRScheduler::id),
            DBESRScheduler::customerId       => $this->getValue(DBESRScheduler::customerId),
            DBESRScheduler::rruleString      => $this->getValue(DBESRScheduler::rruleString),
            DBESRScheduler::contactId        => $this->getValue(DBESRScheduler::contactId),
            DBESRScheduler::siteNo           => $this->getValue(DBESRScheduler::siteNo),
            DBESRScheduler::priority         => $this->getValue(DBESRScheduler::priority),
            DBESRScheduler::hideFromCustomer => $this->getValue(DBESRScheduler::hideFromCustomer),
            DBESRScheduler::teamId           => $this->getValue(DBESRScheduler::teamId),
            DBESRScheduler::details          => $this->getValue(DBESRScheduler::details),
            DBESRScheduler::internalNotes    => $this->getValue(DBESRScheduler::internalNotes),
            DBESRScheduler::createdBy        => $this->getValue(DBESRScheduler::createdBy),
            DBESRScheduler::updatedBy        => $this->getValue(DBESRScheduler::updatedBy),
            DBESRScheduler::createdAt        => $this->getValue(DBESRScheduler::createdAt),
            DBESRScheduler::updatedAt        => $this->getValue(DBESRScheduler::updatedAt),
        ];
    }

}

?>
