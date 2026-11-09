@extends('layouts.app')

@section('title', 'Bienestar · SGA')
@section('header_title', 'Coordinación de Bienestar')
@section('header_subtitle', 'Monitoreo de riesgo, seguimiento de casos y reportes')

@section('content')
    <!-- Resumen Rápido del Semáforo (4 tarjetas limpias) -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5">
            <span class="mono text-xs text-slate-400 uppercase">Total Aprendices</span>
            <p class="text-3xl font-bold text-white mt-1">{{ $stats['total'] }}</p>
        </div>

        <div class="bg-rose-500/10 border border-rose-500/30 rounded-2xl p-5">
            <div class="flex items-center justify-between">
                <span class="mono text-xs text-rose-300 uppercase">Riesgo Alto</span>
                <span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span>
            </div>
            <p class="text-3xl font-bold text-rose-400 mt-1">{{ $stats['alto'] }}</p>
        </div>

        <div class="bg-amber-500/10 border border-amber-500/30 rounded-2xl p-5">
            <div class="flex items-center justify-between">
                <span class="mono text-xs text-amber-300 uppercase">Riesgo Medio</span>
                <span class="w-2.5 h-2.5 rounded-full bg-amber-400"></span>
            </div>
            <p class="text-3xl font-bold text-amber-300 mt-1">{{ $stats['medio'] }}</p>
        </div>

        <div class="bg-emerald-500/10 border border-emerald-500/30 rounded-2xl p-5">
            <div class="flex items-center justify-between">
                <span class="mono text-xs text-emerald-300 uppercase">Riesgo Bajo</span>
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-400"></span>
            </div>
            <p class="text-3xl font-bold text-emerald-400 mt-1">{{ $stats['bajo'] }}</p>
        </div>
    </div>

    <!-- ================================================================= -->
    <!-- PESTAÑA 1: MONITOREO Y SEMÁFORO -->
    <!-- ================================================================= -->
    @if($activeTab === 'monitoreo')
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6">
            <!-- Filtros Simples -->
            <form method="GET" action="{{ route('bienestar.dashboard') }}" class="grid grid-cols-1 sm:grid-cols-5 gap-3 mb-6">
                <input type="hidden" name="tab" value="monitoreo">
                <input name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Buscar aprendiz..."
                       class="px-3.5 py-2 rounded-xl bg-slate-950 border border-slate-800 text-sm text-white placeholder-slate-500">

                <select name="ficha_id" class="px-3.5 py-2 rounded-xl bg-slate-950 border border-slate-800 text-sm text-white">
                    <option value="">Todas las fichas</option>
                    @foreach($fichas as $f)
                        <option value="{{ $f->id }}" @selected(($filters['ficha_id'] ?? '') == $f->id)>Ficha {{ $f->code }}</option>
                    @endforeach
                </select>

                <select name="jornada" class="px-3.5 py-2 rounded-xl bg-slate-950 border border-slate-800 text-sm text-white">
                    <option value="">Todas las jornadas</option>
                    @foreach(['Mañana', 'Tarde', 'Noche', 'Fines de Semana'] as $j)
                        <option value="{{ $j }}" @selected(($filters['jornada'] ?? '') === $j)>{{ $j }}</option>
                    @endforeach
                </select>

                <select name="risk_level" class="px-3.5 py-2 rounded-xl bg-slate-950 border border-slate-800 text-sm text-white">
                    <option value="">Todos los riesgos</option>
                    <option value="alto" @selected(($filters['risk_level'] ?? '') === 'alto')>Riesgo Alto</option>
                    <option value="medio" @selected(($filters['risk_level'] ?? '') === 'medio')>Riesgo Medio</option>
                    <option value="bajo" @selected(($filters['risk_level'] ?? '') === 'bajo')>Riesgo Bajo</option>
                </select>

                <button type="submit" class="py-2 px-4 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-medium transition cursor-pointer">
                    Filtrar
                </button>
            </form>

            <!-- Tabla Clara de Semaforización -->
            <div class="overflow-x-auto rounded-xl border border-slate-800">
                <table class="w-full text-sm text-left">
                    <thead class="bg-slate-950 text-slate-400 text-xs uppercase mono">
                        <tr>
                            <th class="px-4 py-3">Aprendiz</th>
                            <th class="px-4 py-3">Ficha / Programa</th>
                            <th class="px-4 py-3 text-center">Deserción</th>
                            <th class="px-4 py-3 text-center">Académico</th>
                            <th class="px-4 py-3 text-center">Psicosocial</th>
                            <th class="px-4 py-3 text-center">Semáforo</th>
                            <th class="px-4 py-3 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800">
                        @foreach($apprentices as $a)
                            <tr class="hover:bg-slate-800/40">
                                <td class="px-4 py-3.5">
                                    <p class="font-semibold text-white">{{ $a->user?->name }}</p>
                                    <p class="text-xs text-slate-400">{{ $a->document_type }} {{ $a->document_number }}</p>
                                </td>
                                <td class="px-4 py-3.5">
                                    <p class="font-medium text-indigo-300">Ficha {{ $a->ficha?->code }} ({{ $a->ficha?->jornada }})</p>
                                    <p class="text-xs text-slate-400">{{ $a->ficha?->trainingProgram?->name }}</p>
                                </td>
                                <td class="px-4 py-3.5 text-center mono text-slate-300">{{ $a->dropout_risk_score }}%</td>
                                <td class="px-4 py-3.5 text-center mono text-slate-300">{{ $a->academic_risk_score }}%</td>
                                <td class="px-4 py-3.5 text-center mono text-slate-300">{{ $a->psychosocial_risk_score }}%</td>
                                <td class="px-4 py-3.5 text-center">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold
                                        @if($a->risk_level === 'alto') bg-rose-500/15 text-rose-300 border border-rose-500/30
                                        @elseif($a->risk_level === 'medio') bg-amber-500/15 text-amber-300 border border-amber-500/30
                                        @else bg-emerald-500/15 text-emerald-300 border border-emerald-500/30 @endif">
                                        {{ strtoupper($a->risk_level) }} ({{ $a->global_risk_score }}%)
                                    </span>
                                </td>
                                <td class="px-4 py-3.5 text-right whitespace-nowrap">
                                    <a href="{{ route('instructor.expediente', $a->id) }}" class="text-xs font-medium text-slate-400 hover:text-white mr-3">
                                        Expediente
                                    </a>
                                    <a href="{{ route('bienestar.dashboard', ['tab' => 'casos', 'open_for' => $a->id]) }}"
                                       class="px-3 py-1.5 rounded-lg bg-indigo-600/20 text-indigo-300 border border-indigo-500/30 hover:bg-indigo-600 hover:text-white text-xs font-semibold transition">
                                        + Caso
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <!-- ================================================================= -->
    <!-- PESTAÑA 2: VARIABLES DEL MODELO -->
    <!-- ================================================================= -->
    @if($activeTab === 'variables')
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6">
            <div class="mb-6">
                <h2 class="text-lg font-bold text-white">Variables y Pesos del Modelo</h2>
                <p class="text-sm text-slate-400">Ajusta el impacto de cada factor y recalcula automáticamente el semáforo de todos los aprendices.</p>
            </div>

            <form method="POST" action="{{ route('bienestar.parameters.update') }}">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    @foreach($parameters as $param)
                        <div class="p-4 rounded-xl border border-slate-800 bg-slate-950 flex flex-col justify-between">
                            <div>
                                <span class="mono text-[11px] uppercase text-indigo-400">{{ $param->dimension }}</span>
                                <h3 class="text-sm font-bold text-white mt-0.5">{{ $param->name }}</h3>
                                <p class="text-xs text-slate-400 mt-1 mb-4">{{ $param->description }}</p>
                            </div>
                            <div class="flex items-center justify-between gap-3 pt-3 border-t border-slate-800">
                                <span class="mono text-xs text-slate-500">{{ $param->min_value }} – {{ $param->max_value }}</span>
                                <input type="number"
                                       name="weights[{{ $param->id }}]"
                                       min="{{ $param->min_value }}"
                                       max="{{ $param->max_value }}"
                                       step="0.1"
                                       value="{{ $param->weight }}"
                                       class="w-24 px-3 py-1.5 rounded-lg border border-slate-700 bg-slate-900 text-white text-center mono font-semibold text-sm">
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold transition cursor-pointer">
                        Guardar y Recalcular Riesgos
                    </button>
                </div>
            </form>
        </div>
    @endif

    <!-- ================================================================= -->
    <!-- PESTAÑA 3: TRAZABILIDAD DE CASOS -->
    <!-- ================================================================= -->
    @if($activeTab === 'casos')
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Nuevo Caso -->
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 h-fit">
                <h2 class="text-base font-bold text-white mb-4">Abrir Caso de Bienestar</h2>
                <form method="POST" action="{{ route('bienestar.cases.store') }}" class="space-y-3 text-sm">
                    @csrf
                    <div>
                        <label class="block text-xs text-slate-400 mb-1">Aprendiz</label>
                        <select name="apprentice_profile_id" required class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white">
                            @foreach($apprentices as $apr)
                                <option value="{{ $apr->id }}" @selected(request('open_for') == $apr->id)>
                                    {{ $apr->user?->name }} ({{ strtoupper($apr->risk_level) }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-xs text-slate-400 mb-1">Tipo</label>
                            <select name="risk_type" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white">
                                <option value="integral">Integral</option>
                                <option value="desercion">Deserción</option>
                                <option value="academico">Académico</option>
                                <option value="psicosocial">Psicosocial</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs text-slate-400 mb-1">Prioridad</label>
                            <select name="priority" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white">
                                <option value="critica">Crítica</option>
                                <option value="alta" selected>Alta</option>
                                <option value="media">Media</option>
                                <option value="baja">Baja</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs text-slate-400 mb-1">Asunto</label>
                        <input name="title" required placeholder="Título del caso" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white">
                    </div>

                    <div>
                        <label class="block text-xs text-slate-400 mb-1">Diagnóstico inicial</label>
                        <textarea name="initial_diagnosis" rows="2" required class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white"></textarea>
                    </div>

                    <div>
                        <label class="block text-xs text-slate-400 mb-1">Ruta de atención y compromisos</label>
                        <textarea name="commitments" rows="2" placeholder="Acuerdos con el aprendiz..." class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white"></textarea>
                    </div>

                    <button type="submit" class="w-full py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-semibold transition cursor-pointer">
                        Registrar Caso
                    </button>
                </form>
            </div>

            <!-- Listado de Casos -->
            <div class="lg:col-span-2 space-y-4">
                @foreach($cases as $case)
                    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5">
                        <div class="flex flex-wrap items-start justify-between gap-3 mb-3">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="mono text-xs font-bold text-indigo-400">{{ $case->case_code }}</span>
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-medium {{ $case->status === 'resuelto' ? 'bg-emerald-500/15 text-emerald-300 border border-emerald-500/30' : 'bg-amber-500/15 text-amber-300 border border-amber-500/30' }}">
                                        {{ $case->statusLabel() }}
                                    </span>
                                </div>
                                <h3 class="text-base font-bold text-white mt-1">{{ $case->title }}</h3>
                                <p class="text-xs text-slate-400">Aprendiz: {{ $case->apprenticeProfile?->user?->name }}</p>
                            </div>

                            <!-- Eliminación bloqueada hasta que esté resuelto -->
                            <form method="POST" action="{{ route('bienestar.cases.destroy', $case->id) }}">
                                @csrf
                                @method('DELETE')
                                @if($case->canBeDeleted())
                                    <button type="submit" class="px-3 py-1.5 rounded-lg bg-rose-500/15 text-rose-300 border border-rose-500/30 hover:bg-rose-500/25 text-xs font-medium cursor-pointer">
                                        Eliminar
                                    </button>
                                @else
                                    <span class="px-3 py-1.5 rounded-lg bg-slate-950 border border-slate-800 text-slate-500 text-xs">
                                        Bloqueado hasta resolución
                                    </span>
                                @endif
                            </form>
                        </div>

                        <p class="text-xs text-slate-300 mb-3"><strong>Compromisos:</strong> {{ $case->commitments ?: $case->initial_diagnosis }}</p>

                        <details class="pt-3 border-t border-slate-800 text-xs">
                            <summary class="font-semibold text-indigo-400 cursor-pointer">Actualizar estado o agregar seguimiento</summary>
                            <form method="POST" action="{{ route('bienestar.cases.update', $case->id) }}" class="mt-3 grid grid-cols-1 sm:grid-cols-3 gap-2">
                                @csrf
                                @method('PUT')
                                <select name="status" class="px-3 py-2 rounded-lg bg-slate-950 border border-slate-800 text-white">
                                    @foreach(['abierto' => 'Abierto', 'en_ruta' => 'En Ruta', 'compromiso_activo' => 'Compromiso Activo', 'en_seguimiento' => 'En Seguimiento', 'resuelto' => 'Resuelto'] as $k => $v)
                                        <option value="{{ $k }}" @selected($case->status === $k)>{{ $v }}</option>
                                    @endforeach
                                </select>
                                <input name="followup_notes" placeholder="Nota de avance..." class="px-3 py-2 rounded-lg bg-slate-950 border border-slate-800 text-white">
                                <button type="submit" class="py-2 px-4 rounded-lg bg-indigo-600 text-white font-medium cursor-pointer">Guardar</button>
                            </form>
                        </details>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- ================================================================= -->
    <!-- PESTAÑA 4: REPORTES MASIVOS -->
    <!-- ================================================================= -->
    @if($activeTab === 'reportes')
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6">
            <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
                <div>
                    <h2 class="text-lg font-bold text-white">Reporte Consolidado por Ficha y Jornada</h2>
                    <p class="text-sm text-slate-400">Vulnerabilidad agrupada para toma de decisiones institucionales.</p>
                </div>
                <a href="{{ route('bienestar.reports.export', $filters) }}"
                   class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-sm font-semibold transition">
                    Descargar Excel (CSV)
                </a>
            </div>

            <div class="overflow-x-auto rounded-xl border border-slate-800">
                <table class="w-full text-sm text-left">
                    <thead class="bg-slate-950 text-slate-400 text-xs uppercase mono">
                        <tr>
                            <th class="px-4 py-3">Ficha</th>
                            <th class="px-4 py-3">Programa</th>
                            <th class="px-4 py-3">Jornada</th>
                            <th class="px-4 py-3 text-center">Total</th>
                            <th class="px-4 py-3 text-center text-rose-400">Alto</th>
                            <th class="px-4 py-3 text-center text-amber-300">Medio</th>
                            <th class="px-4 py-3 text-center text-emerald-400">Bajo</th>
                            <th class="px-4 py-3 text-right">Riesgo Promedio</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800">
                        @foreach($reportData['by_ficha'] as $row)
                            <tr class="hover:bg-slate-800/40">
                                <td class="px-4 py-3.5 mono font-bold text-indigo-300">{{ $row['ficha_code'] }}</td>
                                <td class="px-4 py-3.5 text-white">{{ $row['program'] }}</td>
                                <td class="px-4 py-3.5 text-slate-400">{{ $row['jornada'] }}</td>
                                <td class="px-4 py-3.5 text-center font-semibold">{{ $row['total'] }}</td>
                                <td class="px-4 py-3.5 text-center font-bold text-rose-400">{{ $row['alto'] }}</td>
                                <td class="px-4 py-3.5 text-center font-bold text-amber-300">{{ $row['medio'] }}</td>
                                <td class="px-4 py-3.5 text-center font-bold text-emerald-400">{{ $row['bajo'] }}</td>
                                <td class="px-4 py-3.5 text-right mono font-bold text-white">{{ $row['avg_global'] }}%</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
@endsection
