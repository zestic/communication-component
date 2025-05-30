<?php

require __DIR__ . '/../vendor/autoload.php';

// Get the updated template content
function getGenericEmailTemplate(): string
{
    return <<<TWIG
{% block subject %}{{ subject }}{% endblock %}

{% block body_html %}
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ subject|default('Generic Email') }}</title>
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
        .additional-info {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #eee;
            font-size: 13px;
        }
    </style>
</head>
<body>
    <div class="content">
        {{ body|raw }}
        
        {% if additionalData is defined and additionalData %}
            <div class="additional-info">
                {% if additionalData.timestamp is defined %}
                    <p>Sent on: {{ additionalData.timestamp }}</p>
                {% endif %}
                
                {% if additionalData.sender is defined %}
                    <p>From: {{ additionalData.sender }}</p>
                {% endif %}
                
                {# Loop through any other additional data #}
                {% for key, value in additionalData %}
                    {% if key != 'timestamp' and key != 'sender' %}
                        <p>{{ key|title }}: {{ value }}</p>
                    {% endif %}
                {% endfor %}
            </div>
        {% endif %}
    </div>
    
    <div class="footer">
        <p>This is an automated message. Please do not reply to this email.</p>
        <p>© {{ "now"|date("Y") }} Your Company. All rights reserved.</p>
    </div>
</body>
</html>
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

// Output the template to a file
$templateFile = __DIR__ . '/../templates/emails/generic.html.twig';
$templateDir = dirname($templateFile);

// Create the directory if it doesn't exist
if (!is_dir($templateDir)) {
    mkdir($templateDir, 0755, true);
}

// Write the template to the file
file_put_contents($templateFile, getGenericEmailTemplate());

echo "Template updated successfully at: {$templateFile}\n";
echo "You can now use this template in your application.\n";
