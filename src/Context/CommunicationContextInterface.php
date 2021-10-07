<?php
declare(strict_types=1);

namespace Communication\Context;

use Symfony\Component\Mime\Address;
use Symfony\Component\Notifier\Message\MessageInterface;

interface CommunicationContextInterface
{
    public function getFrom(): ?Address;
    public function setFrom($from);
    public function getRecipients(): array;
    public function setRecipients($recipients);
    public function createMessage(): MessageInterface;
}
