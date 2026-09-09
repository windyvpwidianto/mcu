<?php
namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class EnabledEventTypeScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    protected $fillter;
    public function __contstruct(array $fillter)
    {
        $this->fillter = $fillter;
    }
    public function apply(Builder $builder, Model $model): void
    {
        $builder->when(
            fn($query) => $query->whereHas('EventCategories', function ($q) {
                $q->where('status', 'enabled');
            })
        );
        if ($this->filter['status'] ?? false) {
            $builder->where('status', 'enabled');
        }
    }
}
