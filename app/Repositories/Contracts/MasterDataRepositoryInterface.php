<?php

namespace App\Repositories\Contracts;

use App\Models\AcademicPeriod;
use App\Models\Ficha;
use App\Models\Sede;
use App\Models\TrainingProgram;
use Illuminate\Support\Collection;

interface MasterDataRepositoryInterface
{
    public function getSedes(): Collection;
    public function createSede(array $data): Sede;
    public function getAcademicPeriods(): Collection;
    public function createAcademicPeriod(array $data): AcademicPeriod;
    public function getTrainingPrograms(): Collection;
    public function createTrainingProgram(array $data): TrainingProgram;
    public function getFichasWithRelations(): Collection;
    public function createFicha(array $data): Ficha;
    public function assignInstructorToFicha(int $fichaId, int $instructorProfileId, array $pivotData): void;
}
