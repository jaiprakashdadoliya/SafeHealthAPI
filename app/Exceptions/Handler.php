<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use App\Traits\RestApi;
use Config;

class Handler extends ExceptionHandler
{
    use RestApi;
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $exception)
    {
        $rest_config = $this->rest_config();
        $http_status = $this->http_status_codes();
        if($rest_config['rest_handle_exceptions']){  
            if (config('app.debug') && config('app.env') == "production") {
                 if($exception->getMessage() == "Unauthenticated."){
                    return $this->resultResponse(
                        Config::get('restresponsecode.UNAUTHENTICATE'), 
                        [], 
                        [],
                        $exception->getMessage(), 
                        $http_status['HTTP_EXCEPTION']
                  );
                }else{
                    return $this->resultResponse(
                        Config::get('restresponsecode.EXCEPTION'), 
                        [], 
                        [],
                        __('messages.3001'), 
                        $http_status['HTTP_EXCEPTION']
                  );
                }
            }else{
                if($exception->getMessage() == "Unauthenticated."){
                    return $this->resultResponse(
                        Config::get('restresponsecode.UNAUTHENTICATE'), 
                        [], 
                        [],
                        $exception->getMessage(), 
                        $http_status['HTTP_EXCEPTION']
                  );
                }else{
                    $line = $exception->getLine(); 
                    $filePath = $exception->getFile(); 
                    $filePathInfo = !empty($filePath) ? pathinfo($filePath):[];
                    $filePathInfoName =isset($filePathInfo['basename']) ? $filePathInfo['basename'] :'';
                    
                    return $this->resultResponse(
                        Config::get('restresponsecode.EXCEPTION'), 
                        [], 
                        [],
                        $exception->getMessage().'. Error occurring on '.$filePathInfoName.' and line number is '.$line.'.', 
                        $http_status['HTTP_EXCEPTION']
                      );
                }
            }
        }
        return parent::render($request, $exception);
    }
}


