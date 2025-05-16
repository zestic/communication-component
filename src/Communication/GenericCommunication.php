<?php

declare(strict_types=1);

namespace Communication\Communication;

use Communication\Communication;

final class GenericCommunication extends Communication
{
    public function dispatch(string $subject, string $body)
    {
        $bodyContext = [
            'body' => $body,
        ];
        $this->context
            ->setBodyContext($bodyContext)
            ->setSubject($subject);

        $this->send();
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
