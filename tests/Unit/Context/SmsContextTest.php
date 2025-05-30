<?php

declare(strict_types=1);

namespace Tests\Unit\Context;

use Communication\Context\SmsContext;
use Communication\Entity\Recipient;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mime\Address;

/**
 * @covers \Communication\Context\SmsContext
 */
class SmsContextTest extends TestCase
{
    private SmsContext $context;

    protected function setUp(): void
    {
        $this->context = new SmsContext();
    }

    /**
     * Test that SmsContext inherits all functionality from AbstractCommunicationContext
     */
    public function testInheritedBodyContextFunctionality(): void
    {
        // Test initial state
        $this->assertSame([], $this->context->getBodyContext());

        // Test adding individual context items
        $result = $this->context->addBodyContext('phoneNumber', '+1234567890');
        $this->assertSame($this->context, $result); // Test fluent interface
        $this->assertSame(['phoneNumber' => '+1234567890'], $this->context->getBodyContext());

        $this->context->addBodyContext('message', 'Your verification code is 123456');
        $this->assertSame([
            'phoneNumber' => '+1234567890',
            'message' => 'Your verification code is 123456',
        ], $this->context->getBodyContext());

        // Test setting entire context array
        $newContext = ['code' => '123456', 'expiryMinutes' => 5];
        $result = $this->context->setBodyContext($newContext);
        $this->assertSame($this->context, $result); // Test fluent interface
        $this->assertSame($newContext, $this->context->getBodyContext());
    }

    /**
     * Test from address functionality for SMS
     */
    public function testFromAddressFunctionality(): void
    {
        // Test initial state
        $this->assertNull($this->context->getFrom());

        // Test setting from string (using email-like format for testing)
        $result = $this->context->setFrom('sms@example.com');
        $this->assertSame($this->context, $result); // Test fluent interface
        $fromAddress = $this->context->getFrom();
        $this->assertInstanceOf(Address::class, $fromAddress);
        $this->assertSame('sms@example.com', $fromAddress->getAddress());

        // Test setting from Address object
        $address = new Address('sms-service@example.com', 'SMS Service');
        $this->context->setFrom($address);
        $this->assertSame($address, $this->context->getFrom());

        // Test setting from array
        $this->context->setFrom(['email' => 'company-sms@example.com', 'name' => 'Company SMS']);
        $fromAddress = $this->context->getFrom();
        $this->assertInstanceOf(Address::class, $fromAddress);
        $this->assertSame('company-sms@example.com', $fromAddress->getAddress());
        $this->assertSame('Company SMS', $fromAddress->getName());

        // Test setting from Recipient entity with phone number
        $recipient = (new Recipient(['sms']))
            ->setPhone('+2222222222')
            ->setName('Service Provider');
        $this->context->setFrom($recipient);
        $fromAddress = $this->context->getFrom();
        $this->assertInstanceOf(Address::class, $fromAddress);
        $this->assertSame('plus2222222222@sms.internal', $fromAddress->getAddress());
        $this->assertSame('Service Provider', $fromAddress->getName());

        // Test setting null
        $this->context->setFrom(null);
        $this->assertNull($this->context->getFrom());
    }

    /**
     * Test recipients functionality for SMS
     */
    public function testRecipientsFunctionality(): void
    {
        // Test initial state
        $this->assertSame([], $this->context->getRecipients());

        // Test setting recipients from array of email-like addresses (for testing)
        $recipients = ['user1@sms.example.com', 'user2@sms.example.com'];
        $result = $this->context->setRecipients($recipients);
        $this->assertSame($this->context, $result); // Test fluent interface

        $recipientAddresses = $this->context->getRecipients();
        $this->assertCount(2, $recipientAddresses);
        $this->assertInstanceOf(Address::class, $recipientAddresses[0]);
        $this->assertSame('user1@sms.example.com', $recipientAddresses[0]->getAddress());
        $this->assertSame('user2@sms.example.com', $recipientAddresses[1]->getAddress());

        // Test setting recipients from mixed array
        $smsRecipient = (new Recipient(['sms']))
            ->setPhone('+4444444444')
            ->setName('Recipient Name');
        $mixedRecipients = [
            'user3@sms.example.com',
            new Address('user4@sms.example.com', 'User Name'),
            ['email' => 'user5@sms.example.com', 'name' => 'Contact Name'],
            $smsRecipient,
        ];
        $this->context->setRecipients($mixedRecipients);
        $recipientAddresses = $this->context->getRecipients();
        $this->assertCount(4, $recipientAddresses);

        // @phpstan-ignore-next-line
        $this->assertSame('user3@sms.example.com', $recipientAddresses[0]->getAddress());
        // @phpstan-ignore-next-line
        $this->assertSame('user4@sms.example.com', $recipientAddresses[1]->getAddress());
        // @phpstan-ignore-next-line
        $this->assertSame('User Name', $recipientAddresses[1]->getName());
        // @phpstan-ignore-next-line
        $this->assertSame('user5@sms.example.com', $recipientAddresses[2]->getAddress());
        // @phpstan-ignore-next-line
        $this->assertSame('Contact Name', $recipientAddresses[2]->getName());
        // @phpstan-ignore-next-line
        $this->assertSame('plus4444444444@sms.internal', $recipientAddresses[3]->getAddress());
        // @phpstan-ignore-next-line
        $this->assertSame('Recipient Name', $recipientAddresses[3]->getName());
    }

    /**
     * Test subject functionality for SMS (though less common)
     */
    public function testSubjectFunctionality(): void
    {
        // Test initial state
        $this->assertSame('', $this->context->getSubject());

        // Test setting subject
        $result = $this->context->setSubject('Verification Code');
        $this->assertSame($this->context, $result); // Test fluent interface
        $this->assertSame('Verification Code', $this->context->getSubject());
    }

    /**
     * Test template functionality for SMS
     */
    public function testTemplateFunctionality(): void
    {
        // Test initial state
        $this->assertSame('', $this->context->getHtmlTemplate());
        $this->assertSame('', $this->context->getTextTemplate());

        // Test setting templates
        $result = $this->context->setTextTemplate('sms_verification');
        $this->assertSame($this->context, $result); // Test fluent interface
        $this->assertSame('sms_verification', $this->context->getTextTemplate());

        // HTML template is less common for SMS but should work
        $this->context->setHtmlTemplate('sms_rich_content');
        $this->assertSame('sms_rich_content', $this->context->getHtmlTemplate());

        // Test setting null
        $this->context->setTextTemplate(null);
        $this->context->setHtmlTemplate(null);
        $this->assertNull($this->context->getTextTemplate());
        $this->assertNull($this->context->getHtmlTemplate());
    }

    /**
     * Test BCC functionality for SMS (typically not used but inherited)
     */
    public function testBccFunctionality(): void
    {
        // Test initial state
        $this->assertSame([], $this->context->getBcc());

        // Test setting BCC (though not typical for SMS, using email-like format)
        $bccAddresses = ['bcc1@sms.example.com', 'bcc2@sms.example.com'];
        $result = $this->context->setBcc($bccAddresses);
        $this->assertSame($this->context, $result); // Test fluent interface

        $bcc = $this->context->getBcc();
        $this->assertCount(2, $bcc);
        $this->assertInstanceOf(Address::class, $bcc[0]);
        $this->assertSame('bcc1@sms.example.com', $bcc[0]->getAddress());
        $this->assertSame('bcc2@sms.example.com', $bcc[1]->getAddress());
    }

    /**
     * Test reply-to functionality for SMS (typically not used but inherited)
     */
    public function testReplyToFunctionality(): void
    {
        // Test initial state (should return empty array when no from address)
        $this->assertSame([], $this->context->getReplyTo());

        // Set a from address
        $fromAddress = new Address('sms@example.com', 'SMS Service');
        $this->context->setFrom($fromAddress);
        $this->assertSame([$fromAddress], $this->context->getReplyTo());

        // Test setting reply-to addresses
        $replyToAddresses = ['reply@sms.example.com'];
        $result = $this->context->setReplyTo($replyToAddresses);
        $this->assertSame($this->context, $result); // Test fluent interface

        $replyTo = $this->context->getReplyTo();
        $this->assertCount(1, $replyTo);
        $this->assertInstanceOf(Address::class, $replyTo[0]);
        $this->assertSame('reply@sms.example.com', $replyTo[0]->getAddress());
    }

    /**
     * Test method chaining with SMS context
     */
    public function testMethodChaining(): void
    {
        $result = $this->context
            ->setFrom('sms@example.com')
            ->setRecipients(['user@sms.example.com'])
            ->setSubject('SMS Alert')
            ->setTextTemplate('alert_sms')
            ->addBodyContext('alertType', 'security')
            ->addBodyContext('timestamp', '2025-01-01 12:00:00');

        $this->assertSame($this->context, $result);
        // @phpstan-ignore-next-line
        $this->assertSame('sms@example.com', $this->context->getFrom()->getAddress());
        $this->assertCount(1, $this->context->getRecipients());
        // @phpstan-ignore-next-line
        $this->assertSame('user@sms.example.com', $this->context->getRecipients()[0]->getAddress());
        $this->assertSame('SMS Alert', $this->context->getSubject());
        $this->assertSame('alert_sms', $this->context->getTextTemplate());
        $this->assertSame([
            'alertType' => 'security',
            'timestamp' => '2025-01-01 12:00:00',
        ], $this->context->getBodyContext());
    }

    /**
     * Test complete SMS context setup
     */
    public function testCompleteSmsSetup(): void
    {
        $this->context
            ->setFrom(['email' => 'security@sms.example.com', 'name' => 'Security Service'])
            ->setRecipients([
                'user1@sms.example.com',
                ['email' => 'user2@sms.example.com', 'name' => 'John Doe'],
            ])
            ->setSubject('Security Alert')
            ->setTextTemplate('security_alert_sms')
            ->setBodyContext([
                'alertType' => 'login_attempt',
                'location' => 'New York, NY',
                'timestamp' => '2025-01-01 12:00:00',
                'actionRequired' => true,
            ]);

        // Verify all properties are set correctly
        // @phpstan-ignore-next-line
        $this->assertSame('security@sms.example.com', $this->context->getFrom()->getAddress());
        // @phpstan-ignore-next-line
        $this->assertSame('Security Service', $this->context->getFrom()->getName());

        $recipients = $this->context->getRecipients();
        $this->assertCount(2, $recipients);
        // @phpstan-ignore-next-line
        $this->assertSame('user1@sms.example.com', $recipients[0]->getAddress());
        // @phpstan-ignore-next-line
        $this->assertSame('user2@sms.example.com', $recipients[1]->getAddress());
        // @phpstan-ignore-next-line
        $this->assertSame('John Doe', $recipients[1]->getName());

        $this->assertSame('Security Alert', $this->context->getSubject());
        $this->assertSame('security_alert_sms', $this->context->getTextTemplate());

        $expectedContext = [
            'alertType' => 'login_attempt',
            'location' => 'New York, NY',
            'timestamp' => '2025-01-01 12:00:00',
            'actionRequired' => true,
        ];
        $this->assertSame($expectedContext, $this->context->getBodyContext());
    }

    /**
     * Test that SmsContext implements CommunicationContextInterface
     */
    public function testImplementsInterface(): void
    {
        $this->assertInstanceOf(\Communication\Context\CommunicationContextInterface::class, $this->context);
    }

    /**
     * Test SMS-specific use case: verification code
     */
    public function testVerificationCodeUseCase(): void
    {
        $this->context
            ->setFrom('service@sms.example.com')
            ->setRecipients(['user@sms.example.com'])
            ->setTextTemplate('verification_code')
            ->addBodyContext('code', '123456')
            ->addBodyContext('expiryMinutes', 5)
            ->addBodyContext('serviceName', 'MyApp');

        // @phpstan-ignore-next-line
        $this->assertSame('service@sms.example.com', $this->context->getFrom()->getAddress());
        // @phpstan-ignore-next-line
        $this->assertSame('user@sms.example.com', $this->context->getRecipients()[0]->getAddress());
        $this->assertSame('verification_code', $this->context->getTextTemplate());

        $expectedContext = [
            'code' => '123456',
            'expiryMinutes' => 5,
            'serviceName' => 'MyApp',
        ];
        $this->assertSame($expectedContext, $this->context->getBodyContext());
    }
}
