<?php

namespace App\Notifications;

use App\Models\Report;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReportSentNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private Report $report;

    /**
     * Create a new notification instance.
     */
    public function __construct(Report $report)
    {
        $this->report = $report;
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
            ->subject('Laporan Baru dari Kutkatha - ' . $this->report->title)
            ->greeting('Yth. ' . $notifiable->name . ',')
            ->line('Ada laporan baru dari sistem Kutkatha yang perlu Anda tinjau.')
            ->line('Detail Laporan:')
            ->line('• Judul: ' . $this->report->title)
            ->line('• Tipe: ' . $this->report->report_type_name)
            ->line('• Periode: ' . $this->report->period_start->format('d M Y') . ' - ' . $this->report->period_end->format('d M Y'))
            ->line('Ringkasan Data:')
            ->line('• Total Konsultasi: ' . number_format($this->report->total_consultations))
            ->line('• Total Pengguna Baru: ' . number_format($this->report->total_users))
            ->line('• Total Psikolog Aktif: ' . number_format($this->report->total_psikologs))
            ->action('Lihat Laporan Lengkap', url('/pemerintah/report/' . $this->report->id))
            ->salutation('Hormat kami, Tim Kutkatha');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'report_sent',
            'title' => 'Laporan Baru',
            'message' => 'Laporan "' . $this->report->title . '" telah dikirim.',
            'report_id' => $this->report->id,
            'action_url' => '/pemerintah/report/' . $this->report->id,
        ];
    }
}
