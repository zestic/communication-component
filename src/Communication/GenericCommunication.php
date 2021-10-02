<?php
declare(strict_types=1);

namespace Communication\Notification;

use Communication\Communication;
use Symfony\Component\Mime\Address;

final class GenericCommunication extends Communication
{
    public function dispatch(string $subject, string $body, ?Address $from)
    {
        $this->context->set('body', $body);
        /** @var \Communication\Context\EmailContext $emailContext */
        $emailContext = $this->context->getMeta('email');
        $emailContext
            ->setHtmlTemplate('generic')
            ->setSubject($subject);
        $this->send();
    }

    protected function getAllowedNotifications(): array
    {
        return [
            'email',
        ];
    }
}
