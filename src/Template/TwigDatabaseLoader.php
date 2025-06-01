<?php

declare(strict_types=1);

namespace Communication\Template;

use Twig\Error\LoaderError;
use Twig\Loader\LoaderInterface;
use Twig\Source;

class TwigDatabaseLoader implements LoaderInterface
{
    private TemplateRepositoryInterface $templateRepository;

    private array $cache = [];

    private array $dependencies = [];

    public function __construct(TemplateRepositoryInterface $templateRepository)
    {
        $this->templateRepository = $templateRepository;
    }

    public function getSourceContext(string $name): Source
    {
        if (!$template = $this->findTemplate($name)) {
            throw new LoaderError(sprintf('Template "%s" does not exist.', $name));
        }

        // Track template dependencies by parsing extends and include tags
        $this->updateDependencies($name, $template->getContent());

        return new Source(
            $template->getContent(),
            $name,
            ''
        );
    }

    public function getCacheKey(string $name): string
    {
        if (!$template = $this->findTemplate($name)) {
            throw new LoaderError(sprintf('Template "%s" does not exist.', $name));
        }

        return $template->getId() . '/' . $template->getName() . '/' . $template->getUpdatedAt()->getTimestamp();
    }

    public function isFresh(string $name, int $time): bool
    {
        if (!$template = $this->findTemplate($name)) {
            return false;
        }

        // Check if the main template is fresh
        if ($template->getUpdatedAt()->getTimestamp() > $time) {
            return false;
        }

        // Check if any dependencies are fresh
        if (isset($this->dependencies[$name])) {
            foreach ($this->dependencies[$name] as $depName) {
                if ($depTemplate = $this->findTemplate($depName)) {
                    if ($depTemplate->getUpdatedAt()->getTimestamp() > $time) {
                        return false;
                    }
                }
            }
        }

        return true;
    }

    public function exists(string $name): bool
    {
        return $this->findTemplate($name) !== null;
    }

    private function findTemplate(string $name): ?TemplateInterface
    {
        if (isset($this->cache[$name])) {
            return $this->cache[$name];
        }

        $template = $this->templateRepository->findByName($name);
        if ($template) {
            $this->cache[$name] = $template;
        }

        return $template;
    }

    private function updateDependencies(string $name, string $content): void
    {
        if (!isset($this->dependencies[$name])) {
            $this->dependencies[$name] = [];

            // Match {% extends "template" %} and {% include "template" %}
            $patterns = [
                '/{%\\s*extends\\s+[\'"](.*?)[\'"]\\s*%}/i',
                '/{%\\s*include\\s+[\'"](.*?)[\'"]\\s*%}/i',
            ];

            foreach ($patterns as $pattern) {
                if (preg_match_all($pattern, $content, $matches)) {
                    foreach ($matches[1] as $depName) {
                        // Use template name directly (e.g., "base.html.twig")
                        $this->dependencies[$name][] = $depName;
                    }
                }
            }
        }
    }
}
