<?php
/**
 * Created by PhpStorm.
 * User: fizdalf
 * Date: 24/09/2018
 * Time: 12:00
 */

require_once($cfg["path_gc"] . "/DBEntity.inc.php");

class DBECustomerReviewMeetingDocument extends DBEntity
{
    const customerReviewMeetingDocumentID = "customerReviewMeetingDocumentID";
    const customerID = "customerID";
    const meetingDate = "meetingDate";
    const file = "file";
    const uploadedBy = "uploadedBy";
    const uploadedAt = "uploadedAt";
    const fileName = "fileName";
    const mimeType = "mimeType";

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

        $this->setTableName("customerReviewMeetingDocument");

        $this->addColumn(
            self::customerReviewMeetingDocumentID,
            DA_ID,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::customerID,
            DA_ID,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::meetingDate,
            DA_STRING,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::file,
            DA_BLOB,
            DA_ALLOW_NULL
        );
        $this->addColumn(
            self::fileName,
            DA_STRING,
            DA_ALLOW_NULL
        );
        $this->addColumn(
            self::mimeType,
            DA_STRING,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::uploadedAt,
            DA_DATETIME,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::uploadedBy,
            DA_ID,
            DA_NOT_NULL
        );

        $this->setPK(0);
        $this->setAddColumnsOff();
    }
}