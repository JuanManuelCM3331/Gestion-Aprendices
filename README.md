# SGA 2.0 – Sistema Inteligente de Gestión y Permanencia de Aprendices (Laravel 12)

Refactorización integral del sistema **Gestión de Aprendices** migrado a **Laravel 12** bajo el **Patrón MVC (Modelo-Vista-Controlador)** enriquecido con **Arquitectura en Capas (Service y Repository Patterns)** y frontend **100% en Tailwind CSS**.

---

## Arquitectura del Proyecto (MVC + Service & Repository Layers)

```text
New-Gestion-Aprendices/
├── app/
│   ├── Http/
│   │   ├── Controllers/           # Capa de Controladores (Delgados, orientados a dominio)
│   │   │   ├── LandingController.php
│   │   │   ├── AuthController.php
│   │   │   ├── BienestarController.php
│   │   │   ├── InstructorController.php
│   │   │   ├── AdminController.php
│   │   │   └── AprendizController.php
│   │   └── Middleware/
│   │       └── RoleAndPermissionMiddleware.php  # RBAC Granular por rol y permiso de módulo
│   ├── Services/                  # Capa de Lógica de Negocio (Service Pattern)
│   │   ├── RiskEngineService.php        # Algoritmo predictivo multifactorial en tiempo real
│   │   ├── BienestarService.php         # Intervención, pesos dinámicos y reportes masivos
│   │   ├── InstructorService.php        # Carga ágil individual/masiva y escalamiento en 1 clic
│   │   ├── AdminGovernanceService.php   # Auditoría inmutable, permisos y tablas maestras
│   │   ├── AprendizPortalService.php    # Autogestión, transparencia y carga de soportes
│   │   ├── AuditService.php             # Firma criptográfica SHA-256 encadenada + IP
│   │   └── NotificationService.php      # Alertas internas de plataforma
│   ├── Repositories/              # Capa de Acceso a Datos Desacoplada (Repository Pattern)
│   │   ├── Contracts/                   # Interfaces inyectadas vía AppServiceProvider
│   │   └── Eloquent/                    # Implementaciones concretas con Eloquent ORM
│   └── Models/                    # Capa de Dominio y Guardas de Inmutabilidad
│       ├── User.php, Sede.php, AcademicPeriod.php, TrainingProgram.php, Ficha.php
│       ├── InstructorProfile.php, ApprenticeProfile.php, RiskParameter.php
│       ├── Observation.php, EvidenceSupport.php
│       ├── WelfareCase.php              # Guard de bloqueo de eliminación si status != 'resuelto'
│       └── AuditLog.php                 # Guard de inmutabilidad estricta (bloquea UPDATE/DELETE)
├── database/
│   ├── migrations/                # Esquema relacional completo de las 14 tablas del dominio
│   └── seeders/DatabaseSeeder.php # Datos semilla completos para los 4 roles y cohorte
├── resources/views/               # Frontend 100% Tailwind CSS (Cero Bootstrap)
│   ├── layouts/app.blade.php
│   ├── landing.blade.php
│   ├── auth/login.blade.php
│   ├── bienestar/dashboard.blade.php
│   ├── instructor/dashboard.blade.php & expediente.blade.php
│   ├── admin/dashboard.blade.php
│   └── aprendiz/dashboard.blade.php
└── tests/Feature/
    └── SgaArchitectureAndRolesTest.php  # Suite automatizada PHPUnit de los 4 perfiles
```

---

## Los 4 Perfiles del Sistema y Credenciales de Demostración

En la pantalla de login (`/login`) dispones de botones de autocompletado en 1 clic para probar cada rol:

| Perfil | Usuario | Contraseña | Capacidades Principales |
| :--- | :--- | :--- | :--- |
| **1. Gestor de Bienestar / Coordinador** *(Núcleo de Intervención)* | `bienestar` | `bienestar123` | **Monitoreo predictivo** con semaforización automática (deserción, académico, psicosocial), **gestión de variables y pesos** del modelo sin tocar código, **trazabilidad de casos** (bloqueando eliminación hasta su resolución) y **reportes masivos** por ficha, programa o jornada (CSV/Impresión). |
| **2. Instructor** *(Control Académico y Detección Temprana)* | `instructor1` | `123` | **Carga ágil de novedades** (individual, múltiple o masiva por ficha) que alimenta en tiempo real el cálculo de riesgo, **escalamiento directo** en 1 clic a Bienestar, **historial contextual** acotado a su competencia y **gestión de asignaciones y horarios**. |
| **3. Administrador del Sistema** *(Gobernanza y Seguridad)* | `admin` | `admin123` | **Auditoría inmutable** (quién, cuándo, IP, antes/después y firma SHA-256 encadenada sin posibilidad de alterar ni borrar), **control de acceso granular** (CRUD, reset de clave y permisos por módulo) y **mantenimiento de tablas maestras** (Programas, Sedes, Periodos, Fichas). |
| **4. Aprendiz** *(Autogestión y Transparencia)* | `aprendiz1` | `123` | **Visibilidad de estado** y semáforo transparente con línea de tiempo de observaciones positivas/negativas, **gestión de soportes** (carga de excusas médicas y descargos con archivos adjuntos vinculados al expediente) y **notificaciones internas**. |

---

## Ejecución Rápida

### 1. En entorno local (XAMPP / PHP CLI)
```bash
php artisan migrate:fresh --seed
php artisan serve
```
Luego abre `http://127.0.0.1:8000` (o desde Apache XAMPP en `http://localhost/New-Gestion-Aprendices/`).

### 2. Ejecutar Pruebas Automatizadas
```bash
php artisan test
```
