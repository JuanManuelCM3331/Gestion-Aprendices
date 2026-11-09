<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>SGA – Sistema de Gestión de Aprendices</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link
        href="https://fonts.googleapis.com/css2?family=DM+Mono:wght@400;500&family=DM+Sans:wght@300;400;500;600&display=swap"
        rel="stylesheet" />
    <style>
        :root{
            --bg-1: #070912; /* fondo principal */
            --bg-2: #0b1220; /* fondo secundario */
            --muted: #94a3b8; /* texto secundario */
            --accent: #6366f1; /* indigo */
            --accent-600: #4f46e5;
            --surface: rgba(12,15,25,0.95);
            --card-text: #e6eef8;
        }
        * {
            font-family: 'DM Sans', sans-serif;
        }

        .mono {
            font-family: 'DM Mono', monospace;
        }

        body {
            background: linear-gradient(180deg,var(--bg-1), var(--bg-2));
            color: var(--card-text);
            -webkit-font-smoothing:antialiased;
            -moz-osx-font-smoothing:grayscale;
        }

        /* Barra lateral decorativa igual que el dashboard */
        .side-accent {
            border-left: 3px solid #6366f1;
        }

        /* Tarjeta de servicio */
        .service-card {
            background: linear-gradient(180deg, rgba(9,12,20,0.9), rgba(14,18,28,0.9));
            color: var(--card-text);
            border: 1px solid rgba(99, 102, 241, 0.18);
            box-shadow: 0 8px 20px rgba(2,6,23,0.45);
            transition: border-color 0.2s, transform 0.2s, box-shadow 0.2s;
            padding: 1rem;
        }

        .service-card:hover {
            border-color: rgba(99, 102, 241, 0.6);
            transform: translateY(-4px);
            box-shadow: 0 14px 30px rgba(2,6,23,0.6);
        }

        .service-card h3,
        .service-card p,
        .service-card a,
        .service-card span {
            color: var(--card-text);
        }

        /* Titulares y párrafos */
        h1, h2, h3 { color: #ffffff; }
        p, .text-slate-400 { color: var(--muted); }

        /* Botones estándar */
        .btn-primary { background: linear-gradient(90deg,var(--accent-600), var(--accent)); color: #fff; }
        .btn-outline { border: 1px solid rgba(148,163,184,0.12); color: var(--muted); }

        /* Badges */
        .badge-sys { background: rgba(99,102,241,0.12); color: var(--accent); border: 1px solid rgba(99,102,241,0.18); }

        /* Roles: apariencia uniforme coherente con cartas */
        section#roles .rounded-xl.p-6 {
            background: linear-gradient(180deg, rgba(12,15,25,0.6), rgba(10,12,18,0.6));
            border: 1px solid rgba(148,163,184,0.06);
        }

        section#roles h2 { color: #ffffff; }

        section#roles .badge-sys, section#roles .mono[class*="bg-"] {
            color: #e6eef8 !important;
        }

        /* Animación de entrada escalonada */
        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .fade-up {
            animation: fadeUp 0.6s ease both;
        }

        .delay-1 {
            animation-delay: 0.1s;
        }

        .delay-2 {
            animation-delay: 0.2s;
        }

        .delay-3 {
            animation-delay: 0.3s;
        }

        .delay-4 {
            animation-delay: 0.4s;
        }

        .delay-5 {
            animation-delay: 0.5s;
        }

        .delay-6 {
            animation-delay: 0.6s;
        }

        /* Línea separadora con degradado */
        .divider {
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(99, 102, 241, 0.6), transparent);
        }

        /* Pulse en el indicador activo */
        @keyframes pulse-dot {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.3;
            }
        }

        .pulse-dot {
            animation: pulse-dot 2s ease-in-out infinite;
        }

        /* Navbar link overrides para mejor legibilidad */
        nav {
            color: rgba(203,213,225,0.95);
        }

        nav a {
            color: rgba(203,213,225,0.9);
            text-decoration: none;
        }

        nav a:hover {
            color: #ffffff;
        }
    </style>
</head>

<body class="bg-white text-black bg-grid min-h-screen">

    <!-- Glow de fondo -->
    <div class="relative overflow-hidden">

        <!-- ====== NAVBAR ====== -->
        <nav class="relative z-10 flex items-center justify-between px-8 py-5 border-b border-slate-800 fade-up">
            <div class="flex items-center gap-3">
                <!-- Indicador activo -->
                <span class="w-2 h-2 rounded-full bg-indigo-400 pulse-dot"></span>
                <span class="mono text-xs text-indigo-400 uppercase tracking-widest">Sistema</span>
                <span class="text-slate-600">|</span>
                <span class="text-white font-semibold tracking-wide">SGA</span>
            </div>
            <div class="hidden md:flex items-center gap-6 text-sm text-slate-400">
                <a href="#servicios" class="hover:text-white transition-colors">Servicios</a>
                <a href="#roles" class="hover:text-white transition-colors">Roles</a>
                <a href="#contacto" class="hover:text-white transition-colors">Contacto</a>
                <a href="dashboard.php"
                    class="px-4 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-medium transition-colors">
                    Ingresar al sistema →
                </a>
            </div>
        </nav>

        <!-- ====== HERO ====== -->
    <section class="relative z-10 max-w-5xl mx-auto px-8 pt-24 pb-20 text-center">
            <h1 class="fade-up delay-2 text-5xl md:text-6xl font-semibold text-white leading-tight tracking-tight mb-6">
                Sistema de Gestión<br />
                <span class="text-indigo-400">de Aprendices</span>
            </h1>

            <p class="fade-up delay-3 text-slate-400 text-lg max-w-2xl mx-auto leading-relaxed mb-10">
                Plataforma centralizada para el seguimiento, valoración de riesgos y gestión de aprendices, instructores
                y observaciones en contextos educativos.
            </p>

            <div class="fade-up delay-4 flex flex-col sm:flex-row items-center justify-center gap-4">
                <a href="dashboard.php"
                    class="px-6 py-3 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white font-medium transition-all hover:scale-105 active:scale-95">
                    Acceder al sistema
                </a>
                <a href="#servicios"
                    class="px-6 py-3 rounded-lg border border-slate-700 hover:border-indigo-500 text-slate-300 hover:text-white font-medium transition-all">
                    Ver servicios
                </a>
            </div>
        </section>

        <div class="divider mx-8 fade-up delay-5"></div>

        <!-- ====== MÉTRICAS ====== -->
        <section
            class="relative z-10 max-w-5xl mx-auto px-8 py-12 grid grid-cols-2 md:grid-cols-4 gap-6 fade-up delay-5">
            <div class="text-center side-accent pl-4">
                <div class="mono text-xs text-indigo-400 uppercase tracking-widest">Aprendices</div>
            </div>
            <div class="text-center side-accent pl-4">
                <div class="mono text-xs text-indigo-400 uppercase tracking-widest">Instructores</div>
            </div>
            <div class="text-center side-accent pl-4">
                <div class="mono text-xs text-indigo-400 uppercase tracking-widest">Riesgos</div>
            </div>
            <div class="text-center side-accent pl-4">
                <div class="mono text-xs text-indigo-400 uppercase tracking-widest">Observaciones</div>
            </div>
        </section>

        <div class="divider mx-8"></div>

        <!-- ====== SERVICIOS ====== -->
        <section id="servicios" class="relative z-10 max-w-5xl mx-auto px-8 py-16">
            <div class="mb-10">
                <span class="mono text-xs text-indigo-400 uppercase tracking-widest">/ servicios</span>
                <h2 class="text-2xl font-semibold text-white mt-2">¿Qué ofrece el sistema?</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">

                <div class="service-card rounded-xl p-6">

                    <h3 class="text-white font-medium mb-2">Panel de control</h3>
                    <p class="text-slate-400 text-sm leading-relaxed">Visualización en tiempo real de métricas:
                        aprendices, instructores, riesgos y usuarios activos del sistema.</p>
                </div>

                <div class="service-card rounded-xl p-6">

                    <h3 class="text-white font-medium mb-2">Valoración de riesgos</h3>
                    <p class="text-slate-400 text-sm leading-relaxed">Clasificación automática de riesgos por nivel
                        (bajo, medio, alto) con justificación y seguimiento por aprendiz.</p>
                </div>

                <div class="service-card rounded-xl p-6">

                    <h3 class="text-white font-medium mb-2">Gestión de observaciones</h3>
                    <p class="text-slate-400 text-sm leading-relaxed">Los instructores pueden registrar observaciones
                        sobre cada aprendiz, visibles desde su panel personal.</p>
                </div>
                <div class="service-card rounded-xl p-6">
                    <h3 class="text-white font-medium mb-2">Administración de usuarios</h3>
                    <p class="text-slate-400 text-sm leading-relaxed">Creación, edición y eliminación de usuarios con
                        roles diferenciados: administrador, instructor y aprendiz.</p>
                </div>

                <div class="service-card rounded-xl p-6">
                    <h3 class="text-white font-medium mb-2">Seguridad y auditoría</h3>
                    <p class="text-slate-400 text-sm leading-relaxed">Autenticación por sesión, contraseñas hasheadas,
                        sentencias preparadas y registro completo de auditoría.</p>
                </div>

                <div class="service-card rounded-xl p-6">
                    <h3 class="text-white font-medium mb-2">Despliegue con Docker</h3>
                    <p class="text-slate-400 text-sm leading-relaxed">Infraestructura contenerizada con PHP 8.2 + Apache
                        y MySQL 8.0, lista para ejecutar con un solo comando.</p>
                </div>

            </div>
        </section>

        <div class="divider mx-8"></div>

        <!-- ====== ROLES ====== -->
        <section id="roles" class="relative z-10 max-w-5xl mx-auto px-8 py-16">
            <div class="mb-10">
                <span class="mono text-xs text-indigo-400 uppercase tracking-widest">/ roles del sistema</span>
                <h2 class="text-2xl font-semibold text-white mt-2">Acceso por perfil</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">

                <div class="service-card rounded-xl p-6 bg-indigo-600/10">
                    <div class="flex items-center gap-3 mb-4">
                        <span class="badge-sys mono text-xs px-2 py-1 rounded">admin</span>
                    </div>
                    <h3 class="text-white font-medium mb-3">Administrador</h3>
                    <ul class="space-y-2 text-sm text-slate-400">
                        <li class="flex items-center gap-2"><span class="text-indigo-400">→</span> Gestión completa de
                            usuarios</li>
                        <li class="flex items-center gap-2"><span class="text-indigo-400">→</span> Administrar
                            aprendices e instructores</li>
                        <li class="flex items-center gap-2"><span class="text-indigo-400">→</span> Acceso al registro de
                            auditoría</li>
                        <li class="flex items-center gap-2"><span class="text-indigo-400">→</span> Control total de
                            riesgos</li>
                    </ul>
                </div>

                <div class="service-card rounded-xl p-6 bg-slate-800/60 border border-slate-700">
                    <div class="flex items-center gap-3 mb-4">
                        <span
                            class="mono text-xs px-2 py-1 rounded bg-slate-700 text-slate-300 border border-slate-600">instructor</span>
                    </div>
                    <h3 class="text-white font-medium mb-3">Instructor</h3>
                    <ul class="space-y-2 text-sm text-slate-400">
                        <li class="flex items-center gap-2"><span class="text-slate-500">→</span> Ver listado de
                            aprendices</li>
                        <li class="flex items-center gap-2"><span class="text-slate-500">→</span> Crear observaciones
                        </li>
                        <li class="flex items-center gap-2"><span class="text-slate-500">→</span> Consultar riesgos
                            activos</li>
                    </ul>
                </div>

                <div class="service-card rounded-xl p-6 bg-slate-800/60 border border-slate-700">
                    <div class="flex items-center gap-3 mb-4">
                        <span
                            class="mono text-xs px-2 py-1 rounded bg-slate-700 text-slate-300 border border-slate-600">aprendiz</span>
                    </div>
                    <h3 class="text-white font-medium mb-3">Aprendiz</h3>
                    <ul class="space-y-2 text-sm text-slate-400">
                        <li class="flex items-center gap-2"><span class="text-slate-500">→</span> Ver observaciones
                            propias</li>
                        <li class="flex items-center gap-2"><span class="text-slate-500">→</span> Consultar información
                            de riesgo</li>
                    </ul>
                </div>

            </div>
        </section>

        <div class="divider mx-8"></div>

        <!-- ====== CONTACTO ====== -->
        <section id="contacto" class="relative z-10 max-w-5xl mx-auto px-8 py-16">
            <div class="mb-10">
                <span class="mono text-xs text-indigo-400 uppercase tracking-widest">/ contacto</span>
                <h2 class="text-5xl font-bold text-white mt-2">¿Necesitas soporte?</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-start">
                <div>
                    <p class="text-slate-400 leading-relaxed mb-6">
                        Para reportar problemas, solicitar acceso o hacer preguntas técnicas, crea un issue directamente
                        en el repositorio del proyecto.
                    </p>
                    <div class="space-y-4">
                        <div class="flex items-center gap-3 side-accent pl-4">
                            <div>
                                <div class="text-xs text-slate-500 mono">Desarrollador</div>
                                <div class="text-white text-sm font-medium">Juan Manuel Cardona Molina</div>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 side-accent pl-4">
                            <div>
                                <div class="text-xs text-slate-500 mono">Repositorio</div>
                                <a href="https://github.com/JuanManuelCM3331/New-Gestion-Aprendices"
                                    class="text-indigo-400 text-sm hover:text-indigo-300 transition-colors">
                                    github.com/JuanManuelCM3331/New-Gestion-Aprendices
                                </a>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 side-accent pl-4">
                            <div>
                                <div class="text-xs text-slate-500 mono">Stack</div>
                                <div class="text-white text-sm font-medium">PHP 8.2 · MySQL 8.0 · Apache · Docker</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="service-card rounded-xl p-6">
                    <div class="mono text-xs text-indigo-400 mb-4">$ acceso rápido</div>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between text-sm py-2 border-b border-slate-800">
                            <span class="text-slate-400">URL local</span>
                            <span class="mono text-indigo-300">localhost:8080</span>
                        </div>
                        <div class="flex items-center justify-between text-sm py-2 border-b border-slate-800">
                            <span class="text-slate-400">Usuario por defecto</span>
                            <span class="mono text-indigo-300">admin</span>
                        </div>
                        <div class="flex items-center justify-between text-sm py-2 border-b border-slate-800">
                            <span class="text-slate-400">Contraseña</span>
                            <span class="mono text-indigo-300">admin123</span>
                        </div>
                        <div class="flex items-center justify-between text-sm py-2">
                            <span class="text-slate-400">Levantar sistema</span>
                            <span class="mono text-indigo-300">docker compose up</span>
                        </div>
                    </div>
                    <a href="dashboard.php"
                        class="mt-6 block text-center px-4 py-2.5 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-medium transition-all hover:scale-105 active:scale-95">
                        Ingresar →
                    </a>
                </div>
            </div>
        </section>

        <!-- ====== FOOTER ====== -->
        <footer
            class="relative z-10 border-t border-slate-800 px-8 py-6 flex flex-col md:flex-row items-center justify-between gap-4 text-xs text-slate-600">
            <span class="mono">SGA · Sistema de Gestión de Aprendices</span>
            <span>Desarrollado por Juan Manuel Cardona Molina</span>
            <a href="https://github.com/JuanManuelCM3331/New-Gestion-Aprendices"
                class="hover:text-slate-400 transition-colors">GitHub →</a>
        </footer>

    </div><!-- /overflow-hidden -->
</body>

</html>