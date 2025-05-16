<?php

declare(strict_types=1);

namespace Communication\Factory\Notification;

use Communication\Context\CommunicationContextInterface;
use Symfony\Component\Notifier\Notification\Notification;

interface NotificationFactoryInterface
{
    public function create(CommunicationContextInterface $emailContext, string $channel): Notification;
}
