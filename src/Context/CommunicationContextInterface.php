<?php

declare(strict_types=1);

namespace Communication\Context;

use Symfony\Component\Mime\Address;

interface CommunicationContextInterface
{
    /**
     * @param mixed $value
     */
    public function addBodyContext(string $name, $value): self;

    public function getBodyContext(): array;

    public function setBodyContext(array $bodyContext): self;

    public function getFrom(): ?Address;

    /**
     * @param mixed $from
     */
    public function setFrom($from): self;

    /**
     * @return array<mixed>
     */
    public function getRecipients(): array;

    /**
     * @param mixed $recipients
     */
    public function setRecipients($recipients): self;

    public function getSubject(): string;

    public function getHtmlTemplate(): ?string;

    public function getTextTemplate(): ?string;

    /**
     * @return array<Address>
     */
    public function getBcc(): array;

    /**
     * @return array<Address>
     */
    public function getReplyTo(): array;
}
