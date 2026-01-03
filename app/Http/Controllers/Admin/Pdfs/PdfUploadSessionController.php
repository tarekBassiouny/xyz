<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Pdfs;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Pdfs\StorePdfUploadSessionRequest;
use App\Models\Center;
use App\Models\User;
use App\Services\Pdfs\PdfUploadSessionService;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;

class PdfUploadSessionController extends Controller
{
    public function __construct(private readonly PdfUploadSessionService $uploadSessionService) {}

    public function store(StorePdfUploadSessionRequest $request, Center $center): JsonResponse
    {
        $admin = $this->requireAdmin();

        $session = $this->uploadSessionService->initialize(
            $center,
            $admin,
            (string) $request->input('original_filename'),
            $request->integer('file_size_kb')
        );

        /** @var string|null $uploadUrl */
        $uploadUrl = $session->getAttribute('upload_url');
        /** @var \DateTimeInterface|null $expiresAt */
        $expiresAt = $session->expires_at;
        $expiresAtString = $expiresAt?->format(DATE_ATOM);

        return response()->json([
            'success' => true,
            'data' => [
                'upload_session_id' => $session->id,
                'provider' => 'spaces',
                'remote_id' => $session->object_key,
                'upload_endpoint' => $uploadUrl,
                'required_headers' => [
                    'Content-Type' => 'application/pdf',
                ],
                'expires_at' => $expiresAtString,
            ],
        ], 201);
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
}
