<?php
namespace App\Console\Commands\Workflow;

use App\Workflow;
use App\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class MonthlyInfo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'workflow:monthly-info';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send info about monthly workflows';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $start_month = new \DateTime(date('Y-m-01'));
        $end_month = clone $start_month;
        $end_month->add(new \DateInterval('P1M'))->sub(new \DateInterval('P1D'));

        $users = User::get()->map(function ($user) use ($start_month, $end_month) {
            /* @var $user User */
            $user->workflow = $user->workflow()->whereBetween('start_at', [$start_month->format('Y-m-d'), $end_month->format('Y-m-d')])->get();
            $user->info = array_merge($user->userBalance('array_all'), $user->userWorkflow('array_all'));
            return $user;
        })->toArray();
        foreach ($users as $user) {
            $sendTo = $user['email'];
            $sendToName = $user['name'];
            $subject = 'Щомісячна інформація про робочі процеси';
            Mail::send('emails.workflow.monthly-info', compact('subject', 'user', 'start_month', 'end_month'), function ($message) use ($sendTo, $sendToName, $subject) {
                $message->to($sendTo, $sendToName)->subject($subject);
            });
        }
    }
}