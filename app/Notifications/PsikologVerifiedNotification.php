<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PsikologVerifiedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Selamat! Akun Psikolog Anda Telah Diverifikasi')
            ->greeting('Halo ' . $notifiable->name . '!')
            ->line('Selamat! Akun psikolog Anda di Kutkatha telah berhasil diverifikasi.')
            ->line('Anda sekarang dapat:')
            ->line('• Membuat jadwal konsultasi')
            ->line('• Menerima booking dari klien')
            ->line('• Menulis artikel edukasi')
            ->line('• Menjawab pertanyaan di forum')
            ->action('Masuk ke Dashboard', url('/psikolog/dashboard'))
            ->line('Terima kasih telah bergabung dengan Kutkatha!')
            ->salutation('Salam, Tim Kutkatha');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'psikolog_verified',
            'title' => 'Akun Terverifikasi',
            'message' => 'Selamat! Akun psikolog Anda telah diverifikasi.',
            'action_url' => '/psikolog/dashboard',
        ];
    }
}
