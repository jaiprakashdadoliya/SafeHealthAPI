<?php

namespace App\Modules\Clinics\Controllers;

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
use App\Modules\Clinics\Models\Clinics;

/**
 * ClinicsController
 *
 * @package                Safehealth
 * @subpackage             ClinicsController
 * @category               Controller
 * @DateOfCreation         27 june 2018
 * @ShortDescription       This controller to handle all the operation related to 
                           clinic
 **/

class ClinicsController extends Controller
{
    use SessionTrait, RestApi;

    // @var Array $http_codes
    // This protected member contains Http Status Codes
    protected $http_codes = [];
    protected $primaryKey = 'clinic_id';
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct() {
        $this->http_codes = $this->http_status_codes();
        
        // Init security library object
        $this->securityLibObj = new SecurityLib();

        // Init exception library object
        $this->exceptionLibObj = new ExceptionLib();
        // Init exception library object
        $this->clinicModelObj = new Clinics();
    }

    /**
    */
    /**
     * @DateOfCreation      11 July 2018
     * @ShortDescription    This function is responsible to display a listing of the Doctors Clinics 
     *                      for Timing Section.
     * @param $request - Request object for request data
     * @return \Illuminate\Http\Response
     */

    protected function getClinicListForTiming(Request $request)
    {
        $user_id = (in_array($request->user()->user_type, Config::get('constants.USER_TYPE_STAFF'))) ? $request->user()->created_by : $request->user()->user_id;
        $clinicList = array();
        //Get agency id
        $clinicList = $this->clinicModelObj->getClinicListForTiming($user_id);
        if($clinicList) {
            return $this->resultResponse(
                        Config::get('restresponsecode.SUCCESS'),
                        $clinicList,
                        [],
                        'success',
                        $this->http_codes['HTTP_OK']
                      );
        }else {
            return $this->resultResponse(
                        Config::get('restresponsecode.ERROR'),
                        [],
                        [],
                        'failed',
                        $this->http_codes['HTTP_NO_CONTENT']
                      );
        }
    }

    /**
     * @DateOfCreation       11 July 2018
     * @ShortDescription     This function is responsible to Display a listing of the Doctors Clinics 
                            on Listings page.
     * @param                $request - Request object for request data
     * @return               \Illuminate\Http\Response
     */

    protected function getClinicList(Request $request)
    {
        $requestData = $this->getRequestData($request);
        $requestData['user_id'] = (in_array($request->user()->user_type, Config::get('constants.USER_TYPE_STAFF'))) ? $request->user()->created_by : $request->user()->user_id;
        $clinics = $this->clinicModelObj->getClinicList($requestData);
        return $this->resultResponse(
                Config::get('restresponsecode.SUCCESS'),
                $clinics,
                [],
                trans('Clinics::messages.clinic_list'),
                $this->http_codes['HTTP_OK']
            );
    }

    /**
     * @DateOfCreation      11 July 2018
     * @ShortDescription    This function is responsible for creating new or updating an 
                            existing Clinic details.
     * @param               $request - Request object for request data
     * @return              \Illuminate\Http\Response
     */

    public function saveClinic(Request $request) {
        $extra = [];
        $requestData = $this->getRequestData($request);
        $requestData['user_id'] = (in_array($request->user()->user_type, Config::get('constants.USER_TYPE_STAFF'))) ? $request->user()->created_by : $request->user()->user_id;
        $validate = $this->ClinicValidator($requestData, $extra);
        if($validate["error"]){
            return $this->resultResponse(
                    Config::get('restresponsecode.ERROR'),
                    [],
                    $validate['errors'],
                    trans('Clinics::messages.clinic_validation_failed'),
                    $this->http_codes['HTTP_OK']
                  );
        }
        $clinicId = (array_key_exists('clinic_id', $requestData)) ? $requestData['clinic_id'] : false;

        if($clinicId && !empty($clinicId)){
            $requestData['clinic_id'] = $this->securityLibObj->decrypt($requestData['clinic_id']);
            $new = false;
        }else{
            $new = true;
        }

        $requestData['resource_type'] = Config::get('constants.RESOURCE_TYPE_WEB');
        $clinicSaved = $this->clinicModelObj->saveClinic($requestData);

        // validate, is query executed successfully
        if($clinicSaved) {
            return $this->resultResponse(
                        Config::get('restresponsecode.SUCCESS'),
                        $clinicSaved,
                        [],
                        ($new) ? trans('Clinics::messages.clinic_added') : trans('Clinics::messages.clinic_updated'),
                        '',
                        $this->http_codes['HTTP_OK']
                    );
        }else{
            return $this->resultResponse(
                        Config::get('restresponsecode.ERROR'),
                        [],
                        trans('Clinics::messages.clinic_failed'),
                        [],
                        $this->http_codes['HTTP_OK']
                    );
        }
    }

    /**
     * @DateOfCreation      11 July 2018
     * @ShortDescription    This function is responsible to remove the specified Clinic from storage.
     * @param               $request - Request object for request data
     * @return              \Illuminate\Http\Response
     */
    public function deleteClinic(Request $request)
    {   
        $requestData = $this->getRequestData($request);
        $primaryKey = $this->clinicModelObj->getTablePrimaryIdColumn();
        $primaryId = $this->securityLibObj->decrypt($requestData[$primaryKey]);
        $isPrimaryIdExist = $this->clinicModelObj->isPrimaryIdExist($primaryId);
        
        if(!$isPrimaryIdExist){
            return $this->resultResponse(
                Config::get('restresponsecode.ERROR'),
                [], 
                [$primaryKey=> [trans('DoctorProfile::messages.clinic_not_found')]],
                trans('DoctorProfile::messages.clinic_not_found'), 
                $this->http_codes['HTTP_OK']
            ); 
        }
        $clinicDeleted = $this->clinicModelObj->deleteClinic($primaryId);
        if($clinicDeleted) {
            return $this->resultResponse(
                        Config::get('restresponsecode.SUCCESS'),
                        [],
                        [],
                        trans('Clinics::messages.clinic_deleted'),
                        $this->http_codes['HTTP_OK']
                    );
        }else{
            return $this->resultResponse(
                        Config::get('restresponsecode.ERROR'),
                        [],
                        trans('Clinics::messages.clinic_delete_failed'),
                        [],
                        $this->http_codes['HTTP_OK']
                    );
        }
    }

    /**
    * @DateOfCreation        27 July 2018
    * @ShortDescription      This function is responsible for validating clinic data
    * @param                 Array $requestData This contains full request data
    * @param                 Array $extra extra validation rules 
    * @return                VIEW
    */ 
    protected function ClinicValidator(array $requestData, $extra = []) {
        $clin_id = ($requestData['clinic_id']) ? $this->securityLibObj->decrypt($requestData['clinic_id']):0;
        $error = false;
        $errors = [];
        $validationData = [
            'clinic_name'          => 'required',
            'clinic_phone'         => 'required|unique:clinics,clinic_phone,'.$clin_id.',clinic_id',
            'clinic_address_line1' => 'required',
            'clinic_pincode'       => 'required',
        ];

        $validator = Validator::make($requestData, $validationData);
        if($validator->fails()) {
            $error = true;
            $errors = $validator->errors();
        }
        return ["error" => $error,"errors" => $errors];
    }
}
