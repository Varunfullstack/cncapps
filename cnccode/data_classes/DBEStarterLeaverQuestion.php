<?php
/**
 * Created by PhpStorm.
 * User: fizdalf
 * Date: 25/01/2019
 * Time: 10:45
 */

require_once($cfg["path_gc"] . "/DBEntity.inc.php");

class DBEStarterLeaverQuestion extends DBEntity
{
    const questionID = "questionID";
    const customerID = "customerID";
    const formType = "formType";
    const name = "name";
    const type = "type";
    const label = "label";
    const options = "options";
    const multi = "multi";
    const required = "required";
    const sortOrder = "sortOrder";


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
        $this->setTableName("starterLeaverQuestion");
        $this->addColumn(
            self::questionID,
            DA_ID,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::customerID,
            DA_ID,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::formType,
            DA_STRING,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::name,
            DA_STRING,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::type,
            DA_STRING,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::label,
            DA_STRING,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::options,
            DA_STRING,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::multi,
            DA_INTEGER,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::required,
            DA_INTEGER,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::sortOrder,
            DA_INTEGER,
            DA_NOT_NULL
        );

        $this->setPK(0);
        $this->setAddColumnsOff();
    }
}