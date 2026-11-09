<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AprendizController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BienestarController;
use App\Http\Controllers\InstructorController;
use App\Http\Controllers\LandingController;
use Illuminate\Support\Facades\Route;

// Landing pública y Autenticación
Route::get('/', [LandingController::class, 'index'])->name('landing');
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [AuthController::class, 'dashboardHub'])->name('dashboard');

    // Descarga transversal de soportes y centro de notificaciones
    Route::get('/soportes/{id}/descargar', [AprendizController::class, 'downloadSupport'])->name('supports.download');
    Route::post('/notificaciones/{id}/leida', [AprendizController::class, 'markNotificationRead'])->name('notifications.read');
    Route::post('/notificaciones/leer-todas', [AprendizController::class, 'markAllNotificationsRead'])->name('notifications.readAll');

    // 1. GESTOR DE BIENESTAR / COORDINADOR (Núcleo de Intervención)
    Route::prefix('bienestar')
        ->name('bienestar.')
        ->middleware('role.access:bienestar|admin,bienestar.monitoreo')
        ->group(function () {
            Route::get('/', [BienestarController::class, 'index'])->name('dashboard');
            Route::post('/parametros', [BienestarController::class, 'updateParameters'])
                ->middleware('role.access:bienestar|admin,bienestar.variables')
                ->name('parameters.update');
            Route::post('/casos', [BienestarController::class, 'storeCase'])
                ->middleware('role.access:bienestar|admin,bienestar.casos')
                ->name('cases.store');
            Route::put('/casos/{id}', [BienestarController::class, 'updateCase'])
                ->middleware('role.access:bienestar|admin,bienestar.casos')
                ->name('cases.update');
            Route::delete('/casos/{id}', [BienestarController::class, 'destroyCase'])
                ->middleware('role.access:bienestar|admin,bienestar.casos')
                ->name('cases.destroy');
            Route::get('/reportes/export-csv', [BienestarController::class, 'exportReportCsv'])
                ->middleware('role.access:bienestar|admin,bienestar.reportes')
                ->name('reports.export');
            Route::post('/soportes/{id}/revisar', [BienestarController::class, 'reviewSupport'])
                ->name('supports.review');
        });

    // 2. INSTRUCTOR (Control Académico y Detección Temprana)
    Route::prefix('instructor')
        ->name('instructor.')
        ->middleware('role.access:instructor|admin|bienestar')
        ->group(function () {
            Route::get('/', [InstructorController::class, 'index'])->name('dashboard');
            Route::post('/observaciones', [InstructorController::class, 'storeObservations'])
                ->middleware('role.access:instructor|admin,instructor.novedades')
                ->name('observations.store');
            Route::post('/observaciones/{id}/escalar', [InstructorController::class, 'escalateObservation'])
                ->middleware('role.access:instructor|admin,instructor.escalar')
                ->name('observations.escalate');
            Route::get('/aprendices/{id}/expediente', [InstructorController::class, 'showExpediente'])
                ->name('expediente');
            Route::post('/soportes/{id}/revisar', [InstructorController::class, 'reviewSupport'])
                ->name('supports.review');
        });

    // 3. ADMINISTRADOR DEL SISTEMA (Gobernanza y Seguridad)
    Route::prefix('admin')
        ->name('admin.')
        ->middleware('role.access:admin')
        ->group(function () {
            Route::get('/', [AdminController::class, 'index'])->name('dashboard');
            Route::post('/usuarios', [AdminController::class, 'storeUser'])
                ->middleware('role.access:admin,admin.usuarios')
                ->name('users.store');
            Route::put('/usuarios/{id}/acceso', [AdminController::class, 'updateUserAccess'])
                ->middleware('role.access:admin,admin.usuarios')
                ->name('users.update');
            Route::delete('/usuarios/{id}', [AdminController::class, 'destroyUser'])
                ->middleware('role.access:admin,admin.usuarios')
                ->name('users.destroy');

            // Tablas maestras
            Route::post('/maestras/sedes', [AdminController::class, 'storeSede'])
                ->middleware('role.access:admin,admin.maestras')
                ->name('master.sedes.store');
            Route::post('/maestras/periodos', [AdminController::class, 'storeAcademicPeriod'])
                ->middleware('role.access:admin,admin.maestras')
                ->name('master.periods.store');
            Route::post('/maestras/programas', [AdminController::class, 'storeTrainingProgram'])
                ->middleware('role.access:admin,admin.maestras')
                ->name('master.programs.store');
            Route::post('/maestras/fichas', [AdminController::class, 'storeFicha'])
                ->middleware('role.access:admin,admin.maestras')
                ->name('master.fichas.store');
            Route::post('/maestras/asignaciones', [AdminController::class, 'assignInstructor'])
                ->middleware('role.access:admin,admin.maestras')
                ->name('master.assignments.store');
        });

    // 4. APRENDIZ (Autogestión y Transparencia)
    Route::prefix('aprendiz')
        ->name('aprendiz.')
        ->middleware('role.access:aprendiz|admin')
        ->group(function () {
            Route::get('/', [AprendizController::class, 'index'])->name('dashboard');
            Route::post('/observaciones/{observationId}/soportes', [AprendizController::class, 'storeSupport'])
                ->middleware('role.access:aprendiz|admin,aprendiz.soportes')
                ->name('supports.store');
        });
});
