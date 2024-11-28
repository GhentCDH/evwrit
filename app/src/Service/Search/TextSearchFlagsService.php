<?php

namespace App\Service\Search;


use App\Resource\ElasticIdNameResource;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

class TextSearchFlagsService
{

    public function __construct(ContainerInterface $container)
    {
        $this->mycontainer = $container;
    }


    protected function getContainer()
    {
        return $this->mycontainer;
    }

    protected function getDefaultSearchParameters(): array
    {
        return [
            'limit' => 25,
            'page' => 1,
            'ascending' => 1,
            'orderBy' => ['title'],
        ];

    }

    protected function sanitizeSearchParameters(array $config, array $params): array
    { // Set default parameters
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

        $result['orderBy'] = $this->getOrderBy($params);
        $result['sortDir'] = $this->getAscending($params);
        $result['offset'] = ($result['page'] - 1) * $result['limit'];

        return $result;
    }

    private function getAscending(array $params)
    {
        $ascending = isset($params['ascending']) ? $params['ascending'] : 1;
        if (($ascending == '0' || $ascending == '1')) {
            $ascending = intval($ascending);
        }

        return $ascending === 1 ? 'asc' : 'desc';
    }

    private function getOrderBy(array $params)
    {
        $orderBy = isset($params['orderBy']) ? $params['orderBy'] : 'title';

        switch ($orderBy) {
            // convert fieldname to elastic expression
            case 'title':
            case 'year_begin':
            case 'year_end':
            case 'id':
            case 'tm_id':
            case 'flag_review_done':
            case 'flag_needs_attention':
                break;
            default:
                $orderBy = 'title';
        }

        return $orderBy;
    }

    private function getFieldConfig()
    {
        $mapInt = function ($value) {
            if (is_array($value)) {
                if (count($value) === 1)
                    $value = $value[0];
                else
                    return array_map(function ($v) {
                        return intval($v);
                    }, $value);
            }

            if (!is_numeric($value)) {
                return null;
            }

            return [intval($value)];
        };

        $whereInt = function ($field, $value) {
            return "$field IN (" . implode(',', $value) . ")";
        };

        $whereBoolean = function ($field, $value) {
            $value = $value[0];

            if ($value) return "$field = true";
            return "($field = false OR $field IS NULL)";
        };

        $mapBoolean = function ($value) {

            if (is_array($value)) {
                $value = $value[0];
            }

            if (is_bool($value)) {
                return $value;
            }

            if ($value !== 'true' && $value !== 'false') {
                return null;
            }


            return [$value === 'true'] ?? null;
        };

        $fieldConfig = [
            'era' => ['id' => 'era', 'field' => 'era_id', 'mapValue' => $mapInt, 'where' => $whereInt],
            'level_category_category' => ['id' => 'level_category_category', 'field' => 'level_category_category_id', 'mapValue' => $mapInt, 'where' => $whereInt],
            'project' => ['id' => 'project', 'field' => 'project_id', 'mapValue' => $mapInt, 'where' => $whereInt],
            'flag_review_done' => ['id' => 'flag_review_done', 'field' => 'text_flags.review_done', 'mapValue' => $mapBoolean, 'where' => $whereBoolean],
            'flag_needs_attention' => ['id' => 'flag_needs_attention', 'field' => 'text_flags.needs_attention', 'mapValue' => $mapBoolean, 'where' => $whereBoolean],

        ];

        $allowedFilters = [];

        foreach ($fieldConfig as $key => $value) {
            $allowedFilters[] = $key;
        }

        return ['config' => $fieldConfig, 'allowedFilters' => $allowedFilters];
    }


    public function sanitizeSearchFilters(array $config, array $filters)
    {
        $fieldConfig = $config['config'];
        $allowedFilters = $config['allowedFilters'];
        $result = [];

        foreach ($filters as $key => $value) {
            if (in_array($key, $allowedFilters)) {
                $config = $fieldConfig[$key];
                $field = $config['field'];
                $mappedValue = $config['mapValue']($value);

                if ($field && $mappedValue !== null) {
                    $where = $config['where']($field, $mappedValue);
                    $result[] = [
                        'id' => $config['id'],
                        'field' => $field,
                        'value' => $mappedValue,
                        'where' => $where
                    ];
                }
            }
        }


        return $result;
    }


    public function filters(): array
    {
        $eraData = $this->getContainer()->get('era_repository')->findAll(10000);
        $projectData = $this->getContainer()->get('project_repository')->findAll(10000);
        $levelCategoryCategoryData = $this->getContainer()->get('level_category_category_repository')->findAll(10000);


        return ['era' => ElasticIdNameResource::collection($eraData)->toArray(),
            'level_category_category' => ElasticIdNameResource::collection($levelCategoryCategoryData)->toArray(),
            'project' => ElasticIdNameResource::collection($projectData)->toArray(),
            'flag_review_done' => [['id' => null, 'name' => 'Alles',], ['id' => false, 'name' => 'Nee',], ['id' => true, 'name' => 'Ja'],],
            'flag_needs_attention' => [['id' => null, 'name' => 'Alles',], ['id' => false, 'name' => 'Nee',], ['id' => true, 'name' => 'Ja'],]];

    }

    private function buildQuery(array $where, string $orderBy, string $ascending)
    {
        $repo = $this->getContainer()->get('text_repository');

        $query = $repo->defaultQuery()
            ->with([])
            ->join('text__project', 'text__project.text_id', '=', 'text.text_id')
            ->join('level', 'level.text_id', '=', 'text.text_id')
            ->join('level__level_category', 'level.level_id', '=', 'level__level_category.level_id')
            ->join('level_category', 'level_category.level_category_id', '=', 'level__level_category.level_category_id')
            ->leftJoin('text_flags', 'text_flags.text_id', '=', 'text.text_id')
            ->select('text.tm_id',
                'text.title',
                'text.year_begin',
                'text.year_end',
                'text.text_id',
            )
            ->selectRaw('coalesce( "text_flags"."needs_attention", false) as flag_needs_attention')
            ->selectRaw('coalesce( "text_flags"."review_done", false) as flag_review_done')
            ->distinct();

        foreach ($where as $item) {
            $query = $query->whereRaw($item['where']);
        }


        return $query;
    }

    private function mapData($data)
    {
        $result = [];

        foreach ($data as $d) {
            $result[] = [
                'tm_id' => $d->tm_id,
                'title' => $d->title,
                'year_begin' => $d->year_begin,
                'year_end' => $d->year_end,
                'id' => $d->text_id,
                'flag_needs_attention' => is_null($d->flag_needs_attention) ? false : $d->flag_needs_attention,
                'flag_review_done' => is_null($d->flag_review_done) ? false : $d->flag_review_done
            ];
        }


        return $result;
    }

    public function search(Request $request): array
    {
        $config = $this->getFieldConfig();
        $filters = $this->sanitizeSearchFilters($config, $request->query->all('filters') ?? []);
        $params = $this->sanitizeSearchParameters($config, $request->query->all() ?? []);

        $query = $this->buildQuery($filters, $params['orderBy'], $params['sortDir']);
        $count = $query->count('text.text_id');

        if ($count < $params['offset'] * $params['limit']) {
            $data = [];
        } else {
            $data = $this->mapData($query->offset($params['offset'])->limit($params['limit'])->get());
        }

        return [
            'params' => $params,
            'filters' => $filters,
            'data' => $data,
            'count' => $count
        ];

    }
}

?>