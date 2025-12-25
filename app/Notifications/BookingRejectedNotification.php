<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingRejectedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private Booking $booking;
    private string $reason;

    /**
     * Create a new notification instance.
     */
    public function __construct(Booking $booking, string $reason)
    {
        $this->booking = $booking;
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
            ->subject('Booking Ditolak - ' . $this->booking->booking_code)
            ->greeting('Halo ' . $notifiable->name . ',')
            ->line('Mohon maaf, booking konsultasi Anda tidak dapat dikonfirmasi.')
            ->line('Kode Booking: ' . $this->booking->booking_code)
            ->line('Alasan: ' . $this->reason)
            ->line('Jika Anda sudah melakukan pembayaran, dana akan dikembalikan dalam 3-5 hari kerja.')
            ->action('Cari Jadwal Lain', url('/user/psikolog'))
            ->line('Silakan pilih jadwal lain atau psikolog lain.')
            ->salutation('Salam, Tim Kutkatha');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'booking_rejected',
            'title' => 'Booking Ditolak',
            'message' => 'Booking Anda ditolak. Alasan: ' . $this->reason,
            'booking_id' => $this->booking->id,
            'booking_code' => $this->booking->booking_code,
            'action_url' => '/user/psikolog',
        ];
    }
}
