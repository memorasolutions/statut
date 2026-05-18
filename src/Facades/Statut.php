<?php

declare(strict_types=1);

namespace Memora\Statut\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Façade d'accès au fournisseur de surveillance lié au conteneur.
 *
 * @see \Memora\Statut\Contracts\MonitoringProvider
 */
class Statut extends Facade
{
    /**
     * Obtenir le nom de la liaison du conteneur.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'statut';
    }
}
