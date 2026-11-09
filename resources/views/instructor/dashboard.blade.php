@extends('layouts.app')

@section('title', 'Control Académico y Detección Temprana · Instructor | SGA')
@section('header_title', 'Control Académico y Detección Temprana · Instructor')
@section('header_subtitle', 'Carga ágil individual o masiva de novedades, escalamiento directo a Bienestar, expediente contextual y carga lectiva')

@section('content')
    <!-- Pestañas del Módulo Instructor -->
    <div class="flex flex-wrap items-center justify-between gap-4 mb-8 border-b border-slate-800 pb-4">
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('instructor.dashboard', ['tab' => 'novedades']) }}"
               class="px-4 py-2.5 rounded-xl text-sm font-semibold transition {{ $activeTab === 'novedades' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/25' : 'bg-slate-900 text-slate-300 border border-slate-800 hover:text-white' }}">
                1. Carga Ágil de Novedades & Escalamiento Directo
            </a>
            <a href="{{ route('instructor.dashboard', ['tab' => 'asignaciones']) }}"
               class="px-4 py-2.5 rounded-xl text-sm font-semibold transition {{ $activeTab === 'asignaciones' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/25' : 'bg-slate-900 text-slate-300 border border-slate-800 hover:text-white' }}">
                2. Gestión de Asignaciones, Horarios y Fichas ({{ $assignedFichas->count() }})
            </a>
        </div>

        @if($instructorProfile)
            <div class="flex items-center gap-3 px-4 py-2 rounded-xl bg-slate-900 border border-slate-800 text-xs">
                <span class="text-slate-400">Especialidad: <strong class="text-white">{{ $instructorProfile->specialty }}</strong></span>
                <span class="text-slate-600">|</span>
                <span class="mono text-indigo-300">{{ $totalAssignedHours }}h / {{ $instructorProfile->weekly_hours }}h semanales</span>
            </div>
        @endif
    </div>

    <!-- ========================================================================= -->
    <!-- PESTAÑA 1: CARGA ÁGIL INDIVIDUAL / MASIVA Y ESCALAMIENTO DIRECTO -->
    <!-- ========================================================================= -->
    @if($activeTab === 'novedades')
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Formulario de Carga Ágil Individual o Masiva -->
            <div class="lg:col-span-5 bg-slate-900/90 border border-slate-800 rounded-2xl p-6 h-fit">
                <span class="mono text-xs text-sky-400 uppercase tracking-widest">Alimentación en Tiempo Real del Algoritmo</span>
                <h3 class="text-lg font-bold text-white mt-1 mb-1">Registrar Novedad, Inasistencia o Llamado</h3>
                <p class="text-xs text-slate-400 mb-5">Cada registro actualiza instantáneamente los vectores de riesgo (deserción, académico, psicosocial) y notifica al aprendiz.</p>

                <form method="POST" action="{{ route('instructor.observations.store') }}" class="space-y-4 text-sm">
                    @csrf

                    <!-- Selector de Modo (Individual / Selección Múltiple / Masivo por Ficha) -->
                    <div>
                        <label class="block text-xs text-slate-400 mb-1.5 font-semibold uppercase tracking-wider">Modalidad de Carga</label>
                        <div class="grid grid-cols-3 gap-2">
                            <label class="cursor-pointer">
                                <input type="radio" name="target_mode" value="single" checked onchange="toggleTargetMode('single')" class="peer sr-only">
                                <span class="block text-center py-2 px-2 rounded-xl bg-slate-950 border border-slate-800 text-xs font-medium text-slate-300 peer-checked:bg-indigo-600 peer-checked:text-white peer-checked:border-indigo-500 transition">
                                    Individual
                                </span>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="target_mode" value="multiple" onchange="toggleTargetMode('multiple')" class="peer sr-only">
                                <span class="block text-center py-2 px-2 rounded-xl bg-slate-950 border border-slate-800 text-xs font-medium text-slate-300 peer-checked:bg-indigo-600 peer-checked:text-white peer-checked:border-indigo-500 transition">
                                    Múltiple
                                </span>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="target_mode" value="ficha_massive" onchange="toggleTargetMode('ficha_massive')" class="peer sr-only">
                                <span class="block text-center py-2 px-2 rounded-xl bg-slate-950 border border-slate-800 text-xs font-medium text-slate-300 peer-checked:bg-indigo-600 peer-checked:text-white peer-checked:border-indigo-500 transition">
                                    Ficha Masiva
                                </span>
                            </label>
                        </div>
                    </div>

                    <!-- Selector Individual -->
                    <div id="box-single">
                        <label class="block text-xs text-slate-400 mb-1">Aprendiz (Fichas bajo tu responsabilidad)</label>
                        <select name="apprentice_profile_id" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white">
                            @foreach($apprentices as $apr)
                                <option value="{{ $apr->id }}" @selected(request('aprendiz_id') == $apr->id)>
                                    [{{ $apr->ficha?->code }}] {{ $apr->user?->name }} — Riesgo {{ strtoupper($apr->risk_level) }} ({{ $apr->global_risk_score }}%)
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Selector Múltiple -->
                    <div id="box-multiple" class="hidden">
                        <label class="block text-xs text-slate-400 mb-1">Selecciona varios aprendices</label>
                        <div class="max-h-40 overflow-y-auto p-3 rounded-xl bg-slate-950 border border-slate-800 space-y-1.5">
                            @foreach($apprentices as $apr)
                                <label class="flex items-center gap-2 text-xs text-slate-300 cursor-pointer">
                                    <input type="checkbox" name="apprentice_ids[]" value="{{ $apr->id }}" class="rounded accent-indigo-500">
                                    <span>[{{ $apr->ficha?->code }}] {{ $apr->user?->name }} ({{ $apr->global_risk_score }}%)</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <!-- Selector Masivo por Ficha Completa -->
                    <div id="box-ficha-massive" class="hidden">
                        <label class="block text-xs text-slate-400 mb-1">Ficha Asignada (Aplica a todos los aprendices del grupo)</label>
                        <select name="ficha_id" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white">
                            @foreach($assignedFichas as $ficha)
                                <option value="{{ $ficha->id }}">
                                    Ficha {{ $ficha->code }} · {{ $ficha->trainingProgram?->name }} ({{ $ficha->apprentices->count() }} aprendices)
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs text-slate-400 mb-1">Tipo de Novedad</label>
                            <select name="type" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white">
                                <option value="academica">Novedad Académica</option>
                                <option value="inasistencia">Inasistencia Injustificada</option>
                                <option value="llamado_atencion">Llamado de Atención</option>
                                <option value="disciplinaria">Falta Disciplinaria</option>
                                <option value="positiva">Observación Positiva (Mitiga Riesgo)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs text-slate-400 mb-1">Dimensión Impactada</label>
                            <select name="category" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white">
                                <option value="academico">Académico</option>
                                <option value="desercion">Deserción / Permanencia</option>
                                <option value="psicosocial">Psicosocial / Convivencia</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs text-slate-400 mb-1">Severidad / Impacto (1 - 5)</label>
                            <select name="severity" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white">
                                <option value="1">1 · Leve / Mérito</option>
                                <option value="2">2 · Moderado</option>
                                <option value="3" selected>3 · Importante</option>
                                <option value="4">4 · Grave (Alerta)</option>
                                <option value="5">5 · Crítico (Urgente)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs text-slate-400 mb-1">Ajustar Promedio (Opcional)</label>
                            <input type="number" step="0.1" min="0" max="5" name="academic_average" placeholder="Ej: 3.1"
                                   class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs text-slate-400 mb-1">Título de la Observación</label>
                        <input name="title" required placeholder="Ej: Inasistencia a sustentación de arquitectura en capas"
                               class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white">
                    </div>

                    <div>
                        <label class="block text-xs text-slate-400 mb-1">Descripción Contextual</label>
                        <textarea name="description" rows="3" required placeholder="Detalla los hechos observados o los acuerdos académicos..."
                                  class="w-full px-3.5 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white"></textarea>
                    </div>

                    <!-- Switch de Escalamiento Directo a Bienestar -->
                    <div class="p-3.5 rounded-xl bg-rose-500/10 border border-rose-500/30">
                        <label class="flex items-start gap-3 cursor-pointer">
                            <input type="checkbox" name="escalate_immediately" value="1"
                                   onchange="document.getElementById('esc-notes').classList.toggle('hidden', !this.checked)"
                                   class="mt-1 rounded accent-rose-500">
                            <div>
                                <span class="text-xs font-bold text-rose-300 block">⚡ Escalamiento Directo al Equipo de Bienestar</span>
                                <span class="text-[11px] text-rose-200/80">Marca esta casilla si el caso excede el ámbito académico para crear automáticamente una Ruta de Atención Crítica en Bienestar.</span>
                            </div>
                        </label>
                        <div id="esc-notes" class="hidden mt-3">
                            <input name="escalation_notes" placeholder="Motivo urgente de derivación a Psicología / Trabajo Social..."
                                   class="w-full px-3 py-2 rounded-lg bg-slate-950 border border-rose-500/40 text-xs text-white">
                        </div>
                    </div>

                    <button type="submit"
                            class="w-full py-3 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-semibold shadow-lg shadow-indigo-600/25 transition cursor-pointer">
                        Registrar Novedad y Recalcular Riesgo en Tiempo Real →
                    </button>
                </form>
            </div>

            <!-- Columna Derecha: Historial de Observaciones, Botón de Escalamiento en 1 Clic y Soportes -->
            <div class="lg:col-span-7 space-y-6">
                <!-- Acceso Rápido a Expedientes Contextuales de Aprendices -->
                <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="text-base font-bold text-white">Aprendices en Fichas Asignadas (Semáforo en Tiempo Real)</h3>
                            <p class="text-xs text-slate-400">Consulta el historial contextual antes de emitir un juicio o reporte</p>
                        </div>
                    </div>

                    <div class="overflow-x-auto rounded-xl border border-slate-800">
                        <table class="w-full text-xs text-left">
                            <thead class="bg-slate-950 text-slate-400 uppercase mono">
                                <tr>
                                    <th class="px-3.5 py-2.5">Aprendiz</th>
                                    <th class="px-3.5 py-2.5">Ficha</th>
                                    <th class="px-3.5 py-2.5">Asistencia / Prom</th>
                                    <th class="px-3.5 py-2.5">Semáforo Predictivo</th>
                                    <th class="px-3.5 py-2.5 text-right">Expediente Contextual</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-800/80">
                                @foreach($apprentices as $apr)
                                    @php $c = $apr->semaphoreColor(); @endphp
                                    <tr class="hover:bg-slate-800/40">
                                        <td class="px-3.5 py-3 font-semibold text-white">{{ $apr->user?->name }}</td>
                                        <td class="px-3.5 py-3 mono text-indigo-300">{{ $apr->ficha?->code }}</td>
                                        <td class="px-3.5 py-3 text-slate-300">{{ $apr->attendance_rate }}% · Nota {{ $apr->academic_average }}</td>
                                        <td class="px-3.5 py-3">
                                            <span class="px-2.5 py-0.5 rounded-full font-bold bg-{{ $c }}-500/15 text-{{ $c }}-300 border border-{{ $c }}-500/30">
                                                {{ strtoupper($apr->risk_level) }} ({{ $apr->global_risk_score }}%)
                                            </span>
                                        </td>
                                        <td class="px-3.5 py-3 text-right">
                                            <a href="{{ route('instructor.expediente', $apr->id) }}"
                                               class="px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-indigo-600 text-slate-200 hover:text-white font-semibold transition">
                                                Abrir Expediente →
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Bitácora Reciente con Botón de Escalamiento Directo en 1 Clic -->
                <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-6 space-y-4">
                    <div>
                        <h3 class="text-base font-bold text-white">Bitácora de Novedades y Escalamiento Rápido a Bienestar</h3>
                        <p class="text-xs text-slate-400">Deriva cualquier novedad crítica directamente al Núcleo de Intervención con un clic</p>
                    </div>

                    @foreach($recentObservations as $obs)
                        <div class="p-4 rounded-2xl bg-slate-950/90 border border-slate-800 space-y-3">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold
                                            @if($obs->type === 'positiva') bg-emerald-500/15 text-emerald-300 border border-emerald-500/30
                                            @elseif($obs->severity >= 4) bg-rose-500/15 text-rose-300 border border-rose-500/30
                                            @else bg-amber-500/15 text-amber-300 border border-amber-500/30 @endif">
                                            {{ $obs->typeLabel() }} · Sev {{ $obs->severity }}/5
                                        </span>
                                        @if($obs->is_massive)
                                            <span class="mono text-[10px] px-2 py-0.5 rounded bg-indigo-500/15 text-indigo-300">CARGA MASIVA</span>
                                        @endif
                                        @if($obs->mitigated_by_support)
                                            <span class="mono text-[10px] px-2 py-0.5 rounded bg-emerald-500/15 text-emerald-300">MITIGADA POR SOPORTE APROBADO</span>
                                        @endif
                                    </div>
                                    <h4 class="text-sm font-bold text-white mt-1.5">{{ $obs->title }}</h4>
                                    <p class="text-xs text-slate-400">
                                        Aprendiz: <strong class="text-slate-200">{{ $obs->apprenticeProfile?->user?->name }}</strong> ·
                                        Ficha {{ $obs->ficha?->code }} · {{ $obs->created_at->format('d/m/Y H:i') }}
                                    </p>
                                </div>

                                <!-- Botón de Escalamiento Directo -->
                                @if($obs->escalated_to_welfare)
                                    <span class="px-3 py-1 rounded-xl bg-teal-500/15 border border-teal-500/30 text-teal-300 text-xs font-semibold">
                                        ✓ Escalado a Bienestar {{ $obs->welfareCase ? '['.$obs->welfareCase->case_code.']' : '' }}
                                    </span>
                                @elseif($obs->type !== 'positiva')
                                    <form method="POST" action="{{ route('instructor.observations.escalate', $obs->id) }}">
                                        @csrf
                                        <button type="submit"
                                                class="px-3 py-1.5 rounded-xl bg-rose-600 hover:bg-rose-500 text-white text-xs font-semibold shadow-md shadow-rose-600/20 transition cursor-pointer">
                                            ⚡ Escalar a Bienestar
                                        </button>
                                    </form>
                                @endif
                            </div>

                            <p class="text-xs text-slate-300 leading-relaxed">{{ $obs->description }}</p>

                            <!-- Si el aprendiz cargó soportes / excusas médicas en esta observación -->
                            @if($obs->supports->count() > 0)
                                <div class="pt-3 border-t border-slate-800 space-y-2">
                                    <span class="mono text-[11px] text-amber-300 uppercase">Soportes / Excusas cargadas por el aprendiz:</span>
                                    @foreach($obs->supports as $sup)
                                        <div class="p-3 rounded-xl bg-slate-900 border border-slate-800 flex flex-wrap items-center justify-between gap-3 text-xs">
                                            <div>
                                                <span class="font-bold text-white">[{{ $sup->support_type }}] {{ $sup->title }}</span>
                                                <p class="text-slate-400 mt-0.5">{{ $sup->description }}</p>
                                                @if($sup->file_path)
                                                    <a href="{{ route('supports.download', $sup->id) }}" class="text-indigo-400 hover:underline inline-block mt-1">
                                                        📎 Descargar adjunto ({{ $sup->file_name }})
                                                    </a>
                                                @endif
                                            </div>

                                            @if($sup->status === 'pendiente')
                                                <div class="flex items-center gap-2">
                                                    <form method="POST" action="{{ route('instructor.supports.review', $sup->id) }}">
                                                        @csrf
                                                        <input type="hidden" name="status" value="aprobado">
                                                        <button type="submit" class="px-2.5 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white font-semibold cursor-pointer">
                                                            Aprobar y Mitigar Riesgo
                                                        </button>
                                                    </form>
                                                    <form method="POST" action="{{ route('instructor.supports.review', $sup->id) }}">
                                                        @csrf
                                                        <input type="hidden" name="status" value="rechazado">
                                                        <button type="submit" class="px-2.5 py-1 rounded-lg bg-rose-600/20 text-rose-300 border border-rose-500/30 hover:bg-rose-600/30 cursor-pointer">
                                                            Rechazar
                                                        </button>
                                                    </form>
                                                </div>
                                            @else
                                                <span class="mono text-[11px] px-2.5 py-1 rounded-full {{ $sup->status === 'aprobado' ? 'bg-emerald-500/15 text-emerald-300' : 'bg-rose-500/15 text-rose-300' }}">
                                                    {{ strtoupper($sup->status) }}
                                                </span>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <!-- ========================================================================= -->
    <!-- PESTAÑA 2: GESTIÓN DE ASIGNACIONES, CARGA LECTIVA, HORARIOS Y FICHAS -->
    <!-- ========================================================================= -->
    @if($activeTab === 'asignaciones')
        <div class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                @foreach($assignedFichas as $ficha)
                    <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-6 flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between gap-2 mb-3">
                                <span class="mono text-sm font-bold px-3 py-1 rounded-xl bg-indigo-500/15 text-indigo-300 border border-indigo-500/30">
                                    Ficha {{ $ficha->code }}
                                </span>
                                @if($ficha->pivot->is_lead)
                                    <span class="mono text-[11px] px-2.5 py-0.5 rounded-full bg-emerald-500/15 text-emerald-300 border border-emerald-500/30">
                                        Instructor Líder
                                    </span>
                                @endif
                            </div>

                            <h3 class="text-lg font-bold text-white mb-1">{{ $ficha->trainingProgram?->name }}</h3>
                            <p class="text-xs text-slate-400 mb-4">
                                Sede: {{ $ficha->sede?->name }} · Jornada: <strong class="text-slate-200">{{ $ficha->jornada }}</strong> · Ambiente: {{ $ficha->classroom }}
                            </p>

                            <div class="p-3.5 rounded-xl bg-slate-950 border border-slate-800 space-y-2 text-xs mb-4">
                                <div>
                                    <span class="text-slate-500 uppercase block font-semibold">Competencia Asignada:</span>
                                    <span class="text-slate-200 font-medium">{{ $ficha->pivot->competency }}</span>
                                </div>
                                <div class="flex items-center justify-between pt-2 border-t border-slate-800">
                                    <span class="text-indigo-300 font-semibold">🕒 {{ $ficha->pivot->schedule }}</span>
                                    <span class="mono text-slate-400">{{ $ficha->pivot->hours_assigned }}h/sem</span>
                                </div>
                            </div>

                            <!-- Lista Rápida de Estudiantes de la Ficha -->
                            <p class="mono text-[11px] text-slate-400 uppercase mb-2">Aprendices de la Ficha ({{ $ficha->apprentices->count() }})</p>
                            <div class="space-y-1.5 mb-4">
                                @foreach($ficha->apprentices as $student)
                                    @php $col = $student->semaphoreColor(); @endphp
                                    <div class="flex items-center justify-between p-2 rounded-lg bg-slate-950/70 border border-slate-800/80 text-xs">
                                        <span class="text-slate-200 font-medium truncate">{{ $student->user?->name }}</span>
                                        <div class="flex items-center gap-2 shrink-0">
                                            <span class="w-2 h-2 rounded-full bg-{{ $col }}-500"></span>
                                            <span class="mono text-[11px] text-{{ $col }}-300">{{ $student->global_risk_score }}%</span>
                                            <a href="{{ route('instructor.expediente', $student->id) }}" class="text-indigo-400 hover:underline">Expediente</a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <a href="{{ route('instructor.dashboard', ['tab' => 'novedades', 'ficha_id' => $ficha->id]) }}"
                           class="block text-center py-2.5 rounded-xl bg-slate-800 hover:bg-indigo-600 text-white text-xs font-semibold transition">
                            Cargar Novedad a esta Ficha →
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <script>
        function toggleTargetMode(mode) {
            document.getElementById('box-single').classList.toggle('hidden', mode !== 'single');
            document.getElementById('box-multiple').classList.toggle('hidden', mode !== 'multiple');
            document.getElementById('box-ficha-massive').classList.toggle('hidden', mode !== 'ficha_massive');
        }
    </script>
@endsection
