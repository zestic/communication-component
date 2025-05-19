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
    ) {}

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
        $data = json_decode(json_encode($context));
        $schema = json_decode(json_encode($this->contextSchema));
        $validator->validate($data, $schema);

        if (!$validator->isValid()) {
            throw new InvalidContextException($validator->getErrors());
        }
    }

    public function validateSubject(array $context): void
    {
        $validator = new Validator();
        $data = json_decode(json_encode($context));
        $schema = json_decode(json_encode($this->subjectSchema));
        $validator->validate($data, $schema);

        if (!$validator->isValid()) {
            throw new InvalidSubjectException($validator->getErrors());
        }
    }
}
