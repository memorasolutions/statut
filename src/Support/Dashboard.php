<?php

declare(strict_types=1);

namespace Memora\Statut\Support;

readonly class Dashboard
{
    /**
     * @param array<string, float|null> $uptimeByWindow Disponibilité par fenêtre temporelle.
     *        Exemple : ['24h' => 100.0, '7d' => 99.8, '30d' => 99.5, '90d' => 99.2].
     */
    public function __construct(
        public array $uptimeByWindow,
        public ?int $statusFrom,
    ) {
    }

    /**
     * Crée une instance à partir d'une réponse brute de l'API Robotalp.
     */
    public static function fromRobotalp(array $row): self
    {
        return new self(
            uptimeByWindow: [
                '24h' => $row['uptime_last_24_hours'] ?? null,
                '7d'  => $row['uptime_last_1_week'] ?? null,
                '30d' => $row['uptime_last_1_month'] ?? null,
                '90d' => $row['uptime_last_90_days'] ?? null,
                '6m'  => $row['uptime_last_6_months'] ?? null,
            ],
            statusFrom: isset($row['status_from']) ? (int) $row['status_from'] : null,
        );
    }
}
