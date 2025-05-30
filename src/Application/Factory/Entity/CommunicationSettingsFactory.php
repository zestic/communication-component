<?php

declare(strict_types=1);

namespace Communication\Application\Factory\Entity;

use Communication\Entity\CommunicationSettings;
use Psr\Container\ContainerInterface;
use Symfony\Component\Mime\Address;

final class CommunicationSettingsFactory
{
    public function __invoke(ContainerInterface $container): CommunicationSettings
    {
        // Get email and name from environment variables with fallbacks
        $email = getenv('COMMUNICATION_FROM_EMAIL') ?: 'noreply@example.com';
        $name = getenv('COMMUNICATION_FROM_NAME') ?: 'System';

        return new CommunicationSettings(
            new Address($email, $name),
        );
    }
}
