<?php

namespace App\Events;

use App\User;
use App\UserGroup;
use App\Workflow;
use Illuminate\Queue\SerializesModels;

class ResetPasswordEvent extends Event
{
    use SerializesModels;
    public $user;
    public $new_password;

    /**
     * Create a new event instance.
     *
     * @param User $user
     * @param string $new_password
     */
    public function __construct(User $user, $new_password)
    {
        $this->user = $user;
        $this->new_password = $new_password;
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
