<?php

declare(strict_types=1);

namespace Tests\Unit\Communication\Definition;

use Communication\Definition\Exception\InvalidContextException;
use Communication\Definition\Exception\InvalidSubjectException;
use Communication\Definition\MobileChannelDefinition;
use PHPUnit\Framework\TestCase;

class MobileChannelDefinitionTest extends TestCase
{
    private MobileChannelDefinition $definition;

    protected function setUp(): void
    {
        $this->definition = new MobileChannelDefinition(
            'mobile-template',
            ['type' => 'object', 'required' => ['message']],
            ['type' => 'object', 'required' => ['title']],
            2,
            true
        );
    }

    public function testGetters(): void
    {
        $this->assertEquals('mobile', $this->definition->getChannel());
        $this->assertEquals('mobile-template', $this->definition->getTemplate());
        $this->assertEquals(2, $this->definition->getPriority());
        $this->assertTrue($this->definition->requiresAuth());
    }

    public function testValidateContextSuccess(): void
    {
        $context = ['message' => 'Test message'];
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
        $subject = ['title' => 'Test title'];
        $this->definition->validateSubject($subject);
        $this->assertTrue(true); // If we get here, no exception was thrown
    }

    public function testValidateSubjectFailure(): void
    {
        $this->expectException(InvalidSubjectException::class);
        $this->definition->validateSubject(['invalid' => 'title']);
    }
}
