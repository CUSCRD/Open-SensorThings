<?php

namespace App\Http\Middleware;

use App\Constant\TablesName;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Response;

class EnsureTokenIsValid
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $invalidToken = ($request->token === null && $request->header('token') === null);
        if ($invalidToken) {
            return response()->json([
                'success' => false,
                'message' => 'invalid token',
            ])->setStatusCode(401);
        }
        $user = DB::table(TablesName::Users)
            ->where('remember_token', $request->token)
            ->get();
        if (empty($user->toArray())) {
            return response()->json([
                'success' => false,
                'message' => 'invalid token',
            ])->setStatusCode(401);
        }
        return $next($request);
    }
}
