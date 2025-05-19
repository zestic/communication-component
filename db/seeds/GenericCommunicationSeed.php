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
            'template' => 'generic',
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
                'from_address' => 'noreply@example.com',
                'reply_to' => 'support@example.com'
            ]),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ])->save();

        // Create a template for the generic email
        $templateId = $this->generateUlid();
        $communicationTemplates = $this->table('communication_templates');
        $communicationTemplates->insert([
            'id' => $templateId,
            'name' => 'generic',
            'channel' => 'email',
            'subject' => 'Generic Email',
            'content' => $this->getGenericEmailTemplate(),
            'content_type' => 'text/html',
            'metadata' => json_encode([
                'description' => 'A generic email template with a simple body variable',
                'version' => '1.0'
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
        $time = (int)(microtime(true) * 1000);
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
     * Get the HTML template for the generic email
     */
    private function getGenericEmailTemplate(): string
    {
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generic Email</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .content {
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 20px;
            margin-top: 20px;
        }
        .footer {
            margin-top: 20px;
            font-size: 12px;
            color: #777;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="content">
        {{ body }}
    </div>
    <div class="footer">
        <p>This is an automated message. Please do not reply to this email.</p>
    </div>
</body>
</html>
HTML;
    }
}
