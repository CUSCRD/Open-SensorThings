<?php

namespace App\Http\Middleware\CustomAuth;

use Closure;
use Illuminate\Http\Request;
use SebastianBergmann\Environment\Console;

//giới hạn site có thể truy cập
class OGCCORS
{
    const VALID_IP=[
        "http://127.0.0.1:8001"
    ];
    public function handle(Request $request, Closure $next)
    {

        //chỉ định ip thể
//        $ip=$request->header("Origin");
//        if (in_array($ip,self::VALID_IP)){
//            $allowOrigin='Access-Control-Allow-Origin:  ' . $ip;
//            header($allowOrigin);
//        }

        //hoặc dấu sao chấp nhận mọi ip
        header('Access-Control-Allow-Origin:', '*');
        header('Access-Control-Allow-Headers:  Content-Type, X-Auth-Token, Authorization, Origin');
        header('Access-Control-Allow-Methods:  POST, PUT, GET, PATCH');


        return $next($request);
    }
}
