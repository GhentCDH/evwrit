<?php
namespace App\Model;

use App\Model\AbstractModel;


/**
 * Class LookupModel
 *
 * @property string $name
 * @package App\Model
 */
class IdNameModelModel extends AbstractModel implements IdNameModelInterface
{
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->fillable = [ $this->getKeyName(), 'name' ];
    }

    public function __toString(): string
    {
        return $this->name;
    }

    public function getName(): string
    {
        return $this->name;
    }
}