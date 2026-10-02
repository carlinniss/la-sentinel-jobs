<?php

declare(strict_types=1);

namespace Modules\User\App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResumeSignupNotification extends Notification
{
    public function __construct(private readonly bool $hasResume) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject($this->hasResume ? 'Your resume is posted on LA Sentinel Jobs' : 'Finish posting your resume on LA Sentinel Jobs')
            ->greeting('Welcome to LA Sentinel Jobs');

        if ($this->hasResume) {
            return $message
                ->line('Your account is ready and your resume is saved privately to your profile.')
                ->line('You choose whether verified employers can discover it, and you can change that or remove your resume at any time.')
                ->action('Browse current jobs', route('listings.index'))
                ->line('Manage your resume anytime from My Profile.');
        }

        return $message
            ->line('Your account is ready. Add your resume whenever it is handy, from your phone or a computer.')
            ->action('Upload my resume', route('resume.post'))
            ->line('Log in with this email address and the password you created. Your resume stays private until you choose to let verified employers find it.');
    }
}
