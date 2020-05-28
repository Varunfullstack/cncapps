<?php


namespace CNCLTD;


use Monolog\Logger;

abstract class PowerShellCommandRunner
{
    protected $debugMode;
    protected $outputFilePath;
    protected $commandName;
    protected $reuseData;

    /** @var Logger */
    protected $logger;

    public function enableDebugMode()
    {
        $this->debugMode = true;
    }

    public function disableDebugMode()
    {
        $this->debugMode = false;
    }

    /**
     * @return mixed
     */
    public function run()
    {
        $output = null;
        if (!$this->reuseData) {
            $paramParts = $this->getParams();
            $path = POWERSHELL_DIR . '\\' . $this->commandName . ".ps1";
            $cmdParts = [
                "powershell.exe",
                "-executionpolicy",
                "bypass",
                "-NoProfile",
                "-command",
                $path,
            ];

            foreach ($paramParts as $paramPart) {
                $cmdParts[] = '-' . $paramPart->getParameterName();
                $cmdParts[] = base64_encode($paramPart->getValue());
            }

            $cmdParts[] = "-OutputPath";
            $cmdParts[] = $this->outputFilePath;

            // all scripts will have a "path"

            $escaped = implode(' ', array_map('escape_win32_argv', $cmdParts));
            /* In almost all cases, escape for cmd.exe as well - the only exception is
               when using proc_open() with the bypass_shell option. cmd doesn't handle
               arguments individually, so the entire command line string can be escaped,
               no need to process arguments individually */
            $cmd = escape_win32_cmd($escaped);

            if ($this->debugMode) {
                $this->logger->notice('The powershell line to execute is :' . $cmd);
            }
            $output = noshell_exec($cmd);
        }

        if (!file_exists($this->outputFilePath)) {
            $message = "PowerShell script failed to generate output file. File Not Found";
            $this->logger->error($message, ["powerShellStringOutput" => $output]);
            throw new PowerShellScriptFailedToProcessException($output);
        }
        $fileContents = file_get_contents($this->outputFilePath);
        if (!$fileContents) {
            $message = "PowerShell script failed to generate output file. File was empty";
            $this->logger->error($message, ["powerShellStringOutput" => $output]);
            throw new PowerShellScriptFailedToProcessException($output);
        }
//        unlink($this->outputFilePath);

        $decodedJSON = json_decode($fileContents, true, 512);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $message = "PowerShell script failed to generate output file. Failed to decode JSON";
            $this->logger->error($message, ["powerShellStringOutput" => $output]);
            throw new PowerShellScriptFailedToProcessException($output);
        }

        return $decodedJSON;
    }

    protected abstract function getParams(): PowerShellParamCollection;

}