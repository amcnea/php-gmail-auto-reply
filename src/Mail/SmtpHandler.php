<?php

namespace AutoReply\Mail;

use AutoReply\Helper\Config;
use AutoReply\Helper\Logger;
use AutoReply\Search\Item as SearchItem;
use RuntimeException;
use PHPMailer;

class SmtpHandler
{
    /** @var self|null */
    protected static $me = null;
    /** @var PHPMailer */
    protected $mailer;
    /** @var string */
    protected $timeFormat;
    /** @var Logger */
    protected $logger;
    /** @var bool */
    protected $skipSending;

    private function __construct()
    {
        $conf = Config::instance();
        $this->timeFormat = $conf->get('time_format');
        $this->logger = Logger::instance();
        $this->skipSending = !$conf->get('send_email');

        $this->mailer = new PHPMailer();
        $this->mailer->Host = $conf->get('email.outgoing.host');
        $this->mailer->SMTPAuth = $conf->get('email.outgoing.smtpauth');
        $this->mailer->Username = $conf->get('email.outgoing.username');
        $this->mailer->Password = $conf->get('email.outgoing.password');
        $this->mailer->SMTPSecure = $conf->get('email.outgoing.smtpsecure');
        $this->mailer->Port = $conf->get('email.outgoing.port');
        $this->mailer->isSMTP();
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
     * Throws an error for SMTP related actions
     *
     * @param string $message
     * @throws RuntimeException
     */
    protected function throwSmtpError($message)
    {
        $errorMessage = $message . "\n" . $this->mailer->ErrorInfo . "\n";
        $this->logger->error($errorMessage, [__CLASS__]);
        throw new RuntimeException($errorMessage);
    }

    /**
     * Parse a template file and replacing tokens with values
     * Tokens are:
     *   {subject}
     *   {response_subject}
     *   {senddate}
     *   {date}
     *   {name}
     *
     * @param string $template
     * @param EmailOverviewInterface $overview
     */
    protected function parseTemplate($template, $overview)
    {
        $parsedAddress = $this->mailer->parseAddresses($overview->from);
        $contents = file_get_contents($template);
        $replace = array(
            '{subject}' => $overview->subject,
            '{response_subject}' => 'Re: ' . $overview->subject,
            '{senddate}' => date($this->timeFormat, strtotime($overview->date)),
            '{date}' => date($this->timeFormat),
            '{name}' => $parsedAddress[0]['name'],
        );
        $contents = str_replace(array_keys($replace), array_values($replace), $contents);
        $contents = preg_replace('#{\w+}#', '', $contents);
        return $contents;
    }

    /**
     * Sends a reply to an email
     *
     * @param EmailOverviewInterface $emailOverview
     * @param SearchItem $searchItem
     */
    public function sendReply($emailOverview, $searchItem)
    {
        $htmlTemplate = $searchItem->getEmailTemplate() . '.html';
        if (!file_exists($htmlTemplate)) {
            $this->throwSmtpError('Template file could not be found: ' . $htmlTemplate);
        }
        $textTemplate = $searchItem->getEmailTemplate() . '.txt';
        $parsedAddress = $this->mailer->parseAddresses($emailOverview->from);
        $this->mailer->clearAddresses();
        $this->mailer->isHTML(true);
        $this->mailer->setFrom($searchItem->getFromEmail(), $searchItem->getFromName());
        $this->mailer->addReplyTo($searchItem->getFromEmail(), $searchItem->getFromName());
        if ($searchItem->getBbcEmail() !== null) {
            $this->mailer->addBCC($searchItem->getBbcEmail(), $searchItem->getBbcName());
        }
        $this->mailer->addAddress($parsedAddress[0]['address'], $parsedAddress[0]['name']);
        $this->mailer->Subject = 'Re: ' . $emailOverview->subject;
        $this->mailer->Body = $this->parseTemplate($htmlTemplate, $emailOverview);
        if (file_exists($textTemplate)) {
            $this->mailer->AltBody = $this->parseTemplate($textTemplate, $emailOverview);
        }

        $this->logger->debug('Sending email: ' . $this->mailer->Subject, [__CLASS__]);
        if (!$this->skipSending) {
            if (!$this->mailer->send()) {
                $this->throwSmtpError('Error sending reply email.');
            }
        } else {
            $this->logger->debug('Skipping sending email, the \'send_email\' config is set to false.', [__CLASS__]);
        }
    }
}
