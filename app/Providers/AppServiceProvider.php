<?php

namespace App\Providers;

use App\Repositories\Contracts\ApprenticeRepositoryInterface;
use App\Repositories\Contracts\AuditLogRepositoryInterface;
use App\Repositories\Contracts\MasterDataRepositoryInterface;
use App\Repositories\Contracts\ObservationRepositoryInterface;
use App\Repositories\Contracts\RiskParameterRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\Contracts\WelfareCaseRepositoryInterface;
use App\Repositories\Eloquent\EloquentApprenticeRepository;
use App\Repositories\Eloquent\EloquentAuditLogRepository;
use App\Repositories\Eloquent\EloquentMasterDataRepository;
use App\Repositories\Eloquent\EloquentObservationRepository;
use App\Repositories\Eloquent\EloquentRiskParameterRepository;
use App\Repositories\Eloquent\EloquentUserRepository;
use App\Repositories\Eloquent\EloquentWelfareCaseRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services (Repository Pattern bindings).
     */
    public function register(): void
    {
        $this->app->bind(ApprenticeRepositoryInterface::class, EloquentApprenticeRepository::class);
        $this->app->bind(RiskParameterRepositoryInterface::class, EloquentRiskParameterRepository::class);
        $this->app->bind(WelfareCaseRepositoryInterface::class, EloquentWelfareCaseRepository::class);
        $this->app->bind(ObservationRepositoryInterface::class, EloquentObservationRepository::class);
        $this->app->bind(UserRepositoryInterface::class, EloquentUserRepository::class);
        $this->app->bind(MasterDataRepositoryInterface::class, EloquentMasterDataRepository::class);
        $this->app->bind(AuditLogRepositoryInterface::class, EloquentAuditLogRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
