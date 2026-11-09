<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SGA · Sistema de Gestión de Aprendices')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Mono:wght@400;500&family=DM+Sans:ital,opsz,wght@0,9..40,300..700;1,9..40,300..700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"DM Sans"', 'sans-serif'],
                        mono: ['"DM Mono"', 'monospace'],
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'DM Sans', sans-serif; }
        .mono { font-family: 'DM Mono', monospace; }
        @media print {
            .no-print { display: none !important; }
            main { overflow: visible !important; height: auto !important; }
        }
    </style>
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen flex flex-col md:flex-row antialiased selection:bg-indigo-500 selection:text-white">

    @php
        /** @var \App\Models\User|null $currentUser */
        $currentUser = auth()->user();
        $unreadNotifications = $currentUser ? $currentUser->unreadPlatformNotifications()->limit(10)->get() : collect();
    @endphp

    <!-- BARRA LATERAL -->
    <aside class="no-print w-full md:w-72 bg-slate-900/95 border-b md:border-b-0 md:border-r border-slate-800/80 flex flex-col justify-between shrink-0 md:h-screen md:sticky md:top-0">
        <div class="p-6">
            <!-- Brand Header -->
            <div class="flex items-center justify-between mb-6">
                <a href="{{ route('landing') }}" class="flex items-center gap-3 group">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500 to-violet-600 flex items-center justify-center text-white font-bold tracking-wider shadow-lg shadow-indigo-500/20 group-hover:scale-105 transition">
                        SGA
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                            <span class="mono text-[11px] uppercase tracking-widest text-indigo-400">Laravel 12 · MVC</span>
                        </div>
                        <h1 class="text-base font-bold text-white tracking-tight">Gestión Aprendices</h1>
                    </div>
                </a>
            </div>

            @if($currentUser)
                <!-- Tarjeta de Perfil Activo -->
                <div class="mb-6 p-3.5 rounded-2xl bg-slate-800/60 border border-slate-700/60">
                    <div class="flex items-center justify-between gap-2 mb-1">
                        <span class="mono text-[11px] px-2.5 py-0.5 rounded-full font-medium
                            @if($currentUser->role === 'bienestar') bg-teal-500/15 text-teal-300 border border-teal-500/30
                            @elseif($currentUser->role === 'instructor') bg-indigo-500/15 text-indigo-300 border border-indigo-500/30
                            @elseif($currentUser->role === 'admin') bg-rose-500/15 text-rose-300 border border-rose-500/30
                            @else bg-amber-500/15 text-amber-300 border border-amber-500/30 @endif">
                            {{ strtoupper($currentUser->role) }}
                        </span>
                        <span class="mono text-[10px] text-slate-400">IP: {{ request()->ip() }}</span>
                    </div>
                    <p class="text-sm font-semibold text-white truncate">{{ $currentUser->name }}</p>
                    <p class="text-xs text-slate-400 truncate">{{ $currentUser->roleLabel() }}</p>
                </div>

                <!-- Navegación Contextual por Rol -->
                <div class="space-y-1">
                    <p class="px-3 text-[11px] mono uppercase tracking-wider text-slate-500 mb-2">Módulos del Perfil</p>

                    @if(in_array($currentUser->role, ['bienestar', 'admin']))
                        <a href="{{ route('bienestar.dashboard', ['tab' => 'monitoreo']) }}"
                           class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('bienestar.*') && request('tab', 'monitoreo') === 'monitoreo' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/25' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                            <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                            Monitoreo & Semáforo
                        </a>
                        <a href="{{ route('bienestar.dashboard', ['tab' => 'variables']) }}"
                           class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('bienestar.*') && request('tab') === 'variables' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/25' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                            <span class="w-2 h-2 rounded-full bg-indigo-400"></span>
                            Variables del Algoritmo
                        </a>
                        <a href="{{ route('bienestar.dashboard', ['tab' => 'casos']) }}"
                           class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('bienestar.*') && request('tab') === 'casos' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/25' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                            <span class="w-2 h-2 rounded-full bg-amber-400"></span>
                            Trazabilidad de Casos
                        </a>
                        <a href="{{ route('bienestar.dashboard', ['tab' => 'reportes']) }}"
                           class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('bienestar.*') && request('tab') === 'reportes' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/25' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                            <span class="w-2 h-2 rounded-full bg-cyan-400"></span>
                            Reportes Masivos
                        </a>
                    @endif

                    @if(in_array($currentUser->role, ['instructor', 'admin']))
                        <a href="{{ route('instructor.dashboard', ['tab' => 'novedades']) }}"
                           class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('instructor.*') && request('tab', 'novedades') === 'novedades' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/25' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                            <span class="w-2 h-2 rounded-full bg-sky-400"></span>
                            Carga Ágil & Escalamiento
                        </a>
                        <a href="{{ route('instructor.dashboard', ['tab' => 'asignaciones']) }}"
                           class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('instructor.*') && request('tab') === 'asignaciones' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/25' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                            <span class="w-2 h-2 rounded-full bg-violet-400"></span>
                            Mis Fichas y Horarios
                        </a>
                    @endif

                    @if($currentUser->role === 'admin')
                        <div class="pt-3 mt-3 border-t border-slate-800">
                            <p class="px-3 text-[11px] mono uppercase tracking-wider text-rose-400 mb-2">Gobernanza y Seguridad</p>
                            <a href="{{ route('admin.dashboard', ['tab' => 'auditoria']) }}"
                               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('admin.*') && request('tab', 'auditoria') === 'auditoria' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/25' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                <span class="w-2 h-2 rounded-full bg-rose-400"></span>
                                Auditoría Inmutable SHA-256
                            </a>
                            <a href="{{ route('admin.dashboard', ['tab' => 'usuarios']) }}"
                               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('admin.*') && request('tab') === 'usuarios' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/25' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                <span class="w-2 h-2 rounded-full bg-amber-400"></span>
                                Control de Acceso Granular
                            </a>
                            <a href="{{ route('admin.dashboard', ['tab' => 'maestras']) }}"
                               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('admin.*') && request('tab') === 'maestras' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/25' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                                Tablas Maestras (BD)
                            </a>
                        </div>
                    @endif

                    @if(in_array($currentUser->role, ['aprendiz', 'admin']))
                        <div class="pt-3 mt-3 border-t border-slate-800">
                            <p class="px-3 text-[11px] mono uppercase tracking-wider text-amber-400 mb-2">Portal Aprendiz</p>
                            <a href="{{ route('aprendiz.dashboard', ['tab' => 'estado']) }}"
                               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('aprendiz.*') && request('tab', 'estado') === 'estado' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/25' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                                Mi Estado y Semáforo
                            </a>
                            <a href="{{ route('aprendiz.dashboard', ['tab' => 'soportes']) }}"
                               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('aprendiz.*') && request('tab') === 'soportes' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/25' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                <span class="w-2 h-2 rounded-full bg-amber-400"></span>
                                Observaciones y Soportes
                            </a>
                            <a href="{{ route('aprendiz.dashboard', ['tab' => 'notificaciones']) }}"
                               class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('aprendiz.*') && request('tab') === 'notificaciones' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/25' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                <span class="flex items-center gap-3">
                                    <span class="w-2 h-2 rounded-full bg-rose-400"></span>
                                    Alertas y Citaciones
                                </span>
                                @if($unreadNotifications->count() > 0)
                                    <span class="px-2 py-0.5 text-[11px] rounded-full bg-rose-500 text-white font-bold">
                                        {{ $unreadNotifications->count() }}
                                    </span>
                                @endif
                            </a>
                        </div>
                    @endif
                </div>
            @endif
        </div>

        <!-- Footer de Barra Lateral -->
        <div class="p-6 border-t border-slate-800/80 space-y-3">
            <div class="p-3 rounded-xl bg-slate-950/80 border border-slate-800 text-[11px] text-slate-400">
                <div class="mono text-indigo-400 font-medium mb-1">Arquitectura Activa</div>
                <p>MVC + Service Layer + Repository Contracts</p>
            </div>

            @if($currentUser)
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                            class="w-full flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-rose-400 bg-rose-500/10 border border-rose-500/20 hover:bg-rose-500/20 hover:text-rose-300 transition cursor-pointer">
                        <span>Cerrar Sesión Segura</span>
                    </button>
                </form>
            @endif
        </div>
    </aside>

    <!-- CONTENIDO PRINCIPAL -->
    <main class="flex-1 min-w-0 overflow-y-auto">
        <!-- Topbar Superior -->
        <header class="no-print sticky top-0 z-30 backdrop-blur-md bg-slate-950/80 border-b border-slate-800/80 px-6 md:px-10 py-4 flex flex-wrap items-center justify-between gap-4">
            <div>
                <h2 class="text-lg md:text-xl font-bold text-white">@yield('header_title', 'Panel de Control SGA')</h2>
                <p class="text-xs text-slate-400">@yield('header_subtitle', 'Plataforma Predictiva e Integral de Gestión de Aprendices')</p>
            </div>

            @if($currentUser)
                <div class="flex items-center gap-3">
                    <!-- Botón de Notificaciones -->
                    <button type="button"
                            onclick="document.getElementById('notif-drawer').classList.toggle('hidden')"
                            class="relative px-3.5 py-2 rounded-xl bg-slate-900 border border-slate-800 hover:border-indigo-500/50 text-slate-300 hover:text-white text-xs font-medium flex items-center gap-2 transition cursor-pointer">
                        <span>Alertas</span>
                        @if($unreadNotifications->count() > 0)
                            <span class="px-2 py-0.5 rounded-full bg-rose-500 text-white font-bold text-[11px]">
                                {{ $unreadNotifications->count() }}
                            </span>
                        @else
                            <span class="px-2 py-0.5 rounded-full bg-slate-800 text-slate-400 text-[11px]">0</span>
                        @endif
                    </button>

                    <a href="{{ route('landing') }}"
                       class="px-3.5 py-2 rounded-xl bg-slate-900 border border-slate-800 hover:border-slate-700 text-xs text-slate-300 hover:text-white transition">
                        Ver Landing →
                    </a>
                </div>
            @endif
        </header>

        <!-- Drawer de Notificaciones Rápidas -->
        @if($currentUser)
            <div id="notif-drawer" class="hidden fixed right-6 top-16 z-50 w-96 max-w-[92vw] bg-slate-900 border border-slate-700 rounded-2xl shadow-2xl p-5">
                <div class="flex items-center justify-between pb-3 mb-3 border-b border-slate-800">
                    <div>
                        <h3 class="text-sm font-bold text-white">Notificaciones de Plataforma</h3>
                        <p class="text-xs text-slate-400">Alertas de riesgo, citaciones y novedades</p>
                    </div>
                    @if($unreadNotifications->count() > 0)
                        <form method="POST" action="{{ route('notifications.readAll') }}">
                            @csrf
                            <button type="submit" class="text-xs text-indigo-400 hover:text-indigo-300 font-medium cursor-pointer">
                                Marcar todas leídas
                            </button>
                        </form>
                    @endif
                </div>
                <div class="space-y-2.5 max-h-80 overflow-y-auto">
                    @forelse($unreadNotifications as $notif)
                        <div class="p-3 rounded-xl bg-slate-950/90 border border-slate-800">
                            <div class="flex items-center justify-between gap-2 mb-1">
                                <span class="text-xs font-bold text-white">{{ $notif->title }}</span>
                                <span class="mono text-[10px] text-slate-500">{{ $notif->created_at->diffForHumans() }}</span>
                            </div>
                            <p class="text-xs text-slate-300 leading-relaxed mb-2">{{ $notif->message }}</p>
                            <form method="POST" action="{{ route('notifications.read', $notif->id) }}">
                                @csrf
                                <button type="submit" class="text-[11px] text-indigo-400 hover:underline cursor-pointer">
                                    Confirmar lectura ✓
                                </button>
                            </form>
                        </div>
                    @empty
                        <p class="text-xs text-slate-400 py-4 text-center">No tienes alertas pendientes por leer.</p>
                    @endforelse
                </div>
            </div>
        @endif

        <!-- Mensajes Flash de Estado y Errores -->
        <div class="px-6 md:px-10 pt-6">
            @if(session('status'))
                <div class="mb-6 p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 text-sm flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 shrink-0"></span>
                        <span>{{ session('status') }}</span>
                    </div>
                </div>
            @endif

            @if($errors->any())
                <div class="mb-6 p-4 rounded-2xl bg-rose-500/10 border border-rose-500/30 text-rose-300 text-sm space-y-1">
                    <div class="font-bold text-rose-200 mb-1">Atención — Verifica la siguiente información:</div>
                    @foreach($errors->all() as $err)
                        <p>• {{ $err }}</p>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Cuerpo de la Vista -->
        <div class="px-6 md:px-10 pb-12">
            @yield('content')
        </div>
    </main>

</body>
</html>
