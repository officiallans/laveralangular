<?php

namespace App\Console\Commands\Report;

use App\User;
use App\Workflow;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class Reminder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'report:reminder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send reminder about report';
    

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $users = User::all()->filter(function ($user) {
            $options = $user->options;
            return !$options['not_report_reminder'];
        });
        foreach ($users as $user) {
            $sendTo = $user->email;
            $sendToName = $user->name;
            $subject = 'Нагадування про звіт';
            Mail::send('emails.reports.reminder', compact('subject'), function ($message) use ($sendTo, $sendToName, $subject) {
                $message->to($sendTo, $sendToName)->subject($subject);
            });
        }
    }
}
