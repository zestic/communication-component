<?php

declare(strict_types=1);

namespace Communication\Definition\Exception;

class InvalidSubjectException extends \RuntimeException
{
    public function __construct(array $errors)
    {
        $message = 'Invalid subject: ' . implode(', ', array_map(
            fn($error) => "[{$error['property']}] {$error['message']}",
            $errors
        ));
        parent::__construct($message);
    }
}
