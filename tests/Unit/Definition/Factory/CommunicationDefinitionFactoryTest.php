<?php

declare(strict_types=1);

namespace Tests\Unit\Communication\Definition\Factory;

use Communication\Definition\Factory\CommunicationDefinitionFactory;
use PHPUnit\Framework\TestCase;

class CommunicationDefinitionFactoryTest extends TestCase
{
    private CommunicationDefinitionFactory $factory;

    protected function setUp(): void
    {
        $this->factory = new CommunicationDefinitionFactory();
    }

    public function testCreateParcelArrivalDefinition(): void
    {
        $definition = $this->factory->createParcelArrivalDefinition();

        $this->assertEquals('parcel.arrival', $definition->getIdentifier());
        $this->assertEquals('Parcel Arrival Notification', $definition->getName());
        $this->assertCount(2, $definition->getChannelDefinitions());

        $emailDef = $definition->getChannelDefinitions()['email'];
        $this->assertEquals('email', $emailDef->getChannel());
        $this->assertEquals('emails/parcel-arrival.html.twig', $emailDef->getTemplate());
        $this->assertEquals('notifications@mailforwarding.example.com', $emailDef->getFromAddress());
        $this->assertEquals('support@mailforwarding.example.com', $emailDef->getReplyTo());

        $mobileDef = $definition->getChannelDefinitions()['mobile'];
        $this->assertEquals('mobile', $mobileDef->getChannel());
        $this->assertEquals('mobile/parcel-arrival.json', $mobileDef->getTemplate());
        $this->assertEquals(1, $mobileDef->getPriority());
        $this->assertFalse($mobileDef->requiresAuth());
    }

    public function testCreateSubscriptionRenewalDefinition(): void
    {
        $definition = $this->factory->createSubscriptionRenewalDefinition();

        $this->assertEquals('subscription.renewal', $definition->getIdentifier());
        $this->assertEquals('Subscription Renewal Notice', $definition->getName());
        $this->assertCount(1, $definition->getChannelDefinitions());

        $emailDef = $definition->getChannelDefinitions()['email'];
        $this->assertEquals('email', $emailDef->getChannel());
        $this->assertEquals('emails/subscription-renewal.html.twig', $emailDef->getTemplate());
        $this->assertEquals('billing@mailforwarding.example.com', $emailDef->getFromAddress());
        $this->assertNull($emailDef->getReplyTo());
    }
}
