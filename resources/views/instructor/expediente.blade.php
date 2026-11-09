@extends('layouts.app')

@section('title', 'Expediente del Aprendiz · SGA')
@section('header_title', 'Expediente Contextual del Aprendiz')
@section('header_subtitle', $apprentice->user?->name . ' · Ficha ' . ($apprentice->ficha?->code ?? 'N/A'))

@section('content')
    <div class="mb-5">
        <a href="{{ url()->previous() }}" class="text-sm text-indigo-400 hover:underline font-medium">← Volver</a>
    </div>

    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 mb-6 grid grid-cols-2 md:grid-cols-4 gap-4">
        <div>
            <span class="mono text-xs text-slate-400 block">Estado Académico</span>
            <strong class="text-base text-white">{{ $apprentice->academic_status }}</strong>
        </div>
        <div>
            <span class="mono text-xs text-slate-400 block">Asistencia</span>
            <strong class="text-base text-white">{{ $apprentice->attendance_rate }}%</strong>
        </div>
        <div>
            <span class="mono text-xs text-slate-400 block">Promedio</span>
            <strong class="text-base text-white">{{ $apprentice->academic_average }} / 5.0</strong>
        </div>
        <div>
            <span class="mono text-xs text-slate-400 block">Nivel de Riesgo</span>
            <strong class="text-base uppercase {{ $apprentice->risk_level === 'alto' ? 'text-rose-400' : ($apprentice->risk_level === 'medio' ? 'text-amber-300' : 'text-emerald-400') }}">
                {{ $apprentice->risk_level }} ({{ $apprentice->global_risk_score }}%)
            </strong>
        </div>
    </div>

    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6">
        <h2 class="text-base font-bold text-white mb-4">Historial de Observaciones ({{ $apprentice->observations->count() }})</h2>
        <div class="space-y-3">
            @forelse($apprentice->observations as $obs)
                <div class="p-4 rounded-xl bg-slate-950 border border-slate-800">
                    <div class="flex justify-between text-xs text-slate-400 mb-1">
                        <span class="font-semibold text-indigo-300">{{ $obs->typeLabel() }}</span>
                        <span class="mono">{{ $obs->created_at->format('d/m/Y') }}</span>
                    </div>
                    <p class="text-sm font-bold text-white">{{ $obs->title }}</p>
                    <p class="text-xs text-slate-400 mt-1">{{ $obs->description }}</p>
                </div>
            @empty
                <p class="text-sm text-slate-400">Sin observaciones registradas.</p>
            @endforelse
        </div>
    </div>
@endsection
