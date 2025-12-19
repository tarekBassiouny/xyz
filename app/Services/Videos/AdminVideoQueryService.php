<?php

declare(strict_types=1);

namespace App\Services\Videos;

use App\Models\User;
use App\Models\Video;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class AdminVideoQueryService
{
    /**
     * @return LengthAwarePaginator<Video>
     */
    /**
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<Video>
     */
    public function paginate(User $admin, int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = Video::query()
            ->with(['uploadSession', 'creator'])
            ->orderByDesc('created_at');

        $query = $this->applyScope($query, $admin);
        $query = $this->applyFilters($query, $filters);

        return $query->paginate($perPage);
    }

    /**
     * @param  Builder<Video>  $query
     * @return Builder<Video>
     */
    private function applyScope(Builder $query, User $admin): Builder
    {
        if ($admin->center_id !== null) {
            $query->whereHas('creator', static function (Builder $builder) use ($admin): void {
                $builder->where('center_id', $admin->center_id);
            });
        }

        return $query;
    }

    /**
     * @param  Builder<Video>  $query
     * @param  array<string, mixed>  $filters
     * @return Builder<Video>
     */
    private function applyFilters(Builder $query, array $filters): Builder
    {
        if (isset($filters['center_id']) && is_numeric($filters['center_id'])) {
            $centerId = (int) $filters['center_id'];
            $query->whereHas('creator', static function (Builder $builder) use ($centerId): void {
                $builder->where('center_id', $centerId);
            });
        }

        return $query;
    }
}
