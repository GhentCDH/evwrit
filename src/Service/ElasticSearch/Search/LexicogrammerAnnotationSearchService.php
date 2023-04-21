<?php

namespace App\Service\ElasticSearch\Search;

class LexicogrammerAnnotationSearchService extends AnnotationSearchService
{
    protected const indexName = "texts";

    protected function getSearchFilterConfig(): array
    {
        $ret = parent::getSearchFilterConfig();
        $ret['annotations']['filters']['annotation_type']['defaultValue'] = ['morpho_syntactical', 'lexis', 'morphology'];
        return $ret;
    }

    protected function getAggregationConfig(): array
    {
        $ret = parent::getAggregationConfig();
        $ret['annotation_type']['allowedValue'] = ['morpho_syntactical', 'lexis', 'morphology'];
        return $ret;
    }
}