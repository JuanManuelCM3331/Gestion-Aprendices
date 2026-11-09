<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>SGA – Sistema de Gestión de Aprendices</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=DM+Mono:wght@400;500&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <style>
        body {
            font-family: 'DM Sans', sans-serif;
            background: radial-gradient(circle at top, #111827 0%, #070912 65%);
        }
        .mono { font-family: 'DM Mono', monospace; }
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(16px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .fade-up { animation: fadeUp 0.5s cubic-bezier(0.16, 1, 0.3, 1) both; }
        .delay-1 { animation-delay: 0.08s; }
        .delay-2 { animation-delay: 0.16s; }
        .delay-3 { animation-delay: 0.24s; }
    </style>
</head>
<body class="text-slate-100 min-h-screen flex flex-col justify-between antialiased">

    <!-- Navbar -->
    <nav class="max-w-6xl w-full mx-auto flex items-center justify-between px-6 py-6 border-b border-slate-800/80 fade-up">
        <div class="flex items-center gap-3">
            <span class="w-2.5 h-2.5 rounded-full bg-indigo-400 animate-pulse"></span>
            <span class="font-bold text-white tracking-wide">SGA</span>
        </div>
        <div>
            @auth
                <a href="{{ route('dashboard') }}" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-medium transition hover:-translate-y-0.5 active:scale-95 inline-block">
                    Ir a mi panel →
                </a>
            @else
                <a href="{{ route('login') }}" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-medium transition hover:-translate-y-0.5 active:scale-95 inline-block">
                    Iniciar sesión →
                </a>
            @endauth
        </div>
    </nav>

    <!-- Hero Central -->
    <main class="max-w-5xl mx-auto px-6 py-16 text-center">
        <span class="mono inline-block px-3.5 py-1 rounded-full bg-indigo-500/10 border border-indigo-500/30 text-indigo-300 text-xs mb-6 fade-up">
            Seguimiento Académico y Bienestar Estudiantil
        </span>

        <h1 class="text-4xl md:text-5xl font-bold text-white tracking-tight mb-5 fade-up delay-1">
            Sistema de Gestión de <span class="text-indigo-400">Aprendices</span>
        </h1>

        <p class="text-slate-400 text-base max-w-2xl mx-auto mb-10 fade-up delay-2">
            Plataforma unificada para la detección temprana de riesgos, trazabilidad de casos de bienestar, control académico por ficha y autogestión del aprendiz.
        </p>

        <div class="mb-16 fade-up delay-2">
            <a href="{{ route('login') }}" class="inline-block px-7 py-3.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-semibold shadow-lg shadow-indigo-600/25 transition duration-200 hover:-translate-y-1 hover:shadow-indigo-500/40 active:scale-95">
                Acceder al sistema
            </a>
        </div>

        <!-- 4 Perfiles Simples con elevación animada -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 text-left fade-up delay-3">
            <div class="p-6 rounded-2xl bg-slate-900/90 border border-slate-800 hover:border-teal-500/50 hover:-translate-y-1.5 hover:shadow-xl transition duration-300">
                <span class="mono text-xs text-teal-400 uppercase">Bienestar</span>
                <h3 class="text-base font-bold text-white mt-1 mb-2">Coordinación</h3>
                <p class="text-xs text-slate-400 leading-relaxed">Semáforo predictivo de riesgo, ajuste de pesos, seguimiento de casos y reportes.</p>
            </div>

            <div class="p-6 rounded-2xl bg-slate-900/90 border border-slate-800 hover:border-indigo-500/50 hover:-translate-y-1.5 hover:shadow-xl transition duration-300">
                <span class="mono text-xs text-indigo-400 uppercase">Instructor</span>
                <h3 class="text-base font-bold text-white mt-1 mb-2">Control Académico</h3>
                <p class="text-xs text-slate-400 leading-relaxed">Registro ágil de observaciones e inasistencias, escalamiento a bienestar y fichas.</p>
            </div>

            <div class="p-6 rounded-2xl bg-slate-900/90 border border-slate-800 hover:border-rose-500/50 hover:-translate-y-1.5 hover:shadow-xl transition duration-300">
                <span class="mono text-xs text-rose-400 uppercase">Administrador</span>
                <h3 class="text-base font-bold text-white mt-1 mb-2">Gobernanza</h3>
                <p class="text-xs text-slate-400 leading-relaxed">Auditoría inmutable con IP, gestión de usuarios, permisos y tablas maestras.</p>
            </div>

            <div class="p-6 rounded-2xl bg-slate-900/90 border border-slate-800 hover:border-amber-500/50 hover:-translate-y-1.5 hover:shadow-xl transition duration-300">
                <span class="mono text-xs text-amber-400 uppercase">Aprendiz</span>
                <h3 class="text-base font-bold text-white mt-1 mb-2">Autogestión</h3>
                <p class="text-xs text-slate-400 leading-relaxed">Consulta de estado actual, carga de excusas o soportes y notificaciones.</p>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="border-t border-slate-800/80 py-6 px-6 text-center text-xs text-slate-500">
        SGA · Sistema de Gestión de Aprendices
    </footer>

</body>
</html>
