<?php

namespace App\Modules\DoctorProfile\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Traits\RestApi;
use App\Libraries\SecurityLib;
use Config;
use App\Modules\DoctorProfile\Models\DoctorMembership as DoctorMembership;

/**
 * DoctorMembership Class
 *
 * @package                Doctor Profile
 * @subpackage             Doctor Membership
 * @category               Controller
 * @DateOfCreation         21 May 2018
 * @ShortDescription       This is controller which need to perform the options related to 
                           doctor membership table
 */
class DoctorMembershipController extends Controller
{
    /**
     *  use restApi is trait for using function
     */
    use RestApi;
    // @var Array $http_codes
    // This protected member contains Http Status Codes
    protected $http_codes = [];
    
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->http_codes = $this->http_status_codes();
        // Init Membership model object
        $this->membershipModelObj = new DoctorMembership();

        // Init security library object
        $this->securityLibObj = new SecurityLib(); 
    }

    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request memberlist of doctor
     * @return \Illuminate\Http\Response
     */
    public function list(Request $request, $user_id = NULL)
    {
        if($request->isMethod('get')){
            $requestData['user_id']     = $this->securityLibObj->decrypt($user_id);
            $requestData['user_type']   = Config::get('constants.USER_TYPE_DOCTOR');
            $method = Config::get('constants.REQUEST_TYPE_GET');
        }else{
            $requestData = $this->getRequestData($request);
            $requestData['user_id'] = $request->user()->user_id;
            $requestData['user_type'] = $request->user()->user_type;
            $method = Config::get('constants.REQUEST_TYPE_POST');
        }
        
        $doctorMembership =  $this->membershipModelObj->membershipList($requestData, $method);
        // validate, is query executed successfully 
        if(!empty($doctorMembership['result']))
        {
            return  $this->resultResponse(
                Config::get('restresponsecode.SUCCESS'), 
                $doctorMembership,  
                [],
                '', 
                $this->http_codes['HTTP_OK']
            );
        }else{
            return  $this->resultResponse(
                Config::get('restresponsecode.ERROR'), 
                [], 
                [],
                trans('DoctorProfile::messages.membership_not_found'), 
                $this->http_codes['HTTP_OK']
            );
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $requestData = $this->getRequestData($request);
         // Validate request
        $validate = $this->DoctorMembershipValidator($requestData);
        if($validate["error"])
        {
            return $this->resultResponse(
                Config::get('restresponsecode.ERROR'), 
                [], 
                $validate['errors'],
                trans('DoctorProfile::messages.membership_failed'), 
                $this->http_codes['HTTP_OK']
            ); 
        }

        if(!isset($requestData['user_id']) ){
            $requestData['user_id']     = $request->user()->user_id;
            $requestData['user_type']   = $request->user()->user_type;
        }else{
            $requestData['user_id']     = $this->securityLibObj->decrypt($requestData['user_id']);
            unset($requestData['_token']);            
        }
        
        // Create membership in database 
        $isDoctorMembershipCreated = $this->membershipModelObj->createMembership($requestData);
        
        // validate, is query executed successfully 
        if(!empty($isDoctorMembershipCreated))
        {
            return  $this->resultResponse(
                Config::get('restresponsecode.SUCCESS'), 
                $isDoctorMembershipCreated, 
                [],
                trans('DoctorProfile::messages.membership_save'), 
                $this->http_codes['HTTP_OK']
            );

        }else{
            return  $this->resultResponse(
                Config::get('restresponsecode.ERROR'), 
                [], 
                [],
                trans('DoctorProfile::messages.membership_failed'), 
                $this->http_codes['HTTP_OK']
            );
        }
    }

    /**
    * This function is responsible for validating membership data
    * 
    * @param  Array $data This contains full member input data 
    * @return Array $error status of error
    */ 
    private function DoctorMembershipValidator(array $requestData)
    {
        $error = false;
        $errors = [];
        $validationData = [];
        $validationData = [
            'doc_mem_name' => 'required'
        ];       
        $validator  = Validator::make(
            $requestData,
            $validationData
        );
        if($validator->fails()){
            $error = true;
            $errors = $validator->errors();
        }
        return ["error" => $error,"errors" => $errors];
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $requestData = $this->getRequestData($request);
        // Validate request
        $validate = $this->DoctorMembershipValidator($requestData);
        if($validate["error"])
        {
            return $this->resultResponse(
                Config::get('restresponsecode.ERROR'), 
                [], 
                $validate['errors'],
                trans('DoctorProfile::messages.membership_failed'), 
                $this->http_codes['HTTP_OK']
            ); 
        }
        $requestData['user_id']     = $request->user()->user_id;
        // Update membership detail in database 
        $isDoctorMembershipUpdate = $this->membershipModelObj->updateMembership($requestData);
        // validate, is query executed successfully 
        if(!empty($isDoctorMembershipUpdate))
        {
            return  $this->resultResponse(
                Config::get('restresponsecode.SUCCESS'), 
                [], 
                [],
                trans('DoctorProfile::messages.membership_update'), 
                $this->http_codes['HTTP_OK']
            );
        }else{
            return  $this->resultResponse(
                Config::get('restresponsecode.ERROR'), 
                [], 
                [],
                trans('DoctorProfile::messages.membership_fail'), 
                $this->http_codes['HTTP_OK']
            );
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id doctor id for particular doctor
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $requestData = $this->getRequestData($request);
        $primaryKey = $this->membershipModelObj->getTablePrimaryIdColumn();
        $primaryId = $this->securityLibObj->decrypt($requestData[$primaryKey]);
        $isPrimaryIdExist = $this->membershipModelObj->isPrimaryIdExist($primaryId);

        if(!$isPrimaryIdExist){
            return $this->resultResponse(
                Config::get('restresponsecode.ERROR'), 
                [], 
                [$primaryKey=> [trans('DoctorProfile::messages.membership_not_found')]],
                trans('DoctorProfile::messages.membership_not_found'), 
                $this->http_codes['HTTP_OK']
            ); 
        }

        $isDoctorMembershipDeleted=$this->membershipModelObj->deleteMembership($primaryId);
        // validate, is query executed successfully 
        if(!empty($isDoctorMembershipDeleted))
        {
            return  $this->resultResponse(
                Config::get('restresponsecode.SUCCESS'), 
                [], 
                [],
                trans('DoctorProfile::messages.membership_delete'), 
                $this->http_codes['HTTP_OK']
            );
        }else{
            return  $this->resultResponse(
                Config::get('restresponsecode.ERROR'), 
                [], 
                trans('DoctorProfile::messages.membership_fail'), 
                [],
                $this->http_codes['HTTP_OK']
            );
        }
    }
}