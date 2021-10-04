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

    protected const AGG_NUMERIC = "numeric";
    protected const AGG_OBJECT = "object";
    protected const AGG_EXACT_TEXT = "exact_text";
    protected const AGG_NESTED = "nested";
    protected const AGG_BOOLEAN = "bool";
    protected const AGG_MULTIPLE_FIELDS_OBJECT = "multiple_fields_object";
    protected const AGG_GLOBAL_STATS = "stats";

    protected const AGG_NESTED_ID_NAME = "nested_id_name";
    protected const AGG_OBJECT_ID_NAME = "object_id_name";

    protected const FILTER_NUMERIC = "numeric";
    protected const FILTER_OBJECT_ID = "object_id";
    protected const FILTER_NUMERIC_MULTIPLE = "numeric_multiple";
    protected const FILTER_NESTED_ID = "nested_id";
    protected const FILTER_NESTED_TOGGLE = "nested_toggle";
    protected const FILTER_NESTED_MULTIPLE = "nested_multiple";
    protected const FILTER_TEXT = "text";
    protected const FILTER_TEXT_EXACT = "text_exact";
    protected const FILTER_TEXT_MULTIPLE = "text_multiple";
    protected const FILTER_BOOLEAN = "boolean";
    protected const FILTER_MULTIPLE_FIELDS_OBJECT = "multiple_fields_object";
    protected const FILTER_DATE_RANGE = "date_range";
    protected const FILTER_NUMERIC_RANGE_SLIDER = "numeric_range";

    public const ENABLE_CACHE = 1;

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

    protected function sanitizeSearchParameters(array $params): array
    {
        // Set default parameters
        $defaults = $this->getDefaultSearchParameters();

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

    protected function sanitizeSearchFilters(array $params): array
    {
        // Init Filters
        $filterDefaults = $this->getDefaultSearchFilters();
        $filters = $filterDefaults;

        // Validate values
        $filterConfigs = $this->getSearchFilterConfig();

        foreach ($filterConfigs as $filterName => $filterConfig) {

            $filterValue = $params[$filterName] ?? false;

            switch ($filterConfig['type']) {

                case self::FILTER_NUMERIC:
                    if ($filterValue === false) continue;
                    if (is_numeric($filterValue)) {
                        $filters[$filterName] = $filterValue;
                    }
                    break;
                case self::FILTER_OBJECT_ID:
                case self::FILTER_NESTED_ID:
                    if ($filterValue === false) continue;
                    if (is_array($filterValue) || is_numeric($filterValue)) {
                        $filters[$filterName] = $filterValue;
                    }
                    break;
                case self::FILTER_NESTED_MULTIPLE:
                    // for each subfilter, clean value
                    foreach($filterConfig['filters'] as $subFilterName => $subFilterConfig) {
                        $subFilterValue = $params[$subFilterName] ?? false;
                        if ($subFilterValue === false) continue;
                        if (is_array($subFilterValue) || is_numeric($subFilterValue)) {
                            $filters[$subFilterName] = $subFilterValue;
                        }
                    }
                    break;
                case self::FILTER_BOOLEAN:
                    if ($filterValue === false) continue;
                        $filters[$filterName] = ($filterValue === '1');
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

                    if ( $rangeFilter) {
                        $filters[$filterName] = $rangeFilter;
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

                    if ( $rangeFilter) {
                        $filters[$filterName] = $rangeFilter;
                    }

                    break;
                case self::FILTER_TEXT_MULTIPLE:
                    if ($filterValue === false) continue;
                    if (is_array($filterValue)) {
                        $filters[$filterName] = $filterValue;
                    }
                    break;
                case self::FILTER_TEXT:
                    if ($filterValue === false) continue;
                    if (is_array($filterValue)) {
                        $filters[$filterName] = $filterValue;
                    }
                    if (is_string($filterValue)) {
                        $combination = $params[$filterName . '_combination'] ?? 'any';
                        $combination = in_array($combination, ['any', 'all', 'phrase'], true) ? $combination : 'any';

                        $filters[$filterName] = [
                            'text' => $filterValue,
                            'combination' => $combination
                        ];
                    }
                    break;
                default:
                    if ($filterValue === false) continue;
                    if (is_string($filterValue)) {
                        $filters[$filterName] = $filterValue;
                    }
                    break;
            }
        }

        return $filters;
    }

    protected function sanitizeSearchResult(array $result) {
        return $result;
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
        $searchFilters = [];
        if (isset($params['filters']) && is_array($params['filters'])) {
            $searchFilters = $this->sanitizeSearchFilters($params['filters']);
            $searchQuery = $this->createSearchQuery($searchFilters);
            $query->setQuery($searchQuery);
            $query->setHighlight($this->createHighlight($searchFilters));
            dump(json_encode($searchQuery->toArray(), JSON_PRETTY_PRINT));
        }

        // Search
        $data = $this->getIndex()->search($query)->getResponse()->getData();

        // Format response
        $response = [
            'count' => $data['hits']['total']['value'] ?? 0,
            'data' => [],
            'search' => $searchParams,
            'filters' => ( isset($params['filters']) && is_array($params['filters']) ? $params['filters'] : [])
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
                    foreach($inner_hit['hits']['hits'] as $hit) {
                        $values[] = $hit['_source'];
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
     * Todo: Now based on aggregation type or config, could be based on aggregation config check
     */
    private function getAggregationFilters(): array {

        $filters = $this->getSearchFilterConfig();
        $aggOrFilters = [];
        foreach( $filters as $filterName => $filterConfig ) {
            $filterType = $filterConfig['type'];
            switch($filterType) {
                case self::FILTER_NESTED_ID:
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
        dump($aggConfigs);
        if ( !count($aggConfigs) ) {
            return [];
        }

        // sanitize filter values
        $filterValues = $this->sanitizeSearchFilters($filterValues);
        //dump($filterValues);

        // get filters used in multiselect aggregations
        $aggFilterConfigs = $this->getAggregationFilters($filterValues);
        dump($aggFilterConfigs);

        // create global search query
        // exclude aggregation filters, will be added to aggregations
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
            $boolIsGlobal = $this->isGlobalAggregation($aggName);

            // aggregation field = field property or config name
            $aggField = $aggConfig['field'] ?? $aggName;

            // query root
            $aggParentQuery = $boolIsGlobal ? $aggGlobalQuery : $query;

            // add aggregation filter (if not global)
            // - remove excludeFilter
            // - don't filter myself
            if ( !$boolIsGlobal ) {
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

            // add aggregation
            switch($aggType) {
                case self::AGG_NUMERIC:
                    $aggParentQuery->addAggregation(
                        (new Aggregation\Terms($aggName))
                            ->setSize(self::MAX_AGG)
                            ->setField($aggField)
                    );
                    break;
                case self::AGG_GLOBAL_STATS:
                    $aggParentQuery->addAggregation(
                        (new Aggregation\Stats($aggName))
                            ->setField($aggField)
                    );
                    break;
                case self::AGG_EXACT_TEXT:
                    $aggField = $aggField.'.keyword';
                    $aggField = ltrim($aggField,'.'); // aggField might be empty

                    $aggParentQuery->addAggregation(
                        (new Aggregation\Terms($aggName))
                            ->setSize(self::MAX_AGG)
                            ->setField($aggField)
                    );
                    break;
                case self::AGG_NESTED:
                    $aggNestedPath = $aggConfig['nested_path'] ?? $aggName;
                    $aggField = isset($aggConfig['nested_path']) ? $aggConfig['nested_path'].'.'.$aggField : $aggField;

                    $aggNestedFilter = $aggConfig['filter'] ?? [];
                    unset($aggNestedFilter[$aggName]); // do not filter myself
                    $aggNestedFilter = array_intersect_key($aggNestedFilter, $filterValues); // only add filters with values

                    // nested?
                    if ( true ) {
                        $aggSubQuery = new Aggregation\Nested($aggName, $aggNestedPath);
                        $aggParentQuery->addAggregation($aggSubQuery);
                        $aggParentQuery = $aggSubQuery;

                        // filtered aggregation?
                        if ($aggNestedFilter) {

                            $aggSubQuery = new Aggregation\Filter($aggName);

                            $filterQuery = new Query\BoolQuery();
                            foreach($aggNestedFilter as $queryFilterField => $aggFilterField ) {
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
                            $aggSubQuery->setFilter($filterQuery);

                            $aggParentQuery->addAggregation($aggSubQuery);
                            $aggParentQuery = $aggSubQuery;
                        }
                    }

                    // id aggregation + name subaggregation
                    $aggTerm = (new Aggregation\Terms('id'))
                        ->setSize(self::MAX_AGG)
                        ->setField($aggField . '.id')
                        ->addAggregation(
                            (new Aggregation\Terms('name'))
                                ->setField($aggField . '.name.keyword')
                        );

                    $aggParentQuery->addAggregation($aggTerm);

                    break;
                case self::AGG_OBJECT_ID_NAME:
                    $aggField = $aggField.'.id_name.keyword';
                    $aggField = ltrim($aggField,'.'); // aggField might be empty

                    $aggTerm = (new Aggregation\Terms($aggName))
                        ->setSize(self::MAX_AGG)
                        ->setField($aggField);
                    $aggParentQuery->addAggregation($aggTerm);

                    break;
                case self::AGG_NESTED_ID_NAME:
                    $aggNestedPath = $aggConfig['nested_path'] ?? $aggName;

                    $aggField = $aggField.'.id_name.keyword';
                    $aggField = ltrim($aggField,'.'); // aggField might be empty

                    // add path?
                    if ( isset($aggConfig['nested_path']) ) {
                        $aggField = $aggNestedPath.'.'.$aggField;
                    }

                    $aggNestedFilter = $aggConfig['filter'] ?? [];
                    unset($aggNestedFilter[$aggName]); // do not filter myself
                    $aggNestedFilter = array_intersect_key($aggNestedFilter, $filterValues); // only add filters with values

                    // nested?
                    if ( true ) {
                        $aggSubQuery = new Aggregation\Nested($aggName, $aggNestedPath);
                        $aggParentQuery->addAggregation($aggSubQuery);
                        $aggParentQuery = $aggSubQuery;
                    }

                    // filtered aggregation?
                    if ($aggNestedFilter) {

                        $aggSubQuery = new Aggregation\Filter($aggName);

                        $filterQuery = new Query\BoolQuery();
                        foreach($aggNestedFilter as $queryFilterField => $aggFilterField ) {
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
                        $aggSubQuery->setFilter($filterQuery);

                        $aggParentQuery->addAggregation($aggSubQuery);
                        $aggParentQuery = $aggSubQuery;
                    }

                    // id_name
                    $aggTerm = (new Aggregation\Terms('id_name'))
                        ->setSize(self::MAX_AGG)
                        ->setField($aggField)
                        ->addAggregation( new Aggregation\ReverseNested('top_reverse_nested') );
                    $aggParentQuery->addAggregation($aggTerm);

                    break;
                case self::AGG_BOOLEAN:
                    $aggParentQuery->addAggregation(
                        (new Aggregation\Terms($aggName))
                            ->setSize(self::MAX_AGG)
                            ->setField($aggField)
                    );
                    break;
                case self::AGG_MULTIPLE_FIELDS_OBJECT:
                    // fieldName = [
                    //     [multiple_names] (e.g., [patron, scribe, related]),
                    //      'actual field name' (e.g. 'person'),
                    //      'dependend field name' (e.g. 'role')
                    //  ]
                    foreach ($aggName[0] as $key) {
                        $aggParentQuery->addAggregation(
                            (new Aggregation\Nested($key, $key))
                                ->addAggregation(
                                    (new Aggregation\Terms('id'))
                                        ->setSize(self::MAX_AGG)
                                        ->setField($key . '.id')
                                        ->addAggregation(
                                            (new Aggregation\Terms('name'))
                                                ->setField($key . '.name.keyword')
                                        )
                                )
                        );
                    }
                    break;
                }
        }

        dump(json_encode($query->toArray(),JSON_PRETTY_PRINT));

        // parse query result
        $searchResult = $this->getIndex()->search($query);
        $results = [];

        $arrAggData = $searchResult->getAggregations();
        //dump($arrAggData);

        foreach($aggConfigs as $aggName => $aggConfig) {
            $aggType = $aggConfig['type'];
            switch($aggType) {
                case self::AGG_NUMERIC:
                case self::AGG_EXACT_TEXT:
                    $aggregation = $arrAggData[$aggName] ?? [];
                    foreach ($aggregation['buckets'] as $result) {
                        $results[$aggName][] = [
                            'id' => $result['key'],
                            'name' => $result['key'],
                            'count' => $result['doc_count']
                        ];
                    }
                    break;
                case self::AGG_OBJECT:
                    $aggregation = $arrAggData[$aggName] ?? [];
                    foreach ($aggregation['buckets'] as $result) {
                        $results[$aggName][] = [
                            'id' => $result['key'],
                            'name' => $result['name']['buckets'][0]['key'],
                            'count' => $result['doc_count']
                        ];
                    }
                    break;
                case self::AGG_NESTED:
                    $aggregation = $arrAggData[$aggName] ?? [];

                    // filtered?
                    $aggregation = $aggregation[$aggName] ?? $aggregation;
                    $aggregation_results = $aggregation['id_name']['buckets'];

                    foreach ($aggregation_results as $result) {
                        $results[$aggName][] = [
                            'id' => $result['key'],
                            'name' => $result['name']['buckets'][0]['key'],
                            'count' => $result['doc_count']
                        ];
                    }
                    break;
                case self::AGG_OBJECT_ID_NAME:
                    $aggregation = $arrAggData[$aggName] ?? [];

                    // global/local filtered?
                    while ( isset($aggregation[$aggName]) ) {
                        $aggregation = $aggregation[$aggName];
                    }
                    $aggregation_results = $aggregation['buckets'] ?? [];

                    foreach ($aggregation_results as $result) {
                        $parts = explode('_',$result['key'],2);
                        $results[$aggName][] = [
                            'id' => $parts[0],
                            'name' => $parts[1],
                            'count' => $result['doc_count']
                        ];
                    }
                    break;

                case self::AGG_NESTED_ID_NAME:
                    $aggregation = $arrAggData[$aggName] ?? [];

                    // global/local filtered?
                    while ( isset($aggregation[$aggName]) ) {
                        $aggregation = $aggregation[$aggName];
                    }
                    $aggregation_results = $aggregation['id_name']['buckets'] ?? [];

                    foreach ($aggregation_results as $result) {
                        $parts = explode('_',$result['key'],2);
                        $results[$aggName][] = [
                            'id' => $parts[0],
                            'name' => $parts[1],
                            'count' => $result['top_reverse_nested']['doc_count'] ?? $result['doc_count']
                        ];
                    }
                    break;
                case self::AGG_BOOLEAN:
                    $aggregation = $arrAggData[$aggName] ?? [];

                    // global/local filtered?
                    while ( isset($aggregation[$aggName]) ) {
                        $aggregation = $aggregation[$aggName];
                    }
                    $aggregation_results = $aggregation['buckets'];

                    foreach ($aggregation_results as $result) {
                        $results[$aggName][] = [
                            'id' => $result['key'],
                            'name' => $result['key_as_string'],
                            'count' => $result['doc_count']
                        ];
                    }
                    break;
            }
        }

        return $results;
    }

    public function searchAndAggregate(array $params, $cache = null): array
    {
        // cache? add elasticsearch cache header
        if ( $cache === self::ENABLE_CACHE) {
            $cache_params = $this->sanitizeSearchParameters($params);
            if ( isset($params['filters']) ) {
                $cache_params['filters'] = $this->sanitizeSearchFilters($params['filters']);
            }

            $cache_key = md5(json_encode($cache_params));
            $this->getClient()->addHeader('elasticsearch-cache-key', $cache_key);
        }

        // search
        if ( $cache ) {
            $this->getClient()->getConnection()->addConfig('headers', ['elasticsearch-cache-key' => "search-".$cache_key]);
        }
        $result = $this->search($params);

        // aggregate
        if ( $cache ) {
            $this->getClient()->getConnection()->addConfig('headers', ['elasticsearch-cache-key' => "aqg-".$cache_key]);
        }
        $result['aggregation'] = $this->aggregate($params['filters'] ?? []);

        return $result;
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

            $filterValue = $filters[$filterName] ?? null;
            $filterType = $filterConfig['type'];
            $filterField = $filterConfig['field'] ?? $filterName;

            // skip filter if no subfilters and no filter value
            if ( !isset($filterConfig['filters']) && !$filterValue ) {
                continue;
            }

            // type
            switch ($filterType) {
                case self::FILTER_NUMERIC:
                    $query->addMust(
                        new Query\Match($filterField, $filterValue)
                    );
                    break;
                case self::FILTER_OBJECT_ID:
                    // If value == -1, select all entries without a value for a specific field
                    if ($filterValue == -1) {
                        $query->addMustNot(
                            new Query\Exists($filterField)
                        );
                    } else {
                        $query->addMust(
                            new Query\Match($filterField . '.id', $filterValue)
                        );
                    }
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
                    $filterPath = $filterConfig['nested_path'] ?? $filterName;
                    $filterField = isset($filterConfig['nested_path']) ? $filterConfig['nested_path'].'.'.$filterField : $filterField;
                    $filterFieldId = $filterField.".id";

                    // multiple values?
                    if ( is_array($filterValue) ) {
                        $subquery = new Query\BoolQuery();
                        foreach( $filterValue as $val) {
                            $subquery->addShould(['match' => [$filterFieldId => $val]]);
                        }
                        $query->addMust(
                            (new Query\Nested())
                                ->setPath($filterPath)
                                ->setQuery($subquery)
                            ->setInnerHits()
                        );
                    }
                    // single value
                    else {
                        // If value == -1, select all entries without a value for a specific field
                        if ($filterValue == -1) {
                            $query->addMustNot(
                                (new Query\Nested())
                                    ->setPath($filterPath)
                                    ->setQuery(
                                        (new Query\BoolQuery())
                                            ->addMust(new Query\Exists($filterField))
                                    )
                            );
                        } else {
                            $query->addMust(
                                (new Query\Nested())
                                    ->setPath($filterPath)
                                    ->setQuery(
                                        (new Query\BoolQuery())
                                            ->addMust(['match' => [$filterFieldId => $filterValue]])
                                    )
                            );
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
                            $subFilterValue = $filters[$subFilterName] ?? null;
                            $subFilterField = $subFilterConfig['field'] ?? $subFilterName;
                            $subFilterField = isset($filterConfig['nested_path']) ? $filterConfig['nested_path'].'.'.$subFilterField : $subFilterField;
                            $subFilterFieldId = $subFilterField.".id";

                            if ( is_array($subFilterValue) ) {
                                $subFilterQuery = new Query\Terms($subFilterFieldId, $subFilterValue);
                                $subquery->addMust( $subFilterQuery );
                            }
                        }

                        // create nested query
                        $queryNested = (new Query\Nested())
                                ->setPath($filterPath)
                                ->setQuery($subquery);

                        // inner hits?
                        if ($filterConfig['innerHits']) {
                            $queryNested->setInnerHits( new Query\InnerHits() );
                        }

                        $query->addMust($queryNested);
                    }
                    break;
                case self::FILTER_NESTED_TOGGLE:
                    foreach ($filterValue as $key => $filterValue) {
                        // value = [actual value, include/exclude]
                        if (!$filterValue[1]) {
                            // management collection not present
                            // no management collections present or only other management collections present
                            $query->addMust(
                                (new Query\BoolQuery())
                                    ->addShould(
                                        (new Query\BoolQuery())
                                            ->addMustNot(
                                                (new Query\Nested())
                                                    ->setPath($key)
                                                    ->setQuery(
                                                        (new Query\BoolQuery())
                                                            ->addMust(new Query\Exists($key))
                                                    )
                                            )
                                    )
                                    ->addShould(
                                        (new Query\BoolQuery())
                                            ->addMustNot(
                                                (new Query\Nested())
                                                    ->setPath($key)
                                                    ->setQuery(
                                                        (new Query\BoolQuery())
                                                            ->addMust(['match' => [$key . '.id' => $filterValue[0]]])
                                                    )
                                            )
                                    )
                            );
                        } else {
                            // management collection present
                            $query->addMust(
                                (new Query\Nested())
                                    ->setPath($key)
                                    ->setQuery(
                                        (new Query\BoolQuery())
                                            ->addMust(['match' => [$key . '.id' => $filterValue[0]]])
                                    )
                            );
                        }
                    }
                    break;
                case self::FILTER_TEXT:
                    $query->addMust(self::constructTextQuery($filterName, $filterValue));
                    break;
                case self::FILTER_TEXT_MULTIPLE:
                    $subQuery = new Query\BoolQuery();
                    foreach ($filterValue as $field => $filterValue) {
                        $subQuery->addShould(self::constructTextQuery($field, $filterValue));
                    }
                    $query->addMust($subQuery);
                    break;
                case self::FILTER_TEXT_EXACT:
                    if ($filterValue == -1) {
                        $query->addMustNot(
                            new Query\Exists($filterName)
                        );
                    } else {
                        $query->addMust(
                            (new Query\Match($filterName . '.keyword', $filterValue))
                        );
                    }
                    break;
                case self::FILTER_BOOLEAN:
                    $query->addMust(
                        (new Query\Match($filterName, $filterValue))
                    );
                    break;
                case self::FILTER_MULTIPLE_FIELDS_OBJECT:
                    // options = [[keys], value]
                    foreach ($filterValue as $key => $options) {
                        $subQuery = new Query\BoolQuery();
                        foreach ($options[0] as $key) {
                            $subQuery->addShould(
                                (new Query\Nested())
                                    ->setPath($key)
                                    ->setQuery(
                                        (new Query\BoolQuery())
                                            ->addMust(['match' => [$key . '.id' => $options[1]]])
                                    )
                            );
                        }
                        $query->addMust($subQuery);
                    }
                    break;
            }
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
    protected function isGlobalAggregation($config_name) {
        $config = $this->getAggregationConfig()[$config_name];
        return ( in_array($config['type'], [self::AGG_GLOBAL_STATS],true) || ($config['globalAggregation'] ?? false) );
    }

}
