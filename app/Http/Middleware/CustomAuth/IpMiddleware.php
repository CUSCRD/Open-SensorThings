<?php


namespace App\Http\Middleware\CustomAuth;


use Closure;

class IpMiddleware
{
    const RESTRICT_IP=[
//        '127.0.0.1'
    ];
    public function handle($request, Closure $next)
    {
        // here instead of checking a single ip address we can do collection of ips
        //address in constant file and check with in_array function
        if (in_array($request->ip(),self::RESTRICT_IP)) {
            return response(['message'=>'ip is not allowed to access this site'],403);
        }
        return $next($request);
    }

}
