<?php

namespace Tests\Feature;

use App\Models\ApprenticeProfile;
use App\Models\AuditLog;
use App\Models\Ficha;
use App\Models\Observation;
use App\Models\RiskParameter;
use App\Models\User;
use App\Models\WelfareCase;
use App\Services\AuditService;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SgaArchitectureAndRolesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_landing_and_login_pages_render_correctly(): void
    {
        $this->get(route('landing'))
            ->assertOk()
            ->assertSee('Sistema de Gestión de');

        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Iniciar Sesión');
    }

    public function test_bienestar_can_adjust_risk_parameters_and_block_unresolved_case_deletion(): void
    {
        $bienestar = User::where('username', 'bienestar')->firstOrFail();

        $this->actingAs($bienestar)
            ->get(route('bienestar.dashboard'))
            ->assertOk()
            ->assertSee('Coordinación de Bienestar');

        // 1. Ajustar pesos del algoritmo sin tocar código
        $params = RiskParameter::pluck('weight', 'id')->map(fn ($w) => $w + 1.0)->toArray();
        $this->actingAs($bienestar)
            ->post(route('bienestar.parameters.update'), ['weights' => $params])
            ->assertRedirect();

        // 2. Verificar bloqueo estricto al intentar eliminar un caso NO resuelto
        $activeCase = WelfareCase::where('status', '!=', 'resuelto')->firstOrFail();
        $this->actingAs($bienestar)
            ->delete(route('bienestar.cases.destroy', $activeCase->id))
            ->assertSessionHasErrors('case_error');

        $this->assertDatabaseHas('welfare_cases', ['id' => $activeCase->id]);

        // 3. Verificar que un caso RESUELTO sí puede eliminarse
        $resolvedCase = WelfareCase::where('status', 'resuelto')->firstOrFail();
        $this->actingAs($bienestar)
            ->delete(route('bienestar.cases.destroy', $resolvedCase->id))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseMissing('welfare_cases', ['id' => $resolvedCase->id]);

        // 4. Exportación de reporte masivo CSV
        $this->actingAs($bienestar)
            ->get(route('bienestar.reports.export'))
            ->assertOk();
    }

    public function test_instructor_can_record_massive_observations_and_escalate_directly_to_welfare(): void
    {
        $instructor = User::where('username', 'instructor1')->firstOrFail();
        $ficha = Ficha::where('code', '2847101')->firstOrFail();

        // Carga masiva para toda la ficha asignada
        $this->actingAs($instructor)
            ->post(route('instructor.observations.store'), [
                'target_mode' => 'ficha_massive',
                'ficha_id' => $ficha->id,
                'type' => 'academica',
                'category' => 'academico',
                'severity' => 4,
                'title' => 'Evaluación diagnóstica de sprint técnico',
                'description' => 'Revisión grupal de arquitectura MVC + Service + Repository.',
            ])
            ->assertRedirect()
            ->assertSessionHas('status');

        $obs = Observation::where('title', 'Evaluación diagnóstica de sprint técnico')->firstOrFail();
        $this->assertTrue($obs->is_massive);

        // Escalamiento directo a Bienestar
        $this->actingAs($instructor)
            ->post(route('instructor.observations.escalate', $obs->id), [
                'escalation_notes' => 'Requiere acompañamiento urgente del equipo interdisciplinario.',
            ])
            ->assertRedirect();

        $this->assertTrue($obs->fresh()->escalated_to_welfare);
        $this->assertDatabaseHas('welfare_cases', [
            'origin_observation_id' => $obs->id,
        ]);
    }

    public function test_admin_audit_log_is_strictly_immutable_and_verifies_sha256_chain(): void
    {
        $admin = User::where('username', 'admin')->firstOrFail();

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Registro de Auditoría Inmutable');

        /** @var AuditService $auditService */
        $auditService = app(AuditService::class);
        $integrity = $auditService->verifyChainIntegrity();
        $this->assertTrue($integrity['valid']);

        $log = AuditLog::firstOrFail();

        // Intentar alterar un registro de auditoría debe lanzar DomainException
        $this->expectException(DomainException::class);
        $log->update(['action' => 'Intento de alteración maliciosa']);
    }

    public function test_aprendiz_can_upload_support_and_instructor_approval_mitigates_risk(): void
    {
        Storage::fake('public');

        $aprendizUser = User::where('username', 'aprendiz1')->firstOrFail();
        $instructorUser = User::where('username', 'instructor1')->firstOrFail();
        $profile = ApprenticeProfile::where('user_id', $aprendizUser->id)->firstOrFail();
        $observation = $profile->observations()->where('type', '!=', 'positiva')->firstOrFail();

        $file = UploadedFile::fake()->create('incapacidad_medica.pdf', 120, 'application/pdf');

        $this->actingAs($aprendizUser)
            ->post(route('aprendiz.supports.store', $observation->id), [
                'title' => 'Certificado de Incapacidad EPS',
                'support_type' => 'Excusa Médica',
                'description' => 'Adjunto soporte médico oficial para justificar la inasistencia.',
                'attachment' => $file,
            ])
            ->assertRedirect();

        $support = $observation->supports()->latest()->firstOrFail();
        $this->assertEquals('pendiente', $support->status);

        // El instructor aprueba el soporte y se mitiga la observación automáticamente
        $this->actingAs($instructorUser)
            ->post(route('instructor.supports.review', $support->id), [
                'status' => 'aprobado',
                'review_notes' => 'Soporte médico verificado satisfactoriamente.',
            ])
            ->assertRedirect();

        $this->assertEquals('aprobado', $support->fresh()->status);
        $this->assertTrue($observation->fresh()->mitigated_by_support);
    }
}
