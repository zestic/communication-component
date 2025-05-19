<?php

declare(strict_types=1);

namespace Tests\Unit\Communication\Interactor;

use Communication\Communication;
use Communication\Context\CommunicationContext;
use Communication\Context\CommunicationContextInterface;
use Communication\Definition\CommunicationDefinition;
use Communication\Definition\EmailChannelDefinition;
use Communication\Definition\Repository\CommunicationDefinitionRepositoryInterface;
use Communication\Interactor\SendCommunication;
use Communication\Recipient;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\NotifierInterface;

/**
 * @covers \Communication\Interactor\SendCommunication
 */
class SendCommunicationTest extends MockeryTestCase
{
    private CommunicationDefinitionRepositoryInterface|MockInterface $definitionRepository;
    private array $notificationFactories;
    private NotifierInterface|MockInterface $notifier;
    private SendCommunication $sendCommunication;
    private CommunicationDefinition|MockInterface $definition;
    private EmailChannelDefinition|MockInterface $emailChannelDefinition;
    private CommunicationContextInterface|MockInterface $emailContext;
    private CommunicationContext|MockInterface $communicationContext;
    private Notification|MockInterface $notification;
    private MockInterface $notificationFactory;

    protected function setUp(): void
    {
        $this->definitionRepository = Mockery::mock(CommunicationDefinitionRepositoryInterface::class);
        $this->notificationFactory = Mockery::mock('NotificationFactory');
        $this->notificationFactories = ['email' => $this->notificationFactory];
        $this->notifier = Mockery::mock(NotifierInterface::class);

        $this->sendCommunication = new SendCommunication(
            $this->definitionRepository,
            $this->notificationFactories,
            $this->notifier
        );

        // Setup common mocks
        $this->definition = Mockery::mock(CommunicationDefinition::class);
        $this->emailChannelDefinition = Mockery::mock(EmailChannelDefinition::class);
        $this->emailContext = Mockery::mock(CommunicationContextInterface::class);
        $this->communicationContext = Mockery::mock(CommunicationContext::class);
        $this->notification = Mockery::mock(Notification::class);
    }

    public function testSendThrowsExceptionWhenDefinitionNotFound(): void
    {
        // Arrange
        $communication = new Communication('unknown.definition');

        $this->definitionRepository->shouldReceive('findByIdentifier')
            ->with('unknown.definition')
            ->once()
            ->andReturnNull();

        // Assert & Act
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Communication definition not found: unknown.definition');

        $this->sendCommunication->send($communication);
    }

    public function testSendValidatesCommunicationContexts(): void
    {
        // Arrange
        $communication = Mockery::mock(Communication::class);
        $recipient = Mockery::mock(Recipient::class);

        // Setup communication mock
        $communication->shouldReceive('getDefinitionId')->andReturn('test.definition');
        $communication->shouldReceive('getContext')->andReturn($this->communicationContext);
        $communication->shouldReceive('getRecipients')->andReturn([$recipient]);

        // Setup definition repository mock
        $this->definitionRepository->shouldReceive('findByIdentifier')
            ->with('test.definition')
            ->once()
            ->andReturn($this->definition);

        // Setup channel definition mock
        $this->emailChannelDefinition->shouldReceive('getChannel')->andReturn('email');
        $this->definition->shouldReceive('getChannelDefinitions')
            ->andReturn([$this->emailChannelDefinition]);

        // Setup context mocks
        $this->communicationContext->shouldReceive('getContext')
            ->with('email')
            ->andReturn($this->emailContext);

        // Setup validation expectations
        $contextData = ['subject' => 'Test Subject', 'body' => 'Test Body'];
        $this->emailContext->shouldReceive('getBodyContext')->andReturn($contextData);
        $this->emailChannelDefinition->shouldReceive('validateContext')
            ->with($contextData)
            ->once();

        $this->emailContext->shouldReceive('getSubject')->andReturn('Test Subject');
        $this->emailChannelDefinition->shouldReceive('validateSubject')
            ->with(['subject' => 'Test Subject'])
            ->once();

        // Setup template application expectations
        $this->emailChannelDefinition->shouldReceive('getTemplate')
            ->andReturn('email-template.html.twig');
        $this->emailContext->shouldReceive('setHtmlTemplate')
            ->with('email-template.html.twig')
            ->once();

        // Setup from address expectations
        $this->emailChannelDefinition->shouldReceive('getFromAddress')
            ->andReturn('from@example.com');
        $this->emailContext->shouldReceive('setFrom')
            ->with('from@example.com')
            ->once();

        // Setup recipient expectations
        $recipient->shouldReceive('getChannels')->andReturn(['email']);
        $this->definition->shouldReceive('getChannelDefinition')
            ->with('email')
            ->andReturn($this->emailChannelDefinition);

        // Setup notification creation expectations
        $this->notificationFactory->shouldReceive('create')
            ->with($this->emailContext, 'email')
            ->andReturn($this->notification);

        // Setup sending expectations
        $this->notifier->shouldReceive('send')
            ->with($this->notification, $recipient)
            ->once();

        // Act
        $this->sendCommunication->send($communication);

        // No explicit assertions needed as Mockery will verify all expectations
    }

    public function testSendSkipsChannelsNotInDefinition(): void
    {
        // Arrange
        $communication = Mockery::mock(Communication::class);
        $recipient = Mockery::mock(Recipient::class);

        // Setup communication mock
        $communication->shouldReceive('getDefinitionId')->andReturn('test.definition');
        $communication->shouldReceive('getContext')->andReturn($this->communicationContext);
        $communication->shouldReceive('getRecipients')->andReturn([$recipient]);

        // Setup definition repository mock
        $this->definitionRepository->shouldReceive('findByIdentifier')
            ->with('test.definition')
            ->once()
            ->andReturn($this->definition);

        // Setup channel definition mock
        $this->emailChannelDefinition->shouldReceive('getChannel')->andReturn('email');
        $this->definition->shouldReceive('getChannelDefinitions')
            ->andReturn([$this->emailChannelDefinition]);

        // Setup context mocks
        $this->communicationContext->shouldReceive('getContext')
            ->with('email')
            ->andReturn($this->emailContext);

        // Setup validation expectations
        $contextData = ['subject' => 'Test Subject', 'body' => 'Test Body'];
        $this->emailContext->shouldReceive('getBodyContext')->andReturn($contextData);
        $this->emailChannelDefinition->shouldReceive('validateContext')
            ->with($contextData)
            ->once();

        $this->emailContext->shouldReceive('getSubject')->andReturn('Test Subject');
        $this->emailChannelDefinition->shouldReceive('validateSubject')
            ->with(['subject' => 'Test Subject'])
            ->once();

        // Setup template application expectations
        $this->emailChannelDefinition->shouldReceive('getTemplate')
            ->andReturn('email-template.html.twig');
        $this->emailContext->shouldReceive('setHtmlTemplate')
            ->with('email-template.html.twig')
            ->once();

        // Setup from address expectations
        $this->emailChannelDefinition->shouldReceive('getFromAddress')
            ->andReturn('from@example.com');
        $this->emailContext->shouldReceive('setFrom')
            ->with('from@example.com')
            ->once();

        // Setup recipient expectations - recipient has email and sms channels, but only email is in definition
        $recipient->shouldReceive('getChannels')->andReturn(['email', 'sms']);
        $this->definition->shouldReceive('getChannelDefinition')
            ->with('email')
            ->andReturn($this->emailChannelDefinition);
        $this->definition->shouldReceive('getChannelDefinition')
            ->with('sms')
            ->andReturnNull();

        // Setup notification creation expectations
        $this->notificationFactory->shouldReceive('create')
            ->with($this->emailContext, 'email')
            ->andReturn($this->notification);

        // Setup sending expectations
        $this->notifier->shouldReceive('send')
            ->with($this->notification, $recipient)
            ->once();

        // Act
        $this->sendCommunication->send($communication);

        // No explicit assertions needed as Mockery will verify all expectations
    }
}
