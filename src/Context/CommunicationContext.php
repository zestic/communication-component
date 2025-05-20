<?php

declare(strict_types=1);

namespace Communication\Context;

class CommunicationContext
{
    /**
     * @param array<string, CommunicationContextInterface> $channelContexts
     */
    public function __construct(
        private array $channelContexts = [],
    ) {
    }

    /**
     * @param array<mixed> $args
     */
    public function __call(string $method, array $args): CommunicationContext
    {
        if (!str_starts_with($method, 'set')) {
            throw new \BadMethodCallException();
        }

        foreach ($this->channelContexts as $context) {
            if (method_exists($context, $method)) {
                $context->$method(...$args);
            }
        }

        return $this;
    }

    /**
     * @param mixed $value
     */
    public function addToContext(string $name, $value): CommunicationContext
    {
        foreach ($this->channelContexts as $channelContext) {
            $channelContext->addBodyContext($name, $value);
        }

        return $this;
    }

    /**
     * @param mixed $value
     */
    public function addEmailContext(string $name, $value): CommunicationContext
    {
        $this->channelContexts['email']->addBodyContext($name, $value);

        return $this;
    }

    /**
     * @return CommunicationContextInterface|null
     */
    public function getContext(string $name): ?CommunicationContextInterface
    {
        return $this->channelContexts[$name] ?? null;
    }

    /**
     * @param array<mixed> $bodyContext
     */
    public function setBodyContext(array $bodyContext): CommunicationContext
    {
        foreach ($this->channelContexts as $context) {
            $context->setBodyContext($bodyContext);
        }

        return $this;
    }

    /**
     * @param mixed $from
     */
    public function setFrom($from): CommunicationContext
    {
        foreach ($this->channelContexts as $context) {
            $context->setFrom($from);
        }

        return $this;
    }

    /**
     * @param array<mixed> $recipients
     */
    public function setRecipients(array $recipients): CommunicationContext
    {
        foreach ($this->channelContexts as $context) {
            $context->setRecipients($recipients);
        }

        return $this;
    }
}
