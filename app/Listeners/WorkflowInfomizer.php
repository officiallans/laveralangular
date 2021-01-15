<?php

namespace App\Listeners;

use App\Events\WorkflowEvent;
use App\Workflow;
use Illuminate\Support\Facades\Mail;

class WorkflowInfomizer
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
     * @param  WorkflowEvent $event
     * @return void
     */
    public function handle(WorkflowEvent $event)
    {
        $sendTo = $event->sendTo;
        $workflowAuthor = $event->workflowAuthor;
        $workflow = $event->workflow;
        $user = $event->user;

        if ($workflow->trashed()) {
            $view = 'emails.workflow.delete';
            $subject = 'Видалення ';
        } elseif ($workflow->wasRecentlyCreated) {
            $view = 'emails.workflow.new';
            $subject = 'Додавання ';
        } elseif ((bool) $workflow->getOriginal('confirmed') !== $workflow->confirmed && $workflow->confirmed) {
            $view = 'emails.workflow.confirm';
            $subject = 'Підтвердження ';
        } else {
            $view = 'emails.workflow.update';
            $subject = 'Оновлення ';
        }

        $type = strtolower(Workflow::$typeTranslate[$workflow->type]);
        
        $subject .= $type . ' для ' . $workflowAuthor->name;
        if($user->id !== $workflowAuthor->id) {
            $sendTo[] = $workflowAuthor;
        }
        $balance = $user->userBalance();
        foreach ($sendTo as $mailer) {
            Mail::send($view, compact('workflowAuthor', 'workflow', 'user', 'balance', 'type', 'subject'), function ($message) use ($mailer, $subject) {
                $message->to($mailer['email'], $mailer['name'])->subject($subject);
            });
        }
    }
}
