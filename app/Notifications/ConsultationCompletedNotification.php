<?php

namespace App\Notifications;

use App\Models\Consultation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ConsultationCompletedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private Consultation $consultation;

    /**
     * Create a new notification instance.
     */
    public function __construct(Consultation $consultation)
    {
        $this->consultation = $consultation;
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
        $booking = $this->consultation->booking;
        $psikolog = $booking->schedule->psikolog;

        $mail = (new MailMessage)
            ->subject('Hasil Konsultasi Tersedia - ' . $booking->booking_code)
            ->greeting('Halo ' . $notifiable->name . '!')
            ->line('Konsultasi Anda telah selesai dan hasil konsultasi sudah tersedia.')
            ->line('Psikolog: ' . $psikolog->user->name)
            ->line('Ringkasan: ' . \Illuminate\Support\Str::limit($this->consultation->summary, 200))
            ->action('Lihat Hasil Lengkap', url('/user/consultation/' . $this->consultation->id));

        if ($this->consultation->next_session_date) {
            $mail->line('Sesi berikutnya disarankan pada: ' . $this->consultation->next_session_date->format('d M Y'));
        }

        return $mail
            ->line('Jangan lupa untuk memberikan feedback Anda!')
            ->salutation('Salam, Tim Kutkatha');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'consultation_completed',
            'title' => 'Hasil Konsultasi Tersedia',
            'message' => 'Hasil konsultasi Anda dengan ' . $this->consultation->booking->schedule->psikolog->user->name . ' sudah tersedia.',
            'consultation_id' => $this->consultation->id,
            'booking_code' => $this->consultation->booking->booking_code,
            'action_url' => '/user/consultation/' . $this->consultation->id,
        ];
    }
}
