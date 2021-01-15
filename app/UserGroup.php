<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\Finder\Exception\AccessDeniedException;

class UserGroup extends Model
{
    protected $table = 'user_groups';

    public function users()
    {
        return $this->belongsToMany('App\User', 'user_in_group', 'group_id', 'user_id');
    }

    public function author()
    {
        return $this->belongsTo('App\User', 'author_id', 'id')->withoutGlobalScope('active');
    }

    public function checkAccess(User $user)
    {
        if($user->type === 'main') return true; 
        if($user->type === 'manager' && $this->author_id === $user->id) return true; 
        $allowed = collect($this->users()->get())
            ->map(function ($user) {
                return $user->id;
            });

        if ($allowed->contains($user->id)) return true;
        throw new AccessDeniedException();
    }
}
