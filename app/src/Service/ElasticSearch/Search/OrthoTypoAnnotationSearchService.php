<?php

namespace App\Service\ElasticSearch\Search;

class OrthoTypoAnnotationSearchService extends AnnotationSearchService
{
    protected const indexName = "texts";

    protected array $allowedBaseAnnotationTypes = ['orthography', 'typography'];

    protected function initSearchConfig(): array
    {
        $ret = parent::initSearchConfig();
        $ret['annotations']['filters']['annotation_type']['defaultValue'] = ['orthography', 'typography'];

        return $ret;
    }

}