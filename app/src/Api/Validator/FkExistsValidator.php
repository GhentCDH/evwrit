<?php

namespace App\Api\Validator;

use App\Model\AbstractModel;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class FkExistsValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof FkExists) {
            throw new UnexpectedTypeException($constraint, FkExists::class);
        }

        // Null / empty is handled by required/optional wrapping, not here.
        if ($value === null || $value === '') {
            return;
        }

        /** @var class-string<AbstractModel> $modelClass */
        $modelClass = $constraint->modelClass;

        $exists = $modelClass::query()->whereKey($value)->exists();

        if (!$exists) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ model }}', class_basename($modelClass))
                ->setParameter('{{ id }}', (string) $value)
                ->addViolation();
        }
    }
}
