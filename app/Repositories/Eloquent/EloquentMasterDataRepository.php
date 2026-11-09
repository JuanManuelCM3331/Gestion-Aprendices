<?php

namespace App\Repositories\Eloquent;

use App\Models\AcademicPeriod;
use App\Models\Ficha;
use App\Models\Sede;
use App\Models\TrainingProgram;
use App\Repositories\Contracts\MasterDataRepositoryInterface;
use Illuminate\Support\Collection;

class EloquentMasterDataRepository implements MasterDataRepositoryInterface
{
    public function getSedes(): Collection
    {
        return Sede::withCount('fichas')->orderBy('name')->get();
    }

    public function createSede(array $data): Sede
    {
        return Sede::create($data);
    }

    public function getAcademicPeriods(): Collection
    {
        return AcademicPeriod::withCount('fichas')->orderByDesc('start_date')->get();
    }

    public function createAcademicPeriod(array $data): AcademicPeriod
    {
        if (!empty($data['is_current'])) {
            AcademicPeriod::query()->update(['is_current' => false]);
        }

        return AcademicPeriod::create($data);
    }

    public function getTrainingPrograms(): Collection
    {
        return TrainingProgram::withCount('fichas')->orderBy('name')->get();
    }

    public function createTrainingProgram(array $data): TrainingProgram
    {
        return TrainingProgram::create($data);
    }

    public function getFichasWithRelations(): Collection
    {
        return Ficha::with([
            'trainingProgram',
            'sede',
            'academicPeriod',
            'instructors.user',
            'apprentices',
        ])->withCount('apprentices')->orderBy('code')->get();
    }

    public function createFicha(array $data): Ficha
    {
        return Ficha::create($data);
    }

    public function assignInstructorToFicha(int $fichaId, int $instructorProfileId, array $pivotData): void
    {
        $ficha = Ficha::findOrFail($fichaId);
        $ficha->instructors()->syncWithoutDetaching([
            $instructorProfileId => $pivotData,
        ]);
    }
}
