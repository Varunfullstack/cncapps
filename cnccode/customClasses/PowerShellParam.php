<?php


namespace CNCLTD;


class PowerShellParam
{
    protected $parameterName;
    protected $value;

    /**
     * PowerShellParam constructor.
     * @param $parameterName
     * @param $value
     */
    public function __construct($parameterName, $value)
    {
        $this->parameterName = $parameterName;
        $this->value = $value;
    }

    /**
     * @return mixed
     */
    public function getParameterName()
    {
        return $this->parameterName;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }
}