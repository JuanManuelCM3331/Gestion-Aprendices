<?php

namespace App\Services;

use App\Models\AcademicPeriod;
use App\Models\ApprenticeProfile;
use App\Models\Ficha;
use App\Models\InstructorProfile;
use App\Models\Sede;
use App\Models\TrainingProgram;
use App\Models\User;
use App\Repositories\Contracts\MasterDataRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use DomainException;
use Illuminate\Support\Facades\Hash;

class AdminGovernanceService
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly MasterDataRepositoryInterface $masterDataRepository,
        private readonly RiskEngineService $riskEngineService,
        private readonly AuditService $auditService
    ) {}

    public function getGovernanceDashboardData(array $auditFilters = []): array
    {
        return [
            'users' => $this->userRepository->getAllWithProfiles(),
            'auditLogs' => $this->auditService->getLogs($auditFilters, 150),
            'chainIntegrity' => $this->auditService->verifyChainIntegrity(),
            'sedes' => $this->masterDataRepository->getSedes(),
            'periods' => $this->masterDataRepository->getAcademicPeriods(),
            'programs' => $this->masterDataRepository->getTrainingPrograms(),
            'fichas' => $this->masterDataRepository->getFichasWithRelations(),
            'instructors' => InstructorProfile::with('user')->get(),
            'availablePermissions' => User::AVAILABLE_PERMISSIONS,
        ];
    }

    public function createUserWithProfile(array $data): User
    {
        $permissions = !empty($data['permissions']) && is_array($data['permissions'])
            ? array_values($data['permissions'])
            : User::defaultPermissionsForRole($data['role']);

        $user = $this->userRepository->create([
            'name' => $data['name'],
            'username' => $data['username'],
            'email' => $data['email'],
            'role' => $data['role'],
            'permissions' => $permissions,
            'is_active' => true,
            'password' => Hash::make($data['password']),
        ]);

        if ($user->role === User::ROLE_INSTRUCTOR) {
            InstructorProfile::create([
                'user_id' => $user->id,
                'document_number' => $data['document_number'] ?? ('10' . rand(10000000, 99999999)),
                'specialty' => $data['specialty'] ?? 'Desarrollo de Software y Ciberseguridad',
                'contract_type' => $data['contract_type'] ?? 'Planta',
                'weekly_hours' => (int) ($data['weekly_hours'] ?? 40),
            ]);
        }

        if ($user->role === User::ROLE_APRENDIZ) {
            $profile = ApprenticeProfile::create([
                'user_id' => $user->id,
                'ficha_id' => !empty($data['ficha_id']) ? (int) $data['ficha_id'] : Ficha::first()?->id,
                'document_type' => $data['document_type'] ?? 'CC',
                'document_number' => $data['document_number'] ?? ('10' . rand(10000000, 99999999)),
                'attendance_rate' => (float) ($data['attendance_rate'] ?? 96.0),
                'academic_average' => (float) ($data['academic_average'] ?? 4.2),
                'socioeconomic_stratum' => $data['socioeconomic_stratum'] ?? '2',
            ]);

            $this->riskEngineService->recalculateForApprentice($profile->id, false);
        }

        $this->auditService->log(
            'Gobernanza · Control de Acceso',
            "Creó usuario '{$user->username}' con rol '{$user->role}' y " . count($permissions) . " permisos granulares",
            'warning',
            'User',
            $user->id,
            null,
            [
                'username' => $user->username,
                'role' => $user->role,
                'permissions' => $permissions,
            ]
        );

        return $user;
    }

    public function updateUserAccessControl(int $userId, array $data): User
    {
        $user = $this->userRepository->findById($userId);
        if (!$user) {
            throw new DomainException('Usuario no encontrado.');
        }

        $oldSnapshot = [
            'role' => $user->role,
            'is_active' => $user->is_active,
            'permissions' => $user->permissions,
        ];

        $updatePayload = [
            'name' => $data['name'] ?? $user->name,
            'role' => $data['role'] ?? $user->role,
            'is_active' => isset($data['is_active']) ? (bool) $data['is_active'] : $user->is_active,
            'permissions' => isset($data['permissions']) && is_array($data['permissions'])
                ? array_values($data['permissions'])
                : $user->permissions,
        ];

        if (!empty($data['new_password'])) {
            $updatePayload['password'] = Hash::make($data['new_password']);
        }

        $updated = $this->userRepository->update($userId, $updatePayload);

        $this->auditService->log(
            'Gobernanza · Control de Acceso',
            "Actualizó permisos/credenciales de usuario '{$updated->username}'" . (!empty($data['new_password']) ? ' (Incluyó restablecimiento de clave)' : ''),
            'critical',
            'User',
            $updated->id,
            $oldSnapshot,
            [
                'role' => $updated->role,
                'is_active' => $updated->is_active,
                'permissions' => $updated->permissions,
                'password_reset' => !empty($data['new_password']),
            ]
        );

        return $updated;
    }

    public function deleteUserAccount(int $userId, int $actingAdminId): void
    {
        if ($userId === $actingAdminId) {
            throw new DomainException('Por seguridad, no puedes eliminar tu propia cuenta de Administrador en sesión.');
        }

        $user = $this->userRepository->findById($userId);
        if (!$user) {
            throw new DomainException('Usuario no encontrado.');
        }

        $snapshot = $user->only(['id', 'name', 'username', 'email', 'role']);
        $this->userRepository->delete($userId);

        $this->auditService->log(
            'Gobernanza · Control de Acceso',
            "Eliminó cuenta de usuario '{$snapshot['username']}' (Rol: {$snapshot['role']})",
            'critical',
            'User',
            $userId,
            $snapshot,
            null
        );
    }

    // Mantenimiento de Tablas Maestras
    public function createSede(array $data): Sede
    {
        $sede = $this->masterDataRepository->createSede($data);
        $this->auditService->log('Gobernanza · Tablas Maestras', "Creó Sede [{$sede->code}] {$sede->name}", 'info', 'Sede', $sede->id, null, $sede->toArray());
        return $sede;
    }

    public function createAcademicPeriod(array $data): AcademicPeriod
    {
        $period = $this->masterDataRepository->createAcademicPeriod($data);
        $this->auditService->log('Gobernanza · Tablas Maestras', "Creó Periodo Académico [{$period->code}] {$period->name}", 'info', 'AcademicPeriod', $period->id, null, $period->toArray());
        return $period;
    }

    public function createTrainingProgram(array $data): TrainingProgram
    {
        $program = $this->masterDataRepository->createTrainingProgram($data);
        $this->auditService->log('Gobernanza · Tablas Maestras', "Creó Programa de Formación [{$program->code}] {$program->name}", 'info', 'TrainingProgram', $program->id, null, $program->toArray());
        return $program;
    }

    public function createFicha(array $data): Ficha
    {
        $ficha = $this->masterDataRepository->createFicha($data);
        $this->auditService->log('Gobernanza · Tablas Maestras', "Creó Ficha / Grupo [{$ficha->code}] Jornada {$ficha->jornada}", 'info', 'Ficha', $ficha->id, null, $ficha->toArray());
        return $ficha;
    }

    public function assignInstructorToFicha(array $data): void
    {
        $this->masterDataRepository->assignInstructorToFicha(
            (int) $data['ficha_id'],
            (int) $data['instructor_profile_id'],
            [
                'competency' => $data['competency'],
                'schedule' => $data['schedule'],
                'hours_assigned' => (int) ($data['hours_assigned'] ?? 12),
                'is_lead' => !empty($data['is_lead']),
            ]
        );

        $this->auditService->log(
            'Gobernanza · Tablas Maestras',
            "Asignó instructor ID {$data['instructor_profile_id']} a Ficha ID {$data['ficha_id']} ({$data['competency']})",
            'info',
            'Ficha',
            (int) $data['ficha_id'],
            null,
            $data
        );
    }
}
