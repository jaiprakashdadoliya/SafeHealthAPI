<?php 
namespace App\Traits;
use Illuminate\Support\Facades\Auth;
use App\Traits\RestApi;
use Config;
use Illuminate\Http\Request;

/**
 * SessionTrait
 *
 * @package                Safe Health
 * @subpackage             SessionTrait
 * @category               Trait
 * @DateOfCreation         23 April 2018
 * @ShortDescription       This trait is responsible to Check he use is valid or not
 **/
trait SessionTrait
{
     /**
    * @DateOfCreation        23 Apr 2018
    * @ShortDescription      This function is responsible to get the check user  
    * @return                Array with status and user intance
    */
     protected function isUserNotValid($type, $request){
        if($type != $request->user()->user_type){
            return $this->resultResponse(
                Config::get('restresponsecode.ERROR'), 
                [], 
                ['user'=> trans('Auth::messages.user_not_found')],
                trans('Auth::messages.user_not_found'), 
                $this->http_codes['HTTP_OK']
            );
        }else{ 
            return false;
        }
     }
} 	
