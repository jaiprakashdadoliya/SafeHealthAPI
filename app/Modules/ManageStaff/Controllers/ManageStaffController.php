<?php

namespace App\Modules\ManageStaff\Controllers;

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
use App\Modules\ManageStaff\Models\ManageStaff;
use App\Modules\Auth\Models\Auth as AuthModel;
use Illuminate\Support\Facades\Hash;


/**
 * ManageStaffController
 *
 * @package                Safehealth
 * @subpackage             ManageStaffController
 * @category               Controller
 * @DateOfCreation         08 june 2018
 * @ShortDescription       This controller to handle all the operation related to 
                           doctors staff
 **/
class ManageStaffController extends Controller
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
    public function __construct() {
        $this->http_codes = $this->http_status_codes();
        
        // Init security library object
        $this->securityLibObj = new SecurityLib(); 

        // Init Staff model object
        $this->staffModelObj = new ManageStaff(); 

        // Init exception library object
        $this->exceptionLibObj = new ExceptionLib();

        // Init Auth Model object
        $this->authModelObj = new AuthModel();
    }

    /**
     * Display a listing of the Staff for a Doctor.
     *
     * @param $docId - Doctor ID
     *
     * @return \Illuminate\Http\Response
     */
    public function getStaffList(Request $request)
    {
        $requestData       = $this->getRequestData($request);
        $requestData['doc_user_id']     = $request->user()->user_id;
        $manageStaff  = $this->staffModelObj->getStaffList($requestData);
        return $this->resultResponse(
                Config::get('restresponsecode.SUCCESS'), 
                $manageStaff, 
                [],
                trans('ManageStaff::messages.staff_list_data'),
                $this->http_codes['HTTP_OK']
            );
    }

    /**
     * Creating new Staff.
     *
     * @param $request - Request object for request data
     *
     * @return \Illuminate\Http\Response
     */
    public function addStaff(Request $request) {
        $extra = [];
        $userData = [];
        $staffData = [];
        $requestData = $this->getRequestData($request);

        $validate = $this->ManageStaffValidator($requestData, $extra);
        if($validate["error"]){
            return $this->resultResponse(
                    Config::get('restresponsecode.ERROR'), 
                    [], 
                    $validate['errors'],
                    trans('ManageStaff::messages.staff_validation_failed'), 
                    $this->http_codes['HTTP_OK']
                  ); 
        }

        $userData = [
            'user_firstname'     => $requestData['user_firstname'],
            'user_lastname'      => $requestData['user_lastname'],
            'user_gender'        => $requestData['user_gender_id'],
            'user_mobile'        => $requestData['user_mobile'],
            'user_email'         => $requestData['user_email'],
            'user_type'          => $requestData['user_type_id'],
            'user_password'      => $requestData['user_password'],
            'user_adhaar_number' => $requestData['user_adhaar_number'],
            'user_country_code'  => $requestData['user_country_code'],
            'user_status'        => Config::get('constants.USER_STATUS_ACTIVE'),
            'resource_type'      => Config::get('constants.RESOURCE_TYPE_WEB'),
            'ip_address'         => $requestData['ip_address']
        ];
        
        $staffData = [
            'resource_type' => Config::get('constants.RESOURCE_TYPE_WEB'),
            'ip_address'    => $requestData['ip_address'],
            'doc_user_id'       => $request->user()->user_id
        ];
        
        // Create user in database 
        try {    
            DB::beginTransaction();
            $createdUserId = $this->authModelObj->createUser($userData);

            // validate, is query executed successfully 
            if($createdUserId){
                $staffData['user_id'] = $createdUserId;
                // We are not paasing email error to user, we are logging error 
                $staffAdded = $this->staffModelObj->saveStaff($staffData);
        
                // validate, is query executed successfully 
                if($staffAdded) {

                    // $isMailSent = $this->sendVerificationLink($requestData, $createdUserId);
                    // if($isMailSent){
                        DB::commit();
                            // return success response 
                            return $this->resultResponse(
                                        Config::get('restresponsecode.SUCCESS'), 
                                        $staffAdded,
                                        [],
                                        trans('ManageStaff::messages.staff_added'),
                                        '', 
                                        $this->http_codes['HTTP_OK']
                                    );
                    // }else{
                    //     DB::rollback();
                    //     return $this->resultResponse(
                    //                 Config::get('restresponsecode.ERROR'), 
                    //                 [], 
                    //                 trans('ManageStaff::messages.staff_failed'), 
                    //                 [],
                    //                 $this->http_codes['HTTP_OK']
                    //             );
                    // }
                }else{
                    DB::rollback();
                    return $this->resultResponse(
                                Config::get('restresponsecode.ERROR'), 
                                [], 
                                trans('ManageStaff::messages.staff_failed'), 
                                [],
                                $this->http_codes['HTTP_OK']
                            );
                }
            }else{
                DB::rollback();
                return $this->resultResponse(
                        Config::get('restresponsecode.ERROR'), 
                        [], 
                        trans('ManageStaff::messages.staff_failed'), 
                        [],
                        $this->http_codes['HTTP_OK']
                    );
            }
        } catch (\Exception $ex) {
            DB::rollback();
            $eMessage = $this->exceptionLibObj->reFormAndLogException($ex,'ManageStaffController', 'saveStaff');            
            return $this->resultResponse(
                    Config::get('restresponsecode.EXCEPTION'), 
                    [], 
                    [],
                    $eMessage, 
                    $this->http_codes['HTTP_OK']
                  );
        }
    }

    /**
     * Updating an existing Staff.
     *
     * @param $request - Request object for request data
     *
     * @return \Illuminate\Http\Response
     */
    public function saveStaff(Request $request) {
        $extra = [];
        $userData = [];
        $staffData = [];
        $whereData = [];
        $requestData = $this->getRequestData($request);

        $validate = $this->ManageStaffValidator($requestData, $extra);
        if($validate["error"]){
            return $this->resultResponse(
                    Config::get('restresponsecode.ERROR'), 
                    [], 
                    $validate['errors'],
                    trans('ManageStaff::messages.staff_validation_failed'), 
                    $this->http_codes['HTTP_OK']
                  ); 
        }

        $userData = [
            'user_firstname'     => $requestData['user_firstname'],
            'user_lastname'      => $requestData['user_lastname'],
            'user_gender'        => $requestData['user_gender_id'],
            'user_mobile'        => $requestData['user_mobile'],
            'user_email'         => $requestData['user_email'],
            'user_type'          => $requestData['user_type_id'],
            'user_adhaar_number' => $requestData['user_adhaar_number'],
            'user_country_code'  => $requestData['user_country_code'],
            'user_password'      => Hash::make($requestData['user_password']),
            'user_status'        => Config::get('constants.USER_STATUS_ACTIVE'),
            'resource_type'      => Config::get('constants.RESOURCE_TYPE_WEB'),
            'ip_address'         => $requestData['ip_address'],
        ];
        $whereData = [
            'user_id' => $this->securityLibObj->decrypt($requestData['user_id']),
        ];
        
        $staffData = [
            'doc_staff_id'  => $this->securityLibObj->decrypt($requestData['doc_staff_id']),
            'resource_type' => Config::get('constants.RESOURCE_TYPE_WEB'),
            'ip_address'    => $requestData['ip_address'],
            'doc_user_id'       => $request->user()->user_id
        ];
        
        // Create user in database 
        try {
            DB::beginTransaction();
            $updateUserId = $this->staffModelObj->updateStaffUser($userData,$whereData);

            // validate, is query executed successfully 
            if($updateUserId){
                // We are not paasing email error to user, we are logging error 
                $staffAdded = $this->staffModelObj->saveStaff($staffData);
        
                // validate, is query executed successfully 
                if($staffAdded) {

                    // $isMailSent = $this->sendVerificationLink($requestData, $createdUserId);
                    // if($isMailSent){
                        DB::commit();
                            // return success response 
                            return $this->resultResponse(
                                        Config::get('restresponsecode.SUCCESS'), 
                                        $staffAdded,
                                        [],
                                        trans('ManageStaff::messages.staff_added'),
                                        '', 
                                        $this->http_codes['HTTP_OK']
                                    );
                    // }else{
                    //     DB::rollback();
                    //     return $this->resultResponse(
                    //                 Config::get('restresponsecode.ERROR'), 
                    //                 [], 
                    //                 trans('ManageStaff::messages.staff_failed'), 
                    //                 [],
                    //                 $this->http_codes['HTTP_OK']
                    //             );
                    // }
                }else{
                    DB::rollback();
                    return $this->resultResponse(
                                Config::get('restresponsecode.ERROR'), 
                                [], 
                                trans('ManageStaff::messages.staff_failed'), 
                                [],
                                $this->http_codes['HTTP_OK']
                            );
                }
            }else{
                DB::rollback();
                return $this->resultResponse(
                        Config::get('restresponsecode.ERROR'), 
                        [], 
                        trans('ManageStaff::messages.staff_failed'), 
                        [],
                        $this->http_codes['HTTP_OK']
                    );
            }
        } catch (\Exception $ex) {
            DB::rollback();
            $eMessage = $this->exceptionLibObj->reFormAndLogException($ex,'ManageStaffController', 'saveStaff');            
            return $this->resultResponse(
                    Config::get('restresponsecode.EXCEPTION'), 
                    [], 
                    [],
                    $eMessage, 
                    $this->http_codes['HTTP_OK']
                  );
        }
    }

    /**
     * Remove the specified Staff from storage.
     *
     * @param $request - Request object for request data
     *
     * @return \Illuminate\Http\Response
     */
    public function deleteStaff(Request $request)
    {   
        $requestData = $this->getRequestData($request);
        $primaryKey = $this->staffModelObj->getTablePrimaryIdColumn();
        $primaryId = $this->securityLibObj->decrypt($requestData[$primaryKey]);
        $isPrimaryIdExist = $this->staffModelObj->isPrimaryIdExist($primaryId);
        
        if(!$isPrimaryIdExist){
            return $this->resultResponse(
                Config::get('restresponsecode.ERROR'),
                [], 
                [$primaryKey=> [trans('ManageStaff::messages.staff_not_found')]],
                trans('ManageStaff::messages.staff_not_found'), 
                $this->http_codes['HTTP_OK']
            ); 
        }
        $staffDeleted = $this->staffModelObj->deleteStaff($primaryId);
        if($staffDeleted) {
            return $this->resultResponse(
                Config::get('restresponsecode.SUCCESS'), 
                [],
                [],
                trans('ManageStaff::messages.staff_deleted'), 
                $this->http_codes['HTTP_OK']
            );
        }else{
            return $this->resultResponse(
                Config::get('restresponsecode.ERROR'), 
                [], 
                trans('ManageStaff::messages.staff_delete_failed'), 
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
    protected function ManageStaffValidator($data, $extra = []) {
        $user_id = (!empty($data['user_id'])) ? $this->securityLibObj->decrypt($data['user_id']):0;
        $doc_staff_id = (!empty($data['doc_staff_id'])) ? $this->securityLibObj->decrypt($data['doc_staff_id']):0;
        
        $error = false;
        $errors = [];
        $rules = [
            'user_firstname' => 'required|string|max:150|min:3',
            'user_lastname' => 'required|string|max:150|min:3',
            'user_country_code' => 'required',
            'user_gender_id'   => 'required',
            'user_mobile' => 'required|unique:users,user_mobile,'.$user_id.',user_id',
            'user_adhaar_number'=> 'required|numeric|regex:/[0-9]{12}/|unique:users,user_adhaar_number,'.$user_id.',user_id',
            'user_email' => 'required|unique:users,user_email,'.$user_id.',user_id',
        ];
        $rules['user_password'] = 'required|min:6|regex:/^.*(?=.{3,})(?=.*[a-zA-Z])(?=.*[0-9])(?=.*[\d\X])(?=.*[!@$#%]).*$/';
        $rules = array_merge($rules,$extra);
        $validator = Validator::make($data, $rules);
        if($validator->fails()) {
            $error = true;
            $errors = $validator->errors();
        }
        return ["error" => $error,"errors" => $errors];
    }
}
