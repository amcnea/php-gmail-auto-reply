<?php

namespace AutoReply\Search;

use AutoReply\Helper\Logger;
use RuntimeException;

/**
 * Class Manager
 *
 * Manages reading the search config file and compiling a list of search items
 */
class Manager
{
    /** @var self|null */
    protected static $me = null;

    /** @var array[Item] */
    protected $items = [];
    /** @var array[] */
    protected $mailboxNames = [];
    /** @var Logger */
    protected $logger;

    private function __construct()
    {
        $this->logger = Logger::instance();
        $this->loadItems();
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
     * Throws an error related to parsing the search config file
     *
     * @param string $index Index of the item that failed validation
     * @param [] $item The item that failed validation
     */
    protected function throwParseError($index, $item)
    {
        $message = "Required search item '$index' is missing in item: " . print_r($item, true);
        $this->logger->error($message, [__CLASS__]);
        throw new RuntimeException($message);
    }

    /**
     * Validates a SearchItem array from the search config file
     * @param [] $item The item to validate
     */
    protected function validateItem($item)
    {
        $required = [
            'mailboxName',
            'imapSearch',
            'emailTemplate',
            'fromEmail',
            'fromName'
        ];
        foreach ($required as $index) {
            if (!isset($item[$index])) {
                $this->throwParseError($index, $item);
            }
        }
    }

    /**
     * Loads the search config file
     */
    protected function loadItems()
    {
        $items = include CONFIG_DIR . '/search.php';
        foreach ($items as $item) {
            $this->validateItem($item);
            $searchItem = new Item(
                $item['mailboxName'],
                $item['imapSearch'],
                $item['emailTemplate'],
                $item['fromName'],
                $item['fromEmail']
            );
            if (isset($item['bbcEmail'])) {
                $searchItem->setBbcEmail($item['bbcEmail']);
            }
            if (isset($item['bbcName'])) {
                $searchItem->setBbcName($item['bbcName']);
            }
            $this->items[] = $searchItem;
            $this->mailboxNames[$item['mailboxName']] = true;
        }
    }

    /**
     * Gets the array of search items
     *
     * @return array[Item]
     */
    public function &getItems()
    {
        return $this->items;
    }

    /**
     * Gets the array of mailboxes to be searched
     *
     * @return array[string]
     */
    public function getMailboxNames()
    {
        return array_keys($this->mailboxNames);
    }
}
