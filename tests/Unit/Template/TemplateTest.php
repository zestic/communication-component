<?php

declare(strict_types=1);

namespace Tests\Unit\Template;

use Communication\Template\Template;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Communication\Template\Template
 */
class TemplateTest extends TestCase
{
    /**
     * @covers \Communication\Template\Template::__construct
     * @covers \Communication\Template\Template::getId
     * @covers \Communication\Template\Template::getName
     * @covers \Communication\Template\Template::getChannel
     * @covers \Communication\Template\Template::getContent
     * @covers \Communication\Template\Template::getContentType
     * @covers \Communication\Template\Template::getSubject
     * @covers \Communication\Template\Template::getMetadata
     * @covers \Communication\Template\Template::getCreatedAt
     * @covers \Communication\Template\Template::getUpdatedAt
     */
    public function testTemplateCreation(): void
    {
        $id = 'template123';
        $name = 'welcome_email';
        $channel = 'email';
        $content = 'Hello {{ name }}';
        $contentType = 'text/html';
        $subject = 'Welcome!';
        $metadata = ['category' => 'onboarding'];
        $createdAt = new DateTimeImmutable('2025-01-01 12:00:00');
        $updatedAt = new DateTimeImmutable('2025-01-02 12:00:00');

        $template = new Template(
            $id,
            $name,
            $channel,
            $content,
            $contentType,
            $subject,
            $metadata,
            $createdAt,
            $updatedAt
        );

        $this->assertSame($id, $template->getId());
        $this->assertSame($name, $template->getName());
        $this->assertSame($channel, $template->getChannel());
        $this->assertSame($content, $template->getContent());
        $this->assertSame($contentType, $template->getContentType());
        $this->assertSame($subject, $template->getSubject());
        $this->assertSame($metadata, $template->getMetadata());
        $this->assertSame($createdAt, $template->getCreatedAt());
        $this->assertSame($updatedAt, $template->getUpdatedAt());
    }

    /**
     * @covers \Communication\Template\Template::__construct
     * @covers \Communication\Template\Template::getContentType
     * @covers \Communication\Template\Template::getSubject
     * @covers \Communication\Template\Template::getMetadata
     */
    public function testTemplateCreationWithDefaults(): void
    {
        $template = new Template(
            'template123',
            'welcome_email',
            'email',
            'Hello {{ name }}'
        );

        $this->assertSame('text/html', $template->getContentType());
        $this->assertNull($template->getSubject());
        $this->assertSame([], $template->getMetadata());
        $this->assertInstanceOf(DateTimeImmutable::class, $template->getCreatedAt());
        $this->assertInstanceOf(DateTimeImmutable::class, $template->getUpdatedAt());
        $this->assertEquals(
            $template->getCreatedAt()->getTimestamp(),
            $template->getUpdatedAt()->getTimestamp()
        );
    }
}
