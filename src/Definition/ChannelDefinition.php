<?php

declare(strict_types=1);

namespace Communication\Definition;

interface ChannelDefinition
{
    public function getTemplate(): string;
    public function validateContext(array $context): void;
    public function validateSubject(array $context): void;
    public function getChannel(): string;
    public function getContextSchema(): array;
    public function getSubjectSchema(): array;
}
