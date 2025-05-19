<?php

declare(strict_types=1);

namespace Tests\Unit\Communication\Definition;

use Communication\Definition\ChannelDefinition;
use Communication\Definition\CommunicationDefinition;
use PHPUnit\Framework\TestCase;

class CommunicationDefinitionTest extends TestCase
{
    public function testAddAndGetChannelDefinition(): void
    {
        $channelDef = $this->createMock(ChannelDefinition::class);
        $channelDef->method('getChannel')->willReturn('email');

        $definition = new CommunicationDefinition('test.notification', 'Test Notification');
        $definition->addChannelDefinition($channelDef);

        $this->assertSame($channelDef, $definition->getChannelDefinition('email'));
        $this->assertNull($definition->getChannelDefinition('unknown'));
    }

    public function testGetIdentifier(): void
    {
        $definition = new CommunicationDefinition('test.notification', 'Test Notification');
        $this->assertEquals('test.notification', $definition->getIdentifier());
    }

    public function testGetChannelDefinitions(): void
    {
        $emailDef = $this->createMock(ChannelDefinition::class);
        $emailDef->method('getChannel')->willReturn('email');

        $mobileDef = $this->createMock(ChannelDefinition::class);
        $mobileDef->method('getChannel')->willReturn('mobile');

        $definition = new CommunicationDefinition('test.notification', 'Test Notification');
        $definition->addChannelDefinition($emailDef);
        $definition->addChannelDefinition($mobileDef);

        $channelDefs = $definition->getChannelDefinitions();
        $this->assertCount(2, $channelDefs);
        $this->assertSame($emailDef, $channelDefs['email']);
        $this->assertSame($mobileDef, $channelDefs['mobile']);
    }
}
