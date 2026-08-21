<?php

namespace App\Exceptions;

use RuntimeException;

class CrmLookupException extends RuntimeException
{
    public function __construct(string $message, public readonly string $reason = 'invalid')
    {
        parent::__construct($message);
    }

    public function isTransient(): bool
    {
        return in_array($this->reason, ['unavailable', 'limit', 'configuration'], true);
    }
}
