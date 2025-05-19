<?php

declare(strict_types=1);

namespace Tests\Unit\Communication\Definition;

use Communication\Definition\EmailChannelDefinition;
use Communication\Definition\Exception\InvalidContextException;
use Communication\Definition\Exception\InvalidSubjectException;
use PHPUnit\Framework\TestCase;

class EmailChannelDefinitionTest extends TestCase
{
    private EmailChannelDefinition $definition;

    protected function setUp(): void
    {
        $this->definition = new EmailChannelDefinition(
            'email-template',
            ['type' => 'object', 'required' => ['body']],
            ['type' => 'object', 'required' => ['subject']],
            'from@example.com',
            'reply@example.com'
        );
    }

    public function testGetters(): void
    {
        $this->assertEquals('email', $this->definition->getChannel());
        $this->assertEquals('email-template', $this->definition->getTemplate());
        $this->assertEquals('from@example.com', $this->definition->getFromAddress());
        $this->assertEquals('reply@example.com', $this->definition->getReplyTo());
    }

    public function testValidateContextSuccess(): void
    {
        $context = ['body' => 'Test body'];
        $this->definition->validateContext($context);
        $this->assertTrue(true); // If we get here, no exception was thrown
    }

    public function testValidateContextFailure(): void
    {
        $this->expectException(InvalidContextException::class);
        $this->definition->validateContext(['invalid' => 'context']);
    }

    public function testValidateSubjectSuccess(): void
    {
        $subject = ['subject' => 'Test subject'];
        $this->definition->validateSubject($subject);
        $this->assertTrue(true); // If we get here, no exception was thrown
    }

    public function testValidateSubjectFailure(): void
    {
        $this->expectException(InvalidSubjectException::class);
        $this->definition->validateSubject(['invalid' => 'subject']);
    }
}
