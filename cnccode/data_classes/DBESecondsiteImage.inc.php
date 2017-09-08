<?php /*
* SecondsiteImage table
* @authors Karim Ahmed
* @access public
*/
require_once($cfg["path_gc"] . "/DBEntity.inc.php");

class DBESecondsiteImage extends DBEntity
{
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
        $this->setTableName("secondsite_image");
        $this->addColumn("secondsiteImageID", DA_ID, DA_NOT_NULL);
        $this->addColumn("customerItemID", DA_ID, DA_NOT_NULL);
        $this->addColumn("imageName", DA_STRING, DA_NOT_NULL);
        $this->addColumn("status", DA_STRING, DA_ALLOW_NULL);
        $this->addColumn("imagePath", DA_STRING, DA_ALLOW_NULL);
        $this->addColumn("imageTime", DA_DATETIME, DA_ALLOW_NULL);
        $this->setPK(0);
        $this->setAddColumnsOff();
    }
}

?>