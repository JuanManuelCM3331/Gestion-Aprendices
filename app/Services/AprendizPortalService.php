<?php

namespace App\Services;

use App\Models\EvidenceSupport;
use App\Models\User;
use App\Repositories\Contracts\ApprenticeRepositoryInterface;
use App\Repositories\Contracts\ObservationRepositoryInterface;
use DomainException;
use Illuminate\Http\UploadedFile;

class AprendizPortalService
{
    public function __construct(
        private readonly ApprenticeRepositoryInterface $apprenticeRepository,
        private readonly ObservationRepositoryInterface $observationRepository,
        private readonly NotificationService $notificationService,
        private readonly AuditService $auditService
    ) {}

    public function getPortalData(User $user): array
    {
        $profile = $this->apprenticeRepository->findByUserId($user->id);

        return [
            'profile' => $profile,
            'observations' => $profile ? $profile->observations : collect(),
            'supports' => $profile ? $profile->evidenceSupports : collect(),
            'welfareCases' => $profile ? $profile->welfareCases : collect(),
            'notifications' => $user->platformNotifications()->limit(30)->get(),
            'unreadCount' => $user->unreadPlatformNotifications()->count(),
        ];
    }

    /**
     * Gestión de soportes: permite al aprendiz cargar documentos, evidencias o excusas médicas
     * directamente vinculadas a una observación de su expediente.
     */
    public function uploadSupportForObservation(
        User $user,
        int $observationId,
        array $data,
        ?UploadedFile $attachment = null
    ): EvidenceSupport {
        $profile = $this->apprenticeRepository->findByUserId($user->id);
        if (!$profile) {
            throw new DomainException('No tienes un expediente de aprendiz vinculado.');
        }

        $observation = $this->observationRepository->findById($observationId);
        if (!$observation || $observation->apprentice_profile_id !== $profile->id) {
            throw new DomainException('La observación seleccionada no pertenece a tu expediente.');
        }

        $storedPath = null;
        $originalName = null;
        $mimeType = null;

        if ($attachment) {
            $originalName = $attachment->getClientOriginalName();
            $mimeType = $attachment->getClientMimeType();
            $storedPath = $attachment->store('supports', 'public');
        }

        $support = $this->observationRepository->createSupport([
            'observation_id' => $observation->id,
            'apprentice_profile_id' => $profile->id,
            'title' => $data['title'],
            'support_type' => $data['support_type'],
            'description' => $data['description'],
            'file_path' => $storedPath,
            'file_name' => $originalName,
            'mime_type' => $mimeType,
            'status' => 'pendiente',
        ]);

        // Notificar al autor de la observación (instructor) si existe
        if ($observation->author_id) {
            $this->notificationService->notifyUser(
                $observation->author_id,
                "Nuevo Soporte Cargado por {$user->name}",
                "El aprendiz adjuntó un soporte ({$support->support_type}: {$support->title}) a la observación '{$observation->title}'.",
                'soporte',
                'info',
                route('instructor.expediente', $profile->id)
            );
        }

        $this->auditService->log(
            'Aprendiz · Gestión de Soportes',
            "Cargó soporte/evidencia '{$support->title}' ({$support->support_type}) para observación ID {$observation->id}",
            'info',
            'EvidenceSupport',
            $support->id,
            null,
            [
                'observation_id' => $observation->id,
                'support_type' => $support->support_type,
                'file_name' => $originalName,
            ]
        );

        return $support;
    }
}
