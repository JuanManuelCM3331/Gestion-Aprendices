@extends('layouts.app')

@section('title', 'Autogestión y Transparencia · Portal del Aprendiz | SGA')
@section('header_title', 'Autogestión y Transparencia · Portal del Aprendiz')
@section('header_subtitle', 'Visibilidad transparente de tu semáforo de riesgo, historial cronológico de observaciones, carga de soportes y centro de alertas')

@section('content')
    <!-- Pestañas del Aprendiz -->
    <div class="flex flex-wrap items-center gap-2 mb-8 border-b border-slate-800 pb-4">
        <a href="{{ route('aprendiz.dashboard', ['tab' => 'estado']) }}"
           class="px-4 py-2.5 rounded-xl text-sm font-semibold transition {{ $activeTab === 'estado' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/25' : 'bg-slate-900 text-slate-300 border border-slate-800 hover:text-white' }}">
            1. Visibilidad de Estado & Semáforo
        </a>
        <a href="{{ route('aprendiz.dashboard', ['tab' => 'soportes']) }}"
           class="px-4 py-2.5 rounded-xl text-sm font-semibold transition {{ $activeTab === 'soportes' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/25' : 'bg-slate-900 text-slate-300 border border-slate-800 hover:text-white' }}">
            2. Historial de Observaciones & Carga de Soportes ({{ $observations->count() }})
        </a>
        <a href="{{ route('aprendiz.dashboard', ['tab' => 'notificaciones']) }}"
           class="px-4 py-2.5 rounded-xl text-sm font-semibold transition flex items-center gap-2 {{ $activeTab === 'notificaciones' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/25' : 'bg-slate-900 text-slate-300 border border-slate-800 hover:text-white' }}">
            <span>3. Mis Alertas y Citaciones</span>
            @if($unreadCount > 0)
                <span class="px-2 py-0.5 rounded-full bg-rose-500 text-white text-xs font-bold">{{ $unreadCount }}</span>
            @endif
        </a>
    </div>

    @if(!$profile)
        <div class="p-8 rounded-2xl bg-slate-900 border border-slate-800 text-center">
            <h3 class="text-lg font-bold text-white">Vista de Supervisión</h3>
            <p class="text-sm text-slate-400 mt-1">Inicia sesión con un perfil de Aprendiz (ej: <code class="mono text-amber-300">aprendiz1 / 123</code>) para visualizar su expediente de autogestión.</p>
        </div>
    @else
        @php $color = $profile->semaphoreColor(); @endphp

        <!-- Encabezado Transparente de Estado Institucional -->
        <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-6 mb-8 grid grid-cols-1 lg:grid-cols-12 gap-6 items-center">
            <div class="lg:col-span-5 space-y-2">
                <div class="flex items-center gap-2.5">
                    <span class="w-3.5 h-3.5 rounded-full bg-{{ $color }}-500 animate-pulse"></span>
                    <span class="mono text-xs uppercase text-{{ $color }}-300 font-bold">{{ $profile->semaphoreLabel() }}</span>
                </div>
                <h2 class="text-2xl font-bold text-white">{{ auth()->user()->name }}</h2>
                <p class="text-xs text-slate-400">
                    {{ $profile->document_type }} {{ $profile->document_number }} ·
                    Ficha <strong class="mono text-indigo-300">{{ $profile->ficha?->code }}</strong> ({{ $profile->ficha?->jornada }}) ·
                    {{ $profile->ficha?->trainingProgram?->name }}
                </p>
                <div class="pt-2 flex flex-wrap gap-2 text-xs">
                    <span class="px-3 py-1 rounded-xl bg-slate-950 border border-slate-800 text-slate-300">
                        Estado Institucional: <strong class="text-white">{{ $profile->academic_status }}</strong>
                    </span>
                    <span class="px-3 py-1 rounded-xl bg-slate-950 border border-slate-800 text-slate-300">
                        Asistencia: <strong class="text-emerald-400">{{ $profile->attendance_rate }}%</strong>
                    </span>
                    <span class="px-3 py-1 rounded-xl bg-slate-950 border border-slate-800 text-slate-300">
                        Promedio: <strong class="text-indigo-300">{{ $profile->academic_average }} / 5.0</strong>
                    </span>
                </div>
            </div>

            <!-- Desglose Visual de los 3 Vectores de Riesgo -->
            <div class="lg:col-span-7 grid grid-cols-1 sm:grid-cols-4 gap-3">
                <div class="p-4 rounded-xl bg-slate-950 border border-slate-800 text-center">
                    <span class="mono text-[10px] text-slate-400 uppercase block">Deserción</span>
                    <span class="text-2xl font-extrabold text-rose-400 mt-1 block">{{ $profile->dropout_risk_score }}%</span>
                </div>
                <div class="p-4 rounded-xl bg-slate-950 border border-slate-800 text-center">
                    <span class="mono text-[10px] text-slate-400 uppercase block">Académico</span>
                    <span class="text-2xl font-extrabold text-amber-300 mt-1 block">{{ $profile->academic_risk_score }}%</span>
                </div>
                <div class="p-4 rounded-xl bg-slate-950 border border-slate-800 text-center">
                    <span class="mono text-[10px] text-slate-400 uppercase block">Psicosocial</span>
                    <span class="text-2xl font-extrabold text-violet-300 mt-1 block">{{ $profile->psychosocial_risk_score }}%</span>
                </div>
                <div class="p-4 rounded-xl bg-{{ $color }}-500/10 border border-{{ $color }}-500/30 text-center">
                    <span class="mono text-[10px] text-{{ $color }}-300 uppercase block">Global</span>
                    <span class="text-2xl font-extrabold text-{{ $color }}-400 mt-1 block">{{ $profile->global_risk_score }}%</span>
                </div>
            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- PESTAÑA 1: VISIBILIDAD DE ESTADO Y RUTAS DE BIENESTAR -->
        <!-- ========================================================================= -->
        @if($activeTab === 'estado')
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Mis Compromisos y Rutas de Atención en Bienestar -->
                <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-6 space-y-4">
                    <div>
                        <span class="mono text-xs text-teal-400 uppercase tracking-widest">Acompañamiento Institucional</span>
                        <h3 class="text-lg font-bold text-white mt-1">Mis Rutas de Atención, Compromisos y Planes de Mejora</h3>
                    </div>

                    @forelse($welfareCases as $case)
                        <div class="p-4 rounded-2xl bg-slate-950 border border-slate-800 space-y-2.5 text-xs">
                            <div class="flex items-center justify-between">
                                <span class="mono font-bold text-teal-300">{{ $case->case_code }}</span>
                                <span class="px-2.5 py-0.5 rounded-full bg-teal-500/15 text-teal-300 border border-teal-500/30 font-semibold">
                                    {{ $case->statusLabel() }}
                                </span>
                            </div>
                            <h4 class="text-sm font-bold text-white">{{ $case->title }}</h4>
                            <p class="text-slate-300"><strong>Ruta de Atención:</strong> {{ $case->intervention_route }}</p>
                            <p class="text-slate-300"><strong>Mis Compromisos:</strong> {{ $case->commitments }}</p>
                            <p class="text-slate-300"><strong>Plan de Mejora:</strong> {{ $case->improvement_plan }}</p>
                            @if($case->due_date)
                                <p class="mono text-[11px] text-amber-300">Fecha de revisión: {{ $case->due_date->format('d/m/Y') }}</p>
                            @endif
                        </div>
                    @empty
                        <p class="text-xs text-slate-400">No tienes rutas de intervención activas en este momento.</p>
                    @endforelse
                </div>

                <!-- Resumen Rápido de Observaciones Recientes -->
                <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-6 space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="mono text-xs text-indigo-400 uppercase tracking-widest">Transparencia Académica</span>
                            <h3 class="text-lg font-bold text-white mt-1">Últimas Observaciones Registradas</h3>
                        </div>
                        <a href="{{ route('aprendiz.dashboard', ['tab' => 'soportes']) }}" class="text-xs text-indigo-400 hover:underline">
                            Cargar Soportes / Excusas →
                        </a>
                    </div>

                    @foreach($observations->take(4) as $obs)
                        <div class="p-4 rounded-2xl bg-slate-950 border border-slate-800 space-y-1.5">
                            <div class="flex items-center justify-between text-xs">
                                <span class="font-bold {{ $obs->type === 'positiva' ? 'text-emerald-400' : 'text-amber-300' }}">
                                    {{ $obs->type === 'positiva' ? '★ ' : '• ' }}{{ $obs->typeLabel() }}
                                </span>
                                <span class="mono text-[11px] text-slate-500">{{ $obs->created_at->format('d/m/Y') }}</span>
                            </div>
                            <h4 class="text-sm font-bold text-white">{{ $obs->title }}</h4>
                            <p class="text-xs text-slate-400">{{ $obs->description }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- ========================================================================= -->
        <!-- PESTAÑA 2: HISTORIAL CRONOLÓGICO Y GESTIÓN DE SOPORTES / EVIDENCIAS -->
        <!-- ========================================================================= -->
        @if($activeTab === 'soportes')
            <div class="space-y-5">
                <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-6">
                    <span class="mono text-xs text-amber-400 uppercase tracking-widest">Expediente Transparente y Derecho al Debido Proceso</span>
                    <h3 class="text-xl font-bold text-white mt-1">Historial Cronológico de Observaciones y Carga de Soportes</h3>
                    <p class="text-sm text-slate-400 mt-1">
                        Consulta todas tus observaciones positivas o negativas. Puedes vincular excusas médicas, descargos o documentos soporte directamente a cada observación; cuando tu instructor o Bienestar lo apruebe, el algoritmo mitigará automáticamente el impacto en tu riesgo.
                    </p>
                </div>

                @foreach($observations as $obs)
                    <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-6 space-y-4">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="px-3 py-0.5 rounded-full text-xs font-bold
                                        @if($obs->type === 'positiva') bg-emerald-500/15 text-emerald-300 border border-emerald-500/30
                                        @elseif($obs->severity >= 4) bg-rose-500/15 text-rose-300 border border-rose-500/30
                                        @else bg-amber-500/15 text-amber-300 border border-amber-500/30 @endif">
                                        {{ $obs->typeLabel() }} · Impacto {{ $obs->severity }}/5
                                    </span>
                                    @if($obs->mitigated_by_support)
                                        <span class="px-2.5 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 text-xs font-semibold">
                                            ✓ Mitigada por Soporte Aprobado
                                        </span>
                                    @endif
                                </div>
                                <h4 class="text-base font-bold text-white mt-2">{{ $obs->title }}</h4>
                                <p class="text-xs text-slate-400">
                                    Registrado por: <strong class="text-slate-200">{{ $obs->author?->name ?? 'Sistema' }}</strong> ·
                                    Fecha: {{ $obs->created_at->format('d/m/Y H:i') }}
                                </p>
                            </div>
                        </div>

                        <p class="text-sm text-slate-300 leading-relaxed">{{ $obs->description }}</p>

                        <!-- Soportes ya vinculados a esta observación -->
                        @if($obs->supports->count() > 0)
                            <div class="space-y-2 pt-3 border-t border-slate-800">
                                <span class="mono text-[11px] text-indigo-300 uppercase">Mis Soportes y Evidencias Vinculadas ({{ $obs->supports->count() }})</span>
                                @foreach($obs->supports as $sup)
                                    <div class="p-3.5 rounded-xl bg-slate-950 border border-slate-800 flex flex-wrap items-center justify-between gap-3 text-xs">
                                        <div>
                                            <span class="font-bold text-white">[{{ $sup->support_type }}] {{ $sup->title }}</span>
                                            <p class="text-slate-400 mt-0.5">{{ $sup->description }}</p>
                                            @if($sup->file_path)
                                                <a href="{{ route('supports.download', $sup->id) }}" class="text-indigo-400 hover:underline inline-block mt-1">
                                                    📎 Archivo adjunto: {{ $sup->file_name }}
                                                </a>
                                            @endif
                                        </div>
                                        <span class="mono px-3 py-1 rounded-full font-bold
                                            @if($sup->status === 'aprobado') bg-emerald-500/15 text-emerald-300
                                            @elseif($sup->status === 'rechazado') bg-rose-500/15 text-rose-300
                                            @else bg-amber-500/15 text-amber-300 @endif">
                                            {{ strtoupper($sup->status) }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <!-- Formulario de Carga de Soporte / Evidencia -->
                        @if($obs->type !== 'positiva')
                            <details class="pt-3 border-t border-slate-800">
                                <summary class="text-xs font-semibold text-amber-300 hover:text-amber-200 cursor-pointer">
                                    📎 + Cargar Excusa Médica, Evidencia o Descargo para esta Observación
                                </summary>

                                <form method="POST"
                                      action="{{ route('aprendiz.supports.store', $obs->id) }}"
                                      enctype="multipart/form-data"
                                      class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-3 text-xs bg-slate-950 p-4 rounded-xl border border-slate-800">
                                    @csrf
                                    <div>
                                        <label class="block text-slate-400 mb-1">Título del Soporte</label>
                                        <input name="title" required placeholder="Ej: Incapacidad Médica EPS / Descargo"
                                               class="w-full px-3 py-2 rounded-xl bg-slate-900 border border-slate-800 text-white">
                                    </div>
                                    <div>
                                        <label class="block text-slate-400 mb-1">Tipo de Documento</label>
                                        <select name="support_type" class="w-full px-3 py-2 rounded-xl bg-slate-900 border border-slate-800 text-white">
                                            <option value="Excusa Médica">Excusa Médica</option>
                                            <option value="Descargo a Llamado de Atención">Descargo a Llamado de Atención</option>
                                            <option value="Soporte Calamidad Doméstica">Soporte Calamidad Doméstica</option>
                                            <option value="Certificado Laboral/Institucional">Certificado Laboral/Institucional</option>
                                            <option value="Otro">Otro</option>
                                        </select>
                                    </div>
                                    <div class="md:col-span-2">
                                        <label class="block text-slate-400 mb-1">Explicación o Respuesta del Aprendiz</label>
                                        <textarea name="description" rows="2" required placeholder="Describe la justificación o el contexto del soporte..."
                                                  class="w-full px-3 py-2 rounded-xl bg-slate-900 border border-slate-800 text-white"></textarea>
                                    </div>
                                    <div>
                                        <label class="block text-slate-400 mb-1">Adjuntar Archivo (PDF, JPG, PNG, DOCX, TXT · Máx 5MB)</label>
                                        <input type="file" name="attachment"
                                               class="w-full text-slate-300 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-indigo-600 file:text-white hover:file:bg-indigo-500">
                                    </div>
                                    <div class="flex items-end justify-end">
                                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-semibold cursor-pointer">
                                            Vincular Soporte a mi Expediente →
                                        </button>
                                    </div>
                                </form>
                            </details>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif

        <!-- ========================================================================= -->
        <!-- PESTAÑA 3: CENTRO DE NOTIFICACIONES Y ALERTAS DEL APRENDIZ -->
        <!-- ========================================================================= -->
        @if($activeTab === 'notificaciones')
            <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-6 space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="mono text-xs text-rose-400 uppercase tracking-widest">Alertas en Tiempo Real</span>
                        <h3 class="text-lg font-bold text-white mt-1">Notificaciones sobre Estado de Riesgo, Citaciones y Observaciones</h3>
                    </div>
                    @if($unreadCount > 0)
                        <form method="POST" action="{{ route('notifications.readAll') }}">
                            @csrf
                            <button type="submit" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-xs text-white font-semibold cursor-pointer">
                                Marcar todas como leídas
                            </button>
                        </form>
                    @endif
                </div>

                <div class="space-y-3">
                    @forelse($notifications as $notif)
                        <div class="p-4 rounded-2xl border flex items-start justify-between gap-4
                            {{ $notif->read_at ? 'bg-slate-950/60 border-slate-800/70' : 'bg-slate-950 border-indigo-500/40' }}">
                            <div>
                                <div class="flex items-center gap-2 mb-1">
                                    @if(!$notif->read_at)
                                        <span class="w-2 h-2 rounded-full bg-indigo-400"></span>
                                    @endif
                                    <span class="mono text-[10px] uppercase px-2 py-0.5 rounded bg-slate-800 text-slate-300">{{ $notif->type }}</span>
                                    <span class="text-sm font-bold text-white">{{ $notif->title }}</span>
                                </div>
                                <p class="text-xs text-slate-300 leading-relaxed">{{ $notif->message }}</p>
                                <span class="mono text-[10px] text-slate-500 mt-1 block">{{ $notif->created_at->format('d/m/Y H:i') }} ({{ $notif->created_at->diffForHumans() }})</span>
                            </div>

                            @if(!$notif->read_at)
                                <form method="POST" action="{{ route('notifications.read', $notif->id) }}">
                                    @csrf
                                    <button type="submit" class="px-3 py-1.5 rounded-lg bg-indigo-600/20 text-indigo-300 border border-indigo-500/30 hover:bg-indigo-600/30 text-xs font-medium shrink-0 cursor-pointer">
                                        Marcar leída
                                    </button>
                                </form>
                            @endif
                        </div>
                    @empty
                        <p class="text-xs text-slate-400 py-6 text-center">No tienes notificaciones registradas.</p>
                    @endforelse
                </div>
            </div>
        @endif
    @endif
@endsection
