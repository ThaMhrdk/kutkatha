<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewBookingNotification extends Notification implements ShouldQueue
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

        return (new MailMessage)
            ->subject('Booking Baru - ' . $this->booking->booking_code)
            ->greeting('Halo ' . $notifiable->name . '!')
            ->line('Anda mendapat booking konsultasi baru.')
            ->line('Detail Booking:')
            ->line('• Kode Booking: ' . $this->booking->booking_code)
            ->line('• Klien: ' . $this->booking->user->name)
            ->line('• Tanggal: ' . $schedule->date->format('d M Y'))
            ->line('• Waktu: ' . $schedule->formatted_time)
            ->line('• Tipe: ' . $schedule->consultation_type_name)
            ->line('• Keluhan: ' . $this->booking->complaint)
            ->action('Lihat Detail Booking', url('/psikolog/booking/' . $this->booking->id))
            ->line('Silakan konfirmasi setelah klien melakukan pembayaran.')
            ->salutation('Salam, Tim Kutkatha');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'new_booking',
            'title' => 'Booking Baru',
            'message' => 'Anda mendapat booking baru dari ' . $this->booking->user->name,
            'booking_id' => $this->booking->id,
            'booking_code' => $this->booking->booking_code,
            'action_url' => '/psikolog/booking/' . $this->booking->id,
        ];
    }
}
