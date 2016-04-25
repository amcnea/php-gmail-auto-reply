<?php

namespace AutoReply\Search;

use AutoReply\Mail\EmailOverviewInterface;

/**
 * Class Item contains the data associated with a search include the matching emails, and data need to reply to
 * matches on these search items.
 *
 * @method string getImapSearch()
 * @method string getEmailTemplate()
 * @method string getMailboxName()
 * @method string getFromEmail()
 * @method string getFromName()
 * @method string getBbcEmail()
 * @method string getBbcName()
 * @method string getEmails()
 * @method void setBbcEmail(string $value)
 * @method void setBbcName(string $value)
 */
class Item
{
    /** @var string */
    protected $mailboxName;
    /** @var string */
    protected $imapSearch;
    /** @var string */
    protected $emailTemplate;
    /** @var string */
    protected $fromEmail;
    /** @var string */
    protected $fromName;
    /**
     * Optional
     * @var string|null
     */
    protected $bbcEmail;
    /**
     * Optional
     * @var string|null
     */
    protected $bbcName;
    /**
     * Contains email overviews that match the search criteria
     * @var EmailOverviewInterface[]
     */
    public $emails;

    /**
     * Item constructor.
     * @param string $mailboxName The name of the imap mailbox associated with the search
     * @param string $imapSearch The imap search string to use
     * @param string $emailTemplate The response email associated with this email
     * @param string $fromName The name to use on the response email
     * @param string $fromEmail The response email address to use
     */
    public function __construct($mailboxName, $imapSearch, $emailTemplate, $fromName, $fromEmail)
    {
        $this->mailboxName = $mailboxName;
        $this->imapSearch = $imapSearch;
        $this->fromEmail = $fromEmail;
        $this->fromName = $fromName;

        if (!defined('TEMPLATE_DIR') || $emailTemplate{0} === '/') {
            $this->emailTemplate = $emailTemplate;
        } else {
            $this->emailTemplate = TEMPLATE_DIR . '/' . $emailTemplate;
        }
    }

    /**
     * Throws a fatal error if an invalid method is called (to duplicate PHP native handling)
     *
     * @param string $name The name of the method
     */
    protected function invalidMethod($name)
    {
        $method = __CLASS__ . '::' . $name . '()';
        die("PHP Fatal error:  Call to undefined method $method");
    }

    /**
     * Magic method that handles all getters and setters for the class
     *
     * @param string $methodName The name of the method being called
     * @param [] $args The arguments for the given method
     * @return mixed
     */
    public function __call($methodName, $args)
    {
        $settableProperties = ['bbcEmail', 'bbcName'];
        $type = substr($methodName, 0, 3);
        if ($type === 'get') {
            $propertyName = lcfirst(substr($methodName, 3));
            if (property_exists($this, $propertyName)) {
                return $this->{$propertyName};
            }
        } elseif ($type === 'set') {
            $propertyName = lcfirst(substr($methodName, 3));
            if (in_array($propertyName, $settableProperties)) {
                $this->{$propertyName} = $args[0];
                return null;
            }
        }
        $this->invalidMethod($methodName);
        return null;
    }
}
