<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

trait HasSettingSearch
{
    /**
     * @param  Builder<static>  $query
     */
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (! filled($term)) {
            return $query;
        }

        $columns = property_exists($this, 'searchableColumns')
            ? $this->searchableColumns
            : ['name'];

        return $query->where(function (Builder $q) use ($term, $columns): void {
            foreach ($columns as $column) {
                $q->orWhere($column, 'like', "%{$term}%");
            }
        });
    }
}
