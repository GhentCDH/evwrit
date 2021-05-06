<?php
namespace App\Model;


use AppBundle\Model\IdElasticInterface;
use AppBundle\Model\IdJsonInterface;
use Illuminate\Database\Eloquent\Model;
use function Symfony\Component\String\u;

/**
 * Class LookupModel
 *
 * @property string $name
 * @package App\Model
 */
class IdNameModel extends BaseModel implements IdNameInterface
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