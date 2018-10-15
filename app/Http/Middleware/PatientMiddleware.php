<?php

namespace App\Http\Middleware;

use Closure;

class PatientMiddleware
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
        if ($request->user() && $request->user()->user_type != Config::get('constants.USER_TYPE_PATIENT'))
        {
            $this->http_codes = $this->http_status_codes();
            return $this->resultResponse(
                        Config::get('restresponsecode.UNAUTHENTICATE'), 
                        [], 
                        ['user'=> trans('Auth::messages.permission_denied')],
                        trans('Auth::messages.permission_denied'), 
                        $this->http_codes['HTTP_EXCEPTION']
                  );
        }
        return $next($request);
    }
}
