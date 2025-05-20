<?php

declare(strict_types=1);

namespace Communication\Definition;

class MobileChannelDefinition extends AbstractChannelDefinition implements MobileChannelDefinitionInterface
{
    public function __construct(
        string $template,
        array $contextSchema,
        array $subjectSchema,
        private int $priority = 0,
        private bool $requiresAuth = false,
    ) {
        parent::__construct('mobile', $template, $contextSchema, $subjectSchema);
    }

    public function getPriority(): int
    {
        return $this->priority;
    }

    public function requiresAuth(): bool
    {
        return $this->requiresAuth;
    }
}
