<?php

namespace App\Http\Controllers;

use App\Services\AdminGovernanceService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function __construct(
        private readonly AdminGovernanceService $adminGovernanceService
    ) {}

    public function index(Request $request): View
    {
        $auditFilters = $request->only(['module', 'severity', 'search']);
        $tab = $request->query('tab', 'auditoria');

        $data = $this->adminGovernanceService->getGovernanceDashboardData($auditFilters);

        return view('admin.dashboard', array_merge($data, [
            'auditFilters' => $auditFilters,
            'activeTab' => $tab,
        ]));
    }

    public function storeUser(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'username' => ['required', 'string', 'max:60', 'unique:users,username'],
            'email' => ['required', 'email', 'max:150', 'unique:users,email'],
            'password' => ['required', 'string', 'min:4'],
            'role' => ['required', 'in:admin,bienestar,instructor,aprendiz'],
            'permissions' => ['nullable', 'array'],
            'ficha_id' => ['nullable', 'integer', 'exists:fichas,id'],
            'specialty' => ['nullable', 'string', 'max:120'],
            'document_number' => ['nullable', 'string', 'max:30'],
        ]);

        $user = $this->adminGovernanceService->createUserWithProfile($validated);

        return redirect()
            ->route('admin.dashboard', ['tab' => 'usuarios'])
            ->with('status', "Usuario '{$user->username}' ({$user->roleLabel()}) registrado y auditado correctamente.");
    }

    public function updateUserAccess(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['nullable', 'string', 'max:120'],
            'role' => ['required', 'in:admin,bienestar,instructor,aprendiz'],
            'is_active' => ['required', 'boolean'],
            'permissions' => ['nullable', 'array'],
            'new_password' => ['nullable', 'string', 'min:4'],
        ]);

        try {
            $updated = $this->adminGovernanceService->updateUserAccessControl($id, $validated);
            return redirect()
                ->route('admin.dashboard', ['tab' => 'usuarios'])
                ->with('status', "Permisos granulares y credenciales de '{$updated->username}' actualizados.");
        } catch (DomainException $e) {
            return back()->withErrors(['admin_error' => $e->getMessage()]);
        }
    }

    public function destroyUser(int $id): RedirectResponse
    {
        try {
            $this->adminGovernanceService->deleteUserAccount($id, (int) Auth::id());
            return redirect()
                ->route('admin.dashboard', ['tab' => 'usuarios'])
                ->with('status', 'Cuenta de usuario eliminada y evento registrado en Auditoría Inmutable.');
        } catch (DomainException $e) {
            return back()->withErrors(['admin_error' => $e->getMessage()]);
        }
    }

    public function storeSede(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:20', 'unique:sedes,code'],
            'name' => ['required', 'string', 'max:150'],
            'city' => ['required', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:200'],
        ]);

        $this->adminGovernanceService->createSede($validated);

        return redirect()
            ->route('admin.dashboard', ['tab' => 'maestras'])
            ->with('status', 'Nueva Sede institucional creada exitosamente.');
    }

    public function storeAcademicPeriod(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:20', 'unique:academic_periods,code'],
            'name' => ['required', 'string', 'max:120'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'is_current' => ['nullable', 'boolean'],
        ]);

        $this->adminGovernanceService->createAcademicPeriod($validated);

        return redirect()
            ->route('admin.dashboard', ['tab' => 'maestras'])
            ->with('status', 'Periodo académico registrado en tablas maestras.');
    }

    public function storeTrainingProgram(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:30', 'unique:training_programs,code'],
            'name' => ['required', 'string', 'max:180'],
            'level' => ['required', 'in:Tecnólogo,Técnico,Especialización,Complementaria'],
            'duration_trimesters' => ['required', 'integer', 'min:1', 'max:12'],
            'area' => ['nullable', 'string', 'max:120'],
        ]);

        $this->adminGovernanceService->createTrainingProgram($validated);

        return redirect()
            ->route('admin.dashboard', ['tab' => 'maestras'])
            ->with('status', 'Programa de formación creado en tablas maestras.');
    }

    public function storeFicha(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:30', 'unique:fichas,code'],
            'training_program_id' => ['required', 'integer', 'exists:training_programs,id'],
            'sede_id' => ['required', 'integer', 'exists:sedes,id'],
            'academic_period_id' => ['required', 'integer', 'exists:academic_periods,id'],
            'jornada' => ['required', 'in:Mañana,Tarde,Noche,Fines de Semana'],
            'classroom' => ['nullable', 'string', 'max:80'],
        ]);

        $this->adminGovernanceService->createFicha($validated);

        return redirect()
            ->route('admin.dashboard', ['tab' => 'maestras'])
            ->with('status', 'Nueva Ficha / Grupo de formación creada.');
    }

    public function assignInstructor(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ficha_id' => ['required', 'integer', 'exists:fichas,id'],
            'instructor_profile_id' => ['required', 'integer', 'exists:instructor_profiles,id'],
            'competency' => ['required', 'string', 'max:160'],
            'schedule' => ['required', 'string', 'max:120'],
            'hours_assigned' => ['required', 'integer', 'min:1', 'max:48'],
            'is_lead' => ['nullable', 'boolean'],
        ]);

        $this->adminGovernanceService->assignInstructorToFicha($validated);

        return redirect()
            ->route('admin.dashboard', ['tab' => 'maestras'])
            ->with('status', 'Carga lectiva y horario asignado al instructor en la ficha.');
    }
}
