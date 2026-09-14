<?php

namespace App\Notifications;

use App\Models\TeacherAssignment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TeacherAssignmentNotification extends Notification
{
    use Queueable;

    public function __construct(
        public TeacherAssignment $assignment
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array<int, string>
     */
    public function via($notifiable): array
    {
        return ['mail'];
    }

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

        $className = $this->assignment->schoolClass?->name ?? 'Class';
        $sectionName = $this->assignment->section?->name ?? '';
        $subjectName = $this->assignment->subject?->name ?? 'Subject';
        $academicYear = $this->assignment->academicYear?->name ?? '';

        $classLabel = $className . ($sectionName ? " (Section {$sectionName})" : '');
        $dashboardUrl = route('login');

        return (new MailMessage)
            ->subject("{$schoolShort} - New Academic Assignment: {$classLabel} - {$subjectName}")
            ->greeting("Hello {$notifiable->name},")
            ->line("You have been assigned a new academic subject for evaluation and mark entry at **{$schoolShort}**.")
            ->line("**Academic Year:** {$academicYear}")
            ->line("**Class & Section:** {$classLabel}")
            ->line("**Subject:** {$subjectName}")
            ->line("You can now access your faculty portal to view your assigned student roster and record marks.")
            ->action('Access Faculty Portal', $dashboardUrl)
            ->line("If you have any questions regarding your curriculum assignment, please contact the YES INDIA Technical Team.")
            ->salutation("Best regards,\n{$schoolShort}");
    }
}
