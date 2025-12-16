<?php

namespace App\Notifications;

use App\Models\Lkh;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LkhRejectedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Lkh $lkh
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('LKH Ditolak - ' . $this->lkh->tanggal->format('d F Y'))
            ->greeting('Halo ' . $notifiable->name . ',')
            ->line('LKH Anda untuk tanggal **' . $this->lkh->tanggal->format('d F Y') . '** telah ditolak.')
            ->line('**Uraian Kegiatan:** ' . $this->lkh->uraian_kegiatan)
            ->line('**Catatan:** ' . ($this->lkh->catatan_approval ?? 'Tidak ada catatan'))
            ->action('Lihat Detail LKH', url('/lkh/' . $this->lkh->id))
            ->line('Silakan perbaiki LKH sesuai catatan dan submit ulang.')
            ->salutation('Terima kasih');
    }
}

