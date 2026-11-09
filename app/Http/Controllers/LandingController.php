<?php

namespace App\Http\Controllers;

use App\Models\ApprenticeProfile;
use App\Models\AuditLog;
use App\Models\Ficha;
use App\Models\Observation;
use App\Models\User;
use App\Models\WelfareCase;
use Illuminate\View\View;

class LandingController extends Controller
{
    public function index(): View
    {
        $metrics = [
            'aprendices' => ApprenticeProfile::count(),
            'fichas' => Ficha::count(),
            'casos_bienestar' => WelfareCase::count(),
            'observaciones' => Observation::count(),
            'auditoria_logs' => AuditLog::count(),
            'usuarios' => User::count(),
        ];

        return view('landing', compact('metrics'));
    }
}
