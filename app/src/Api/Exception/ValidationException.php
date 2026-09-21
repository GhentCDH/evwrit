<?php

namespace App\Api\Exception;

/**
 * Thrown when an incoming payload fails schema-derived validation.
 *
 * Carries a map of field name => list of error messages, surfaced to the client
 * in the JSend "data" payload.
 */
class ValidationException extends \RuntimeException
{
    /**
     * @param array<string, string[]> $errors
     */
    public function __construct(private readonly array $errors, string $message = 'Validation failed')
    {
        parent::__construct($message);
    }

    /**
     * @return array<string, string[]>
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
