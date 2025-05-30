<?php

declare(strict_types=1);

namespace Tests\Unit\Context;

use Communication\Context\EmailContext;
use Communication\Entity\Recipient;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mime\Address;

/**
 * @covers \Communication\Context\EmailContext
 */
class EmailContextTest extends TestCase
{
    private EmailContext $context;

    protected function setUp(): void
    {
        $this->context = new EmailContext();
    }

    /**
     * @covers \Communication\Context\EmailContext::getBody
     * @covers \Communication\Context\EmailContext::setBody
     */
    public function testBodyManagement(): void
    {
        // Test initial state
        $this->assertSame('', $this->context->getBody());

        // Test setting body
        $result = $this->context->setBody('This is the email body content.');
        $this->assertSame($this->context, $result); // Test fluent interface
        $this->assertSame('This is the email body content.', $this->context->getBody());

        // Test setting empty body
        $this->context->setBody('');
        $this->assertSame('', $this->context->getBody());

        // Test setting HTML body
        $htmlBody = '<h1>Welcome</h1><p>Thank you for joining us!</p>';
        $this->context->setBody($htmlBody);
        $this->assertSame($htmlBody, $this->context->getBody());
    }

    /**
     * @covers \Communication\Context\EmailContext::getCc
     * @covers \Communication\Context\EmailContext::setCc
     */
    public function testCcManagement(): void
    {
        // Test initial state
        $this->assertSame([], $this->context->getCc());

        // Test setting CC addresses from strings
        $ccAddresses = ['cc1@example.com', 'cc2@example.com'];
        $result = $this->context->setCc($ccAddresses);
        $this->assertSame($this->context, $result); // Test fluent interface

        $cc = $this->context->getCc();
        $this->assertCount(2, $cc);
        $this->assertInstanceOf(Address::class, $cc[0]);
        $this->assertSame('cc1@example.com', $cc[0]->getAddress());
        $this->assertSame('cc2@example.com', $cc[1]->getAddress());

        // Test setting CC addresses from Address objects
        $addressObjects = [
            new Address('addr1@example.com', 'CC Name 1'),
            new Address('addr2@example.com', 'CC Name 2'),
        ];
        $this->context->setCc($addressObjects);
        $cc = $this->context->getCc();
        $this->assertCount(2, $cc);
        $this->assertSame('addr1@example.com', $cc[0]->getAddress());
        $this->assertSame('CC Name 1', $cc[0]->getName());
        $this->assertSame('addr2@example.com', $cc[1]->getAddress());
        $this->assertSame('CC Name 2', $cc[1]->getName());

        // Test setting CC addresses from mixed array
        $recipient = (new Recipient(['email']))
            ->setEmail('recipient@example.com')
            ->setName('Recipient Name');
        $mixedCc = [
            'string@example.com',
            new Address('address@example.com', 'Address Name'),
            ['email' => 'array@example.com', 'name' => 'Array Name'],
            $recipient,
        ];
        $this->context->setCc($mixedCc);
        $cc = $this->context->getCc();
        $this->assertCount(4, $cc);

        $this->assertSame('string@example.com', $cc[0]->getAddress());
        $this->assertSame('address@example.com', $cc[1]->getAddress());
        $this->assertSame('Address Name', $cc[1]->getName());
        $this->assertSame('array@example.com', $cc[2]->getAddress());
        $this->assertSame('Array Name', $cc[2]->getName());
        $this->assertSame('recipient@example.com', $cc[3]->getAddress());
        $this->assertSame('Recipient Name', $cc[3]->getName());

        // Test setting empty CC
        $this->context->setCc([]);
        $this->assertSame([], $this->context->getCc());
    }

    /**
     * @covers \Communication\Context\EmailContext::getRecipientAddresses
     */
    public function testGetRecipientAddresses(): void
    {
        // Test with no recipients
        $this->assertSame([], $this->context->getRecipientAddresses());

        // Set some recipients
        $recipients = [
            'user1@example.com',
            new Address('user2@example.com', 'User 2'),
            ['email' => 'user3@example.com', 'name' => 'User 3'],
        ];
        $this->context->setRecipients($recipients);

        $recipientAddresses = $this->context->getRecipientAddresses();
        $this->assertCount(3, $recipientAddresses);
        $this->assertNotEmpty($recipientAddresses);
        $this->assertInstanceOf(Address::class, $recipientAddresses[0]);
        $this->assertSame('user1@example.com', $recipientAddresses[0]->getAddress());
        $this->assertSame('user2@example.com', $recipientAddresses[1]->getAddress());
        $this->assertSame('User 2', $recipientAddresses[1]->getName());
        $this->assertSame('user3@example.com', $recipientAddresses[2]->getAddress());
        $this->assertSame('User 3', $recipientAddresses[2]->getName());
    }

    /**
     * Test that EmailContext inherits all functionality from AbstractCommunicationContext
     */
    public function testInheritedFunctionality(): void
    {
        // Test body context functionality
        $this->context->addBodyContext('name', 'John Doe');
        $this->context->addBodyContext('email', 'john@example.com');
        $this->assertSame([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ], $this->context->getBodyContext());

        // Test from address functionality
        $this->context->setFrom('sender@example.com');
        $fromAddress = $this->context->getFrom();
        $this->assertInstanceOf(Address::class, $fromAddress);
        $this->assertSame('sender@example.com', $fromAddress->getAddress());

        // Test subject functionality
        $this->context->setSubject('Test Email Subject');
        $this->assertSame('Test Email Subject', $this->context->getSubject());

        // Test template functionality
        $this->context->setHtmlTemplate('welcome_email');
        $this->context->setTextTemplate('welcome_email_text');
        $this->assertSame('welcome_email', $this->context->getHtmlTemplate());
        $this->assertSame('welcome_email_text', $this->context->getTextTemplate());

        // Test BCC functionality
        $this->context->setBcc(['bcc@example.com']);
        $bcc = $this->context->getBcc();
        $this->assertCount(1, $bcc);
        $this->assertSame('bcc@example.com', $bcc[0]->getAddress());

        // Test reply-to functionality
        $this->context->setReplyTo(['reply@example.com']);
        $replyTo = $this->context->getReplyTo();
        $this->assertCount(1, $replyTo);
        $this->assertSame('reply@example.com', $replyTo[0]->getAddress());
    }

    /**
     * Test method chaining with EmailContext-specific methods
     */
    public function testMethodChaining(): void
    {
        $result = $this->context
            ->setBody('Email body content')
            ->setCc(['cc@example.com'])
            ->setSubject('Chained Subject')
            ->setFrom('sender@example.com')
            ->addBodyContext('key', 'value');

        $this->assertSame($this->context, $result);
        $this->assertSame('Email body content', $this->context->getBody());
        $this->assertCount(1, $this->context->getCc());
        $this->assertSame('Chained Subject', $this->context->getSubject());
        $this->assertSame('sender@example.com', $this->context->getFrom()->getAddress());
        $this->assertSame(['key' => 'value'], $this->context->getBodyContext());
    }

    /**
     * Test complete email context setup
     */
    public function testCompleteEmailSetup(): void
    {
        $this->context
            ->setFrom(['email' => 'sender@company.com', 'name' => 'Company Name'])
            ->setRecipients([
                'user1@example.com',
                ['email' => 'user2@example.com', 'name' => 'User Two'],
            ])
            ->setCc(['manager@company.com'])
            ->setBcc(['audit@company.com'])
            ->setReplyTo(['support@company.com'])
            ->setSubject('Welcome to Our Service')
            ->setBody('<h1>Welcome!</h1><p>Thank you for joining us.</p>')
            ->setHtmlTemplate('welcome_email')
            ->setTextTemplate('welcome_email_text')
            ->setBodyContext([
                'userName' => 'John Doe',
                'activationLink' => 'https://example.com/activate/123',
            ]);

        // Verify all properties are set correctly
        $this->assertSame('sender@company.com', $this->context->getFrom()->getAddress());
        $this->assertSame('Company Name', $this->context->getFrom()->getName());

        $recipients = $this->context->getRecipients();
        $this->assertCount(2, $recipients);
        $this->assertSame('user1@example.com', $recipients[0]->getAddress());
        $this->assertSame('user2@example.com', $recipients[1]->getAddress());
        $this->assertSame('User Two', $recipients[1]->getName());

        $cc = $this->context->getCc();
        $this->assertCount(1, $cc);
        $this->assertSame('manager@company.com', $cc[0]->getAddress());

        $bcc = $this->context->getBcc();
        $this->assertCount(1, $bcc);
        $this->assertSame('audit@company.com', $bcc[0]->getAddress());

        $replyTo = $this->context->getReplyTo();
        $this->assertCount(1, $replyTo);
        $this->assertSame('support@company.com', $replyTo[0]->getAddress());

        $this->assertSame('Welcome to Our Service', $this->context->getSubject());
        $this->assertSame('<h1>Welcome!</h1><p>Thank you for joining us.</p>', $this->context->getBody());
        $this->assertSame('welcome_email', $this->context->getHtmlTemplate());
        $this->assertSame('welcome_email_text', $this->context->getTextTemplate());

        $expectedContext = [
            'userName' => 'John Doe',
            'activationLink' => 'https://example.com/activate/123',
        ];
        $this->assertSame($expectedContext, $this->context->getBodyContext());
    }
}
