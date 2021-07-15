<?php


namespace CNCLTD;


use Exception;

class PowerShellScriptFailedToProcessException extends Exception
{
    protected $output;

    /**
     * PowerShellScriptFailedToProcessException constructor.
     * @param $output
     */
    public function __construct($output)
    {
        parent::__construct('Powershell script failed to produce output file');
        $this->output = $output;
    }

    /**
     * @return mixed
     */
    public function getOutput()
    {
        return $this->output;
    }

}