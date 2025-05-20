<?php

declare(strict_types=1);

namespace Communication\Definition;

use Communication\Definition\Exception\InvalidContextException;
use Communication\Definition\Exception\InvalidSubjectException;
use JsonSchema\Validator;

abstract class AbstractChannelDefinition implements ChannelDefinition
{
    public function __construct(
        protected string $channel,
        protected string $template,
        protected array $contextSchema,
        protected array $subjectSchema
    ) {
    }

    public function getChannel(): string
    {
        return $this->channel;
    }

    public function getTemplate(): string
    {
        return $this->template;
    }

    public function getContextSchema(): array
    {
        return $this->contextSchema;
    }

    public function getSubjectSchema(): array
    {
        return $this->subjectSchema;
    }

    public function validateContext(array $context): void
    {
        $validator = new Validator();
        $contextData = json_encode($context);
        if ($contextData === false) {
            throw new \RuntimeException('Failed to encode context data to JSON');
        }
        $data = json_decode($contextData);

        $schemaData = json_encode($this->contextSchema);
        if ($schemaData === false) {
            throw new \RuntimeException('Failed to encode context schema to JSON');
        }
        $schema = json_decode($schemaData);

        $validator->validate($data, $schema);

        if (!$validator->isValid()) {
            throw new InvalidContextException($validator->getErrors());
        }
    }

    public function validateSubject(array $context): void
    {
        $validator = new Validator();
        $contextData = json_encode($context);
        if ($contextData === false) {
            throw new \RuntimeException('Failed to encode subject data to JSON');
        }
        $data = json_decode($contextData);

        $schemaData = json_encode($this->subjectSchema);
        if ($schemaData === false) {
            throw new \RuntimeException('Failed to encode subject schema to JSON');
        }
        $schema = json_decode($schemaData);

        $validator->validate($data, $schema);

        if (!$validator->isValid()) {
            throw new InvalidSubjectException($validator->getErrors());
        }
    }
}
