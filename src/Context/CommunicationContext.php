<?php
declare(strict_types=1);

namespace Communication\Context;

class CommunicationContext
{
    /**
     * @var CommunicationContextInterface[] $channelContexts
     */
    public function __construct(
        private array $channelContexts = [],
    )  {
    }

    public function __call(string $method, $args): CommunicationContext
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

    public function addToContext(string $name, $value): CommunicationContext
    {
        foreach ($this->channelContexts as $channel => $context) {
            $this->channelContexts[$channel]->addBodyContext($name, $value);
        }

        return $this;
    }

    public function addEmailContext(string $name, $value): CommunicationContext
    {
        $this->channelContexts['email']->addBodyContext($name, $value);

        return $this;
    }

    public function getContext(string $name)
    {
        return $this->channelContexts[$name];
    }

    public function setBodyContext($from): CommunicationContext
    {
        foreach ($this->channelContexts as $context) {
            $context->setBodyContext($from);
        }

        return $this;
    }

    public function setFrom($from): CommunicationContext
    {
        foreach ($this->channelContexts as $context) {
            $context->setFrom($from);
        }

        return $this;
    }

    public function setRecipients(array $recipients): CommunicationContext
    {
        foreach ($this->channelContexts as $context) {
            $context->setRecipients($recipients);
        }

        return $this;
    }
}
