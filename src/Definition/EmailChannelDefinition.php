<?php

declare(strict_types=1);

namespace Communication\Definition;

class EmailChannelDefinition extends AbstractChannelDefinition
{
    public function __construct(
        string $template,
        array $contextSchema,
        array $subjectSchema,
        private string $fromAddress,
        private ?string $replyTo = null,
        private int $priority = 0,
        private bool $requiresAuth = false
    ) {
        parent::__construct('email', $template, $contextSchema, $subjectSchema);
    }

    public function getFromAddress(): string
    {
        return $this->fromAddress;
    }

    public function getReplyTo(): ?string
    {
        return $this->replyTo;
    }

    public function setFromAddress(string $fromAddress): self
    {
        $this->fromAddress = $fromAddress;
        return $this;
    }

    public function setReplyTo(?string $replyTo): self
    {
        $this->replyTo = $replyTo;
        return $this;
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
