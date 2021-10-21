<?php

namespace App\Service\ElasticSearch;

class LinguisticAnnotationSearchService extends AnnotationSearchService
{
    const indexName = "texts";

    protected function getSearchFilterConfig(): array
    {
        $ret = parent::getSearchFilterConfig();
        $ret['annotations']['filters']['annotation_type']['defaultValue'] = ['morpho_syntactical', 'lexis', 'morphology', 'orthography'];
        return $ret;
    }

    protected function getAggregationConfig(): array
    {
        $ret = parent::getAggregationConfig();
        $ret['annotation_type']['allowedValue'] = ['morpho_syntactical', 'lexis', 'morphology', 'orthography'];
        return $ret;
    }
}