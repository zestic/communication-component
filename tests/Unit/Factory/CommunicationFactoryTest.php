<?php

declare(strict_types=1);

namespace Tests\Unit\Communication\Factory;

use Communication\Context\EmailContext;
use Communication\Context\SmsContext;
use Communication\Entity\Communication;
use Communication\Entity\CommunicationSettings;
use Communication\Factory\CommunicationFactory;
use Communication\Factory\Context\ChannelContextFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mime\Address;

/**
 * @covers \Communication\Factory\CommunicationFactory
 */
class CommunicationFactoryTest extends TestCase
{
    private CommunicationFactory $factory;

    private CommunicationSettings $settings;

    private ChannelContextFactory $channelContextFactory;

    protected function setUp(): void
    {
        // Create real settings object
        $this->settings = new CommunicationSettings(
            new Address('default@example.com', 'Default User')
        );

        // Create a simple channel context factory that returns real contexts
        $this->channelContextFactory = new ChannelContextFactory([
            'email' => EmailContext::class,
            'sms' => SmsContext::class,
        ]);

        $this->factory = new CommunicationFactory(
            $this->channelContextFactory,
            $this->settings
        );
    }

    public function testCreateWithMinimalData(): void
    {
        // Arrange
        $data = [
            'definitionId' => 'test.notification',
            'channels' => ['email'],
        ];

        // Act
        $communication = $this->factory->create($data);

        // Assert
        $this->assertInstanceOf(Communication::class, $communication);
        $this->assertEquals('test.notification', $communication->getDefinitionId());
        $this->assertNotNull($communication->getContext());
        $this->assertNotNull($communication->getContext()->getContext('email'));
    }

    public function testCreateWithFromAddressOverride(): void
    {
        // Arrange
        $data = [
            'definitionId' => 'test.notification',
            'channels' => ['email'],
            'from' => [
                'email' => 'override@example.com',
                'name' => 'Override User',
            ],
        ];

        // Act
        $communication = $this->factory->create($data);

        // Assert
        $this->assertInstanceOf(Communication::class, $communication);
        $this->assertEquals('test.notification', $communication->getDefinitionId());

        $emailContext = $communication->getContext()->getContext('email');
        $this->assertNotNull($emailContext);
        $this->assertEquals('override@example.com', $emailContext->getFrom()->getAddress());
        $this->assertEquals('Override User', $emailContext->getFrom()->getName());
    }

    public function testCreateWithStringFromAddress(): void
    {
        // Arrange
        $data = [
            'definitionId' => 'test.notification',
            'channels' => ['email'],
            'from' => 'string@example.com',
        ];

        // Act
        $communication = $this->factory->create($data);

        // Assert
        $this->assertInstanceOf(Communication::class, $communication);
        $emailContext = $communication->getContext()->getContext('email');
        $this->assertEquals('string@example.com', $emailContext->getFrom()->getAddress());
    }

    public function testCreateWithAddressFromAddress(): void
    {
        // Arrange
        $fromAddress = new Address('address@example.com', 'Address User');
        $data = [
            'definitionId' => 'test.notification',
            'channels' => ['email'],
            'from' => $fromAddress,
        ];

        // Act
        $communication = $this->factory->create($data);

        // Assert
        $this->assertInstanceOf(Communication::class, $communication);
        $emailContext = $communication->getContext()->getContext('email');
        $this->assertEquals('address@example.com', $emailContext->getFrom()->getAddress());
        $this->assertEquals('Address User', $emailContext->getFrom()->getName());
    }

    public function testCreateWithRecipients(): void
    {
        // Arrange
        $data = [
            'definitionId' => 'test.notification',
            'channels' => ['email', 'sms'],
            'recipients' => [
                [
                    'email' => 'user1@example.com',
                    'name' => 'User One',
                    'phone' => '+1234567890',
                ],
                [
                    'email' => 'user2@example.com',
                    'name' => 'User Two',
                ],
            ],
        ];

        // Act
        $communication = $this->factory->create($data);

        // Assert
        $this->assertInstanceOf(Communication::class, $communication);
        $this->assertNotNull($communication->getContext()->getContext('email'));
        $this->assertNotNull($communication->getContext()->getContext('sms'));
    }

    public function testCreateWithFullContextData(): void
    {
        // Arrange
        $data = [
            'definitionId' => 'auth.email-verification',
            'channels' => ['email', 'sms'],
            'from' => [
                'email' => 'noreply@zestic.com',
                'name' => 'Zestic',
            ],
            'context' => [
                'subject' => [
                    'name' => 'John Doe',
                ],
                'body' => [
                    'name' => 'John Doe',
                    'link' => 'verification-token',
                ],
                'email' => [
                    'name' => 'John Doe Full Name',
                ],
                'sms' => [
                    'link' => 'phone-token',
                ],
            ],
            'recipients' => [
                [
                    'email' => 'john@example.com',
                    'name' => 'John Doe',
                    'phone' => '+15058675309',
                ],
            ],
        ];

        // Act
        $communication = $this->factory->create($data);

        // Assert
        $this->assertInstanceOf(Communication::class, $communication);
        $this->assertEquals('auth.email-verification', $communication->getDefinitionId());

        $emailContext = $communication->getContext()->getContext('email');
        $this->assertNotNull($emailContext);
        $this->assertEquals('noreply@zestic.com', $emailContext->getFrom()->getAddress());
        $this->assertEquals('Zestic', $emailContext->getFrom()->getName());

        // Check that subject context was set
        $this->assertEquals(['name' => 'John Doe'], $emailContext->getSubjectContext());

        // Check that body context was set
        $bodyContext = $emailContext->getBodyContext();
        $this->assertEquals('John Doe Full Name', $bodyContext['name']); // Channel-specific override
        $this->assertEquals('verification-token', $bodyContext['link']);

        $smsContext = $communication->getContext()->getContext('sms');
        $this->assertNotNull($smsContext);
        $smsBodyContext = $smsContext->getBodyContext();
        $this->assertEquals('John Doe', $smsBodyContext['name']);
        $this->assertEquals('phone-token', $smsBodyContext['link']); // Channel-specific override
    }

    public function testCreateWithEmptyRecipients(): void
    {
        // Arrange
        $data = [
            'definitionId' => 'test.notification',
            'channels' => ['email'],
            'recipients' => [],
        ];

        // Act
        $communication = $this->factory->create($data);

        // Assert
        $this->assertInstanceOf(Communication::class, $communication);
    }

    public function testCreateWithNoRecipients(): void
    {
        // Arrange
        $data = [
            'definitionId' => 'test.notification',
            'channels' => ['email'],
        ];

        // Act
        $communication = $this->factory->create($data);

        // Assert
        $this->assertInstanceOf(Communication::class, $communication);
    }

    public function testCreateWithInvalidFromArrayThrowsException(): void
    {
        // Arrange
        $data = [
            'definitionId' => 'test.notification',
            'channels' => ['email'],
            'from' => [
                'name' => 'No Email',
            ],
        ];

        // Assert & Act
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('From array must contain "email" key');

        $this->factory->create($data);
    }

    public function testCreateWithNonArrayContext(): void
    {
        // Arrange
        $data = [
            'definitionId' => 'test.notification',
            'channels' => ['email'],
            'context' => 'not-an-array',
        ];

        // Act
        $communication = $this->factory->create($data);

        // Assert
        $this->assertInstanceOf(Communication::class, $communication);
    }

    public function testCreateWithMultipleChannels(): void
    {
        // Arrange
        $data = [
            'definitionId' => 'test.notification',
            'channels' => ['email', 'sms'],
        ];

        // Act
        $communication = $this->factory->create($data);

        // Assert
        $this->assertInstanceOf(Communication::class, $communication);
        $this->assertNotNull($communication->getContext()->getContext('email'));
        $this->assertNotNull($communication->getContext()->getContext('sms'));
    }

    public function testCreateRecipientsWithPartialData(): void
    {
        // Arrange
        $data = [
            'definitionId' => 'test.notification',
            'channels' => ['email'],
            'recipients' => [
                ['email' => 'email-only@example.com'],
                ['email' => 'user@example.com', 'name' => 'Name Only'],
                ['email' => 'phone-user@example.com', 'phone' => '+1234567890'],
            ],
        ];

        // Act
        $communication = $this->factory->create($data);

        // Assert
        $this->assertInstanceOf(Communication::class, $communication);
    }

    public function testCreateWithNonArraySubjectContext(): void
    {
        // Arrange
        $data = [
            'definitionId' => 'test.notification',
            'channels' => ['email'],
            'context' => [
                'subject' => 'not-an-array',
                'body' => ['key' => 'value'],
            ],
        ];

        // Act
        $communication = $this->factory->create($data);

        // Assert
        $this->assertInstanceOf(Communication::class, $communication);
        $emailContext = $communication->getContext()->getContext('email');
        $this->assertEquals(['key' => 'value'], $emailContext->getBodyContext());
    }

    public function testCreateWithNonArrayBodyContext(): void
    {
        // Arrange
        $data = [
            'definitionId' => 'test.notification',
            'channels' => ['email'],
            'context' => [
                'subject' => ['key' => 'value'],
                'body' => 'not-an-array',
            ],
        ];

        // Act
        $communication = $this->factory->create($data);

        // Assert
        $this->assertInstanceOf(Communication::class, $communication);
        $emailContext = $communication->getContext()->getContext('email');
        $this->assertEquals(['key' => 'value'], $emailContext->getSubjectContext());
    }

    public function testCreateWithNonArrayChannelContext(): void
    {
        // Arrange
        $data = [
            'definitionId' => 'test.notification',
            'channels' => ['email'],
            'context' => [
                'email' => 'not-an-array',
            ],
        ];

        // Act
        $communication = $this->factory->create($data);

        // Assert
        $this->assertInstanceOf(Communication::class, $communication);
    }

    public function testCreateWithNonExistentChannelContext(): void
    {
        // Arrange
        $data = [
            'definitionId' => 'test.notification',
            'channels' => ['email'],
            'context' => [
                'nonexistent' => ['key' => 'value'],
            ],
        ];

        // Act
        $communication = $this->factory->create($data);

        // Assert
        $this->assertInstanceOf(Communication::class, $communication);
    }

    public function testCreateWithComplexRecipientData(): void
    {
        // Arrange
        $data = [
            'definitionId' => 'test.notification',
            'channels' => ['email', 'sms'],
            'recipients' => [
                [
                    'email' => 'user@example.com',
                    'name' => 'Test User',
                    'phone' => '+1234567890',
                ],
            ],
        ];

        // Act
        $communication = $this->factory->create($data);

        // Assert
        $this->assertInstanceOf(Communication::class, $communication);
        $this->assertEquals('test.notification', $communication->getDefinitionId());
    }

    public function testCreateUsesSettingsWhenNoFromProvided(): void
    {
        // Arrange
        $data = [
            'definitionId' => 'test.notification',
            'channels' => ['email'],
        ];

        // Act
        $communication = $this->factory->create($data);

        // Assert
        $emailContext = $communication->getContext()->getContext('email');
        $this->assertEquals('default@example.com', $emailContext->getFrom()->getAddress());
        $this->assertEquals('Default User', $emailContext->getFrom()->getName());
    }

    public function testCreateWithUserExampleData(): void
    {
        // Arrange - This is the exact example from the user's request
        $data = [
            'channels' => [
                'email',
                'sms',
            ],
            'definitionId' => 'auth.email-verification',
            'from' => [
                'email' => 'noreply@americasmailbox.com',
                'name' => 'Americas Mailbox',
            ],
            'context' => [
                'subject' => [
                    'name' => 'John Doe',
                ],
                'body' => [
                    'name' => 'John Doe',
                    'link' => 'verification-token',
                ],
                'email' => [
                    'name' => 'John Doe Full Name',
                ],
                'sms' => [
                    'link' => 'phone-token',
                ],
            ],
            'recipients' => [
                [
                    'email' => 'john@example.com',
                    'name' => 'John Doe',
                    'phone' => '+15058675309',
                ],
            ],
        ];

        // Act
        $communication = $this->factory->create($data);

        // Assert
        $this->assertInstanceOf(Communication::class, $communication);
        $this->assertEquals('auth.email-verification', $communication->getDefinitionId());

        // Verify from address
        $emailContext = $communication->getContext()->getContext('email');
        $this->assertEquals('noreply@americasmailbox.com', $emailContext->getFrom()->getAddress());
        $this->assertEquals('Americas Mailbox', $emailContext->getFrom()->getName());

        // Verify contexts are set correctly
        $this->assertEquals(['name' => 'John Doe'], $emailContext->getSubjectContext());
        $emailBodyContext = $emailContext->getBodyContext();
        $this->assertEquals('John Doe Full Name', $emailBodyContext['name']); // Channel-specific override
        $this->assertEquals('verification-token', $emailBodyContext['link']);

        $smsContext = $communication->getContext()->getContext('sms');
        $this->assertNotNull($smsContext);
        $smsBodyContext = $smsContext->getBodyContext();
        $this->assertEquals('John Doe', $smsBodyContext['name']);
        $this->assertEquals('phone-token', $smsBodyContext['link']); // Channel-specific override
    }
}
