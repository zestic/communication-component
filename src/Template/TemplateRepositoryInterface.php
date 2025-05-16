<?php

declare(strict_types=1);

namespace Communication\Template;

interface TemplateRepositoryInterface
{
    public function findById(string $id): ?TemplateInterface;

    public function findByName(string $name): ?TemplateInterface;

    public function findByNameAndChannel(string $name, string $channel): ?TemplateInterface;

    public function save(TemplateInterface $template): void;

    public function delete(string $id): void;
}
