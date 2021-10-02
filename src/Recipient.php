<?php
declare(strict_types=1);

namespace Communication;

use Symfony\Component\Notifier\Exception\InvalidArgumentException;
use Symfony\Component\Notifier\Recipient\EmailRecipientInterface;
use Symfony\Component\Notifier\Recipient\EmailRecipientTrait;
use Symfony\Component\Notifier\Recipient\SmsRecipientInterface;
use Symfony\Component\Notifier\Recipient\SmsRecipientTrait;

final class Recipient implements EmailRecipientInterface, SmsRecipientInterface
{
    use EmailRecipientTrait;
    use SmsRecipientTrait;

    /** @var string[] */
    private array $channels;
    private string $email;
    private string $name;
    private string $phone;

    public function __construct(
        array $channels = [],
    ) {
    }

    public function getChannels(): array
    {
        return $this->channels;
    }

    public function setEmail(string $email): Recipient
    {
        $this->email = $email;
        $this->channels[] = 'email';

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): Recipient
    {
        $this->name = $name;

        return $this;
    }

    public function setPhone(string $phone): Recipient
    {
        $this->phone = $phone;
        $this->channels[] = 'sms';

        return $this;
    }
}
