<?php

namespace AutoReply\Mail;

use AutoReply\Helper\Config;
use AutoReply\Helper\Logger;
use AutoReply\Search\Item as SearchItem;
use AutoReply\Search\Manager as SearchManager;
use RuntimeException;

class ImapHandler
{
    const MARK_SEEN = 0;
    const MARK_ANSWERED = 1;
    const MARK_FLAGGED = 2;
    const MARK_DELETED = 3;
    const MARK_DRAFT = 4;

    /**
     * @var self|null
     */
    protected static $me = null;

    /** @var bool */
    protected $ssl;
    /** @var string */
    protected $host;
    /** @var integer */
    protected $port;
    /** @var string */
    protected $mode;
    /** @var string */
    protected $username;
    /** @var string */
    protected $password;
    /** @var MailboxInterface[] $mailboxes */
    protected $mailboxes = [];
    /** @var SearchManager */
    protected $searchManager;
    /** @var Logger */
    protected $logger;

    private function __construct()
    {
        $this->searchManager = SearchManager::instance();
        $this->logger = Logger::instance();
        $conf = Config::instance();
        $this->ssl = $conf->get('email.incoming.secure');
        $this->host = $conf->get('email.incoming.host');
        $this->port = $conf->get('email.incoming.port');
        $this->username = $conf->get('email.incoming.username');
        $this->password = $conf->get('email.incoming.password');
        $this->mode = 'imap';
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
     * Throws an error related to an imap action
     * @param string $message The error message to use
     */
    protected function throwImapError($message)
    {
        $errorMessage = $message . "\n" . 'Error is: ' . imap_last_error() . "\n" . print_r(imap_errors(), true);
        $this->logger->error($errorMessage, [__CLASS__]);
        throw new RuntimeException($errorMessage);
    }

    /**
     * Validates a mailbox was connected to successfully
     * @param MailboxInterface $mailbox The mailbox to validate
     * @param string $mailboxName The name of the mailbox
     */
    protected function validateMailbox($mailbox, $mailboxName)
    {
        if (!isset($mailbox->Mailbox)) {
            $this->throwImapError('Invalid mailbox object return.  Mailbox name missing.');
        }
        $name = preg_replace('/^{.+?}/', '', $mailbox->Mailbox);
        if ($name === '<no_mailbox>') {
            $this->throwImapError('No mailbox found with that name.');
        }
        if ($name !== $mailboxName) {
            $this->throwImapError('Mailbox given does not match name.');
        }
    }

    /**
     * Opens an imap stream to a mailbox
     *
     * @param string $mailboxName The name of the mailbox to connect to
     * @return MailboxInterface
     * @throws RuntimeException
     */
    protected function openConnection($mailboxName)
    {
        $sslStr = $this->ssl ? '/ssl' : '';
        $connectionString = '{'.$this->host.':'.$this->port.'/'.$this->mode.$sslStr.'/novalidate-cert}' . $mailboxName;

        $this->logger->debug("Connecting to {$this->host}", [__CLASS__]);
        $mailboxStream = imap_open($connectionString, $this->username, $this->password);
        if(!$mailboxStream){
            $this->throwImapError('Failed to connect to account.');
        }

        /** @var MailboxInterface|false $mailbox */
        $mailbox = imap_check($mailboxStream);
        if ($mailbox === false) {
            $this->throwImapError('Failed checking mailbox.');
        }
        $this->validateMailbox($mailbox, $mailboxName);
        $mailbox->stream = &$mailboxStream;
        $this->logger->debug("Connected successfully. Received {$mailbox->Nmsgs} messages.", [__CLASS__]);
        return $mailbox;
    }

    /**
     * Opens a connections to a list of mailboxes
     *
     * @param string[] $mailboxNames An array of mailbox names
     */
    protected function getMailboxes($mailboxNames)
    {
        //Open connection to mailboxes to be searched
        $this->logger->debug("Opening connections to mailboxes.", [__CLASS__]);
        foreach ($mailboxNames as $name) {
            if (!isset($this->mailboxes[$name])) {
                $this->mailboxes[$name] = $this->openConnection($name);
            }
        }
    }

    /**
     * Searches mailboxes for matching emails
     *
     * @return SearchItem[]
     */
    public function searchMailboxes()
    {
        $this->getMailboxes($this->searchManager->getMailboxNames());
        $searchItems = &$this->searchManager->getItems();
        $this->logger->debug("Searching mailboxes for matching emails.", [__CLASS__]);
        /** @var SearchItem $item */
        foreach ($searchItems as $index => $item) {
            $mailboxStream = &$this->mailboxes[$item->getMailboxName()]->stream;
            $emailIds = imap_search($mailboxStream, $item->getImapSearch(), SE_UID);
            if ($emailIds === false) {
                $emailIds = [];
            }
            foreach ($emailIds as $id) {
                $overview = imap_fetch_overview($mailboxStream, $id, FT_UID);
                if (count($overview) === 1) {
                    $item->emails[$id] = $overview[0];
                } else {
                    $this->throwImapError('Invalid email overview for id = ' . $id);
                }
            }
        }
        return $searchItems;
    }

    /**
     * Changes the state of the email to the given state.
     * $mark as can be:
     *   self::MARK_SEEN
     *   self::MARK_ANSWERED
     *   self::MARK_FLAGGED
     *   self::MARK_DELETED
     *   self::MARK_DRAFT
     *
     * @param string $mailboxName The name of the mailbox the email is in
     * @param string $emailUid The unique id for the email
     * @param integer $markAs The state to mark the email as
     */
    public function markEmail($mailboxName, $emailUid, $markAs)
    {
        switch ($markAs) {
            case self::MARK_SEEN:
                $mark = '\\Seen';
                break;
            case self::MARK_ANSWERED:
                $mark = '\\Answered';
                break;
            case self::MARK_FLAGGED:
                $mark = '\\Flagged';
                break;
            case self::MARK_DELETED:
                $mark = '\\Deleted';
                break;
            case self::MARK_DRAFT:
                $mark = '\\Draft';
                break;
            default:
                throw new RuntimeException('Invalid email marking given');
        }
        imap_setflag_full($this->mailboxes[$mailboxName]->stream, $emailUid, $mark, ST_UID);
    }
}
