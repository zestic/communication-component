<?php
declare(strict_types=1);

namespace Communication\Factory\Context;

use Communication\Context\EmailContext;
use Communication\Recipient;
use Psr\Container\ContainerInterface;

final class EmailContextFactory implements ContextFactoryInterface
{
    public function create(ContainerInterface $container, array $data): EmailContext
    {
        return (new EmailContext())
            ->setCc($data['cc'] ?? [])
            ->setBcc($data['bcc'] ?? [])
            ->setFrom($data['from'] ?? null)
            ->setReplyTo($data['reply_to'] ?? []);
    }
}
