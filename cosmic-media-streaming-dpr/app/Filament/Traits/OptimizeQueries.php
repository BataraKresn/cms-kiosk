<?php

namespace App\Filament\Traits;

use Illuminate\Database\Eloquent\Builder;

trait OptimizeQueries
{
    /**
     * Optimize Eloquent query with eager loading
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
}
