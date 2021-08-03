<?php

namespace App\Http\Middleware;

use App\User;
use Closure;

class CheckUser
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $token_key = $request->header('token');
        if (!$token_key){
            return response()->json(['msg' => 'Unauthorized'],401);
        }
        $token = User::where('token',$token_key)->first();
        if (empty($token)){
            return response()->json(['msg' => 'Unauthorized'],401);
        }
        return $next($request);
    }
}
