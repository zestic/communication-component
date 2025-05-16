<?php

declare(strict_types=1);

namespace Communication\Template;

interface TemplateInterface
{
    public function getId(): string;

    public function getName(): string;

    public function getSubject(): ?string;

    public function getChannel(): string;

    public function getContent(): string;

    public function getContentType(): string;

    public function getMetadata(): array;

    public function getCreatedAt(): \DateTimeImmutable;

    public function getUpdatedAt(): \DateTimeImmutable;
}
