<?php

namespace App\Modules\Accounts\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Auth;
use Session;
use App\Traits\RestApi;
use Config;
use DB;
use Illuminate\Support\Facades\Validator;
use App\Libraries\SecurityLib;
use App\Libraries\ExceptionLib;
use App\Modules\Accounts\Models\Accounts;

/**
 * AccountsController
 *
 * @package                RxHealth
 * @subpackage             AccountsController
 * @category               Controller
 * @DateOfCreation         04 Sep 2018
 * @ShortDescription       This controller to handle all the operation related to 
                           Accounts
 **/
class AccountsController extends Controller
{

     use  RestApi;

    // @var Array $http_codes
    // This protected member contains Http Status Codes
    protected $http_codes = [];

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(Request $request)
    {
        $this->http_codes = $this->http_status_codes();

        // Init security library object
        $this->securityLibObj = new SecurityLib(); 

        // Init Doctor experience Model Object
        $this->accountsObj = new Accounts();

        // Init exception library object
        $this->exceptionLibObj = new ExceptionLib();        
    }

    /**
    * @DateOfCreation        21 May 2018
    * @ShortDescription      This function is responsible to get the experience list if doctors 
    * @param                 Integer $user_id   
    * @return                Array of status and message
    */
    public function paymentsHistory(Request $request)
    {
        $requestData = $this->getRequestData($request);
        $requestData['user_id'] = $request->user()->user_id;
        $paymentsHistory  = $this->accountsObj->getPaymentHistory($requestData);
        if($paymentsHistory){
            return $this->resultResponse(
                Config::get('restresponsecode.SUCCESS'), 
                $paymentsHistory, 
                [],
                trans('Accounts::messages.payment_history_fetch_success'),
                $this->http_codes['HTTP_OK']
            );
        }else{
            return $this->resultResponse(
                Config::get('restresponsecode.ERROR'), 
                [], 
                [],
                trans('Accounts::messages.not_able_to_get_fetch_payments'),
                $this->http_codes['HTTP_OK']
            );    
        }   
    }

    /**
    * @DateOfCreation        21 May 2018
    * @ShortDescription      This function is responsible to get the experience list if doctors 
    * @param                 Integer $user_id   
    * @return                Array of status and message
    */
    public function invoicesHistory(Request $request)
    {
        $requestData = $this->getRequestData($request);
        $requestData['user_id'] = $request->user()->user_id;
        $invoicesHistory  = $this->accountsObj->getInvoiceHistory($requestData);
        return $this->resultResponse(
                Config::get('restresponsecode.SUCCESS'), 
                $invoicesHistory, 
                [],
                trans('Accounts::messages.invoice_history_fetch_success'),
                $this->http_codes['HTTP_OK']
            );
    }
}
