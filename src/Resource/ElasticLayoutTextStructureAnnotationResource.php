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

class ElasticLayoutTextStructureAnnotationResource extends BaseElasticAnnotationResource
{
    protected $includeAttributes = ['part','partNumber'];
    protected $generateContext = false;
}