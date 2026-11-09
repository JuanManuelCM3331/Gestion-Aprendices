<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión · SGA</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=DM+Mono:wght@400;500&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'DM Sans', sans-serif; }
        .mono { font-family: 'DM Mono', monospace; }
    </style>
</head>
<body class="min-h-screen bg-slate-950 text-slate-100 flex items-center justify-center p-4">

    <div class="w-full max-w-md bg-slate-900 border border-slate-800 rounded-3xl shadow-2xl p-8">
        <!-- Header -->
        <div class="text-center mb-8">
            <a href="{{ route('landing') }}" class="mx-auto mb-3 w-14 h-14 rounded-2xl bg-gradient-to-br from-indigo-500 to-violet-600 text-white flex items-center justify-center text-lg font-bold shadow-lg shadow-indigo-500/25">
                SGA
            </a>
            <h1 class="text-2xl font-bold text-white">Iniciar Sesión</h1>
            <p class="text-slate-400 text-sm mt-1">Sistema de Gestión de Aprendices</p>
        </div>

        @if($errors->any())
            <div class="mb-5 px-4 py-3 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-300 text-sm">
                @foreach($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('login.submit') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-medium text-slate-400 mb-1.5">Usuario</label>
                <input id="login-identifier"
                       name="identifier"
                       value="{{ old('identifier') }}"
                       placeholder="Ingresa tu usuario"
                       required
                       class="w-full px-4 py-2.5 rounded-xl border border-slate-800 bg-slate-950 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition">
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-400 mb-1.5">Contraseña</label>
                <input id="login-password"
                       name="password"
                       type="password"
                       placeholder="Ingresa tu contraseña"
                       required
                       class="w-full px-4 py-2.5 rounded-xl border border-slate-800 bg-slate-950 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition">
            </div>

            <button type="submit"
                    class="w-full py-3 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-semibold shadow-lg shadow-indigo-600/20 transition cursor-pointer">
                Entrar al sistema
            </button>
        </form>

        <!-- Usuarios de prueba en 1 clic -->
        <div class="mt-6 pt-5 border-t border-slate-800">
            <p class="mono text-xs text-slate-400 uppercase tracking-wider mb-3">Acceso rápido de prueba</p>
            <div class="grid grid-cols-2 gap-2">
                <button type="button" onclick="fillRole('bienestar', 'bienestar123')"
                        class="p-2.5 rounded-xl bg-slate-950 border border-slate-800 hover:border-teal-500/50 text-left transition cursor-pointer">
                    <span class="block text-xs font-bold text-teal-300">Bienestar</span>
                    <span class="mono block text-[11px] text-slate-500">bienestar / bienestar123</span>
                </button>
                <button type="button" onclick="fillRole('instructor1', '123')"
                        class="p-2.5 rounded-xl bg-slate-950 border border-slate-800 hover:border-indigo-500/50 text-left transition cursor-pointer">
                    <span class="block text-xs font-bold text-indigo-300">Instructor</span>
                    <span class="mono block text-[11px] text-slate-500">instructor1 / 123</span>
                </button>
                <button type="button" onclick="fillRole('admin', 'admin123')"
                        class="p-2.5 rounded-xl bg-slate-950 border border-slate-800 hover:border-rose-500/50 text-left transition cursor-pointer">
                    <span class="block text-xs font-bold text-rose-300">Administrador</span>
                    <span class="mono block text-[11px] text-slate-500">admin / admin123</span>
                </button>
                <button type="button" onclick="fillRole('aprendiz1', '123')"
                        class="p-2.5 rounded-xl bg-slate-950 border border-slate-800 hover:border-amber-500/50 text-left transition cursor-pointer">
                    <span class="block text-xs font-bold text-amber-300">Aprendiz</span>
                    <span class="mono block text-[11px] text-slate-500">aprendiz1 / 123</span>
                </button>
            </div>
        </div>
    </div>

    <script>
        function fillRole(user, pass) {
            document.getElementById('login-identifier').value = user;
            document.getElementById('login-password').value = pass;
        }
    </script>
</body>
</html>
