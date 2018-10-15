<?php

namespace App\Http\Middleware;
use App\Traits\RestApi;
use Config;
use Closure;
use Cookie;

class AuthTokenMiddleware
{
    use RestApi;
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    { 
        $authToken = Cookie::get(Config::get('constants.AUTH_TOKEN_NAME'));
          if($authToken != ''){
              $request->headers->set('Authorization', 'Bearer '.$authToken);
          }          
          return $next($request);
    }
}
