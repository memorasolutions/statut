<?php

declare(strict_types=1);

namespace Memora\Statut\Support;

readonly class MonitorDto
{
    public function __construct(
        public int|string $id,
        public string $name,
        public string $url,
        public string $type,
        public MonitorStatus $status,
        public ?int $responseTimeMs,
        public ?int $lastCheckAt,
        public ?array $uptime,
    ) {
    }

    /**
     * Crée une instance à partir d'une réponse brute de l'API Robotalp.
     *
     * Mapping Robotalp :
     *   status        : 1 = Actif, 2 = En pause
     *   availability  : 0 = En cours de vérification, 1 = Up, 2 = Down
     */
    public static function fromRobotalp(array $row): self
    {
        $status = match (true) {
            ($row['status'] ?? null) === 2 => MonitorStatus::PAUSED,
            ($row['availability'] ?? null) === 1 => MonitorStatus::UP,
            ($row['availability'] ?? null) === 2 => MonitorStatus::DOWN,
            default => MonitorStatus::UNKNOWN,
        };

        $lastRunMs = $row['last_run_time'] ?? null;

        return new self(
            id: $row['id'],
            name: (string) ($row['name'] ?? ''),
            url: (string) ($row['address'] ?? ''),
            type: (string) ($row['robot_type']['name'] ?? 'unknown'),
            status: $status,
            responseTimeMs: isset($row['monitoring_parameters']['response_time'])
                ? (int) $row['monitoring_parameters']['response_time']
                : null,
            lastCheckAt: is_int($lastRunMs) && $lastRunMs > 0 ? intdiv($lastRunMs, 1000) : null,
            uptime: [
                '24h' => $row['uptime_last_24_hours'] ?? null,
                '7d'  => $row['uptime_last_1_week'] ?? null,
                '30d' => $row['uptime_last_1_month'] ?? null,
                '90d' => $row['uptime_last_90_days'] ?? null,
            ],
        );
    }
}
