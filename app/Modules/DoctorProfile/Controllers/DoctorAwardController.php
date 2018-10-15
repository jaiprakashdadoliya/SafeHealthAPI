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
use App\Modules\DoctorProfile\Models\DoctorAward;

/**
 * DoctorAwardsController
 *
 * @package                ILD Registry
 * @subpackage             DoctorAwardsController
 * @category               Controller
 * @DateOfCreation         21 may 2018
 * @ShortDescription       This controller to handle all the operation related to 
                           doctors experience
 **/
class DoctorAwardController extends Controller {
    use SessionTrait, RestApi;

    // @var Array $http_codes
    // This protected member contains Http Status Codes
    protected $http_codes = []; 

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct() {
        $this->http_codes = $this->http_status_codes();
        
        // Init security library object
        $this->securityLibObj = new SecurityLib(); 

        // Init Awards model object
        $this->awardsModelObj = new DoctorAward(); 

        // Init exception library object
        $this->exceptionLibObj = new ExceptionLib();
    }

    /**
     * Display a listing of the Awards for a Doctor.
     *
     * @param $docId - Doctor ID
     *
     * @return \Illuminate\Http\Response
     */
    public function showAwardList(Request $request) {
        $requestData = $this->getRequestData($request);
        $requestData['user_id'] = $request->user()->user_id;
        $doctorAwards = $this->awardsModelObj->getAwardsList($requestData);
        
        // validate, is query executed successfully 
        if($doctorAwards)
        {
            return $this->resultResponse(
                        Config::get('restresponsecode.SUCCESS'), 
                        $doctorAwards,
                        [],
                        '', 
                        $this->http_codes['HTTP_OK']
                    );
        }else{
            return $this->resultResponse(
                        Config::get('restresponsecode.ERROR'), 
                        [], 
                        trans('DoctorProfile::messages.award_failed'), 
                        [],
                        $this->http_codes['HTTP_OK']
                    );
        }
    }

    /**
     * Creating new or updating an existing Award.
     *
     * @param $request - Request object for request data
     *
     * @return \Illuminate\Http\Response
     */
    public function saveAward(Request $request) {
        $extra = [];
        $requestData = $this->getRequestData($request);
        $requestData['user_id'] = $request->user()->user_id;
        $requestData['user_type'] = $request->user()->user_type;
        $validate = $this->DoctorAwardValidator($requestData, $extra);
        if($validate["error"]){
            return $this->resultResponse(
                    Config::get('restresponsecode.ERROR'), 
                    [], 
                    $validate['errors'],
                    trans('DoctorProfile::messages.award_validation_failed'), 
                    $this->http_codes['HTTP_OK']
                  ); 
        }
        $awardId = (array_key_exists('doc_award_id', $requestData)) ? $requestData['doc_award_id'] : false;
        if($awardId && !empty($awardId)){
            $requestData['doc_award_id'] = $this->securityLibObj->decrypt($requestData['doc_award_id']);
            $new = false;
        }else{
            $new = true;
        }
        
        $requestData['resource_type'] = Config::get('constants.RESOURCE_TYPE_WEB');
        $awardSaved = $this->awardsModelObj->saveAward($requestData);

        
        // validate, is query executed successfully 
        if($awardSaved) {
            return $this->resultResponse(
                        Config::get('restresponsecode.SUCCESS'), 
                        $awardSaved,
                        [],
                        ($new) ? trans('DoctorProfile::messages.award_added') : trans('DoctorProfile::messages.award_updated'),
                        '', 
                        $this->http_codes['HTTP_OK']
                    );
        }else{
            return $this->resultResponse(
                        Config::get('restresponsecode.ERROR'), 
                        [], 
                        trans('DoctorProfile::messages.award_failed'), 
                        [],
                        $this->http_codes['HTTP_OK']
                    );
        }
    }

    /**
     * Remove the specified Award from storage.
     *
     * @param $request - Request object for request data
     *
     * @return \Illuminate\Http\Response
     */
    public function deleteAward(Request $request) {
        $requestData = $this->getRequestData($request);
        $primaryKey = $this->awardsModelObj->getTablePrimaryIdColumn();
        $primaryId = $this->securityLibObj->decrypt($requestData[$primaryKey]);
        $isPrimaryIdExist = $this->awardsModelObj->isPrimaryIdExist($primaryId);
            
        if(!$isPrimaryIdExist){
            return $this->resultResponse(
                Config::get('restresponsecode.ERROR'),
                [], 
                [$primaryKey=> [trans('DoctorProfile::messages.award_not_found')]],
                trans('DoctorProfile::messages.award_not_found'), 
                $this->http_codes['HTTP_OK']
            ); 
        }

        $awardDeleted = $this->awardsModelObj->deleteAward($primaryId);
        if($awardDeleted) {
            return $this->resultResponse(
                        Config::get('restresponsecode.SUCCESS'), 
                        [],
                        [],
                        trans('DoctorProfile::messages.award_deleted'), 
                        $this->http_codes['HTTP_OK']
                    );
        }else{
            return $this->resultResponse(
                        Config::get('restresponsecode.ERROR'), 
                        [], 
                        trans('DoctorProfile::messages.award_delete_failed'), 
                        [],
                        $this->http_codes['HTTP_OK']
                    );
        }
    }

    /**
    * @DateOfCreation        03 Apr 2018
    *
    * @ShortDescription      This function is responsible for validating blog data
    *
    * @param                 Array $data This contains full request data
    * @param                 Array $extra extra validation rules 
    *
    * @return                VIEW
    */ 
    protected function DoctorAwardValidator(array $data, $extra = []) {
        $error = false;
        $errors = [];
        $rules = [
            'doc_award_name' => 'required|string',
            'doc_award_year' => 'required',
        ];
        $rules = array_merge($rules,$extra);
        $validator = Validator::make($data, $rules);
        if($validator->fails()) {
            $error = true;
            $errors = $validator->errors();
        }
        return ["error" => $error,"errors" => $errors];
    }
}
