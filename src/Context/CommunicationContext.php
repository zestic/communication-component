<?php
declare(strict_types=1);

namespace Communication\Context;

final class CommunicationContext
{
    public function __construct(
        private array $data = [],
        private array $meta = [],
    )  {
    }

    public function get(string $key)
    {
        return $this->data[$key] ?? null;
    }

    public function getMeta(string $key)
    {
        return $this->meta[$key] ?? null;
    }

    public function set(string $key, $value): self
    {
        $this->data[$key] = $value;

        return $this;
    }

    public function setMeta(string $key, $meta): self
    {
        $this->meta[$key] = $meta;

        return $this;
    }

    public function toArray(): array
    {
        return $this->data;
    }
}
