<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VerifyEmailNotification extends Notification
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
    public function toMail($notifiable)
    {
        if ($notifiable->email_verification_token) {
            $verificationUrl = url('api/verify/' . $notifiable->email_verification_token);
            return (new MailMessage)
                ->subject('Xác nhận thành viên trên XemPhim')
                ->view('emails.email_verification', [
                    'userName' => $notifiable->name,
                    'verificationUrl' => $verificationUrl
                ]);
        }

        if ($notifiable->password_retrieval_code) {
            $verificationUrl = url('api/reset-password/' . $notifiable->password_retrieval_code);
            return (new MailMessage)
                ->subject('Lấy lại mật khẩu trên XemPhim')
                ->view('emails.forgot_password', [
                    'userName' => $notifiable->name,
                    'verificationUrl' => $verificationUrl
                ]);
        }

        $verificationUrl = url("api/verify/update-profile/{$notifiable->id}/{$notifiable->email}");
        return (new MailMessage)
            ->subject('Cập nhật thông tin trên XemPhim')
            ->view('emails.email_update_profile', [
                'userName' => $notifiable->name,
                'verificationUrl' => $verificationUrl
            ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
