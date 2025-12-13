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
    public function paginate(User $admin, int $perPage = 15): LengthAwarePaginator
    {
        $query = Video::query()
            ->with(['uploadSession', 'creator'])
            ->orderByDesc('created_at');

        $query = $this->applyScope($query, $admin);

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
}
