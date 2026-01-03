<?php

declare(strict_types=1);

namespace App\Services\Videos;

use App\Actions\Concerns\NormalizesTranslations;
use App\Models\Center;
use App\Models\User;
use App\Models\Video;
use App\Services\Centers\CenterScopeService;

class VideoService
{
    use NormalizesTranslations;

    public function __construct(private readonly CenterScopeService $centerScopeService) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(Center $center, User $admin, array $data): Video
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
        $payload['source_type'] = $payload['source_type'] ?? 1;
        $payload['source_provider'] = $payload['source_provider'] ?? 'bunny';
        $payload['encoding_status'] = 0;
        $payload['lifecycle_status'] = 0;

        return Video::create($payload);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Video $video, User $admin, array $data): Video
    {
        if (! $admin->hasRole('super_admin')) {
            $this->centerScopeService->assertAdminCenterId($admin, $video->center_id);
        }

        $localeValue = request()->attributes->get('locale', app()->getLocale());
        $locale = is_string($localeValue) ? $localeValue : (string) app()->getLocale();
        $data['locale'] = $locale;

        $payload = $this->normalizeTranslations($data, [
            'title_translations',
            'description_translations',
        ], [
            'title_translations' => $video->title_translations ?? [],
            'description_translations' => $video->description_translations ?? [],
        ], 'locale');

        $video->update($payload);

        return $video->fresh(['uploadSession', 'creator']) ?? $video;
    }

    public function delete(Video $video, User $admin): void
    {
        if (! $admin->hasRole('super_admin')) {
            $this->centerScopeService->assertAdminCenterId($admin, $video->center_id);
        }

        $video->delete();
    }
}
