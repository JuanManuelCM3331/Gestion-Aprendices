<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    public const ROLE_ADMIN = 'admin';
    public const ROLE_BIENESTAR = 'bienestar';
    public const ROLE_INSTRUCTOR = 'instructor';
    public const ROLE_APRENDIZ = 'aprendiz';

    /**
     * Catálogo de permisos granulares por módulo para Gobernanza y Seguridad
     */
    public const AVAILABLE_PERMISSIONS = [
        'bienestar.monitoreo'  => 'Monitoreo Predictivo y Semáforo',
        'bienestar.variables'  => 'Ajuste de Variables del Algoritmo',
        'bienestar.casos'      => 'Trazabilidad y Rutas de Atención',
        'bienestar.reportes'   => 'Reportes Masivos Institucionales',
        'instructor.novedades' => 'Carga Individual y Masiva de Novedades',
        'instructor.escalar'   => 'Escalamiento Directo a Bienestar',
        'instructor.expediente'=> 'Consulta de Expediente Contextual',
        'instructor.fichas'    => 'Gestión de Asignaciones y Horarios',
        'admin.auditoria'      => 'Auditoría Inmutable y Trazabilidad IP',
        'admin.usuarios'       => 'Control de Acceso Granular (Usuarios)',
        'admin.maestras'       => 'Mantenimiento de Tablas Maestras',
        'aprendiz.estado'      => 'Visibilidad de Estado y Semáforo',
        'aprendiz.soportes'    => 'Carga de Soportes y Excusas',
        'aprendiz.alertas'     => 'Centro de Notificaciones',
    ];

    protected $fillable = [
        'name',
        'username',
        'email',
        'role',
        'permissions',
        'is_active',
        'last_login_at',
        'last_login_ip',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'permissions' => 'array',
            'is_active' => 'boolean',
            'password' => 'hashed',
        ];
    }

    public function instructorProfile(): HasOne
    {
        return $this->hasOne(InstructorProfile::class);
    }

    public function apprenticeProfile(): HasOne
    {
        return $this->hasOne(ApprenticeProfile::class);
    }

    public function platformNotifications(): HasMany
    {
        return $this->hasMany(PlatformNotification::class)->latest();
    }

    public function unreadPlatformNotifications(): HasMany
    {
        return $this->hasMany(PlatformNotification::class)->whereNull('read_at')->latest();
    }

    public function hasRole(string|array $roles): bool
    {
        $roles = (array) $roles;
        return in_array($this->role, $roles, true);
    }

    public function hasPermission(string $permission): bool
    {
        if ($this->role === self::ROLE_ADMIN) {
            $explicit = $this->permissions;
            if (is_array($explicit) && !empty($explicit)) {
                return in_array($permission, $explicit, true);
            }
            return true;
        }

        $perms = $this->permissions ?? self::defaultPermissionsForRole($this->role);
        return in_array($permission, $perms, true);
    }

    public static function defaultPermissionsForRole(string $role): array
    {
        return match ($role) {
            self::ROLE_ADMIN => array_keys(self::AVAILABLE_PERMISSIONS),
            self::ROLE_BIENESTAR => [
                'bienestar.monitoreo',
                'bienestar.variables',
                'bienestar.casos',
                'bienestar.reportes',
            ],
            self::ROLE_INSTRUCTOR => [
                'instructor.novedades',
                'instructor.escalar',
                'instructor.expediente',
                'instructor.fichas',
            ],
            self::ROLE_APRENDIZ => [
                'aprendiz.estado',
                'aprendiz.soportes',
                'aprendiz.alertas',
            ],
            default => [],
        };
    }

    public function roleLabel(): string
    {
        return match ($this->role) {
            self::ROLE_ADMIN => 'Administrador del Sistema',
            self::ROLE_BIENESTAR => 'Gestor de Bienestar / Coordinador',
            self::ROLE_INSTRUCTOR => 'Instructor Académico',
            self::ROLE_APRENDIZ => 'Aprendiz',
            default => ucfirst($this->role),
        };
    }
}
