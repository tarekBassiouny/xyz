<?php

declare(strict_types=1);

namespace App\Services\Pdfs;

use App\Actions\Concerns\NormalizesTranslations;
use App\Models\Center;
use App\Models\Pdf;
use App\Models\PdfUploadSession;
use App\Models\User;
use App\Services\Centers\CenterScopeService;
use Illuminate\Validation\ValidationException;

class PdfService
{
    use NormalizesTranslations;

    public function __construct(private readonly CenterScopeService $centerScopeService) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(Center $center, User $admin, array $data): Pdf
    {
        if (! $admin->hasRole('super_admin')) {
            $this->centerScopeService->assertAdminCenterId($admin, $center->id);
        }

        $localeValue = request()->attributes->get('locale', app()->getLocale());
        $locale = is_string($localeValue) ? $localeValue : (string) app()->getLocale();
        $data['locale'] = $locale;

        $payload = $this->normalizeTranslations($data, [
            'title_translations',
            'description_translations',
        ], [], 'locale');

        $payload['center_id'] = $center->id;
        $payload['created_by'] = $admin->id;

        if (isset($data['upload_session_id'])) {
            $session = PdfUploadSession::findOrFail((int) $data['upload_session_id']);

            if ((int) $session->center_id !== (int) $center->id) {
                throw ValidationException::withMessages([
                    'upload_session_id' => ['Upload session not found.'],
                ]);
            }

            $payload['source_type'] = 1;
            $payload['source_provider'] = 'spaces';
            $payload['source_id'] = $session->object_key;
            $payload['source_url'] = null;
            $payload['file_extension'] = $session->file_extension;
            $payload['file_size_kb'] = $session->file_size_kb;
        } else {
            if (! isset($data['source_id']) || ! is_string($data['source_id']) || $data['source_id'] === '') {
                throw ValidationException::withMessages([
                    'source_id' => ['Source ID is required when no upload session is provided.'],
                ]);
            }

            $payload['source_type'] = 1;
            $payload['source_provider'] = 'spaces';
            $payload['source_id'] = $data['source_id'];
            $payload['source_url'] = $data['source_url'] ?? null;
            $payload['file_extension'] = $data['file_extension'];
            $payload['file_size_kb'] = $data['file_size_kb'] ?? null;
        }

        /** @var Pdf $pdf */
        $pdf = Pdf::create($payload);

        return $pdf;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Pdf $pdf, User $admin, array $data): Pdf
    {
        if (! $admin->hasRole('super_admin')) {
            $this->centerScopeService->assertAdminCenterId($admin, $pdf->center_id);
        }

        $localeValue = request()->attributes->get('locale', app()->getLocale());
        $locale = is_string($localeValue) ? $localeValue : (string) app()->getLocale();
        $data['locale'] = $locale;

        $payload = $this->normalizeTranslations($data, [
            'title_translations',
            'description_translations',
        ], [
            'title_translations' => $pdf->title_translations ?? [],
            'description_translations' => $pdf->description_translations ?? [],
        ], 'locale');

        $pdf->update($payload);

        return $pdf->fresh(['creator']) ?? $pdf;
    }

    public function delete(Pdf $pdf, User $admin): void
    {
        if (! $admin->hasRole('super_admin')) {
            $this->centerScopeService->assertAdminCenterId($admin, $pdf->center_id);
        }

        $pdf->delete();
    }
}
