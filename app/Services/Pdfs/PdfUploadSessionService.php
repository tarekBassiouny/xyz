<?php

declare(strict_types=1);

namespace App\Services\Pdfs;

use App\Models\Center;
use App\Models\PdfUploadSession;
use App\Models\User;
use App\Services\Centers\CenterScopeService;
use App\Services\Storage\Contracts\StorageServiceInterface;
use App\Services\Storage\StoragePathResolver;

class PdfUploadSessionService
{
    public function __construct(
        private readonly StorageServiceInterface $storageService,
        private readonly StoragePathResolver $pathResolver,
        private readonly CenterScopeService $centerScopeService
    ) {}

    public function initialize(Center $center, User $admin, string $originalFilename, ?int $fileSizeKb = null): PdfUploadSession
    {
        if (! $admin->hasRole('super_admin')) {
            $this->centerScopeService->assertAdminCenterId($admin, $center->id);
        }

        $extension = pathinfo($originalFilename, PATHINFO_EXTENSION);
        $extension = $extension !== '' ? strtolower($extension) : 'pdf';

        $filename = sprintf('%s.%s', uniqid('pdf_', true), $extension);
        $objectKey = $this->pathResolver->pdf($center->id, $filename);

        $ttl = (int) config('pdf.signed_url_ttl', 600);
        $expiresAt = now()->addSeconds($ttl);

        $session = PdfUploadSession::create([
            'center_id' => $center->id,
            'created_by' => $admin->id,
            'object_key' => $objectKey,
            'file_extension' => $extension,
            'file_size_kb' => $fileSizeKb,
            'expires_at' => $expiresAt,
        ]);

        $session->setAttribute('upload_url', $this->storageService->temporaryUploadUrl($objectKey, $ttl));

        return $session;
    }
}
