<?php

declare(strict_types=1);

namespace Memora\Statut\Support;

enum MonitorStatus: string
{
    case UP = 'up';
    case DOWN = 'down';
    case PAUSED = 'paused';
    case UNKNOWN = 'unknown';

    /**
     * Retourne la clé de traduction associée à ce statut.
     */
    public function label(): string
    {
        return "statut::status.{$this->value}";
    }
}
