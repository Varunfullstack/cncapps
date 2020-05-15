<?php
/**
 * Service desk report business class
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
global $cfg;
require_once($cfg ["path_gc"] . "/Business.inc.php");
require_once($cfg ["path_gc"] . "/Controller.inc.php");
require_once($cfg["path_dbe"] . "/CNCMysqli.inc.php");
require_once($cfg ["path_func"] . "/Common.inc.php");

class BUQuestionnaireReport extends Business
{
    public $customerID = false;

    public $questionnaireID;

    /** @var DateTimeInterface */
    public $startDate;

    public $endDate;

    public $period;

    private $startDateOneYearAgo;
    /**
     * @var bool|string
     */
    private $year;
    /**
     * @var bool|string
     */
    private $month;

    /**
     * Constructor
     * @access Public
     * @param $owner
     */
    function __construct(&$owner)
    {
        parent::__construct($owner);
    }

    function setQuestionnaireID($ID)
    {
        $this->questionnaireID = $ID;

    }










}