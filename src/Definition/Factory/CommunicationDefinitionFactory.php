<?php

declare(strict_types=1);

namespace Communication\Definition\Factory;

use Communication\Definition\CommunicationDefinition;
use Communication\Definition\EmailChannelDefinition;
use Communication\Definition\MobileChannelDefinition;

class CommunicationDefinitionFactory
{
    public function createParcelArrivalDefinition(): CommunicationDefinition
    {
        $definition = new CommunicationDefinition('parcel.arrival', 'Parcel Arrival Notification');

        $emailDef = new EmailChannelDefinition(
            'emails/parcel-arrival.html.twig',
            [
                'type' => 'object',
                'required' => ['trackingNumber', 'deliveryDate', 'location'],
                'properties' => [
                    'trackingNumber' => ['type' => 'string'],
                    'deliveryDate' => ['type' => 'string', 'format' => 'date-time'],
                    'location' => ['type' => 'string'],
                    'notes' => ['type' => 'string']
                ]
            ],
            [
                'type' => 'object',
                'required' => ['trackingNumber'],
                'properties' => [
                    'trackingNumber' => ['type' => 'string']
                ]
            ],
            'notifications@mailforwarding.example.com',
            'support@mailforwarding.example.com'
        );

        $mobileDef = new MobileChannelDefinition(
            'mobile/parcel-arrival.json',
            [
                'type' => 'object',
                'required' => ['trackingNumber', 'location'],
                'properties' => [
                    'trackingNumber' => ['type' => 'string'],
                    'location' => ['type' => 'string']
                ]
            ],
            [
                'type' => 'object',
                'required' => ['title'],
                'properties' => [
                    'title' => ['type' => 'string']
                ]
            ],
            1, // High priority
            false // No auth required
        );

        return $definition
            ->addChannelDefinition($emailDef)
            ->addChannelDefinition($mobileDef);
    }

    public function createSubscriptionRenewalDefinition(): CommunicationDefinition
    {
        $definition = new CommunicationDefinition('subscription.renewal', 'Subscription Renewal Notice');

        $emailDef = new EmailChannelDefinition(
            'emails/subscription-renewal.html.twig',
            [
                'type' => 'object',
                'required' => ['subscriptionId', 'expiryDate', 'renewalAmount'],
                'properties' => [
                    'subscriptionId' => ['type' => 'string'],
                    'expiryDate' => ['type' => 'string', 'format' => 'date'],
                    'renewalAmount' => ['type' => 'number'],
                    'discountCode' => ['type' => 'string']
                ]
            ],
            [
                'type' => 'object',
                'required' => ['subscriptionId'],
                'properties' => [
                    'subscriptionId' => ['type' => 'string']
                ]
            ],
            'billing@mailforwarding.example.com'
        );

        return $definition->addChannelDefinition($emailDef);
    }
}
