<?php

declare(strict_types=1);

namespace Tests\Unit\Context;

use Communication\Context\AbstractCommunicationContext;
use Communication\Entity\Recipient;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mime\Address;

/**
 * @covers \Communication\Context\AbstractCommunicationContext
 */
class AbstractCommunicationContextTest extends TestCase
{
    private TestableAbstractCommunicationContext $context;

    protected function setUp(): void
    {
        $this->context = new TestableAbstractCommunicationContext();
    }

    /**
     * @covers \Communication\Context\AbstractCommunicationContext::addBodyContext
     * @covers \Communication\Context\AbstractCommunicationContext::getBodyContext
     * @covers \Communication\Context\AbstractCommunicationContext::setBodyContext
     */
    public function testBodyContextManagement(): void
    {
        // Test initial state
        $this->assertSame([], $this->context->getBodyContext());

        // Test adding individual context items
        $result = $this->context->addBodyContext('name', 'John Doe');
        $this->assertSame($this->context, $result); // Test fluent interface
        $this->assertSame(['name' => 'John Doe'], $this->context->getBodyContext());

        $this->context->addBodyContext('email', 'john@example.com');
        $this->assertSame([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ], $this->context->getBodyContext());

        // Test setting entire context array
        $newContext = ['greeting' => 'Hello', 'count' => 5];
        $result = $this->context->setBodyContext($newContext);
        $this->assertSame($this->context, $result); // Test fluent interface
        $this->assertSame($newContext, $this->context->getBodyContext());
    }

    /**
     * @covers \Communication\Context\AbstractCommunicationContext::getFrom
     * @covers \Communication\Context\AbstractCommunicationContext::setFrom
     * @covers \Communication\Context\AbstractCommunicationContext::extractAddress
     */
    public function testFromAddressManagement(): void
    {
        // Test initial state
        $this->assertNull($this->context->getFrom());

        // Test setting from Address object
        $address = new Address('sender@example.com', 'Sender Name');
        $result = $this->context->setFrom($address);
        $this->assertSame($this->context, $result); // Test fluent interface
        $this->assertSame($address, $this->context->getFrom());

        // Test setting from string
        $this->context->setFrom('new@example.com');
        $fromAddress = $this->context->getFrom();
        $this->assertInstanceOf(Address::class, $fromAddress);
        $this->assertSame('new@example.com', $fromAddress->getAddress());

        // Test setting from array
        $this->context->setFrom(['email' => 'array@example.com', 'name' => 'Array Name']);
        $fromAddress = $this->context->getFrom();
        $this->assertInstanceOf(Address::class, $fromAddress);
        $this->assertSame('array@example.com', $fromAddress->getAddress());
        $this->assertSame('Array Name', $fromAddress->getName());

        // Test setting from Recipient entity
        $recipient = (new Recipient(['email']))
            ->setEmail('recipient@example.com')
            ->setName('Recipient Name');
        $this->context->setFrom($recipient);
        $fromAddress = $this->context->getFrom();
        $this->assertInstanceOf(Address::class, $fromAddress);
        $this->assertSame('recipient@example.com', $fromAddress->getAddress());
        $this->assertSame('Recipient Name', $fromAddress->getName());

        // Test setting null
        $this->context->setFrom(null);
        $this->assertNull($this->context->getFrom());
    }

    /**
     * @covers \Communication\Context\AbstractCommunicationContext::getRecipients
     * @covers \Communication\Context\AbstractCommunicationContext::setRecipients
     * @covers \Communication\Context\AbstractCommunicationContext::extractAddresses
     * @covers \Communication\Context\AbstractCommunicationContext::extractAddress
     */
    public function testRecipientsManagement(): void
    {
        // Test initial state
        $this->assertSame([], $this->context->getRecipients());

        // Test setting recipients from array of strings
        $recipients = ['user1@example.com', 'user2@example.com'];
        $result = $this->context->setRecipients($recipients);
        $this->assertSame($this->context, $result); // Test fluent interface

        $recipientAddresses = $this->context->getRecipients();
        $this->assertCount(2, $recipientAddresses);
        $this->assertInstanceOf(Address::class, $recipientAddresses[0]);
        $this->assertSame('user1@example.com', $recipientAddresses[0]->getAddress());
        $this->assertSame('user2@example.com', $recipientAddresses[1]->getAddress());

        // Test setting recipients from array of Address objects
        $addressObjects = [
            new Address('addr1@example.com', 'Name 1'),
            new Address('addr2@example.com', 'Name 2'),
        ];
        $this->context->setRecipients($addressObjects);
        $this->assertSame($addressObjects, $this->context->getRecipients());

        // Test setting recipients from mixed array
        $recipient = (new Recipient(['email']))
            ->setEmail('recipient@example.com')
            ->setName('Recipient Name');
        $mixedRecipients = [
            'string@example.com',
            new Address('address@example.com', 'Address Name'),
            ['email' => 'array@example.com', 'name' => 'Array Name'],
            $recipient,
        ];
        $this->context->setRecipients($mixedRecipients);
        $recipientAddresses = $this->context->getRecipients();
        $this->assertCount(4, $recipientAddresses);

        $this->assertSame('string@example.com', $recipientAddresses[0]->getAddress());
        $this->assertSame('address@example.com', $recipientAddresses[1]->getAddress());
        $this->assertSame('Address Name', $recipientAddresses[1]->getName());
        $this->assertSame('array@example.com', $recipientAddresses[2]->getAddress());
        $this->assertSame('Array Name', $recipientAddresses[2]->getName());
        $this->assertSame('recipient@example.com', $recipientAddresses[3]->getAddress());
        $this->assertSame('Recipient Name', $recipientAddresses[3]->getName());
    }

    /**
     * @covers \Communication\Context\AbstractCommunicationContext::getSubject
     * @covers \Communication\Context\AbstractCommunicationContext::setSubject
     */
    public function testSubjectManagement(): void
    {
        // Test initial state
        $this->assertSame('', $this->context->getSubject());

        // Test setting subject
        $result = $this->context->setSubject('Test Subject');
        $this->assertSame($this->context, $result); // Test fluent interface
        $this->assertSame('Test Subject', $this->context->getSubject());
    }

    /**
     * @covers \Communication\Context\AbstractCommunicationContext::getHtmlTemplate
     * @covers \Communication\Context\AbstractCommunicationContext::setHtmlTemplate
     */
    public function testHtmlTemplateManagement(): void
    {
        // Test initial state
        $this->assertSame('', $this->context->getHtmlTemplate());

        // Test setting template
        $result = $this->context->setHtmlTemplate('welcome');
        $this->assertSame($this->context, $result); // Test fluent interface
        $this->assertSame('welcome', $this->context->getHtmlTemplate());

        // Test setting null
        $this->context->setHtmlTemplate(null);
        $this->assertNull($this->context->getHtmlTemplate());
    }

    /**
     * @covers \Communication\Context\AbstractCommunicationContext::getTextTemplate
     * @covers \Communication\Context\AbstractCommunicationContext::setTextTemplate
     */
    public function testTextTemplateManagement(): void
    {
        // Test initial state
        $this->assertSame('', $this->context->getTextTemplate());

        // Test setting template
        $result = $this->context->setTextTemplate('welcome_text');
        $this->assertSame($this->context, $result); // Test fluent interface
        $this->assertSame('welcome_text', $this->context->getTextTemplate());

        // Test setting null
        $this->context->setTextTemplate(null);
        $this->assertNull($this->context->getTextTemplate());
    }

    /**
     * @covers \Communication\Context\AbstractCommunicationContext::getBcc
     * @covers \Communication\Context\AbstractCommunicationContext::setBcc
     */
    public function testBccManagement(): void
    {
        // Test initial state
        $this->assertSame([], $this->context->getBcc());

        // Test setting BCC addresses
        $bccAddresses = ['bcc1@example.com', 'bcc2@example.com'];
        $result = $this->context->setBcc($bccAddresses);
        $this->assertSame($this->context, $result); // Test fluent interface

        $bcc = $this->context->getBcc();
        $this->assertCount(2, $bcc);
        $this->assertInstanceOf(Address::class, $bcc[0]);
        $this->assertSame('bcc1@example.com', $bcc[0]->getAddress());
        $this->assertSame('bcc2@example.com', $bcc[1]->getAddress());
    }

    /**
     * @covers \Communication\Context\AbstractCommunicationContext::getReplyTo
     * @covers \Communication\Context\AbstractCommunicationContext::setReplyTo
     */
    public function testReplyToManagement(): void
    {
        // Test initial state (should return from address if no reply-to set)
        $this->assertSame([], $this->context->getReplyTo());

        // Set a from address
        $fromAddress = new Address('from@example.com', 'From Name');
        $this->context->setFrom($fromAddress);
        $this->assertSame([$fromAddress], $this->context->getReplyTo());

        // Test setting reply-to addresses
        $replyToAddresses = ['reply1@example.com', 'reply2@example.com'];
        $result = $this->context->setReplyTo($replyToAddresses);
        $this->assertSame($this->context, $result); // Test fluent interface

        $replyTo = $this->context->getReplyTo();
        $this->assertCount(2, $replyTo);
        $this->assertInstanceOf(Address::class, $replyTo[0]);
        $this->assertSame('reply1@example.com', $replyTo[0]->getAddress());
        $this->assertSame('reply2@example.com', $replyTo[1]->getAddress());
    }

    /**
     * @covers \Communication\Context\AbstractCommunicationContext::extractAddress
     */
    public function testExtractAddressWithInvalidInput(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid address type: integer');

        $this->context->testExtractAddress(123);
    }

    /**
     * @covers \Communication\Context\AbstractCommunicationContext::extractAddress
     */
    public function testExtractAddressWithInvalidArray(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid address type: array');

        $this->context->testExtractAddress(['invalid' => 'array']);
    }
}

/**
 * Testable concrete implementation of AbstractCommunicationContext for testing
 */
class TestableAbstractCommunicationContext extends AbstractCommunicationContext
{
    /**
     * Expose the protected extractAddress method for testing
     */
    public function testExtractAddress($address): Address
    {
        return $this->extractAddress($address);
    }
}
