<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Tablas Maestras (Gobernanza del Administrador)
        Schema::create('sedes', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('name');
            $table->string('city');
            $table->string('address')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('academic_periods', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique(); // Ej: 2026-I, 2026-II
            $table->string('name');
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('is_current')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('training_programs', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name');
            $table->enum('level', ['Tecnólogo', 'Técnico', 'Especialización', 'Complementaria'])->default('Tecnólogo');
            $table->unsignedTinyInteger('duration_trimesters')->default(6);
            $table->string('area')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('fichas', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique(); // Número de ficha (ej. 2847101)
            $table->foreignId('training_program_id')->constrained('training_programs')->cascadeOnDelete();
            $table->foreignId('sede_id')->constrained('sedes')->cascadeOnDelete();
            $table->foreignId('academic_period_id')->constrained('academic_periods')->cascadeOnDelete();
            $table->enum('jornada', ['Mañana', 'Tarde', 'Noche', 'Fines de Semana'])->default('Mañana');
            $table->string('classroom')->nullable();
            $table->enum('status', ['En Ejecución', 'Etapa Productiva', 'Finalizada'])->default('En Ejecución');
            $table->timestamps();
        });

        // 2. Perfiles de Instructores y Asignación de Carga Lectiva / Fichas
        Schema::create('instructor_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->string('document_number', 30)->nullable();
            $table->string('specialty');
            $table->string('contract_type')->default('Planta');
            $table->unsignedSmallInteger('weekly_hours')->default(40);
            $table->string('phone', 30)->nullable();
            $table->timestamps();
        });

        Schema::create('ficha_instructor', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ficha_id')->constrained('fichas')->cascadeOnDelete();
            $table->foreignId('instructor_profile_id')->constrained('instructor_profiles')->cascadeOnDelete();
            $table->string('competency'); // Competencia o asignatura dictada
            $table->string('schedule');   // Ej: Lun-Mié 07:00 - 12:00
            $table->unsignedSmallInteger('hours_assigned')->default(12);
            $table->boolean('is_lead')->default(false); // Vocero / Líder de ficha
            $table->timestamps();
        });

        // 3. Perfiles de Aprendices y Monitoreo Predictivo de Riesgo
        Schema::create('apprentice_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->foreignId('ficha_id')->nullable()->constrained('fichas')->nullOnDelete();
            $table->string('document_type', 10)->default('CC');
            $table->string('document_number', 30)->nullable();
            $table->string('phone', 30)->nullable();
            $table->decimal('attendance_rate', 5, 2)->default(100.00); // % Asistencia
            $table->decimal('academic_average', 4, 2)->default(4.50);  // Promedio 0.0 - 5.0
            $table->unsignedInteger('absences_count')->default(0);
            $table->enum('socioeconomic_stratum', ['1', '2', '3', '4', '5', '6'])->default('2');
            $table->boolean('has_connectivity_issues')->default(false);
            $table->boolean('has_family_vulnerability')->default(false);

            // Vectores de Riesgo Predictivo (0 - 100) y Semáforo Automático
            $table->decimal('dropout_risk_score', 5, 2)->default(0.00);      // Riesgo Deserción
            $table->decimal('academic_risk_score', 5, 2)->default(0.00);     // Riesgo Académico
            $table->decimal('psychosocial_risk_score', 5, 2)->default(0.00); // Riesgo Psicosocial
            $table->decimal('global_risk_score', 5, 2)->default(0.00);       // Puntaje Consolidado
            $table->enum('risk_level', ['bajo', 'medio', 'alto'])->default('bajo'); // Semáforo: verde, amarillo, rojo
            $table->enum('academic_status', ['En Formación', 'Condicionado', 'En Plan de Mejora', 'Aplazado', 'Desertor'])->default('En Formación');
            $table->timestamp('last_risk_calculated_at')->nullable();
            $table->timestamps();
        });

        // 4. Parámetros Dinámicos del Algoritmo de Valoración de Riesgo (Bienestar)
        Schema::create('risk_parameters', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name');
            $table->enum('dimension', ['desercion', 'academico', 'psicosocial', 'umbral']);
            $table->decimal('weight', 6, 2)->default(10.00);
            $table->decimal('min_value', 6, 2)->default(0.00);
            $table->decimal('max_value', 6, 2)->default(100.00);
            $table->text('description')->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // 5. Observaciones, Novedades, Inasistencias y Llamados de Atención (Instructor / Aprendiz)
        Schema::create('observations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('apprentice_profile_id')->constrained('apprentice_profiles')->cascadeOnDelete();
            $table->foreignId('ficha_id')->nullable()->constrained('fichas')->nullOnDelete();
            $table->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('type', ['positiva', 'academica', 'disciplinaria', 'inasistencia', 'llamado_atencion'])->default('academica');
            $table->enum('category', ['desercion', 'academico', 'psicosocial'])->default('academico');
            $table->unsignedTinyInteger('severity')->default(2); // 1 (Leve/Reconocimiento) a 5 (Crítico)
            $table->string('title');
            $table->text('description');
            $table->boolean('is_massive')->default(false);
            $table->boolean('escalated_to_welfare')->default(false);
            $table->timestamp('escalated_at')->nullable();
            $table->boolean('mitigated_by_support')->default(false);
            $table->timestamps();
        });

        // 6. Soportes y Evidencias del Aprendiz (Excusas médicas, descargos, documentos)
        Schema::create('evidence_supports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('observation_id')->constrained('observations')->cascadeOnDelete();
            $table->foreignId('apprentice_profile_id')->constrained('apprentice_profiles')->cascadeOnDelete();
            $table->string('title');
            $table->enum('support_type', ['Excusa Médica', 'Descargo a Llamado de Atención', 'Soporte Calamidad Doméstica', 'Certificado Laboral/Institucional', 'Otro'])->default('Excusa Médica');
            $table->text('description');
            $table->string('file_path')->nullable();
            $table->string('file_name')->nullable();
            $table->string('mime_type')->nullable();
            $table->enum('status', ['pendiente', 'aprobado', 'rechazado'])->default('pendiente');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('review_notes')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });

        // 7. Casos de Bienestar, Rutas de Atención y Compromisos (Trazabilidad con bloqueo de borrado)
        Schema::create('welfare_cases', function (Blueprint $table) {
            $table->id();
            $table->string('case_code', 30)->unique();
            $table->foreignId('apprentice_profile_id')->constrained('apprentice_profiles')->cascadeOnDelete();
            $table->foreignId('origin_observation_id')->nullable()->constrained('observations')->nullOnDelete();
            $table->foreignId('opened_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('risk_type', ['desercion', 'academico', 'psicosocial', 'integral'])->default('integral');
            $table->enum('priority', ['baja', 'media', 'alta', 'critica'])->default('alta');
            $table->enum('status', ['abierto', 'en_ruta', 'compromiso_activo', 'en_seguimiento', 'resuelto'])->default('abierto');
            $table->string('title');
            $table->text('initial_diagnosis');
            $table->text('intervention_route')->nullable(); // Ruta de atención definida
            $table->text('commitments')->nullable();        // Compromisos del aprendiz
            $table->text('improvement_plan')->nullable();   // Plan de mejora
            $table->text('resolution_notes')->nullable();
            $table->date('due_date')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
        });

        Schema::create('welfare_case_followups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('welfare_case_id')->constrained('welfare_cases')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('stage'); // Ej: Citación, Entrevista Psicosocial, Firma de Compromiso, Cierre
            $table->text('notes');
            $table->date('next_followup_date')->nullable();
            $table->timestamps();
        });

        // 8. Notificaciones Internas de la Plataforma
        Schema::create('platform_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('type', ['riesgo', 'citacion_bienestar', 'observacion', 'soporte', 'escalamiento', 'sistema'])->default('sistema');
            $table->enum('severity', ['info', 'warning', 'danger', 'success'])->default('info');
            $table->string('title');
            $table->text('message');
            $table->string('action_url')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });

        // 9. Auditoría Inmutable (Con IP, User-Agent, Diff y Hash Criptográfico Encadenado)
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('username', 100)->default('Sistema');
            $table->string('role', 40)->nullable();
            $table->string('ip_address', 45)->default('127.0.0.1');
            $table->string('user_agent')->nullable();
            $table->string('module', 60);
            $table->string('action');
            $table->string('entity_type', 80)->nullable();
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->enum('severity', ['info', 'warning', 'critical'])->default('info');
            $table->string('previous_hash', 64)->nullable();
            $table->string('integrity_hash', 64);
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('platform_notifications');
        Schema::dropIfExists('welfare_case_followups');
        Schema::dropIfExists('welfare_cases');
        Schema::dropIfExists('evidence_supports');
        Schema::dropIfExists('observations');
        Schema::dropIfExists('risk_parameters');
        Schema::dropIfExists('apprentice_profiles');
        Schema::dropIfExists('ficha_instructor');
        Schema::dropIfExists('instructor_profiles');
        Schema::dropIfExists('fichas');
        Schema::dropIfExists('training_programs');
        Schema::dropIfExists('academic_periods');
        Schema::dropIfExists('sedes');
    }
};
