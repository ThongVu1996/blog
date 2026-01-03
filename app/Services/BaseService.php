<?php

namespace App\Services;

use Closure;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

abstract class BaseService
{
    /**
     * @var string[]
     */
    protected $orderDirMap = [
        'ascending' => 'asc',
        'descending' => 'desc',
    ];

    /**
     * [
     *   field1,
     *   field2
     * ]
     *
     * @var string[]
     */
    protected $searchables = [];

    /**
     * [
     *    columnKey => fieldName
     * ]
     *
     * @var string[]
     */
    protected $orderables = [];

    /**
     * [
     *    columnKey => filterFunction
     * ]
     *
     * @var string[]
     */
    protected $filterables = [];

    /**
     * @var boolean
     */
    protected $isPaginate = true;
    /**
     * Select fields
     *
     * @var string[]
     */
    protected $fields = [];

    /**
     * @var int
     */
    protected $perPage;

    /**
     * @var \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    protected $query;

    public function __construct()
    {
        $this->perPage = config('base.per_page');
    }
    /**
     * Create new service instance
     *
     * @return $this
     */
    public static function getInstance()
    {
        return app(static::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    protected function currentQuery()
    {
        if (!$this->query) {
            $this->newQuery();
        }

        return $this->query;
    }

    /**
     * @param Request $request
     * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function data($search, $orders, $filters, $perPage = null, $all = false)
    {
        $perPage = $perPage ?? $this->perPage;
        $query = $this->currentQuery();

        $this->applySearchToQuery($search, $query);
        $this->applyFilterToQuery($filters, $query);
        $this->applyOrderToQuery($orders, $query);

        if ($all) {
            return $query->get();
        }
        return $query->paginate(intval($perPage))->withQueryString();
    }

    /**
     * @param string $search
     * @param \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    protected function applySearchToQuery($search, Builder $query)
    {
        if (empty($search) || !is_string($search) || !count($this->searchables)) {
            return $query;
        }

        $content = '%' . trim($search) . '%';
        $query->where(function ($q) use ($content) {
            foreach ($this->searchables as $searchable) {
                $q->orWhere($searchable, 'ILIKE', $content);
            }
        });

        return $query;
    }

    /**
     * @param array $filters
     * @param \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    protected function applyFilterToQuery($filters, $query)
    {
        if (!is_array($filters)) {
            return $query;
        }

        foreach ($filters as $filter) {
            $issetFilter = !empty($filter['key']) && isset($filter['data']) && isset($this->filterables[$filter['key']]);
            if ($issetFilter && $filter['data'] !== null && $filter['data'] !== '') {
                $funName = $this->filterables[$filter['key']];
                if (method_exists($this, $funName)) {
                    $this->{$funName}($query, $filter, $filters);
                } else {
                    $this->defaultFilter($query, $filter, $filters);
                }
            }
        }

        return $query;
    }

    /**
     * applyCustomerRole
     *
     * @param  Builder $query
     * @param  int $companyId
     * @param  bool $condition
     * @return Builder
     */
    protected function applyCustomerRole(Builder $query, $companyId, bool $condition): Builder
    {
        return $query->when($condition, function (Builder $query) use ($companyId) {
            $query->where('companies.id', $companyId);
        });
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder $query
     * @param array $filter
     * @param array $filters
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    protected function defaultFilter($query, $filter, $filters)
    {
        $query->where($this->filterables[$filter['key']], $filter['data']);

        return $query;
    }

    /**
     * @param array $orders
     * @param \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    protected function applyOrderToQuery($orders, $query)
    {
        if (!is_array($orders)) {
            return $query;
        }

        foreach ($orders as $order) {
            if (!empty($order['key']) && !empty($order['dir'])) {
                $dir = Str::lower($order['dir']);
                if (!empty($this->orderDirMap[$dir]) && !empty($this->orderables[$order['key']])) {
                    $column = $this->orderables[$order['key']];
                    $direction = $this->orderDirMap[$dir];
                    // Use NULLS FIRST / LAST for predictable sorting of NULLs
                    $nulls = $direction === 'asc' ? 'NULLS FIRST' : 'NULLS LAST';
                    $query->orderByRaw("$column $direction $nulls");
                }
            }
        }
        return $query;
    }

    /**
     * Set the relationships that should be eager loaded.
     *
     * @param array|string $relations
     * @param string|\Closure|null $callback
     * @return $this
     */
    public function with($relations, $callback = null)
    {
        $this->currentQuery()->with(...func_get_args());

        return $this;
    }

    /**
     * Add an exists clause to the query.
     *
     * @param \Closure $callback
     * @param string $boolean
     * @param bool $not
     * @return $this
     */
    public function whereExists(Closure $callback, $boolean = 'and', $not = false)
    {
        $this->currentQuery()->whereExists(...func_get_args());

        return $this;
    }

    /**
     * Add subselect queries to count the relations.
     *
     * @param mixed $relations
     * @return $this
     */
    public function withCount($relations)
    {
        $this->currentQuery()->withCount(...func_get_args());

        return $this;
    }

    /**
     * Add an "order by" clause to the query.
     *
     * @param \Illuminate\Database\Eloquent\Builder|string|\Closure|\Illuminate\Database\Query\Expression|\Illuminate\Database\Query\Builder $column
     * @param string $direction
     * @return $this
     *
     * @throws \InvalidArgumentException
     */
    public function orderBy($column, $direction = 'asc')
    {
        $this->currentQuery()->orderBy(...func_get_args());

        return $this;
    }

    /**
     * Add a basic where clause to the query.
     *
     * @param array|\Closure|string|\Illuminate\Database\Query\Expression $column
     * @param mixed|null $operator
     * @param mixed|null $value
     * @param string $boolean
     * @return $this
     */
    public function where($column, $operator = null, $value = null, $boolean = 'and')
    {
        $this->currentQuery()->where(...func_get_args());

        return $this;
    }

    /**
     * @return $this
     */
    public function newQuery()
    {
        $this->query = $this->makeNewQuery();

        return $this;
    }

    /**
     * Add a join clause to the query.
     *
     * @param string $table
     * @param string|\Closure $first
     * @param string|null $operator
     * @param string|null $second
     * @param string $type
     * @param bool $where
     * @return $this
     */
    public function join($table, $first, $operator = null, $second = null, $type = 'inner', $where = false)
    {
        $this->currentQuery()->join(...func_get_args());

        return $this;
    }

    /**
     * Set the columns to be selected.
     *
     * @param array $columns
     * @return $this
     */
    public function select(array $columns = ['*'])
    {
        $this->currentQuery()->select(...func_get_args());

        return $this;
    }

    /**
     * Add a "group by" clause to the query.
     *
     * @param  array|string  ...$groups
     * @return $this
     */
    public function groupBy(...$groups)
    {
        $this->currentQuery()->groupBy(...func_get_args());

        return $this;
    }

    /**
     * @param $data
     * @return mixed|null
     */
    protected function parseParams($data)
    {
        try {
            return json_decode($data, true);
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     *
     * @return string
     */
    protected function getSelectRaw(array $fields = [])
    {
        return implode(', ', $fields ?: $this->fields);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    abstract public function makeNewQuery();

    public function upsertOrDeleteWhereCondition($model, $insertData, $condition, $updateField, $insertDummy = false)
    {
        if ($insertDummy) {
            $model->where($condition['key'], $condition['value'])->delete();
            $model->insert($insertData);
            return;
        }
        [$updateArray, $insertArray] = collect($insertData)->partition(function ($item) {
            return isset($item['id']);
        });
        $model->whereNotIn('id', $updateArray->pluck('id'))
            ->where($condition['key'], $condition['value'])
            ->delete();
        $model->upsert($updateArray->toArray(), ['id'], $updateField);
        $model->insert($insertArray->toArray());
    }

    /**
     * upsertRawQuery
     *
     * @param  array $data
     * @param  array $fields
     * @param  string $tableName
     * @param  string $conflictString
     * @return int
     */
    protected function upsertRawQuery(array $data, array $fields, string $tableName, string $conflictString): int
    {
        if (empty($data)) {
            return 0;
        }

        $columns = array_keys($data[0]);
        $columnsList = implode(', ', $columns);
        $rowsSql = [];
        $bindings = [];
        foreach ($data as $item) {
            $rowsSql[] = '(' . rtrim(str_repeat('?, ', count($columns)), ', ') . ')';
            array_push($bindings, ...array_values($item));
        }
        $updateSql = implode(', ', array_map(fn($col) => "$col = EXCLUDED.$col", $fields));

        $sql = "INSERT INTO $tableName ($columnsList)
            VALUES " . implode(', ', $rowsSql) . "
            ON CONFLICT ($conflictString) WHERE deleted_at IS NULL
            DO UPDATE SET $updateSql";

        return DB::affectingStatement($sql, $bindings);
    }
}
