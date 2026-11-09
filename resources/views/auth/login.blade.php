<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso al Sistema · SGA Laravel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=DM+Mono:wght@400;500&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'DM Sans', sans-serif; }
        .mono { font-family: 'DM Mono', monospace; }
    </style>
</head>
<body class="min-h-screen bg-slate-950 text-slate-100 flex items-center justify-center p-4 relative overflow-hidden">

    <!-- Efectos decorativos de fondo -->
    <div class="absolute inset-0 pointer-events-none">
        <div class="absolute -top-32 -left-32 w-96 h-96 bg-indigo-600/20 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-32 -right-32 w-96 h-96 bg-teal-500/15 rounded-full blur-3xl"></div>
    </div>

    <div class="relative z-10 w-full max-w-xl bg-slate-900/90 backdrop-blur-xl border border-slate-800 rounded-3xl shadow-2xl p-8 md:p-10">
        <!-- Encabezado -->
        <div class="flex items-center justify-between mb-6">
            <a href="{{ route('landing') }}" class="text-xs text-slate-400 hover:text-white transition flex items-center gap-1.5">
                <span>← Volver al inicio</span>
            </a>
            <span class="mono text-[11px] px-3 py-1 rounded-full bg-indigo-500/10 border border-indigo-500/30 text-indigo-300">
                RBAC Granular + Auditoría IP
            </span>
        </div>

        <div class="text-center mb-8">
            <div class="mx-auto mb-3 w-14 h-14 rounded-2xl bg-gradient-to-br from-indigo-500 to-violet-600 text-white flex items-center justify-center text-xl font-extrabold tracking-wider shadow-lg shadow-indigo-500/25">
                SGA
            </div>
            <h1 class="text-2xl md:text-3xl font-bold text-white">Autenticación Centralizada</h1>
            <p class="text-slate-400 text-sm mt-1">Ingresa con tus credenciales o selecciona uno de los 4 perfiles de demostración</p>
        </div>

        @if($errors->any())
            <div class="mb-6 p-4 rounded-2xl bg-rose-500/10 border border-rose-500/30 text-rose-300 text-sm">
                @foreach($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('login.submit') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1.5">
                    Usuario o Correo Institucional
                </label>
                <input id="login-identifier"
                       name="identifier"
                       value="{{ old('identifier') }}"
                       placeholder="Ej: bienestar, instructor1, admin o aprendiz1"
                       required
                       class="w-full px-4 py-3 rounded-xl bg-slate-950 border border-slate-800 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
            </div>

            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1.5">
                    Contraseña
                </label>
                <input id="login-password"
                       name="password"
                       type="password"
                       placeholder="Ingresa tu contraseña"
                       required
                       class="w-full px-4 py-3 rounded-xl bg-slate-950 border border-slate-800 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
            </div>

            <button type="submit"
                    class="w-full py-3.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-semibold tracking-wide shadow-lg shadow-indigo-600/25 transition hover:-translate-y-0.5 cursor-pointer">
                Iniciar Sesión en SGA →
            </button>
        </form>

        <!-- Selector Rápido de los 4 Perfiles Solicitados -->
        <div class="mt-8 pt-6 border-t border-slate-800">
            <div class="flex items-center justify-between mb-3">
                <span class="mono text-xs text-slate-400 uppercase tracking-wider">Acceso Rápido (4 Perfiles del Proyecto)</span>
                <span class="text-[11px] text-indigo-400">Clic para autocompletar</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                <button type="button" onclick="fillRole('bienestar', 'bienestar123')"
                        class="group p-3 rounded-xl bg-slate-950/90 border border-slate-800 hover:border-teal-500/60 hover:bg-teal-500/5 text-left transition cursor-pointer">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-teal-300">1. Gestor de Bienestar</span>
                        <span class="mono text-[10px] text-slate-500">bienestar / bienestar123</span>
                    </div>
                    <p class="text-[11px] text-slate-400 mt-1">Semáforo predictivo, pesos del algoritmo, casos y reportes</p>
                </button>

                <button type="button" onclick="fillRole('instructor1', '123')"
                        class="group p-3 rounded-xl bg-slate-950/90 border border-slate-800 hover:border-indigo-500/60 hover:bg-indigo-500/5 text-left transition cursor-pointer">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-indigo-300">2. Instructor Académico</span>
                        <span class="mono text-[10px] text-slate-500">instructor1 / 123</span>
                    </div>
                    <p class="text-[11px] text-slate-400 mt-1">Carga masiva/individual, escalamiento directo y horarios</p>
                </button>

                <button type="button" onclick="fillRole('admin', 'admin123')"
                        class="group p-3 rounded-xl bg-slate-950/90 border border-slate-800 hover:border-rose-500/60 hover:bg-rose-500/5 text-left transition cursor-pointer">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-rose-300">3. Administrador Sistema</span>
                        <span class="mono text-[10px] text-slate-500">admin / admin123</span>
                    </div>
                    <p class="text-[11px] text-slate-400 mt-1">Auditoría inmutable IP/SHA-256, permisos y tablas maestras</p>
                </button>

                <button type="button" onclick="fillRole('aprendiz1', '123')"
                        class="group p-3 rounded-xl bg-slate-950/90 border border-slate-800 hover:border-amber-500/60 hover:bg-amber-500/5 text-left transition cursor-pointer">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-amber-300">4. Aprendiz</span>
                        <span class="mono text-[10px] text-slate-500">aprendiz1 / 123</span>
                    </div>
                    <p class="text-[11px] text-slate-400 mt-1">Visibilidad de estado, carga de soportes/excusas y alertas</p>
                </button>
            </div>
        </div>
    </div>

    <script>
        function fillRole(user, pass) {
            document.getElementById('login-identifier').value = user;
            document.getElementById('login-password').value = pass;
            document.getElementById('login-identifier').focus();
        }
    </script>
</body>
</html>
