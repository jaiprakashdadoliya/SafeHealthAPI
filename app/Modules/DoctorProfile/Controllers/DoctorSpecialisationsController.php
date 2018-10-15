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
use App\Modules\DoctorProfile\Models\DoctorSpecialisations;
use App\Libraries\DateTimeLib;

/**
 * DoctorSpecialisationsController
 *
 * @package                ILD India Registry
 * @subpackage             DoctorSpecializationController
 * @category               Controller
 * @DateOfCreation         21 may 2018
 * @ShortDescription       This controller to handle all the operation related to 
                           doctors Specialisations
 **/
class DoctorSpecialisationsController extends Controller
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

        // Init Doctor Specialisations Model Object
        $this->doctorSpecialisationsObj = new DoctorSpecialisations();

        // Init exception library object
        $this->exceptionLibObj = new ExceptionLib();

        // Init dateTime library object
        $this->dateTimeObj = new DateTimeLib();

        
    }
    /**
        * @DateOfCreation        21 May 2018
        * @ShortDescription      This function is responsible to get the Specialisations option list
        * @param                 Integer $user_id   
        * @return                Array of status and message
        */
    public function getSpecialisationsOptionList(Request $request)
    {
        $doctorsSpecialisation  = $this->doctorSpecialisationsObj->getSpecialisationsOptionList();
        return $this->resultResponse(
                Config::get('restresponsecode.SUCCESS'), 
                $doctorsSpecialisation, 
                [],
                trans('DoctorProfile::messages.doctors_specialisation_option_list'),
                $this->http_codes['HTTP_OK']
            );
    }

    /**
    * @DateOfCreation        08 August 2018
    * @ShortDescription      This function is responsible to get the Specialisations tag list
    * @param                 Integer $user_id   
    * @return                Array of status and message
    */
    public function getSpecialisationsTagList(Request $request)
    {   
        $requestData = $this->getRequestData($request);
        $doctorsSpecialisation  = $this->doctorSpecialisationsObj->getSpecialisationsTagList($requestData);
        return $this->resultResponse(
                Config::get('restresponsecode.SUCCESS'), 
                $doctorsSpecialisation, 
                [],
                trans('DoctorProfile::messages.doctors_specialisation_tag_list'),
                $this->http_codes['HTTP_OK']
            );
    }

    /**
    * @DateOfCreation        21 May 2018
    * @ShortDescription      This function is responsible to get the Specialisations list if doctors 
    * @param                 Integer $user_id   
    * @return                Array of status and message
    */
    public function getSpecialisationsList(Request $request)
    {
        $requestData = $this->getRequestData($request);
        $requestData['user_id'] = $request->user()->user_id;
        $requestData['user_type'] = $request->user()->user_type;
        $doctorsSpecialisation  = $this->doctorSpecialisationsObj->getSpecialisationsList($requestData);
        return $this->resultResponse(
                Config::get('restresponsecode.SUCCESS'), 
                $doctorsSpecialisation, 
                [],
                trans('DoctorProfile::messages.doctors_specialisation_list'),
                $this->http_codes['HTTP_OK']
            );
    }

    /**
    * @DateOfCreation        30 May 2018
    * @ShortDescription      Get a validator for an incoming Specialisations request
    * @param                 \Illuminate\Http\Request  $request
    * @return                \Illuminate\Contracts\Validation\Validator
    */
    protected function specialisationValidations($requestData){
        $errors         = [];
        $error          = false;
        $validationData = [];

        
            $validationData = [
                'spl_id' => 'required',
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
    * @ShortDescription      This function is responsible for insert Specialisations Data 
    * @param                 Array $request   
    * @return                Array of status and message
    */
    public function store(Request $request)
    {   
        $requestData                  = $this->getRequestData($request);
        $requestData['user_id']       = $request->user()->user_id;
        $requestData['user_type']     = $request->user()->user_type;
        $validate                     = $this->specialisationValidations($requestData);
        
        if($validate["error"]){
            return $this->resultResponse(
                Config::get('restresponsecode.ERROR'), 
                [], 
                $validate['errors'],
                trans('DoctorProfile::messages.validation_error'), 
                $this->http_codes['HTTP_OK']
            ); 
        }
        $requestData['spl_id']        = $this->securityLibObj->decrypt($requestData['spl_id']);
        $isSpecialisationsOptionExistsToUser = $this->doctorSpecialisationsObj->checkSpecialisationsOptionExistsToUser($requestData['spl_id'], $requestData['user_id']);
        if($isSpecialisationsOptionExistsToUser){
            return $this->resultResponse(
                Config::get('restresponsecode.ERROR'), 
                [], 
                ['spl_id'=> [trans('DoctorProfile::messages.doctors_specialisation_alredy_exist')]],
                trans('DoctorProfile::messages.doctors_specialisation_alredy_exist'), 
                $this->http_codes['HTTP_OK']
            ); 
        }
        $specialisationinsertData     = $this->doctorSpecialisationsObj->doInsertSpecialisations($requestData);
        if($specialisationinsertData){
            return $this->resultResponse(
                    Config::get('restresponsecode.SUCCESS'), 
                    $specialisationinsertData, 
                    [],
                    trans('DoctorProfile::messages.doctors_specialisation_data_inserted'),
                    $this->http_codes['HTTP_OK']
                );
        }else{
            return $this->resultResponse(
                Config::get('restresponsecode.ERROR'), 
                [], 
                [],
                trans('DoctorProfile::messages.doctors_specialisation_data_not_inserted'),
                $this->http_codes['HTTP_OK']
            );
        }
    }
    /**
    * @DateOfCreation        30 May 2018
    * @ShortDescription      This function is responsible for Update Specialisations Data 
    * @param                 Array $request   
    * @return                Array of status and message
    */
    public function update(Request $request)
    {   
        $requestData                  = $this->getRequestData($request);
        $requestData['user_id']       = $request->user()->user_id;
        $requestData['user_type']     = $request->user()->user_type;
        $validate                     = $this->specialisationValidations($requestData);
        
        if($validate["error"]){
            return $this->resultResponse(
                Config::get('restresponsecode.ERROR'), 
                [], 
                $validate['errors'],
                trans('DoctorProfile::messages.validation_error'), 
                $this->http_codes['HTTP_OK']
            ); 
        }

        $docSplId = $this->securityLibObj->decrypt($requestData['doc_spl_id']);
        $SplId = $this->securityLibObj->decrypt($requestData['spl_id']);
        $isSpecialisationsOptionExistsToUser = $this->doctorSpecialisationsObj->checkSpecialisationsOptionExistsToUser($SplId, $request->user()->user_id,$docSplId);

        if($isSpecialisationsOptionExistsToUser){
            return $this->resultResponse(
                Config::get('restresponsecode.ERROR'), 
                [], 
                ['spl_id'=> [trans('DoctorProfile::messages.doctors_specialisation_alredy_exist')]],
                trans('DoctorProfile::messages.doctors_specialisation_alredy_exist'), 
                $this->http_codes['HTTP_OK']
            ); 
        }

        $requestData['updated_at']    = $this->dateTimeObj->getPostgresTimestampAfterXmin(0);
        $requestData['updated_by']    = $request->user()->user_id;
        $requestData['resource_type'] = Config::get('constants.RESOURCE_TYPE_WEB');
        $requestData['spl_id']        = $this->securityLibObj->decrypt($requestData['spl_id']);
        $specialisationsUpdateData         = $this->doctorSpecialisationsObj->doUpdateSpecialisations($requestData);
        if($specialisationsUpdateData){
            return $this->resultResponse(
                Config::get('restresponsecode.SUCCESS'), 
                $specialisationsUpdateData, 
                [],
                trans('DoctorProfile::messages.doctors_specialisation_data_updated'),
                $this->http_codes['HTTP_OK']
            );
        }else{
            return $this->resultResponse(
                Config::get('restresponsecode.ERROR'), 
                [], 
                [],
                trans('DoctorProfile::messages.doctors_specialisation_data_not_updated'),
                $this->http_codes['HTTP_OK']
            );
        }
    }

    /**
    * @DateOfCreation        24 May 2018
    * @ShortDescription      This function is responsible for delete Specialisations Data 
    * @param                 Array $doc_exp_id   
    * @return                Array of status and message
    */
    public function destroy(Request $request)
    {   
        $requestData = $this->getRequestData($request);
        $primaryKey = $this->doctorSpecialisationsObj->getTablePrimaryIdColumn();
        $primaryId = $this->securityLibObj->decrypt($requestData[$primaryKey]);
        $isPrimaryIdExist = $this->doctorSpecialisationsObj->isPrimaryIdExist($primaryId);
        
        if(!$isPrimaryIdExist){
            return $this->resultResponse(
                Config::get('restresponsecode.ERROR'), 
                [], 
                [$primaryKey=> [trans('DoctorProfile::messages.doctors_specialisation_not_exist')]],
                trans('DoctorProfile::messages.doctors_specialisation_not_exist'), 
                $this->http_codes['HTTP_OK']
            ); 
        }

        $specialisationDeleteData   = $this->doctorSpecialisationsObj->doDeleteSpecialisations($primaryId);
        if($specialisationDeleteData){
            return $this->resultResponse(
                    Config::get('restresponsecode.SUCCESS'), 
                    [], 
                    [],
                    trans('DoctorProfile::messages.doctors_specialisation_data_deleted'),
                    $this->http_codes['HTTP_OK']
                );
        }
        return $this->resultResponse(
                Config::get('restresponsecode.ERROR'), 
                [], 
                [],
                trans('DoctorProfile::messages.doctors_specialisation_data_not_deleted'),
                $this->http_codes['HTTP_OK']
            );

    }
}
