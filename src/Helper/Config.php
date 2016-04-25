<?php

namespace AutoReply\Helper;

class Config
{
    /**
     * @var self|null
     */
    protected static $me = null;

    /**
     * Holds the configuration data
     * @var array
     */
    protected $configArray = [];

    private function __construct()
    {
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
     * Sets the config array
     * @param array $configArray
     */
    public function setConfigs(array $configArray)
    {
        $this->configArray = $configArray;
    }

    /**
     * Gets an index from the config array
     * @param $index
     * @return mixed
     */
    public function get($index)
    {
        if (isset($this->configArray[$index])) {
            return $this->configArray[$index];
        }
        return null;
    }
}
