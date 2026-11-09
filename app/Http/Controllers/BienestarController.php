<?php

namespace App\Http\Controllers;

use App\Services\BienestarService;
use App\Services\InstructorService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BienestarController extends Controller
{
    public function __construct(
        private readonly BienestarService $bienestarService,
        private readonly InstructorService $instructorService
    ) {}

    public function index(Request $request): View
    {
        $filters = $request->only(['ficha_id', 'program_id', 'jornada', 'sede_id', 'risk_level', 'status', 'search']);
        $tab = $request->query('tab', 'monitoreo');

        $data = $this->bienestarService->getMonitoringDashboardData($filters);
        $reportData = $this->bienestarService->generateMassiveVulnerabilityReport($filters);

        return view('bienestar.dashboard', array_merge($data, [
            'reportData' => $reportData,
            'filters' => $filters,
            'activeTab' => $tab,
        ]));
    }

    public function updateParameters(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'weights' => ['required', 'array'],
            'weights.*' => ['required', 'numeric', 'min:0', 'max:100'],
        ]);

        $count = $this->bienestarService->updateRiskModelParameters($validated['weights'], (int) Auth::id());

        return redirect()
            ->route('bienestar.dashboard', ['tab' => 'variables'])
            ->with('status', "Parámetros del algoritmo predictivo actualizados sin intervención de código. Se recalcularon {$count} aprendices en tiempo real.");
    }

    public function storeCase(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'apprentice_profile_id' => ['required', 'integer', 'exists:apprentice_profiles,id'],
            'risk_type' => ['required', 'in:desercion,academico,psicosocial,integral'],
            'priority' => ['required', 'in:baja,media,alta,critica'],
            'title' => ['required', 'string', 'max:180'],
            'initial_diagnosis' => ['required', 'string'],
            'intervention_route' => ['nullable', 'string'],
            'commitments' => ['nullable', 'string'],
            'improvement_plan' => ['nullable', 'string'],
            'due_date' => ['nullable', 'date'],
        ]);

        $case = $this->bienestarService->createInterventionCase($validated, (int) Auth::id());

        return redirect()
            ->route('bienestar.dashboard', ['tab' => 'casos'])
            ->with('status', "Ruta de atención y caso [{$case->case_code}] creado exitosamente.");
    }

    public function updateCase(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:abierto,en_ruta,compromiso_activo,en_seguimiento,resuelto'],
            'priority' => ['nullable', 'in:baja,media,alta,critica'],
            'intervention_route' => ['nullable', 'string'],
            'commitments' => ['nullable', 'string'],
            'improvement_plan' => ['nullable', 'string'],
            'resolution_notes' => ['nullable', 'string'],
            'followup_stage' => ['nullable', 'string', 'max:120'],
            'followup_notes' => ['nullable', 'string'],
            'next_followup_date' => ['nullable', 'date'],
        ]);

        try {
            $case = $this->bienestarService->updateCaseAndFollowup($id, $validated, (int) Auth::id());
            return redirect()
                ->route('bienestar.dashboard', ['tab' => 'casos'])
                ->with('status', "Caso [{$case->case_code}] actualizado a estado '{$case->statusLabel()}'.");
        } catch (DomainException $e) {
            return back()->withErrors(['case_error' => $e->getMessage()]);
        }
    }

    public function destroyCase(int $id): RedirectResponse
    {
        try {
            $this->bienestarService->deleteCaseIfResolved($id);
            return redirect()
                ->route('bienestar.dashboard', ['tab' => 'casos'])
                ->with('status', 'El caso resuelto fue eliminado correctamente.');
        } catch (DomainException $e) {
            return redirect()
                ->route('bienestar.dashboard', ['tab' => 'casos'])
                ->withErrors(['case_error' => $e->getMessage()]);
        }
    }

    public function exportReportCsv(Request $request): StreamedResponse
    {
        $filters = $request->only(['ficha_id', 'program_id', 'jornada', 'sede_id', 'risk_level']);
        $report = $this->bienestarService->generateMassiveVulnerabilityReport($filters);

        $filename = 'reporte_vulnerabilidad_sga_' . date('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($report) {
            $handle = fopen('php://output', 'w');
            // UTF-8 BOM para Excel
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'Documento',
                'Aprendiz',
                'Ficha',
                'Programa de Formación',
                'Jornada',
                'Sede',
                '% Asistencia',
                'Promedio Académico',
                'Riesgo Deserción (%)',
                'Riesgo Académico (%)',
                'Riesgo Psicosocial (%)',
                'Puntaje Global (%)',
                'Semáforo de Riesgo',
                'Estado Académico',
                'Casos de Bienestar Activos',
            ], ';');

            foreach ($report['apprentices'] as $a) {
                fputcsv($handle, [
                    $a->document_type . ' ' . $a->document_number,
                    $a->user?->name ?? 'N/A',
                    $a->ficha?->code ?? 'N/A',
                    $a->ficha?->trainingProgram?->name ?? 'N/A',
                    $a->ficha?->jornada ?? 'N/A',
                    $a->ficha?->sede?->name ?? 'N/A',
                    $a->attendance_rate . '%',
                    $a->academic_average,
                    $a->dropout_risk_score,
                    $a->academic_risk_score,
                    $a->psychosocial_risk_score,
                    $a->global_risk_score,
                    strtoupper($a->risk_level),
                    $a->academic_status,
                    $a->welfareCases->where('status', '!=', 'resuelto')->count(),
                ], ';');
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
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

        return back()->with('status', 'Soporte revisado y nivel de riesgo del aprendiz recalculado en tiempo real.');
    }
}
