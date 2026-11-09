@extends('layouts.app')

@section('title', 'Administración · SGA')
@section('header_title', 'Administración y Seguridad')
@section('header_subtitle', 'Auditoría inmutable, gestión de usuarios y tablas maestras')

@section('content')
    <!-- ================================================================= -->
    <!-- PESTAÑA 1: AUDITORÍA INMUTABLE -->
    <!-- ================================================================= -->
    @if($activeTab === 'auditoria')
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6">
            <div class="flex flex-wrap items-center justify-between gap-4 mb-5">
                <div>
                    <h2 class="text-base font-bold text-white">Registro de Auditoría Inmutable</h2>
                    <p class="text-xs text-slate-400">Historial protegido contra modificaciones o eliminaciones ({{ $chainIntegrity['checked_count'] }} registros verificados).</p>
                </div>
                <span class="px-3 py-1 rounded-full text-xs font-semibold bg-emerald-500/15 text-emerald-300 border border-emerald-500/30">
                    ✓ Cadena de Integridad Verificada
                </span>
            </div>

            <div class="overflow-x-auto rounded-xl border border-slate-800">
                <table class="w-full text-sm text-left">
                    <thead class="bg-slate-950 text-slate-400 text-xs uppercase mono">
                        <tr>
                            <th class="px-4 py-3">Fecha</th>
                            <th class="px-4 py-3">Usuario</th>
                            <th class="px-4 py-3">IP</th>
                            <th class="px-4 py-3">Módulo</th>
                            <th class="px-4 py-3">Acción</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800">
                        @foreach($auditLogs as $log)
                            <tr class="hover:bg-slate-800/40">
                                <td class="px-4 py-3 mono text-xs text-slate-400 whitespace-nowrap">{{ $log->created_at?->format('Y-m-d H:i') }}</td>
                                <td class="px-4 py-3 font-medium text-white">{{ $log->username }} <span class="text-xs text-slate-400">({{ $log->role }})</span></td>
                                <td class="px-4 py-3 mono text-xs text-emerald-300">{{ $log->ip_address }}</td>
                                <td class="px-4 py-3 text-xs font-medium text-indigo-300">{{ $log->module }}</td>
                                <td class="px-4 py-3 text-slate-300">{{ $log->action }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <!-- ================================================================= -->
    <!-- PESTAÑA 2: USUARIOS Y PERMISOS -->
    <!-- ================================================================= -->
    @if($activeTab === 'usuarios')
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Crear Usuario -->
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 h-fit">
                <h2 class="text-base font-bold text-white mb-4">Crear Usuario</h2>
                <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-3 text-sm">
                    @csrf
                    <div>
                        <label class="block text-xs text-slate-400 mb-1">Nombre completo</label>
                        <input name="name" required class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white">
                    </div>
                    <div>
                        <label class="block text-xs text-slate-400 mb-1">Usuario</label>
                        <input name="username" required class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white">
                    </div>
                    <div>
                        <label class="block text-xs text-slate-400 mb-1">Correo</label>
                        <input type="email" name="email" required class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white">
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-xs text-slate-400 mb-1">Contraseña</label>
                            <input type="password" name="password" required class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white">
                        </div>
                        <div>
                            <label class="block text-xs text-slate-400 mb-1">Rol</label>
                            <select name="role" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white">
                                <option value="bienestar">Bienestar</option>
                                <option value="instructor">Instructor</option>
                                <option value="aprendiz">Aprendiz</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="w-full py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-semibold transition cursor-pointer">
                        Crear Usuario
                    </button>
                </form>
            </div>

            <!-- Tabla de Usuarios -->
            <div class="lg:col-span-2 bg-slate-900 border border-slate-800 rounded-2xl p-6 space-y-3">
                <h2 class="text-base font-bold text-white mb-2">Usuarios Registrados</h2>

                @foreach($users as $u)
                    <div class="p-4 rounded-xl bg-slate-950 border border-slate-800">
                        <div class="flex items-center justify-between gap-2">
                            <div>
                                <span class="font-bold text-white">{{ $u->name }}</span>
                                <span class="mono text-xs text-slate-400 ml-1">({{ $u->username }})</span>
                                <span class="ml-2 px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-500/15 text-indigo-300">{{ $u->role }}</span>
                            </div>

                            @if($u->id !== auth()->id())
                                <form method="POST" action="{{ route('admin.users.destroy', $u->id) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-3 py-1 rounded-lg bg-rose-500/15 text-rose-300 hover:bg-rose-500/25 text-xs font-medium cursor-pointer">
                                        Eliminar
                                    </button>
                                </form>
                            @endif
                        </div>

                        <details class="mt-3 pt-3 border-t border-slate-800 text-xs">
                            <summary class="text-indigo-400 font-medium cursor-pointer">Editar acceso, clave o permisos</summary>
                            <form method="POST" action="{{ route('admin.users.update', $u->id) }}" class="mt-3 space-y-3">
                                @csrf
                                @method('PUT')
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                                    <select name="role" class="px-3 py-1.5 rounded-lg bg-slate-900 border border-slate-700 text-white">
                                        @foreach(['admin' => 'Admin', 'bienestar' => 'Bienestar', 'instructor' => 'Instructor', 'aprendiz' => 'Aprendiz'] as $rk => $rv)
                                            <option value="{{ $rk }}" @selected($u->role === $rk)>{{ $rv }}</option>
                                        @endforeach
                                    </select>
                                    <select name="is_active" class="px-3 py-1.5 rounded-lg bg-slate-900 border border-slate-700 text-white">
                                        <option value="1" @selected($u->is_active)>Activo</option>
                                        <option value="0" @selected(!$u->is_active)>Bloqueado</option>
                                    </select>
                                    <input type="password" name="new_password" placeholder="Nueva contraseña (opcional)" class="px-3 py-1.5 rounded-lg bg-slate-900 border border-slate-700 text-white">
                                </div>
                                <div class="grid grid-cols-2 gap-1.5 p-3 rounded-lg bg-slate-900 border border-slate-800">
                                    @foreach($availablePermissions as $pk => $pv)
                                        <label class="flex items-center gap-1.5 text-slate-300">
                                            <input type="checkbox" name="permissions[]" value="{{ $pk }}" @checked(in_array($pk, $u->permissions ?? [], true)) class="accent-indigo-500">
                                            <span>{{ $pv }}</span>
                                        </label>
                                    @endforeach
                                </div>
                                <button type="submit" class="px-4 py-1.5 rounded-lg bg-indigo-600 text-white font-medium cursor-pointer">Guardar cambios</button>
                            </form>
                        </details>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- ================================================================= -->
    <!-- PESTAÑA 3: TABLAS MAESTRAS -->
    <!-- ================================================================= -->
    @if($activeTab === 'maestras')
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Programas -->
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6">
                <h2 class="text-base font-bold text-white mb-3">Programas de Formación</h2>
                <form method="POST" action="{{ route('admin.master.programs.store') }}" class="flex gap-2 mb-4 text-sm">
                    @csrf
                    <input name="code" required placeholder="Código" class="w-24 px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white">
                    <input name="name" required placeholder="Nombre del programa" class="flex-1 px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white">
                    <input type="hidden" name="level" value="Tecnólogo">
                    <input type="hidden" name="duration_trimesters" value="6">
                    <button type="submit" class="px-4 py-2 rounded-xl bg-indigo-600 text-white font-medium cursor-pointer">Agregar</button>
                </form>
                <ul class="divide-y divide-slate-800 text-sm">
                    @foreach($programs as $p)
                        <li class="py-2.5 flex justify-between">
                            <span class="font-medium text-slate-200">{{ $p->name }}</span>
                            <span class="mono text-xs text-slate-400">Cód: {{ $p->code }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>

            <!-- Sedes -->
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6">
                <h2 class="text-base font-bold text-white mb-3">Sedes</h2>
                <form method="POST" action="{{ route('admin.master.sedes.store') }}" class="flex gap-2 mb-4 text-sm">
                    @csrf
                    <input name="code" required placeholder="Código" class="w-24 px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white">
                    <input name="name" required placeholder="Nombre de sede" class="flex-1 px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white">
                    <input name="city" required placeholder="Ciudad" class="w-28 px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white">
                    <button type="submit" class="px-4 py-2 rounded-xl bg-indigo-600 text-white font-medium cursor-pointer">Agregar</button>
                </form>
                <ul class="divide-y divide-slate-800 text-sm">
                    @foreach($sedes as $s)
                        <li class="py-2.5 flex justify-between">
                            <span class="font-medium text-slate-200">{{ $s->name }}</span>
                            <span class="text-xs text-slate-400">{{ $s->city }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>

            <!-- Periodos Académicos -->
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6">
                <h2 class="text-base font-bold text-white mb-3">Periodos Académicos</h2>
                <form method="POST" action="{{ route('admin.master.periods.store') }}" class="grid grid-cols-2 gap-2 mb-4 text-sm">
                    @csrf
                    <input name="code" required placeholder="Ej: 2027-I" class="px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white">
                    <input name="name" required placeholder="Nombre" class="px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white">
                    <input type="date" name="start_date" required value="2027-01-15" class="px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white">
                    <div class="flex gap-2">
                        <input type="date" name="end_date" required value="2027-06-30" class="flex-1 px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white">
                        <button type="submit" class="px-4 py-2 rounded-xl bg-indigo-600 text-white font-medium cursor-pointer">+</button>
                    </div>
                </form>
                <ul class="divide-y divide-slate-800 text-sm">
                    @foreach($periods as $per)
                        <li class="py-2.5 flex justify-between">
                            <span class="font-medium text-slate-200">{{ $per->code }} · {{ $per->name }}</span>
                            <span class="mono text-xs text-slate-400">{{ $per->start_date->format('Y-m-d') }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>

            <!-- Fichas -->
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6">
                <h2 class="text-base font-bold text-white mb-3">Fichas / Grupos</h2>
                <form method="POST" action="{{ route('admin.master.fichas.store') }}" class="grid grid-cols-2 gap-2 mb-4 text-sm">
                    @csrf
                    <input name="code" required placeholder="N° Ficha" class="px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white">
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
                    <div class="flex gap-2">
                        <select name="jornada" class="flex-1 px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white">
                            <option value="Mañana">Mañana</option>
                            <option value="Tarde">Tarde</option>
                            <option value="Noche">Noche</option>
                            <option value="Fines de Semana">Fines de Semana</option>
                        </select>
                        <input type="hidden" name="academic_period_id" value="{{ $periods->first()?->id }}">
                        <button type="submit" class="px-4 py-2 rounded-xl bg-indigo-600 text-white font-medium cursor-pointer">+</button>
                    </div>
                </form>
                <ul class="divide-y divide-slate-800 text-sm">
                    @foreach($fichas as $f)
                        <li class="py-2.5 flex justify-between">
                            <span class="font-medium text-slate-200">Ficha {{ $f->code }} ({{ $f->jornada }})</span>
                            <span class="mono text-xs text-slate-400">{{ $f->apprentices_count }} aprendices</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif
@endsection
