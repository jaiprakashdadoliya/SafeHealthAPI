<?php

namespace App\Modules\PatientGroups\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Libraries\SecurityLib;
use Illuminate\Support\Facades\Validator;
use App\Traits\RestApi;
use Config;
use App\Modules\PatientGroups\Models\PatientGroups as PatientGroups;

class PatientGroupsController extends Controller
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
        // Init PatientGroups model object
        $this->patientsGroupsModelObj = new PatientGroups();
        // Init security library object
        $this->securityLibObj = new SecurityLib();  
    }

    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request service
     * @return \Illuminate\Http\Response
     */
    public function patientGroupsList(Request $request)
    {
        $requestData = $this->getRequestData($request);
        $requestData['user_id'] = $request->user()->user_id;
        $patientsGroups =  $this->patientsGroupsModelObj->getList($requestData);
        // validate, is query executed successfully 
        if($patientsGroups)
        {
            return  $this->resultResponse(
                Config::get('restresponsecode.SUCCESS'), 
                $patientsGroups,  
                [],
                trans('PatientGroups::messages.pat_group_fetch'),
                $this->http_codes['HTTP_OK']
            );
        }else{
            return  $this->resultResponse(
                Config::get('restresponsecode.ERROR'), 
                [], 
                trans('PatientGroups::messages.pat_group_failed'), 
                [],
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
        $validate = $this->patientGroupsValidator($requestData);
        if($validate["error"])
        {
            return $this->resultResponse(
                Config::get('restresponsecode.ERROR'), 
                [], 
                $validate['errors'],
                trans('PatientGroups::messages.pat_group_failed'), 
                $this->http_codes['HTTP_OK']
            ); 
        }
        $requestData['user_id'] = $request->user()->user_id;
       // Create service in database 
        $isPatientGroupCreated = $this->patientsGroupsModelObj->createPatientGroup($requestData);
        // validate, is query executed successfully 
        if(!empty($isPatientGroupCreated))
        {
            return  $this->resultResponse(
                Config::get('restresponsecode.SUCCESS'), 
                $isPatientGroupCreated, 
                [],
                trans('PatientGroups::messages.pat_group_save'), 
                $this->http_codes['HTTP_OK']
            );

        }else{
            return  $this->resultResponse(
                Config::get('restresponsecode.ERROR'), 
                [], 
                [],
                trans('PatientGroups::messages.pat_group_failed'), 
                $this->http_codes['HTTP_OK']
            );
        }
    }

    /**
    * This function is responsible for validating service data
    * 
    * @param  Array $data This contains full member input data 
    * @return Array $error status of error
    */ 
    private function patientGroupsValidator(array $data)
    {
        $error      = false;
        $errors     = [];
        $rules      = ['pat_group_name'     => 'required'];
        $messages   = ['pat_group_name.required'     => "The appointment category name is required."];
        $validator = Validator::make($data, $rules, $messages); 
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
        $validate = $this->patientGroupsValidator($requestData);
        if($validate["error"])
        {
            return $this->resultResponse(
                Config::get('restresponsecode.ERROR'), 
                [], 
                $validate['errors'],
                trans('PatientGroups::messages.pat_group_failed'), 
                $this->http_codes['HTTP_OK']
            ); 
        }
        $requestData['user_id']     = $request->user()->user_id;
        // Update service detail in database 
        $isPatientGroupUpdated = $this->patientsGroupsModelObj->updatePatientGroup($requestData);
        // validate, is query executed successfully 
        if(!empty($isPatientGroupUpdated))
        {
            return  $this->resultResponse(
                Config::get('restresponsecode.SUCCESS'), 
                $isPatientGroupUpdated, 
                [],
                trans('PatientGroups::messages.pat_group_update'), 
                $this->http_codes['HTTP_OK']
            );
        }else{
            return  $this->resultResponse(
                Config::get('restresponsecode.ERROR'), 
                [], 
                [],
                trans('PatientGroups::messages.pat_group_failed'), 
                $this->http_codes['HTTP_OK']
            );
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id service id for particular service
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {   
        $requestData = $this->getRequestData($request);
        $primaryKey = $this->patientsGroupsModelObj->getTablePrimaryIdColumn();
        $primaryId = $this->securityLibObj->decrypt($requestData[$primaryKey]);
        $isPrimaryIdExist = $this->patientsGroupsModelObj->isPrimaryIdExist($primaryId);
        
        if(!$isPrimaryIdExist){
            return $this->resultResponse(
                Config::get('restresponsecode.ERROR'),
                [], 
                [$primaryKey=> [trans('PatientGroups::messages.pat_group_not_found')]],
                trans('PatientGroups::messages.pat_group_not_found'), 
                $this->http_codes['HTTP_OK']
            ); 
        }
        $isPatientGroupDeleted=$this->patientsGroupsModelObj->deletePatientGroup($primaryId);
        // validate, is query executed successfully 
        if(!empty($isPatientGroupDeleted))
        {
            return  $this->resultResponse(
                Config::get('restresponsecode.SUCCESS'), 
                [], 
                [],
                trans('PatientGroups::messages.pat_group_delete'), 
                $this->http_codes['HTTP_OK']
            );
        }else{
            return  $this->resultResponse(
                Config::get('restresponsecode.ERROR'), 
                [], 
                trans('PatientGroups::messages.pat_group_failed'), 
                [],
                $this->http_codes['HTTP_OK']
            );
        }
    }
}
