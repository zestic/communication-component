<?php

declare(strict_types=1);

namespace Tests\Unit\Communication\Entity;

use Communication\Entity\CommunicationSettings;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mime\Address;

/**
 * @covers \Communication\Entity\CommunicationSettings
 */
class CommunicationSettingsTest extends TestCase
{
    private CommunicationSettings $settings;

    protected function setUp(): void
    {
        $this->settings = new CommunicationSettings(
            new Address('initial@example.com', 'Initial User')
        );
    }

    /**
     * @covers \Communication\Entity\CommunicationSettings::getFromAddress
     */
    public function testGetFromAddress(): void
    {
        $fromAddress = $this->settings->getFromAddress();

        $this->assertInstanceOf(Address::class, $fromAddress);
        $this->assertEquals('initial@example.com', $fromAddress->getAddress());
        $this->assertEquals('Initial User', $fromAddress->getName());
    }

    /**
     * @covers \Communication\Entity\CommunicationSettings::setFromAddress
     */
    public function testSetFromAddressWithAddressInstance(): void
    {
        $newAddress = new Address('new@example.com', 'New User');

        $result = $this->settings->setFromAddress($newAddress);

        $this->assertSame($this->settings, $result); // Test fluent interface
        $this->assertSame($newAddress, $this->settings->getFromAddress());
        $this->assertEquals('new@example.com', $this->settings->getFromAddress()->getAddress());
        $this->assertEquals('New User', $this->settings->getFromAddress()->getName());
    }

    /**
     * @covers \Communication\Entity\CommunicationSettings::setFromAddress
     */
    public function testSetFromAddressWithStringEmail(): void
    {
        $result = $this->settings->setFromAddress('string@example.com');

        $this->assertSame($this->settings, $result); // Test fluent interface
        $fromAddress = $this->settings->getFromAddress();
        $this->assertInstanceOf(Address::class, $fromAddress);
        $this->assertEquals('string@example.com', $fromAddress->getAddress());
        $this->assertEquals('', $fromAddress->getName()); // Should be empty string
    }

    /**
     * @covers \Communication\Entity\CommunicationSettings::setFromAddress
     */
    public function testSetFromAddressWithArrayEmailAndName(): void
    {
        $addressData = [
            'email' => 'array@example.com',
            'name' => 'Array User'
        ];

        $result = $this->settings->setFromAddress($addressData);

        $this->assertSame($this->settings, $result); // Test fluent interface
        $fromAddress = $this->settings->getFromAddress();
        $this->assertInstanceOf(Address::class, $fromAddress);
        $this->assertEquals('array@example.com', $fromAddress->getAddress());
        $this->assertEquals('Array User', $fromAddress->getName());
    }

    /**
     * @covers \Communication\Entity\CommunicationSettings::setFromAddress
     */
    public function testSetFromAddressWithArrayEmailOnly(): void
    {
        $addressData = ['email' => 'arrayonly@example.com'];

        $result = $this->settings->setFromAddress($addressData);

        $this->assertSame($this->settings, $result); // Test fluent interface
        $fromAddress = $this->settings->getFromAddress();
        $this->assertInstanceOf(Address::class, $fromAddress);
        $this->assertEquals('arrayonly@example.com', $fromAddress->getAddress());
        $this->assertEquals('', $fromAddress->getName()); // Should default to empty string
    }

    /**
     * @covers \Communication\Entity\CommunicationSettings::setFromAddress
     */
    public function testSetFromAddressWithArrayMissingEmailKey(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Array must contain "email" key');

        $this->settings->setFromAddress(['name' => 'No Email']);
    }

    /**
     * @covers \Communication\Entity\CommunicationSettings::setFromAddress
     */
    public function testSetFromAddressWithArrayEmptyEmailKey(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Array must contain "email" key');

        $this->settings->setFromAddress(['email' => null, 'name' => 'Null Email']);
    }

    /**
     * @covers \Communication\Entity\CommunicationSettings::setFromAddress
     */
    public function testSetFromAddressWithInvalidType(): void
    {
        $this->expectException(\TypeError::class);

        $this->settings->setFromAddress(123); // Invalid type
    }

    /**
     * @covers \Communication\Entity\CommunicationSettings::setFromAddress
     */
    public function testSetFromAddressWithEmptyArray(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Array must contain "email" key');

        $this->settings->setFromAddress([]);
    }

    /**
     * @covers \Communication\Entity\CommunicationSettings::setFromAddress
     */
    public function testSetFromAddressWithObject(): void
    {
        $this->expectException(\TypeError::class);

        $this->settings->setFromAddress(new \stdClass());
    }

    /**
     * @covers \Communication\Entity\CommunicationSettings::setFromAddress
     */
    public function testSetFromAddressWithNull(): void
    {
        $this->expectException(\TypeError::class);

        $this->settings->setFromAddress(null);
    }

    /**
     * @covers \Communication\Entity\CommunicationSettings::setFromAddress
     */
    public function testSetFromAddressWithArrayExtraKeys(): void
    {
        $addressData = [
            'email' => 'extra@example.com',
            'name' => 'Extra User',
            'extra' => 'ignored',
            'another' => 'also ignored'
        ];

        $result = $this->settings->setFromAddress($addressData);

        $this->assertSame($this->settings, $result);
        $fromAddress = $this->settings->getFromAddress();
        $this->assertEquals('extra@example.com', $fromAddress->getAddress());
        $this->assertEquals('Extra User', $fromAddress->getName());
        // Extra keys should be ignored
    }

    /**
     * @covers \Communication\Entity\CommunicationSettings::setFromAddress
     */
    public function testSetFromAddressWithEmptyStringName(): void
    {
        $addressData = [
            'email' => 'emptyname@example.com',
            'name' => ''
        ];

        $result = $this->settings->setFromAddress($addressData);

        $this->assertSame($this->settings, $result);
        $fromAddress = $this->settings->getFromAddress();
        $this->assertEquals('emptyname@example.com', $fromAddress->getAddress());
        $this->assertEquals('', $fromAddress->getName());
    }
}
