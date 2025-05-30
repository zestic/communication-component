<?php

declare(strict_types=1);

namespace Communication\Definition\Repository;

use Communication\Definition\CommunicationDefinition;
use Communication\Definition\EmailChannelDefinition;
use Communication\Definition\MobileChannelDefinition;
use PDO;

class PostgresCommunicationDefinitionRepository implements CommunicationDefinitionRepositoryInterface
{
    public function __construct(
        private PDO $pdo
    ) {
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function findByIdentifier(string $identifier): ?CommunicationDefinition
    {
        $stmt = $this->pdo->prepare('
            SELECT cd.identifier,
                   cd.name,
                   chd.channel,
                   chd.template,
                   chd.context_schema,
                   chd.subject_schema,
                   chd.channel_config
            FROM communication_definitions cd
            LEFT JOIN channel_definitions chd ON cd.identifier = chd.communication_identifier
            WHERE cd.identifier = :identifier
        ');

        $stmt->execute(['identifier' => $identifier]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($rows)) {
            return null;
        }

        $definition = new CommunicationDefinition($identifier, $rows[0]['name']);

        foreach ($rows as $row) {
            if ($row['channel'] === null) {
                continue;
            }

            $channelConfig = json_decode($row['channel_config'], true);
            if (!is_array($channelConfig)) {
                throw new \RuntimeException("Invalid channel_config JSON for channel: {$row['channel']}");
            }

            $contextSchema = json_decode($row['context_schema'], true);
            if (!is_array($contextSchema)) {
                throw new \RuntimeException("Invalid context_schema JSON for channel: {$row['channel']}");
            }

            $subjectSchema = json_decode($row['subject_schema'], true);
            if (!is_array($subjectSchema)) {
                throw new \RuntimeException("Invalid subject_schema JSON for channel: {$row['channel']}");
            }

            $channelDef = match($row['channel']) {
                'email' => new EmailChannelDefinition(
                    $row['template'],
                    $contextSchema,
                    $subjectSchema,
                    $channelConfig['from_address'] ?? '',
                    $channelConfig['reply_to'] ?? null
                ),
                'mobile' => new MobileChannelDefinition(
                    $row['template'],
                    $contextSchema,
                    $subjectSchema,
                    (int) ($channelConfig['priority'] ?? 0),
                    (bool) ($channelConfig['requires_auth'] ?? false)
                ),
                default => throw new \RuntimeException("Unknown channel type: {$row['channel']}")
            };

            $definition->addChannelDefinition($channelDef);
        }

        return $definition;
    }

    public function save(CommunicationDefinition $definition): void
    {
        $this->pdo->beginTransaction();

        try {
            // Insert or update communication definition
            $stmt = $this->pdo->prepare('
                INSERT INTO communication_definitions (identifier, name, updated_at)
                VALUES (:identifier, :name, CURRENT_TIMESTAMP)
                ON CONFLICT (identifier)
                DO UPDATE SET name = :name, updated_at = CURRENT_TIMESTAMP
            ');

            $stmt->execute([
                'identifier' => $definition->getIdentifier(),
                'name' => $definition->getName(),
            ]);

            // Delete existing channel definitions
            $stmt = $this->pdo->prepare('
                DELETE FROM channel_definitions
                WHERE communication_identifier = :identifier
            ');

            $stmt->execute(['identifier' => $definition->getIdentifier()]);

            // Insert new channel definitions
            $stmt = $this->pdo->prepare('
                INSERT INTO channel_definitions (
                    communication_identifier,
                    channel,
                    template,
                    context_schema,
                    subject_schema,
                    channel_config
                ) VALUES (
                    :identifier,
                    :channel,
                    :template,
                    :context_schema,
                    :subject_schema,
                    :channel_config
                )
            ');

            foreach ($definition->getChannelDefinitions() as $channelDef) {
                $channelConfig = match(true) {
                    $channelDef instanceof EmailChannelDefinition => [
                        'from_address' => $channelDef->getFromAddress(),
                        'reply_to' => $channelDef->getReplyTo(),
                    ],
                    $channelDef instanceof MobileChannelDefinition => [
                        'priority' => $channelDef->getPriority(),
                        'requires_auth' => $channelDef->requiresAuth(),
                    ],
                    default => throw new \RuntimeException('Unknown channel definition type: ' . get_class($channelDef))
                };

                $stmt->execute([
                    'identifier' => $definition->getIdentifier(),
                    'channel' => $channelDef->getChannel(),
                    'template' => $channelDef->getTemplate(),
                    'context_schema' => json_encode($channelDef->getContextSchema()),
                    'subject_schema' => json_encode($channelDef->getSubjectSchema()),
                    'channel_config' => json_encode($channelConfig),
                ]);
            }

            $this->pdo->commit();
        } catch (\Throwable $e) {
            $this->pdo->rollBack();

            throw $e;
        }
    }
}
