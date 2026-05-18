<?php

declare(strict_types=1);

namespace Memora\Statut\Support;

readonly class IncidentDto
{
    public function __construct(
        public string $id,
        public ?int $monitorId,
        public string $monitorName,
        public string $startedAt,
        public ?string $endedAt,
        public bool $resolved,
        public ?string $cause,
    ) {
    }

    /**
     * Crée une instance à partir d'une réponse brute de l'API Robotalp.
     */
    public static function fromRobotalp(array $row): self
    {
        $monitor = $row['robot'] ?? $row['monitor'] ?? [];

        return new self(
            id:           (string) ($row['id'] ?? $row['incident_id'] ?? ''),
            monitorId:    isset($monitor['id']) ? (int) $monitor['id'] : (isset($row['robot_id']) ? (int) $row['robot_id'] : null),
            monitorName:  (string) ($monitor['name'] ?? ($row['robot_name'] ?? '')),
            startedAt:    (string) ($row['started_at'] ?? $row['start_time'] ?? ''),
            endedAt:      $row['ended_at'] ?? null,
            resolved:     (bool) ($row['resolved'] ?? ($row['status_id'] ?? 0) === 1),
            cause:        $row['cause'] ?? $row['reason'] ?? null,
        );
    }
}
