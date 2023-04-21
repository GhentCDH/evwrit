<?php

namespace App\Service\ElasticSearch\Search;

class LanguageAnnotationSearchService extends AnnotationSearchService
{
    protected const indexName = "texts";

    protected function getSearchFilterConfig(): array
    {
        $ret = parent::getSearchFilterConfig();
        $ret['annotations']['filters']['annotation_type']['defaultValue'] = ['language'];

        return $ret;
    }

    protected function getAggregationConfig(): array
    {
        $ret = parent::getAggregationConfig();
        $ret['annotation_type']['allowedValue'] = ['language'];
        return $ret;
    }

}