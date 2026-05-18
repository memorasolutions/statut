<?php

declare(strict_types=1);

namespace Memora\Statut\Contracts;

use Memora\Statut\Support\Dashboard;
use Memora\Statut\Support\IncidentDto;
use Memora\Statut\Support\MonitorDto;
use Memora\Statut\Support\Overview;

/**
 * Interface unifiée pour interagir avec un fournisseur de surveillance externe.
 *
 * Toute nouvelle plateforme de monitoring (Robotalp, UptimeRobot, BetterStack,
 * etc.) doit implémenter ce contrat afin d'être interchangeable sans modifier
 * la page de statut elle-même.
 */
interface MonitoringProvider
{
    /**
     * Récupère un aperçu global des statuts de surveillance.
     *
     * @return Overview Contient les décomptes globaux (total, up, down, paused, incidents actifs).
     */
    public function getOverview(): Overview;

    /**
     * Liste tous les moniteurs configurés dans l'espace de travail.
     *
     * @return array<int, MonitorDto>
     */
    public function listMonitors(): array;

    /**
     * Récupère les détails d'un moniteur spécifique.
     */
    public function getMonitor(int|string $id): MonitorDto;

    /**
     * Récupère la liste des incidents actifs (non résolus).
     *
     * @return array<int, IncidentDto>
     */
    public function getActiveIncidents(): array;

    /**
     * Récupère le tableau de bord d'un moniteur (uptime par fenêtre temporelle).
     */
    public function getMonitorDashboard(int|string $id): Dashboard;
}
