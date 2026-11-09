<?php

namespace App\Http\Middleware;

use App\Services\AuditService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleAndPermissionMiddleware
{
    public function __construct(
        private readonly AuditService $auditService
    ) {}

    public function handle(Request $request, Closure $next, string $roles, ?string $requiredPermission = null): Response
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        if (!$user->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'credentials' => 'Tu cuenta ha sido bloqueada temporalmente por el Administrador del Sistema.',
            ]);
        }

        $allowedRoles = array_map('trim', explode('|', $roles));

        if (!in_array($user->role, $allowedRoles, true)) {
            $this->auditService->log(
                'Seguridad · Control de Acceso',
                "Bloqueo de acceso no autorizado a ruta [{$request->path()}] (Rol requerido: {$roles}, Rol actual: {$user->role})",
                'critical'
            );

            abort(403, 'Control de Acceso Granular: Tu perfil no tiene autorización para ingresar a este módulo.');
        }

        if ($requiredPermission && !$user->hasPermission($requiredPermission)) {
            $this->auditService->log(
                'Seguridad · Control de Acceso',
                "Bloqueo por permiso granular faltante [{$requiredPermission}] en ruta [{$request->path()}]",
                'warning'
            );

            abort(403, "Permiso Granular Requerido: No cuentas con el permiso [{$requiredPermission}] habilitado por el Administrador.");
        }

        return $next($request);
    }
}
