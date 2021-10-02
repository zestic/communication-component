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
        $cc = $this->createRecipients($data['cc'] ?? []);
        $bcc = $this->createRecipients($data['bcc'] ?? []);
        $from = $this->createRecipients($data['from'] ?? []);
        $replyTo = $this->createRecipients($data['reply_to'] ?? []);

        return (new EmailContext())
            ->setCc($cc)
            ->setBcc($bcc)
            ->setFrom($from)
            ->setReplyTo($replyTo);
    }

    private function createRecipients(array $config): array
    {
        $recipients = [];
        foreach ($config as $email) {
            $recipients[] = (new Recipient())->setEmail($email);
        }

        return $recipients;
    }
}
