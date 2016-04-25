<?php

namespace AutoReply\Mail;

/**
 * Class MailboxInterface
 *
 * Interface like object defined for type hinting on the imap mailbox check object
 * stream variable attached to default object returned my imap_check() function
 */
abstract class MailboxInterface
{
    /** @var string */
    public $Date;
    /** @var string */
    public $Driver;
    /** @var string */
    public $Mailbox;
    /** @var string */
    public $Nmsgs;
    /** @var string */
    public $Recent;
    /**
     * An imap stream resource produced by the 'imap_open' function
     * @var resource */
    public $stream;
}
