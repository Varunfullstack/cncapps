<?php

namespace CNCLTD;

use Bramus\Monolog\Formatter\ColoredLineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class LoggerCLI
{
    /**
     * @var Logger
     */
    private $log;

    /**
     * LoggerCLI constructor.
     * @param $logName
     * @throws \Exception
     */
    public function __construct($logName)
    {
        $date        = new \DateTime();
        $this->log   = new Logger('logger');
        $logFileName = $logName . ".log";
        $logPath     = APPLICATION_LOGS . '/' . $logFileName;
        $this->log->pushHandler(new \Monolog\Handler\RotatingFileHandler($logPath, 14, Logger::INFO));
        $consoleHandler = new StreamHandler('php://stdout', Logger::INFO);
        $consoleHandler->setFormatter(new ColoredLineFormatter());
        $this->log->pushHandler($consoleHandler);
    }

    public function info($message, $context = [])
    {
        $this->log->info($message, $context);
    }

    public function error($message)
    {
        $this->log->error($message);
    }

    public function notice($message)
    {
        $this->log->notice($message);
    }

    public function warning($message)
    {
        $this->log->warning($message);
    }

    public function debug($message, $context = [])
    {
        $this->log->debug($message, $context);
    }
}

