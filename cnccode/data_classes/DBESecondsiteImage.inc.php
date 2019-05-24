<?php /*
* SecondsiteImage table
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_gc"] . "/DBEntity.inc.php");

class DBESecondsiteImage extends DBEntity
{
    const secondsiteImageID = "secondsiteImageID";
    const customerItemID = "customerItemID";
    const imageName = "imageName";
    const status = "status";
    const imagePath = "imagePath";
    const imageTime = "imageTime";
    const replicationImagePath = "replicationImagePath";
    const replicationImageTime = "replicationImageTime";
    const replicationStatus = "replicationStatus";

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
        $this->setTableName("secondsite_image");
        $this->addColumn(
            self::secondsiteImageID,
            DA_ID,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::customerItemID,
            DA_ID,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::imageName,
            DA_STRING,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::status,
            DA_STRING,
            DA_ALLOW_NULL
        );
        $this->addColumn(
            self::imagePath,
            DA_STRING,
            DA_ALLOW_NULL
        );
        $this->addColumn(
            self::imageTime,
            DA_DATETIME,
            DA_ALLOW_NULL
        );
        $this->addColumn(
            self::replicationImagePath,
            DA_STRING,
            DA_ALLOW_NULL
        );
        $this->addColumn(
            self::replicationImageTime,
            DA_DATETIME,
            DA_ALLOW_NULL
        );
        $this->addColumn(
            self::replicationStatus,
            DA_STRING,
            DA_ALLOW_NULL
        );
        $this->setPK(0);
        $this->setAddColumnsOff();
    }
}

?>