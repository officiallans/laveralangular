<?php

namespace App\Console\Commands\Workflow;

use App\User;
use App\Workflow;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class Vacation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'workflow:vacation';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send reminder about yearly vacation';


    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $today = new \DateTime();
        $year = $today->format('Y');
        $users = User::get()->map(function ($user) use ($year) {
            /* @var $user User */
            $user['vacation'] = $user->userWorkflow('vacation', $year);
            $user['diff'] = Workflow::MAX_VACATION_DURATION - $user['vacation'];
            return $user;
        })->toArray();

        foreach ($users as $user) {
            /* @var $user User */
            $sendTo = $user['email'];
            $sendToName = $user['name'];
            $subject = 'Нагадування про відпустку';
            if($user['vacation'] < Workflow::MAX_VACATION_DURATION) {
                Mail::send('emails.workflow.vacation', compact('subject', 'user'), function ($message) use ($sendTo, $sendToName, $subject) {
                    $message->to($sendTo, $sendToName)->subject($subject);
                });
            }
        }
    }
}
