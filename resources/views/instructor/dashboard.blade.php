@extends('layouts.app')

@section('title', 'Instructor · SGA')
@section('header_title', 'Panel del Instructor')
@section('header_subtitle', 'Registro de novedades, escalamiento a bienestar y fichas asignadas')

@section('content')
    @if($activeTab === 'novedades')
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Formulario Simple de Novedades -->
            <div class="lg:col-span-4 bg-slate-900 border border-slate-800 rounded-2xl p-6 h-fit">
                <h2 class="text-base font-bold text-white mb-4">Registrar Observación / Inasistencia</h2>

                <form method="POST" action="{{ route('instructor.observations.store') }}" class="space-y-3.5 text-sm">
                    @csrf
                    <div class="grid grid-cols-2 gap-2">
                        <label class="cursor-pointer">
                            <input type="radio" name="target_mode" value="single" checked onchange="toggleMode('single')" class="peer sr-only">
                            <span class="block text-center py-2 rounded-xl bg-slate-950 border border-slate-800 text-xs font-semibold text-slate-400 peer-checked:bg-indigo-600 peer-checked:text-white peer-checked:border-indigo-500">
                                Individual
                            </span>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="target_mode" value="ficha_massive" onchange="toggleMode('ficha_massive')" class="peer sr-only">
                            <span class="block text-center py-2 rounded-xl bg-slate-950 border border-slate-800 text-xs font-semibold text-slate-400 peer-checked:bg-indigo-600 peer-checked:text-white peer-checked:border-indigo-500">
                                Masivo por Ficha
                            </span>
                        </label>
                    </div>

                    <div id="mode-single">
                        <label class="block text-xs text-slate-400 mb-1">Aprendiz</label>
                        <select name="apprentice_profile_id" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white">
                            @foreach($apprentices as $apr)
                                <option value="{{ $apr->id }}">[{{ $apr->ficha?->code }}] {{ $apr->user?->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div id="mode-massive" class="hidden">
                        <label class="block text-xs text-slate-400 mb-1">Ficha completa</label>
                        <select name="ficha_id" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white">
                            @foreach($assignedFichas as $ficha)
                                <option value="{{ $ficha->id }}">Ficha {{ $ficha->code }} ({{ $ficha->apprentices->count() }} aprendices)</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-xs text-slate-400 mb-1">Tipo</label>
                            <select name="type" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white">
                                <option value="academica">Académica</option>
                                <option value="inasistencia">Inasistencia</option>
                                <option value="llamado_atencion">Llamado de Atención</option>
                                <option value="disciplinaria">Disciplinaria</option>
                                <option value="positiva">Positiva</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs text-slate-400 mb-1">Severidad (1-5)</label>
                            <select name="severity" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white">
                                <option value="1">1 - Leve</option>
                                <option value="2">2 - Baja</option>
                                <option value="3" selected>3 - Media</option>
                                <option value="4">4 - Alta</option>
                                <option value="5">5 - Crítica</option>
                            </select>
                        </div>
                    </div>

                    <input type="hidden" name="category" value="academico">

                    <div>
                        <label class="block text-xs text-slate-400 mb-1">Asunto</label>
                        <input name="title" required placeholder="Resumen breve" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white">
                    </div>

                    <div>
                        <label class="block text-xs text-slate-400 mb-1">Detalle</label>
                        <textarea name="description" rows="3" required placeholder="Describe la observación..." class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white"></textarea>
                    </div>

                    <label class="flex items-center gap-2 p-3 rounded-xl bg-rose-500/10 border border-rose-500/30 text-xs text-rose-300 font-medium cursor-pointer">
                        <input type="checkbox" name="escalate_immediately" value="1" class="rounded accent-rose-500">
                        <span>Derivar inmediatamente al equipo de Bienestar</span>
                    </label>

                    <button type="submit" class="w-full py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-semibold transition cursor-pointer">
                        Guardar Observación
                    </button>
                </form>
            </div>

            <!-- Listado de Observaciones y Escalamiento en 1 clic -->
            <div class="lg:col-span-8 bg-slate-900 border border-slate-800 rounded-2xl p-6">
                <h2 class="text-base font-bold text-white mb-4">Observaciones Recientes</h2>

                <div class="space-y-3">
                    @foreach($recentObservations as $obs)
                        <div class="p-4 rounded-xl bg-slate-950 border border-slate-800 flex flex-col gap-2">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <div>
                                    <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full {{ $obs->type === 'positiva' ? 'bg-emerald-500/15 text-emerald-300' : 'bg-amber-500/15 text-amber-300' }}">
                                        {{ $obs->typeLabel() }}
                                    </span>
                                    <a href="{{ route('instructor.expediente', $obs->apprentice_profile_id) }}" class="ml-2 text-sm font-bold text-white hover:text-indigo-400">
                                        {{ $obs->apprenticeProfile?->user?->name }}
                                    </a>
                                    <span class="mono text-xs text-slate-500 ml-1">· Ficha {{ $obs->ficha?->code }}</span>
                                </div>

                                @if($obs->escalated_to_welfare)
                                    <span class="text-xs font-semibold text-teal-300 bg-teal-500/15 px-2.5 py-1 rounded-lg">Escalado a Bienestar</span>
                                @elseif($obs->type !== 'positiva')
                                    <form method="POST" action="{{ route('instructor.observations.escalate', $obs->id) }}">
                                        @csrf
                                        <button type="submit" class="px-3 py-1 rounded-lg bg-rose-500/15 text-rose-300 border border-rose-500/30 hover:bg-rose-500/25 text-xs font-semibold cursor-pointer">
                                            Escalar a Bienestar
                                        </button>
                                    </form>
                                @endif
                            </div>

                            <p class="text-sm font-medium text-slate-200">{{ $obs->title }}</p>
                            <p class="text-xs text-slate-400">{{ $obs->description }}</p>

                            <!-- Soportes cargados por el aprendiz -->
                            @foreach($obs->supports as $sup)
                                <div class="mt-2 p-3 rounded-lg bg-slate-900 border border-slate-800 flex items-center justify-between gap-3 text-xs">
                                    <div>
                                        <span class="font-semibold text-white">Soporte: {{ $sup->title }}</span>
                                        <span class="text-slate-400 block">{{ $sup->description }}</span>
                                    </div>
                                    @if($sup->status === 'pendiente')
                                        <div class="flex gap-1.5">
                                            <form method="POST" action="{{ route('instructor.supports.review', $sup->id) }}">
                                                @csrf
                                                <input type="hidden" name="status" value="aprobado">
                                                <button type="submit" class="px-2.5 py-1 rounded bg-emerald-600 text-white font-medium cursor-pointer">Aprobar</button>
                                            </form>
                                            <form method="POST" action="{{ route('instructor.supports.review', $sup->id) }}">
                                                @csrf
                                                <input type="hidden" name="status" value="rechazado">
                                                <button type="submit" class="px-2.5 py-1 rounded bg-slate-800 text-slate-300 cursor-pointer">Rechazar</button>
                                            </form>
                                        </div>
                                    @else
                                        <span class="mono font-semibold uppercase {{ $sup->status === 'aprobado' ? 'text-emerald-400' : 'text-rose-400' }}">{{ $sup->status }}</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    @if($activeTab === 'asignaciones')
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            @foreach($assignedFichas as $ficha)
                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6">
                    <div class="flex items-center justify-between mb-2">
                        <span class="mono text-xs font-bold text-indigo-400 uppercase">Ficha {{ $ficha->code }}</span>
                        <span class="text-xs text-slate-400">{{ $ficha->jornada }}</span>
                    </div>
                    <h3 class="text-base font-bold text-white mb-1">{{ $ficha->trainingProgram?->name }}</h3>
                    <p class="text-xs text-slate-400 mb-4">{{ $ficha->pivot->schedule }} ({{ $ficha->pivot->hours_assigned }}h)</p>

                    <div class="border-t border-slate-800 pt-3 space-y-2">
                        <p class="mono text-xs text-slate-500 uppercase">Aprendices ({{ $ficha->apprentices->count() }})</p>
                        @foreach($ficha->apprentices as $student)
                            <div class="flex items-center justify-between text-xs py-1">
                                <span class="text-slate-200 font-medium">{{ $student->user?->name }}</span>
                                <a href="{{ route('instructor.expediente', $student->id) }}" class="text-indigo-400 hover:underline font-medium">
                                    Ver expediente →
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <script>
        function toggleMode(mode) {
            document.getElementById('mode-single').classList.toggle('hidden', mode !== 'single');
            document.getElementById('mode-massive').classList.toggle('hidden', mode !== 'ficha_massive');
        }
    </script>
@endsection
