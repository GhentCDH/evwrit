<?php

namespace App\Service\ElasticSearch;

use Elastica\Aggregation;
use Elastica\Query;
use Elastica\Query\AbstractQuery;
use Elastica\Index;


abstract class AbstractSearchService extends AbstractService implements SearchServiceInterface
{
    const MAX_AGG = 2147483647;
    const MAX_SEARCH = 10000;
    const SEARCH_RAW_MAX_RESULTS = 500;

    protected const FILTER_NUMERIC = "numeric"; // numeric term filter
    protected const FILTER_BOOLEAN = "boolean"; // boolean term filter
    protected const FILTER_KEYWORD = "keyword"; // term filter
    protected const FILTER_WILDCARD = "wildcard"; // wildcard term filter

    protected const FILTER_TEXT = "text";
    protected const FILTER_TEXT_MULTIPLE = "text_multiple";

    protected const FILTER_OBJECT_ID = "object_id";
    protected const FILTER_NESTED_ID = "nested_id";

    protected const FILTER_NESTED_MULTIPLE = "nested_multiple";
    protected const FILTER_DATE_RANGE = "date_range";
    protected const FILTER_NUMERIC_RANGE_SLIDER = "numeric_range";

    protected const AGG_NUMERIC = "numeric";
    protected const AGG_KEYWORD = "exact_text";
    protected const AGG_NESTED_KEYWORD = "nested_term";
    protected const AGG_BOOLEAN = "bool";
    protected const AGG_GLOBAL_STATS = "stats";

    protected const AGG_NESTED_ID_NAME = "nested_id_name";
    protected const AGG_OBJECT_ID_NAME = "object_id_name";

    /**
     * Add aggregation details to search service
     * Return array of aggregation_field => [
     *  'type' => aggregation type
     * ]
     * @return array
     */
    protected abstract function getAggregationConfig(): array;

    /**
     * Add search filter details to search service
     * Return array of search_field => [
     *  'type' => aggregation type
     * ]
     * @return array
     */
    protected abstract function getSearchFilterConfig(): array;

    protected function getDefaultSearchParameters(): array
    {
        return [];
    }

    protected function getDefaultSearchFilters(): array
    {
        return [];
    }

    protected function sanitizeSearchParameters(array $params, bool $merge_defaults = true): array
    {
        // Set default parameters
        $defaults = $merge_defaults ? $this->getDefaultSearchParameters() : [];
        $result = array_intersect_key(
            $defaults,
            array_flip([
                'limit',
                'orderBy',
                'page',
                'ascending'
            ])
        );

        // Pagination
        if (isset($params['limit']) && is_numeric($params['limit'])) {
            $result['limit'] = $params['limit'];
        }
        if (isset($params['page']) && is_numeric($params['page'])) {
            $result['page'] = $params['page'];
        }

        // Sorting
        if (isset($params['orderBy'])) {
            if (isset($params['ascending']) && ($params['ascending'] == '0' || $params['ascending'] == '1')) {
                $result['ascending'] = intval($params['ascending']);
            }
            $result['orderBy'] = $params['orderBy'];
        }

        return $result;
    }

    protected function sanitizeSearchFilter($filterValue, $filterConfig, $filterName, $params) {
        $ret = null;

        switch ($filterConfig['type'] ?? false) {
            case self::FILTER_NUMERIC:
            case self::FILTER_OBJECT_ID:
            case self::FILTER_NESTED_ID:
                if ($filterValue === false) break;
                if (is_array($filterValue)) {
                    $ret = array_map( fn($value) => (int) $value , $filterValue);
                }
                if (is_numeric($filterValue)) {
                    $ret = (int) $filterValue;
                }
                break;
            case self::FILTER_BOOLEAN:
                if ($filterValue === false) break;
                $ret = ($filterValue === '1');
                break;
            case self::FILTER_DATE_RANGE:
                $rangeFilter = [];

                $valueField = $filterConfig['floorField'];
                if (isset($params[$valueField]) && is_numeric($params[$valueField])) {
                    $rangeFilter['floor'] = $params[$valueField];
                }

                $valueField = $filterConfig['ceilingField'];
                if (isset($params[$valueField]) && is_numeric($params[$valueField])) {
                    $rangeFilter['ceiling'] = $params[$valueField];
                }

                $valueField = $filterConfig['typeField'];
                if (isset($params[$valueField]) && in_array($params[$valueField], ['exact','included','include','overlap'], true)) {
                    $rangeFilter['type'] = $params[$valueField];
                }

                if ($rangeFilter) {
                    $ret = $rangeFilter;
                }

                break;
            case self::FILTER_NUMERIC_RANGE_SLIDER:
                $rangeFilter = [];
                $ignore = $filterConfig['ignore'] ?? [];
                $ignore = is_array($ignore) ? $ignore : [ $ignore ];

                $value = $filterValue[0] ?? null;
                if ( is_numeric($value) && !in_array(floatval($value), $ignore)) {
                    $rangeFilter['floor'] = floatval($value);
                }

                $value = $filterValue[1] ?? null;
                if ( is_numeric($value) && !in_array(floatval($value), $ignore)) {
                    $rangeFilter['ceiling'] = floatval($value);
                }

                if ($rangeFilter) {
                    $ret = $rangeFilter;
                }

                break;
            case self::FILTER_TEXT_MULTIPLE:
                if ($filterValue === false) break;
                if (is_array($filterValue)) {
                    $ret = $filterValue;
                }
                break;
            case self::FILTER_TEXT:
                if ($filterValue === false) break;
                if (is_array($filterValue)) {
                    $ret = $filterValue;
                }
                if (is_string($filterValue)) {
                    $combination = $params[$filterName . '_combination'] ?? 'any';
                    $combination = in_array($combination, ['any', 'all', 'phrase'], true) ? $combination : 'any';

                    $ret = [
                        'text' => $filterValue,
                        'combination' => $combination
                    ];
                }
                break;
            default:
                if ($filterValue === false) break;
                if (is_string($filterValue)) {
                    $ret = $filterValue;
                }
                if (is_array($filterValue)) {
                    $ret = $filterValue;
                }
                break;
        }
        return $ret;
    }

    protected function sanitizeSearchFilters(array $params): array
    {
        // Init Filters
        $filterDefaults = $this->getDefaultSearchFilters();
        $filters = $filterDefaults;

        // Validate values
        $filterConfigs = $this->getSearchFilterConfig();

        foreach ($filterConfigs as $filterName => $filterConfig) {
            // filterValue = fixed value || query value || default value || false
            $filterValue = $filterConfig['value'] ?? $params[$filterName] ?? $filterConfig['defaultValue'] ?? false;

            // filter has subfilters?
            if ($filterConfig['filters'] ?? false) {
                foreach ($filterConfig['filters'] as $subFilterName => $subFilterConfig) {
                    // filterValue = fixed value || query value || default value || false
                    $subFilterValue = $subFilterConfig['value'] ?? $params[$subFilterName] ?? $subFilterConfig['defaultValue'] ?? false;
                    if ($subFilterValue === false) continue;
                    if ($subFilterConfig)
                        $ret = $this->sanitizeSearchFilter($subFilterValue, $subFilterConfig, $subFilterName, $params);
                    if (!is_null($ret)) {
                        $filters[$subFilterName] = $ret;
                    }
                }
            } else {
                // no subfilters
                $filterValue = $this->sanitizeSearchFilter($filterValue, $filterConfig, $filterName, $params);
                if ( !is_null($filterValue) ) {
                    $filters[$filterName] = $filterValue;
                }
            }
        }

        return $filters;
    }

    protected function sanitizeSearchResult(array $result) {
        return $result;
    }

    public function searchRaw(array $params = null, array $fields = null): array
    {
        // sanitize search parameters
        $searchParams = $this->sanitizeSearchParameters($params, false);

        // Construct query
        $query = new Query();

        // Number of results
        if (isset($searchParams['limit']) && is_numeric($searchParams['limit'])) {
            $query->setSize(min($searchParams['limit'], static::SEARCH_RAW_MAX_RESULTS)); //todo; fix this!
        } else {
            $query->setSize(static::SEARCH_RAW_MAX_RESULTS);
        }

        // Pagination
        if (isset($searchParams['page']) && is_numeric($searchParams['page']) &&
            isset($searchParams['limit']) && is_numeric($searchParams['limit'])
        ) {
            $query->setFrom(($searchParams['page'] - 1) * $searchParams['limit']);
        }

        // Sorting
        if (isset($searchParams['orderBy'])) {
            if (isset($searchParams['ascending']) && $searchParams['ascending'] == 0) {
                $order = 'desc';
            } else {
                $order = 'asc';
            }
            $sort = [];
            foreach ($searchParams['orderBy'] as $field) {
                $sort[] = [$field => $order];
            }
            $query->setSort($sort);
        }

        // Set result fields
        // todo: better use fields option?
        if ( $fields ) {
            $query->setSource($fields);
        }

        // Filtering
        $searchFilters = $this->sanitizeSearchFilters($params['filters'] ?? []);
        if ( count($searchFilters) ) {
            $searchQuery = $this->createSearchQuery($searchFilters);
            $query->setQuery($searchQuery);
        }

        // Search
        $data = $this->getIndex()->search($query)->getResponse()->getData();

        // Format response
        $response = [
            'count' => $data['hits']['total']['value'] ?? 0,
            'data' => [],
        ];

        // Build array to remove _stemmer or _original blow
        $rename = [];
        $filterConfigs = $this->getSearchFilterConfig();
        foreach ($filterConfigs as $filterName => $filterConfig)
        {
            switch ($filterConfig['type'] ?? null) {
                case self::FILTER_TEXT:
                    $filterValue = $filterValues[$filterName] ?? null;
                    if (isset($filterValue['field'])) {
                        $rename[$filterValue['field']] = explode('_', $filterValue['field'])[0];
                    }
                    break;
                case self::FILTER_TEXT_MULTIPLE:
                    $filterValues = $filterValues[$filterName] ?? null;
                    if ( is_array($filterValues) ) {
                        foreach($filterValues as $filterValue) {
                            if (isset($filterValue['field'])) {
                                $rename[$filterValue['field']] = explode('_', $filterValue['field'])[0];
                            }
                        }
                    }
                    break;
            }
        }
        foreach ( ($data['hits']['hits'] ?? []) as $result) {
            $part = $result['_source'];
            // Remove _stemmer or _original
            foreach ($rename as $key => $value) {
                if (isset($part[$key])) {
                    $part[$value] = $part[$key];
                    unset($part[$key]);
                }
                if (isset($part['original_' . $key])) {
                    $part['original_' . $value] = $part['original_' . $key];
                    unset($part['original_' . $key]);
                }
            }

            // add inner_hits
            if ( isset($result['inner_hits']) ) {
                $part['inner_hits'] = [];
                foreach( $result['inner_hits'] as $field_name => $inner_hit ) {
                    $values = [];
                    foreach($inner_hit['hits']['hits'] as $hit) {
                        $values[] = $hit['_source'];
                    }
                    $part['inner_hits'][$field_name] = $values;
                }
            }

            // sanitize result
            $response['data'][] = $part;
        }

        unset($data);

        return $response;
    }

    protected function search(array $params = null): array
    {
        // sanitize search parameters
        $searchParams = $this->sanitizeSearchParameters($params);

        // Construct query
        $query = new Query();
        // Number of results
        if (isset($searchParams['limit']) && is_numeric($searchParams['limit'])) {
            $query->setSize($searchParams['limit']);
        }

        // Pagination
        if (isset($searchParams['page']) && is_numeric($searchParams['page']) &&
            isset($searchParams['limit']) && is_numeric($searchParams['limit'])
        ) {
            $query->setFrom(($searchParams['page'] - 1) * $searchParams['limit']);
        }

        // Sorting
        if (isset($searchParams['orderBy'])) {
            if (isset($searchParams['ascending']) && $searchParams['ascending'] == 0) {
                $order = 'desc';
            } else {
                $order = 'asc';
            }
            $sort = [];
            foreach ($searchParams['orderBy'] as $field) {
                $sort[] = [$field => $order];
            }
            $query->setSort($sort);
        }

        // Filtering
//        dump($params);
        $searchFilters = $this->sanitizeSearchFilters($params['filters'] ?? []);
        if ( count($searchFilters) ) {
//            dump($searchFilters);
            $searchQuery = $this->createSearchQuery($searchFilters);
            $query->setQuery($searchQuery);
            $query->setHighlight($this->createHighlight($searchFilters));
//            dump(json_encode($query->toArray(), JSON_PRETTY_PRINT));
        }

        // Search
        $data = $this->getIndex()->search($query)->getResponse()->getData();

        // Format response
        $response = [
            'count' => $data['hits']['total']['value'] ?? 0,
            'data' => [],
            'search' => $searchParams,
            'filters' => $searchFilters
        ];

        // Build array to remove _stemmer or _original blow
        $rename = [];
        $filterConfigs = $this->getSearchFilterConfig();
        foreach ($filterConfigs as $filterName => $filterConfig)
        {
            switch ($filterConfig['type'] ?? null) {
                case self::FILTER_TEXT:
                    $filterValue = $filterValues[$filterName] ?? null;
                    if (isset($filterValue['field'])) {
                        $rename[$filterValue['field']] = explode('_', $filterValue['field'])[0];
                    }
                    break;
                case self::FILTER_TEXT_MULTIPLE:
                    $filterValues = $filterValues[$filterName] ?? null;
                    if ( is_array($filterValues) ) {
                        foreach($filterValues as $filterValue) {
                            if (isset($filterValue['field'])) {
                                $rename[$filterValue['field']] = explode('_', $filterValue['field'])[0];
                            }
                        }
                    }
                    break;
            }
        }
        foreach ( ($data['hits']['hits'] ?? []) as $result) {
            $part = $result['_source'];
            if (isset($result['highlight'])) {
                foreach ($result['highlight'] as $key => $value) {
                    $part['original_' . $key] = $part[$key];
                    $part[$key] = self::formatHighlight($value[0]);
                }
            }
            // Remove _stemmer or _original
            foreach ($rename as $key => $value) {
                if (isset($part[$key])) {
                    $part[$value] = $part[$key];
                    unset($part[$key]);
                }
                if (isset($part['original_' . $key])) {
                    $part['original_' . $value] = $part['original_' . $key];
                    unset($part['original_' . $key]);
                }
            }

            // add inner_hits
            if ( isset($result['inner_hits']) ) {
                $part['inner_hits'] = [];
                foreach( $result['inner_hits'] as $field_name => $inner_hit ) {
                    $values = [];
                    foreach($inner_hit['hits']['hits'] ?? [] as $hit) {
                        if ( $hit['_source'] ?? false ) {
                            $values[] = $hit['_source'];
                        }
                    }
                    $part['inner_hits'][$field_name] = $values;
                }
            }

            // sanitize result
            $response['data'][] = $this->sanitizeSearchResult($part);
        }

        return $response;
    }


    /**
     * Return filters that are used in aggregations
     * Todo: Now based on aggregation type or config (mostly nested), should be based on aggregation config check!
     */
    private function getAggregationFilters(): array {

        $filters = $this->getSearchFilterConfig();
        $aggOrFilters = [];
        foreach( $filters as $filterName => $filterConfig ) {
            $filterType = $filterConfig['type'];
            switch($filterType) {
                case self::FILTER_NESTED_ID:
                case self::FILTER_OBJECT_ID:
                case self::FILTER_NESTED_MULTIPLE:
                    if ( ($aggConfig['aggregationFilter'] ?? true) ) {
                        $aggOrFilters[$filterName] = $filterConfig;
                    }
                    break;
            }
        }

        return $aggOrFilters;
    }

    protected function aggregate(array $filterValues): array
    {
        // get agg config
        $aggConfigs = $this->getAggregationConfig();
//        dump($aggConfigs);
        if ( !count($aggConfigs) ) {
            return [];
        }

        // sanitize filter values
        $filterValues = $this->sanitizeSearchFilters($filterValues);

        // get filters used in multiselect aggregations
        $aggFilterConfigs = $this->getAggregationFilters($filterValues);

        // create global search query
        // exclude filters used in multiselect aggregations, will be added as aggregation filters
        $query = (new Query())
            ->setQuery($this->createSearchQuery($filterValues, array_keys($aggFilterConfigs)))
            ->setSize(0); // Only aggregation will be used

        // create global aggregation (unfiltered, full dataset)
        // global aggregations will be added as sub-aggregations to this aggregation
        $aggGlobalQuery = new Aggregation\GlobalAggregation("global_aggregation");
        $query->addAggregation($aggGlobalQuery);

        // walk aggregation configs
        foreach($aggConfigs as $aggName => $aggConfig) {
            // aggregation type
            $aggType = $aggConfig['type'];

            // global aggregation
            $aggIsGlobal = $this->isGlobalAggregation($aggConfig);

            // aggregation field = field property or config name
            $aggField = $aggConfig['field'] ?? $aggName;

            // query root
            $aggParentQuery = $aggIsGlobal ? $aggGlobalQuery : $query;

            // add aggregation filter (if not global)
            // - remove excludeFilter
            // - don't filter myself
            if ( !$aggIsGlobal ) {
                $aggSearchFilters = array_diff_key($aggFilterConfigs, array_flip($aggConfig['excludeFilter'] ?? []));
                unset($aggSearchFilters[$aggName]);

                if (count($aggSearchFilters)) {
                    $filterQuery = $this->createSearchQuery($filterValues, [], $aggSearchFilters);
                    if ( $filterQuery->count() ) {
                        $aggSubQuery = new Aggregation\Filter($aggName);
                        $aggSubQuery->setFilter($filterQuery);

                        $aggParentQuery->addAggregation($aggSubQuery);
                        $aggParentQuery = $aggSubQuery;
                    }
                }
            }

            // nested aggregation?
            $aggIsNested = $this->isNestedAggregation($aggConfig);
            if ( $aggIsNested ) {
                // add nested path to filed
                $aggNestedPath = $aggConfig['nested_path'] ?? $aggName;
                $aggField = isset($aggConfig['nested_path']) ? $aggConfig['nested_path'].'.'.$aggField : $aggField;

                // add nested aggregation
                $aggSubQuery = new Aggregation\Nested($aggName, $aggNestedPath);
                $aggParentQuery->addAggregation($aggSubQuery);
                $aggParentQuery = $aggSubQuery;

                // prepare possible aggregation filter
                $filterQuery = new Query\BoolQuery();
                $filterCount = 0;

                // aggregation has filter config?
                $aggNestedFilter = $aggConfig['filter'] ?? [];
                unset($aggNestedFilter[$aggName]); // do not filter myself
                $aggNestedFilter = array_intersect_key($aggNestedFilter, $filterValues); // only add filters with values

                if ($aggNestedFilter) {
                    foreach($aggNestedFilter as $queryFilterField => $aggFilterConfig ) {
                        $filterCount++;
                        $aggFilterConfig = is_string($aggFilterConfig) ? [ 'field' => $aggFilterConfig, 'type' => self::FILTER_KEYWORD ] : $aggFilterConfig;
                        $aggFilterConfig['name'] = $queryFilterField;
                        $aggFilterField = $aggFilterConfig['field'];
                        $aggFilterType = $aggFilterConfig['type'];

                        switch ($aggFilterType) {
                            case self::FILTER_OBJECT_ID:
                                $aggFilterField .= '.id';
                                break;
                        }

                        if ( is_array($filterValues[$queryFilterField]) ) {
                            $filterQuery->addFilter(
                                new Query\Terms($aggNestedPath.'.'.$aggFilterField, $filterValues[$queryFilterField])
                            );
                        } else {
                            $filterQuery->addFilter(
                                (new Query\Term())
                                    ->setTerm($aggNestedPath.'.'.$aggFilterField, $filterValues[$queryFilterField])
                            );
                        }
                    }
                }

                // aggregation has a limit on allowed values?
                if ( $aggConfig['allowedValue'] ?? false ) {
                    $filterCount++;
                    $allowedValue = $aggConfig['allowedValue'];
                    if ( is_array($allowedValue) ) {
                        $filterQuery->addFilter(
                            new Query\Terms($aggField, $allowedValue)
                        );
                    } else {
                        $filterQuery->addFilter(
                            (new Query\Term())
                                ->setTerm($aggField, $allowedValue)
                        );
                    }
                }

                // filter aggregation?
                if ( $filterCount ) {
                    $aggSubQuery = new Aggregation\Filter($aggName);
                    $aggSubQuery->setFilter($filterQuery);

                    $aggParentQuery->addAggregation($aggSubQuery);
                    $aggParentQuery = $aggSubQuery;
                }

            }

            // add aggregation
            $aggTerm = null;
            switch($aggType) {
                case self::AGG_GLOBAL_STATS:
                    $aggParentQuery->addAggregation(
                        (new Aggregation\Stats($aggName))
                            ->setField($aggField)
                    );
                    break;
                case self::AGG_KEYWORD:
                    $aggField = $aggField.'.keyword';

                    $aggTerm = (new Aggregation\Terms($aggName))
                        ->setSize(self::MAX_AGG)
                        ->setField($aggField);
                    $aggParentQuery->addAggregation($aggTerm);

                    break;
                case self::AGG_BOOLEAN:
                case self::AGG_NUMERIC:
                    $aggTerm = (new Aggregation\Terms($aggName))
                        ->setSize(self::MAX_AGG)
                        ->setField($aggField);
                    $aggParentQuery->addAggregation($aggTerm);

                    break;
                case self::AGG_OBJECT_ID_NAME:
                case self::AGG_NESTED_ID_NAME:
                    $aggField = $aggField.'.id_name.keyword';

                    $aggTerm = (new Aggregation\Terms($aggName))
                        ->setSize(self::MAX_AGG)
                        ->setField($aggField);
                    $aggParentQuery->addAggregation($aggTerm);

                    break;
            }

            // count top documents?
            if ( $aggIsNested && $aggTerm ) {
                $aggTerm->addAggregation( new Aggregation\ReverseNested('top_reverse_nested') );
            }

        }

//        dump(json_encode($query->toArray(),JSON_PRETTY_PRINT));

        // parse query result
        $searchResult = $this->getIndex()->search($query);
        $results = [];

        $arrAggData = $searchResult->getAggregations();
//        dump($arrAggData);

        foreach($aggConfigs as $aggName => $aggConfig) {
            $aggType = $aggConfig['type'];

            // get aggregation results
            $aggResults = $arrAggData['global_aggregation'][$aggName] ?? $arrAggData[$aggName] ?? [];

            // local/global filtered?
            while ( isset($aggResults[$aggName]) ) {
                $aggResults = $aggResults[$aggName];
            }
            $aggResults = $aggResults['buckets'] ?? $aggResults;

            switch($aggType) {
                case self::AGG_GLOBAL_STATS:
                    $results[$aggName] = $aggResults;
                    break;
                case self::AGG_NUMERIC:
                case self::AGG_KEYWORD:
                    foreach ($aggResults as $result) {
                        if ( !isset($result['key']) ) continue;
                        if ( count($aggConfig['limitValue'] ?? []) && !in_array($result['key'], $aggConfig['limitValue'], true) ) {
                            continue;
                        }
                        $results[$aggName][] = [
                            'id' => $result['key'],
                            'name' => str_replace("morpho_syntactical", "syntax", $result['key']), // todo: DIRTY QUICK FIX!!
                            'count' => $result['top_reverse_nested']['doc_count'] ?? $result['doc_count']
                        ];
                    }
//                    $this->sortAggregationResult($results[$aggName]);
                    break;
                case self::AGG_OBJECT_ID_NAME:
                case self::AGG_NESTED_ID_NAME:
                    foreach ($aggResults as $result) {
                        if ( !isset($result['key']) ) continue;
                        $parts = explode('_',$result['key'],2);
                        // limitValue/limitId?
                        if ( count($aggConfig['limitId'] ?? []) && !in_array((int) $parts[0], $aggConfig['limitId'], true) ) {
                            continue;
                        }
                        if ( count($aggConfig['limitValue'] ?? []) && !in_array((int) $parts[1], $aggConfig['limitValue'], true) ) {
                            continue;
                        }
                        // ignoreValue?
                        if ( count($aggConfig['ignoreValue'] ?? []) && in_array($parts[1], $aggConfig['ignoreValue'], true) ) {
                            continue;
                        }

                        $results[$aggName][] = [
                            'id' => (int) $parts[0],
                            'name' => $parts[1],
                            'count' => $result['top_reverse_nested']['doc_count'] ?? $result['doc_count']
                        ];
                    }
//                    $this->sortAggregationResult($results[$aggName]);
                    break;
                case self::AGG_BOOLEAN:
                    foreach ($aggResults as $result) {
                        if ( !isset($result['key']) ) continue;
                        $results[$aggName][] = [
                            'id' => $result['key'],
                            'name' => $result['key_as_string'],
                            'count' => $result['top_reverse_nested']['doc_count'] ?? $result['doc_count']
                        ];
                    }
                    break;
            }
        }

        return $results;
    }

    public function searchAndAggregate(array $params): array
    {
        // search
        $result = $this->search($params);

        // aggregate
        $result['aggregation'] = $this->aggregate($params['filters'] ?? []);

        return $result;
    }

    protected function addFieldQuery(Query\BoolQuery $query, array $filterConfig, array $filters) {
        $filterName = $filterConfig['name'];
        $filterValue = $filterConfig['value'] ?? $filters[$filterName] ?? $filters['defaultValue'] ?? null; // filter can have fixed value
        $filterType = $filterConfig['type'];
        $filterField = $filterConfig['field'] ?? $filterName;
        $filterPath = $filterConfig['nested_path'] ?? $filterName;

        // skip config if no subfilters and no filter value
        if ( !isset($filterConfig['filters']) && !$filterValue ) {
            return;
        }

        $boolIsNestedFilter = $this->isNestedFilter($filterConfig);
        if ( $boolIsNestedFilter ) {
            $filterField = isset($filterConfig['nested_path']) ? $filterConfig['nested_path'].'.'.$filterField : $filterField;

            $subquery = new Query\BoolQuery();
            $queryNested = (new Query\Nested())
                ->setPath($filterPath)
                ->setQuery($subquery);

            // inner hits?
            if ($filterConfig['innerHits'] ?? false) {
                $innerHits = new Query\InnerHits();
                if ( $filterConfig['innerHits']['size'] ?? false ) {
                    $innerHits->setSize($filterConfig['innerHits']['size']);
                }
                $queryNested->setInnerHits($innerHits);
            }

            $query->addMust($queryNested);
            $query = $subquery;
        }

        switch ($filterType) {
            case self::FILTER_OBJECT_ID:
                $filterField .= '.id';
            case self::FILTER_KEYWORD: // includes FILTER_OBJECT_ID
                // If value == -1, select all entries without a value for a specific field
                if ($filterValue === -1) {
                    $query->addMustNot(
                        new Query\Exists($filterField)
                    );
                    break;
                }
            case self::FILTER_NUMERIC: // includes FILTER_OBJECT_ID & FILTER_KEYWORD
                if (is_array($filterValue)) {
                    $filterQuery = new Query\Terms($filterField, $filterValue);
                    $query->addMust($filterQuery);
                } else {
                    $filterQuery = new Query\Term();
                    $filterQuery->setTerm($filterField, $filterValue);
                    $query->addMust($filterQuery);
                }
                break;
            case self::FILTER_BOOLEAN:
                $filterQuery = new Query\Term();
                $filterQuery->setTerm($filterField, $filterValue);

                $query->addMust( $filterQuery );
                break;
            case self::FILTER_WILDCARD:
                $filterQuery = new Query\Wildcard($filterField, $filterValue);
                $query->addMust( $filterQuery );
                break;
            case self::FILTER_TEXT:
                $query->addMust(self::constructTextQuery($filterField, $filterValue));
                break;
            case self::FILTER_TEXT_MULTIPLE:
                $subQuery = new Query\BoolQuery();
                foreach ($filterValue as $field => $value) {
                    $subQuery->addShould(self::constructTextQuery($field, $value));
                }
                $query->addMust($subQuery);
                break;
            case self::FILTER_NUMERIC_RANGE_SLIDER:
                $floorField = $filterConfig['floorField'] ?? $filterName;
                $ceilingField = $filterConfig['ceilingField'] ?? $filterName;

                if (isset($filterValue['floor'])) {
                    $query->addMust(
                        (new Query\Range())
                            ->addField($floorField, ['gte' => $filterValue['floor']])
                    );
                }
                if (isset($filterValue['ceiling'])) {
                    $query->addMust(
                        (new Query\Range())
                            ->addField($ceilingField, ['lte' => $filterValue['ceiling']])
                    );
                }
                break;
            case self::FILTER_DATE_RANGE:
                // The data interval must exactly match the search interval
                if (isset($filterValue['type']) && $filterValue['type'] == 'exact') {
                    if (isset($filterValue['floor'])) {
                        $query->addMust(
                            new Query\Match($filterConfig['floorField'], $filterValue['floor'])
                        );
                    }
                    if (isset($filterValue['ceiling'])) {
                        $query->addMust(
                            new Query\Match($filterConfig['ceilingField'], $filterValue['ceiling'])
                        );
                    }
                }

                // The data interval must be included in the search interval
                if (isset($filterValue['type']) && $filterValue['type'] == 'included') {
                    if (isset($filterValue['floor'])) {
                        $query->addMust(
                            (new Query\Range())
                                ->addField($filterConfig['floorField'], ['gte' => $filterValue['floor']])
                        );
                    }
                    if (isset($filterValue['ceiling'])) {
                        $query->addMust(
                            (new Query\Range())
                                ->addField($filterConfig['ceilingField'], ['lte' => $filterValue['ceiling']])
                        );
                    }
                }

                // The data interval must include the search interval
                // If only start or end: exact match with start or end
                // range must be between floor and ceiling
                if (isset($filterValue['type']) && $filterValue['type'] == 'include') {
                    if (isset($filterValue['floor']) && isset($filterValue['ceiling'])) {
                        $query->addMust(
                            (new Query\Range())
                                ->addField($filterConfig['floorField'], ['lte' => $filterValue['floor']])
                        );
                        $query->addMust(
                            (new Query\Range())
                                ->addField($filterConfig['ceilingField'], ['gte' => $filterValue['ceiling']])
                        );
                    }
                }
                // The data interval must overlap with the search interval
                // floor or ceiling must be within range, or range must be between floor and ceiling
                if (isset($filterValue['type']) && $filterValue['type'] == 'overlap') {
                    $args = [];
                    if (isset($filterValue['floor'])) {
                        $args['gte'] = $filterValue['floor'];
                    }
                    if (isset($filterValue['ceiling'])) {
                        $args['lte'] = $filterValue['ceiling'];
                    }
                    $subQuery = (new Query\BoolQuery())
                        // floor
                        ->addShould(
                            (new Query\Range())
                                ->addField(
                                    $filterConfig['floorField'],
                                    $args
                                )
                        )
                        // ceiling
                        ->addShould(
                            (new Query\Range())
                                ->addField(
                                    $filterConfig['ceilingField'],
                                    $args
                                )
                        );
                    if (isset($filterValue['floor']) && isset($filterValue['ceiling'])) {
                        $subQuery
                            // between floor and ceiling
                            ->addShould(
                                (new Query\BoolQuery())
                                    ->addMust(
                                        (new Query\Range())
                                            ->addField($filterConfig['floorField'], ['lte' => $filterValue['floor']])
                                    )
                                    ->addMust(
                                        (new Query\Range())
                                            ->addField($filterConfig['ceilingField'], ['gte' => $filterValue['ceiling']])
                                    )
                            );
                    }
                    $query->addMust(
                        $subQuery
                    );
                }
                break;
            case self::FILTER_NESTED_ID:
                $filterFieldId = $filterField.".id";

                // multiple values?
                if ( is_array($filterValue) ) {
                    $subquery = new Query\BoolQuery();
                    foreach( $filterValue as $val) {
                        $subquery->addShould(['match' => [$filterFieldId => $val]]);
                    }

//                    // create nested query
//                    $queryNested = (new Query\Nested())
//                        ->setPath($filterPath)
//                        ->setQuery($subquery);
//
//                    // inner hits?
//                    if ($filterConfig['innerHits'] ?? false) {
//                        $innerHits = new Query\InnerHits();
//                        if ( $filterConfig['innerHits']['size'] ?? false ) {
//                            $innerHits->setSize($filterConfig['innerHits']['size']);
//                        }
//                        $queryNested->setInnerHits($innerHits);
//                    }

                    $query->addMust($subquery);
                }
                // single value
                else {
                    // If value == -1, select all entries without a value for a specific field
                    if ($filterValue == -1) {
                        $query->addMustNot(new Query\Exists($filterField));
                    } else {
                        $query->addMust(['match' => [$filterFieldId => $filterValue]]);
                    }
                }
                break;
            case self::FILTER_NESTED_MULTIPLE:
                $filterPath = $filterConfig['nested_path'] ?? $filterName;

                // subfilters with values
                $subFilters = array_intersect_key($filterConfig['filters'] ?? [], $filters);

                // add subfilters
                if (count($subFilters)) {
                    $subquery = new Query\BoolQuery();
                    foreach($subFilters as $subFilterName => $subFilterConfig) {
//                        $subFilterValue = $subFilterConfig['value'] ?? $filters[$subFilterName] ?? $subFilterConfig['defaultValue'] ?? null;
                        $subFilterConfig['field'] = $subFilterConfig['field'] ?? $subFilterName;
                        $subFilterConfig['field'] = isset($filterConfig['nested_path']) ? $filterConfig['nested_path'].'.'.$subFilterConfig['field'] : $subFilterConfig['field'];
                        $subFilterConfig['type'] = $subFilterConfig['type'] ?? self::FILTER_KEYWORD;
                        $subFilterConfig['name'] = $subFilterName;

                        $this->addFieldQuery($subquery, $subFilterConfig, $filters);
                    }

//                    // create nested query
//                    $queryNested = (new Query\Nested())
//                        ->setPath($filterPath)
//                        ->setQuery($subquery);
//
//                    // inner hits?
//                    if ($filterConfig['innerHits'] ?? false) {
//                        $innerHits = new Query\InnerHits();
//                        if ( $filterConfig['innerHits']['size'] ?? false ) {
//                            $innerHits->setSize($filterConfig['innerHits']['size']);
//                        }
//                        $queryNested->setInnerHits($innerHits);
//                    }

                    $query->addMust($subquery);
                }
                break;
        }
    }

    protected function createSearchQuery(array $filters, array $excludeConfigs = null, $useOnlyConfigs = null): Query\BoolQuery
    {
        $filterConfigs = $useOnlyConfigs ?? $this->getSearchFilterConfig();

        // parent query
        $query = new Query\BoolQuery();

        // walk filter configs
        foreach ($filterConfigs as $filterName => $filterConfig) {
                        // skip excluded filters
            if ( $excludeConfigs && in_array($filterName, $excludeConfigs, true) ) {
                continue;
            }

            $filterConfig['name'] = $filterName;

            $this->addFieldQuery($query, $filterConfig, $filters);
        }

        return $query;
    }

    protected function createHighlight(array $filters): array
    {
        $highlights = [
            'number_of_fragments' => 0,
            'pre_tags' => ['<mark>'],
            'post_tags' => ['</mark>'],
            'fields' => [],
        ];

        $filterConfig = $this->getSearchFilterConfig();

        foreach( $filters as $filterName => $filterValue ) {
            $filterType = $filterConfig[$filterName]['type'] ?? false;
            switch ($filterType) {
                case self::FILTER_TEXT:
                    $field = $filterValue['field'] ?? $filterName;
                    $highlights['fields'][$filterName] = new \stdClass();
                    break;
                case self::FILTER_TEXT_MULTIPLE:
                    foreach ($filterValue as $key => $value) {
                        $field = $value['field'] ?? $key;
                        $highlights['fields'][$field] = new \stdClass();
                    }
                    break;
            }
        }

        return $highlights;
    }

    private static function formatHighlight(string $highlight): array
    {
        $lines = explode(PHP_EOL, html_entity_decode($highlight));
        $result = [];
        foreach ($lines as $number => $line) {
            // Remove \r
            $line = trim($line);
            // Each word is marked separately, so we only need the lines with <mark> in them
            if (strpos($line, '<mark>') !== false) {
                $result[$number] = $line;
            }
        }
        return $result;
    }

    /**
     * Construct a text query
     * @param  string         $key   Elasticsearch field to match (unless $value['field']) is provided
     * @param  array          $value Array with [combination] of match (any, all, phrase), the [text] to search for and optionally the [field] to search in (if not provided, $key is used)
     * @return AbstractQuery
     */
    protected static function constructTextQuery(string $key, array $value): AbstractQuery
    {
        // Verse initialization
        if (isset($value['init']) && $value['init']) {
            return new Query\MatchPhrase($key, $value['text']);
        }

        $field = $value['field'] ?? $key;
        // Replace multiple spaces with a single space
        $text = preg_replace('!\s+!', ' ', $value['text']);

        // Remove colons
        $text = str_replace(':', '', $text);

        // Check if user does not use advanced syntax
        if (preg_match('/AND|OR|[\/~\-"()]/', $text) === 0) {
            if ($value['combination'] == 'phrase') {
                if (preg_match('/[*?]/', $text) === 0) {
                    $text = '"' . $text . '"';
                } else {
                    $text = implode(' AND ', explode(' ', $text));
                }
            } elseif ($value['combination'] == 'all') {
                $text = implode(' AND ', explode(' ', $text));
            }
        }

        return (new Query\QueryString($text))->setDefaultField($field);
    }

    protected function normalizeString(string $input): string
    {
        $result = $input;

        // Get wildcard character position and remove wildcards
        // question mark
        $qPos = [];
        $lastPos = 0;
        while (($lastPos = strpos($result, '?', $lastPos))!== false) {
            $qPos[] = $lastPos;
            $lastPos = $lastPos + strlen('*');
        }
        $result = str_replace('?', '', $result);
        // asterisk
        $aPos = [];
        $lastPos = 0;
        while (($lastPos = strpos($result, '*', $lastPos))!== false) {
            $aPos[] = $lastPos;
            $lastPos = $lastPos + strlen('*');
        }
        $result = str_replace('*', '', $result);

        $normalizedArray = $this->getIndex()->analyze(
            [
                'analyzer' => 'custom_greek_original',
                'text' => $result,
            ]
        );
        $normalizedTokens = [];
        foreach ($normalizedArray as $token) {
            $normalizedTokens[] = $token['token'];
        }
        $result = implode(' ', $normalizedTokens);

        // Reinsert wildcards
        foreach ($aPos as $a) {
            $result = substr_replace($result, '*', $a, 0);
        }
        foreach ($qPos as $q) {
            $result = substr_replace($result, '?', $q, 0);
        }

        return $result;
    }

    /** check if aggregation works on global dataset or filtered dataset */
    protected function isGlobalAggregation($config) {
        return ( in_array($config['type'], [self::AGG_GLOBAL_STATS],true) || ($config['globalAggregation'] ?? false) );
    }

    protected function isNestedAggregation($config) {
        return ( in_array($config['type'], [self::AGG_NESTED_ID_NAME, self::AGG_NESTED_KEYWORD],true) || ($config['nested_path'] ?? false) );
    }

    protected function isNestedFilter($config) {
        return ( in_array($config['type'], [self::FILTER_NESTED_ID, self::FILTER_NESTED_MULTIPLE],true) || ($config['nested_path'] ?? false) );
    }

    protected function sortAggregationResult(?array &$agg_result) {
        if( !$agg_result ) {
            return;
        }
        usort($agg_result, function($a, $b) {
            return strnatcasecmp($a['name'], $b['name']);
        });
    }

}
