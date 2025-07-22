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
            'welcome.html.twig',
            'email',
            '{% extends "base.html.twig" %}{% block content %}Hello {{ name }}{% endblock %}',
            'text/html',
            'Welcome!',
            [],
            new DateTimeImmutable('2025-01-01 12:00:00'),
            new DateTimeImmutable('2025-01-01 12:00:00')
        );

        $this->repository->shouldReceive('findByName')
            ->once()
            ->with('welcome.html.twig')
            ->andReturn($template);

        $source = $this->loader->getSourceContext('welcome.html.twig');

        $this->assertSame('welcome.html.twig', $source->getName());
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
            'welcome.html.twig',
            'email',
            '{% extends "base.html.twig" %}{% block content %}Hello {{ name }}{% endblock %}',
            'text/html',
            'Welcome!',
            [],
            new DateTimeImmutable('2025-01-01 12:00:00'),
            new DateTimeImmutable('2025-01-01 12:00:00')
        );

        $baseTemplate = new Template(
            'base123',
            'base.html.twig',
            'email',
            '<!DOCTYPE html><html><body>{% block content %}{% endblock %}</body></html>',
            'text/html',
            null,
            [],
            new DateTimeImmutable('2025-01-01 12:00:00'),
            new DateTimeImmutable('2025-01-01 12:00:00')
        );

        $this->repository->shouldReceive('findByName')
            ->once()
            ->with('welcome.html.twig')
            ->andReturn($childTemplate);

        $this->repository->shouldReceive('findByName')
            ->once()
            ->with('base.html.twig')
            ->andReturn($baseTemplate);

        $source = $this->loader->getSourceContext('welcome.html.twig');
        $this->assertSame('welcome.html.twig', $source->getName());
        $this->assertSame($childTemplate->getContent(), $source->getCode());

        // Test that base template is also accessible
        $baseSource = $this->loader->getSourceContext('base.html.twig');
        $this->assertSame('base.html.twig', $baseSource->getName());
        $this->assertSame($baseTemplate->getContent(), $baseSource->getCode());
    }

    /**
     * @covers \Communication\Template\TwigDatabaseLoader::getSourceContext
     * @covers \Communication\Template\TwigDatabaseLoader::findTemplate
     */
    public function testTemplateNotFound(): void
    {
        $this->repository->shouldReceive('findByName')
            ->once()
            ->with('nonexistent.html.twig')
            ->andReturnNull();

        $this->expectException(LoaderError::class);
        $this->expectExceptionMessage('Template "nonexistent.html.twig" does not exist.');

        $this->loader->getSourceContext('nonexistent.html.twig');
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
            'welcome.html.twig',
            'email',
            'Hello {{ name }}',
            'text/html',
            'Welcome!',
            [],
            new DateTimeImmutable('2025-01-01 12:00:00'),
            new DateTimeImmutable('2025-01-01 12:00:00')
        );

        $this->repository->shouldReceive('findByName')
            ->once()
            ->with('welcome.html.twig')
            ->andReturn($template);

        $cacheKey = $this->loader->getCacheKey('welcome.html.twig');
        $this->assertStringContainsString('template123', $cacheKey);
        $this->assertStringContainsString('welcome.html.twig', $cacheKey);
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
            'welcome.html.twig',
            'email',
            'Hello {{ name }}',
            'text/html',
            'Welcome!',
            [],
            new DateTimeImmutable('2025-01-01 12:00:00'),
            new DateTimeImmutable('2025-01-01 12:00:00')
        );

        $this->repository->shouldReceive('findByName')
            ->once()
            ->with('welcome.html.twig')
            ->andReturn($template);

        $this->repository->shouldReceive('findByName')
            ->once()
            ->with('nonexistent.html.twig')
            ->andReturnNull();

        $this->assertTrue($this->loader->exists('welcome.html.twig'));
        $this->assertFalse($this->loader->exists('nonexistent.html.twig'));
    }

    /**
     * @covers \Communication\Template\TwigDatabaseLoader::isFresh
     * @covers \Communication\Template\TwigDatabaseLoader::findTemplate
     */
    public function testIsFresh(): void
    {
        $template = new Template(
            'template123',
            'welcome.html.twig',
            'email',
            'Hello {{ name }}',
            'text/html',
            'Welcome!',
            [],
            new DateTimeImmutable('2025-01-01 12:00:00'),
            new DateTimeImmutable('2025-01-01 12:00:00')
        );

        $this->repository->shouldReceive('findByName')
            ->once()
            ->with('welcome.html.twig')
            ->andReturn($template);

        // Test with a time after the template's update
        $this->assertTrue($this->loader->isFresh('welcome.html.twig', strtotime('2025-01-02 12:00:00')));

        // Test with a time before the template's update
        $this->assertFalse($this->loader->isFresh('welcome.html.twig', strtotime('2024-12-31 12:00:00')));
    }

    /**
     * @covers \Communication\Template\TwigDatabaseLoader::getSourceContext
     * @covers \Communication\Template\TwigDatabaseLoader::findTemplate
     */
    public function testGetSourceContextWithNamespacedTemplate(): void
    {
        $template = new Template(
            'error404',
            '404.html.twig',
            'error',
            '<h1>404 Not Found</h1><p>The page you are looking for does not exist.</p>',
            'text/html',
            'Page Not Found',
            [],
            new DateTimeImmutable('2025-01-01 12:00:00'),
            new DateTimeImmutable('2025-01-01 12:00:00')
        );

        $this->repository->shouldReceive('findByNameAndChannel')
            ->once()
            ->with('404.html.twig', 'error')
            ->andReturn($template);

        $source = $this->loader->getSourceContext('@error/404.html.twig');

        $this->assertSame('@error/404.html.twig', $source->getName());
        $this->assertSame($template->getContent(), $source->getCode());
    }

    /**
     * @covers \Communication\Template\TwigDatabaseLoader::getCacheKey
     * @covers \Communication\Template\TwigDatabaseLoader::findTemplate
     */
    public function testGetCacheKeyWithNamespacedTemplate(): void
    {
        $template = new Template(
            'error404',
            '404.html.twig',
            'error',
            '<h1>404 Not Found</h1>',
            'text/html',
            'Page Not Found',
            [],
            new DateTimeImmutable('2025-01-01 12:00:00'),
            new DateTimeImmutable('2025-01-01 12:00:00')
        );

        $this->repository->shouldReceive('findByNameAndChannel')
            ->once()
            ->with('404.html.twig', 'error')
            ->andReturn($template);

        $cacheKey = $this->loader->getCacheKey('@error/404.html.twig');
        $this->assertStringContainsString('error404', $cacheKey);
        $this->assertStringContainsString('404.html.twig', $cacheKey);
        $this->assertStringContainsString((string)$template->getUpdatedAt()->getTimestamp(), $cacheKey);
    }

    /**
     * @covers \Communication\Template\TwigDatabaseLoader::exists
     * @covers \Communication\Template\TwigDatabaseLoader::findTemplate
     */
    public function testExistsWithNamespacedTemplate(): void
    {
        $template = new Template(
            'error404',
            '404.html.twig',
            'error',
            '<h1>404 Not Found</h1>',
            'text/html',
            'Page Not Found',
            [],
            new DateTimeImmutable('2025-01-01 12:00:00'),
            new DateTimeImmutable('2025-01-01 12:00:00')
        );

        $this->repository->shouldReceive('findByNameAndChannel')
            ->once()
            ->with('404.html.twig', 'error')
            ->andReturn($template);

        $this->repository->shouldReceive('findByNameAndChannel')
            ->once()
            ->with('nonexistent.html.twig', 'error')
            ->andReturnNull();

        $this->assertTrue($this->loader->exists('@error/404.html.twig'));
        $this->assertFalse($this->loader->exists('@error/nonexistent.html.twig'));
    }

    /**
     * @covers \Communication\Template\TwigDatabaseLoader::isFresh
     * @covers \Communication\Template\TwigDatabaseLoader::findTemplate
     */
    public function testIsFreshWithNamespacedTemplate(): void
    {
        $template = new Template(
            'error404',
            '404.html.twig',
            'error',
            '<h1>404 Not Found</h1>',
            'text/html',
            'Page Not Found',
            [],
            new DateTimeImmutable('2025-01-01 12:00:00'),
            new DateTimeImmutable('2025-01-01 12:00:00')
        );

        $this->repository->shouldReceive('findByNameAndChannel')
            ->once()
            ->with('404.html.twig', 'error')
            ->andReturn($template);

        // Test with a time after the template's update
        $this->assertTrue($this->loader->isFresh('@error/404.html.twig', strtotime('2025-01-02 12:00:00')));

        // Test with a time before the template's update
        $this->assertFalse($this->loader->isFresh('@error/404.html.twig', strtotime('2024-12-31 12:00:00')));
    }

    /**
     * @covers \Communication\Template\TwigDatabaseLoader::getSourceContext
     * @covers \Communication\Template\TwigDatabaseLoader::findTemplate
     */
    public function testNamespacedTemplateNotFound(): void
    {
        $this->repository->shouldReceive('findByNameAndChannel')
            ->once()
            ->with('nonexistent.html.twig', 'error')
            ->andReturnNull();

        $this->expectException(LoaderError::class);
        $this->expectExceptionMessage('Template "@error/nonexistent.html.twig" does not exist.');

        $this->loader->getSourceContext('@error/nonexistent.html.twig');
    }

    /**
     * @covers \Communication\Template\TwigDatabaseLoader::findTemplate
     */
    public function testInvalidNamespacedTemplateName(): void
    {
        // Test template name that starts with @ but has no slash - should return null directly
        // No repository call should be made
        $this->assertFalse($this->loader->exists('@invalidname'));
    }

    /**
     * @covers \Communication\Template\TwigDatabaseLoader::findTemplate
     */
    public function testCachingWorksForNamespacedTemplates(): void
    {
        $template = new Template(
            'error404',
            '404.html.twig',
            'error',
            '<h1>404 Not Found</h1>',
            'text/html',
            'Page Not Found',
            [],
            new DateTimeImmutable('2025-01-01 12:00:00'),
            new DateTimeImmutable('2025-01-01 12:00:00')
        );

        // Repository should only be called once due to caching
        $this->repository->shouldReceive('findByNameAndChannel')
            ->once()
            ->with('404.html.twig', 'error')
            ->andReturn($template);

        // First call
        $this->assertTrue($this->loader->exists('@error/404.html.twig'));

        // Second call should use cache
        $this->assertTrue($this->loader->exists('@error/404.html.twig'));
    }
}
