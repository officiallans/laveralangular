<?php

namespace App\Http\Middleware\Group;

use App\UserGroup;
use Closure;
use Symfony\Component\HttpKernel\Exception\HttpException;

class Show
{
    /**
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $user = $request->user();
        $group = UserGroup::with('users')->findOrFail($request->group);
        if (
            $user->isType('main') ||
            ($user->isType('manager') && $group->author_id === $user->id) ||
            $group->users()->where('users.id', $user->id)->count()
        ) {
            return $next($request);
        } else {
            throw new HttpException(403, "GroupShow access denied");
        }
    }
}
