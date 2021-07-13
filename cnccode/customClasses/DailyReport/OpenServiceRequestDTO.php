<?php


namespace CNCLTD\DailyReport;


use DateTime;
use Exception;

class OpenServiceRequestDTO
{
    const MAX_DETAILS_CHARACTERS = 150;
    private $raisedBy;
    private $raisedOn;
    private $status;
    private $details;
    private $id;

    /**
     * OpenServiceRequestDTO constructor.
     * @param $raisedBy
     * @param $raisedOn
     * @param $status
     * @param $details
     */
    public function __construct($id, $raisedBy, $raisedOn, $status, $details)
    {
        $this->raisedBy = $raisedBy;
        $this->raisedOn = $raisedOn;
        $this->status = $status;
        $this->details = $details;
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getRaisedBy()
    {
        return $this->raisedBy;
    }

    /**
     * @return mixed
     * @throws Exception
     */
    public function getRaisedOn()
    {
        return (new DateTime($this->raisedOn))->format('d-m-Y h:i');
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return mixed
     */
    public function getDetails()
    {

        return $this->getFirstLine($this->details, self::MAX_DETAILS_CHARACTERS);
    }

    private function getFirstLine($details, $maxCharacters = 100)
    {
        $details = strip_tags($details);
        $details = preg_replace(
            "!\s+!",
            ' ',
            $details
        );

        $lines = preg_split(
            "/\./",
            $details
        );
        $result = "";
        $counter = 0;

        do {
            if ($counter) {
                $result .= '.';
            }
            $result .= $lines[$counter];
            $counter++;
        } while ($counter < count($lines) && strlen($result) < $maxCharacters);
        return $result;
    }

}