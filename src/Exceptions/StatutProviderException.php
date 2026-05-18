<?php

declare(strict_types=1);

namespace Memora\Statut\Exceptions;

class StatutProviderException extends \RuntimeException
{
    public function __construct(
        public readonly string $provider,
        public readonly int $statusCode,
        string $message,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $statusCode, $previous);
    }
}
