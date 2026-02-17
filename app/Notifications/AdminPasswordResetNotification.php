<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\User;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminPasswordResetNotification extends Notification
{
    public function __construct(
        private readonly string $token,
        private readonly bool $isInvite = false
    ) {}

    /**
     * @param  mixed  $notifiable
     * @return array<int, string>
     */
    public function via($notifiable): array
    {
        return ['mail'];
    }

    /**
     * @param  mixed  $notifiable
     */
    public function toMail($notifiable): MailMessage
    {
        if ($notifiable instanceof User) {
            $notifiable->loadMissing('center');
        }

        $email = (string) ($notifiable->email ?? '');
        $centerSlug = $notifiable instanceof User
            ? trim((string) ($notifiable->center?->slug ?? ''))
            : null;
        if ($centerSlug === '') {
            $centerSlug = null;
        }

        $resetUrl = $this->frontendLink('admin/reset-password', $centerSlug).'?'.http_build_query([
            'token' => $this->token,
            'email' => $email,
        ]);
        $loginUrl = $this->frontendLink('admin/login', $centerSlug);

        if ($this->isInvite) {
            return (new MailMessage)
                ->subject('Admin access invitation')
                ->greeting('Hello!')
                ->line('You have been invited to the admin portal.')
                ->line('Please set your password to activate your account.')
                ->action('Set your password', $resetUrl)
                ->line('Login URL: '.$loginUrl);
        }

        return (new MailMessage)
            ->subject('Reset your admin password')
            ->greeting('Hello!')
            ->line('We received a request to reset your admin password.')
            ->action('Reset password', $resetUrl)
            ->line('If you did not request this, no further action is required.');
    }

    private function frontendLink(string $path, ?string $centerSlug = null): string
    {
        $baseUrl = $this->frontendBaseUrl($centerSlug);

        if ($path === '') {
            return $baseUrl;
        }

        return rtrim($baseUrl, '/').'/'.ltrim($path, '/');
    }

    private function frontendBaseUrl(?string $centerSlug = null): string
    {
        $frontendUrl = rtrim((string) config('app.frontend_url', config('app.url')), '/');

        if ($centerSlug === null || $centerSlug === '') {
            return $frontendUrl;
        }

        $parts = parse_url($frontendUrl);

        if ($parts === false || empty($parts['host'])) {
            return $frontendUrl;
        }

        $host = $parts['host'];

        if (str_starts_with($host, $centerSlug.'.')) {
            return $frontendUrl;
        }

        $newHost = $centerSlug.'.'.$host;
        $scheme = $parts['scheme'] ?? 'https';
        $port = isset($parts['port']) ? ':'.$parts['port'] : '';
        $path = $parts['path'] ?? '';
        $path = rtrim($path, '/');

        $base = $scheme.'://'.$newHost.$port;

        if ($path !== '') {
            $base .= $path;
        }

        return $base;
    }
}
