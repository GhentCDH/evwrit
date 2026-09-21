<?php

namespace App\Api\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * Asserts that the given value is the primary key of an existing row of $modelClass.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::IS_REPEATABLE)]
class FkExists extends Constraint
{
    public string $message = 'The referenced {{ model }} ({{ id }}) does not exist.';

    /** @var class-string */
    public string $modelClass;

    public function __construct(string $modelClass, ?array $groups = null, mixed $payload = null)
    {
        parent::__construct([], $groups, $payload);
        $this->modelClass = $modelClass;
    }
}
