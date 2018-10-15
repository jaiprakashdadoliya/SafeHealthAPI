<?php 
namespace App\Traits;
use Illuminate\Support\Facades\Auth;

/**
 * FunctionalTrait
 *
 * @package                Safe Health
 * @subpackage             FunctionalTrait
 * @category               Trait
 * @DateOfCreation         23 April 2018
 * @ShortDescription       This trait is responsible to Check he use is from API or from Web
 **/
trait FunctionalTrait
{
     /**
    * @DateOfCreation        23 Apr 2018
    * @ShortDescription      This function is responsible to get the check user  
    * @return                Array with status and user intance
    */
     protected function checkUser(){
     	$status = true;
     	$data = [];
     	if(Auth::guard('api')->check()){
     		$data['user'] = Auth::guard('api');
        }elseif (Auth::guard('doctors')->check()) {
           $data['user'] = Auth::guard('doctors');
        }else{
        	$data['user'] = '';
            $status = false;
        }
        return ['status' => $status, 'data'=>$data];
     }
} 	

