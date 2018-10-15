<?php
namespace App\Libraries;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
/**
 * ExceptionLib Class
 *
 * @package                Safe Health
 * @subpackage             ExceptionLib
 * @category               Library
 * @DateOfCreation         28 May 2018
 * @ShortDescription       This Library is responsible for handling exception data
 */
class ExceptionLib {
	
    /**
    * @DateOfCreation        28 May 2018
    * @ShortDescription      This function is responsible to change exception message 
                             according to environment
    * @param                 Exception $exceptionObj
    * @return                String
    */
    public function reFormAndLogException($exceptionObj, $class, $function){
        $message = 'Exception occured in function : '.$class.'.'.$function. "\n";
        $message .=  $exceptionObj->getMessage(). "\n";
        Log::error($message);
        if (config('app.debug') && config('app.env') != "production") {
            return $message;
        }else{
            return __('messages.3001');
        }        
    }
}    
