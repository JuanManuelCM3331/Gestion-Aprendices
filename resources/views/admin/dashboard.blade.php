@extends('layouts.app')

@section('title', 'Gobernanza y Seguridad · Administrador | SGA')
@section('header_title', 'Gobernanza y Seguridad · Administrador del Sistema')
@section('header_subtitle', 'Auditoría inmutable con firma SHA-256 y rastreo IP, control de acceso granular RBAC y mantenimiento de tablas maestras')

@section('content')
    <!-- Pestañas de Gobernanza -->
    <div class="flex flex-wrap items-center gap-2 mb-8 border-b border-slate-800 pb-4">
        <a href="{{ route('admin.dashboard', ['tab' => 'auditoria']) }}"
           class="px-4 py-2.5 rounded-xl text-sm font-semibold transition {{ $activeTab === 'auditoria' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/25' : 'bg-slate-900 text-slate-300 border border-slate-800 hover:text-white' }}">
            1. Auditoría Inmutable & Rastreo IP ({{ $auditLogs->count() }})
        </a>
        <a href="{{ route('admin.dashboard', ['tab' => 'usuarios']) }}"
           class="px-4 py-2.5 rounded-xl text-sm font-semibold transition {{ $activeTab === 'usuarios' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/25' : 'bg-slate-900 text-slate-300 border border-slate-800 hover:text-white' }}">
            2. Control de Acceso Granular & Usuarios ({{ $users->count() }})
        </a>
        <a href="{{ route('admin.dashboard', ['tab' => 'maestras']) }}"
           class="px-4 py-2.5 rounded-xl text-sm font-semibold transition {{ $activeTab === 'maestras' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/25' : 'bg-slate-900 text-slate-300 border border-slate-800 hover:text-white' }}">
            3. Mantenimiento de Tablas Maestras (Programas, Sedes, Periodos, Fichas)
        </a>
    </div>

    <!-- ========================================================================= -->
    <!-- PESTAÑA 1: AUDITORÍA INMUTABLE CON CADENA SHA-256 Y DIRECCIÓN IP -->
    <!-- ========================================================================= -->
    @if($activeTab === 'auditoria')
        <!-- Banner de Integridad Criptográfica -->
        <div class="p-5 rounded-2xl mb-6 border flex flex-wrap items-center justify-between gap-4
            {{ $chainIntegrity['valid'] ? 'bg-emerald-500/10 border-emerald-500/30' : 'bg-rose-500/10 border-rose-500/30' }}">
            <div>
                <div class="flex items-center gap-2.5">
                    <span class="w-3 h-3 rounded-full {{ $chainIntegrity['valid'] ? 'bg-emerald-400 animate-pulse' : 'bg-rose-500' }}"></span>
                    <h3 class="text-base font-bold text-white">
                        {{ $chainIntegrity['valid'] ? 'Cadena de Auditoría Inmutable Verificada (SHA-256 Encadenado)' : 'Alerta de Integridad en Cadena de Auditoría' }}
                    </h3>
                </div>
                <p class="text-xs text-slate-300 mt-1">
                    El modelo <code class="mono text-indigo-300">AuditLog</code> bloquea a nivel de ORM cualquier sentencia <code class="mono">UPDATE</code> o <code class="mono">DELETE</code>. Cada evento firma criptográficamente el hash anterior, el autor, la dirección IP y los valores antes/después.
                </p>
            </div>
            <div class="text-right mono text-xs">
                <span class="text-slate-400 block">Bloques Verificados: <strong class="text-white">{{ $chainIntegrity['checked_count'] }}</strong></span>
                <span class="text-emerald-300 text-[11px]">Hash Raíz: {{ substr($chainIntegrity['last_hash'] ?? '', 0, 20) }}...</span>
            </div>
        </div>

        <!-- Filtros de Auditoría -->
        <form method="GET" action="{{ route('admin.dashboard') }}" class="bg-slate-900/90 border border-slate-800 rounded-2xl p-4 mb-6 grid grid-cols-1 md:grid-cols-4 gap-3 items-end">
            <input type="hidden" name="tab" value="auditoria">
            <div>
                <label class="block text-xs text-slate-400 mb-1">Buscar Usuario / IP / Acción</label>
                <input name="search" value="{{ $auditFilters['search'] ?? '' }}" placeholder="Ej: 127.0.0.1, admin, pesos..."
                       class="w-full px-3.5 py-2 rounded-xl bg-slate-950 border border-slate-800 text-sm text-white">
            </div>
            <div>
                <label class="block text-xs text-slate-400 mb-1">Severidad</label>
                <select name="severity" class="w-full px-3.5 py-2 rounded-xl bg-slate-950 border border-slate-800 text-sm text-white">
                    <option value="">Todas las severidades</option>
                    <option value="critical" @selected(($auditFilters['severity'] ?? '') === 'critical')>Crítico (Cambios de Riesgo / Borrados / Bloqueos)</option>
                    <option value="warning" @selected(($auditFilters['severity'] ?? '') === 'warning')>Advertencia</option>
                    <option value="info" @selected(($auditFilters['severity'] ?? '') === 'info')>Informativo</option>
                </select>
            </div>
            <div class="md:col-span-2 flex gap-2">
                <button type="submit" class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold cursor-pointer">
                    Filtrar Registros Inmutables
                </button>
                <a href="{{ route('admin.dashboard', ['tab' => 'auditoria']) }}" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300 text-sm hover:text-white">
                    Ver Todos
                </a>
            </div>
        </form>

        <!-- Tabla de Auditoría Inmutable -->
        <div class="bg-slate-900/90 border border-slate-800 rounded-2xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left">
                    <thead class="bg-slate-950 text-slate-400 uppercase mono">
                        <tr>
                            <th class="px-4 py-3.5">ID / Fecha Exacta</th>
                            <th class="px-4 py-3.5">Usuario / Rol</th>
                            <th class="px-4 py-3.5">Dirección IP</th>
                            <th class="px-4 py-3.5">Módulo</th>
                            <th class="px-4 py-3.5">Acción y Trazabilidad de Cambios</th>
                            <th class="px-4 py-3.5">Severidad</th>
                            <th class="px-4 py-3.5">Firma SHA-256</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/80">
                        @foreach($auditLogs as $log)
                            <tr class="hover:bg-slate-800/40 align-top">
                                <td class="px-4 py-3.5 mono text-slate-300 whitespace-nowrap">
                                    #{{ $log->id }}<br>
                                    <span class="text-[11px] text-slate-500">{{ $log->created_at?->format('Y-m-d H:i:s') }}</span>
                                </td>
                                <td class="px-4 py-3.5">
                                    <span class="font-bold text-white block">{{ $log->username }}</span>
                                    <span class="mono text-[10px] text-indigo-400 uppercase">{{ $log->role }}</span>
                                </td>
                                <td class="px-4 py-3.5 mono text-emerald-300 whitespace-nowrap">
                                    {{ $log->ip_address }}
                                </td>
                                <td class="px-4 py-3.5 text-slate-300 font-medium">
                                    {{ $log->module }}
                                </td>
                                <td class="px-4 py-3.5 text-slate-200 max-w-md">
                                    <p class="font-medium">{{ $log->action }}</p>
                                    @if($log->old_values || $log->new_values)
                                        <details class="mt-1.5">
                                            <summary class="text-[11px] text-indigo-400 cursor-pointer hover:underline">Ver Diff JSON (Valores Antes / Después)</summary>
                                            <div class="mt-1.5 p-2.5 rounded-lg bg-slate-950 border border-slate-800 mono text-[10px] text-slate-300 space-y-1 overflow-x-auto">
                                                @if($log->old_values)
                                                    <div><span class="text-rose-400">ANTES:</span> {{ json_encode($log->old_values, JSON_UNESCAPED_UNICODE) }}</div>
                                                @endif
                                                @if($log->new_values)
                                                    <div><span class="text-emerald-400">DESPUÉS:</span> {{ json_encode($log->new_values, JSON_UNESCAPED_UNICODE) }}</div>
                                                @endif
                                            </div>
                                        </details>
                                    @endif
                                </td>
                                <td class="px-4 py-3.5">
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase
                                        @if($log->severity === 'critical') bg-rose-500/15 text-rose-300 border border-rose-500/30
                                        @elseif($log->severity === 'warning') bg-amber-500/15 text-amber-300 border border-amber-500/30
                                        @else bg-sky-500/15 text-sky-300 border border-sky-500/30 @endif">
                                        {{ $log->severity }}
                                    </span>
                                </td>
                                <td class="px-4 py-3.5 mono text-[10px] text-slate-500" title="Hash Completo: {{ $log->integrity_hash }}">
                                    🔒 {{ substr($log->integrity_hash, 0, 14) }}...
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <!-- ========================================================================= -->
    <!-- PESTAÑA 2: CONTROL DE ACCESO GRANULAR (CRUD DE USUARIOS Y PERMISOS) -->
    <!-- ========================================================================= -->
    @if($activeTab === 'usuarios')
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Crear Nuevo Usuario -->
            <div class="lg:col-span-4 bg-slate-900/90 border border-slate-800 rounded-2xl p-6 h-fit">
                <span class="mono text-xs text-amber-400 uppercase tracking-widest">Ciclo de Vida de Usuarios</span>
                <h3 class="text-lg font-bold text-white mt-1 mb-4">Registrar Usuario con Rol y Permisos</h3>

                <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-3.5 text-sm">
                    @csrf
                    <div>
                        <label class="block text-xs text-slate-400 mb-1">Nombre Completo</label>
                        <input name="name" required placeholder="Ej: Ing. Camilo Restrepo"
                               class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs text-slate-400 mb-1">Username</label>
                            <input name="username" required placeholder="camilo.restrepo"
                                   class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white">
                        </div>
                        <div>
                            <label class="block text-xs text-slate-400 mb-1">Rol Principal</label>
                            <select name="role" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white">
                                <option value="bienestar">Gestor de Bienestar</option>
                                <option value="instructor">Instructor</option>
                                <option value="aprendiz">Aprendiz</option>
                                <option value="admin">Administrador</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs text-slate-400 mb-1">Correo Institucional</label>
                        <input type="email" name="email" required placeholder="usuario@sga.edu.co"
                               class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs text-slate-400 mb-1">Contraseña Inicial</label>
                            <input type="password" name="password" required placeholder="Mínimo 4 caracteres"
                                   class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white">
                        </div>
                        <div>
                            <label class="block text-xs text-slate-400 mb-1">Ficha (Si es Aprendiz)</label>
                            <select name="ficha_id" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white">
                                @foreach($fichas as $f)
                                    <option value="{{ $f->id }}">{{ $f->code }} ({{ $f->jornada }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <button type="submit" class="w-full py-3 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-semibold shadow-lg shadow-indigo-600/20 transition cursor-pointer">
                        Crear Usuario y Asignar Permisos Base
                    </button>
                </form>
            </div>

            <!-- Listado de Usuarios con Matriz de Permisos Granulares y Restablecimiento de Credenciales -->
            <div class="lg:col-span-8 space-y-4">
                @foreach($users as $u)
                    <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-5 space-y-3">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <div class="flex items-center gap-2.5">
                                    <span class="text-base font-bold text-white">{{ $u->name }}</span>
                                    <span class="mono text-xs text-indigo-400">({{ $u->username }})</span>
                                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold uppercase
                                        {{ $u->is_active ? 'bg-emerald-500/15 text-emerald-300 border border-emerald-500/30' : 'bg-rose-500/15 text-rose-300 border border-rose-500/30' }}">
                                        {{ $u->is_active ? 'Activo' : 'Bloqueado' }}
                                    </span>
                                </div>
                                <p class="text-xs text-slate-400 mt-0.5">
                                    Rol: <strong class="text-slate-200">{{ $u->roleLabel() }}</strong> · {{ $u->email }} ·
                                    Último IP: <span class="mono text-slate-300">{{ $u->last_login_ip ?? 'Sin ingreso' }}</span>
                                </p>
                            </div>

                            @if($u->id !== auth()->id())
                                <form method="POST" action="{{ route('admin.users.destroy', $u->id) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-3 py-1.5 rounded-xl bg-rose-500/15 border border-rose-500/30 text-rose-300 hover:bg-rose-500/25 text-xs font-semibold cursor-pointer">
                                        Eliminar Usuario
                                    </button>
                                </form>
                            @endif
                        </div>

                        <!-- Panel Desplegable de Control Granular de Permisos y Credenciales -->
                        <details class="pt-3 border-t border-slate-800">
                            <summary class="text-xs font-semibold text-amber-300 hover:text-amber-200 cursor-pointer flex items-center justify-between">
                                <span>⚙ Configurar Permisos Granulares por Módulo / Restablecer Credenciales ({{ count($u->permissions ?? []) }} permisos activos)</span>
                                <span class="mono text-[11px] text-slate-500">Editar Acceso</span>
                            </summary>

                            <form method="POST" action="{{ route('admin.users.update', $u->id) }}" class="mt-4 space-y-4 text-xs">
                                @csrf
                                @method('PUT')
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                    <div>
                                        <label class="block text-slate-400 mb-1">Rol Asignado</label>
                                        <select name="role" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white">
                                            @foreach(['admin' => 'Administrador', 'bienestar' => 'Gestor de Bienestar', 'instructor' => 'Instructor', 'aprendiz' => 'Aprendiz'] as $rKey => $rLabel)
                                                <option value="{{ $rKey }}" @selected($u->role === $rKey)>{{ $rLabel }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-slate-400 mb-1">Estado de Acceso</label>
                                        <select name="is_active" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white">
                                            <option value="1" @selected($u->is_active)>Habilitado</option>
                                            <option value="0" @selected(!$u->is_active)>Bloqueado (Denegar Login)</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-slate-400 mb-1">Restablecer Contraseña (Opcional)</label>
                                        <input type="password" name="new_password" placeholder="Nueva clave si deseas cambiarla"
                                               class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white">
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-slate-400 mb-2 font-semibold">Permisos Granulares Habilitados (Bloquea acceso entre módulos no marcados):</label>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 p-3 rounded-xl bg-slate-950 border border-slate-800">
                                        @foreach($availablePermissions as $permKey => $permDesc)
                                            <label class="flex items-center gap-2 text-slate-300 cursor-pointer">
                                                <input type="checkbox"
                                                       name="permissions[]"
                                                       value="{{ $permKey }}"
                                                       @checked(in_array($permKey, $u->permissions ?? [], true))
                                                       class="rounded accent-indigo-500">
                                                <span><strong class="mono text-indigo-300">{{ $permKey }}</strong> — {{ $permDesc }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="flex justify-end">
                                    <button type="submit" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-semibold cursor-pointer">
                                        Guardar Permisos y Credenciales
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
    <!-- PESTAÑA 3: MANTENIMIENTO DE TABLAS MAESTRAS DE LA BASE DE DATOS -->
    <!-- ========================================================================= -->
    @if($activeTab === 'maestras')
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- 1. Programas de Formación -->
            <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-6 space-y-4">
                <h3 class="text-base font-bold text-white">1. Programas de Formación ({{ $programs->count() }})</h3>
                <form method="POST" action="{{ route('admin.master.programs.store') }}" class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                    @csrf
                    <input name="code" required placeholder="Código (Ej: 228199)" class="px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white">
                    <input name="name" required placeholder="Nombre del Programa" class="px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white">
                    <select name="level" class="px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white">
                        <option value="Tecnólogo">Tecnólogo</option>
                        <option value="Técnico">Técnico</option>
                        <option value="Especialización">Especialización</option>
                    </select>
                    <div class="flex gap-2">
                        <input type="number" name="duration_trimesters" value="6" min="1" max="12" class="w-20 px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white">
                        <button type="submit" class="flex-1 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-semibold cursor-pointer">+ Crear Programa</button>
                    </div>
                </form>
                <div class="space-y-1.5 text-xs">
                    @foreach($programs as $prog)
                        <div class="p-2.5 rounded-xl bg-slate-950 border border-slate-800 flex justify-between">
                            <span><strong class="mono text-indigo-300">[{{ $prog->code }}]</strong> {{ $prog->name }} ({{ $prog->level }})</span>
                            <span class="mono text-slate-400">{{ $prog->fichas_count }} fichas</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- 2. Sedes Institucionales -->
            <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-6 space-y-4">
                <h3 class="text-base font-bold text-white">2. Sedes y Centros de Formación ({{ $sedes->count() }})</h3>
                <form method="POST" action="{{ route('admin.master.sedes.store') }}" class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                    @csrf
                    <input name="code" required placeholder="Código (Ej: SED-ORI)" class="px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white">
                    <input name="name" required placeholder="Nombre de la Sede" class="px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white">
                    <input name="city" required placeholder="Ciudad / Municipio" class="px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white">
                    <button type="submit" class="py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-semibold cursor-pointer">+ Crear Sede</button>
                </form>
                <div class="space-y-1.5 text-xs">
                    @foreach($sedes as $sede)
                        <div class="p-2.5 rounded-xl bg-slate-950 border border-slate-800 flex justify-between">
                            <span><strong class="mono text-emerald-300">[{{ $sede->code }}]</strong> {{ $sede->name }} — {{ $sede->city }}</span>
                            <span class="mono text-slate-400">{{ $sede->fichas_count }} fichas</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- 3. Periodos Académicos -->
            <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-6 space-y-4">
                <h3 class="text-base font-bold text-white">3. Periodos Académicos ({{ $periods->count() }})</h3>
                <form method="POST" action="{{ route('admin.master.periods.store') }}" class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                    @csrf
                    <input name="code" required placeholder="Código (Ej: 2027-I)" class="px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white">
                    <input name="name" required placeholder="Nombre del Periodo" class="px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white">
                    <input type="date" name="start_date" required value="2027-01-15" class="px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white">
                    <div class="flex gap-2">
                        <input type="date" name="end_date" required value="2027-06-30" class="flex-1 px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white">
                        <button type="submit" class="px-3 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-semibold cursor-pointer">+ Crear</button>
                    </div>
                </form>
                <div class="space-y-1.5 text-xs">
                    @foreach($periods as $per)
                        <div class="p-2.5 rounded-xl bg-slate-950 border border-slate-800 flex justify-between">
                            <span><strong class="mono text-amber-300">[{{ $per->code }}]</strong> {{ $per->name }}</span>
                            <span class="mono text-slate-400">{{ $per->start_date->format('Y-m-d') }} a {{ $per->end_date->format('Y-m-d') }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- 4. Fichas y Asignación de Instructores -->
            <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-6 space-y-4">
                <h3 class="text-base font-bold text-white">4. Creación de Fichas y Asignación de Instructores</h3>
                <form method="POST" action="{{ route('admin.master.fichas.store') }}" class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs pb-4 border-b border-slate-800">
                    @csrf
                    <input name="code" required placeholder="Número de Ficha (Ej: 2890105)" class="px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white">
                    <select name="training_program_id" class="px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white">
                        @foreach($programs as $p)
                            <option value="{{ $p->id }}">{{ $p->name }}</option>
                        @endforeach
                    </select>
                    <select name="sede_id" class="px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white">
                        @foreach($sedes as $s)
                            <option value="{{ $s->id }}">{{ $s->name }}</option>
                        @endforeach
                    </select>
                    <select name="academic_period_id" class="px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white">
                        @foreach($periods as $per)
                            <option value="{{ $per->id }}">{{ $per->code }}</option>
                        @endforeach
                    </select>
                    <select name="jornada" class="px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white">
                        <option value="Mañana">Jornada Mañana</option>
                        <option value="Tarde">Jornada Tarde</option>
                        <option value="Noche">Jornada Noche</option>
                        <option value="Fines de Semana">Fines de Semana</option>
                    </select>
                    <button type="submit" class="py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-semibold cursor-pointer">+ Crear Ficha</button>
                </form>

                <!-- Asignar Instructor a Ficha -->
                <form method="POST" action="{{ route('admin.master.assignments.store') }}" class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                    @csrf
                    <select name="ficha_id" class="px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white">
                        @foreach($fichas as $f)
                            <option value="{{ $f->id }}">Ficha {{ $f->code }} ({{ $f->jornada }})</option>
                        @endforeach
                    </select>
                    <select name="instructor_profile_id" class="px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white">
                        @foreach($instructors as $inst)
                            <option value="{{ $inst->id }}">{{ $inst->user?->name }}</option>
                        @endforeach
                    </select>
                    <input name="competency" required placeholder="Competencia a orientar" class="px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white">
                    <input name="schedule" required placeholder="Horario (Ej: Mié-Vie 14:00-18:00)" class="px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white">
                    <input type="number" name="hours_assigned" value="12" min="1" max="40" class="px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white">
                    <button type="submit" class="py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-semibold cursor-pointer">+ Asignar Carga Lectiva</button>
                </form>
            </div>
        </div>
    @endif
@endsection
