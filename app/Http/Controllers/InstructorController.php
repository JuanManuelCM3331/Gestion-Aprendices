<?php

namespace App\Http\Controllers;

use App\Services\InstructorService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class InstructorController extends Controller
{
    public function __construct(
        private readonly InstructorService $instructorService
    ) {}

    public function index(Request $request): View
    {
        $filters = $request->only(['ficha_id', 'risk_level']);
        $tab = $request->query('tab', 'novedades');

        $workspace = $this->instructorService->getInstructorWorkspaceData(Auth::user(), $filters);

        return view('instructor.dashboard', array_merge($workspace, [
            'filters' => $filters,
            'activeTab' => $tab,
        ]));
    }

    public function storeObservations(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'target_mode' => ['required', 'in:single,multiple,ficha_massive'],
            'apprentice_profile_id' => ['nullable', 'integer', 'exists:apprentice_profiles,id'],
            'apprentice_ids' => ['nullable', 'array'],
            'apprentice_ids.*' => ['integer', 'exists:apprentice_profiles,id'],
            'ficha_id' => ['nullable', 'integer', 'exists:fichas,id'],
            'type' => ['required', 'in:positiva,academica,disciplinaria,inasistencia,llamado_atencion'],
            'category' => ['required', 'in:desercion,academico,psicosocial'],
            'severity' => ['required', 'integer', 'min:1', 'max:5'],
            'title' => ['required', 'string', 'max:180'],
            'description' => ['required', 'string'],
            'academic_average' => ['nullable', 'numeric', 'min:0', 'max:5'],
            'escalate_immediately' => ['nullable', 'boolean'],
            'escalation_notes' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $count = $this->instructorService->recordObservationsBulkOrSingle($validated, Auth::user());
            return redirect()
                ->route('instructor.dashboard', ['tab' => 'novedades'])
                ->with('status', "Se registraron {$count} novedad(es) exitosamente y se recalculó el semáforo de riesgo en tiempo real.");
        } catch (DomainException $e) {
            return back()->withInput()->withErrors(['obs_error' => $e->getMessage()]);
        }
    }

    public function escalateObservation(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'escalation_notes' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $case = $this->instructorService->escalateObservationToWelfare(
                $id,
                Auth::user(),
                $validated['escalation_notes'] ?? null
            );

            return back()->with('status', "Caso crítico escalado directamente al Equipo de Bienestar con código [{$case->case_code}].");
        } catch (DomainException $e) {
            return back()->withErrors(['obs_error' => $e->getMessage()]);
        }
    }

    public function showExpediente(int $id): View|RedirectResponse
    {
        try {
            $apprentice = $this->instructorService->getApprenticeContextualDossier($id, Auth::user());
            return view('instructor.expediente', compact('apprentice'));
        } catch (DomainException $e) {
            return redirect()->route('instructor.dashboard')->withErrors(['obs_error' => $e->getMessage()]);
        }
    }

    public function reviewSupport(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:aprobado,rechazado'],
            'review_notes' => ['nullable', 'string', 'max:500'],
        ]);

        $this->instructorService->reviewEvidenceSupport(
            $id,
            $validated['status'],
            $validated['review_notes'] ?? null,
            Auth::user()
        );

        return back()->with('status', 'Soporte validado y cálculo de riesgo actualizado en tiempo real.');
    }
}
