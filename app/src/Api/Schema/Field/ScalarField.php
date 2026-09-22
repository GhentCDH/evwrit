<?php

namespace App\Api\Schema\Field;

use App\Api\Schema\Field\Concerns\FieldConfig;
use App\Api\Schema\ResourceResolver;
use App\Model\AbstractModel;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A plain scalar column (string, integer, boolean, number, text, date).
 *
 * Acts as its own fluent builder: the schema helpers create it and callers chain
 * ->required()->max()->colspan()->readonly() etc.
 */
class ScalarField implements FieldInterface
{
    use FieldConfig;

    public const TYPE_STRING = 'string';
    public const TYPE_INTEGER = 'integer';
    public const TYPE_BOOLEAN = 'boolean';
    public const TYPE_NUMBER = 'number';
    public const TYPE_TEXT = 'text';
    public const TYPE_DATE = 'date';

    private string $label;
    private string $widget;
    private int $colspan = 12;
    private bool $writable = true;
    private bool $required = false;
    private bool $nullable = true;
    private ?int $maxLength = null;

    public function __construct(
        private readonly string $id,
        private readonly string $dataType,
        ?string $label = null,
        ?string $widget = null,
    ) {
        $this->label = $label ?? self::humanize($id);
        $this->widget = $widget ?? self::defaultWidget($dataType);
    }

    public function label(string $label): static
    {
        $this->label = $label;

        return $this;
    }

    public function widget(string $widget): static
    {
        $this->widget = $widget;

        return $this;
    }

    public function colspan(int $colspan): static
    {
        $this->colspan = $colspan;

        return $this;
    }

    public function required(bool $required = true): static
    {
        $this->required = $required;
        if ($required) {
            $this->nullable = false;
        }

        return $this;
    }

    public function nullable(bool $nullable = true): static
    {
        $this->nullable = $nullable;

        return $this;
    }

    public function readonly(bool $readonly = true): static
    {
        $this->writable = !$readonly;

        return $this;
    }

    public function max(int $maxLength): static
    {
        $this->maxLength = $maxLength;

        return $this;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function isWritable(): bool
    {
        return $this->writable;
    }

    public function isRequiredForCreate(): bool
    {
        return $this->required;
    }

    public function getConstraints(): array
    {
        $constraints = [];

        // A required scalar must not be blank; an optional one may be null.
        if ($this->nullable) {
            $constraints[] = new Assert\AtLeastOneOf([
                new Assert\IsNull(),
                new Assert\Sequentially($this->valueConstraints()),
            ]);

            return $constraints;
        }

        return $this->valueConstraints();
    }

    /**
     * @return Constraint[]
     */
    private function valueConstraints(): array
    {
        $constraints = [];

        switch ($this->dataType) {
            case self::TYPE_INTEGER:
                $constraints[] = new Assert\Type('integer');
                break;
            case self::TYPE_NUMBER:
                $constraints[] = new Assert\Type('numeric');
                break;
            case self::TYPE_BOOLEAN:
                $constraints[] = new Assert\Type('bool');
                break;
            default: // string / text
                $constraints[] = new Assert\Type('string');
                if ($this->maxLength !== null) {
                    $constraints[] = new Assert\Length(max: $this->maxLength);
                }
        }

        return $constraints;
    }

    public function toSchemaArray(RouterInterface $router, ?ResourceResolver $resources = null): array
    {
        $column = [
            'id' => $this->getId(),
            'label' => $this->label,
            'type' => $this->schemaDataType(), // scalar type is a bare string
        ];
        if ($this->required) {
            $column['required'] = true;
        }
        $column = array_merge($column, $this->columnAttributes());
        if ($this->wantsFieldInput()) {
            $column['fieldInput'] = $this->buildFieldInput($this->widget, ['colspan' => $this->colspan]);
        }

        return $column;
    }

    public function readValue(AbstractModel $model): mixed
    {
        return $model->getAttribute($this->id);
    }

    public function writeValue(AbstractModel $model, mixed $input, string $op = 'create'): void
    {
        $model->setAttribute($this->id, $this->cast($input));
    }

    /**
     * Data type reported in the "type" block (widget differentiates the rendering);
     * long text is still string data.
     */
    private function schemaDataType(): string
    {
        return $this->dataType === self::TYPE_TEXT ? self::TYPE_STRING : $this->dataType;
    }

    private function cast(mixed $value): mixed
    {
        if ($value === null) {
            return null;
        }

        return match ($this->dataType) {
            self::TYPE_INTEGER => (int) $value,
            self::TYPE_NUMBER => $value + 0,
            self::TYPE_BOOLEAN => (bool) $value,
            default => (string) $value,
        };
    }

    private static function defaultWidget(string $dataType): string
    {
        return match ($dataType) {
            self::TYPE_TEXT => 'textarea',
            self::TYPE_INTEGER, self::TYPE_NUMBER => 'number',
            self::TYPE_BOOLEAN => 'boolean',
            self::TYPE_DATE => 'date',
            default => 'text', // string
        };
    }

    public static function humanize(string $id): string
    {
        return ucfirst(trim(preg_replace('/[_\s]+/', ' ', $id)));
    }
}
