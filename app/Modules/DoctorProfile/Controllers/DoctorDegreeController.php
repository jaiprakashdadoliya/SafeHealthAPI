<?php

namespace App\Modules\DoctorProfile\Controllers;

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
use App\Modules\DoctorProfile\Models\DoctorProfile as Doctors;
use App\Modules\DoctorProfile\Models\DoctorDegree;

/**
 * DoctorDegreeController
 *
 * @package                ILD India Registry
 * @subpackage             DoctorDegreeController
 * @category               Controller
 * @DateOfCreation         30 may 2018
 * @ShortDescription       This controller to handle all the operation related to 
                           doctors degree
 **/

class DoctorDegreeController extends Controller
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

        // Init Doctor degree Model Object
        $this->doctorDegreeObj = new DoctorDegree();

        // Init exception library object
        $this->exceptionLibObj = new ExceptionLib();

        
    }

    /**
    * @DateOfCreation        30 May 2018
    * @ShortDescription      This function is responsible to get the Degree list if doctors 
    * @return                Array of status and message
    */
    public function getDegreeList(Request $request, $user_id=NULL)
    {
        $requestData = $this->getRequestData($request);

        if($request->isMethod('get')){
            $requestData['user_id'] = $this->securityLibObj->decrypt($user_id);
            $method = Config::get('constants.REQUEST_TYPE_GET');
        }else{
            $requestData['user_id'] = $request->user()->user_id;            
            $method = Config::get('constants.REQUEST_TYPE_POST');
        }

        $doctorsDegree  = $this->doctorDegreeObj->getDegreeList($requestData, $method);
        return $this->resultResponse(
                Config::get('restresponsecode.SUCCESS'), 
                $doctorsDegree, 
                [],
                trans('DoctorProfile::messages.doctors_degree_list'),
                $this->http_codes['HTTP_OK']
            );
    }

    /**
    * @DateOfCreation        30 May 2018
    * @ShortDescription      Get a validator for an incoming Degree request
    * @param                 \Illuminate\Http\Request  $request
    * @return                \Illuminate\Contracts\Validation\Validator
    */
    protected function degreeValidations($requestData){
        $errors         = [];
        $error          = false;
        $validationData = [];

        // Check the login type is Email or Mobile
        $validationData = [
            'doc_deg_name' 		   => 'required|max:150',
            'doc_deg_passing_year' => 'required|max:4|min:4',
            'doc_deg_institute'    => 'required|max:150'
        ];
      
        $validator  = Validator::make(
            $requestData,
            $validationData
        );

        if($validator->fails()){
            $error  = true;
            $errors = $validator->errors();
        }

        return ["error" => $error,"errors"=>$errors];
    }

    /**
    * @DateOfCreation        30 May 2018
    * @ShortDescription      This function is responsible for insert Degree Data 
    * @param                 Array $request   
    * @return                Array of status and message
    */
    public function store(Request $request)
    {   
        $requestData = $this->getRequestData($request);

        if(!isset($requestData['user_id']) ){
            $requestData['user_id']     = $request->user()->user_id;
            $requestData['user_type']   = $request->user()->user_type;
        }else{
            $requestData['user_id']     = $this->securityLibObj->decrypt($requestData['user_id']);
            unset($requestData['_token']);            
        }

        $validate = $this->degreeValidations($requestData);
        
        if($validate["error"]){
            return $this->resultResponse(
                Config::get('restresponsecode.ERROR'), 
                [], 
                $validate['errors'],
                trans('DoctorProfile::messages.validation_error'), 
                $this->http_codes['HTTP_OK']
            ); 
        }
        $degreeInsertData     = $this->doctorDegreeObj->doInsertDegree($requestData);
        if($degreeInsertData){
            return $this->resultResponse(
                    Config::get('restresponsecode.SUCCESS'), 
                    $degreeInsertData, 
                    [],
                    trans('DoctorProfile::messages.doctors_degree_data_inserted'),
                    $this->http_codes['HTTP_OK']
                );
        }else{
            return $this->resultResponse(
                Config::get('restresponsecode.ERROR'), 
                [], 
                [],
                trans('DoctorProfile::messages.doctors_degree_data_not_inserted'),
                $this->http_codes['HTTP_OK']
            );
        }
                 
    }

    /**
    * @DateOfCreation        30 May 2018
    * @ShortDescription      This function is responsible for update Degree Data 
    * @param                 Array $request   
    * @return                Array of status and message
    */
    public function update(Request $request)
    {   
    	$requestData        = $this->getRequestData($request);
        if(isset($requestData['user_id'])){
            unset($requestData['user_id']);
        }
        $validate           = $this->degreeValidations($requestData);
        
        if($validate["error"]){
            return $this->resultResponse(
                Config::get('restresponsecode.ERROR'), 
                [], 
                $validate['errors'],
                trans('DoctorProfile::messages.validation_error'), 
                $this->http_codes['HTTP_OK']
            ); 
        }

        $degreeUpdateData   = $this->doctorDegreeObj->doUpdateDegree($requestData);
        if($degreeUpdateData){
            return $this->resultResponse(
                Config::get('restresponsecode.SUCCESS'), 
                $degreeUpdateData, 
                [],
                trans('DoctorProfile::messages.doctors_degree_data_updated'),
                $this->http_codes['HTTP_OK']
            );
        }else{
            return $this->resultResponse(
                Config::get('restresponsecode.ERROR'), 
                [], 
                [],
                trans('DoctorProfile::messages.doctors_degree_data_not_updated'),
                $this->http_codes['HTTP_OK']
            );
        }
    }

    /**
    * @DateOfCreation        31 May 2018
    * @ShortDescription      This function is responsible for delete Degree Data 
    * @param                 Array $request
    * @return                Array of status and message
    */
   	public function destroy(Request $request)
   	{
   		$requestData = $this->getRequestData($request);
        $primaryKey = $this->doctorDegreeObj->getTablePrimaryIdColumn();
        $primaryId = $this->securityLibObj->decrypt($requestData[$primaryKey]);
        $isPrimaryIdExist = $this->doctorDegreeObj->isPrimaryIdExist($primaryId);
        
        if(!$isPrimaryIdExist){
            return $this->resultResponse(
                Config::get('restresponsecode.ERROR'), 
                [], 
                [$primaryKey=> [trans('DoctorProfile::messages.doctors_degree_not_exist')]],
                trans('DoctorProfile::messages.doctors_degree_not_exist'), 
                $this->http_codes['HTTP_OK']
            ); 
        }
   		
		$deleteDegree = $this->doctorDegreeObj->doDeleteDegree($primaryId);
		if($deleteDegree){
            return $this->resultResponse(
                Config::get('restresponsecode.SUCCESS'), 
                [], 
                [],
                trans('DoctorProfile::messages.doctors_degree_data_deleted'),
                $this->http_codes['HTTP_OK']
            );
        }else{
            return $this->resultResponse(
                Config::get('restresponsecode.ERROR'), 
                [], 
                [],
                trans('DoctorProfile::messages.doctors_degree_data_not_deleted'),
                $this->http_codes['HTTP_OK']
            );
        }   			
   	}
   	
}
