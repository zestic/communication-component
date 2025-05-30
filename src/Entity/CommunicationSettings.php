<?php

declare(strict_types=1);

namespace Communication\Entity;

use Symfony\Component\Mime\Address;

class CommunicationSettings
{
    public function __construct(
        private Address $fromAddress,
    ) {
    }

    public function getFromAddress(): Address
    {
        return $this->fromAddress;
    }

    /**
     * @param Address|string|array<string, mixed> $fromAddress
     */
    public function setFromAddress(Address|string|array $fromAddress): self
    {
        if ($fromAddress instanceof Address) {
            $this->fromAddress = $fromAddress;
        } elseif (is_string($fromAddress)) {
            $this->fromAddress = new Address($fromAddress);
        } elseif (is_array($fromAddress)) {
            if (!array_key_exists('email', $fromAddress) || !is_string($fromAddress['email']) || $fromAddress['email'] === '') {
                throw new \InvalidArgumentException('Array must contain "email" key');
            }
            $email = $fromAddress['email'];
            $name = $fromAddress['name'] ?? '';
            $this->fromAddress = new Address($email, $name);
        } else {
            throw new \InvalidArgumentException('fromAddress must be an Address instance, string, or array with email key');
        }

        return $this;
    }
}
