<?php

namespace App\Service\ElasticSearch\Search;

class OrthoTypoAnnotationSearchService extends AnnotationSearchService
{
    protected const indexName = "texts";

    protected function getSearchFilterConfig(): array
    {
        $ret = parent::getSearchFilterConfig();
        $ret['annotations']['filters']['annotation_type']['defaultValue'] = ['orthography', 'typography'];

        return $ret;
    }

    protected function getAggregationConfig(): array
    {
        $ret = parent::getAggregationConfig();
        $ret['annotation_type']['allowedValue'] = ['orthography', 'typography'];
        return $ret;
    }

}