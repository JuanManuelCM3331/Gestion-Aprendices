<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>SGA – Sistema de Gestión de Aprendices | Laravel MVC + Capas</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=DM+Mono:wght@400;500&family=DM+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    <style>
        body {
            font-family: 'DM Sans', sans-serif;
            background: radial-gradient(circle at top, #111827 0%, #070912 65%);
            color: #e6eef8;
        }
        .mono { font-family: 'DM Mono', monospace; }
        .glass-card {
            background: linear-gradient(180deg, rgba(15, 23, 42, 0.85), rgba(9, 13, 24, 0.92));
            border: 1px solid rgba(99, 102, 241, 0.22);
            box-shadow: 0 12px 32px rgba(2, 6, 23, 0.55);
            transition: all 0.25s ease;
        }
        .glass-card:hover {
            border-color: rgba(99, 102, 241, 0.65);
            transform: translateY(-4px);
        }
        .divider {
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(99, 102, 241, 0.55), transparent);
        }
    </style>
</head>
<body class="min-h-screen antialiased selection:bg-indigo-500 selection:text-white">

    <!-- NAVBAR -->
    <nav class="max-w-7xl mx-auto flex items-center justify-between px-6 md:px-10 py-6 border-b border-slate-800/80">
        <div class="flex items-center gap-3">
            <span class="w-2.5 h-2.5 rounded-full bg-indigo-400 animate-pulse"></span>
            <span class="mono text-xs text-indigo-400 uppercase tracking-widest">Laravel 12 · MVC + Capas</span>
            <span class="text-slate-600">|</span>
            <span class="text-white font-bold tracking-wide">SGA 2.0</span>
        </div>
        <div class="hidden md:flex items-center gap-7 text-sm text-slate-300">
            <a href="#arquitectura" class="hover:text-white transition">Arquitectura</a>
            <a href="#roles" class="hover:text-white transition">4 Perfiles</a>
            <a href="#servicios" class="hover:text-white transition">Módulos</a>
            @auth
                <a href="{{ route('dashboard') }}"
                   class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-semibold shadow-lg shadow-indigo-600/25 transition">
                    Ir a mi Panel ({{ auth()->user()->username }}) →
                </a>
            @else
                <a href="{{ route('login') }}"
                   class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-semibold shadow-lg shadow-indigo-600/25 transition">
                    Ingresar al Sistema →
                </a>
            @endauth
        </div>
    </nav>

    <!-- HERO -->
    <section class="max-w-6xl mx-auto px-6 md:px-10 pt-20 pb-16 text-center">
        <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-indigo-500/10 border border-indigo-500/30 text-indigo-300 text-xs mono mb-6">
            <span>Refactorización Integral en Laravel · Patrón MVC + Service & Repository Layers</span>
        </div>

        <h1 class="text-4xl md:text-6xl font-bold text-white leading-tight tracking-tight mb-6">
            Sistema Inteligente de Gestión y<br />
            <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-400 via-violet-400 to-emerald-400">
                Permanencia de Aprendices
            </span>
        </h1>

        <p class="text-slate-400 text-base md:text-lg max-w-3xl mx-auto leading-relaxed mb-10">
            Ecosistema predictivo con semaforización automática en tiempo real (deserción, académico y psicosocial),
            trazabilidad de rutas de bienestar, escalamiento temprano, autogestión de evidencias y auditoría criptográfica inmutable.
        </p>

        <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
            <a href="{{ route('login') }}"
               class="px-7 py-3.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-semibold shadow-xl shadow-indigo-600/30 transition hover:scale-105">
                Acceder a la Plataforma
            </a>
            <a href="#roles"
               class="px-7 py-3.5 rounded-xl border border-slate-700 hover:border-indigo-500 text-slate-300 hover:text-white font-medium transition">
                Explorar los 4 Roles
            </a>
        </div>
    </section>

    <div class="divider max-w-6xl mx-auto"></div>

    <!-- MÉTRICAS EN VIVO -->
    <section class="max-w-6xl mx-auto px-6 md:px-10 py-12 grid grid-cols-2 md:grid-cols-6 gap-4">
        <div class="glass-card rounded-2xl p-5 text-center">
            <span class="mono text-[11px] text-indigo-400 uppercase tracking-widest block">Aprendices</span>
            <span class="text-3xl font-extrabold text-white mt-1 block">{{ $metrics['aprendices'] }}</span>
        </div>
        <div class="glass-card rounded-2xl p-5 text-center">
            <span class="mono text-[11px] text-emerald-400 uppercase tracking-widest block">Fichas Activas</span>
            <span class="text-3xl font-extrabold text-white mt-1 block">{{ $metrics['fichas'] }}</span>
        </div>
        <div class="glass-card rounded-2xl p-5 text-center">
            <span class="mono text-[11px] text-amber-400 uppercase tracking-widest block">Casos Bienestar</span>
            <span class="text-3xl font-extrabold text-white mt-1 block">{{ $metrics['casos_bienestar'] }}</span>
        </div>
        <div class="glass-card rounded-2xl p-5 text-center">
            <span class="mono text-[11px] text-sky-400 uppercase tracking-widest block">Observaciones</span>
            <span class="text-3xl font-extrabold text-white mt-1 block">{{ $metrics['observaciones'] }}</span>
        </div>
        <div class="glass-card rounded-2xl p-5 text-center">
            <span class="mono text-[11px] text-rose-400 uppercase tracking-widest block">Logs SHA-256</span>
            <span class="text-3xl font-extrabold text-white mt-1 block">{{ $metrics['auditoria_logs'] }}</span>
        </div>
        <div class="glass-card rounded-2xl p-5 text-center">
            <span class="mono text-[11px] text-violet-400 uppercase tracking-widest block">Usuarios</span>
            <span class="text-3xl font-extrabold text-white mt-1 block">{{ $metrics['usuarios'] }}</span>
        </div>
    </section>

    <div class="divider max-w-6xl mx-auto"></div>

    <!-- ARQUITECTURA MVC + SERVICE + REPOSITORY -->
    <section id="arquitectura" class="max-w-6xl mx-auto px-6 md:px-10 py-16">
        <div class="mb-10">
            <span class="mono text-xs text-indigo-400 uppercase tracking-widest">/ ingeniería de software</span>
            <h2 class="text-3xl font-bold text-white mt-2">Patrón MVC Enriquecido con Arquitectura en Capas</h2>
            <p class="text-sm text-slate-400 mt-1">Separación estricta de responsabilidades para mantenibilidad empresarial y cero deuda técnica.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-5">
            <div class="glass-card rounded-2xl p-6 border-t-4 border-t-indigo-500">
                <span class="mono text-xs text-indigo-400">Capa 1 · Presentación & HTTP</span>
                <h3 class="text-lg font-bold text-white mt-1 mb-2">Vistas Tailwind + Controladores</h3>
                <p class="text-xs text-slate-400 leading-relaxed">
                    Interfaces reactivas en Blade + Tailwind CSS y controladores delgados (<code class="text-indigo-300">app/Http/Controllers</code>) protegidos por <code class="text-indigo-300">RoleAndPermissionMiddleware</code>.
                </p>
            </div>
            <div class="glass-card rounded-2xl p-6 border-t-4 border-t-emerald-500">
                <span class="mono text-xs text-emerald-400">Capa 2 · Lógica de Negocio</span>
                <h3 class="text-lg font-bold text-white mt-1 mb-2">Service Pattern (<code class="text-xs">app/Services</code>)</h3>
                <p class="text-xs text-slate-400 leading-relaxed">
                    Orquesta el motor predictivo <code class="text-emerald-300">RiskEngineService</code>, bloqueo de casos no resueltos, escalamiento temprano y cadena criptográfica de auditoría.
                </p>
            </div>
            <div class="glass-card rounded-2xl p-6 border-t-4 border-t-amber-500">
                <span class="mono text-xs text-amber-400">Capa 3 · Persistencia Desacoplada</span>
                <h3 class="text-lg font-bold text-white mt-1 mb-2">Repository Pattern (<code class="text-xs">Contracts</code>)</h3>
                <p class="text-xs text-slate-400 leading-relaxed">
                    Interfaces en <code class="text-amber-300">app/Repositories/Contracts</code> enlazadas por Inyección de Dependencias con sus implementaciones Eloquent.
                </p>
            </div>
            <div class="glass-card rounded-2xl p-6 border-t-4 border-t-rose-500">
                <span class="mono text-xs text-rose-400">Capa 4 · Dominio & Inmutabilidad</span>
                <h3 class="text-lg font-bold text-white mt-1 mb-2">Modelos Eloquent Protegidos</h3>
                <p class="text-xs text-slate-400 leading-relaxed">
                    Guards de dominio a nivel de ORM en <code class="text-rose-300">AuditLog</code> (inmutable ante edición/borrado) y <code class="text-rose-300">WelfareCase</code> (bloqueo hasta resolución).
                </p>
            </div>
        </div>
    </section>

    <div class="divider max-w-6xl mx-auto"></div>

    <!-- LOS 4 ROLES DEL SISTEMA -->
    <section id="roles" class="max-w-6xl mx-auto px-6 md:px-10 py-16">
        <div class="mb-10">
            <span class="mono text-xs text-indigo-400 uppercase tracking-widest">/ ecosistema de 4 actores</span>
            <h2 class="text-3xl font-bold text-white mt-2">Capacidades Especializadas por Perfil</h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- 1. Gestor de Bienestar -->
            <div class="glass-card rounded-2xl p-7">
                <div class="flex items-center justify-between mb-4">
                    <span class="mono text-xs px-3 py-1 rounded-full bg-teal-500/15 text-teal-300 border border-teal-500/30">
                        1 · NÚCLEO DE INTERVENCIÓN
                    </span>
                    <span class="mono text-xs text-slate-400">user: bienestar</span>
                </div>
                <h3 class="text-xl font-bold text-white mb-3">Gestor de Bienestar / Coordinador</h3>
                <ul class="space-y-2.5 text-sm text-slate-300">
                    <li class="flex items-start gap-2.5"><span class="text-teal-400 font-bold">→</span> <span><strong>Monitoreo predictivo:</strong> Panel con semaforización automática por riesgo de deserción, académico y psicosocial.</span></li>
                    <li class="flex items-start gap-2.5"><span class="text-teal-400 font-bold">→</span> <span><strong>Gestión de variables:</strong> Ajuste en caliente de pesos y umbrales del algoritmo sin tocar código fuente.</span></li>
                    <li class="flex items-start gap-2.5"><span class="text-teal-400 font-bold">→</span> <span><strong>Trazabilidad de casos:</strong> Seguimiento de rutas, compromisos y planes de mejora con bloqueo de borrado hasta resolución.</span></li>
                    <li class="flex items-start gap-2.5"><span class="text-teal-400 font-bold">→</span> <span><strong>Reportes masivos:</strong> Informes consolidados por ficha, programa o jornada con exportación CSV.</span></li>
                </ul>
            </div>

            <!-- 2. Instructor -->
            <div class="glass-card rounded-2xl p-7">
                <div class="flex items-center justify-between mb-4">
                    <span class="mono text-xs px-3 py-1 rounded-full bg-indigo-500/15 text-indigo-300 border border-indigo-500/30">
                        2 · CONTROL ACADÉMICO Y DETECCIÓN TEMPRANA
                    </span>
                    <span class="mono text-xs text-slate-400">user: instructor1</span>
                </div>
                <h3 class="text-xl font-bold text-white mb-3">Instructor</h3>
                <ul class="space-y-2.5 text-sm text-slate-300">
                    <li class="flex items-start gap-2.5"><span class="text-indigo-400 font-bold">→</span> <span><strong>Carga de novedades ágil:</strong> Registro individual o masivo por ficha de observaciones, inasistencias y llamados de atención con recálculo en vivo.</span></li>
                    <li class="flex items-start gap-2.5"><span class="text-indigo-400 font-bold">→</span> <span><strong>Escalamiento directo:</strong> Flujo rápido en 1 clic para derivar casos críticos directamente al equipo de Bienestar.</span></li>
                    <li class="flex items-start gap-2.5"><span class="text-indigo-400 font-bold">→</span> <span><strong>Historial contextual:</strong> Expediente académico y disciplinario del aprendiz acotado a su competencia.</span></li>
                    <li class="flex items-start gap-2.5"><span class="text-indigo-400 font-bold">→</span> <span><strong>Gestión de asignaciones:</strong> Visualización de carga lectiva, horarios y fichas bajo su responsabilidad.</span></li>
                </ul>
            </div>

            <!-- 3. Administrador del Sistema -->
            <div class="glass-card rounded-2xl p-7">
                <div class="flex items-center justify-between mb-4">
                    <span class="mono text-xs px-3 py-1 rounded-full bg-rose-500/15 text-rose-300 border border-rose-500/30">
                        3 · GOBERNANZA Y SEGURIDAD
                    </span>
                    <span class="mono text-xs text-slate-400">user: admin</span>
                </div>
                <h3 class="text-xl font-bold text-white mb-3">Administrador del Sistema</h3>
                <ul class="space-y-2.5 text-sm text-slate-300">
                    <li class="flex items-start gap-2.5"><span class="text-rose-400 font-bold">→</span> <span><strong>Auditoría inmutable:</strong> Rastreo de usuario, fecha, hora, dirección IP, valores anteriores/nuevos y firma SHA-256 sin posibilidad de alteración.</span></li>
                    <li class="flex items-start gap-2.5"><span class="text-rose-400 font-bold">→</span> <span><strong>Control de acceso granular:</strong> Ciclo de vida de usuarios (CRUD), restablecimiento de credenciales y matriz de permisos por módulo.</span></li>
                    <li class="flex items-start gap-2.5"><span class="text-rose-400 font-bold">→</span> <span><strong>Mantenimiento de la plataforma:</strong> Administración de tablas maestras (Programas, Sedes, Periodos Académicos y Fichas).</span></li>
                </ul>
            </div>

            <!-- 4. Aprendiz -->
            <div class="glass-card rounded-2xl p-7">
                <div class="flex items-center justify-between mb-4">
                    <span class="mono text-xs px-3 py-1 rounded-full bg-amber-500/15 text-amber-300 border border-amber-500/30">
                        4 · AUTOGESTIÓN Y TRANSPARENCIA
                    </span>
                    <span class="mono text-xs text-slate-400">user: aprendiz1</span>
                </div>
                <h3 class="text-xl font-bold text-white mb-3">Aprendiz</h3>
                <ul class="space-y-2.5 text-sm text-slate-300">
                    <li class="flex items-start gap-2.5"><span class="text-amber-400 font-bold">→</span> <span><strong>Visibilidad de estado:</strong> Consulta transparente de su semáforo de riesgo y línea de tiempo cronológica de observaciones positivas o negativas.</span></li>
                    <li class="flex items-start gap-2.5"><span class="text-amber-400 font-bold">→</span> <span><strong>Gestión de soportes:</strong> Carga de documentos, excusas médicas y descargos vinculados a cada observación de su expediente.</span></li>
                    <li class="flex items-start gap-2.5"><span class="text-amber-400 font-bold">→</span> <span><strong>Notificaciones internas:</strong> Alertas automáticas sobre cambios en riesgo, citaciones de bienestar y nuevas novedades.</span></li>
                </ul>
            </div>
        </div>
    </section>

    <!-- FOOTER -->
    <footer class="border-t border-slate-800/80 px-6 md:px-10 py-8 text-center text-xs text-slate-500">
        <div class="max-w-6xl mx-auto flex flex-col md:flex-row items-center justify-between gap-4">
            <span class="mono">SGA · Sistema de Gestión de Aprendices · Laravel 12 MVC + Capas</span>
            <span>Desarrollado por Juan Manuel Cardona Molina · Ciberseguridad & DevOps</span>
            <a href="{{ route('login') }}" class="text-indigo-400 hover:text-indigo-300 font-medium">Iniciar Sesión →</a>
        </div>
    </footer>

</body>
</html>
