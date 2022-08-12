<?php

namespace App\Service\ElasticSearch;

use Elastica\Aggregation;
use Elastica\Query;
use Elastica\Query\AbstractQuery;
use Elastica\Index;


abstract class AbstractSearchService extends AbstractService implements SearchServiceInterface, SearchConfigInterface
{
    const MAX_AGG = 2147483647;
    const MAX_SEARCH = 10000;
    const SEARCH_RAW_MAX_RESULTS = 500;
    private const DEFAULT_FILTER_TYPE = self::FILTER_KEYWORD;

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

    protected function sanitizeSearchFilters(array $params): array
    {
        // Init Filters
        $filters = $this->getDefaultSearchFilters();

        // Validate values
        $filterConfigs = $this->getSanitizedSearchFilterConfig();

        foreach ($filterConfigs as $filterName => $filterConfig) {
            // filter has subfilters?
            if ($filterConfig['filters'] ?? false) {
                foreach ($filterConfig['filters'] as $subFilterName => $subFilterConfig) {
                    $ret = $this->sanitizeSearchFilter($subFilterName, $subFilterConfig, $params);
                    if (!is_null($ret)) {
                        $filters[$subFilterName] = $ret;
                    }
                }
            } else {
                // no subfilters
                $filterValue = $this->sanitizeSearchFilter($filterName, $filterConfig, $params);
                if (!is_null($filterValue)) {
                    $filters[$filterName] = $filterValue;
                }
            }
        }

        return $filters;
    }

    protected function sanitizeSearchFilter($filterName, $filterConfig, $params): ?array
    {
        $ret = null;

        $filterValue = $filterConfig['value'] ?? $params[$filterName] ?? $filterConfig['defaultValue'] ?? null;

        switch ($filterConfig['type'] ?? self::DEFAULT_FILTER_TYPE) {
            case self::FILTER_NUMERIC:
            case self::FILTER_OBJECT_ID:
            case self::FILTER_NESTED_ID:
                if ($filterValue === null) break;
                if (is_array($filterValue)) {
                    $ret['value'] = array_map(fn($value) => (int)$value, $filterValue);
                }
                if (is_numeric($filterValue)) {
                    $ret['value'] = [(int)$filterValue];
                }
                $ret['operator'] = $params[$filterName . '_op'] ?? ['or'];
                $ret['operator'] = is_array($ret['operator']) ? $ret['operator'] : [$ret['operator']];
                break;
            case self::FILTER_KEYWORD:
                if ($filterValue === null) break;
                $ret['value'] = is_array($filterValue) ? $filterValue : [ $filterValue ];
                $ret['operator'] = $params[$filterName . '_op'] ?? ['or'];
                $ret['operator'] = is_array($ret['operator']) ? $ret['operator'] : [$ret['operator']];
                break;
            case self::FILTER_BOOLEAN:
                if ($filterValue === null) break;
                $ret['value'] = ($filterValue === '1' || $filterValue === 'true');
                break;
            case self::FILTER_EXISTS:
                if ($filterValue === null) break;
                if ($filterValue === 'true') {
                    $ret['value'] = true;
                }
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
                if (isset($params[$valueField]) && in_array($params[$valueField], ['exact', 'included', 'include', 'overlap'], true)) {
                    $rangeFilter['type'] = $params[$valueField];
                }

                if ($rangeFilter) {
                    $ret['value'] = $rangeFilter;
                }

                break;
            case self::FILTER_DMY_RANGE:
                $rangeFilter = [
                    'from' => [],
                    'till' => [],
                    'has_from' => false,
                    'has_till' => false
                ];
                $boolValid = false;

                $dateParts = [
                    'month',
                    'day',
                    'year',
                ];

                foreach ($dateParts as $datePart) {
                    $rangeFilter['from'][$datePart] = is_numeric($filterValue['from'][$datePart] ?? null) ? intval($filterValue['from'][$datePart]) : null;
                    $rangeFilter['has_from'] = $rangeFilter['has_from'] || ($rangeFilter['from'][$datePart] !== null);
                    $rangeFilter['till'][$datePart] = is_numeric($filterValue['till'][$datePart] ?? null) ? intval($filterValue['till'][$datePart]) : null;
                    $rangeFilter['has_till'] = $rangeFilter['has_till'] || ($rangeFilter['till'][$datePart] !== null);
                }

                // check if valid range query
                if ($rangeFilter['has_from'] && $rangeFilter['has_till']
                    && !array_diff_key(array_filter($rangeFilter['from'], fn($i) => $i != null), array_filter($rangeFilter['till'], fn($i) => $i != null))
                    && !array_diff_key(array_filter($rangeFilter['till'], fn($i) => $i != null), array_filter($rangeFilter['from'], fn($i) => $i != null))
                    && isset($rangeFilter['till']['month'])
                ) {
                    $rangeFilter['type'] = 'range';
                    $boolValid = true;
                } elseif ($rangeFilter['has_from']) {
                    $rangeFilter['type'] = 'exact';
                    $boolValid = true;
                } else {
                    $rangeFilter['type'] = 'invalid';
                }

                if ($boolValid) {
                    $ret['value'] = $rangeFilter;
                }

                break;
            case self::FILTER_NUMERIC_RANGE_SLIDER:
                $rangeFilter = [];
                $ignore = $filterConfig['ignore'] ?? [];
                $ignore = is_array($ignore) ? $ignore : [$ignore];

                $value = $filterValue[0] ?? null;
                if (is_numeric($value) && !in_array(floatval($value), $ignore)) {
                    $rangeFilter['floor'] = floatval($value);
                }

                $value = $filterValue[1] ?? null;
                if (is_numeric($value) && !in_array(floatval($value), $ignore)) {
                    $rangeFilter['ceiling'] = floatval($value);
                }

                if ($rangeFilter) {
                    $ret['value'] = $rangeFilter;
                }

                break;
            case self::FILTER_TEXT_MULTIPLE:
                if ($filterValue === null) break;
                if (is_array($filterValue)) {
                    $ret['value'] = $filterValue;
                }
                break;
            case self::FILTER_TEXT:
                if ($filterValue === null) break;
                if (is_array($filterValue)) {
                    $ret['value'] = $filterValue;
                }
                if (is_string($filterValue)) {
                    $combination = $params[$filterName . '_combination'] ?? 'any';
                    $combination = in_array($combination, ['any', 'all', 'phrase'], true) ? $combination : 'any';

                    $ret['value'] = [
                        'text' => $filterValue,
                        'combination' => $combination
                    ];
                }
                break;
            default:
                if ($filterValue === null) break;
                if (is_string($filterValue)) {
                    $ret['value'] = $filterValue;
                }
                if (is_array($filterValue)) {
                    $ret['value'] = $filterValue;
                }
                break;
        }
        return $ret;
    }

    private final function sanitizeSearchFilterConfig(string $name, array $config): array
    {
        $config['name'] = $name;
        $config['type'] = $config['type'] ?? self::DEFAULT_FILTER_TYPE;
        if ( $config['type'] !== self::FILTER_NESTED_MULTIPLE ) {
            $config['field'] = $config['field'] ?? $config['name'];
        }
        if($this->isNestedFilter($config)) {
            $config['nested_path'] = $config['nested_path'] ?? $config['name'];
        }
        if($config['filters'] ?? []) {
            foreach($config['filters'] as $sub_name => $sub_config) {
                $sub_config['fieldPrefix'] = $config['nested_path'] ?? null;
                $config['filters'][$sub_name] = $this->sanitizeSearchFilterConfig($sub_name, $sub_config);
            }
        }
        return $config;
    }

    private final function sanitizeAggregationConfig(string $name, array $config): array
    {
        $config['name'] = $name;
        $config['field'] = $config['field'] ?? $config['name'];
        if($this->isNestedAggregation($config)) {
            $config['nested_path'] = $config['nested_path'] ?? $config['name'];
        }
        if($config['filters'] ?? []) {
            foreach($config['filters'] as $sub_name => $sub_config) {
                $sub_config['fieldPrefix'] = $config['nested_path'] ?? null;
                $config['filters'][$sub_name] = $this->sanitizeSearchFilterConfig($sub_name, $sub_config);
            }
        }
        return $config;
    }

    protected function getDefaultSearchFilters(): array
    {
        return [];
    }

    protected function getDefaultSearchParameters(): array
    {
        return [];
    }

    /**
     * Add search filter details to search service
     * Return array of search_field => [
     *  'type' => aggregation type
     * ]
     * @return array
     */
    protected abstract function getSearchFilterConfig(): array;

    /**
     * Add aggregation details to search service
     * Return array of aggregation_field => [
     *  'type' => aggregation type
     * ]
     * @return array
     */
    protected abstract function getAggregationConfig(): array;

    private function getSanitizedAggregationConfig(): array
    {
        static $config = null;

        if ($config) {
            return $config;
        }

        $config = $this->getAggregationConfig();
        foreach ($config as $filterName => $filterConfig) {
            $config[$filterName] = $this->sanitizeAggregationConfig($filterName, $filterConfig);
        }
        return $config;
    }

    private function getSanitizedSearchFilterConfig(): array
    {
        static $config = null;

        if ($config) {
            return $config;
        }

        $config = $this->getSearchFilterConfig();
        foreach ($config as $filterName => $filterConfig) {
            $config[$filterName] = $this->sanitizeSearchFilterConfig($filterName, $filterConfig);
        }
        return $config;
    }

    protected function createSearchQuery(array $filters, array $excludeConfigs = null, $useOnlyConfigs = null): Query\BoolQuery
    {
        $filterConfigs = $useOnlyConfigs ?? $this->getSanitizedSearchFilterConfig();

        // parent query
        $query = new Query\BoolQuery();

        // walk filter configs
        foreach ($filterConfigs as $filterName => $filterConfig) {
            // skip excluded filters
            if ($excludeConfigs && in_array($filterName, $excludeConfigs, true)) {
                continue;
            }

            $this->addFieldQuery($query, $filterConfig, $filters);
        }

        return $query;
    }

    private function calculateFilterField($config): ?string
    {
        $filterField = $config['field'] ?? null;
        if (!$filterField) {
            return null;
        }
        if($config['nested_path'] ?? null) {
            $filterField = $config['nested_path'] . ($filterField ? '.' . $filterField : '');
        }
        if ($config['fieldPrefix'] ?? null) {
            $filterField = $config['fieldPrefix'] . ($filterField ? '.' . $filterField : '');
        }

        return $filterField;
    }

    protected function addFieldQuery(Query\BoolQuery $query, array $filterConfig, array $filters)
    {
        $query_top = $query;

        // nested filter?
        $boolIsNestedFilter = $this->isNestedFilter($filterConfig);

        $filterName = $filterConfig['name'];
        $filterField = $this->calculateFilterField($filterConfig);
        $filterValue = $filterConfig['value'] ?? $filters[$filterName]['value'] ?? $filterConfig['defaultValue'] ?? null; // filter can have fixed value
        $filterType = $filterConfig['type'];
        $filterNestedPath = $filterConfig['nested_path'] ?? null;

        // skip config if no subfilters and no filter value
        if (!isset($filterConfig['filters']) && !$filterValue) {
            return;
        }

        // nested filter? add nested query
//        if ( $boolIsNestedFilter ) {
//            $queryNested = self::createNestedQuery($filterNestedPath, $filterConfig);
//            $subquery = $queryNested->getParam('query');
//
//            $query->addMust($queryNested);
//            $query = $subquery;
//        }

        switch ($filterType) {
            case self::FILTER_OBJECT_ID:
            case self::FILTER_NESTED_ID:
            case self::FILTER_KEYWORD:
            case self::FILTER_NUMERIC:
                $arrSuffix = [
                    self::FILTER_OBJECT_ID => '.id',
                    self::FILTER_NESTED_ID => '.id',
                    self::FILTER_KEYWORD => '.keyword',
                ];

                $filterFieldId = $filterField . ($arrSuffix[$filterType] ?? '');
                $filterFieldCount = $filterField . '_count';
                $filterOperator = $filters[$filterName]['operator'];

                // AND operator? or default OR operator?
                if (in_array('and', $filterOperator, true)) {
                    $query = new Query\BoolQuery();

                    foreach ($filterValue as $value) {
                        // nested query? create nested query for each value
                        if ($boolIsNestedFilter) {
                            // create nested query
                            $queryNested = self::createNestedQuery($filterNestedPath, $filterConfig);
                            $query_filters = $queryNested->getParam('query');
                            // add nested query to main query, basted on operator
                            $query->addFilter($queryNested);
                        } else {
                            $query_filters = $query;
                        }

                        // If value == -1, select all entries without a value for a specific field
                        if ($value === -1) {
                            $query_filters->addMustNot(new Query\Exists($filterFieldId));
                        } else {
                            $query_filters->addMust(self::createTermQuery($filterFieldId, $value));
                        }
                    }
                    if ($query->count()) {
                        in_array('not', $filterOperator, true) ? $query_top->addMustNot($query) : $query_top->addFilter($query);
                        // only these allowed?
                        // todo: what if count field inside nested?
                        if (in_array('only', $filterOperator, true)) {
                            $query_top->addMust((new Query\Term())->setTerm($filterFieldCount, count($filterValue)));
                        }
                    }
                } else {
                    // nested query?
                    if ($boolIsNestedFilter) {
                        // create nested query
                        $query = self::createNestedQuery($filterNestedPath, $filterConfig);
                        $query_filters = $query->getParam('query');
                    } else {
                        $query_filters = $query = new Query\BoolQuery();
                    }

                    $query_filters_count = 0;
                    foreach ($filterValue as $value) {
                        if ($value === -1) {
                            $query_filters->addShould((new Query\BoolQuery())->addMustNot(new Query\Exists($filterFieldId)));
                        } else {
                            $query_filters->addShould(self::createTermQuery($filterFieldId, $value));
                            $query_filters_count++;
                        }
                    }

                    if ($query_filters->count()) {
                        in_array('not', $filterOperator, true) ? $query_top->addMustNot($query) : $query_top->addFilter($query);
                        // allow
                        if (in_array('only', $filterOperator, true)) {
                            $query_top->addMust((new Query\Term())->setTerm($filterFieldCount, count($filterValue)));
                        }
                    }
                }

                break;
            case self::FILTER_EXISTS:
                if ( $filterValue ) {
                    $filterQuery = new Query\Exists($filterField);               
                    $query->addMust($filterQuery);
                }
                break;
            case self::FILTER_BOOLEAN:
                if ($filterConfig['only_filter_on_true'] ?? false) {
                    if ($filterValue) {
                        $filterQuery = new Query\Term();
                        $filterQuery->setTerm($filterField, $filterValue ? $filterConfig['true_value'] ?? true : $filterConfig['false_value'] ?? false);

                        $query->addMust($filterQuery);
                    }
                } else {
                    $filterQuery = new Query\Term();
                    $filterQuery->setTerm($filterField, $filterValue ? $filterConfig['true_value'] ?? true : $filterConfig['false_value'] ?? false);

                    $query->addMust($filterQuery);
                }
                break;
            case self::FILTER_WILDCARD:
                $filterQuery = new Query\Wildcard($filterField, $filterValue);
                $query->addMust($filterQuery);
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
            case self::FILTER_DMY_RANGE:
                if (($filterValue['type'] ?? null) === 'exact') {
                    foreach ($filterValue['from'] as $datePart => $value) {
                        // todo: add datepart field option
                        if ($value) {
                            $query->addMust(
                                (new Query\Term())->setTerm($filterField . '.' . $datePart, $value)
                            );
                        }
                    }
                }
                if (($filterValue['type'] ?? null) === 'range') {
                    $dateParts = ['year', 'month', 'day'];

                    $count = count(array_filter($filterValue['from']));
                    $fromCarry = [];
                    $tillCarry = [];
                    $i = 0;

                    $fromQuery = new Query\BoolQuery();
                    $tillQuery = new Query\BoolQuery();

                    foreach ($dateParts as $datePart) {
                        if (!is_null($filterValue['from'][$datePart] ?? null) && !is_null($filterValue['till'][$datePart] ?? null)) {
                            $fromQueryPart = new Query\BoolQuery();
                            $tillQueryPart = new Query\BoolQuery();

                            $i++;
                            foreach ($fromCarry as $carryDatePart) {
                                $fromQueryPart->addMust((new Query\Term())->setTerm($filterField . '.' . $carryDatePart, $filterValue['from'][$carryDatePart]));
                                $tillQueryPart->addMust((new Query\Term())->setTerm($filterField . '.' . $carryDatePart, $filterValue['till'][$carryDatePart]));
                            }
                            $operator = ($i === $count) ? 'gte' : 'gt';
                            $fromQueryPart->addMust((new Query\Range())->addField($filterField . '.' . $datePart, [$operator => $filterValue['from'][$datePart]]));
                            $operator = ($i === $count) ? 'lte' : 'lt';
                            $tillQueryPart->addMust((new Query\Range())->addField($filterField . '.' . $datePart, [$operator => $filterValue['till'][$datePart]]));
                            $fromCarry[] = $datePart;

                            $fromQuery->addShould($fromQueryPart);
                            $tillQuery->addShould($tillQueryPart);
                        }
                    }

                    if ($fromQuery->count()) {
                        $query->addMust($fromQuery);
                    }
                    if ($tillQuery->count()) {
                        $query->addMust($tillQuery);
                    }
                    /*
                    year > from[year] or
                    OR ( year == from[year] and ( month > from[month] )
                    OR ( year == from[year] and ( month == from[month] ) and ( day >= from[day] )

*/
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

            case self::FILTER_NESTED_MULTIPLE:
                $filterPath = $filterConfig['nested_path'] ?? $filterName;

                // create nested query
                $queryNested = self::createNestedQuery($filterNestedPath, $filterConfig);
                $subquery = $queryNested->getParam('query');

                // subfilters with values
                $subFilters = array_intersect_key($filterConfig['filters'] ?? [], $filters);

                // add subfilters
                if (count($subFilters)) {
                    foreach ($subFilters as $subFilterName => $subFilterConfig) {
//                        $subFilterConfig['name'] = $subFilterName;
//                        $subFilterConfig['field'] = $subFilterConfig['field'] ?? $subFilterName;
//                        $subFilterConfig['fieldPrefix'] = $filterPath;
//                        $subFilterConfig['type'] = $subFilterConfig['type'] ?? self::FILTER_KEYWORD; // legacy?

                        $this->addFieldQuery($subquery, $subFilterConfig, $filters);
                    }
                    if ($subquery->count()) {
                        $query->addMust($queryNested);
                    }
                }
                break;
        }
    }

    protected function isNestedFilter($config)
    {
        return (in_array($config['type'], [self::FILTER_NESTED_ID, self::FILTER_NESTED_MULTIPLE], true) || ($config['nested_path'] ?? false));
    }

    private static function createNestedQuery(string $filterNestedPath, array $filterConfig = []): Query\Nested
    {
        // create nested query
        $queryNested = (new Query\Nested())
            ->setPath($filterNestedPath)
            ->setQuery(new Query\BoolQuery());

        // add inner hits?
        if ($filterConfig['innerHits'] ?? false) {
            $innerHits = new Query\InnerHits();
            if ($filterConfig['innerHits']['size'] ?? false) {
                $innerHits->setSize($filterConfig['innerHits']['size']);
            }
            $queryNested->setInnerHits($innerHits);
        }

        return $queryNested;
    }

    private static function createTermQuery(string $field, $value): Query\Term
    {
        return (new Query\Term())->setTerm($field, $value);
    }

    /**
     * Construct a text query
     * @param string $key Elasticsearch field to match (unless $value['field']) is provided
     * @param array $value Array with [combination] of match (any, all, phrase), the [text] to search for and optionally the [field] to search in (if not provided, $key is used)
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

    public function searchAndAggregate(array $params): array
    {
        // search
        $result = $this->search($params);

        // aggregate
        $result['aggregation'] = $this->aggregate($params['filters'] ?? []);

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

        // Track total number of hits
        $query->setTrackTotalHits();

        // Filtering
//        dump($params);
        $searchFilters = $this->sanitizeSearchFilters($params['filters'] ?? []);
        if (count($searchFilters)) {
//            dump($searchFilters);
            $query->setQuery($this->createSearchQuery($searchFilters));
            $query->setHighlight($this->createHighlight($searchFilters));
            dump(json_encode($query->toArray(), JSON_PRETTY_PRINT));
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
        $filterConfigs = $this->getSanitizedSearchFilterConfig();
        foreach ($filterConfigs as $filterName => $filterConfig) {
            switch ($filterConfig['type'] ?? null) {
                case self::FILTER_TEXT:
                    $filterValue = $filterValues[$filterName] ?? null;
                    if (isset($filterValue['field'])) {
                        $rename[$filterValue['field']] = explode('_', $filterValue['field'])[0];
                    }
                    break;
                case self::FILTER_TEXT_MULTIPLE:
                    $filterValues = $filterValues[$filterName] ?? null;
                    if (is_array($filterValues)) {
                        foreach ($filterValues as $filterValue) {
                            if (isset($filterValue['field'])) {
                                $rename[$filterValue['field']] = explode('_', $filterValue['field'])[0];
                            }
                        }
                    }
                    break;
            }
        }
        foreach (($data['hits']['hits'] ?? []) as $result) {
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
            if (isset($result['inner_hits'])) {
                $part['inner_hits'] = [];
                foreach ($result['inner_hits'] as $field_name => $inner_hit) {
                    $values = [];
                    foreach ($inner_hit['hits']['hits'] ?? [] as $hit) {
                        if ($hit['_source'] ?? false) {
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
        if ($fields) {
            $query->setSource($fields);
        }

        // Filtering
        $searchFilters = $this->sanitizeSearchFilters($params['filters'] ?? []);
        if (count($searchFilters)) {
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
        $filterConfigs = $this->getSanitizedSearchFilterConfig();
        foreach ($filterConfigs as $filterName => $filterConfig) {
            switch ($filterConfig['type'] ?? null) {
                case self::FILTER_TEXT:
                    $filterValue = $filterValues[$filterName] ?? null;
                    if (isset($filterValue['field'])) {
                        $rename[$filterValue['field']] = explode('_', $filterValue['field'])[0];
                    }
                    break;
                case self::FILTER_TEXT_MULTIPLE:
                    $filterValues = $filterValues[$filterName] ?? null;
                    if (is_array($filterValues)) {
                        foreach ($filterValues as $filterValue) {
                            if (isset($filterValue['field'])) {
                                $rename[$filterValue['field']] = explode('_', $filterValue['field'])[0];
                            }
                        }
                    }
                    break;
            }
        }
        foreach (($data['hits']['hits'] ?? []) as $result) {
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
            if (isset($result['inner_hits'])) {
                $part['inner_hits'] = [];
                foreach ($result['inner_hits'] as $field_name => $inner_hit) {
                    $values = [];
                    foreach ($inner_hit['hits']['hits'] as $hit) {
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


    protected function createHighlight(array $filters): array
    {
        $highlights = [
            'number_of_fragments' => 0,
            'pre_tags' => ['<mark>'],
            'post_tags' => ['</mark>'],
            'fields' => [],
        ];

        $filterConfig = $this->getSanitizedSearchFilterConfig();

        foreach ($filters as $filterName => $filterValue) {
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

    protected function sanitizeSearchResult(array $result)
    {
        return $result;
    }

    protected function aggregate(array $filterValues): array
    {
        // get agg config
        $aggConfigs = $this->getSanitizedAggregationConfig();
        if (!count($aggConfigs)) {
            return [];
        }

        // sanitize filter values
        $filterValues = $this->sanitizeSearchFilters($filterValues);

        // get filters used in multiselect aggregations
        // these filters don't filter the whole set, but filter the set for each aggregation
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
        foreach ($aggConfigs as $aggName => $aggConfig) {
            $aggType = $aggConfig['type']; // aggregation type
            $aggField = $aggConfig['field']; // aggregation field = field property or config name
            $aggIsGlobal = $this->isGlobalAggregation($aggConfig); // global aggregation?

            // query root
            $aggParentQuery = $aggIsGlobal ? $aggGlobalQuery : $query;

            // add aggregation filter (if not global)
            // - remove excludeFilter
            // - don't filter myself
            if (!$aggIsGlobal) {
                $aggSearchFilters = array_diff_key($aggFilterConfigs, array_flip($aggConfig['excludeFilter'] ?? []));
                unset($aggSearchFilters[$aggName]);

                if (count($aggSearchFilters)) {
                    $filterQuery = $this->createSearchQuery($filterValues, [], $aggSearchFilters);
                    if ($filterQuery->count()) {
                        $aggSubQuery = new Aggregation\Filter($aggName);
                        $aggSubQuery->setFilter($filterQuery);

                        $aggParentQuery->addAggregation($aggSubQuery);
                        $aggParentQuery = $aggSubQuery;
                    }
                }
            }

            // nested aggregation?
            $aggIsNested = $this->isNestedAggregation($aggConfig);
            if ($aggIsNested) {
                // add nested path to filed
                $aggNestedPath = $aggConfig['nested_path'];
                $aggField = $aggNestedPath . ($aggField ? '.' . $aggField : '' );

                // add nested aggregation
                $aggSubQuery = new Aggregation\Nested($aggName, $aggNestedPath);
                $aggParentQuery->addAggregation($aggSubQuery);
                $aggParentQuery = $aggSubQuery;

                // prepare possible aggregation filter
                $filterQuery = new Query\BoolQuery();
                $filterCount = 0;

                // aggregation has filter config?
                $aggNestedFilter = $aggConfig['filters'] ?? [];
                unset($aggNestedFilter[$aggName]); // do not filter myself
                $aggNestedFilter = array_intersect_key($aggNestedFilter, $filterValues); // only add filters with values

                if ($aggNestedFilter) {
                    foreach ($aggNestedFilter as $queryFilterField => $aggFilterConfig) {
                        $filterCount++;
                        self::addFieldQuery($filterQuery, $aggFilterConfig, $filterValues);
                    }
                }

                // aggregation has a limit on allowed values?
                if ($aggConfig['allowedValue'] ?? false) {
                    $filterCount++;
                    $allowedValue = $aggConfig['allowedValue'];
                    if (is_array($allowedValue)) {
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
                if ($filterCount) {
                    $aggSubQuery = new Aggregation\Filter($aggName);
                    $aggSubQuery->setFilter($filterQuery);

                    $aggParentQuery->addAggregation($aggSubQuery);
                    $aggParentQuery = $aggSubQuery;
                }
            }

            // add aggregation
            $aggTerm = null;
            switch ($aggType) {
                case self::AGG_GLOBAL_STATS:
                    $aggParentQuery->addAggregation(
                        (new Aggregation\Stats($aggName))
                            ->setField($aggField)
                    );
                    break;
                case self::AGG_KEYWORD:
                    $aggField = $aggField . '.keyword';
                    $aggFilterValues = $filterValues[$aggName]['value'] ?? [];

                    $aggTerm = (new Aggregation\Terms($aggName))
                        ->setSize(self::MAX_AGG)
                        ->setField($aggField);

                    // allow 0 doc count if aggregation is filtered
                    if ( count($aggFilterValues) ) {
                        $aggTerm->setMinimumDocumentCount(0);
                    }

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
                    $aggField = $aggField . '.id_name.keyword';
                    $aggFilterValues = $filterValues[$aggName]['value'] ?? [];

                    $aggTerm = (new Aggregation\Terms($aggName))
                        ->setSize(self::MAX_AGG)
                        ->setField($aggField);

                    // allow 0 doc count if aggregation is filtered
                    if ( count($aggFilterValues) ) {
                        $aggTerm->setMinimumDocumentCount(0);
                    }

                    $aggParentQuery->addAggregation($aggTerm);

                    break;
            }

            // count top documents?
            if ($aggIsNested && $aggTerm) {
                $aggTerm->addAggregation(new Aggregation\ReverseNested('top_reverse_nested'));
            }

        }

        dump(json_encode($query->toArray(),JSON_PRETTY_PRINT));

        // parse query result
        $searchResult = $this->getIndex()->search($query);
        $results = [];

        $arrAggData = $searchResult->getAggregations();
//        dump($arrAggData);

        foreach ($aggConfigs as $aggName => $aggConfig) {
            $aggType = $aggConfig['type'];

            // get aggregation results
            $aggResults = $arrAggData['global_aggregation'][$aggName] ?? $arrAggData[$aggName] ?? [];

            // local/global filtered?
            while (isset($aggResults[$aggName])) {
                $aggResults = $aggResults[$aggName];
            }
            $aggResults = $aggResults['buckets'] ?? $aggResults;

            switch ($aggType) {
                case self::AGG_GLOBAL_STATS:
                    $results[$aggName] = $aggResults;
                    break;
                case self::AGG_NUMERIC:
                case self::AGG_KEYWORD:
                    foreach ($aggResults as $result) {
                        if (!isset($result['key'])) continue;
                        if (count($aggConfig['limitValue'] ?? []) && !in_array($result['key'], $aggConfig['limitValue'], true)) {
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
                    $aggFilterValues = $filterValues[$aggName]['value'] ?? [];
                    foreach ($aggResults as $result) {
                        if (!isset($result['key'])) continue;
                        $parts = explode('_', $result['key'], 2);
                        $count = (int) ($result['top_reverse_nested']['doc_count'] ?? $result['doc_count']);
                        $id = (int) $parts[0];
                        $name = $parts[1];

                        // limitValue/limitId?
                        if (count($aggConfig['limitId'] ?? []) && !in_array((int)$parts[0], $aggConfig['limitId'], true)) {
                            continue;
                        }
                        if (count($aggConfig['limitValue'] ?? []) && !in_array((int)$parts[1], $aggConfig['limitValue'], true)) {
                            continue;
                        }
                        // ignoreValue?
                        if (count($aggConfig['ignoreValue'] ?? []) && in_array($parts[1], $aggConfig['ignoreValue'], true)) {
                            continue;
                        }
                        // zero doc count?
                        if ( $count === 0 && ( !count($aggFilterValues) || !in_array($id, $aggFilterValues, true) ) ) {
                            continue;
                        }

                        $results[$aggName][] = [
                            'id' => $id,
                            'name' => $name,
                            'count' => $count
                        ];
                    }
//                    $this->sortAggregationResult($results[$aggName]);
                    break;
                case self::AGG_BOOLEAN:
                    foreach ($aggResults as $result) {
                        if (!isset($result['key'])) continue;
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


    /**
     * Return filters that are used in aggregations
     * Todo: Now based on aggregation type or config (mostly nested), should be based on aggregation config check!
     */
    private function getAggregationFilters(): array
    {

        $filters = $this->getSanitizedSearchFilterConfig();
        $aggOrFilters = [];
        foreach ($filters as $filterName => $filterConfig) {
            $filterType = $filterConfig['type'];
            switch ($filterType) {
                case self::FILTER_NESTED_ID:
                case self::FILTER_OBJECT_ID:
                case self::FILTER_NESTED_MULTIPLE:
                    if (($filterConfig['aggregationFilter'] ?? true)) {
                        $aggOrFilters[$filterName] = $filterConfig;
                    }
                    break;
                default:
                    if (($filterConfig['aggregationFilter'] ?? false)) {
                        $aggOrFilters[$filterName] = $filterConfig;
                    }
                    break;
            }
        }

        return $aggOrFilters;
    }

    /** check if aggregation works on global dataset or filtered dataset */
    protected function isGlobalAggregation($config)
    {
        return (in_array($config['type'], [self::AGG_GLOBAL_STATS], true) || ($config['globalAggregation'] ?? false));
    }

    protected function isNestedAggregation($config)
    {
        return (in_array($config['type'], [self::AGG_NESTED_ID_NAME, self::AGG_NESTED_KEYWORD], true) || ($config['nested_path'] ?? false));
    }

    protected function normalizeString(string $input): string
    {
        $result = $input;

        // Get wildcard character position and remove wildcards
        // question mark
        $qPos = [];
        $lastPos = 0;
        while (($lastPos = strpos($result, '?', $lastPos)) !== false) {
            $qPos[] = $lastPos;
            $lastPos = $lastPos + strlen('*');
        }
        $result = str_replace('?', '', $result);
        // asterisk
        $aPos = [];
        $lastPos = 0;
        while (($lastPos = strpos($result, '*', $lastPos)) !== false) {
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

    protected function sortAggregationResult(?array &$agg_result)
    {
        if (!$agg_result) {
            return;
        }
        usort($agg_result, function ($a, $b) {
            return strnatcasecmp($a['name'], $b['name']);
        });
    }

}
