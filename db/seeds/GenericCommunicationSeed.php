<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

class GenericCommunicationSeed extends AbstractSeed
{
    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeders is available here:
     * https://book.cakephp.org/phinx/0/en/seeding.html
     */
    public function run(): void
    {
        // Create a generic communication definition
        $communicationDefinitions = $this->table('communication_definitions');
        $communicationDefinitions->insert([
            'identifier' => 'generic.email',
            'name' => 'Generic Email Communication',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ])->save();

        // Create a channel definition for email
        $channelDefinitions = $this->table('channel_definitions');
        $channelDefinitions->insert([
            'communication_identifier' => 'generic.email',
            'channel' => 'email',
            'template' => 'generic.html.twig',
            'context_schema' => json_encode([
                'type' => 'object',
                'required' => ['body'],
                'properties' => [
                    'body' => ['type' => 'string'],
                    'additionalData' => ['type' => 'object']
                ]
            ]),
            'subject_schema' => json_encode([
                'type' => 'object',
                'required' => ['subject'],
                'properties' => [
                    'subject' => ['type' => 'string']
                ]
            ]),
            'channel_config' => json_encode([
                'from_address' => $_ENV['COMMUNICATION_FROM_EMAIL'] ?? 'noreply@example.com',
                'reply_to' => $_ENV['COMMUNICATION_REPLY_EMAIL'] ?? $_ENV['COMMUNICATION_FROM_EMAIL'] ?? 'support@example.com',
            ]),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ])->save();

        $communicationTemplates = $this->table('communication_templates');

        // Create base email template (level 1 - base)
        $baseTemplateId = $this->generateUlid();
        $communicationTemplates->insert([
            'id' => $baseTemplateId,
            'name' => 'base.html.twig',
            'channel' => 'email',
            'subject' => null,
            'content' => $this->getBaseEmailTemplate(),
            'content_type' => 'text/html',
            'metadata' => json_encode([
                'description' => 'Base email template with common HTML structure and styling',
                'version' => '1.0',
                'level' => 'base'
            ]),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ])->save();

        // Create email layout template (level 2 - layout)
        $layoutTemplateId = $this->generateUlid();
        $communicationTemplates->insert([
            'id' => $layoutTemplateId,
            'name' => 'email_layout.html.twig',
            'channel' => 'email',
            'subject' => null,
            'content' => $this->getEmailLayoutTemplate(),
            'content_type' => 'text/html',
            'metadata' => json_encode([
                'description' => 'Email layout template with email-specific structure',
                'version' => '1.0',
                'level' => 'layout',
                'extends' => 'base.html.twig'
            ]),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ])->save();

        // Create generic email template (level 3 - specific template)
        $genericTemplateId = $this->generateUlid();
        $communicationTemplates->insert([
            'id' => $genericTemplateId,
            'name' => 'generic.html.twig',
            'channel' => 'email',
            'subject' => 'Generic Email',
            'content' => $this->getGenericEmailTemplate(),
            'content_type' => 'text/html',
            'metadata' => json_encode([
                'description' => 'Generic email template with a simple body variable',
                'version' => '1.0',
                'level' => 'template',
                'extends' => 'email_layout.html.twig'
            ]),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ])->save();
    }

    /**
     * Generate a ULID (Universally Unique Lexicographically Sortable Identifier)
     * This is a simplified implementation for demonstration purposes
     */
    private function generateUlid(): string
    {
        $time = (string)(microtime(true) * 1000);
        $timestamp = str_pad(base_convert($time, 10, 32), 10, '0', STR_PAD_LEFT);
        $randomness = bin2hex(random_bytes(8));

        // Convert to Crockford's base32 (using only uppercase letters and digits 0-9, excluding I, L, O, U)
        $base32Chars = '0123456789ABCDEFGHJKMNPQRSTVWXYZ';
        $ulid = '';

        // Convert timestamp
        for ($i = 0; $i < strlen($timestamp); $i++) {
            $char = $timestamp[$i];
            $index = hexdec($char);
            $ulid .= $base32Chars[$index];
        }

        // Convert randomness
        for ($i = 0; $i < strlen($randomness); $i++) {
            $char = $randomness[$i];
            $index = hexdec($char) % 32;
            $ulid .= $base32Chars[$index];
        }

        return $ulid;
    }

    /**
     * Get the base HTML template (Level 1 - Base)
     *
     * This provides the common HTML structure and basic styling for all email templates
     */
    private function getBaseEmailTemplate(): string
    {
        return <<<TWIG
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{% block title %}{{ subject|default('Email') }}{% endblock %}</title>
    {% block stylesheets %}
    <style>
        /* Base styles for all emails */
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
        }
        .email-header {
            padding: 20px;
            background-color: #ffffff;
        }
        .email-footer {
            margin-top: 20px;
            padding: 20px;
            font-size: 12px;
            color: #777;
            text-align: center;
            background-color: #f9f9f9;
            border-top: 1px solid #eee;
        }
        /* Responsive styles */
        @media only screen and (max-width: 600px) {
            .email-container {
                width: 100% !important;
            }
            .email-header, .email-footer {
                padding: 15px !important;
            }
        }
    </style>
    {% endblock %}
</head>
<body>
    <div class="email-container">
        {% block body %}
            {% block header %}
            <div class="email-header">
                {% block header_content %}{% endblock %}
            </div>
            {% endblock %}

            {% block content %}{% endblock %}

            {% block footer %}
            <div class="email-footer">
                {% block footer_content %}
                <p>This is an automated message. Please do not reply to this email.</p>
                <p>© {{ "now"|date("Y") }} Your Company. All rights reserved.</p>
                {% endblock %}
            </div>
            {% endblock %}
        {% endblock %}
    </div>
</body>
</html>
TWIG;
    }

    /**
     * Get the email layout template (Level 2 - Layout)
     *
     * This extends the base template and provides email-specific content structure
     */
    private function getEmailLayoutTemplate(): string
    {
        return <<<TWIG
{% extends 'base.html.twig' %}

{% block stylesheets %}
{{ parent() }}
<style>
    /* Email-specific layout styles */
    .email-content {
        padding: 20px;
    }
    .content-section {
        background-color: #f9f9f9;
        border: 1px solid #ddd;
        border-radius: 5px;
        padding: 20px;
        margin-bottom: 20px;
    }
    .additional-info {
        margin-top: 15px;
        padding-top: 15px;
        border-top: 1px solid #eee;
        font-size: 13px;
        color: #666;
    }
    .additional-info p {
        margin: 5px 0;
    }
</style>
{% endblock %}

{% block content %}
<div class="email-content">
    {% block email_content %}
    <div class="content-section">
        {% block main_content %}{% endblock %}

        {% block additional_info %}
        {% if additionalData is defined and additionalData %}
            <div class="additional-info">
                {% if additionalData.timestamp is defined %}
                    <p><strong>Sent on:</strong> {{ additionalData.timestamp }}</p>
                {% endif %}

                {% if additionalData.sender is defined %}
                    <p><strong>From:</strong> {{ additionalData.sender }}</p>
                {% endif %}

                {# Loop through any other additional data #}
                {% for key, value in additionalData %}
                    {% if key != 'timestamp' and key != 'sender' %}
                        <p><strong>{{ key|title }}:</strong> {{ value }}</p>
                    {% endif %}
                {% endfor %}
            </div>
        {% endif %}
        {% endblock %}
    </div>
    {% endblock %}
</div>
{% endblock %}
TWIG;
    }

    /**
     * Get the HTML template for the generic email (Level 3 - Specific Template)
     *
     * This extends the email layout and provides the specific content for generic emails
     */
    private function getGenericEmailTemplate(): string
    {
        return <<<TWIG
{% extends 'email_layout.html.twig' %}

{% block subject %}{{ subject }}{% endblock %}

{% block title %}{{ subject|default('Generic Email') }}{% endblock %}

{% block main_content %}
{{ body|raw }}
{% endblock %}

{% block body_text %}
{{ subject|default('Generic Email') }}

{{ body|striptags }}

{% if additionalData is defined and additionalData %}
{% if additionalData.timestamp is defined %}Sent on: {{ additionalData.timestamp }}{% endif %}
{% if additionalData.sender is defined %}From: {{ additionalData.sender }}{% endif %}
{% endif %}

This is an automated message. Please do not reply to this email.
© {{ "now"|date("Y") }} Your Company. All rights reserved.
{% endblock %}
TWIG;
    }
}
