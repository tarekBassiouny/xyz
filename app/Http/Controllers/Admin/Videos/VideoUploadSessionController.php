<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Videos;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Videos\StoreVideoUploadSessionRequest;
use App\Http\Requests\Admin\Videos\UpdateVideoUploadSessionRequest;
use App\Models\Center;
use App\Models\User;
use App\Models\Video;
use App\Models\VideoUploadSession;
use App\Services\Videos\VideoUploadService;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;

class VideoUploadSessionController extends Controller
{
    public function __construct(private readonly VideoUploadService $uploadService) {}

    public function store(StoreVideoUploadSessionRequest $request, Center $center): JsonResponse
    {
        $admin = $this->requireAdmin();
        $video = Video::findOrFail((int) $request->integer('video_id'));

        if ((int) $video->center_id !== (int) $center->id) {
            $this->notFound();
        }

        $session = $this->uploadService->initializeUpload(
            admin: $admin,
            center: $center,
            originalFilename: (string) $request->input('original_filename'),
            video: $video
        );

        /** @var string|null $uploadUrl */
        $uploadUrl = $session->getAttribute('upload_url');

        return response()->json([
            'success' => true,
            'data' => [
                'upload_session_id' => $session->id,
                'provider' => 'bunny',
                'remote_id' => $session->bunny_upload_id,
                'upload_endpoint' => $uploadUrl,
                'required_headers' => [],
                'expires_at' => null,
            ],
        ], 201);
    }

    public function update(
        UpdateVideoUploadSessionRequest $request,
        Center $center,
        VideoUploadSession $videoUploadSession
    ): JsonResponse {
        $admin = $this->requireAdmin();

        if ((int) $videoUploadSession->center_id !== (int) $center->id) {
            $this->notFound();
        }

        $session = $this->uploadService->transition(
            $admin,
            $videoUploadSession,
            (string) $request->input('status'),
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $session->id,
                'upload_status' => $session->upload_status,
                'progress_percent' => $session->progress_percent,
                'error_message' => $session->error_message,
            ],
        ]);
    }

    private function requireAdmin(): User
    {
        $admin = request()->user();

        if (! $admin instanceof User) {
            throw new HttpResponseException(response()->json([
                'success' => false,
                'error' => [
                    'code' => 'UNAUTHORIZED',
                    'message' => 'Authentication required.',
                ],
            ], 401));
        }

        return $admin;
    }

    private function notFound(): void
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'error' => [
                'code' => 'NOT_FOUND',
                'message' => 'Video upload session not found.',
            ],
        ], 404));
    }
}
