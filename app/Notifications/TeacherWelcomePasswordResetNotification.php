<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class TeacherWelcomePasswordResetNotification extends ResetPassword
{
    /**
     * Build the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return MailMessage
     */
    public function toMail($notifiable): MailMessage
    {
        $schoolName = setting('school_name', config('app.name', 'Result Management System'));
        $schoolShort = setting('school_short_name', 'RUIHSS POONCH');
        $appName = setting('app_name', 'Result Management System');
        $portalTitle = setting('portal_title', 'Examination & Result Portal');

        $resetUrl = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        $expireMinutes = config('auth.passwords.'.config('auth.defaults.passwords').'.expire', 60);

        return (new MailMessage)
            ->subject("{$schoolShort} - Teacher Account Created - Set Password & Login")
            ->greeting("Hello {$notifiable->name},")
            ->line("An official faculty account has been created for you on the **{$schoolShort}** {$appName}.")
            ->line("Your registered login username is: **{$notifiable->email}**")
            ->line("To activate your account and access student mark entry, please set your personal password by clicking the button below:")
            ->action('Set Password & Access Portal', $resetUrl)
            ->line("This secure link will expire in {$expireMinutes} minutes.")
            ->line("After setting your password, you will be able to log in immediately with your email and new password.")
            ->line("If you have any questions or did not expect this invitation, please contact the YES INDIA Technical Team.")
            ->salutation("Best regards,\n{$schoolShort}");
    }
}
