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
            startedAt:    self::normalizeTimestamp($row['started_at'] ?? $row['start_time'] ?? null) ?? '',
            endedAt:      self::normalizeTimestamp($row['ended_at'] ?? null),
            resolved:     (bool) ($row['resolved'] ?? ($row['status_id'] ?? 0) === 1),
            cause:        $row['cause'] ?? $row['reason'] ?? null,
        );
    }

    /**
     * Normalise une valeur temporelle Robotalp en chaîne ISO 8601.
     *
     * L'API renvoie tantôt une chaîne ISO (« 2026-05-19T10:00:00Z »), tantôt
     * un entier Unix en secondes ou en millisecondes. Cette méthode unifie
     * tout cela en une chaîne ISO 8601 exploitable par Carbon::parse().
     */
    private static function normalizeTimestamp(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            $ts = (int) $value;
            if ($ts > 1_000_000_000_000) {
                $ts = intdiv($ts, 1000);
            }

            return gmdate('c', $ts);
        }

        return (string) $value;
    }
}
