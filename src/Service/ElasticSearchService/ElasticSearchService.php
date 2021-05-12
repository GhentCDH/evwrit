<?php

namespace App\Service\ElasticSearchService;

use Elastica\Aggregation;
use Elastica\Query;
use Elastica\Query\AbstractQuery;
use Elastica\Index;


abstract class ElasticSearchService implements ElasticSearchServiceInterface
{
    private $client;
    private $indexName;
    private $index;

    const MAX_AGG = 2147483647;
    const MAX_SEARCH = 10000;

    protected const AGG_NUMERIC = "numeric";
    protected const AGG_OBJECT = "object";
    protected const AGG_EXACT_TEXT = "exact_text";
    protected const AGG_NESTED = "nested";
    protected const AGG_BOOLEAN = "bool";
    protected const AGG_MULTIPLE_FIELDS_OBJECT = "multiple_fields_object";

    protected const FILTER_NUMERIC = "numeric";
    protected const FILTER_NUMERIC_MULTIPLE = "numeric_multiple";
    protected const FILTER_NESTED = "nested";
    protected const FILTER_NESTED_TOGGLE = "nested_toggle";
    protected const FILTER_OBJECT = "object";
    protected const FILTER_TEXT = "text";
    protected const FILTER_TEXT_EXACT = "text_exact";
    protected const FILTER_TEXT_MULTIPLE = "text_multiple";
    protected const FILTER_BOOLEAN = "boolean";
    protected const FILTER_MULTIPLE_FIELDS_OBJECT = "multiple_fields_object";
    protected const FILTER_DATE_RANGE = "date_range";



    protected function __construct(
        ElasticSearchClient $client,
        string $indexName
    ) {
        $this->client = $client;
        $this->indexName = $indexName;
        $this->index = $this->client->getIndex($indexName);
    }

    /**
     * Return Elasticsearch Client
     *
     * @return Index
     */
    protected function getClient(): ElasticSearchClient
    {
        return $this->client;
    }

    /**
     * Return Elasticsearch Index
     *
     * @return Index
     */
    protected function getIndex(): Index
    {
        return $this->index;
    }

    /**
     * Add aggregation details to search service
     * Return array of aggregation_field => aggregation_type
     * @return array
     */
    protected abstract function getAggregationFilterConfig(): array;

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
        $defaults = static::getDefaultSearchParameters();

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
            switch ($params['orderBy']) {
                // convert fieldname to elastic expression
                case 'name':
                    $result['orderBy'] = ['name.keyword'];

                    break;
                case 'date':

                    break;
                default:
                    $result['orderBy'] = $defaults['orderBy'];
                    break;
            }
        }

        return $result;
    }

    protected function sanitizeSearchFilters(array $params): array
    {
        // Init Filters
        $filterDefaults = static::getDefaultSearchFilters();
        $filters = $filterDefaults;

        // Limit allowed filters
        $filterConfig = static::getSearchFilterConfig();
        $params_filtered = array_intersect_key($params, $filterConfig);

        // Validate values
        foreach ($params_filtered as $filterName => $filterValue) {
            switch ($filterConfig[$filterName]['type']) {
                case self::FILTER_NESTED:
                    if ( is_array($filterValue) || is_numeric($filterValue) ) {
                        $filters[$filterName] = $filterValue;
                    }
                    break;
                case self::FILTER_BOOLEAN:
                    $filters[$filterName] = ($filterValue === '1');
                    break;
                case 'date':
                    if (is_array($filterValue)) {
                        $filters[$filterName] = $filterValue;
                        foreach (array_keys($filterValue) as $subKey) {
                            switch ($subKey) {
                                case 'year_from':
                                case 'year_to':
                                    if (is_numeric($filterValue[$subKey])) {
                                        $filters[$filterName][$subKey] = $filterValue[$subKey];
                                    }
                                    break;
                            }
                        }
                    }
                    break;
                case self::FILTER_TEXT_MULTIPLE:
                    if (is_array($filterValue)) {
                        $filters[$filterName] = $filterValue;
                    }
                    break;
                case self::FILTER_TEXT:
                    if (is_array($filterValue)) {
                        $filters[$filterName] = $filterValue;
                    }
                    if (is_string($filterValue)) {
                        $combination = $params[$filterName.'_combination'] ?? 'any';
                        $combination = in_array($combination, ['any','all','phrase'],true) ? $combination: 'any';

                        $filters[$filterName] = [
                            'text' => $filterValue,
                            'combination' => $combination
                        ];
                    }
                    break;
                default:
                    if (is_string($filterValue)) {
                        $filters[$filterName] = $filterValue;
                    }
                    break;
            }
        }

        dump($filters);

        return $filters;
    }

    protected function search(array $params = null): array
    {
        // sanitize search parameters
        $searchParams = self::sanitizeSearchParameters($params);

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
            $query->setQuery(self::createSearchQuery($searchFilters));
            $query->setHighlight(self::createHighlight($searchFilters));
        }

        // Search
        dump(json_encode($query->getQuery()->toArray()));
        $data = $this->getIndex()->search($query)->getResponse()->getData();

        // Format response
        // @flamsens: elastic 7 needs ['value']
        $response = [
            'count' => $data['hits']['total']['value'],
            'data' => []
        ];

        // Build array to remove _stemmer or _original blow
        $rename = [];
        $filterConfig = static::getSearchFilterConfig();
        foreach ($searchFilters as $filterName => $filterValue)
        {
            switch ($filterConfig[$filterName]['type']) {
                case self::FILTER_TEXT:
                    if (isset($filterValue['field'])) {
                        $rename[$filterValue['field']] = explode('_', $filterValue['field'])[0];
                    }
                    break;
                case self::FILTER_TEXT_MULTIPLE:
                    $filterValues = $filterValue;
                    foreach($filterValues as $filterValue) {
                        if (isset($filterValue['field'])) {
                            $rename[$filterValue['field']] = explode('_', $filterValue['field'])[0];
                        }
                    }
                    break;
            }
        }
        foreach ($data['hits']['hits'] as $result) {
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
            $response['data'][] = $part;
        }

        return $response;
    }

    protected function aggregate(array $filters): array
    {
        $aggFilters = static::getAggregationFilterConfig();
        if ( !count($aggFilters) ) {
            return [];
        }

        // create search query
        $query = (new Query())
            ->setQuery(self::createSearchQuery($this->sanitizeSearchFilters($filters)))
            // Only aggregation will be used
            ->setSize(0);

        // add aggregations
        foreach($aggFilters as $fieldName => $aggFilter) {
            $aggType = $aggFilter['type'];
            switch($aggType) {
                case self::AGG_NUMERIC:
                    $query->addAggregation(
                        (new Aggregation\Terms($fieldName))
                            ->setSize(self::MAX_AGG)
                            ->setField($fieldName)
                    );
                    break;
                case self::AGG_OBJECT:
                    $query->addAggregation(
                        (new Aggregation\Terms($fieldName))
                            ->setSize(self::MAX_AGG)
                            ->setField($fieldName . '.id')
                            ->addAggregation(
                                (new Aggregation\Terms('name'))
                                    ->setField($fieldName . '.name.keyword')
                            )
                    );
                    break;
                case self::AGG_EXACT_TEXT:
                    $query->addAggregation(
                        (new Aggregation\Terms($fieldName))
                            ->setSize(self::MAX_AGG)
                            ->setField($fieldName . '.keyword')
                    );
                    break;
                case self::AGG_NESTED:
                    $query->addAggregation(
                        (new Aggregation\Nested($fieldName, $fieldName))
                            ->addAggregation(
                                (new Aggregation\Terms('id'))
                                    ->setSize(self::MAX_AGG)
                                    ->setField($fieldName . '.id')
                                    ->addAggregation(
                                        (new Aggregation\Terms('name'))
                                            ->setField($fieldName . '.name.keyword')
                                    )
                            )
                    );
                    break;
                case self::AGG_BOOLEAN:
                    $query->addAggregation(
                        (new Aggregation\Terms($fieldName))
                            ->setSize(self::MAX_AGG)
                            ->setField($fieldName)
                    );
                    break;
                case self::AGG_MULTIPLE_FIELDS_OBJECT:
                    // fieldName = [
                    //     [multiple_names] (e.g., [patron, scribe, related]),
                    //      'actual field name' (e.g. 'person'),
                    //      'dependend field name' (e.g. 'role')
                    //  ]
                    foreach ($fieldName[0] as $key) {
                        $query->addAggregation(
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

        // parse query result
        $searchResult = $this->getIndex()->search($query);
        $results = [];

        foreach($aggFilters as $fieldName => $aggFilter) {
            $aggType = $aggFilter['type'];
            switch($aggType) {
                case self::AGG_NUMERIC:
                    $aggregation = $searchResult->getAggregation($fieldName);
                    foreach ($aggregation['buckets'] as $result) {
                        $results[$fieldName][] = [
                            'id' => $result['key'],
                            'name' => $result['key'],
                        ];
                    }
                    break;
                case self::AGG_OBJECT:
                    $aggregation = $searchResult->getAggregation($fieldName);
                    foreach ($aggregation['buckets'] as $result) {
                        $results[$fieldName][] = [
                            'id' => $result['key'],
                            'name' => $result['name']['buckets'][0]['key'],
                        ];
                    }
                    break;
                case self::AGG_EXACT_TEXT:
                    $aggregation = $searchResult->getAggregation($fieldName);
                    foreach ($aggregation['buckets'] as $result) {
                        $results[$fieldName][] = [
                            'id' => $result['key'],
                            'name' => $result['key'],
                        ];
                    }
                    break;
                case self::AGG_NESTED:
                    $aggregation = $searchResult->getAggregation($fieldName);
                    foreach ($aggregation['id']['buckets'] as $result) {
                        $results[$fieldName][] = [
                            'id' => $result['key'],
                            'name' => $result['name']['buckets'][0]['key'],
                        ];
                    }
                    break;
                case self::AGG_BOOLEAN:
                    $aggregation = $searchResult->getAggregation($fieldName);
                    foreach ($aggregation['buckets'] as $result) {
                        $results[$fieldName][] = [
                            'id' => $result['key'],
                            'name' => $result['key_as_string'],
                        ];
                    }
                    break;
                case self::AGG_MULTIPLE_FIELDS_OBJECT:
                    /*
                    foreach ($fieldNames as $fieldName) {
                        // fieldName = [
                        //     [multiple_names] (e.g., [patron, scribe, related]),
                        //      'actual field name' (e.g. 'person'),
                        //      'dependent field name' (e.g. 'role')
                        //  ]

                        //  a filter is set for the actual field name
                        if (isset($filterValues['multiple_fields_object'][$fieldName[1]])) {
                            $ids = [];
                            foreach ($fieldName[0] as $key) {
                                $aggregation = $searchResult->getAggregation($key);
                                foreach ($aggregation['id']['buckets'] as $result) {
                                    if (!in_array($result['key'], $ids)) {
                                        $ids[] = $result['key'];
                                        $results[$fieldName[1]][] = [
                                            'id' => $result['key'],
                                            'name' => $result['name']['buckets'][0]['key'],
                                        ];
                                    }

                                    // check if this result is a result of the actual field filter
                                    if ($result['key'] == $filterValues['multiple_fields_object'][$fieldName[1]][1]) {
                                        $results[$fieldName[2]][] = [
                                            'id' => $key,
                                            'name' => $this->roles[str_replace('_public', '', $key)]->getName() . ' (' . $result['doc_count'] . ')',
                                        ];
                                    }
                                }
                            }
                        } else {
                            // prevent duplicate entries
                            $ids = [];
                            foreach ($fieldName[0] as $key) {
                                $aggregation = $searchResult->getAggregation($key);
                                foreach ($aggregation['id']['buckets'] as $result) {
                                    if (!in_array($result['key'], $ids)) {
                                        $ids[] = $result['key'];
                                        $results[$fieldName[1]][] = [
                                            'id' => $result['key'],
                                            'name' => $result['name']['buckets'][0]['key'],
                                        ];
                                    }
                                }
                            }
                        }
                    }
                    */
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

    protected static function createSearchQuery(array $filters): Query\BoolQuery
    {
        $filterConfig = static::getSearchFilterConfig();
        $filterQuery = new Query\BoolQuery();
        dump($filters);
        dump($filterConfig);
        foreach ($filters as $filterName => $filterValue) {
            switch ($filterConfig[$filterName]['type']) {
                case self::FILTER_NUMERIC:
                    $filterQuery->addMust(
                        new Query\Match($filterName, $filterValue)
                    );
                    break;
                case self::FILTER_OBJECT:
                    // If value == -1, select all entries without a value for a specific field
                    if ($filterValue == -1) {
                        $filterQuery->addMustNot(
                            new Query\Exists($filterValue)
                        );
                    } else {
                        $filterQuery->addMust(
                            new Query\Match($filterName . '.id', $filterValue)
                        );
                    }
                    break;
                case self::FILTER_DATE_RANGE:
                    // If type is not set, us broad match (backward compatibility)
                    // The data interval must exactly match the search interval
                    if (isset($filterValue['type']) && $filterValue['type'] == 'exact') {
                        if (isset($filterValue['startDate'])) {
                            $filterQuery->addMust(
                                new Query\Match($filterValue['floorField'], $filterValue['startDate'])
                            );
                        }
                        if (isset($filterValue['endDate'])) {
                            $filterQuery->addMust(
                                new Query\Match($filterValue['ceilingField'], $filterValue['endDate'])
                            );
                        }
                    }
                    // The data interval must be included in the search interval
                    // If only start or end: exact match with start or end
                    // range must be between floor and ceiling
                    if (isset($filterValue['type']) && $filterValue['type'] == 'included') {
                        if (isset($filterValue['startDate']) && !isset($filterValue['endDate'])) {
                            $filterQuery->addMust(
                                new Query\Match($filterValue['floorField'], $filterValue['startDate'])
                            );
                        } elseif (isset($filterValue['endDate']) && !isset($filterValue['startDate'])) {
                            $filterQuery->addMust(
                                new Query\Match($filterValue['ceilingField'], $filterValue['endDate'])
                            );
                        } else {
                            $filterQuery->addMust(
                                (new Query\Range())
                                    ->addField($filterValue['floorField'], ['gte' => $filterValue['startDate']])
                            );
                            $filterQuery->addMust(
                                (new Query\Range())
                                    ->addField($filterValue['ceilingField'], ['lte' => $filterValue['endDate']])
                            );
                        }
                    }
                    // The data interval must include the search interval
                    // If only start or end: exact match with start or end
                    // range must be between floor and ceiling
                    if (isset($filterValue['type']) && $filterValue['type'] == 'include') {
                        if (isset($filterValue['startDate']) && !isset($filterValue['endDate'])) {
                            $filterQuery->addMust(
                                new Query\Match($filterValue['floorField'], $filterValue['startDate'])
                            );
                        } elseif (isset($filterValue['endDate']) && !isset($filterValue['startDate'])) {
                            $filterQuery->addMust(
                                new Query\Match($filterValue['ceilingField'], $filterValue['endDate'])
                            );
                        } else {
                            $filterQuery->addMust(
                                (new Query\Range())
                                    ->addField($filterValue['floorField'], ['lte' => $filterValue['startDate']])
                            );
                            $filterQuery->addMust(
                                (new Query\Range())
                                    ->addField($filterValue['ceilingField'], ['gte' => $filterValue['endDate']])
                            );
                        }
                    }
                    // The data interval must overlap with the search interval
                    // floor or ceiling must be within range, or range must be between floor and ceiling
                    else {
                        $args = [];
                        if (isset($filterValue['startDate'])) {
                            $args['gte'] = $filterValue['startDate'];
                        }
                        if (isset($filterValue['endDate'])) {
                            $args['lte'] = $filterValue['endDate'];
                        }
                        $subQuery = (new Query\BoolQuery())
                            // floor
                            ->addShould(
                                (new Query\Range())
                                    ->addField(
                                        $filterValue['floorField'],
                                        $args
                                    )
                            )
                            // ceiling
                            ->addShould(
                                (new Query\Range())
                                    ->addField(
                                        $filterValue['ceilingField'],
                                        $args
                                    )
                            );
                        if (isset($filterValue['startDate']) && isset($filterValue['endDate'])) {
                            $subQuery
                                // between floor and ceiling
                                ->addShould(
                                    (new Query\BoolQuery())
                                        ->addMust(
                                            (new Query\Range())
                                                ->addField($filterValue['floorField'], ['lte' => $filterValue['startDate']])
                                        )
                                        ->addMust(
                                            (new Query\Range())
                                                ->addField($filterValue['ceilingField'], ['gte' => $filterValue['endDate']])
                                        )
                                );
                        }
                        $filterQuery->addMust(
                            $subQuery
                        );
                    }
                    break;
                case self::FILTER_NESTED:
                    // multiple values?
                    if ( is_array($filterValue) ) {
                        $subquery = new Query\BoolQuery();
                        foreach( $filterValue as $val) {
                            $subquery->addShould(['match' => [$filterName . '.id' => $val]]);
                        }
                        $filterQuery->addMust(
                            (new Query\Nested())
                                ->setPath($filterName)
                                ->setQuery($subquery)
                        );
                    }
                    // single value
                    else {
                        // If value == -1, select all entries without a value for a specific field
                        if ($filterValue == -1) {
                            $filterQuery->addMustNot(
                                (new Query\Nested())
                                    ->setPath($filterName)
                                    ->setQuery(
                                        (new Query\BoolQuery())
                                            ->addMust(new Query\Exists($filterName))
                                    )
                            );
                        } else {
                            $filterQuery->addMust(
                                (new Query\Nested())
                                    ->setPath($filterName)
                                    ->setQuery(
                                        (new Query\BoolQuery())
                                            ->addMust(['match' => [$filterName . '.id' => $filterValue]])
                                    )
                            );
                        }
                    }
                    break;
                case self::FILTER_NESTED_TOGGLE:
                    foreach ($filterValue as $key => $filterValue) {
                        // value = [actual value, include/exclude]
                        if (!$filterValue[1]) {
                            // management collection not present
                            // no management collections present or only other management collections present
                            $filterQuery->addMust(
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
                            $filterQuery->addMust(
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
                    $filterQuery->addMust(self::constructTextQuery($filterName, $filterValue));
                    break;
                case self::FILTER_TEXT_MULTIPLE:
                    $subQuery = new Query\BoolQuery();
                    foreach ($filterValue as $field => $filterValue) {
                        $subQuery->addShould(self::constructTextQuery($field, $filterValue));
                    }
                    $filterQuery->addMust($subQuery);
                    break;
                case self::FILTER_TEXT_EXACT:
                    if ($filterValue == -1) {
                        $filterQuery->addMustNot(
                            new Query\Exists($filterName)
                        );
                    } else {
                        $filterQuery->addMust(
                            (new Query\Match($filterName . '.keyword', $filterValue))
                        );
                    }
                    break;
                case self::FILTER_BOOLEAN:
                    $filterQuery->addMust(
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
                        $filterQuery->addMust($subQuery);
                    }
                    break;
            }
        }
        return $filterQuery;
    }

    protected static function createHighlight(array $filters): array
    {
        $highlights = [
            'number_of_fragments' => 0,
            'pre_tags' => ['<mark>'],
            'post_tags' => ['</mark>'],
            'fields' => [],
        ];

        $filterConfig = static::getSearchFilterConfig();

        foreach( $filters as $filterName => $filterValue ) {
            $filterType = $filterConfig[$filterName]['type'];
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
    protected static function constructTextQuery($key, $value): AbstractQuery
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
}
