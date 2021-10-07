<?php
declare(strict_types=1);

namespace Communication\Factory\Message;

use Communication\Context\CommunicationContextInterface;
use Symfony\Component\Notifier\Message\MessageInterface;

interface MessageFactoryInterface
{
    public function createMessage(CommunicationContextInterface $context): MessageInterface;
}
