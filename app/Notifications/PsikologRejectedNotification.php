<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PsikologRejectedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private string $reason;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $reason)
    {
        $this->reason = $reason;
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
            ->subject('Verifikasi Akun Psikolog Ditolak')
            ->greeting('Halo ' . $notifiable->name . ',')
            ->line('Mohon maaf, pengajuan verifikasi akun psikolog Anda ditolak.')
            ->line('Alasan penolakan:')
            ->line($this->reason)
            ->line('Anda dapat mengajukan ulang dengan melengkapi dokumen yang diperlukan.')
            ->action('Perbaiki Profil', url('/psikolog/dashboard'))
            ->line('Jika ada pertanyaan, silakan hubungi tim support kami.')
            ->salutation('Salam, Tim Kutkatha');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'psikolog_rejected',
            'title' => 'Verifikasi Ditolak',
            'message' => 'Pengajuan verifikasi Anda ditolak. Alasan: ' . $this->reason,
            'action_url' => '/psikolog/dashboard',
        ];
    }
}
