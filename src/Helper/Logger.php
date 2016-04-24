<?php

namespace AutoReply\Helper;

use Psr\Log\LoggerInterface;

class Logger implements LoggerInterface
{
    //Logger Levels
    const DEBUG = 100;
    const INFO = 200;
    const NOTICE = 250;
    const WARNING = 300;
    const ERROR = 400;
    const CRITICAL = 500;
    const ALERT = 550;
    const EMERGENCY = 600;

    /** @var self|null */
    static protected $me = null;
    /** @var integer */
    protected $logLevel;

    private function __construct()
    {
        $level = Config::instance()->get('log_level');
        if (is_numeric($level)) {
            $this->logLevel = $level;
        } else {
            $this->logLevel = $this->getLogLevelFromName($level);
        }
    }

    /**
     * Converts a LogLevel name into a LogLevel integer
     *
     * @param string $name The name of the log level (exactly same as constants spellings)
     * @return int The associated constant value
     */
    public function getLogLevelFromName($name)
    {
        switch (strtolower($name)) {
            case "EMERGENCY":
                return self::EMERGENCY;
            case "ALERT":
                return self::ALERT;
            case "CRITICAL":
                return self::CRITICAL;
            case "ERROR":
                return self::ERROR;
            case "WARNING":
                return self::WARNING;
            case "NOTICE":
                return self::NOTICE;
            case "INFO":
                return self::INFO;
            case "DEBUG":
                return self::DEBUG;
            default:
                return self::DEBUG;
        }
    }

    /**
     * Converts a LogLevel integer into a LogLevel string
     *
     * @param integer $logLevel
     * @return string
     */
    public function getNameFromLogLevel($logLevel)
    {
        switch ($logLevel) {
            case self::EMERGENCY:
                return "EMERGENCY";
            case self::ALERT:
                return "ALERT";
            case self::CRITICAL:
                return "CRITICAL";
            case self::ERROR:
                return "ERROR";
            case self::WARNING:
                return "WARNING";
            case self::NOTICE:
                return "NOTICE";
            case self::INFO:
                return "INFO";
            case self::DEBUG:
                return "DEBUG";
            default:
                return "DEBUG";
        }
    }

    /**
     * Returns an instance of this class
     * @return self
     */
    public static function instance()
    {
        if (self::$me === null) {
            self::$me = new self();
        }
        return self::$me;
    }

    /**
     * @inheritdoc
     */
    public function emergency($message, array $context = array())
    {
        $this->log(self::EMERGENCY, $message, $context);
    }

    /**
     * @inheritdoc
     */
    public function alert($message, array $context = array())
    {
        $this->log(self::ALERT, $message, $context);
    }

    /**
     * @inheritdoc
     */
    public function critical($message, array $context = array())
    {
        $this->log(self::CRITICAL, $message, $context);
    }

    /**
     * @inheritdoc
     */
    public function error($message, array $context = array())
    {
        $this->log(self::ERROR, $message, $context);
    }

    /**
     * @inheritdoc
     */
    public function warning($message, array $context = array())
    {
        $this->log(self::WARNING, $message, $context);
    }

    /**
     * @inheritdoc
     */
    public function notice($message, array $context = array())
    {
        $this->log(self::NOTICE, $message, $context);
    }

    /**
     * @inheritdoc
     */
    public function info($message, array $context = array())
    {
        $this->log(self::INFO, $message, $context);
    }

    /**
     * @inheritdoc
     */
    public function debug($message, array $context = array())
    {
        $this->log(self::DEBUG, $message, $context);
    }

    /**
     * @inheritdoc
     */
    public function log($level, $message, array $context = array())
    {
        if ($level <= $this->logLevel) {
            $level = $this->getNameFromLogLevel($level);
            $context = implode(',', $context);
            echo  "$level: [$context] - $message\n";
        }
    }
}