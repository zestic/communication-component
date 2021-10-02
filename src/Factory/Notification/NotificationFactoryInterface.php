<?php
declare(strict_types=1);

namespace Communication\Factory\Notification;

use Communication\Context\CommunicationContext;
use Symfony\Component\Notifier\Notification\Notification;

interface NotificationFactoryInterface
{
    public function create(CommunicationContext $context, string $channel): Notification;
}
