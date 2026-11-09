@extends('layouts.app')

@section('title', 'Expediente Contextual del Aprendiz | SGA')
@section('header_title', 'Historial Contextual Académico y Disciplinario')
@section('header_subtitle', 'Expediente integral del aprendiz limitado a la competencia asignada para toma de decisiones informada')

@section('content')
    @php $color = $apprentice->semaphoreColor(); @endphp

    <div class="mb-6 flex items-center justify-between">
        <a href="{{ url()->previous() }}" class="px-4 py-2 rounded-xl bg-slate-900 border border-slate-800 text-xs text-slate-300 hover:text-white transition">
            ← Volver al Panel
        </a>
        <span class="px-3.5 py-1.5 rounded-full text-xs font-bold border bg-{{ $color }}-500/15 text-{{ $color }}-300 border-{{ $color }}-500/30">
            {{ $apprentice->semaphoreLabel() }} · Puntaje Global {{ $apprentice->global_risk_score }}%
        </span>
    </div>

    <!-- Ficha Resumen del Aprendiz -->
    <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-6 mb-6 grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="md:col-span-2">
            <span class="mono text-xs text-indigo-400 uppercase">Aprendiz en Formación</span>
            <h2 class="text-2xl font-bold text-white mt-1">{{ $apprentice->user?->name }}</h2>
            <p class="text-sm text-slate-400 mt-1">
                {{ $apprentice->document_type }} {{ $apprentice->document_number }} · Correo: {{ $apprentice->user?->email }}
            </p>
            <div class="mt-4 flex flex-wrap gap-2 text-xs">
                <span class="px-3 py-1 rounded-lg bg-slate-950 border border-slate-800 text-slate-300">
                    Ficha: <strong class="text-indigo-300 mono">{{ $apprentice->ficha?->code }}</strong>
                </span>
                <span class="px-3 py-1 rounded-lg bg-slate-950 border border-slate-800 text-slate-300">
                    Programa: <strong class="text-white">{{ $apprentice->ficha?->trainingProgram?->name }}</strong>
                </span>
                <span class="px-3 py-1 rounded-lg bg-slate-950 border border-slate-800 text-slate-300">
                    Jornada: <strong class="text-white">{{ $apprentice->ficha?->jornada }}</strong>
                </span>
                <span class="px-3 py-1 rounded-lg bg-slate-950 border border-slate-800 text-slate-300">
                    Estado: <strong class="text-white">{{ $apprentice->academic_status }}</strong>
                </span>
            </div>
        </div>

        <div class="space-y-2 bg-slate-950 border border-slate-800 rounded-xl p-4 text-xs">
            <span class="mono text-slate-400 uppercase block mb-1">Indicadores Académicos</span>
            <div class="flex justify-between"><span>Tasa de Asistencia:</span><strong class="text-white">{{ $apprentice->attendance_rate }}%</strong></div>
            <div class="flex justify-between"><span>Inasistencias Acumuladas:</span><strong class="text-rose-400">{{ $apprentice->absences_count }}</strong></div>
            <div class="flex justify-between"><span>Promedio Académico:</span><strong class="text-emerald-400">{{ $apprentice->academic_average }} / 5.0</strong></div>
            <div class="flex justify-between"><span>Estrato Socioeconómico:</span><strong class="text-white">{{ $apprentice->socioeconomic_stratum }}</strong></div>
        </div>

        <div class="space-y-2 bg-slate-950 border border-slate-800 rounded-xl p-4 text-xs">
            <span class="mono text-slate-400 uppercase block mb-1">Vectores Predictivos</span>
            <div class="flex justify-between"><span>Riesgo Deserción:</span><strong class="text-rose-400 mono">{{ $apprentice->dropout_risk_score }}%</strong></div>
            <div class="flex justify-between"><span>Riesgo Académico:</span><strong class="text-amber-300 mono">{{ $apprentice->academic_risk_score }}%</strong></div>
            <div class="flex justify-between"><span>Riesgo Psicosocial:</span><strong class="text-violet-300 mono">{{ $apprentice->psychosocial_risk_score }}%</strong></div>
            <div class="flex justify-between pt-1 border-t border-slate-800"><span>Global Consolidado:</span><strong class="text-white mono">{{ $apprentice->global_risk_score }}%</strong></div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Línea de Tiempo de Observaciones y Soportes -->
        <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-6 space-y-4">
            <h3 class="text-base font-bold text-white">Historial Cronológico de Observaciones ({{ $apprentice->observations->count() }})</h3>

            @forelse($apprentice->observations as $obs)
                <div class="p-4 rounded-xl bg-slate-950 border border-slate-800 space-y-2">
                    <div class="flex items-center justify-between gap-2">
                        <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold {{ $obs->type === 'positiva' ? 'bg-emerald-500/15 text-emerald-300' : 'bg-amber-500/15 text-amber-300' }}">
                            {{ $obs->typeLabel() }} (Sev {{ $obs->severity }}/5)
                        </span>
                        <span class="mono text-[11px] text-slate-500">{{ $obs->created_at->format('d/m/Y H:i') }}</span>
                    </div>
                    <h4 class="text-sm font-bold text-white">{{ $obs->title }}</h4>
                    <p class="text-xs text-slate-300 leading-relaxed">{{ $obs->description }}</p>
                    <p class="text-[11px] text-slate-500">Registrado por: {{ $obs->author?->name ?? 'Sistema' }}</p>

                    @if(!$obs->escalated_to_welfare && $obs->type !== 'positiva')
                        <form method="POST" action="{{ route('instructor.observations.escalate', $obs->id) }}" class="pt-2">
                            @csrf
                            <button type="submit" class="px-3 py-1 rounded-lg bg-rose-600 hover:bg-rose-500 text-white text-xs font-semibold cursor-pointer">
                                ⚡ Escalar Caso Crítico a Bienestar
                            </button>
                        </form>
                    @endif
                </div>
            @empty
                <p class="text-xs text-slate-400">Sin observaciones registradas.</p>
            @endforelse
        </div>

        <!-- Rutas de Atención en Bienestar Asociadas -->
        <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-6 space-y-4">
            <h3 class="text-base font-bold text-white">Rutas de Atención y Compromisos con Bienestar ({{ $apprentice->welfareCases->count() }})</h3>

            @forelse($apprentice->welfareCases as $case)
                <div class="p-4 rounded-xl bg-slate-950 border border-slate-800 space-y-2 text-xs">
                    <div class="flex items-center justify-between">
                        <span class="mono font-bold text-teal-300">{{ $case->case_code }}</span>
                        <span class="px-2.5 py-0.5 rounded-full bg-slate-800 text-white font-semibold">{{ $case->statusLabel() }}</span>
                    </div>
                    <h4 class="text-sm font-bold text-white">{{ $case->title }}</h4>
                    <p class="text-slate-300"><strong>Ruta:</strong> {{ $case->intervention_route }}</p>
                    <p class="text-slate-300"><strong>Compromisos:</strong> {{ $case->commitments }}</p>
                    <p class="text-slate-300"><strong>Plan de mejora:</strong> {{ $case->improvement_plan }}</p>
                </div>
            @empty
                <p class="text-xs text-slate-400">El aprendiz no tiene casos abiertos en Bienestar actualmente.</p>
            @endforelse
        </div>
    </div>
@endsection
