<?php

namespace App\Service\ElasticSearch\Search;

class LexicogrammerAnnotationSearchService extends AnnotationSearchService
{
    protected const indexName = "texts";

    protected array $allowedBaseAnnotationTypes = ['morpho_syntactical', 'lexis', 'morphology'];

    protected function getSearchFilterConfig(): array
    {
        $ret = parent::getSearchFilterConfig();
        $ret['annotations']['filters']['annotation_type']['defaultValue'] = ['morpho_syntactical', 'lexis', 'morphology'];
        return $ret;
    }

    protected function onInitAggregationConfig(array $arrAggregationConfigs, array $arrFilterValues): void
    {
    }

}