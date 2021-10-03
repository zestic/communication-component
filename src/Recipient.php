<?php
declare(strict_types=1);

namespace Communication;

use Symfony\Component\Mime\Address;
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
    private string $name = '';

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

    public function getEmailAddress(): Address
    {
        return new Address($this->email, $this->name);
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
