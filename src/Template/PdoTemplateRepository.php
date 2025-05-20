<?php

declare(strict_types=1);

namespace Communication\Template;

use DateTimeImmutable;
use PDO;

class PdoTemplateRepository implements TemplateRepositoryInterface
{
    private string $driver;

    public function __construct(
        private readonly PDO $pdo
    ) {
        /** @var string */
        $driver = $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
        $this->driver = $driver;
        $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }

    public function findById(string $id): ?TemplateInterface
    {
        $sql = 'SELECT id, name, channel, content, content_type, subject, metadata, created_at, updated_at FROM communication_templates WHERE id = :id';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);

        /** @var array<string, mixed>|false $result */
        $result = $stmt->fetch();

        return $this->hydrate($result === false ? null : $result);
    }

    public function findByName(string $name): ?TemplateInterface
    {
        $sql = 'SELECT id, name, channel, content, content_type, subject, metadata, created_at, updated_at FROM communication_templates WHERE name = :name';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['name' => $name]);

        /** @var array<string, mixed>|false $result */
        $result = $stmt->fetch();

        return $this->hydrate($result === false ? null : $result);
    }

    public function findByNameAndChannel(string $name, string $channel): ?TemplateInterface
    {
        $sql = 'SELECT id, name, channel, content, content_type, subject, metadata, created_at, updated_at FROM communication_templates WHERE name = :name AND channel = :channel';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'name' => $name,
            'channel' => $channel,
        ]);

        /** @var array<string, mixed>|false $result */
        $result = $stmt->fetch();

        return $this->hydrate($result === false ? null : $result);
    }

    public function save(TemplateInterface $template): void
    {
        error_log('Driver: ' . $this->driver);
        $params = [
            'id' => $template->getId(),
            'name' => $template->getName(),
            'channel' => $template->getChannel(),
            'subject' => $template->getSubject(),
            'content' => $template->getContent(),
            'content_type' => $template->getContentType(),
            'metadata' => $this->encodeMetadata($template->getMetadata()),
            'created_at' => $this->formatDateTime($template->getCreatedAt()),
            'updated_at' => $this->formatDateTime($template->getUpdatedAt()),
        ];

        // Try update first
        $sql = 'UPDATE communication_templates SET 
                name = :name,
                channel = :channel,
                subject = :subject,
                content = :content,
                content_type = :content_type,
                metadata = :metadata,
                created_at = :created_at,
                updated_at = :updated_at
                WHERE id = :id';

        $stmt = $this->pdo->prepare($sql);
        error_log('Executing query with params: ' . print_r($params, true));
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        $stmt->execute();

        // If no rows were updated, insert
        if ($stmt->rowCount() === 0) {
            $sql = 'INSERT INTO communication_templates 
                    (id, name, channel, subject, content, content_type, metadata, created_at, updated_at)
                    VALUES 
                    (:id, :name, :channel, :subject, :content, :content_type, :metadata, :created_at, :updated_at)';

            $stmt = $this->pdo->prepare($sql);
            error_log('Executing query with params: ' . print_r($params, true));
            foreach ($params as $key => $value) {
                $stmt->bindValue(':' . $key, $value);
            }
            $stmt->execute();
        }
    }

    public function delete(string $id): void
    {
        $sql = 'DELETE FROM communication_templates WHERE id = :id';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
    }

    /**
     * @param array<string, mixed>|null $data
     */
    private function hydrate(?array $data): ?TemplateInterface
    {
        if (!$data) {
            return null;
        }

        error_log('Data received in hydrate: ' . print_r($data, true));

        /** @var string $id */
        $id = $data['id'];
        /** @var string $name */
        $name = $data['name'];
        /** @var string $channel */
        $channel = $data['channel'];
        /** @var string $content */
        $content = $data['content'];
        /** @var string $contentType */
        $contentType = $data['content_type'];
        /** @var string|null $subject */
        $subject = $data['subject'];
        /** @var string|array<string, mixed>|null $metadata */
        $metadata = $data['metadata'];
        /** @var string $createdAt */
        $createdAt = $data['created_at'];
        /** @var string $updatedAt */
        $updatedAt = $data['updated_at'];

        return new Template(
            $id,
            $name,
            $channel,
            $content,
            $contentType,
            $subject,
            $this->decodeMetadata($metadata),
            new DateTimeImmutable($createdAt),
            new DateTimeImmutable($updatedAt)
        );
    }

    /**
     * @param array<string, mixed> $metadata
     */
    private function encodeMetadata(array $metadata): string
    {
        // SQLite and PostgreSQL need JSON encoding
        if ($this->driver === 'pgsql' || $this->driver === 'sqlite') {
            return json_encode($metadata, JSON_THROW_ON_ERROR);
        }

        // MySQL doesn't need JSON encoding for JSON columns
        return json_encode($metadata, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    /**
     * @param string|array<string, mixed>|null $metadata
     * @return array<string, mixed>
     */
    private function decodeMetadata(string|array|null $metadata): array
    {
        if ($metadata === null) {
            return [];
        }

        // SQLite and PostgreSQL return JSON as a string
        if (is_string($metadata)) {
            /** @var array<string, mixed> */
            $decoded = json_decode($metadata, true, 512, JSON_THROW_ON_ERROR);

            return $decoded;
        }

        // MySQL returns JSON as an array already
        return $metadata;
    }

    private function formatDateTime(DateTimeImmutable $dateTime): string
    {
        if ($this->driver === 'sqlite') {
            return $dateTime->format('Y-m-d H:i:s');
        }

        return $dateTime->format('Y-m-d\TH:i:s.uP');
    }
}
