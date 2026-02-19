<?php

declare(strict_types=1);

use App\Models\Center;
use App\Models\User;
use App\Notifications\AdminCenterOnboardingNotification;
use App\Notifications\AdminPasswordResetNotification;
use Tests\TestCase;

uses(TestCase::class)->group('notifications');

it('builds password reset links from frontend domain with center slug', function (): void {
    config()->set('app.frontend_url', 'https://admin.najaah.me');

    $center = new Center([
        'slug' => 'center-123',
    ]);

    $admin = new User([
        'email' => 'admin@example.com',
    ]);
    $admin->setRelation('center', $center);

    $notification = new AdminPasswordResetNotification('token123', true);
    $mailMessage = $notification->toMail($admin);

    expect($mailMessage->actionUrl)
        ->toStartWith('https://center-123.admin.najaah.me/admin/reset-password?');

    parse_str((string) parse_url((string) $mailMessage->actionUrl, PHP_URL_QUERY), $query);

    expect($query['token'] ?? null)->toBe('token123')
        ->and($query['email'] ?? null)->toBe('admin@example.com');
});

it('builds onboarding links from frontend domain with center slug', function (): void {
    config()->set('app.frontend_url', 'https://admin.najaah.me');

    $center = new Center([
        'name' => 'Cairo Center',
        'slug' => 'cairo',
    ]);

    $admin = new User([
        'email' => 'owner@example.com',
    ]);

    $notification = new AdminCenterOnboardingNotification($center, 'token456');
    $mailMessage = $notification->toMail($admin);

    expect($mailMessage->actionUrl)
        ->toStartWith('https://cairo.admin.najaah.me/admin/reset-password?');

    $outroLines = array_map(
        static fn ($line): string => (string) $line,
        $mailMessage->outroLines
    );

    $loginLineFound = collect($outroLines)->contains(
        static fn (string $line): bool => str_contains($line, 'https://cairo.admin.najaah.me/admin/login')
    );

    expect($loginLineFound)->toBeTrue();
});
