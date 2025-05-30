<?php

declare(strict_types=1);

namespace Communication\Examples;

use Communication\Entity\Communication;
use Communication\Context\CommunicationContext;
use Communication\Context\EmailContext;
use Communication\Interactor\SendCommunication;
use Communication\Recipient;

/**
 * This example demonstrates how to use the generic communication template
 * that was created by the GenericCommunicationSeed.
 */
class GenericCommunicationExample
{
    public function sendGenericEmail(
        SendCommunication $sender,
        string $recipientEmail,
        string $recipientName,
        string $body
    ): void {
        // Create a new Communication with the generic.email definition ID
        $communication = new Communication('generic.email');

        // Create a recipient
        $recipient = (new Recipient())
            ->setEmail($recipientEmail)
            ->setName($recipientName);

        // Add the recipient to the communication
        $communication->addRecipient($recipient);

        // Set the context data for the email channel
        $emailContext = new EmailContext(/* email message factory would be injected */);
        $emailContext->setSubject("Generic Email");
        $emailContext->setBodyContext([
            'body' => $body,
            'additionalData' => [
                'timestamp' => date('Y-m-d H:i:s'),
                'sender' => 'System'
            ]
        ]);

        // Create a communication context with the email context
        $context = new CommunicationContext(['email' => $emailContext]);

        // Set the context on the communication
        $communication = new Communication('generic.email', $context);

        // Add the recipient to the communication
        $communication->addRecipient($recipient);

        // Send the communication
        $sender->send($communication);
    }

    public function sendWelcomeEmail(
        SendCommunication $sender,
        string $recipientEmail,
        string $recipientName
    ): void {
        // Create the welcome message body
        $body = <<<HTML
<h1>Welcome, {$recipientName}!</h1>
<p>Thank you for joining our platform. We're excited to have you on board.</p>
<p>Here are a few things you can do to get started:</p>
<ul>
    <li>Complete your profile</li>
    <li>Explore our features</li>
    <li>Connect with other users</li>
</ul>
<p>If you have any questions, please don't hesitate to contact our support team.</p>
HTML;

        // Send the welcome email using the generic communication
        $this->sendGenericEmail($sender, $recipientEmail, $recipientName, $body);
    }

    public function sendPasswordResetEmail(
        SendCommunication $sender,
        string $recipientEmail,
        string $recipientName,
        string $resetToken
    ): void {
        // Create the password reset message body
        $body = <<<HTML
<h1>Password Reset Request</h1>
<p>Hello {$recipientName},</p>
<p>We received a request to reset your password. If you didn't make this request, you can ignore this email.</p>
<p>To reset your password, click the link below:</p>
<p><a href="https://example.com/reset-password?token={$resetToken}">Reset Password</a></p>
<p>This link will expire in 24 hours.</p>
HTML;

        // Send the password reset email using the generic communication
        $this->sendGenericEmail($sender, $recipientEmail, $recipientName, $body);
    }
}
