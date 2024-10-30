<?php


namespace App\Resource;


use App\Model\AbstractAnnotationModel;
use App\Model\GenericTextStructure;
use App\Model\Text;
use App\Model\TextSelection;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use JsonSerializable;
use function Symfony\Component\String\u;

class ElasticGenericTextStructureAnnotationResource extends BaseElasticAnnotationResource
{
    protected array $includeAttributes = ['part','partNumber', 'textLevel',];
    protected bool $generateContext = true;
    protected bool $allowEmptyRelationProperties = true;
}