<?php

namespace App\Http\Middleware\Group;

use App\UserGroup;
use Closure;
use Symfony\Component\HttpKernel\Exception\HttpException;

class Create
{
    /**
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($request->user()->isType('main') || $request->user()->isType('manager')) {
            return $next($request);
        } else {
            throw new HttpException(403, "GroupCreate access denied");
        }
    }
}
