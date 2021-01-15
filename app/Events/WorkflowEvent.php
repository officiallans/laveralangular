<?php

namespace App\Events;

use App\User;
use App\UserGroup;
use App\Workflow;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;

class WorkflowEvent extends Event
{
    use SerializesModels;
    public $workflow;
    public $workflowAuthor;
    public $user;
    public $sendTo;
    
    public function __construct($workflow)
    {
        $this->workflow = $workflow;
        $this->workflowAuthor = $workflow->author;
        
        $this->user = Auth::user();
        
        $this->sendTo = $workflow->author()->get()->first()->groups()->get()->map(function (UserGroup $groups) {
            return [$groups->users()->get(), $groups->author()->get()];
        })->collapse()->collapse()->filter(function ($user) {
            return $user->type !== 'participant' && $this->workflowAuthor->id !== $user->id;
        })->merge(User::where('type', 'main')->get())->unique(function ($user) {
            return $user->id;
        })->toArray();
    }

    /**
     * Get the channels the event should be broadcast on.
     *
     * @return array
     */
    public function broadcastOn()
    {
        return [];
    }
}
