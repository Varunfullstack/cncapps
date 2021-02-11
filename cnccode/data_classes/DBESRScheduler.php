<?php /*
* Questionnaire table
* @authors Karim Ahmed
* @access public
*/
global $cfg;
require_once($cfg["path_gc"] . "/DBEntity.inc.php");

class DBESRScheduler extends DBEntity implements JsonSerializable
{
    const id                  = "id";
    const customerId          = "customerId";
    const rruleString         = "rruleString";
    const contactId           = "contactId";
    const siteNo              = "siteNo";
    const priority            = "priority";
    const hideFromCustomer    = "hideFromCustomer";
    const teamId              = "teamId";
    const details             = "details";
    const internalNotes       = "internalNotes";
    const createdBy           = 'createdBy';
    const updatedBy           = "updatedBy";
    const createdAt           = "createdAt";
    const updatedAt           = "updatedAt";
    const linkedSalesOrderId  = "linkedSalesOrderId";
    const emailSubjectSummary = "emailSubjectSummary";
    const assetName           = "assetName";
    const assetTitle          = "assetTitle";
    const emptyAssetReason    = "emptyAssetReason";

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
        $this->addColumn(
            self::linkedSalesOrderId,
            DA_ID,
            DA_ALLOW_NULL
        );
        $this->addColumn(
            self::emailSubjectSummary,
            DA_STRING,
            DA_ALLOW_NULL
        );
        $this->addColumn(
            self::assetName,
            DA_TEXT,
            DA_ALLOW_NULL
        );
        $this->addColumn(
            self::assetTitle,
            DA_TEXT,
            DA_ALLOW_NULL
        );
        $this->addColumn(
            self::emptyAssetReason,
            DA_TEXT,
            DA_ALLOW_NULL
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
            self::id                  => $this->getValue(self::id),
            self::customerId          => $this->getValue(self::customerId),
            self::rruleString         => $this->getValue(self::rruleString),
            self::contactId           => $this->getValue(self::contactId),
            self::siteNo              => $this->getValue(self::siteNo),
            self::priority            => $this->getValue(self::priority),
            self::hideFromCustomer    => $this->getValue(self::hideFromCustomer),
            self::teamId              => $this->getValue(self::teamId),
            self::details             => $this->getValue(self::details),
            self::internalNotes       => $this->getValue(self::internalNotes),
            self::createdBy           => $this->getValue(self::createdBy),
            self::updatedBy           => $this->getValue(self::updatedBy),
            self::createdAt           => $this->getValue(self::createdAt),
            self::updatedAt           => $this->getValue(self::updatedAt),
            self::linkedSalesOrderId  => $this->getValue(self::linkedSalesOrderId),
            self::emailSubjectSummary => $this->getValue(self::emailSubjectSummary)
        ];
    }

}

?>
