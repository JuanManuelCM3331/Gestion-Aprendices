<?php

namespace App\Repositories\Eloquent;

use App\Models\RiskParameter;
use App\Repositories\Contracts\RiskParameterRepositoryInterface;
use Illuminate\Support\Collection;

class EloquentRiskParameterRepository implements RiskParameterRepositoryInterface
{
    public function getAllOrdered(): Collection
    {
        return RiskParameter::with('updater')->orderBy('dimension')->orderBy('id')->get();
    }

    public function getKeyedWeights(): array
    {
        return RiskParameter::query()
            ->pluck('weight', 'code')
            ->map(fn ($v) => (float) $v)
            ->toArray();
    }

    public function updateWeight(int $id, float $weight, int $updatedBy): RiskParameter
    {
        $parameter = RiskParameter::findOrFail($id);
        $clamped = max((float) $parameter->min_value, min((float) $parameter->max_value, $weight));

        $parameter->update([
            'weight' => $clamped,
            'updated_by' => $updatedBy,
        ]);

        return $parameter->fresh(['updater']);
    }
}
