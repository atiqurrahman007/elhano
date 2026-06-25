<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
class CheckReffer
{

    public function handle($request, Closure $next)
    {


        return $next($request);
    }

}
