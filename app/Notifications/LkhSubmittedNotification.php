<?php

namespace App\Notifications;

use App\Models\Lkh;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LkhSubmittedNotification extends Notification implements ShouldQueue
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
            ->subject('LKH Baru Menunggu Approval - ' . $this->lkh->tanggal->format('d F Y'))
            ->greeting('Halo ' . $notifiable->name . ',')
            ->line('Ada LKH baru yang menunggu approval Anda.')
            ->line('**Pegawai:** ' . $this->lkh->user->name)
            ->line('**Tanggal:** ' . $this->lkh->tanggal->format('d F Y'))
            ->line('**Uraian Kegiatan:** ' . $this->lkh->uraian_kegiatan)
            ->action('Review & Approve', url('/lkh/pending/approval'))
            ->line('Silakan review dan berikan approval segera.')
            ->salutation('Terima kasih');
    }
}
