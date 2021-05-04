<?php

namespace CNCLTD\StatsFromJSON;
class FixSLAStat extends Stat
{
    private $description;

    public function __construct($p1Value, $p2Value, $p3Value, $p4Value, $penaltiesAgreed)
    {
        $this->description = $penaltiesAgreed ? "Fix SLA" : "Fix OLA";
        parent::__construct(
            $p1Value,
            $p2Value,
            $p3Value,
            $p4Value
        );
    }

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