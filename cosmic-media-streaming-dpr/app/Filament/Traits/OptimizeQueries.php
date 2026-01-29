<?php

namespace App\Filament\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;

trait OptimizeQueries
{
    /**
     * Optimize Eloquent query with eager loading and caching
     */
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        
        // Add common relationships to eager load
        if (method_exists(static::getModel(), 'getEagerLoadableRelations')) {
            $relations = static::getModel()::getEagerLoadableRelations();
            if (!empty($relations)) {
                $query->with($relations);
            }
        }
        
        return $query;
    }
    
    /**
     * Cache table queries for better performance
     * Call this in table() method: static::cacheTableQuery($query)
     */
    public static function cacheTableQuery(Builder $query, int $minutes = 5): Builder
    {
        $cacheKey = static::class . '_table_' . md5($query->toSql() . serialize($query->getBindings()));
        
        return $query->remember($minutes * 60)->cacheTags([static::class]);
    }
}
