<?php

declare(strict_types=1);

namespace Communication\Communication;

use Communication\Communication;
use Communication\Interactor\SendCommunication;

final class GenericCommunication extends Communication
{
    private SendCommunication $sender;

    public function __construct(
        SendCommunication $sender,
        string $definitionId = 'generic.email'
    ) {
        parent::__construct($definitionId);
        $this->sender = $sender;
    }

    /**
     * Dispatch a generic communication with the given subject and body
     */
    public function dispatch(string $subject, string $body): void
    {
        $bodyContext = [
            'body' => $body,
        ];
        $this->getContext()
            ->setBodyContext($bodyContext)
            ->setSubject($subject);

        $this->sender->send($this);
    }

    protected function getAllowedChannels(): array
    {
        return [
            'email',
        ];
    }

    protected function getTemplates(): array
    {
        return [
            'email' => [
                'html' => 'generic',
            ],
        ];
    }
}
