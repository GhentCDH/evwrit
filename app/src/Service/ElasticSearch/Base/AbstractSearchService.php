<?php

namespace App\Service\ElasticSearch\Base;

use Elastica\Aggregation;
use Elastica\Query;
use Elastica\Query\AbstractQuery;


abstract class AbstractSearchService extends AbstractService implements SearchServiceInterface, SearchConfigInterface
{
    const MAX_AGG = 2147483647;
    const MAX_SEARCH = 10000;
    const SEARCH_RAW_MAX_RESULTS = 500;
    private const DEFAULT_FILTER_TYPE = self::FILTER_KEYWORD;

    /**
     * Add search filter details to search service
     * Return array of search_field => [
     *  'type' => aggregation type
     * ]
     * @return array
     */
    protected abstract function initSearchConfig(): array;

    /**
     * Add aggregation details to search service
     * Return array of aggregation_field => [
     *  'type' => aggregation type
     * ]
     * @return array
     */
    protected abstract function initAggregationConfig(): array;

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
        $filterConfigs = $this->getSearchConfig();
        $this->debug && dump($filterConfigs);

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

        $param_name = $filterConfig['filterParameter'] ?? $filterName; //todo: remove snake case!

//        $filterValue = $filterConfig['value'] ?? $params[$filterName] ?? $filterConfig['defaultValue'] ?? null;
        $filterValue = $filterConfig['value'] ?? $params[$param_name] ?? $filterConfig['defaultValue'] ?? null;

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
                $ret['value'] = ($filterValue === '1'
                    || $filterValue === 'true'
                    || is_array($filterValue) && (in_array('1', $filterValue, true) || in_array('true', $filterValue, true) )
                );
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
                if (is_string($filterValue) && $filterValue !== '') {
                    $this->debug && dump($filterName);
                    $this->debug && dump($params);
                    $combinationField = $filterConfig['combinationField'];
                    $combinationOptions = $filterConfig['combinationOptions'] ?? ['any'];
                    $combination = $params[$combinationField] ?? 'any';
                    $combination = in_array($combination, $combinationOptions, true) ? $combination : 'any';

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

    private function sanitizeSearchFilterConfig(string $name, array $config, string $prefix = null): array
    {
        $arrFieldPrefix = [];
        if ( $prefix ) {
            $arrFieldPrefix[] = $prefix;
        }

        $config['name'] = $name;
        $config['type'] = $config['type'] ?? self::DEFAULT_FILTER_TYPE;
        if ( $config['type'] !== self::FILTER_NESTED_MULTIPLE ) {
            $config['field'] = $config['field'] ?? $config['name'];
        }
        if($this->isNestedFilter($config)) {
            $config['nestedPath'] = $config['nestedPath'] ?? $config['field'];
            $arrFieldPrefix[] = $config['nestedPath'];
        }
        $config['anyKey'] = $config['anyKey'] ?? self::ANY_KEY;
        $config['noneKey'] = $config['noneKey'] ?? self::NONE_KEY;

        // subfilters?
        if($config['filters'] ?? []) {
            foreach($config['filters'] as $sub_name => $sub_config) {
                $config['filters'][$sub_name] = $this->sanitizeSearchFilterConfig($sub_name, $sub_config, $config['nestedPath'] ?? null);
            }
        }

        // fix lazy configuration
        if ( count($arrFieldPrefix) ) {
            $fieldPrefix = implode('.', $arrFieldPrefix).'.';
            // add missing field prefix?
            if ( isset($config['field']) && !str_starts_with($config['field'], $fieldPrefix) ) {
                $config['field'] = $fieldPrefix.$config['field'];
            }
        }

        return $config;
    }

    private function sanitizeAggregationConfig(string $name, array $config, string $prefix = null): array
    {
        $arrFieldPrefix = [];
        if ( $prefix ) {
            $arrFieldPrefix[] = $prefix;
        }

        $config['name'] = $name;
        $config['field'] = $config['field'] ?? $config['name'];
        $config['active'] = (bool) ($config['active'] ?? true);
        if($this->isNestedAggregation($config)) {
            $config['nestedPath'] = $config['nestedPath'] ?? $config['field'];
            $arrFieldPrefix[] = $config['nestedPath'];
        }
        $config['countTopDocuments'] = (bool) ($config['countTopDocuments'] ?? True);

        if($config['filters'] ?? []) {
            foreach($config['filters'] as $sub_name => $sub_config) {
//                $sub_config['fieldPrefix'] = $config['nestedPath'] ?? null;
                $config['filters'][$sub_name] = $this->sanitizeSearchFilterConfig($sub_name, $sub_config, $config['nestedPath'] ?? null);
            }
        }

        $config['aggregations'] = $config['aggregations'] ?? [];

        $config['anyKey'] = $config['anyKey'] ?? self::ANY_KEY;
        $config['anyLabel'] = $config['anyLabel'] ?? self::ANY_LABEL;
        $config['noneKey'] = $config['noneKey'] ?? self::NONE_KEY;
        $config['noneLabel'] = $config['noneLabel'] ?? self::NONE_LABEL;

        // fix lazy configuration
        if ( count($arrFieldPrefix) ) {
            $fieldPrefix = implode('.', $arrFieldPrefix).'.';
            // add missing field prefix?
            if ( isset($config['field']) && !str_starts_with($config['field'], $fieldPrefix) ) {
                $config['field'] = $fieldPrefix.$config['field'];
            }
        }

        return $config;
    }

    private function sanitizeTermAggregationItems(array $items, array $aggConfig, array $aggFilterValues): array
    {
        $output = [];
        foreach($items as $item) {
            $count = $item['count'];
            $value = $item['id'];
            $label = $item['name'];
            $active = $item['active'] ?? false;

            // limitValue?
            if (count($aggConfig['allowedValue'] ?? []) && !in_array($value, $aggConfig['allowedValue'], true)) {
                continue;
            }
            // ignoreValue?
            if (count($aggConfig['ignoreValue'] ?? []) && in_array($value, $aggConfig['ignoreValue'], true)) {
                continue;
            }
            // zero doc count? only allow if value in search filter values
            if ( $count === 0 && ( !count($aggFilterValues) || !in_array($value, $aggFilterValues, true) ) ) {
                continue;
            }
            // replace label?
            if ( $aggConfig['replaceLabel'] ?? null ) {
                $label = str_replace($aggConfig['replaceLabel']['search'], $aggConfig['replaceLabel']['replace'], $label);
            }

            if ($aggConfig['mapLabel'] ?? null ) {
                $label = $aggConfig['mapLabel'][$label] ?? $label;
            }

            $output[] = [
                'id' => $value,
                'name' => $label,
                'count' => $count,
                'active' => $active
            ];
        }
        return $output;
    }

    protected function getDefaultSearchFilters(): array
    {
        return [];
    }

    protected function getDefaultSearchParameters(): array
    {
        return [];
    }

    protected function onBeforeSearch(array &$searchParams, Query $query, Query\FunctionScore $queryFS): void {
    }

    protected function onInitAggregationConfig(array &$arrAggregationConfigs, array $arrFilterValues): void {
    }

    protected function getAggregationConfig(): array
    {
        static $config = null;

        if ($config) {
            return $config;
        }

        $config = $this->initAggregationConfig();
        foreach ($config as $filterName => $filterConfig) {
            $config[$filterName] = $this->sanitizeAggregationConfig($filterName, $filterConfig);
        }
        return $config;
    }

    private function getSearchConfig(): array
    {
        static $config = null;

        if ($config) {
            return $config;
        }

        $config = $this->initSearchConfig();
        foreach ($config as $filterName => $filterConfig) {
            $config[$filterName] = $this->sanitizeSearchFilterConfig($filterName, $filterConfig);
        }
        return $config;
    }

    protected function createSearchQuery(array $filterValues, ?array $filterConfigs = null): Query\BoolQuery
    {
        $filterConfigs = $filterConfigs ?? $this->getSearchConfig();

        // create parent query
        $query = new Query\BoolQuery();

        // walk filter configs
        foreach ($filterConfigs as $filterConfig) {
            $this->addFieldQuery($query, $filterConfig, $filterValues);
        }

        return $query;
    }

    private function calculateFilterField($config): ?string
    {
        $filterField = $config['field'] ?? null;
        if (!$filterField) {
            return null;
        }

        return $filterField;
    }

    protected function addFieldQuery(Query\BoolQuery $query, array $filterConfig, array $filterValues): void
    {
        $query_top = $query;

        // nested filter?
        $boolIsNestedFilter = $this->isNestedFilter($filterConfig);

        $filterName = $filterConfig['name'];
        $filterField = $this->calculateFilterField($filterConfig);
//        $filterValue = $filterConfig['value'] ?? $filterValues[$filterName]['value'] ?? $filterConfig['defaultValue'] ?? null; // filter can have fixed value, query value or default value
        $filterValue = $filterValues[$filterName]['value'] ?? null;
        $filterType = $filterConfig['type'];
        $filterNestedPath = $filterConfig['nestedPath'] ?? null;

        // skip filter if no filter value and no subfilters
        if (!isset($filterConfig['filters']) && !$filterValue) {
            return;
        }

        // add filter based on type
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
                // todo: default filter operator is hard coded, should make this configuration?
                $filterOperator = $filterValues[$filterName]['operator'] ?? ['or'];

                $boolIsNone = in_array((int) $filterConfig['noneKey'], $filterValue, true)
                    || in_array($filterConfig['noneKey'], $filterValue, true)
                    || in_array('none', $filterOperator, true);

                if ($boolIsNone) {
                    if ($boolIsNestedFilter) {
                        $queryNested = self::createNestedQuery($filterNestedPath, $filterConfig);
                        $query_filters = $queryNested->getParam('query');
                        $query->addFilter($queryNested);
                    } else {
                        $query_filters = $query;
                    }
                    $query_filters->addMustNot(new Query\Exists($filterFieldId));
                    break;
                }

                $boolIsAny = in_array((int) $filterConfig['anyKey'], $filterValue, true)
                    || in_array($filterConfig['anyKey'], $filterValue, true)
                    || in_array('any', $filterOperator, true);
                if ($boolIsAny) {
                    if ($boolIsNestedFilter) {
                        $queryNested = self::createNestedQuery($filterNestedPath, $filterConfig);
                        $query_filters = $queryNested->getParam('query');
                        $query->addFilter($queryNested);
                    } else {
                        $query_filters = $query;
                    }
                    $query_filters->addMust(new Query\Exists($filterFieldId));
                    break;
                }

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

                        $query_filters->addMust(self::createTermQuery($filterFieldId, $value));
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

                    $query_filters->addShould(new Query\Terms($filterFieldId, $filterValue));

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
                $floorField = $filterConfig['floorField'] ?? $filterConfig['field'] ?? $filterName;
                $ceilingField = $filterConfig['ceilingField'] ?? $filterConfig['field'] ?? $filterName;

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
                            self::createTermQuery($filterConfig['floorField'], $filterValue['floor'])
//                            new Query\Match($filterConfig['floorField'], $filterValue['floor'])
                        );
                    }
                    if (isset($filterValue['ceiling'])) {
                        $query->addMust(
                            self::createTermQuery($filterConfig['ceilingField'], $filterValue['ceiling'])
//                            new Query\Match($filterConfig['ceilingField'], $filterValue['ceiling'])
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
                // add subfilters with values
                // todo: this is wrong no? filter can have default value, should not be left out
//                $subFilters = array_intersect_key($filterConfig['filters'] ?? [], $filterValues);
//                if (count($subFilters)) {
//                    // create nested query
//                    $queryNested = self::createNestedQuery($filterNestedPath, $filterConfig);
//                    $subQuery = $queryNested->getParam('query');
//
//                    foreach ($subFilters as $subFilterName => $subFilterConfig) {
//                        $this->addFieldQuery($subQuery, $subFilterConfig, $filterValues);
//                    }
//
//                    if ($subQuery->count()) {
//                        $query->addMust($queryNested);
//                    }
//                }

                $queryNested = self::createNestedQuery($filterNestedPath, $filterConfig);
                $subQuery = $this->createSearchQuery($filterValues, $filterConfig['filters']);

                // count number of inner hits
                if ($filterConfig['boost'] ?? 1) {
                    $subQuery->addMust( new Query\MatchAll() );
                }

                if ($subQuery->count()) {
                    $queryNested->setQuery($subQuery);
                    $query->addMust($queryNested);
                }

            break;
        }
    }

    protected function isNestedFilter($config)
    {
        return (in_array($config['type'], [self::FILTER_NESTED_ID, self::FILTER_NESTED_MULTIPLE], true) || ($config['nestedPath'] ?? false));
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

        // score mode
        if ($filterConfig['scoreMode'] ?? false) {
            $queryNested->setParam('score_mode', $filterConfig['scoreMode']);
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
            $text = trim($text);
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
        $queryFS = new Query\FunctionScore();
        $query = new Query($queryFS);

        // onBeforeSearch
        $this->onBeforeSearch($searchParams, $query, $queryFS);
        $this->debug && dump($searchParams);

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
//        dump($params['filters']);
        $searchFilters = $this->sanitizeSearchFilters($params['filters'] ?? []);
        if (count($searchFilters)) {
            $this->debug && dump($searchFilters);
            $queryFS->setQuery($this->createSearchQuery($searchFilters));
            $query->setHighlight($this->createHighlight($searchFilters));
        } else {
            $queryFS->setQuery( new Query\MatchAll() );
        }

        $this->debug && dump(json_encode($query->toArray(), JSON_PRETTY_PRINT));

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
        $filterConfigs = $this->getSearchConfig();
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
            $part['_score'] = $result['_score'];
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
                    $count = $inner_hit['hits']['total']['value'] ?? null;
                    $part['inner_hits'][$field_name] = [ 'data' => $values, 'count' => $count ];
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
        $filterConfigs = $this->getSearchConfig();
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
            $part['_score'] = $result['_score'];

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

        $filterConfig = $this->getSearchConfig();

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


    /**
     * @param array $arrFilterValues
     * @return array
     *
     * There are 3 types of filters in an aggregation query
     * - filters that reduce the document set for all aggregations.
     * - filters that reduce the document set for each aggregation separately.
     *   ex: aggregations that allow multiselect must not be filtered by their own selected values
     * - filters that reduce the nested document set.
     *   ex: think of a collection of objects with properties 'type' and 'subtype'. an aggregation on 'subtype'
     *   must also filter the nested set of objects based on the value of 'type'.
     *
     */
    protected function aggregate(array $arrFilterValues): array
    {
        // get aggregation configurations
        $arrAggregationConfigs = $this->getAggregationConfig();
        if (!count($arrAggregationConfigs)) {
            return [];
        }

        $arrFilterConfigs = $this->getSearchConfig();

        // sanitize filter values
        $arrFilterValues = $this->sanitizeSearchFilters($arrFilterValues);

        // event onInitAggregationConfig
        $this->onInitAggregationConfig($arrAggregationConfigs, $arrFilterValues);

        // get filters used in multiselect aggregations
        // these filters are added to each aggregation and don't filter the global set
        $arrAggregationFilterConfigs = $this->getAggregationFilters();

        // create global set search query
        // exclude filters used in multiselect aggregations, will be added as aggregation filters
        $arrGlobalSetFilterConfigs = array_diff_key($arrFilterConfigs, $arrAggregationFilterConfigs);
        $query = (new Query())
            ->setQuery($this->createSearchQuery($arrFilterValues, $arrGlobalSetFilterConfigs))
            ->setSize(0); // Only aggregation will be used

        // create global aggregation (unfiltered, full dataset)
        // global aggregations will be added as sub-aggregations to this aggregation
        $aggGlobalQuery = new Aggregation\GlobalAggregation("global_aggregation");
        $query->addAggregation($aggGlobalQuery);

        // walk aggregation configs
        foreach ($arrAggregationConfigs as $aggName => $aggConfig) {
            $aggType = $aggConfig['type'];
            $aggField = $aggConfig['field'];
            $aggIsGlobal = $this->isGlobalAggregation($aggConfig); // global aggregation?
            $countTopDocuments = $aggConfig['countTopDocuments'];

            // skip inactive aggregations
            if (!$aggConfig['active']) {
                continue;
            }

            // skip aggregations with false condition
            if ( is_callable($aggConfig['condition'] ?? null) ) {
                if ( !$aggConfig['condition']($aggName, $aggConfig, $arrFilterValues) ) {
                    continue;
                }
            }

            // set aggregation parent
            $aggParentQuery = $aggIsGlobal ? $aggGlobalQuery : $query;

            // always add aggregation filter (if not global)
            // - easier to parse results
            // + remove excludeFilter
            // + don't filter myself
            if (!$aggIsGlobal) {
                // calculate filters used in aggregation
                $aggFilterConfigs = array_diff_key($arrAggregationFilterConfigs, array_flip($aggConfig['excludeFilter'] ?? []));
                unset($aggFilterConfigs[$aggName]);

                // calculate filter values used in aggregation
                // instead of excluding filters, more correct to remove filter values for that filter, default value possible, no?
                $aggFilterValues = array_diff_key($arrFilterValues, array_flip($aggConfig['excludeFilter'] ?? []));
                unset($aggFilterValues[$aggName]);

//                if (count($aggSearchFilters)) {
//                if (count($aggFilterValues)) {
//                    $filterQuery = $this->createSearchQuery($aggFilterValues, [], $aggSearchFilters);
                $filterQuery = $this->createSearchQuery($aggFilterValues);
//                } else {
//                    $filterQuery = new Query\BoolQuery();
//                }

                $aggSubQuery = new Aggregation\Filter($aggName);
                $aggSubQuery->setFilter($filterQuery);

                $aggParentQuery->addAggregation($aggSubQuery);
                $aggParentQuery = $aggSubQuery;
            }

            // nested aggregation? filter nested set!
            $aggIsNested = $this->isNestedAggregation($aggConfig);
            if ($aggIsNested) {
                // add nested path to filed
                $aggNestedPath = $aggConfig['nestedPath'];

                // add nested aggregation
                $aggSubQuery = new Aggregation\Nested($aggName, $aggNestedPath);
                $aggParentQuery->addAggregation($aggSubQuery);
                $aggParentQuery = $aggSubQuery;

                // prepare possible aggregation filter
//                $filterQuery = new Query\BoolQuery();

                // aggregation has filter config?
                // todo: instead of manual filter list, can this be done by getting all filters with the same nested path?
                $aggNestedFilterConfigs = [];
                foreach( $arrAggregationFilterConfigs as $config ) {
                    if ( ($config['nestedPath'] ?? null) === $aggNestedPath ) {
                        $aggNestedFilterConfigs = array_merge($aggNestedFilterConfigs, $config['filters'] ?? []);
                    }
                }

                // calculate filter values used in aggregation
                // instead of excluding filters, more correct to remove filter values for that filter, default value possible, no?
                $aggFilterValues = array_intersect_key($arrFilterValues, $aggNestedFilterConfigs);
                $aggFilterValues = array_diff_key($aggFilterValues, array_flip($aggConfig['excludeFilter'] ?? []));
                unset($aggFilterValues[$aggName]);

//                $aggNestedFilterConfigs = $aggConfig['filters'] ?? [];
//                unset($aggNestedFilterConfigs[$aggName]); // do not filter myself
//                $aggNestedFilterConfigs = array_intersect_key($aggNestedFilterConfigs, $arrFilterValues); // only add filters with values
//
//                if ($aggNestedFilterConfigs) {
//                    foreach ($aggNestedFilterConfigs as $queryFilterField => $aggFilterConfig) {
//                        self::addFieldQuery($filterQuery, $aggFilterConfig, $arrFilterValues);
//                    }
//                }

                $filterQuery = $this->createSearchQuery($aggFilterValues, $aggNestedFilterConfigs);

                // aggregation has a limit on allowed values?
                if ($aggConfig['allowedValue'] ?? false) {
                    $allowedValue = is_array($aggConfig['allowedValue']) ? $aggConfig['allowedValue'] : [ $aggConfig['allowedValue'] ];
                    $filterQuery->addFilter(
                        new Query\Terms($aggField, $allowedValue)
                    );
                }

                // filter aggretation (if valid subquery)
                if ($filterQuery->count()) {
                    $aggSubQuery = new Aggregation\Filter($aggName);
                    $aggSubQuery->setFilter($filterQuery);

                    $aggParentQuery->addAggregation($aggSubQuery);
                    $aggParentQuery = $aggSubQuery;
                }
            }

            // add aggregation
            switch ($aggType) {
                case self::AGG_GLOBAL_STATS:
                    $aggParentQuery->addAggregation(
                        (new Aggregation\Stats($aggName))
                            ->setField($aggField)
                    );
                    break;
                case self::AGG_CARDINALITY:
                    $aggParentQuery->addAggregation(
                        (new Aggregation\Cardinality($aggName))
                            ->setField($aggField)
                    );
                    break;
                case self::AGG_KEYWORD:
                    $aggField = $aggField . '.keyword';
                    $aggFilterValues = $arrFilterValues[$aggName]['value'] ?? [];

                    $aggTerm = (new Aggregation\Terms($aggName))
                        ->setSize(self::MAX_AGG)
                        ->setField($aggField);

                    // allow 0 doc count if aggregation is filtered
                    if ( count($aggFilterValues) ) {
                        $aggTerm->setMinimumDocumentCount(0);
                    }

                    // count top documents?
                    $aggIsNested && $aggTerm->addAggregation(new Aggregation\ReverseNested('top_reverse_nested'));

                    // subaggregations
                    // todo: fix hard coded aggregation type!!
                    foreach($aggConfig['aggregations'] as $subAggName => $subAggConfig) {
                        $aggTerm->addAggregation((new Aggregation\Terms($subAggName))
                            ->setField($subAggConfig['field'].".keyword")
                        );
                    }

                    $aggParentQuery->addAggregation($aggTerm);
                    break;
                case self::AGG_BOOLEAN:
                case self::AGG_NUMERIC:
                    $aggTerm = (new Aggregation\Terms($aggName))
                        ->setSize(self::MAX_AGG)
                        ->setField($aggField);

                    // count top documents?
                    $aggIsNested && $countTopDocuments && $aggTerm->addAggregation(new Aggregation\ReverseNested('top_reverse_nested'));

                    $aggParentQuery->addAggregation($aggTerm);
                    break;
                case self::AGG_OBJECT_ID_NAME:
                case self::AGG_NESTED_ID_NAME:
                    // todo: remove 'locale' option, add 'keywordField' that overrides default '.id_name.keyword'
                    $aggLocalePrefix = ($aggConfig['locale'] ?? null) ? '.'.$aggConfig['locale'] : '';
                    $aggField = $aggField . '.id_name'.$aggLocalePrefix.'.keyword';
                    $aggFilterValues = $arrFilterValues[$aggName]['value'] ?? [];

                    $aggTerm = (new Aggregation\Terms($aggName))
                        ->setSize(self::MAX_AGG)
                        ->setField($aggField);

                    // count top documents?
                    $aggIsNested && $countTopDocuments && $aggTerm->addAggregation(new Aggregation\ReverseNested('top_reverse_nested'));

                    // allow 0 doc count if aggregation is filtered
                    if ( count($aggFilterValues) ) {
                        $aggTerm->setMinimumDocumentCount(0);
                    }

                    $aggParentQuery->addAggregation($aggTerm);

                    // count missing
                    $aggCountMissing = new Aggregation\Missing('count_missing', $aggField);
                    $aggIsNested && $countTopDocuments && $aggCountMissing->addAggregation(new Aggregation\ReverseNested('top_reverse_nested'));
                    $aggParentQuery->addAggregation($aggCountMissing);
                    // count any
                    $aggCountAny = new Aggregation\Filters('count_any');
                    $aggIsNested && $countTopDocuments && $aggCountAny->addAggregation(new Aggregation\ReverseNested('top_reverse_nested'));
                    $aggCountAny->addFilter(new Query\Exists($aggField));

                    $aggParentQuery->addAggregation($aggCountAny);
                    break;
            }
        }

        $this->debug && dump(json_encode($query->toArray(),JSON_PRETTY_PRINT));

        // parse query result
        $searchResult = $this->getIndex()->search($query);
        $results = [];

        $arrAggData = $searchResult->getAggregations();
//        dump($arrAggData);

        foreach ($arrAggregationConfigs as $aggName => $aggConfig) {
            $aggType = $aggConfig['type'];

            // get aggregation results
            $aggData = $arrAggData['global_aggregation'][$aggName] ?? $arrAggData[$aggName] ?? [];
//            dump($aggName);
//            dump($aggData);

            switch ($aggType) {
                case self::AGG_GLOBAL_STATS:
                    $aggResults = $this->getAggregationData($aggData, $aggName, $aggName);
                    $results[$aggName] = $aggResults;
                    break;
                case self::AGG_CARDINALITY:
                    $aggResults = $this->getAggregationData($aggData, $aggName, $aggName);
                    $results[$aggName] = $aggResults;
                    break;
                case self::AGG_NUMERIC:
                case self::AGG_KEYWORD:
                    $aggFilterValues = $arrFilterValues[$aggName]['value'] ?? [];
                    $aggResults = $this->getAggregationData($aggData, $aggName, $aggName);

                    $items = [];
                    foreach ($aggResults['buckets'] ?? [] as $result) {
                        if (!isset($result['key'])) continue;

                        $item = [
                            'id' => $result['key'],
                            'name' => $result['key'],
                            'count' => (int) ($result['top_reverse_nested']['doc_count'] ?? $result['doc_count'])
                        ];
                        foreach($aggConfig['aggregations'] as $subAggName => $subAggConfig) {
                            $item[$subAggName] = $result[$subAggName]['buckets'][0]['key'] ?? null;
                        }

                        $items[] = $item;
//                        dump($item);
                    }
                    $results[$aggName] = $this->sanitizeTermAggregationItems($items, $aggConfig, $aggFilterConfigs);
                    break;
                case self::AGG_OBJECT_ID_NAME:
                case self::AGG_NESTED_ID_NAME:
                    $items = [];
                    $aggFilterValues = $arrFilterValues[$aggName]['value'] ?? [];

                    // get none count
                    $aggResults = $this->getAggregationData($aggData, $aggName, 'count_missing');
                    if ( $aggResults['doc_count'] ?? null) {
                        $item = [
                            'id' => $aggConfig['noneKey'],
                            'name' => $aggConfig['noneLabel'],
                            'count' => (int) ($aggResults['top_reverse_nested']['doc_count'] ?? $aggResults['doc_count'])
                        ];
                        if ( in_array((int) $aggConfig['noneKey'], $aggFilterValues) ) {
                            $item['active'] = true;
                        }
                        $items[] = $item;
                    }

                    // get any count
                    $aggResults = $this->getAggregationData($aggData, $aggName, 'count_any');
                    if ( $aggResults['buckets'][0]['doc_count'] ?? null) {
                        $item = [
                            'id' => $aggConfig['anyKey'],
                            'name' => $aggConfig['anyLabel'],
                            'count' => (int) ($aggResults['buckets'][0]['top_reverse_nested']['doc_count'] ?? $aggResults['buckets'][0]['doc_count'])
                        ];
                        if ( in_array((int) $aggConfig['anyKey'], $aggFilterValues) ) {
                            $item['active'] = true;
                        }
                        $items[] = $item;
                    }

                    // get values
                    $aggResults = $this->getAggregationData($aggData, $aggName, $aggName);
                    foreach ($aggResults['buckets'] ?? [] as $result) {
                        if (!isset($result['key'])) continue;
                        $parts = explode('_', $result['key'], 2);

                        $item = [
                            'id' => (int) $parts[0],
                            'name' => $parts[1],
                            'count' => (int) ($result['top_reverse_nested']['doc_count'] ?? $result['doc_count'])
                        ];
                        if ( in_array((int) $parts[0], $aggFilterValues) ) {
                            $item['active'] = true;
                        }

                        $items[] = $item;
                    }
                    $results[$aggName] = $this->sanitizeTermAggregationItems($items, $aggConfig, $aggFilterConfigs);
                    break;
                case self::AGG_BOOLEAN:
                    $aggResults = $this->getAggregationData($aggData, $aggName, $aggName);
                    $items = [];
                    foreach ($aggResults['buckets'] ?? [] as $result) {
                        if (!isset($result['key'])) continue;
                        $items[] = [
                            'id' => $result['key'],
                            'name' => $result['key_as_string'],
                            'count' => (int) ($result['top_reverse_nested']['doc_count'] ?? $result['doc_count'])
                        ];
                    }
                    $results[$aggName] = $this->sanitizeTermAggregationItems($items, $aggConfig, $aggFilterConfigs);
                    break;
            }
        }

        return $results;
    }

    private function getAggregationData(array $data, string $top_agg_name, string $agg_name): ?array
    {
        $results = $data;
        while($results[$agg_name] ?? $results[$top_agg_name] ?? null) {
            $results = $results[$agg_name] ?? $results[$top_agg_name];
        }
        return $results;
    }

    /**
     * Return filters that are used in aggregations
     * Todo: Now based on aggregation type or config (mostly nested), should be based on aggregation config check!
     */
    private function getAggregationFilters(): array
    {

        $filters = $this->getSearchConfig();
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
    protected function isGlobalAggregation($config): bool
    {
        return (in_array($config['type'], [self::AGG_GLOBAL_STATS], true) || ($config['globalAggregation'] ?? false));
    }

    protected function isNestedAggregation($config): bool
    {
        return (in_array($config['type'], [self::AGG_NESTED_ID_NAME, self::AGG_NESTED_KEYWORD], true) || ($config['nestedPath'] ?? false));
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
