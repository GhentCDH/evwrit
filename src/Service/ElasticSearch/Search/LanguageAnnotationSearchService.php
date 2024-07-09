<?php

namespace App\Service\ElasticSearch\Search;

class LanguageAnnotationSearchService extends AnnotationSearchService
{
    protected const indexName = "texts";

    protected array $allowedBaseAnnotationTypes = ['language'];

    protected function initSearchConfig(): array
    {
        $ret = parent::initSearchConfig();
        $ret['annotations']['filters']['annotation_type']['defaultValue'] = ['language'];

        return $ret;
    }

}