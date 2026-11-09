@extends('layouts.app')

@section('title', 'Mi Portal · Aprendiz | SGA')
@section('header_title', 'Portal del Aprendiz')
@section('header_subtitle', 'Estado actual, observaciones, carga de soportes y notificaciones')

@section('content')
    @if(!$profile)
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-8 text-center">
            <p class="text-slate-400 text-sm">Inicia sesión con una cuenta de aprendiz (ej: <strong class="text-white">aprendiz1 / 123</strong>) para consultar su expediente.</p>
        </div>
    @else
        <!-- Resumen de Estado -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5">
                <span class="mono text-xs text-slate-400 uppercase">Mi Semáforo</span>
                <p class="text-2xl font-bold uppercase mt-1 {{ $profile->risk_level === 'alto' ? 'text-rose-400' : ($profile->risk_level === 'medio' ? 'text-amber-300' : 'text-emerald-400') }}">
                    {{ $profile->risk_level }} ({{ $profile->global_risk_score }}%)
                </p>
            </div>
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5">
                <span class="mono text-xs text-slate-400 uppercase">Estado Académico</span>
                <p class="text-xl font-bold text-white mt-1">{{ $profile->academic_status }}</p>
            </div>
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5">
                <span class="mono text-xs text-slate-400 uppercase">Asistencia</span>
                <p class="text-2xl font-bold text-white mt-1">{{ $profile->attendance_rate }}%</p>
            </div>
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5">
                <span class="mono text-xs text-slate-400 uppercase">Promedio</span>
                <p class="text-2xl font-bold text-white mt-1">{{ $profile->academic_average }} / 5.0</p>
            </div>
        </div>

        <!-- ================================================================= -->
        <!-- PESTAÑA 1: MI ESTADO Y COMPROMISOS -->
        <!-- ================================================================= -->
        @if($activeTab === 'estado')
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6">
                    <h2 class="text-base font-bold text-white mb-4">Mis Rutas y Compromisos de Bienestar</h2>
                    <div class="space-y-3">
                        @forelse($welfareCases as $case)
                            <div class="p-4 rounded-xl bg-slate-950 border border-slate-800">
                                <div class="flex justify-between text-xs mb-1">
                                    <span class="mono font-bold text-teal-400">{{ $case->case_code }}</span>
                                    <span class="font-medium text-slate-400">{{ $case->statusLabel() }}</span>
                                </div>
                                <p class="text-sm font-bold text-white">{{ $case->title }}</p>
                                <p class="text-xs text-slate-300 mt-1"><strong>Compromiso:</strong> {{ $case->commitments }}</p>
                            </div>
                        @empty
                            <p class="text-sm text-slate-400">No tienes compromisos pendientes con Bienestar.</p>
                        @endforelse
                    </div>
                </div>

                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-base font-bold text-white">Observaciones Recientes</h2>
                        <a href="{{ route('aprendiz.dashboard', ['tab' => 'soportes']) }}" class="text-xs font-semibold text-indigo-400 hover:underline">
                            Ver todas / Cargar soporte →
                        </a>
                    </div>
                    <div class="space-y-3">
                        @foreach($observations->take(4) as $obs)
                            <div class="p-4 rounded-xl bg-slate-950 border border-slate-800">
                                <div class="flex justify-between text-xs mb-1">
                                    <span class="font-semibold {{ $obs->type === 'positiva' ? 'text-emerald-400' : 'text-amber-300' }}">{{ $obs->typeLabel() }}</span>
                                    <span class="mono text-slate-500">{{ $obs->created_at->format('d/m/Y') }}</span>
                                </div>
                                <p class="text-sm font-bold text-white">{{ $obs->title }}</p>
                                <p class="text-xs text-slate-400 mt-0.5">{{ $obs->description }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        <!-- ================================================================= -->
        <!-- PESTAÑA 2: OBSERVACIONES Y CARGA DE SOPORTES -->
        <!-- ================================================================= -->
        @if($activeTab === 'soportes')
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 space-y-4">
                <div>
                    <h2 class="text-base font-bold text-white">Historial de Observaciones y Carga de Soportes</h2>
                    <p class="text-xs text-slate-400">Adjunta excusas médicas o descargos a tus observaciones para que queden vinculados a tu expediente.</p>
                </div>

                @foreach($observations as $obs)
                    <div class="p-5 rounded-xl bg-slate-950 border border-slate-800 space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $obs->type === 'positiva' ? 'bg-emerald-500/15 text-emerald-300' : 'bg-amber-500/15 text-amber-300' }}">
                                {{ $obs->typeLabel() }}
                            </span>
                            <span class="mono text-xs text-slate-500">{{ $obs->created_at->format('d/m/Y H:i') }}</span>
                        </div>

                        <div>
                            <h3 class="text-sm font-bold text-white">{{ $obs->title }}</h3>
                            <p class="text-xs text-slate-300 mt-1">{{ $obs->description }}</p>
                        </div>

                        <!-- Soportes enviados -->
                        @foreach($obs->supports as $sup)
                            <div class="p-3 rounded-lg bg-slate-900 border border-slate-800 flex items-center justify-between text-xs">
                                <div>
                                    <span class="font-semibold text-white">{{ $sup->support_type }}: {{ $sup->title }}</span>
                                    <p class="text-slate-400">{{ $sup->description }}</p>
                                </div>
                                <span class="mono px-2.5 py-1 rounded-full font-semibold uppercase
                                    {{ $sup->status === 'aprobado' ? 'bg-emerald-500/15 text-emerald-300' : ($sup->status === 'rechazado' ? 'bg-rose-500/15 text-rose-300' : 'bg-amber-500/15 text-amber-300') }}">
                                    {{ $sup->status }}
                                </span>
                            </div>
                        @endforeach

                        @if($obs->type !== 'positiva')
                            <details class="pt-2 border-t border-slate-800 text-xs">
                                <summary class="font-semibold text-indigo-400 cursor-pointer">+ Adjuntar soporte o excusa médica</summary>
                                <form method="POST" action="{{ route('aprendiz.supports.store', $obs->id) }}" enctype="multipart/form-data" class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    @csrf
                                    <input name="title" required placeholder="Título del soporte" class="px-3 py-2 rounded-lg bg-slate-900 border border-slate-800 text-white">
                                    <select name="support_type" class="px-3 py-2 rounded-lg bg-slate-900 border border-slate-800 text-white">
                                        <option value="Excusa Médica">Excusa Médica</option>
                                        <option value="Descargo a Llamado de Atención">Descargo a Llamado de Atención</option>
                                        <option value="Soporte Calamidad Doméstica">Soporte Calamidad Doméstica</option>
                                    </select>
                                    <input name="description" required placeholder="Explicación breve..." class="sm:col-span-2 px-3 py-2 rounded-lg bg-slate-900 border border-slate-800 text-white">
                                    <input type="file" name="attachment" class="text-slate-300">
                                    <button type="submit" class="py-2 px-4 rounded-lg bg-indigo-600 text-white font-medium cursor-pointer">Enviar soporte</button>
                                </form>
                            </details>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif

        <!-- ================================================================= -->
        <!-- PESTAÑA 3: NOTIFICACIONES -->
        <!-- ================================================================= -->
        @if($activeTab === 'notificaciones')
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 space-y-3">
                <div class="flex items-center justify-between mb-2">
                    <h2 class="text-base font-bold text-white">Mis Alertas y Notificaciones</h2>
                    @if($unreadCount > 0)
                        <form method="POST" action="{{ route('notifications.readAll') }}">
                            @csrf
                            <button type="submit" class="text-xs font-semibold text-indigo-400 hover:underline cursor-pointer">
                                Marcar todas como leídas
                            </button>
                        </form>
                    @endif
                </div>

                @forelse($notifications as $notif)
                    <div class="p-4 rounded-xl border {{ $notif->read_at ? 'border-slate-800 bg-slate-950/60' : 'border-indigo-500/40 bg-slate-950' }} flex items-center justify-between gap-4">
                        <div>
                            <p class="text-sm font-bold text-white">{{ $notif->title }}</p>
                            <p class="text-xs text-slate-300 mt-0.5">{{ $notif->message }}</p>
                        </div>
                        @if(!$notif->read_at)
                            <form method="POST" action="{{ route('notifications.read', $notif->id) }}">
                                @csrf
                                <button type="submit" class="px-3 py-1.5 rounded-lg bg-indigo-600/20 border border-indigo-500/30 text-xs font-medium text-indigo-300 hover:bg-indigo-600/30 cursor-pointer">
                                    Leída
                                </button>
                            </form>
                        @endif
                    </div>
                @empty
                    <p class="text-sm text-slate-400">No tienes notificaciones.</p>
                @endforelse
            </div>
        @endif
    @endif
@endsection
