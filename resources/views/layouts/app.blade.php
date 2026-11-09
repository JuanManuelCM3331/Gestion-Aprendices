<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SGA · Gestión de Aprendices')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=DM+Mono:wght@400;500&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'DM Sans', sans-serif; }
        .mono { font-family: 'DM Mono', monospace; }
        .sidebar-scroll::-webkit-scrollbar { width: 5px; }
        .sidebar-scroll::-webkit-scrollbar-track { background: transparent; }
        .sidebar-scroll::-webkit-scrollbar-thumb { background: #334155; border-radius: 9999px; }
        .sidebar-scroll::-webkit-scrollbar-thumb:hover { background: #475569; }
        @media print {
            .no-print { display: none !important; }
        }
    </style>
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen flex flex-col md:flex-row antialiased selection:bg-indigo-500 selection:text-white">

    @php
        $user = auth()->user();
        $unreadCount = $user ? $user->unreadPlatformNotifications()->count() : 0;
    @endphp

    <!-- BARRA LATERAL -->
    <aside class="no-print w-full md:w-64 bg-slate-900/95 border-b md:border-b-0 md:border-r border-slate-800 flex flex-col shrink-0 md:h-screen md:sticky md:top-0 overflow-hidden">
        <!-- Cabecera fija de la barra lateral -->
        <div class="p-6 pb-4 shrink-0">
            <a href="{{ route('landing') }}" class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-indigo-500 to-violet-600 text-white flex items-center justify-center font-bold text-sm shadow-lg shadow-indigo-500/20">
                    SGA
                </div>
                <div>
                    <span class="block text-white font-bold text-base leading-none">SGA</span>
                    <span class="text-xs text-indigo-400">{{ $user ? $user->roleLabel() : 'Plataforma' }}</span>
                </div>
            </a>
        </div>

        <!-- Menú navegable con scroll propio -->
        <div class="px-6 py-2 flex-1 overflow-y-auto sidebar-scroll">
            @if($user)
                <nav class="space-y-1 pb-4">
                    @if(in_array($user->role, ['bienestar', 'admin']))
                        <p class="px-3 pt-2 pb-1 text-[11px] mono uppercase tracking-wider text-slate-500">Bienestar</p>
                        <a href="{{ route('bienestar.dashboard', ['tab' => 'monitoreo']) }}"
                           class="block px-3.5 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('bienestar.*') && request('tab', 'monitoreo') === 'monitoreo' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/20' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                            Semáforo de Riesgo
                        </a>
                        <a href="{{ route('bienestar.dashboard', ['tab' => 'casos']) }}"
                           class="block px-3.5 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('bienestar.*') && request('tab') === 'casos' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/20' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                            Casos y Seguimiento
                        </a>
                        <a href="{{ route('bienestar.dashboard', ['tab' => 'variables']) }}"
                           class="block px-3.5 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('bienestar.*') && request('tab') === 'variables' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/20' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                            Variables del Modelo
                        </a>
                        <a href="{{ route('bienestar.dashboard', ['tab' => 'reportes']) }}"
                           class="block px-3.5 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('bienestar.*') && request('tab') === 'reportes' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/20' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                            Reportes
                        </a>
                    @endif

                    @if(in_array($user->role, ['instructor', 'admin']))
                        <p class="px-3 pt-4 pb-1 text-[11px] mono uppercase tracking-wider text-slate-500">Instructor</p>
                        <a href="{{ route('instructor.dashboard', ['tab' => 'novedades']) }}"
                           class="block px-3.5 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('instructor.*') && request('tab', 'novedades') === 'novedades' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/20' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                            Observaciones
                        </a>
                        <a href="{{ route('instructor.dashboard', ['tab' => 'asignaciones']) }}"
                           class="block px-3.5 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('instructor.*') && request('tab') === 'asignaciones' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/20' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                            Mis Fichas y Horarios
                        </a>
                    @endif

                    @if($user->role === 'admin')
                        <p class="px-3 pt-4 pb-1 text-[11px] mono uppercase tracking-wider text-slate-500">Administración</p>
                        <a href="{{ route('admin.dashboard', ['tab' => 'auditoria']) }}"
                           class="block px-3.5 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('admin.*') && request('tab', 'auditoria') === 'auditoria' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/20' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                            Auditoría
                        </a>
                        <a href="{{ route('admin.dashboard', ['tab' => 'usuarios']) }}"
                           class="block px-3.5 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('admin.*') && request('tab') === 'usuarios' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/20' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                            Usuarios y Permisos
                        </a>
                        <a href="{{ route('admin.dashboard', ['tab' => 'maestras']) }}"
                           class="block px-3.5 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('admin.*') && request('tab') === 'maestras' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/20' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                            Programas y Fichas
                        </a>
                    @endif

                    @if(in_array($user->role, ['aprendiz', 'admin']))
                        <p class="px-3 pt-4 pb-1 text-[11px] mono uppercase tracking-wider text-slate-500">Aprendiz</p>
                        <a href="{{ route('aprendiz.dashboard', ['tab' => 'estado']) }}"
                           class="block px-3.5 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('aprendiz.*') && request('tab', 'estado') === 'estado' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/20' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                            Mi Estado
                        </a>
                        <a href="{{ route('aprendiz.dashboard', ['tab' => 'soportes']) }}"
                           class="block px-3.5 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('aprendiz.*') && request('tab') === 'soportes' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/20' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                            Mis Observaciones y Soportes
                        </a>
                        <a href="{{ route('aprendiz.dashboard', ['tab' => 'notificaciones']) }}"
                           class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-sm font-medium transition {{ request()->routeIs('aprendiz.*') && request('tab') === 'notificaciones' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/20' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                            <span>Notificaciones</span>
                            @if($unreadCount > 0)
                                <span class="px-2 py-0.5 text-xs rounded-full bg-rose-500 text-white font-semibold">{{ $unreadCount }}</span>
                            @endif
                        </a>
                    @endif
                </nav>
            @endif
        </div>

        @if($user)
            <div class="p-5 border-t border-slate-800 shrink-0 bg-slate-900">
                <div class="mb-3">
                    <p class="text-sm font-medium text-white truncate">{{ $user->name }}</p>
                    <p class="mono text-xs text-slate-400">{{ $user->username }}</p>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full py-2 px-3 rounded-xl text-xs font-medium text-rose-400 bg-rose-500/10 border border-rose-500/20 hover:bg-rose-500/20 transition cursor-pointer">
                        Cerrar sesión
                    </button>
                </form>
            </div>
        @endif
    </aside>

    <!-- CONTENIDO PRINCIPAL -->
    <main class="flex-1 min-w-0">
        <header class="no-print bg-slate-950/80 backdrop-blur border-b border-slate-800 px-8 py-5 flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold text-white">@yield('header_title', 'Panel Principal')</h1>
                <p class="text-sm text-slate-400">@yield('header_subtitle')</p>
            </div>
        </header>

        <div class="max-w-7xl mx-auto p-8">
            @if(session('status'))
                <div class="mb-6 px-4 py-3 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 text-sm font-medium">
                    {{ session('status') }}
                </div>
            @endif

            @if($errors->any())
                <div class="mb-6 px-4 py-3 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-300 text-sm">
                    @foreach($errors->all() as $err)
                        <p>{{ $err }}</p>
                    @endforeach
                </div>
            @endif

            @yield('content')
        </div>
    </main>

</body>
</html>
