<?php

namespace App\Http\Middleware\Group;

use App\UserGroup;
use Closure;
use Symfony\Component\HttpKernel\Exception\HttpException;

class Update
{
    /**
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $group = UserGroup::findOrFail($request->group);
        if ($group->author_id === $request->user()->id || $request->user()->isType('main')) {
            return $next($request);
        } else {
            throw new HttpException(403, "GroupUpdate access denied");
        }
    }
}
