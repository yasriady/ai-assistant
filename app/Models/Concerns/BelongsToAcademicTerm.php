<?php

namespace App\Models\Concerns;

use App\Services\Term\ActiveTerm;
use Illuminate\Database\Eloquent\Builder;

trait BelongsToAcademicTerm
{
    public function scopeForTerm(Builder $query, ?string $termCode = null): Builder
    {
        $termCode ??= app(ActiveTerm::class)->current();

        return $query->where($this->getTable().'.term_code', $termCode);
    }

    public function scopeForActiveTerm(Builder $query): Builder
    {
        return $this->scopeForTerm($query);
    }
}
