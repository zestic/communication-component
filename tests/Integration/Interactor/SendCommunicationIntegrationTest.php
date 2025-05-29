<?php

declare(strict_types=1);

namespace Tests\Integration\Communication\Interactor;

use Communication\Context\CommunicationContext;
use Communication\Context\EmailContext;
use Communication\Context\SmsContext;
use Communication\Definition\CommunicationDefinition;
use Communication\Definition\EmailChannelDefinition;
use Communication\Definition\Repository\CommunicationDefinitionRepositoryInterface;
use Communication\Entity\Communication;
use Communication\Entity\CommunicationSettings;
use Communication\Entity\Recipient;
use Communication\Factory\CommunicationFactory;
use Communication\Factory\Context\ChannelContextFactory;
use Communication\Interactor\SendCommunication;
use Communication\Notification\EmailNotification;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Notifier\Message\EmailMessage;
use Symfony\Component\Notifier\NotifierInterface;

/**
 * @covers \Communication\Interactor\SendCommunication
 */
class SendCommunicationIntegrationTest extends MockeryTestCase
{
    private CommunicationDefinitionRepositoryInterface|MockInterface $definitionRepository;

    private NotifierInterface|MockInterface $notifier;

    private CommunicationFactory $communicationFactory;

    private SendCommunication $sendCommunication;

    protected function setUp(): void
    {
        $this->definitionRepository = Mockery::mock(CommunicationDefinitionRepositoryInterface::class);
        $this->notifier = Mockery::mock(NotifierInterface::class);

        // Create real CommunicationFactory with dependencies
        $settings = new CommunicationSettings(
            new Address('default@example.com', 'Default User')
        );

        $channelContextFactory = new ChannelContextFactory([
            'email' => EmailContext::class,
            'sms' => SmsContext::class,
        ]);

        $this->communicationFactory = new CommunicationFactory(
            $channelContextFactory,
            $settings
        );

        // Create a real notification factory
        $notificationFactory = new class () {
            /**
             * @param EmailContext $context
             * @param string $channel
             * @return EmailNotification
             */
            public function create(EmailContext $context, string $channel): EmailNotification
            {
                // Create a mock EmailMessage for testing
                $emailMessage = Mockery::mock(EmailMessage::class);
                $emailMessage->shouldReceive('getSubject')->andReturn('Test Subject');

                return new EmailNotification($emailMessage);
            }
        };

        $this->sendCommunication = new SendCommunication(
            $this->definitionRepository,
            ['email' => $notificationFactory],
            $this->notifier,
            $this->communicationFactory
        );
    }

    public function testSendCommunicationWithRealComponents(): void
    {
        // Create a real communication definition
        $definition = new CommunicationDefinition('test.notification', 'Test Notification');

        // Create a real email channel definition
        $emailDef = new EmailChannelDefinition(
            'emails/test-notification.html.twig',
            [
                'type' => 'object',
                'required' => ['message'],
                'properties' => [
                    'message' => ['type' => 'string'],
                    'name' => ['type' => 'string'],
                ],
            ],
            [
                'type' => 'object',
                'required' => ['subject'],
                'properties' => [
                    'subject' => ['type' => 'string'],
                ],
            ],
            'notifications@example.com',
            'reply@example.com'
        );

        $definition->addChannelDefinition($emailDef);

        // Setup repository mock to return our real definition
        $this->definitionRepository->shouldReceive('findByIdentifier')
            ->with('test.notification')
            ->andReturn($definition);

        // Create email message mock
        $emailMessage = Mockery::mock(EmailMessage::class);

        // Create a mock email context
        $emailContext = Mockery::mock(EmailContext::class);
        $emailContext->shouldReceive('getSubject')->andReturn('Test Subject');
        $emailContext->shouldReceive('getBodyContext')->andReturn([
            'message' => 'Hello, World!',
            'name' => 'Test User',
        ]);
        $emailContext->shouldReceive('setHtmlTemplate')->with('emails/test-notification.html.twig')->once();
        $emailContext->shouldReceive('setFrom')->with('notifications@example.com')->once();
        $emailContext->shouldReceive('setRecipients')->withAnyArgs()->once();
        $emailContext->shouldReceive('createMessage')->andReturn($emailMessage);

        // Create a real communication context
        $context = new CommunicationContext(['email' => $emailContext]);

        // Create a real communication
        $communication = new Communication('test.notification', $context);

        // Create a real recipient
        $recipient = new Recipient(['email']);
        $recipient->setEmail('user@example.com');
        $recipient->setName('Test User');

        // Add recipient to communication
        $communication->addRecipient($recipient);

        // Setup notifier mock to verify sending
        $this->notifier->shouldReceive('send')
            ->with(Mockery::type(EmailNotification::class), $recipient)
            ->atLeast()->once();

        // Act
        $this->sendCommunication->send($communication);

        // Mockery will verify all expectations
    }

    public function testSendCommunicationWithMultipleRecipients(): void
    {
        // Create a real communication definition
        $definition = new CommunicationDefinition('test.notification', 'Test Notification');

        // Create a real email channel definition
        $emailDef = new EmailChannelDefinition(
            'emails/test-notification.html.twig',
            [
                'type' => 'object',
                'required' => ['message'],
                'properties' => [
                    'message' => ['type' => 'string'],
                ],
            ],
            [
                'type' => 'object',
                'required' => ['subject'],
                'properties' => [
                    'subject' => ['type' => 'string'],
                ],
            ],
            'notifications@example.com'
        );

        $definition->addChannelDefinition($emailDef);

        // Setup repository mock to return our real definition
        $this->definitionRepository->shouldReceive('findByIdentifier')
            ->with('test.notification')
            ->andReturn($definition);

        // Create email message mock
        $emailMessage = Mockery::mock(EmailMessage::class);

        // Create a mock email context
        $emailContext = Mockery::mock(EmailContext::class);
        $emailContext->shouldReceive('getSubject')->andReturn('Test Subject');
        $emailContext->shouldReceive('getBodyContext')->andReturn([
            'message' => 'Hello, World!',
        ]);
        $emailContext->shouldReceive('setHtmlTemplate')->with('emails/test-notification.html.twig')->once();
        $emailContext->shouldReceive('setFrom')->with('notifications@example.com')->once();
        $emailContext->shouldReceive('setRecipients')->withAnyArgs()->once();
        $emailContext->shouldReceive('createMessage')->andReturn($emailMessage);

        // Create a real communication context
        $context = new CommunicationContext(['email' => $emailContext]);

        // Create a real communication
        $communication = new Communication('test.notification', $context);

        // Create multiple recipients
        $recipient1 = new Recipient(['email']);
        $recipient1->setEmail('user1@example.com');

        $recipient2 = new Recipient(['email']);
        $recipient2->setEmail('user2@example.com');

        // Add recipients to communication
        $communication->addRecipient([$recipient1, $recipient2]);

        // Setup notifier mock to verify sending to both recipients
        $this->notifier->shouldReceive('send')
            ->with(Mockery::type(EmailNotification::class), $recipient1)
            ->atLeast()->once();

        $this->notifier->shouldReceive('send')
            ->with(Mockery::type(EmailNotification::class), $recipient2)
            ->atLeast()->once();

        // Act
        $this->sendCommunication->send($communication);

        // Mockery will verify all expectations
    }

    public function testSendCommunicationWithArrayInput(): void
    {
        // Arrange - Create array data that will be converted to Communication by factory
        $arrayData = [
            'definitionId' => 'test.notification',
            'channels' => ['email'],
            'recipients' => [
                [
                    'email' => 'user@example.com',
                    'name' => 'Test User',
                ],
            ],
            'context' => [
                'subject' => ['name' => 'Test User'],
                'body' => ['message' => 'Hello from array input!'],
            ],
            'from' => [
                'email' => 'sender@example.com',
                'name' => 'Test Sender',
            ],
        ];

        // Create a real communication definition
        $definition = new CommunicationDefinition('test.notification', 'Test Notification');

        // Create a real email channel definition
        $emailDef = new EmailChannelDefinition(
            'emails/test-notification.html.twig',
            [
                'type' => 'object',
                'required' => ['message'],
                'properties' => [
                    'message' => ['type' => 'string'],
                ],
            ],
            [
                'type' => 'object',
                'required' => ['subject'],
                'properties' => [
                    'subject' => ['type' => 'string'],
                ],
            ],
            'notifications@example.com'
        );

        $definition->addChannelDefinition($emailDef);

        // Setup repository mock to return our real definition
        $this->definitionRepository->shouldReceive('findByIdentifier')
            ->with('test.notification')
            ->andReturn($definition);

        // Setup notifier mock to verify sending
        $this->notifier->shouldReceive('send')
            ->with(Mockery::type(EmailNotification::class), Mockery::type(Recipient::class))
            ->atLeast()->once();

        // Act - Send using array input (this will use CommunicationFactory internally)
        $this->sendCommunication->send($arrayData);

        // Mockery will verify all expectations
    }
}
