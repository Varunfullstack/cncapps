<?php

namespace CNCLTD\StatsFromJSON;
class AverageTimeAwaitingCNCStat extends Stat
{
    private $description = "Average Fix Time";

    public function toDisplayArray()
    {
        return [
            'description' => $this->description,
            'p1Value'     => $this->getRoundedValue($this->values[1]),
            'p2Value'     => $this->getRoundedValue($this->values[2]),
            'p3Value'     => $this->getRoundedValue($this->values[3]),
            'p4Value'     => $this->getRoundedValue($this->values[4]),
            'allValue'    => $this->getRoundedValue($this->getAverage()),
            'newLine'     => "none"
        ];
    }

}