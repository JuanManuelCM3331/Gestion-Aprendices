<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly AuditService $auditService
    ) {}

    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check()) {
            return $this->redirectByRole(Auth::user());
        }

        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'identifier' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $user = $this->userRepository->findByUsernameOrEmail($credentials['identifier']);

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            $this->auditService->log(
                'Autenticación',
                "Intento de inicio de sesión fallido para identificador '{$credentials['identifier']}'",
                'warning'
            );

            return back()->withInput()->withErrors([
                'credentials' => 'Credenciales inválidas. Verifica tu usuario o contraseña.',
            ]);
        }

        if (!$user->is_active) {
            $this->auditService->log(
                'Autenticación',
                "Intento de acceso en cuenta inactiva/bloqueada '{$user->username}'",
                'critical',
                'User',
                $user->id
            );

            return back()->withErrors([
                'credentials' => 'Tu cuenta se encuentra deshabilitada por Gobernanza del Sistema.',
            ]);
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        $this->userRepository->update($user->id, [
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ]);

        $this->auditService->log(
            'Autenticación',
            "Inicio de sesión exitoso como {$user->roleLabel()}",
            'info',
            'User',
            $user->id
        );

        return $this->redirectByRole($user);
    }

    public function logout(Request $request): RedirectResponse
    {
        if (Auth::check()) {
            $this->auditService->log(
                'Autenticación',
                'Cierre de sesión voluntario',
                'info',
                'User',
                Auth::id()
            );
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    public function dashboardHub(): RedirectResponse
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        return $this->redirectByRole(Auth::user());
    }

    private function redirectByRole(User $user): RedirectResponse
    {
        return match ($user->role) {
            User::ROLE_BIENESTAR => redirect()->route('bienestar.dashboard'),
            User::ROLE_INSTRUCTOR => redirect()->route('instructor.dashboard'),
            User::ROLE_ADMIN => redirect()->route('admin.dashboard'),
            default => redirect()->route('aprendiz.dashboard'),
        };
    }
}
