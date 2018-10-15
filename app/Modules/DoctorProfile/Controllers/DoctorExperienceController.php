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
use App\Modules\DoctorProfile\Models\DoctorExperience;

/**
 * DoctorExperienceController
 *
 * @package                RxHealth
 * @subpackage             DoctorExperienceController
 * @category               Controller
 * @DateOfCreation         21 may 2018
 * @ShortDescription       This controller to handle all the operation related to 
                           doctors experience
 **/
class DoctorExperienceController extends Controller
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

        // Init Doctor experience Model Object
        $this->doctorExperienceObj = new DoctorExperience();

        // Init exception library object
        $this->exceptionLibObj = new ExceptionLib();        
    }


    /**
    * @DateOfCreation        21 May 2018
    * @ShortDescription      This function is responsible to get the experience list if doctors 
    * @param                 Integer $user_id   
    * @return                Array of status and message
    */
    public function getExperienceList(Request $request, $user_id=NULL)
    {
        $requestData = $this->getRequestData($request);

        if($request->isMethod('get')){
            $requestData['user_id'] = $this->securityLibObj->decrypt($user_id);
            $method = Config::get('constants.REQUEST_TYPE_GET');
        }else{
            $requestData['user_id'] = $request->user()->user_id;            
            $method = Config::get('constants.REQUEST_TYPE_POST');
        }

        $doctorsExperince  = $this->doctorExperienceObj->getExperienceList($requestData, $method);
        return $this->resultResponse(
                Config::get('restresponsecode.SUCCESS'), 
                $doctorsExperince, 
                [],
                trans('DoctorProfile::messages.doctors_experience_list'),
                $this->http_codes['HTTP_OK']
            );
    }

    /**
    * @DateOfCreation        22 May 2018
    * @ShortDescription      Get a validator for an incoming Experience request
    * @param                 \Illuminate\Http\Request  $request
    * @return                \Illuminate\Contracts\Validation\Validator
    */
    protected function experienceValidations($requestData){
        $errors         = [];
        $error          = false;
        $validationData = [];
        if($requestData['doc_exp_end_month'] < $requestData['doc_exp_start_month']){
            $experience_validation = 'after';
            $experience_validationMessage = trans('DoctorProfile::messages.doctor_experience_after');
        }else{
            $experience_validation          = 'after_or_equal';
            $experience_validationMessage   = trans('DoctorProfile::messages.doctor_experience_after_or_equal');
        }
        // Check the login type is Email or Mobile
            $validationData = [
                'doc_exp_organisation_name' => 'required|max:150',
                'doc_exp_designation'       => 'required|max:150',
                'doc_exp_start_year'        => 'required|max:4|min:4',
                'doc_exp_start_month'       => 'required|max:2|min:1',
                'doc_exp_end_year'          => 'required|max:4|min:4|'.$experience_validation.':doc_exp_start_year',
                'doc_exp_end_month'         => 'required|max:2|min:1',
                'doc_exp_organisation_type' => 'required|max:2'
            ];
        
        $validationMessage = [
            'doc_exp_end_year.'.$experience_validation => $experience_validationMessage
        ];

        $validator  = Validator::make(
            $requestData,
            $validationData,
            $validationMessage
        );
            if($validator->fails()){
                $error  = true;
                $errors = $validator->errors();
            }
        return ["error" => $error,"errors"=>$errors];
    }


    /**
    * @DateOfCreation        24 May 2018
    * @ShortDescription      This function is responsible for insert experience Data 
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

        $validate = $this->experienceValidations($requestData);
        
        if($validate["error"]){
            return $this->resultResponse(
                Config::get('restresponsecode.ERROR'), 
                [], 
                $validate['errors'],
                trans('DoctorProfile::messages.validation_error'), 
                $this->http_codes['HTTP_OK']
            ); 
        }
        $experienceinsertData           = $this->doctorExperienceObj->doInsertExperience($requestData);
        if($experienceinsertData){
            return $this->resultResponse(
                    Config::get('restresponsecode.SUCCESS'), 
                    $experienceinsertData, 
                    [],
                    trans('DoctorProfile::messages.doctors_experience_data_inserted'),
                    $this->http_codes['HTTP_OK']
                );
        }else{
            return $this->resultResponse(
                Config::get('restresponsecode.ERROR'), 
                [], 
                [],
                trans('DoctorProfile::messages.doctors_experience_data_not_inserted'),
                $this->http_codes['HTTP_OK']
            );
        }
            
    }    
    /**
    * @DateOfCreation        22 May 2018
    * @ShortDescription      This function is responsible for Update experience Data 
    * @param                 Array $request   
    * @return                Array of status and message
    */
    public function update(Request $request)
    {   
        $requestData        = $this->getRequestData($request);
        if(isset($requestData['user_id'])){
            unset($requestData['user_id']);
        }
        $validate           = $this->experienceValidations($requestData);
        
        if($validate["error"]){
            return $this->resultResponse(
                Config::get('restresponsecode.ERROR'), 
                [], 
                $validate['errors'],
                trans('DoctorProfile::messages.validation_error'), 
                $this->http_codes['HTTP_OK']
            ); 
        }

        $experienceUpdateData   = $this->doctorExperienceObj->doUpdateExperience($requestData);
        if($experienceUpdateData){
            return $this->resultResponse(
                Config::get('restresponsecode.SUCCESS'), 
                $experienceUpdateData, 
                [],
                trans('DoctorProfile::messages.doctors_experience_data_updated'),
                $this->http_codes['HTTP_OK']
            );
        }else{
            return $this->resultResponse(
                Config::get('restresponsecode.ERROR'), 
                [], 
                [],
                trans('DoctorProfile::messages.doctors_experience_data_not_updated'),
                $this->http_codes['HTTP_OK']
            );
        }
         
    }

    /**
    * @DateOfCreation        24 May 2018
    * @ShortDescription      This function is responsible for delete experience Data 
    * @param                 Array $doc_exp_id   
    * @return                Array of status and message
    */
    public function destroy(Request $request)
    {   
        $requestData = $this->getRequestData($request);
        $primaryKey = $this->doctorExperienceObj->getTablePrimaryIdColumn();
        $primaryId = $this->securityLibObj->decrypt($requestData[$primaryKey]);
        $isPrimaryIdExist = $this->doctorExperienceObj->isPrimaryIdExist($primaryId);
        
        if(!$isPrimaryIdExist){
            return $this->resultResponse(
                Config::get('restresponsecode.ERROR'),
                [], 
                [$primaryKey=> [trans('DoctorProfile::messages.doctors_experience_not_exist')]],
                trans('DoctorProfile::messages.doctors_experience_not_exist'), 
                $this->http_codes['HTTP_OK']
            ); 
        }

        $experienceDeleteData   = $this->doctorExperienceObj->doDeleteExperience($primaryId);
        if($experienceDeleteData){
            return $this->resultResponse(
                    Config::get('restresponsecode.SUCCESS'), 
                    [], 
                    [],
                    trans('DoctorProfile::messages.doctors_experience_data_deleted'),
                    $this->http_codes['HTTP_OK']
                );
        }
        return $this->resultResponse(
                Config::get('restresponsecode.ERROR'), 
                [], 
                [],
                trans('DoctorProfile::messages.doctors_experience_data_not_deleted'),
                $this->http_codes['HTTP_OK']
            );

    }

}