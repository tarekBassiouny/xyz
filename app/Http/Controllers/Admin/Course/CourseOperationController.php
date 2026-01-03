<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Course;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Courses\AssignPdfRequest;
use App\Http\Requests\Admin\Courses\AssignVideoRequest;
use App\Http\Requests\Admin\Courses\CloneCourseRequest;
use App\Http\Resources\Admin\Courses\CourseResource;
use App\Models\Course;
use App\Models\User;
use App\Services\Courses\Contracts\CourseAttachmentServiceInterface;
use App\Services\Courses\Contracts\CourseWorkflowServiceInterface;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;

class CourseOperationController extends Controller
{
    public function assignVideo(
        AssignVideoRequest $request,
        Course $course,
        CourseAttachmentServiceInterface $courseAttachmentService
    ): JsonResponse {
        $admin = $this->requireAdmin();
        /** @var array{video_id:int,order_index?:int|null} $data */
        $data = $request->validated();
        $courseAttachmentService->assignVideo($course, (int) $data['video_id'], $admin);

        return response()->json([
            'success' => true,
            'message' => 'Video assigned successfully',
            'data' => null,
        ], 201);
    }

    public function removeVideo(
        Course $course,
        int $video,
        CourseAttachmentServiceInterface $courseAttachmentService
    ): JsonResponse {
        $admin = $this->requireAdmin();
        $courseAttachmentService->removeVideo($course, $video, $admin);

        return response()->json([
            'success' => true,
            'message' => 'Video removed successfully',
            'data' => null,
        ]);
    }

    public function assignPdf(
        AssignPdfRequest $request,
        Course $course,
        CourseAttachmentServiceInterface $courseAttachmentService
    ): JsonResponse {
        $admin = $this->requireAdmin();
        /** @var array{pdf_id:int,order_index?:int|null} $data */
        $data = $request->validated();
        $courseAttachmentService->assignPdf($course, (int) $data['pdf_id'], $admin);

        return response()->json([
            'success' => true,
            'message' => 'PDF assigned successfully',
            'data' => null,
        ], 201);
    }

    public function removePdf(
        Course $course,
        int $pdf,
        CourseAttachmentServiceInterface $courseAttachmentService
    ): JsonResponse {
        $admin = $this->requireAdmin();
        $courseAttachmentService->removePdf($course, $pdf, $admin);

        return response()->json([
            'success' => true,
            'message' => 'PDF removed successfully',
            'data' => null,
        ]);
    }

    public function publish(
        Course $course,
        CourseWorkflowServiceInterface $courseWorkflowService
    ): JsonResponse {
        $admin = $this->requireAdmin();
        $published = $courseWorkflowService->publishCourse($course, $admin);

        return response()->json([
            'success' => true,
            'message' => 'Course published successfully',
            'data' => new CourseResource($published),
        ]);
    }

    public function cloneCourse(
        CloneCourseRequest $request,
        Course $course,
        CourseWorkflowServiceInterface $courseWorkflowService
    ): JsonResponse {
        $admin = $this->requireAdmin();
        /** @var array<string, mixed> $data */
        $data = $request->validated();
        $options = $data['options'] ?? [];

        if (! is_array($options)) {
            $options = [];
        }

        $cloned = $courseWorkflowService->cloneCourse($course, $admin, $options);

        return response()->json([
            'success' => true,
            'message' => 'Course cloned successfully',
            'data' => new CourseResource($cloned),
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
