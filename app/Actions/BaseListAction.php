<?php

namespace App\Actions;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

abstract class BaseListAction
{
    abstract protected function modelClass(): string;

    protected function defaultWith(): array
    {
        return [];
    }

    protected function defaultOrder(): array
    {
        return ['created_at' => 'desc'];
    }

    /**
     * Convenience: apply exact-match filters for given columns from params.
     * Delegates to global `whereEach()` helper.
     */
    protected function applyExactFilters(Builder $query, array $columns, array $params): Builder
    {
        return whereEach($query, $columns, $params);
    }

    /**
     * Convenience: apply between/min/max range filter on a column.
     * Delegates to global `rangeFilter()` helper.
     */
    protected function applyRangeFilter(Builder $query, string $column, array $params): Builder
    {
        return rangeFilter($query, $column, $params);
    }

    /**
     * Convenience: apply date range filter (defaults min to epoch, max to now).
     * Delegates to global `rangeDateFilter()` helper.
     */
    protected function applyDateRangeFilter(Builder $query, string $column, array $params): Builder
    {
        return rangeDateFilter($query, $column, $params);
    }

    /**
     * Convenience: apply full-text-like search on translatable or plain columns.
     * Delegates to global `filterLike()` helper.
     */
    protected function applySearch(Builder $query, array|string $columns, array $params): Builder
    {
        return filterLike($query, $columns, $params);
    }

    /**
     * Override to apply custom filters using $params and the helpers above.
     */
    protected function applyFilters(Builder $query, array $params): void
    {
        //
    }

    /**
     * Apply ordering. If $params contains `order_by`, delegates to global `orderBy()` helper
     * so that ?order_by=name&order_type=asc works automatically from the request URL.
     * Otherwise uses the default order.
     */
    protected function applyOrder(Builder $query, array $order, array $params = []): void
    {
        if (!empty($params['order_by'])) {
            orderBy($query, $params);
            return;
        }

        foreach ($order as $column => $direction) {
            $query->orderBy($column, $direction);
        }
    }

    public function execute(array $params = []): LengthAwarePaginator
    {
        $query = ($this->modelClass())::query()->with($this->defaultWith());

        $this->applyFilters($query, $params);
        $this->applyOrder($query, $this->defaultOrder(), $params);

        return $query->paginate((int) config('pagination.per_page', 20));
    }
}
