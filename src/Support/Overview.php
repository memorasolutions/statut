<?php

declare(strict_types=1);

namespace Memora\Statut\Support;

readonly class Overview
{
    public function __construct(
        public int $total,
        public int $up,
        public int $down,
        public int $paused,
        public int $activeIncidents,
    ) {
    }

    /**
     * Indique si tous les services sont opérationnels.
     */
    public function allOperational(): bool
    {
        return $this->down === 0 && $this->activeIncidents === 0;
    }
}
