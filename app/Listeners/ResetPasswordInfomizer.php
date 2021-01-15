<?php

namespace App\Listeners;

use App\Events\ResetPasswordEvent;
use Illuminate\Support\Facades\Mail;

class ResetPasswordInfomizer
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  ResetPasswordEvent $event
     * @return void
     */
    public function handle(ResetPasswordEvent $event)
    {
        $user = $event->user;
        $new_password = $event->new_password;
        $subject = 'Скидання паролю для ' . $user->name;

        Mail::send('emails.profile.reset', compact('user', 'new_password', 'subject'), function ($message) use ($user, $subject) {
            $message->to($user->email, $user->name)->subject($subject);
        });
    }
}
