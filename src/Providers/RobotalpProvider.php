<?php

declare(strict_types=1);

namespace Memora\Statut\Providers;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Memora\Statut\Contracts\MonitoringProvider;
use Memora\Statut\Exceptions\StatutProviderException;
use Memora\Statut\Support\Dashboard;
use Memora\Statut\Support\IncidentDto;
use Memora\Statut\Support\MonitorDto;
use Memora\Statut\Support\Overview;

final class RobotalpProvider implements MonitoringProvider
{
    /**
     * @param array{base_url?: string, api_key?: string, workspace_id?: int|string} $config
     */
    public function __construct(private readonly array $config)
    {
        if (empty($this->config['api_key'])) {
            throw new StatutProviderException(
                'Robotalp',
                0,
                "Clef API Robotalp manquante. Définissez STATUT_ROBOTALP_API_KEY dans votre fichier .env.",
            );
        }

        if (empty($this->config['workspace_id'])) {
            throw new StatutProviderException(
                'Robotalp',
                0,
                "Identifiant d'espace de travail Robotalp manquant. Définissez STATUT_ROBOTALP_WORKSPACE_ID dans votre fichier .env.",
            );
        }
    }

    private function http(): PendingRequest
    {
        return Http::baseUrl(rtrim((string) ($this->config['base_url'] ?? 'https://api.robotalp.com'), '/'))
            ->withHeaders([
                'Authorization' => 'Token '.(string) ($this->config['api_key'] ?? ''),
                'Accept'        => 'application/json',
            ])
            ->timeout(10)
            ->acceptJson();
    }

    /**
     * @template TReturn
     * @param  callable():TReturn  $cb
     * @return TReturn
     */
    private function remember(string $key, callable $cb): mixed
    {
        $ttl = (int) config('statut.cache_ttl_seconds', 60);

        return Cache::remember("statut.robotalp.{$key}", $ttl, $cb);
    }

    /**
     * @return array<string, mixed>
     */
    private function fetch(string $path, string $context): array
    {
        $response = $this->http()->get($path);

        if ($response->failed()) {
            throw new StatutProviderException(
                'Robotalp',
                $response->status(),
                "Erreur dans {$context} : ".($response->json('detail') ?? $response->json('message') ?? $response->body()),
            );
        }

        return (array) $response->json();
    }

    public function getOverview(): Overview
    {
        return $this->remember("overview.{$this->config['workspace_id']}", fn () => $this->buildOverview());
    }

    private function buildOverview(): Overview
    {
        $data    = $this->fetch("workspace/{$this->config['workspace_id']}/robots/status/", 'overview');
        $payload = (array) ($data['data'] ?? []);

        return new Overview(
            total:           (int) ($payload['total'] ?? 0),
            up:              (int) ($payload['up'] ?? 0),
            down:            (int) ($payload['down'] ?? 0),
            paused:          (int) ($payload['paused'] ?? 0),
            activeIncidents: (int) ($payload['active_incidents'] ?? 0),
        );
    }

    public function listMonitors(): array
    {
        return $this->remember("monitors.{$this->config['workspace_id']}", function (): array {
            $data = $this->fetch("workspace/{$this->config['workspace_id']}/robots/?per_page=100", 'listMonitors');
            $rows = (array) ($data['data'] ?? []);

            return array_map(static fn (array $r): MonitorDto => MonitorDto::fromRobotalp($r), $rows);
        });
    }

    public function getMonitor(int|string $id): MonitorDto
    {
        return $this->remember("monitor.{$id}", function () use ($id): MonitorDto {
            $data = $this->fetch("robot/{$id}/", "getMonitor({$id})");
            $row  = (array) ($data['data']['robot'] ?? $data['data'] ?? $data);

            return MonitorDto::fromRobotalp($row);
        });
    }

    public function getActiveIncidents(): array
    {
        return $this->remember("incidents.{$this->config['workspace_id']}", function (): array {
            $data = $this->fetch("workspace/{$this->config['workspace_id']}/incident/active/", 'getActiveIncidents');
            $rows = (array) ($data['data'] ?? []);

            return array_map(static fn (array $r): IncidentDto => IncidentDto::fromRobotalp($r), $rows);
        });
    }

    public function getMonitorDashboard(int|string $id): Dashboard
    {
        return $this->remember("dashboard.{$id}", function () use ($id): Dashboard {
            $data = $this->fetch("robot/{$id}/", "getMonitorDashboard({$id})");
            $row  = (array) ($data['data'] ?? $data);

            return Dashboard::fromRobotalp($row);
        });
    }
}
