<?php

namespace App\Repositories\Contracts;

use App\Models\RiskParameter;
use Illuminate\Support\Collection;

interface RiskParameterRepositoryInterface
{
    public function getAllOrdered(): Collection;
    public function getKeyedWeights(): array;
    public function updateWeight(int $id, float $weight, int $updatedBy): RiskParameter;
}
