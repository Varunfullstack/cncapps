<?php

namespace CNCLTD\StatsFromJSON;
class SlaMetStat extends Stat
{

    private $description = "% of Response SLAs Met";

    public function toDisplayArray()
    {
        return [
            'description' => $this->description,
            'p1Value'     => $this->getIntegerPercentage($this->values[1]),
            'p2Value'     => $this->getIntegerPercentage($this->values[2]),
            'p3Value'     => $this->getIntegerPercentage($this->values[3]),
            'p4Value'     => $this->getIntegerPercentage($this->values[4]),
            'allValue'    => $this->getIntegerPercentage($this->getAverage()),
            'newLine'     => "none"
        ];
    }

    private function getIntegerPercentage($value)
    {
        if ($value === null) {
            return '-';
        }
        return (int)($value * 100);
    }

}