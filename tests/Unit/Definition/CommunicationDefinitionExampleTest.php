<?php

declare(strict_types=1);

namespace Tests\Unit\Communication\Definition;

use Communication\Definition\CommunicationDefinition;
use Communication\Definition\EmailChannelDefinition;
use Communication\Definition\Exception\InvalidContextException;
use Communication\Definition\MobileChannelDefinition;
use PHPUnit\Framework\TestCase;

class CommunicationDefinitionExampleTest extends TestCase
{
    public function testParcelArrivalNotification(): void
    {
        // Create a communication definition for parcel arrival
        $definition = new CommunicationDefinition('parcel.arrival', 'Parcel Arrival Notification');

        // Define email channel with schema validation
        $emailDef = new EmailChannelDefinition(
            'emails/parcel-arrival.html.twig',
            [
                'type' => 'object',
                'required' => ['trackingNumber', 'deliveryDate', 'location'],
                'properties' => [
                    'trackingNumber' => ['type' => 'string'],
                    'deliveryDate' => ['type' => 'string', 'format' => 'date-time'],
                    'location' => ['type' => 'string'],
                    'notes' => ['type' => 'string'],
                ],
            ],
            [
                'type' => 'object',
                'required' => ['trackingNumber'],
                'properties' => [
                    'trackingNumber' => ['type' => 'string'],
                ],
            ],
            'notifications@mailforwarding.example.com',
            'support@mailforwarding.example.com'
        );

        // Define mobile notification with schema validation
        $mobileDef = new MobileChannelDefinition(
            'mobile/parcel-arrival.json',
            [
                'type' => 'object',
                'required' => ['trackingNumber', 'location'],
                'properties' => [
                    'trackingNumber' => ['type' => 'string'],
                    'location' => ['type' => 'string'],
                ],
            ],
            [
                'type' => 'object',
                'required' => ['title'],
                'properties' => [
                    'title' => ['type' => 'string'],
                ],
            ],
            1, // High priority
            false // No auth required
        );

        $definition->addChannelDefinition($emailDef)
                  ->addChannelDefinition($mobileDef);

        // Test valid context for email
        $validEmailContext = [
            'trackingNumber' => 'ABC123',
            'deliveryDate' => '2025-05-16T12:00:00Z',
            'location' => 'Locker #123',
            'notes' => 'Large package',
        ];
        $emailDef->validateContext($validEmailContext);

        // Test valid subject for email
        $validEmailSubject = ['trackingNumber' => 'ABC123'];
        $emailDef->validateSubject($validEmailSubject);

        // Test valid context for mobile
        $validMobileContext = [
            'trackingNumber' => 'ABC123',
            'location' => 'Locker #123',
        ];
        $mobileDef->validateContext($validMobileContext);

        // Test valid subject for mobile
        $validMobileSubject = ['title' => 'New Parcel Arrived'];
        $mobileDef->validateSubject($validMobileSubject);

        // Test invalid context (should throw exception)
        $this->expectException(InvalidContextException::class);
        $invalidEmailContext = [
            'trackingNumber' => 'ABC123',
            // Missing required deliveryDate
            'location' => 'Locker #123',
        ];
        $emailDef->validateContext($invalidEmailContext);
    }
}
