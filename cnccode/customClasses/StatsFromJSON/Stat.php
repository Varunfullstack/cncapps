<?php

namespace CNCLTD\StatsFromJSON;
abstract class Stat
{
    protected $values;

    public function __construct($p1Value, $p2Value, $p3Value, $p4Value)
    {
        $this->values = [
            1 => $p1Value,
            2 => $p2Value,
            3 => $p3Value,
            4 => $p4Value
        ];
    }

    public abstract function toDisplayArray();

    protected function getAverage()
    {
        $data       = array_reduce(
            $this->values,
            function ($acc, $value) {
                if ($value !== null) {
                    $acc['validItems']++;
                    $acc['sum'] += $value;
                }
                return $acc;
            },
            ["sum" => 0, "validItems" => 0]
        );
        $validItems = $data['validItems'];
        $sum        = $data['sum'];
        if (!$validItems) {
            return 0;
        }
        return $sum / $validItems;
    }

    protected function getRoundedValue($value)
    {
        if ($value === null) {
            return "-";
        }
        return round($value, 2);
    }
}