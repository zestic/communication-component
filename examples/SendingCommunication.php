<?php

declare(strict_types=1);

namespace Communication\Examples;

use Communication\Entity\Communication;
use Communication\Context\CommunicationContext;
use Communication\Context\EmailContext;
use Communication\Interactor\SendCommunication;
use Communication\Recipient;

/**
 * This example demonstrates how to use the new Communication class
 * with configurable communications and templates stored in a database.
 */
class SendingCommunicationExample
{
    public function sendParcelArrivalNotification(
        SendCommunication $sender,
        string $recipientEmail,
        string $trackingNumber,
        string $deliveryDate,
        string $location
    ): void {
        // Create a new Communication with the definition ID
        $communication = new Communication('parcel.arrival');

        // Create a recipient
        $recipient = (new Recipient())
            ->setEmail($recipientEmail)
            ->setName('Customer');

        // Add the recipient to the communication
        $communication->addRecipient($recipient);

        // Set the context data for the email channel
        $emailContext = new EmailContext(/* email message factory would be injected */);
        $emailContext->setSubject("Your parcel $trackingNumber has arrived");
        $emailContext->setBodyContext([
            'trackingNumber' => $trackingNumber,
            'deliveryDate' => $deliveryDate,
            'location' => $location,
            'notes' => 'Please collect your parcel within 3 days.'
        ]);

        // Create a communication context with the email context
        $context = new CommunicationContext(['email' => $emailContext]);

        // Set the context on the communication
        $communication = new Communication('parcel.arrival', $context);

        // Add the recipient to the communication
        $communication->addRecipient($recipient);

        // Send the communication
        $sender->send($communication);
    }

    public function sendSubscriptionRenewalNotification(
        SendCommunication $sender,
        string $recipientEmail,
        string $subscriptionId,
        string $expiryDate,
        float $renewalAmount,
        ?string $discountCode = null
    ): void {
        // Create a new Communication with the definition ID
        $communication = new Communication('subscription.renewal');

        // Create a recipient
        $recipient = (new Recipient())
            ->setEmail($recipientEmail)
            ->setName('Subscriber');

        // Add the recipient to the communication
        $communication->addRecipient($recipient);

        // Set the context data for the email channel
        $emailContext = new EmailContext(/* email message factory would be injected */);
        $emailContext->setSubject("Your subscription is about to expire");

        $contextData = [
            'subscriptionId' => $subscriptionId,
            'expiryDate' => $expiryDate,
            'renewalAmount' => $renewalAmount,
        ];

        if ($discountCode) {
            $contextData['discountCode'] = $discountCode;
        }

        $emailContext->setBodyContext($contextData);

        // Create a communication context with the email context
        $context = new CommunicationContext(['email' => $emailContext]);

        // Set the context on the communication
        $communication = new Communication('subscription.renewal', $context);

        // Add the recipient to the communication
        $communication->addRecipient($recipient);

        // Send the communication
        $sender->send($communication);
    }
}
