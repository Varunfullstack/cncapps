<?php

namespace CNCLTD\StatsFromJSON;
class StatsFromJSON
{
    private $classesMap = [
        "sla"                => ResponseSLAStat::class,
        "raised"             => TotalServiceRequestsRaisedStat::class,
        "responseTime"       => AverageResponseTimeStat::class,
        "slaMet"             => SlaMetStat::class,
        "fixSLA"             => FixSLAStat::class,
        "avgTimeAwaitingCNC" => AverageTimeAwaitingCNCStat::class,
    ];

    /**
     * @param $jsonString
     * @param $penaltiesAgreed
     * @return Stat[]
     */
    public function __invoke($jsonString, $penaltiesAgreed): array
    {
        $items = json_decode($jsonString, true);
        return $this->classesFromItems($items, $penaltiesAgreed);
    }

    private function classesFromItems($items, $penaltiesAgreed)
    {
        return array_map(
            function ($column, $class) use ($items, $penaltiesAgreed) {
                $p1Value = $items[0][$column];
                $p2Value = $items[1][$column];
                $p3Value = $items[2][$column];
                $p4Value = $items[3][$column];
                if ($class === FixSLAStat::class) {
                    return new $class($p1Value, $p2Value, $p3Value, $p4Value, $penaltiesAgreed);
                }
                return new $class($p1Value, $p2Value, $p3Value, $p4Value);
            },
            array_keys($this->classesMap),
            $this->classesMap
        );
    }

}