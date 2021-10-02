<?php
declare(strict_types=1);

namespace Communication\Context;

final class EmailContext
{
    /** @var \Notification\Recipient[] */
    private $bcc = [];
    /** @var array */
    private $bodyContext;
    /** @var \Notification\Recipient[] */
    private $cc = [];
    /** @var \Notification\Recipient[] */
    private $from = [];
    /** @var string */
    private $htmlTemplate;
    /** @var \Notification\Recipient[] */
    private $replyTo = [];
    /** @var string */
    private $subject = '';
    /** @var string */
    private $textTemplate;
    /** @var \Notification\Recipient[] */
    private $to = [];

    public function getBcc(): array
    {
        return $this->bcc;
    }

    public function setBcc(array $bcc): EmailContext
    {
        $this->bcc = $bcc;

        return $this;
    }

    public function getBodyContext(): array
    {
        return $this->bodyContext;
    }

    public function setBodyContext(array $bodyContext): EmailContext
    {
        $this->bodyContext = $bodyContext;

        return $this;
    }

    public function getCc(): array
    {
        return $this->cc;
    }

    public function setCc(array $cc): EmailContext
    {
        $this->cc = $cc;

        return $this;
    }

    public function getFrom(): array
    {
        return $this->from;
    }

    public function setFrom(array $from): EmailContext
    {
        $this->from = $from;

        return $this;
    }

    public function getHtmlTemplate(): ?string
    {
        return $this->htmlTemplate;
    }

    public function setHtmlTemplate(?string $htmlTemplate): EmailContext
    {
        $this->htmlTemplate = $htmlTemplate;

        return $this;
    }

    public function getReplyTo(): array
    {
        if (empty($this->replyTo)) {
            return $this->from;
        }

        return $this->replyTo;
    }

    public function setReplyTo(array $replyTo): EmailContext
    {
        $this->replyTo = $replyTo;

        return $this;
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function setSubject(string $subject): EmailContext
    {
        $this->subject = $subject;

        return $this;
    }

    public function getTextTemplate(): ?string
    {
        return $this->textTemplate;
    }

    public function setTextTemplate(?string $textTemplate): EmailContext
    {
        $this->textTemplate = $textTemplate;

        return $this;
    }

    public function getTo(): array
    {
        return $this->to;
    }

    public function setTo(array $to): EmailContext
    {
        $this->to = $to;

        return $this;
    }
}
