<?php

namespace App\Service\ElasticSearch\Search;

class LanguageTypographyAnnotationSearchService extends AnnotationSearchService
{
    const indexName = "texts";

    protected function getSearchFilterConfig(): array
    {
        $ret = parent::getSearchFilterConfig();
        $ret['annotations']['filters']['annotation_type']['defaultValue'] = ['language', 'typography'];

        return $ret;
    }

    protected function getAggregationConfig(): array
    {
        $ret = parent::getAggregationConfig();
        $ret['annotation_type']['allowedValue'] = ['language', 'typography'];
        return $ret;
    }

}