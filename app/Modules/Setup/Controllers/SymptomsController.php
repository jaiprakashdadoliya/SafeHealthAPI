<?php

namespace App\Modules\Setup\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Auth;
use Session;
use App\Traits\SessionTrait;
use App\Traits\RestApi;
use Config;
use DB;
use Illuminate\Support\Facades\Validator;
use App\Libraries\SecurityLib;
use App\Libraries\ExceptionLib;
use App\Modules\Setup\Models\Symptoms as Symptoms;

/**
 * SymptomsController
 *
 * @package                ILD India Registry
 * @subpackage             SymptomsController
 * @category               Controller
 * @DateOfCreation         18 june 2018
 * @ShortDescription       This controller to handle all the operation related to 
                           setup Symptoms
 **/
class SymptomsController extends Controller
{

    use SessionTrait, RestApi;

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

        // Init Symptoms Model Object
        $this->symptomsObj = new Symptoms();

        // Init exception library object
        $this->exceptionLibObj = new ExceptionLib();

        
    }
    /**
        * @DateOfCreation        21 May 2018
        * @ShortDescription      This function is responsible to get the Symptoms option list
        * @param                 Integer $user_id   
        * @return                Array of status and message
        */
    public function getSymptomsOptionList($symptomName = null)
    {
        $symptomsOptionList  = $this->symptomsObj->getSymptomsOptionList($symptomName);
        return $this->resultResponse(
                Config::get('restresponsecode.SUCCESS'), 
                $symptomsOptionList, 
                [],
                trans('Setup::messages.symptoms_option_list'),
                $this->http_codes['HTTP_OK']
            );
    }

    /**
        * @DateOfCreation        21 May 2018
        * @ShortDescription      This function is responsible to get the Symptoms option list
        * @param                 Integer $user_id   
        * @return                Array of status and message
        */
    public function getSymptomsOptionListSearch(Request $request)
    {
        $requestData = $this->getRequestData($request);
        $symptomName = isset($requestData['value']) ? $requestData['value']:'';
        if(!empty($symptomName)){
            $symptomsOptionList  = $this->symptomsObj->getSymptomsOptionListSearchByName($symptomName);
            //if(count($symptomsOptionList)<=0){
                $symptomsOptionList = $this->symptomsObj->getSymptomsOptionListBySnomedct($symptomName,$symptomsOptionList);
            //}
            return $this->resultResponse(
                    Config::get('restresponsecode.SUCCESS'), 
                    $symptomsOptionList, 
                    [],
                    trans('Setup::messages.symptoms_option_list'),
                    $this->http_codes['HTTP_OK']
                );
        }else{
            return $this->resultResponse(
                    Config::get('restresponsecode.SUCCESS'), 
                    [], 
                    [],
                    trans('Setup::messages.symptoms_option_list'),
                    $this->http_codes['HTTP_OK']
                );

        }
    }

}
