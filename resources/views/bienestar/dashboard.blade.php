@extends('layouts.app')

@section('title', 'Núcleo de Intervención · Gestor de Bienestar | SGA')
@section('header_title', 'Núcleo de Intervención · Gestor de Bienestar y Coordinación')
@section('header_subtitle', 'Monitoreo predictivo con semaforización automática, ajuste de variables del algoritmo, trazabilidad de casos y reportes masivos')

@section('content')
    <!-- Pestañas de Navegación del Módulo de Bienestar -->
    <div class="no-print flex flex-wrap items-center gap-2 mb-8 border-b border-slate-800 pb-4">
        <a href="{{ route('bienestar.dashboard', array_merge($filters, ['tab' => 'monitoreo'])) }}"
           class="px-4 py-2.5 rounded-xl text-sm font-semibold transition {{ $activeTab === 'monitoreo' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/25' : 'bg-slate-900 text-slate-300 border border-slate-800 hover:text-white' }}">
            1. Monitoreo Predictivo y Semáforo
        </a>
        <a href="{{ route('bienestar.dashboard', array_merge($filters, ['tab' => 'variables'])) }}"
           class="px-4 py-2.5 rounded-xl text-sm font-semibold transition {{ $activeTab === 'variables' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/25' : 'bg-slate-900 text-slate-300 border border-slate-800 hover:text-white' }}">
            2. Gestión de Variables del Modelo
        </a>
        <a href="{{ route('bienestar.dashboard', array_merge($filters, ['tab' => 'casos'])) }}"
           class="px-4 py-2.5 rounded-xl text-sm font-semibold transition {{ $activeTab === 'casos' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/25' : 'bg-slate-900 text-slate-300 border border-slate-800 hover:text-white' }}">
            3. Trazabilidad de Casos ({{ $cases->count() }})
        </a>
        <a href="{{ route('bienestar.dashboard', array_merge($filters, ['tab' => 'reportes'])) }}"
           class="px-4 py-2.5 rounded-xl text-sm font-semibold transition {{ $activeTab === 'reportes' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/25' : 'bg-slate-900 text-slate-300 border border-slate-800 hover:text-white' }}">
            4. Reportes Masivos Consolidados
        </a>
    </div>

    <!-- Tarjetas de Resumen del Semáforo Predictivo -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
        <div class="p-5 rounded-2xl bg-slate-900/90 border border-slate-800">
            <span class="mono text-xs text-slate-400 uppercase tracking-wider">Población Evaluada</span>
            <div class="text-3xl font-extrabold text-white mt-2">{{ $stats['total'] }}</div>
            <p class="text-xs text-slate-400 mt-1">Riesgo global prom: <strong class="text-indigo-400">{{ $stats['avg_global'] }}%</strong></p>
        </div>

        <div class="p-5 rounded-2xl bg-rose-500/10 border border-rose-500/30">
            <div class="flex items-center justify-between">
                <span class="mono text-xs text-rose-300 uppercase tracking-wider">Semáforo Rojo</span>
                <span class="w-3 h-3 rounded-full bg-rose-500 animate-pulse"></span>
            </div>
            <div class="text-3xl font-extrabold text-rose-400 mt-2">{{ $stats['alto'] }}</div>
            <p class="text-xs text-rose-200/80 mt-1">Riesgo Crítico / Alto (Intervención)</p>
        </div>

        <div class="p-5 rounded-2xl bg-amber-500/10 border border-amber-500/30">
            <div class="flex items-center justify-between">
                <span class="mono text-xs text-amber-300 uppercase tracking-wider">Semáforo Amarillo</span>
                <span class="w-3 h-3 rounded-full bg-amber-400"></span>
            </div>
            <div class="text-3xl font-extrabold text-amber-300 mt-2">{{ $stats['medio'] }}</div>
            <p class="text-xs text-amber-200/80 mt-1">Riesgo Medio / Alerta Preventiva</p>
        </div>

        <div class="p-5 rounded-2xl bg-emerald-500/10 border border-emerald-500/30">
            <div class="flex items-center justify-between">
                <span class="mono text-xs text-emerald-300 uppercase tracking-wider">Semáforo Verde</span>
                <span class="w-3 h-3 rounded-full bg-emerald-400"></span>
            </div>
            <div class="text-3xl font-extrabold text-emerald-400 mt-2">{{ $stats['bajo'] }}</div>
            <p class="text-xs text-emerald-200/80 mt-1">Riesgo Bajo / Permanencia Estable</p>
        </div>

        <div class="p-5 rounded-2xl bg-slate-900/90 border border-slate-800 space-y-1.5">
            <span class="mono text-[11px] text-indigo-400 uppercase tracking-wider block">Promedios por Vector</span>
            <div class="flex justify-between text-xs"><span class="text-slate-400">Deserción:</span><span class="font-bold text-white">{{ $stats['avg_dropout'] }}%</span></div>
            <div class="flex justify-between text-xs"><span class="text-slate-400">Académico:</span><span class="font-bold text-white">{{ $stats['avg_academic'] }}%</span></div>
            <div class="flex justify-between text-xs"><span class="text-slate-400">Psicosocial:</span><span class="font-bold text-white">{{ $stats['avg_psychosocial'] }}%</span></div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- PESTAÑA 1: MONITOREO PREDICTIVO CON SEMAFORIZACIÓN AUTOMÁTICA -->
    <!-- ========================================================================= -->
    @if($activeTab === 'monitoreo')
        <!-- Barra de Filtros Institucionales -->
        <form method="GET" action="{{ route('bienestar.dashboard') }}" class="bg-slate-900/90 border border-slate-800 rounded-2xl p-5 mb-6 grid grid-cols-1 md:grid-cols-6 gap-3 items-end">
            <input type="hidden" name="tab" value="monitoreo">
            <div>
                <label class="block text-xs text-slate-400 mb-1">Buscar Aprendiz / Doc</label>
                <input name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Nombre o CC..."
                       class="w-full px-3.5 py-2 rounded-xl bg-slate-950 border border-slate-800 text-sm text-white">
            </div>
            <div>
                <label class="block text-xs text-slate-400 mb-1">Ficha</label>
                <select name="ficha_id" class="w-full px-3.5 py-2 rounded-xl bg-slate-950 border border-slate-800 text-sm text-white">
                    <option value="">Todas las fichas</option>
                    @foreach($fichas as $f)
                        <option value="{{ $f->id }}" @selected(($filters['ficha_id'] ?? '') == $f->id)>
                            {{ $f->code }} · {{ $f->jornada }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs text-slate-400 mb-1">Programa</label>
                <select name="program_id" class="w-full px-3.5 py-2 rounded-xl bg-slate-950 border border-slate-800 text-sm text-white">
                    <option value="">Todos los programas</option>
                    @foreach($programs as $p)
                        <option value="{{ $p->id }}" @selected(($filters['program_id'] ?? '') == $p->id)>{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs text-slate-400 mb-1">Jornada</label>
                <select name="jornada" class="w-full px-3.5 py-2 rounded-xl bg-slate-950 border border-slate-800 text-sm text-white">
                    <option value="">Todas las jornadas</option>
                    @foreach(['Mañana', 'Tarde', 'Noche', 'Fines de Semana'] as $j)
                        <option value="{{ $j }}" @selected(($filters['jornada'] ?? '') === $j)>{{ $j }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs text-slate-400 mb-1">Semáforo</label>
                <select name="risk_level" class="w-full px-3.5 py-2 rounded-xl bg-slate-950 border border-slate-800 text-sm text-white">
                    <option value="">Todos los niveles</option>
                    <option value="alto" @selected(($filters['risk_level'] ?? '') === 'alto')>🔴 Riesgo Alto</option>
                    <option value="medio" @selected(($filters['risk_level'] ?? '') === 'medio')>🟡 Riesgo Medio</option>
                    <option value="bajo" @selected(($filters['risk_level'] ?? '') === 'bajo')>🟢 Riesgo Bajo</option>
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="flex-1 py-2 px-4 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold transition cursor-pointer">
                    Filtrar
                </button>
                <a href="{{ route('bienestar.dashboard', ['tab' => 'monitoreo']) }}" class="py-2 px-3 rounded-xl bg-slate-800 text-slate-300 text-sm hover:text-white">
                    Limpiar
                </a>
            </div>
        </form>

        <!-- Matriz de Semaforización por Aprendiz -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
            @foreach($apprentices as $apprentice)
                @php
                    $color = $apprentice->semaphoreColor();
                @endphp
                <div class="rounded-2xl bg-slate-900/90 border border-slate-800 p-6 flex flex-col justify-between hover:border-{{ $color }}-500/50 transition">
                    <div>
                        <div class="flex flex-wrap items-start justify-between gap-3 mb-4">
                            <div>
                                <div class="flex items-center gap-2.5">
                                    <span class="w-3 h-3 rounded-full bg-{{ $color }}-500"></span>
                                    <h3 class="text-lg font-bold text-white">{{ $apprentice->user?->name }}</h3>
                                </div>
                                <p class="text-xs text-slate-400 mt-0.5">
                                    {{ $apprentice->document_type }} {{ $apprentice->document_number }} ·
                                    Ficha <span class="mono text-indigo-300">{{ $apprentice->ficha?->code ?? 'N/A' }}</span> ({{ $apprentice->ficha?->jornada }}) ·
                                    {{ $apprentice->ficha?->trainingProgram?->name }}
                                </p>
                            </div>

                            <span class="px-3 py-1 rounded-full text-xs font-bold border bg-{{ $color }}-500/15 text-{{ $color }}-300 border-{{ $color }}-500/30">
                                {{ strtoupper($apprentice->risk_level) }} · {{ $apprentice->global_risk_score }}%
                            </span>
                        </div>

                        <!-- Barras de los 3 Vectores del Algoritmo -->
                        <div class="space-y-3 bg-slate-950/80 border border-slate-800/80 rounded-xl p-4 mb-4">
                            <div>
                                <div class="flex justify-between text-xs mb-1">
                                    <span class="text-slate-300 font-medium">1. Riesgo de Deserción (Asistencia: {{ $apprentice->attendance_rate }}%, Fallas: {{ $apprentice->absences_count }})</span>
                                    <span class="mono font-bold text-rose-300">{{ $apprentice->dropout_risk_score }}%</span>
                                </div>
                                <div class="w-full h-2 rounded-full bg-slate-800 overflow-hidden">
                                    <div class="h-full bg-rose-500 rounded-full" style="width: {{ min(100, $apprentice->dropout_risk_score) }}%"></div>
                                </div>
                            </div>

                            <div>
                                <div class="flex justify-between text-xs mb-1">
                                    <span class="text-slate-300 font-medium">2. Riesgo Académico (Promedio: {{ $apprentice->academic_average }}/5.0)</span>
                                    <span class="mono font-bold text-amber-300">{{ $apprentice->academic_risk_score }}%</span>
                                </div>
                                <div class="w-full h-2 rounded-full bg-slate-800 overflow-hidden">
                                    <div class="h-full bg-amber-400 rounded-full" style="width: {{ min(100, $apprentice->academic_risk_score) }}%"></div>
                                </div>
                            </div>

                            <div>
                                <div class="flex justify-between text-xs mb-1">
                                    <span class="text-slate-300 font-medium">3. Riesgo Psicosocial (Estrato {{ $apprentice->socioeconomic_stratum }})</span>
                                    <span class="mono font-bold text-violet-300">{{ $apprentice->psychosocial_risk_score }}%</span>
                                </div>
                                <div class="w-full h-2 rounded-full bg-slate-800 overflow-hidden">
                                    <div class="h-full bg-violet-500 rounded-full" style="width: {{ min(100, $apprentice->psychosocial_risk_score) }}%"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center justify-between gap-3 pt-3 border-t border-slate-800 text-xs">
                        <div class="text-slate-400">
                            Estado: <span class="text-white font-semibold">{{ $apprentice->academic_status }}</span> ·
                            Observaciones: <span class="text-white font-semibold">{{ $apprentice->observations->count() }}</span> ·
                            Casos activos: <span class="text-teal-300 font-semibold">{{ $apprentice->welfareCases->where('status', '!=', 'resuelto')->count() }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <a href="{{ route('instructor.expediente', $apprentice->id) }}"
                               class="px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 font-medium transition">
                                Ver Expediente
                            </a>
                            <a href="{{ route('bienestar.dashboard', ['tab' => 'casos', 'open_for' => $apprentice->id]) }}"
                               class="px-3 py-1.5 rounded-lg bg-teal-600 hover:bg-teal-500 text-white font-semibold transition">
                                + Abrir Ruta de Atención
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <!-- ========================================================================= -->
    <!-- PESTAÑA 2: GESTIÓN DE VARIABLES DEL MODELO SIN TOCAR CÓDIGO FUENTE -->
    <!-- ========================================================================= -->
    @if($activeTab === 'variables')
        <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-6 md:p-8">
            <div class="flex flex-wrap items-center justify-between gap-4 mb-6 pb-5 border-b border-slate-800">
                <div>
                    <span class="mono text-xs text-indigo-400 uppercase tracking-widest">Motor Predictivo Parametrizable</span>
                    <h3 class="text-xl font-bold text-white mt-1">Calibración de Pesos y Umbrales del Algoritmo de Valoración</h3>
                    <p class="text-sm text-slate-400 mt-1">
                        Modifica en caliente el peso de cada variable sin intervenir el código fuente (<code class="text-indigo-300">RiskEngineService</code>). Al guardar, el sistema recalcula automáticamente a todos los aprendices y deja trazabilidad en auditoría.
                    </p>
                </div>
            </div>

            <form method="POST" action="{{ route('bienestar.parameters.update') }}" class="space-y-6">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                    @foreach($parameters as $param)
                        <div class="p-5 rounded-2xl bg-slate-950/90 border border-slate-800 flex flex-col justify-between">
                            <div>
                                <div class="flex items-center justify-between gap-2 mb-2">
                                    <span class="mono text-[11px] px-2.5 py-0.5 rounded-full uppercase
                                        @if($param->dimension === 'desercion') bg-rose-500/15 text-rose-300 border border-rose-500/30
                                        @elseif($param->dimension === 'academico') bg-amber-500/15 text-amber-300 border border-amber-500/30
                                        @elseif($param->dimension === 'psicosocial') bg-violet-500/15 text-violet-300 border border-violet-500/30
                                        @else bg-emerald-500/15 text-emerald-300 border border-emerald-500/30 @endif">
                                        {{ $param->dimension }}
                                    </span>
                                    <span class="mono text-[11px] text-slate-500">{{ $param->code }}</span>
                                </div>

                                <h4 class="text-sm font-bold text-white mb-1">{{ $param->name }}</h4>
                                <p class="text-xs text-slate-400 leading-relaxed mb-4">{{ $param->description }}</p>
                            </div>

                            <div class="pt-3 border-t border-slate-800/80">
                                <div class="flex items-center justify-between text-xs text-slate-400 mb-1.5">
                                    <span>Rango permitido: {{ $param->min_value }} – {{ $param->max_value }}</span>
                                    <span class="mono font-bold text-indigo-300" id="val-label-{{ $param->id }}">{{ $param->weight }}</span>
                                </div>
                                <div class="flex items-center gap-3">
                                    <input type="range"
                                           min="{{ $param->min_value }}"
                                           max="{{ $param->max_value }}"
                                           step="0.5"
                                           value="{{ $param->weight }}"
                                           oninput="document.getElementById('num-{{ $param->id }}').value = this.value; document.getElementById('val-label-{{ $param->id }}').innerText = this.value;"
                                           class="flex-1 accent-indigo-500 cursor-pointer">
                                    <input type="number"
                                           id="num-{{ $param->id }}"
                                           name="weights[{{ $param->id }}]"
                                           min="{{ $param->min_value }}"
                                           max="{{ $param->max_value }}"
                                           step="0.1"
                                           value="{{ $param->weight }}"
                                           oninput="document.getElementById('val-label-{{ $param->id }}').innerText = this.value;"
                                           class="w-20 px-2.5 py-1.5 rounded-xl bg-slate-900 border border-slate-700 text-white text-center mono text-sm">
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="flex justify-end pt-4 border-t border-slate-800">
                    <button type="submit"
                            class="px-6 py-3 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-semibold shadow-lg shadow-indigo-600/25 transition cursor-pointer">
                        Guardar Parámetros y Recalcular Semáforo en Tiempo Real →
                    </button>
                </div>
            </form>
        </div>
    @endif

    <!-- ========================================================================= -->
    <!-- PESTAÑA 3: TRAZABILIDAD DE CASOS Y RUTAS DE ATENCIÓN (BLOQUEO DE BORRADO) -->
    <!-- ========================================================================= -->
    @if($activeTab === 'casos')
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Formulario de Creación de Ruta de Atención -->
            <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-6 h-fit">
                <span class="mono text-xs text-teal-400 uppercase tracking-widest">Nueva Intervención</span>
                <h3 class="text-lg font-bold text-white mt-1 mb-4">Registrar Ruta de Atención y Compromiso</h3>

                <form method="POST" action="{{ route('bienestar.cases.store') }}" class="space-y-3.5 text-sm">
                    @csrf
                    <div>
                        <label class="block text-xs text-slate-400 mb-1">Aprendiz en Riesgo</label>
                        <select name="apprentice_profile_id" required class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white">
                            @foreach($apprentices as $apr)
                                <option value="{{ $apr->id }}" @selected(request('open_for') == $apr->id)>
                                    [{{ strtoupper($apr->risk_level) }} {{ $apr->global_risk_score }}%] {{ $apr->user?->name }} ({{ $apr->ficha?->code }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs text-slate-400 mb-1">Dimensión de Riesgo</label>
                            <select name="risk_type" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white">
                                <option value="integral">Integral</option>
                                <option value="desercion">Deserción</option>
                                <option value="academico">Académico</option>
                                <option value="psicosocial">Psicosocial</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs text-slate-400 mb-1">Prioridad</label>
                            <select name="priority" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white">
                                <option value="critica">Crítica</option>
                                <option value="alta" selected>Alta</option>
                                <option value="media">Media</option>
                                <option value="baja">Baja</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs text-slate-400 mb-1">Título del Caso</label>
                        <input name="title" required placeholder="Ej: Ruta de Permanencia y Apoyo Psicosocial"
                               class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white">
                    </div>

                    <div>
                        <label class="block text-xs text-slate-400 mb-1">Diagnóstico Inicial</label>
                        <textarea name="initial_diagnosis" rows="2" required placeholder="Contexto detectado por el equipo de bienestar..."
                                  class="w-full px-3.5 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white"></textarea>
                    </div>

                    <div>
                        <label class="block text-xs text-slate-400 mb-1">Ruta de Atención Definida</label>
                        <textarea name="intervention_route" rows="2" placeholder="Pasos de acompañamiento psicológico, social o académico..."
                                  class="w-full px-3.5 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white"></textarea>
                    </div>

                    <div>
                        <label class="block text-xs text-slate-400 mb-1">Compromisos del Aprendiz</label>
                        <textarea name="commitments" rows="2" placeholder="Acuerdos y metas firmadas..."
                                  class="w-full px-3.5 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white"></textarea>
                    </div>

                    <div>
                        <label class="block text-xs text-slate-400 mb-1">Plan de Mejora</label>
                        <textarea name="improvement_plan" rows="2" placeholder="Actividades de nivelación y seguimiento..."
                                  class="w-full px-3.5 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white"></textarea>
                    </div>

                    <div>
                        <label class="block text-xs text-slate-400 mb-1">Fecha Límite de Revisión</label>
                        <input type="date" name="due_date" value="{{ now()->addDays(15)->toDateString() }}"
                               class="w-full px-3.5 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white">
                    </div>

                    <button type="submit" class="w-full py-3 rounded-xl bg-teal-600 hover:bg-teal-500 text-white font-semibold shadow-lg shadow-teal-600/20 transition cursor-pointer">
                        Abrir Caso y Notificar Aprendiz
                    </button>
                </form>
            </div>

            <!-- Listado y Seguimiento de Casos con Regla de Bloqueo de Eliminación -->
            <div class="lg:col-span-2 space-y-4">
                <div class="p-4 rounded-2xl bg-indigo-500/10 border border-indigo-500/30 text-xs text-indigo-200 flex items-center justify-between gap-4">
                    <span>
                        <strong>Política de Trazabilidad Inquebrantable:</strong> La eliminación de un caso está bloqueada en la interfaz, en <code class="mono">BienestarService</code> y en el modelo <code class="mono">WelfareCase</code> hasta que su estado sea <strong>Resuelto</strong>.
                    </span>
                </div>

                @foreach($cases as $case)
                    <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-6 space-y-4">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div>
                                <div class="flex items-center gap-2.5">
                                    <span class="mono text-xs px-2.5 py-0.5 rounded-lg bg-slate-800 text-indigo-300 font-bold">{{ $case->case_code }}</span>
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold
                                        @if($case->status === 'resuelto') bg-emerald-500/15 text-emerald-300 border border-emerald-500/30
                                        @else bg-amber-500/15 text-amber-300 border border-amber-500/30 @endif">
                                        {{ $case->statusLabel() }}
                                    </span>
                                    <span class="mono text-xs uppercase px-2 py-0.5 rounded bg-rose-500/15 text-rose-300">
                                        Prioridad {{ $case->priority }}
                                    </span>
                                </div>
                                <h4 class="text-base font-bold text-white mt-2">{{ $case->title }}</h4>
                                <p class="text-xs text-slate-400">
                                    Aprendiz: <strong class="text-slate-200">{{ $case->apprenticeProfile?->user?->name }}</strong> ·
                                    Ficha {{ $case->apprenticeProfile?->ficha?->code }} ·
                                    Abierto por {{ $case->opener?->name ?? 'Sistema' }}
                                </p>
                            </div>

                            <!-- Botón de Eliminación (Bloqueado si no está resuelto / Habilitado si está resuelto) -->
                            <div class="flex items-center gap-2">
                                <form method="POST" action="{{ route('bienestar.cases.destroy', $case->id) }}">
                                    @csrf
                                    @method('DELETE')
                                    @if($case->canBeDeleted())
                                        <button type="submit"
                                                class="px-3 py-1.5 rounded-xl bg-rose-500/15 border border-rose-500/30 text-rose-300 hover:bg-rose-500/25 text-xs font-semibold transition cursor-pointer">
                                            Eliminar Caso Resuelto
                                        </button>
                                    @else
                                        <button type="submit"
                                                title="Intentar eliminar disparará el bloqueo de trazabilidad hasta su resolución"
                                                class="px-3 py-1.5 rounded-xl bg-slate-800 border border-slate-700 text-slate-400 hover:border-amber-500/50 hover:text-amber-300 text-xs font-medium transition cursor-pointer">
                                            🔒 Bloqueado (Probar Regla)
                                        </button>
                                    @endif
                                </form>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-xs bg-slate-950/80 border border-slate-800/80 rounded-xl p-4">
                            <div>
                                <span class="text-slate-500 block mb-1 font-semibold uppercase">Ruta de Atención</span>
                                <p class="text-slate-300 leading-relaxed">{{ $case->intervention_route ?: 'Por definir' }}</p>
                            </div>
                            <div>
                                <span class="text-slate-500 block mb-1 font-semibold uppercase">Compromisos</span>
                                <p class="text-slate-300 leading-relaxed">{{ $case->commitments ?: 'Sin compromisos registrados' }}</p>
                            </div>
                            <div>
                                <span class="text-slate-500 block mb-1 font-semibold uppercase">Plan de Mejora</span>
                                <p class="text-slate-300 leading-relaxed">{{ $case->improvement_plan ?: 'En estructuración' }}</p>
                            </div>
                        </div>

                        <!-- Bitácora de Seguimientos del Caso -->
                        @if($case->followups->count() > 0)
                            <div class="space-y-2">
                                <p class="mono text-[11px] text-slate-400 uppercase">Bitácora de Trazabilidad ({{ $case->followups->count() }})</p>
                                @foreach($case->followups as $fu)
                                    <div class="p-3 rounded-xl bg-slate-950 border border-slate-800 text-xs flex items-start justify-between gap-4">
                                        <div>
                                            <span class="font-bold text-teal-300">{{ $fu->stage }}:</span>
                                            <span class="text-slate-300">{{ $fu->notes }}</span>
                                        </div>
                                        <span class="mono text-[10px] text-slate-500 shrink-0">{{ $fu->created_at->format('d/m/Y H:i') }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <!-- Formulario Rápido de Actualización y Seguimiento -->
                        <details class="group pt-2 border-t border-slate-800">
                            <summary class="text-xs font-semibold text-indigo-400 hover:text-indigo-300 cursor-pointer list-none flex items-center justify-between">
                                <span>+ Registrar Seguimiento / Actualizar Estado o Resolver Caso</span>
                                <span class="mono text-[11px] text-slate-500">Desplegar</span>
                            </summary>

                            <form method="POST" action="{{ route('bienestar.cases.update', $case->id) }}" class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-3 text-xs">
                                @csrf
                                @method('PUT')
                                <div>
                                    <label class="block text-slate-400 mb-1">Estado del Caso</label>
                                    <select name="status" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white">
                                        @foreach(['abierto' => 'Abierto', 'en_ruta' => 'En Ruta de Atención', 'compromiso_activo' => 'Con Compromiso Firmado', 'en_seguimiento' => 'En Seguimiento Activo', 'resuelto' => 'Resuelto / Cerrado (Habilita archivo/eliminación)'] as $stKey => $stLabel)
                                            <option value="{{ $stKey }}" @selected($case->status === $stKey)>{{ $stLabel }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-slate-400 mb-1">Etapa de Bitácora</label>
                                    <input name="followup_stage" placeholder="Ej: Revisión de Compromiso / Cierre Exitoso"
                                           class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white">
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-slate-400 mb-1">Actualizar Compromisos / Plan de Mejora</label>
                                    <input name="commitments" value="{{ $case->commitments }}"
                                           class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white">
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-slate-400 mb-1">Nota de Seguimiento o Acta de Resolución</label>
                                    <textarea name="followup_notes" rows="2" placeholder="Describe el avance del aprendiz en su ruta de atención..."
                                              class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white"></textarea>
                                </div>
                                <div class="md:col-span-2 flex justify-end">
                                    <button type="submit" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-semibold cursor-pointer">
                                        Guardar Seguimiento y Estado
                                    </button>
                                </div>
                            </form>
                        </details>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- ========================================================================= -->
    <!-- PESTAÑA 4: REPORTES MASIVOS CONSOLIDADOS POR FICHA, PROGRAMA Y JORNADA -->
    <!-- ========================================================================= -->
    @if($activeTab === 'reportes')
        <div class="space-y-6">
            <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-6 flex flex-wrap items-center justify-between gap-4">
                <div>
                    <span class="mono text-xs text-cyan-400 uppercase tracking-widest">Inteligencia Institucional</span>
                    <h3 class="text-xl font-bold text-white mt-1">Informes Consolidados de Vulnerabilidad por Ficha, Programa y Jornada</h3>
                    <p class="text-sm text-slate-400">Genera matrices consolidadas para comités de evaluación y seguimiento o descárgalas en formato CSV compatible con Excel.</p>
                </div>
                <div class="no-print flex items-center gap-3">
                    <a href="{{ route('bienestar.reports.export', $filters) }}"
                       class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-sm font-semibold shadow-lg shadow-emerald-600/20 transition">
                        ⬇ Descargar CSV Consolidado
                    </a>
                    <button type="button" onclick="window.print()"
                            class="px-5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-white text-sm font-semibold border border-slate-700 transition cursor-pointer">
                        🖨 Imprimir Informe Ejecutivo
                    </button>
                </div>
            </div>

            <!-- Consolidado por Ficha y Programa -->
            <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-6">
                <h4 class="text-base font-bold text-white mb-4">1. Matriz de Vulnerabilidad Consolidada por Ficha y Programa de Formación</h4>
                <div class="overflow-x-auto rounded-xl border border-slate-800">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-slate-950 text-slate-400 text-xs uppercase mono">
                            <tr>
                                <th class="px-4 py-3">Ficha</th>
                                <th class="px-4 py-3">Programa de Formación</th>
                                <th class="px-4 py-3">Jornada / Sede</th>
                                <th class="px-4 py-3 text-center">Aprendices</th>
                                <th class="px-4 py-3 text-center text-rose-400">Alto (Rojo)</th>
                                <th class="px-4 py-3 text-center text-amber-300">Medio (Amarillo)</th>
                                <th class="px-4 py-3 text-center text-emerald-400">Bajo (Verde)</th>
                                <th class="px-4 py-3 text-right">Prom. Deserción</th>
                                <th class="px-4 py-3 text-right">Prom. Global</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/80">
                            @foreach($reportData['by_ficha'] as $row)
                                <tr class="hover:bg-slate-800/40">
                                    <td class="px-4 py-3.5 mono font-bold text-indigo-300">{{ $row['ficha_code'] }}</td>
                                    <td class="px-4 py-3.5 font-medium text-white">{{ $row['program'] }}</td>
                                    <td class="px-4 py-3.5 text-xs text-slate-400">{{ $row['jornada'] }} · {{ $row['sede'] }}</td>
                                    <td class="px-4 py-3.5 text-center font-bold">{{ $row['total'] }}</td>
                                    <td class="px-4 py-3.5 text-center font-bold text-rose-400">{{ $row['alto'] }}</td>
                                    <td class="px-4 py-3.5 text-center font-bold text-amber-300">{{ $row['medio'] }}</td>
                                    <td class="px-4 py-3.5 text-center font-bold text-emerald-400">{{ $row['bajo'] }}</td>
                                    <td class="px-4 py-3.5 text-right mono text-slate-300">{{ $row['avg_dropout'] }}%</td>
                                    <td class="px-4 py-3.5 text-right mono font-bold text-white">{{ $row['avg_global'] }}%</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Consolidado por Jornada -->
            <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-6">
                <h4 class="text-base font-bold text-white mb-4">2. Distribución de Riesgo por Jornada Institucional</h4>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    @foreach($reportData['by_jornada'] as $jRow)
                        <div class="p-4 rounded-xl bg-slate-950 border border-slate-800">
                            <div class="flex items-center justify-between mb-2">
                                <span class="font-bold text-white">{{ $jRow['jornada'] }}</span>
                                <span class="mono text-xs text-indigo-400">{{ $jRow['total'] }} aprendices</span>
                            </div>
                            <div class="flex items-center justify-between text-xs py-1 border-b border-slate-800">
                                <span class="text-rose-300">Riesgo Alto:</span>
                                <span class="font-bold text-rose-400">{{ $jRow['alto'] }}</span>
                            </div>
                            <div class="flex items-center justify-between text-xs py-1 border-b border-slate-800">
                                <span class="text-amber-300">Riesgo Medio:</span>
                                <span class="font-bold text-amber-300">{{ $jRow['medio'] }}</span>
                            </div>
                            <div class="flex items-center justify-between text-xs py-1 border-b border-slate-800">
                                <span class="text-emerald-300">Riesgo Bajo:</span>
                                <span class="font-bold text-emerald-400">{{ $jRow['bajo'] }}</span>
                            </div>
                            <div class="flex items-center justify-between text-xs pt-2">
                                <span class="text-slate-400">Índice Global:</span>
                                <span class="mono font-bold text-white">{{ $jRow['avg_global'] }}%</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
@endsection
