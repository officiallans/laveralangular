<?php

namespace App\Console\Commands\Workflow;

use App\Workflow;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class NextInfo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'workflow:next-info';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send info about tomorrow workflow';
    

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $today = new \DateTime();
        $tomorrow = $today->add(new \DateInterval('P1D'))->format('Y-m-d');
        $data = Workflow::confirmed(false)->where('start_at', $tomorrow)->with('author')->get();
        
        foreach ($data as $workflow) {
            $sendTo = $workflow->author->email;
            $sendToName = $workflow->author->name;
            $subject = 'Нагадування про відпрацювання';
            $date = date('d.m.Y', strtotime($workflow->start_at));
            Mail::send('emails.workflow.next-info', compact('type', 'date', 'subject'), function ($message) use ($sendTo, $sendToName, $subject) {
                $message->to($sendTo, $sendToName)->subject($subject);
            });
        }
    }
}
