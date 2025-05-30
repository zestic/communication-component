<?php

declare(strict_types=1);

namespace Communication\Factory\Context;

use Communication\Context\EmailContext;
use Psr\Container\ContainerInterface;

class EmailContextFactory implements ContextFactoryInterface
{
    public function create(ContainerInterface $container, array $config): EmailContext
    {
        return (new EmailContext())
            ->setCc($config['data']['cc'] ?? [])
            ->setBcc($config['data']['bcc'] ?? [])
            ->setFrom($config['data']['from'] ?? null)
            ->setReplyTo($config['data']['reply_to'] ?? []);
    }
}
