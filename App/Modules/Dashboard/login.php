<div class="min-h-screen w-full flex items-center justify-center bg-slate-100 px-4 relative overflow-hidden">
    <!-- Fondo decorativo -->
    <div class="absolute inset-0 pointer-events-none">
        <div class="absolute -top-20 -left-20 w-72 h-72 bg-blue-200/30 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-20 -right-20 w-80 h-80 bg-slate-300/30 rounded-full blur-3xl"></div>
    </div> <!-- Login -->
    <div
        class="relative z-10 w-full max-w-md bg-white/90 backdrop-blur-sm border border-slate-200 rounded-3xl shadow-2xl p-8 transition duration-300 hover:shadow-blue-100">
        <!-- Header -->
        <div class="text-center mb-8">
            <div
                class="mx-auto mb-4 w-16 h-16 rounded-2xl bg-slate-900 text-white flex items-center justify-center text-xl font-extrabold tracking-wider shadow-lg">
                SGA
            </div>
            <h2 class="text-3xl font-bold text-slate-800"> SGA Login </h2>
            <p class="text-slate-500 mt-2 text-sm"> Sistema de Gestión Académica </p>
        </div> <!-- Error --> <?php if (!empty($loginError)): ?>
            <div class="mb-5 px-4 py-3 rounded-xl bg-red-50 border border-red-200 text-red-700 text-sm">
                <?= htmlspecialchars($loginError) ?>
            </div> <?php endif; ?> <!-- Formulario -->
        <form method="POST" action="?action=login" class="space-y-5">
            <div>
                <label class="block text-sm font-medium text-slate-600 mb-1"> Usuario </label>
                <input id="login-user" name="user" placeholder="Ingresa tu usuario" required
                    class="w-full px-4 py-3 rounded-xl border border-slate-300 bg-slate-50 transition focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 hover:border-blue-400 hover:bg-white">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-600 mb-1"> Contraseña </label>
                <input id="login-pass" name="pass" type="password" placeholder="Ingresa tu contraseña" required
                    class="w-full px-4 py-3 rounded-xl border border-slate-300 bg-slate-50 transition focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 hover:border-blue-400 hover:bg-white">
            </div>
            <button type="submit"
                class="w-full py-3 rounded-xl bg-slate-900 text-white font-semibold tracking-wide transition duration-300 hover:bg-blue-600 hover:shadow-lg hover:-translate-y-0.5 cursor-pointer">
                Entrar al sistema
            </button>
        </form>

        <!-- Botones de autocompletado para usuarios de prueba -->
        <div class="mt-6 pt-5 border-t border-slate-200">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">
                    Usuarios de prueba
                </span>
                <button type="button" onclick="fillCredentials('admin', 'admin123')"
                    class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 transition cursor-pointer">
                    Autocompletar Admin
                </button>
            </div>
            <div class="grid grid-cols-3 gap-2">
                <button type="button" onclick="fillCredentials('admin', 'admin123')"
                    class="group p-2.5 rounded-xl bg-slate-50 border border-slate-200 hover:border-indigo-400 hover:bg-indigo-50/60 transition text-left cursor-pointer">
                    <span class="block text-xs font-bold text-slate-700 group-hover:text-indigo-600">Admin</span>
                    <span class="block text-[11px] text-slate-400 mt-0.5">admin123</span>
                </button>
                <button type="button" onclick="fillCredentials('instructor1', '123')"
                    class="group p-2.5 rounded-xl bg-slate-50 border border-slate-200 hover:border-emerald-400 hover:bg-emerald-50/60 transition text-left cursor-pointer">
                    <span class="block text-xs font-bold text-slate-700 group-hover:text-emerald-600">Instructor</span>
                    <span class="block text-[11px] text-slate-400 mt-0.5">123</span>
                </button>
                <button type="button" onclick="fillCredentials('aprendiz1', '123')"
                    class="group p-2.5 rounded-xl bg-slate-50 border border-slate-200 hover:border-amber-400 hover:bg-amber-50/60 transition text-left cursor-pointer">
                    <span class="block text-xs font-bold text-slate-700 group-hover:text-amber-600">Aprendiz</span>
                    <span class="block text-[11px] text-slate-400 mt-0.5">123</span>
                </button>
            </div>
        </div>

        <script>
        function fillCredentials(user, pass) {
            const userInput = document.getElementById('login-user');
            const passInput = document.getElementById('login-pass');
            if (userInput && passInput) {
                userInput.value = user;
                passInput.value = pass;
                userInput.focus();
            }
        }
        </script>

        <!-- Footer -->
        <div class="mt-8 text-center">
            <p class="text-xs text-slate-400"> Plataforma administrativa · SGA </p>
        </div>
    </div>