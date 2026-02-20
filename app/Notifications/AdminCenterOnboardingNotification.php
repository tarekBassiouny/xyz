<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Center;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminCenterOnboardingNotification extends Notification
{
    public function __construct(
        private readonly Center $center,
        private readonly string $token
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
        $email = (string) ($notifiable->email ?? '');
        $centerSlug = trim((string) ($this->center->slug ?? ''));
        if ($centerSlug === '') {
            $centerSlug = null;
        }

        $resetUrl = $this->frontendLink('reset-password', $centerSlug).'?'.http_build_query([
            'token' => $this->token,
            'email' => $email,
        ]);
        $loginUrl = $this->frontendLink('login', $centerSlug);

        return (new MailMessage)
            ->subject('Center access granted')
            ->greeting('Hello!')
            ->line('You have been added as an owner for '.$this->center->name.' center.')
            ->line('Please set your password to access the admin portal.')
            ->action('Set your password', $resetUrl)
            ->line('Login URL: '.$loginUrl);
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
