<?php

declare(strict_types=1);

namespace Tests\Unit\Template;

use Communication\Template\Template;
use Communication\Template\TemplateRepositoryInterface;
use Communication\Template\TwigDatabaseLoader;
use DateTimeImmutable;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Twig\Error\LoaderError;

/**
 * @covers \Communication\Template\TwigDatabaseLoader
 * @uses \Communication\Template\Template
 */
class TwigDatabaseLoaderTest extends MockeryTestCase
{
    private TemplateRepositoryInterface|Mockery\MockInterface $repository;

    private TwigDatabaseLoader $loader;

    protected function setUp(): void
    {
        $this->repository = Mockery::mock(TemplateRepositoryInterface::class);
        $this->loader = new TwigDatabaseLoader($this->repository);
    }

    /**
     * @covers \Communication\Template\TwigDatabaseLoader::getSourceContext
     * @covers \Communication\Template\TwigDatabaseLoader::findTemplate
     * @covers \Communication\Template\TwigDatabaseLoader::updateDependencies
     */
    public function testGetSourceContext(): void
    {
        $template = new Template(
            'template123',
            'welcome',
            'email',
            '{% extends "base" %}{% block content %}Hello {{ name }}{% endblock %}',
            'text/html',
            'Welcome!',
            [],
            new DateTimeImmutable('2025-01-01 12:00:00'),
            new DateTimeImmutable('2025-01-01 12:00:00')
        );

        $this->repository->shouldReceive('findByNameAndChannel')
            ->once()
            ->with('welcome', 'email')
            ->andReturn($template);

        $source = $this->loader->getSourceContext('welcome:email');

        $this->assertSame('welcome:email', $source->getName());
        $this->assertSame($template->getContent(), $source->getCode());
    }

    /**
     * @covers \Communication\Template\TwigDatabaseLoader::getSourceContext
     * @covers \Communication\Template\TwigDatabaseLoader::findTemplate
     * @covers \Communication\Template\TwigDatabaseLoader::updateDependencies
     */
    public function testGetSourceContextWithInheritance(): void
    {
        $childTemplate = new Template(
            'template123',
            'welcome',
            'email',
            '{% extends "base" %}{% block content %}Hello {{ name }}{% endblock %}',
            'text/html',
            'Welcome!',
            [],
            new DateTimeImmutable('2025-01-01 12:00:00'),
            new DateTimeImmutable('2025-01-01 12:00:00')
        );

        $baseTemplate = new Template(
            'base123',
            'base',
            'email',
            '<!DOCTYPE html><html><body>{% block content %}{% endblock %}</body></html>',
            'text/html',
            null,
            [],
            new DateTimeImmutable('2025-01-01 12:00:00'),
            new DateTimeImmutable('2025-01-01 12:00:00')
        );

        $this->repository->shouldReceive('findByNameAndChannel')
            ->once()
            ->with('welcome', 'email')
            ->andReturn($childTemplate);

        $this->repository->shouldReceive('findByNameAndChannel')
            ->once()
            ->with('base', 'email')
            ->andReturn($baseTemplate);

        $source = $this->loader->getSourceContext('welcome:email');
        $this->assertSame('welcome:email', $source->getName());
        $this->assertSame($childTemplate->getContent(), $source->getCode());

        // Test that base template is also accessible
        $baseSource = $this->loader->getSourceContext('base:email');
        $this->assertSame('base:email', $baseSource->getName());
        $this->assertSame($baseTemplate->getContent(), $baseSource->getCode());
    }

    /**
     * @covers \Communication\Template\TwigDatabaseLoader::getSourceContext
     * @covers \Communication\Template\TwigDatabaseLoader::findTemplate
     */
    public function testTemplateNotFound(): void
    {
        $this->repository->shouldReceive('findByNameAndChannel')
            ->once()
            ->with('nonexistent', 'email')
            ->andReturnNull();

        $this->expectException(LoaderError::class);
        $this->expectExceptionMessage('Template "nonexistent:email" does not exist.');

        $this->loader->getSourceContext('nonexistent:email');
    }

    /**
     * @covers \Communication\Template\TwigDatabaseLoader::isFresh
     * @covers \Communication\Template\TwigDatabaseLoader::findTemplate
     */
    /**
     * @covers \Communication\Template\TwigDatabaseLoader::getCacheKey
     * @covers \Communication\Template\TwigDatabaseLoader::findTemplate
     */
    public function testGetCacheKey(): void
    {
        $template = new Template(
            'template123',
            'welcome',
            'email',
            'Hello {{ name }}',
            'text/html',
            'Welcome!',
            [],
            new DateTimeImmutable('2025-01-01 12:00:00'),
            new DateTimeImmutable('2025-01-01 12:00:00')
        );

        $this->repository->shouldReceive('findByNameAndChannel')
            ->once()
            ->with('welcome', 'email')
            ->andReturn($template);

        $cacheKey = $this->loader->getCacheKey('welcome:email');
        $this->assertStringContainsString('template123', $cacheKey);
        $this->assertStringContainsString('welcome', $cacheKey);
        $this->assertStringContainsString((string)$template->getUpdatedAt()->getTimestamp(), $cacheKey);
    }

    /**
     * @covers \Communication\Template\TwigDatabaseLoader::exists
     * @covers \Communication\Template\TwigDatabaseLoader::findTemplate
     */
    public function testExists(): void
    {
        $template = new Template(
            'template123',
            'welcome',
            'email',
            'Hello {{ name }}',
            'text/html',
            'Welcome!',
            [],
            new DateTimeImmutable('2025-01-01 12:00:00'),
            new DateTimeImmutable('2025-01-01 12:00:00')
        );

        $this->repository->shouldReceive('findByNameAndChannel')
            ->once()
            ->with('welcome', 'email')
            ->andReturn($template);

        $this->repository->shouldReceive('findByNameAndChannel')
            ->once()
            ->with('nonexistent', 'email')
            ->andReturnNull();

        $this->assertTrue($this->loader->exists('welcome:email'));
        $this->assertFalse($this->loader->exists('nonexistent:email'));
    }

    /**
     * @covers \Communication\Template\TwigDatabaseLoader::isFresh
     * @covers \Communication\Template\TwigDatabaseLoader::findTemplate
     */
    public function testIsFresh(): void
    {
        $template = new Template(
            'template123',
            'welcome',
            'email',
            'Hello {{ name }}',
            'text/html',
            'Welcome!',
            [],
            new DateTimeImmutable('2025-01-01 12:00:00'),
            new DateTimeImmutable('2025-01-01 12:00:00')
        );

        $this->repository->shouldReceive('findByNameAndChannel')
            ->once()
            ->with('welcome', 'email')
            ->andReturn($template);

        // Test with a time after the template's update
        $this->assertTrue($this->loader->isFresh('welcome:email', strtotime('2025-01-02 12:00:00')));

        // Test with a time before the template's update
        $this->assertFalse($this->loader->isFresh('welcome:email', strtotime('2024-12-31 12:00:00')));
    }
}
