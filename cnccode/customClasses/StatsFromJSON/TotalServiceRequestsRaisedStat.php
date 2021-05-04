<?php

namespace CNCLTD\StatsFromJSON;
class TotalServiceRequestsRaisedStat extends Stat
{
    private $description = "Total SR Raised";

    public function toDisplayArray()
    {
        return [
            'description' => $this->description,
            'p1Value'     => (int)$this->values[1],
            'p2Value'     => (int)$this->values[2],
            'p3Value'     => (int)$this->values[3],
            'p4Value'     => (int)$this->values[4],
            'allValue'    => (int)$this->getSum(),
            'newLine'     => "none"
        ];
    }

    private function getSum()
    {
        return array_reduce(
            $this->values,
            function ($acc, $value) {
                $acc += $value;
                return $acc;
            },
            0
        );
    }
}