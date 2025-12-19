<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Center;
use App\Models\User;
use App\Notifications\AdminCenterOnboardingNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;

class SendCenterOnboardingEmail implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        private readonly int $centerId,
        private readonly int $ownerId
    ) {}

    public function handle(): void
    {
        $center = Center::find($this->centerId);
        $owner = User::find($this->ownerId);

        if (! $center instanceof Center || ! $owner instanceof User) {
            Log::warning('Center onboarding email skipped due to missing data.', [
                'center_id' => $this->centerId,
                'owner_id' => $this->ownerId,
            ]);

            return;
        }

        if ($owner->email === null) {
            Log::warning('Center onboarding email skipped due to missing email.', [
                'center_id' => $center->id,
                'owner_id' => $owner->id,
            ]);

            return;
        }

        $token = Password::broker()->createToken($owner);
        $owner->notify(new AdminCenterOnboardingNotification($center, $token));
    }
}
