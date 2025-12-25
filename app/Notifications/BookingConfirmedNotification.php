<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingConfirmedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private Booking $booking;

    /**
     * Create a new notification instance.
     */
    public function __construct(Booking $booking)
    {
        $this->booking = $booking;
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
        $schedule = $this->booking->schedule;
        $psikolog = $schedule->psikolog;

        return (new MailMessage)
            ->subject('Booking Dikonfirmasi - ' . $this->booking->booking_code)
            ->greeting('Halo ' . $notifiable->name . '!')
            ->line('Booking konsultasi Anda telah dikonfirmasi.')
            ->line('Detail Konsultasi:')
            ->line('• Kode Booking: ' . $this->booking->booking_code)
            ->line('• Psikolog: ' . $psikolog->user->name)
            ->line('• Tanggal: ' . $schedule->date->format('d M Y'))
            ->line('• Waktu: ' . $schedule->formatted_time)
            ->line('• Tipe: ' . $schedule->consultation_type_name)
            ->when($schedule->consultation_type === 'offline', function ($mail) use ($schedule) {
                return $mail->line('• Lokasi: ' . $schedule->location);
            })
            ->action('Lihat Detail', url('/user/booking/' . $this->booking->id))
            ->line('Pastikan Anda hadir tepat waktu.')
            ->salutation('Salam, Tim Kutkatha');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'booking_confirmed',
            'title' => 'Booking Dikonfirmasi',
            'message' => 'Booking Anda telah dikonfirmasi oleh ' . $this->booking->schedule->psikolog->user->name,
            'booking_id' => $this->booking->id,
            'booking_code' => $this->booking->booking_code,
            'action_url' => '/user/booking/' . $this->booking->id,
        ];
    }
}
