<?php

namespace App\Console\Commands;

use App\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class Message extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'message {header} {content}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send some message';
    

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $header = $this->argument('header');
        $content = $this->argument('content');
        $users = User::all();
        
        foreach ($users as $user) {
            $sendTo = $user->email;
            $sendToName = $user->name;

            Mail::send('emails.profile.message', compact('header', 'content'), function ($message) use ($sendTo, $sendToName, $header) {
                $message->to($sendTo, $sendToName)->subject($header);
            });
        }
    }
}
