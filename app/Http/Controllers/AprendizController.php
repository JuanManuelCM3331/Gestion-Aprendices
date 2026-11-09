<?php

namespace App\Http\Controllers;

use App\Models\EvidenceSupport;
use App\Services\AprendizPortalService;
use App\Services\NotificationService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AprendizController extends Controller
{
    public function __construct(
        private readonly AprendizPortalService $aprendizPortalService,
        private readonly NotificationService $notificationService
    ) {}

    public function index(Request $request): View
    {
        $tab = $request->query('tab', 'estado');
        $data = $this->aprendizPortalService->getPortalData(Auth::user());

        return view('aprendiz.dashboard', array_merge($data, [
            'activeTab' => $tab,
        ]));
    }

    public function storeSupport(Request $request, int $observationId): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'support_type' => ['required', 'in:Excusa Médica,Descargo a Llamado de Atención,Soporte Calamidad Doméstica,Certificado Laboral/Institucional,Otro'],
            'description' => ['required', 'string', 'max:1500'],
            'attachment' => ['nullable', 'file', 'max:5120', 'mimes:pdf,jpg,jpeg,png,doc,docx,txt'],
        ]);

        try {
            $this->aprendizPortalService->uploadSupportForObservation(
                Auth::user(),
                $observationId,
                $validated,
                $request->file('attachment')
            );

            return redirect()
                ->route('aprendiz.dashboard', ['tab' => 'soportes'])
                ->with('status', 'Tu soporte / evidencia ha sido cargado y vinculado a tu expediente para revisión.');
        } catch (DomainException $e) {
            return back()->withErrors(['support_error' => $e->getMessage()]);
        }
    }

    public function downloadSupport(int $id): StreamedResponse|RedirectResponse
    {
        $support = EvidenceSupport::findOrFail($id);
        $user = Auth::user();

        if ($user->role === 'aprendiz' && $support->apprenticeProfile?->user_id !== $user->id) {
            abort(403, 'No tienes permiso para descargar este soporte.');
        }

        if (!$support->file_path || !Storage::disk('public')->exists($support->file_path)) {
            return back()->withErrors(['support_error' => 'Este soporte no incluye archivo adjunto o el archivo no se encuentra disponible.']);
        }

        return Storage::disk('public')->download($support->file_path, $support->file_name ?? basename($support->file_path));
    }

    public function markNotificationRead(int $id): RedirectResponse
    {
        $this->notificationService->markAsRead($id, (int) Auth::id());
        return back()->with('status', 'Notificación marcada como leída.');
    }

    public function markAllNotificationsRead(): RedirectResponse
    {
        $this->notificationService->markAllAsRead((int) Auth::id());
        return back()->with('status', 'Todas tus alertas han sido marcadas como leídas.');
    }
}
