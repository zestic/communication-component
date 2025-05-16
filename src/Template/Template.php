<?php

declare(strict_types=1);

namespace Communication\Template;

class Template implements TemplateInterface
{
    private string $id;

    private string $name;

    private ?string $subject;

    private string $channel;

    private string $content;

    private string $contentType;

    private array $metadata;

    private \DateTimeImmutable $createdAt;

    private \DateTimeImmutable $updatedAt;

    public function __construct(
        string $id,
        string $name,
        string $channel,
        string $content,
        string $contentType = 'text/html',
        ?string $subject = null,
        array $metadata = [],
        ?\DateTimeImmutable $createdAt = null,
        ?\DateTimeImmutable $updatedAt = null
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->channel = $channel;
        $this->content = $content;
        $this->contentType = $contentType;
        $this->subject = $subject;
        $this->metadata = $metadata;
        $this->createdAt = $createdAt ?? new \DateTimeImmutable();
        $this->updatedAt = $updatedAt ?? $this->createdAt;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getSubject(): ?string
    {
        return $this->subject;
    }

    public function getChannel(): string
    {
        return $this->channel;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getContentType(): string
    {
        return $this->contentType;
    }

    public function getMetadata(): array
    {
        return $this->metadata;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }
}
