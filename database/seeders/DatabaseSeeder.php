<?php

namespace Database\Seeders;

use App\Models\AcademicPeriod;
use App\Models\ApprenticeProfile;
use App\Models\EvidenceSupport;
use App\Models\Ficha;
use App\Models\InstructorProfile;
use App\Models\Observation;
use App\Models\RiskParameter;
use App\Models\Sede;
use App\Models\TrainingProgram;
use App\Models\User;
use App\Models\WelfareCase;
use App\Models\WelfareCaseFollowup;
use App\Services\AuditService;
use App\Services\NotificationService;
use App\Services\RiskEngineService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Usuarios Principales (Los 4 roles del sistema)
        $admin = User::create([
            'name' => 'Juan Manuel Cardona Molina',
            'username' => 'admin',
            'email' => 'admin@sga.edu.co',
            'role' => User::ROLE_ADMIN,
            'permissions' => User::defaultPermissionsForRole(User::ROLE_ADMIN),
            'is_active' => true,
            'password' => Hash::make('admin123'),
        ]);

        $bienestar = User::create([
            'name' => 'Dra. Laura Sofía Mendoza',
            'username' => 'bienestar',
            'email' => 'bienestar@sga.edu.co',
            'role' => User::ROLE_BIENESTAR,
            'permissions' => User::defaultPermissionsForRole(User::ROLE_BIENESTAR),
            'is_active' => true,
            'password' => Hash::make('bienestar123'),
        ]);

        $instructor1User = User::create([
            'name' => 'Ing. Carlos Andrés Restrepo',
            'username' => 'instructor1',
            'email' => 'carlos.restrepo@sga.edu.co',
            'role' => User::ROLE_INSTRUCTOR,
            'permissions' => User::defaultPermissionsForRole(User::ROLE_INSTRUCTOR),
            'is_active' => true,
            'password' => Hash::make('123'),
        ]);

        $instructor2User = User::create([
            'name' => 'MSc. Valentina Gómez',
            'username' => 'instructor2',
            'email' => 'valentina.gomez@sga.edu.co',
            'role' => User::ROLE_INSTRUCTOR,
            'permissions' => User::defaultPermissionsForRole(User::ROLE_INSTRUCTOR),
            'is_active' => true,
            'password' => Hash::make('123'),
        ]);

        // 2. Tablas Maestras: Sedes, Periodos, Programas y Fichas
        $sedeCentral = Sede::create([
            'code' => 'SED-CEN',
            'name' => 'Centro de Servicios y Gestión Tecnológica',
            'city' => 'Medellín',
            'address' => 'Calle 51 # 57-70 Torre Norte',
        ]);

        $sedeSur = Sede::create([
            'code' => 'SED-SUR',
            'name' => 'Complejo Tecnológico Industrial del Sur',
            'city' => 'Itagüí',
            'address' => 'Cra 42 # 75-110 Autopista Sur',
        ]);

        $sedeNorte = Sede::create([
            'code' => 'SED-TIC',
            'name' => 'Nodo de Innovación y Ciberseguridad TIC',
            'city' => 'Bello',
            'address' => 'Av. Regional # 45-19',
        ]);

        AcademicPeriod::create([
            'code' => '2026-I',
            'name' => 'Primer Semestre Lectivo 2026',
            'start_date' => '2026-01-20',
            'end_date' => '2026-06-30',
            'is_current' => false,
        ]);

        $periodoActual = AcademicPeriod::create([
            'code' => '2026-II',
            'name' => 'Segundo Semestre Lectivo 2026 (Vigente)',
            'start_date' => '2026-07-15',
            'end_date' => '2026-12-15',
            'is_current' => true,
        ]);

        $progAdso = TrainingProgram::create([
            'code' => '228118',
            'name' => 'Análisis y Desarrollo de Software (ADSO)',
            'level' => 'Tecnólogo',
            'duration_trimesters' => 7,
            'area' => 'Tecnologías de la Información',
        ]);

        $progCyber = TrainingProgram::create([
            'code' => '228124',
            'name' => 'Ciberseguridad y Defensa de Redes',
            'level' => 'Tecnólogo',
            'duration_trimesters' => 6,
            'area' => 'Seguridad Digital y DevOps',
        ]);

        $progDatos = TrainingProgram::create([
            'code' => '228130',
            'name' => 'Analítica de Datos e Inteligencia Artificial',
            'level' => 'Tecnólogo',
            'duration_trimesters' => 6,
            'area' => 'Ciencia de Datos',
        ]);

        $fichaAdso = Ficha::create([
            'code' => '2847101',
            'training_program_id' => $progAdso->id,
            'sede_id' => $sedeCentral->id,
            'academic_period_id' => $periodoActual->id,
            'jornada' => 'Mañana',
            'classroom' => 'Lab Software 402',
            'status' => 'En Ejecución',
        ]);

        $fichaCyber = Ficha::create([
            'code' => '2847102',
            'training_program_id' => $progCyber->id,
            'sede_id' => $sedeNorte->id,
            'academic_period_id' => $periodoActual->id,
            'jornada' => 'Noche',
            'classroom' => 'SOC Lab 201',
            'status' => 'En Ejecución',
        ]);

        $fichaDatos = Ficha::create([
            'code' => '2847103',
            'training_program_id' => $progDatos->id,
            'sede_id' => $sedeSur->id,
            'academic_period_id' => $periodoActual->id,
            'jornada' => 'Tarde',
            'classroom' => 'Aula IA 105',
            'status' => 'En Ejecución',
        ]);

        $fichaFines = Ficha::create([
            'code' => '2847104',
            'training_program_id' => $progAdso->id,
            'sede_id' => $sedeCentral->id,
            'academic_period_id' => $periodoActual->id,
            'jornada' => 'Fines de Semana',
            'classroom' => 'Aula Híbrida 308',
            'status' => 'En Ejecución',
        ]);

        // 3. Perfiles de Instructores y Asignación de Carga Lectiva
        $inst1 = InstructorProfile::create([
            'user_id' => $instructor1User->id,
            'document_number' => '71389451',
            'specialty' => 'Arquitectura de Software, Laravel & DevSecOps',
            'contract_type' => 'Planta',
            'weekly_hours' => 40,
            'phone' => '3004128900',
        ]);

        $inst2 = InstructorProfile::create([
            'user_id' => $instructor2User->id,
            'document_number' => '43912084',
            'specialty' => 'Ciencia de Datos, Python & Modelos Predictivos',
            'contract_type' => 'Contratista',
            'weekly_hours' => 32,
            'phone' => '3115902341',
        ]);

        $fichaAdso->instructors()->attach($inst1->id, [
            'competency' => 'Arquitectura en Capas MVC, Patrones Service/Repository en Laravel',
            'schedule' => 'Lun - Mié - Vie · 07:00 a 12:00',
            'hours_assigned' => 16,
            'is_lead' => true,
        ]);

        $fichaCyber->instructors()->attach($inst1->id, [
            'competency' => 'Seguridad de Aplicaciones Web, Auditoría Inmutable y Hardening',
            'schedule' => 'Mar - Jue · 18:00 a 22:00',
            'hours_assigned' => 12,
            'is_lead' => true,
        ]);

        $fichaFines->instructors()->attach($inst1->id, [
            'competency' => 'Integración Continua CI/CD y Contenedores Docker',
            'schedule' => 'Sábados · 08:00 a 16:00',
            'hours_assigned' => 8,
            'is_lead' => false,
        ]);

        $fichaDatos->instructors()->attach($inst2->id, [
            'competency' => 'Modelado Predictivo de Deserción Estudiantil',
            'schedule' => 'Lun a Vie · 13:00 a 17:30',
            'hours_assigned' => 20,
            'is_lead' => true,
        ]);

        // 4. Parámetros Dinámicos del Algoritmo de Riesgo (Módulo Bienestar)
        $parameters = [
            [
                'code' => 'peso_inasistencias',
                'name' => 'Peso por Inasistencia Injustificada',
                'dimension' => 'desercion',
                'weight' => 9.5,
                'min_value' => 1.0,
                'max_value' => 30.0,
                'description' => 'Impacto directo en el índice de deserción por cada falla no cubierta por excusa médica aprobada.',
            ],
            [
                'code' => 'peso_baja_asistencia',
                'name' => 'Multiplicador Déficit de Asistencia (<92%)',
                'dimension' => 'desercion',
                'weight' => 1.9,
                'min_value' => 0.5,
                'max_value' => 10.0,
                'description' => 'Penalización progresiva por cada punto porcentual debajo del estándar de permanencia.',
            ],
            [
                'code' => 'peso_promedio_bajo',
                'name' => 'Peso por Déficit de Promedio Académico (<3.8)',
                'dimension' => 'academico',
                'weight' => 24.0,
                'min_value' => 5.0,
                'max_value' => 50.0,
                'description' => 'Sensibilidad del modelo frente a calificaciones acumuladas cercanas al umbral de pérdida.',
            ],
            [
                'code' => 'peso_novedad_academica',
                'name' => 'Factor por Observación Académica (x Severidad)',
                'dimension' => 'academico',
                'weight' => 6.5,
                'min_value' => 1.0,
                'max_value' => 25.0,
                'description' => 'Incremento del riesgo académico por entregas tardías o bajo rendimiento reportado por instructores.',
            ],
            [
                'code' => 'peso_llamado_atencion',
                'name' => 'Peso por Llamado de Atención / Disciplinario',
                'dimension' => 'academico',
                'weight' => 12.0,
                'min_value' => 2.0,
                'max_value' => 35.0,
                'description' => 'Impacto de llamados de atención escritos sobre el expediente académico y psicosocial.',
            ],
            [
                'code' => 'peso_vulnerabilidad_psico',
                'name' => 'Factor de Vulnerabilidad Psicosocial y Socioeconómica',
                'dimension' => 'psicosocial',
                'weight' => 15.0,
                'min_value' => 2.0,
                'max_value' => 40.0,
                'description' => 'Ponderación de alertas emocionales, familiares, conectividad y casos escalados a Bienestar.',
            ],
            [
                'code' => 'bono_observacion_positiva',
                'name' => 'Factor Protector por Observación Positiva / Mérito',
                'dimension' => 'academico',
                'weight' => 5.5,
                'min_value' => 0.0,
                'max_value' => 20.0,
                'description' => 'Mitigación otorgada por reconocimientos positivos o liderazgo académico registrado.',
            ],
            [
                'code' => 'umbral_riesgo_medio',
                'name' => 'Umbral Semáforo Amarillo (Riesgo Medio)',
                'dimension' => 'umbral',
                'weight' => 35.0,
                'min_value' => 15.0,
                'max_value' => 60.0,
                'description' => 'Puntaje global a partir del cual el aprendiz pasa a estado de alerta preventiva (Amarillo).',
            ],
            [
                'code' => 'umbral_riesgo_alto',
                'name' => 'Umbral Semáforo Rojo (Riesgo Crítico / Alto)',
                'dimension' => 'umbral',
                'weight' => 65.0,
                'min_value' => 50.0,
                'max_value' => 95.0,
                'description' => 'Puntaje global a partir del cual se activa intervención prioritaria de Bienestar (Rojo).',
            ],
        ];

        foreach ($parameters as $param) {
            RiskParameter::create(array_merge($param, ['updated_by' => $bienestar->id]));
        }

        // 5. Aprendices (Incluyendo aprendiz1 de demostración y cohorte diversa)
        $apprenticesSeed = [
            [
                'name' => 'Mateo Alejandro Ríos',
                'username' => 'aprendiz1',
                'email' => 'mateo.rios@aprendiz.sga.edu.co',
                'ficha_id' => $fichaAdso->id,
                'doc' => '1036948210',
                'attendance' => 76.5,
                'average' => 2.9,
                'absences' => 5,
                'stratum' => '1',
                'connectivity' => true,
                'family_vuln' => true,
            ],
            [
                'name' => 'Camila Andrea Quintero',
                'username' => 'camila.quintero',
                'email' => 'camila.quintero@aprendiz.sga.edu.co',
                'ficha_id' => $fichaCyber->id,
                'doc' => '1020481920',
                'attendance' => 79.0,
                'average' => 3.1,
                'absences' => 4,
                'stratum' => '2',
                'connectivity' => false,
                'family_vuln' => true,
            ],
            [
                'name' => 'Santiago Morales Ospina',
                'username' => 'santiago.morales',
                'email' => 'santiago.morales@aprendiz.sga.edu.co',
                'ficha_id' => $fichaAdso->id,
                'doc' => '1017294011',
                'attendance' => 86.0,
                'average' => 3.4,
                'absences' => 2,
                'stratum' => '2',
                'connectivity' => true,
                'family_vuln' => false,
            ],
            [
                'name' => 'Daniela Herrera Cano',
                'username' => 'daniela.herrera',
                'email' => 'daniela.herrera@aprendiz.sga.edu.co',
                'ficha_id' => $fichaDatos->id,
                'doc' => '1039401288',
                'attendance' => 88.5,
                'average' => 3.5,
                'absences' => 2,
                'stratum' => '3',
                'connectivity' => false,
                'family_vuln' => false,
            ],
            [
                'name' => 'Sebastián Vargas Mejía',
                'username' => 'sebastian.vargas',
                'email' => 'sebastian.vargas@aprendiz.sga.edu.co',
                'ficha_id' => $fichaFines->id,
                'doc' => '1040918273',
                'attendance' => 83.0,
                'average' => 3.3,
                'absences' => 3,
                'stratum' => '2',
                'connectivity' => true,
                'family_vuln' => false,
            ],
            [
                'name' => 'Mariana López Aristizábal',
                'username' => 'mariana.lopez',
                'email' => 'mariana.lopez@aprendiz.sga.edu.co',
                'ficha_id' => $fichaAdso->id,
                'doc' => '1015928374',
                'attendance' => 98.5,
                'average' => 4.8,
                'absences' => 0,
                'stratum' => '3',
                'connectivity' => false,
                'family_vuln' => false,
            ],
            [
                'name' => 'Nicolás Pineda Salazar',
                'username' => 'nicolas.pineda',
                'email' => 'nicolas.pineda@aprendiz.sga.edu.co',
                'ficha_id' => $fichaCyber->id,
                'doc' => '1035819204',
                'attendance' => 95.0,
                'average' => 4.5,
                'absences' => 0,
                'stratum' => '3',
                'connectivity' => false,
                'family_vuln' => false,
            ],
        ];

        $createdProfiles = [];
        foreach ($apprenticesSeed as $seed) {
            $u = User::create([
                'name' => $seed['name'],
                'username' => $seed['username'],
                'email' => $seed['email'],
                'role' => User::ROLE_APRENDIZ,
                'permissions' => User::defaultPermissionsForRole(User::ROLE_APRENDIZ),
                'is_active' => true,
                'password' => Hash::make('123'),
            ]);

            $createdProfiles[$seed['username']] = ApprenticeProfile::create([
                'user_id' => $u->id,
                'ficha_id' => $seed['ficha_id'],
                'document_type' => 'CC',
                'document_number' => $seed['doc'],
                'phone' => '310' . rand(2000000, 9999999),
                'attendance_rate' => $seed['attendance'],
                'academic_average' => $seed['average'],
                'absences_count' => $seed['absences'],
                'socioeconomic_stratum' => $seed['stratum'],
                'has_connectivity_issues' => $seed['connectivity'],
                'has_family_vulnerability' => $seed['family_vuln'],
            ]);
        }

        $mateo = $createdProfiles['aprendiz1'];
        $camila = $createdProfiles['camila.quintero'];
        $santiago = $createdProfiles['santiago.morales'];
        $mariana = $createdProfiles['mariana.lopez'];

        // 6. Observaciones (Individuales, Masivas, Positivas y Escaladas)
        $obsMateo1 = Observation::create([
            'apprentice_profile_id' => $mateo->id,
            'ficha_id' => $fichaAdso->id,
            'author_id' => $instructor1User->id,
            'type' => 'inasistencia',
            'category' => 'desercion',
            'severity' => 4,
            'title' => 'Tres inasistencias consecutivas en módulo de Arquitectura Laravel',
            'description' => 'El aprendiz no se presentó a las sesiones prácticas de lunes a miércoles sin reporte previo. Se detecta riesgo de deserción temprana.',
            'escalated_to_welfare' => true,
            'escalated_at' => now()->subDays(3),
        ]);

        $obsMateo2 = Observation::create([
            'apprentice_profile_id' => $mateo->id,
            'ficha_id' => $fichaAdso->id,
            'author_id' => $instructor1User->id,
            'type' => 'llamado_atencion',
            'category' => 'academico',
            'severity' => 4,
            'title' => 'Incumplimiento en entrega de sprint de patrones Repository y Service',
            'description' => 'No entregó el avance de la capa de repositorios ni asistió a la sustentación técnica programada.',
        ]);

        Observation::create([
            'apprentice_profile_id' => $mateo->id,
            'ficha_id' => $fichaAdso->id,
            'author_id' => $instructor1User->id,
            'type' => 'positiva',
            'category' => 'academico',
            'severity' => 1,
            'title' => 'Participación destacada en taller de seguridad OWASP',
            'description' => 'Demostró excelente dominio en identificación de vulnerabilidades SQLi y XSS durante el laboratorio.',
        ]);

        $obsCamila = Observation::create([
            'apprentice_profile_id' => $camila->id,
            'ficha_id' => $fichaCyber->id,
            'author_id' => $instructor1User->id,
            'type' => 'disciplinaria',
            'category' => 'psicosocial',
            'severity' => 5,
            'title' => 'Episodio de crisis de ansiedad y manifestación de retiro voluntario',
            'description' => 'Durante la jornada nocturna la aprendiz manifestó sobrecarga laboral y familiar severa con intención de abandonar la ficha.',
            'escalated_to_welfare' => true,
            'escalated_at' => now()->subDays(2),
        ]);

        Observation::create([
            'apprentice_profile_id' => $santiago->id,
            'ficha_id' => $fichaAdso->id,
            'author_id' => $instructor1User->id,
            'type' => 'academica',
            'category' => 'academico',
            'severity' => 3,
            'title' => 'Refuerzo requerido en modelado relacional y migraciones',
            'description' => 'Requiere nivelación en diseño de llaves foráneas y normalización.',
        ]);

        Observation::create([
            'apprentice_profile_id' => $mariana->id,
            'ficha_id' => $fichaAdso->id,
            'author_id' => $instructor1User->id,
            'type' => 'positiva',
            'category' => 'academico',
            'severity' => 1,
            'title' => 'Liderazgo como vocera de ficha y mentoría a compañeros',
            'description' => 'Apoya activamente a los aprendices con dificultades técnicas en el proyecto integrador.',
        ]);

        // 7. Soportes / Excusas cargadas por el aprendiz
        EvidenceSupport::create([
            'observation_id' => $obsMateo1->id,
            'apprentice_profile_id' => $mateo->id,
            'title' => 'Incapacidad Médica EPS Sura - Infección Respiratoria',
            'support_type' => 'Excusa Médica',
            'description' => 'Adjunto certificado médico de incapacidad por 3 días expedido por urgencias médicas.',
            'status' => 'pendiente',
        ]);

        // 8. Casos de Bienestar y Seguimientos (Uno Activo con bloqueo de borrado, uno Resuelto)
        $caseMateo = WelfareCase::create([
            'case_code' => 'BIE-2026-A901X',
            'apprentice_profile_id' => $mateo->id,
            'origin_observation_id' => $obsMateo1->id,
            'opened_by' => $bienestar->id,
            'assigned_to' => $bienestar->id,
            'risk_type' => 'desercion',
            'priority' => 'critica',
            'status' => 'compromiso_activo',
            'title' => 'Intervención Integral por Inasistencias y Riesgo de Deserción',
            'initial_diagnosis' => 'Aprendiz con dificultades de transporte y conectividad, sumado a retraso en entregas técnicas del trimestre actual.',
            'intervention_route' => '1. Entrevista con Psicología y Trabajo Social. 2. Postulación a apoyo de transporte institucional. 3. Plan de nivelación con Instructor Líder.',
            'commitments' => 'Asistir al 100% de las sesiones de las próximas 3 semanas y entregar el plan de mejora del módulo Laravel antes del viernes.',
            'improvement_plan' => 'Tutorías personalizadas martes y jueves de 12:00 a 13:30 en el Laboratorio 402.',
            'due_date' => now()->addDays(12)->toDateString(),
        ]);

        WelfareCaseFollowup::create([
            'welfare_case_id' => $caseMateo->id,
            'user_id' => $bienestar->id,
            'stage' => 'Entrevista Inicial y Firma de Acta de Compromiso',
            'notes' => 'El aprendiz asistió puntualmente a la citación, expuso su situación familiar y firmó compromiso pedagógico y psicosocial.',
            'next_followup_date' => now()->addDays(7)->toDateString(),
        ]);

        $caseCamila = WelfareCase::create([
            'case_code' => 'BIE-2026-C402P',
            'apprentice_profile_id' => $camila->id,
            'origin_observation_id' => $obsCamila->id,
            'opened_by' => $instructor1User->id,
            'assigned_to' => $bienestar->id,
            'risk_type' => 'psicosocial',
            'priority' => 'critica',
            'status' => 'en_ruta',
            'title' => 'Escalamiento Directo: Acompañamiento Psicosocial Urgente',
            'initial_diagnosis' => 'Caso derivado por el instructor Carlos Restrepo ante riesgo inminente de retiro voluntario en jornada nocturna.',
            'intervention_route' => 'Atención prioritaria por psicología clínica y flexibilización temporal de entregas académicas.',
            'commitments' => 'Sesión semanal con Bienestar al Aprendiz.',
            'improvement_plan' => 'Ajuste de cronograma de laboratorios.',
            'due_date' => now()->addDays(10)->toDateString(),
        ]);

        WelfareCase::create([
            'case_code' => 'BIE-2026-R100Z',
            'apprentice_profile_id' => $santiago->id,
            'opened_by' => $bienestar->id,
            'assigned_to' => $bienestar->id,
            'risk_type' => 'academico',
            'priority' => 'media',
            'status' => 'resuelto',
            'title' => 'Nivelación Académica Trimestre Anterior (Caso Resuelto)',
            'initial_diagnosis' => 'Dificultad inicial en lógica de programación.',
            'intervention_route' => 'Monitoría par con vocera de ficha.',
            'commitments' => 'Completar taller práctico.',
            'improvement_plan' => 'Aprobado con nota 4.3.',
            'resolution_notes' => 'El aprendiz superó satisfactoriamente todos los resultados de aprendizaje pendientes.',
            'resolved_at' => now()->subDays(5),
        ]);

        // 9. Ejecutar Motor Predictivo de Riesgo para todos los aprendices
        /** @var RiskEngineService $riskEngine */
        $riskEngine = app(RiskEngineService::class);
        $riskEngine->recalculateAllApprentices(false);

        // 10. Notificaciones iniciales para Mateo (aprendiz1) y Bienestar
        /** @var NotificationService $notifier */
        $notifier = app(NotificationService::class);
        $notifier->notifyUser(
            $mateo->user_id,
            'Alerta de Semáforo: Riesgo Alto Detectado',
            'Tu índice predictivo de riesgo se encuentra en nivel ALTO. Se ha activado una ruta de acompañamiento con Bienestar al Aprendiz.',
            'riesgo',
            'danger',
            '/aprendiz'
        );
        $notifier->notifyUser(
            $mateo->user_id,
            'Citación Activa de Bienestar [BIE-2026-A901X]',
            'Tienes un compromiso y plan de mejora activo con la Dra. Laura Sofía Mendoza. Puedes consultar los detalles en tu perfil.',
            'citacion_bienestar',
            'warning',
            '/aprendiz'
        );
        $notifier->notifyUser(
            $mateo->user_id,
            'Nueva Observación Registrada por Ing. Carlos Restrepo',
            'Se registró un llamado de atención en tu expediente. Recuerda que puedes cargar soportes o descargos desde tu módulo de autogestión.',
            'observacion',
            'info',
            '/aprendiz'
        );

        // 11. Registros iniciales en Auditoría Inmutable con Hash SHA-256 encadenado
        /** @var AuditService $audit */
        $audit = app(AuditService::class);
        $audit->log(
            'Gobernanza · Inicialización',
            'Despliegue de Arquitectura Laravel MVC + Capas (Service & Repository) e inicialización de cadena criptográfica SHA-256',
            'info'
        );
        $audit->log(
            'Bienestar · Motor Predictivo',
            'Calibración inicial de pesos de valoración de riesgo (Deserción, Académico, Psicosocial) y semaforización de cohorte',
            'warning',
            'RiskParameter',
            1
        );
        $audit->log(
            'Instructor · Escalamiento Directo',
            'Derivación de caso crítico [BIE-2026-C402P] desde Ficha 2847102 hacia el Núcleo de Intervención de Bienestar',
            'critical',
            'WelfareCase',
            $caseCamila->id
        );
    }
}
