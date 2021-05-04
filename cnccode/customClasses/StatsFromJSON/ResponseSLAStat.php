<?php

namespace CNCLTD\StatsFromJSON;
class ResponseSLAStat extends Stat
{
    private $description = "Response SLA";

    public function toDisplayArray()
    {
        return [
            'description' => $this->description,
            'p1Value'     => $this->values[1] ?? "-",
            'p2Value'     => $this->values[2] ?? "-",
            'p3Value'     => $this->values[3] ?? "-",
            'p4Value'     => $this->values[4] ?? "-",
            'allValue'    => "-",
            'newLine'     => "none"
        ];
    }
}