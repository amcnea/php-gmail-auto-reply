<?php

namespace AutoReply\Mail;

/**
 * Class EmailOverviewInterface
 *
 * Interface like object defined for type hinting on the email over object return by 'imap_fetch_overview' function
 */
abstract class EmailOverviewInterface
{
    /** @var string */
    public $subject; //Test Message
    /** @var string */
    public $from; //John Doe <johndoe@gmail.com>
    /** @var string */
    public $to; //sample@email.com
    /** @var string */
    public $date; //Fri, 22 Apr 2016 22:24:48 -0500
    /** @var string */
    public $message_id; //<CA+WgRv0bnnDjE_JOKhstuFmOOBj-N4urEDf8mHajUk907Up9aQ@mail.gmail.com>
    /** @var integer */
    public $size; //3326
    /** @var integer */
    public $uid; //15
    /** @var integer */
    public $msgno; //15
    /** @var integer */
    public $recent; //0
    /** @var integer */
    public $flagged; //0
    /** @var integer */
    public $answered; //0
    /** @var integer */
    public $deleted; //0
    /** @var integer */
    public $seen; //0
    /** @var integer */
    public $draft; //0
    /** @var integer */
    public $udate; //1461381889
}