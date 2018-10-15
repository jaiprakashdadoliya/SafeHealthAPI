<?php

namespace App\Modules\AppointmentCategory\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Libraries\SecurityLib;
use Illuminate\Support\Facades\Validator;
use App\Traits\RestApi;
use Config;
use App\Modules\AppointmentCategory\Models\AppointmentCategory as AppointmentCategory;

class AppointmentCategoryController extends Controller
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
        // Init AppointmentCategory model object
        $this->appointmentCategoryModelObj = new AppointmentCategory();
        // Init security library object
        $this->securityLibObj = new SecurityLib();  
    }

    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request service
     * @return \Illuminate\Http\Response
     */
    public function appointmentCategoryList(Request $request)
    {
        $requestData = $this->getRequestData($request);
        $requestData['user_id'] = $request->user()->user_id;
        $appointmentCategory =  $this->appointmentCategoryModelObj->getList($requestData);
        // validate, is query executed successfully 
        if($appointmentCategory)
        {
            return  $this->resultResponse(
                Config::get('restresponsecode.SUCCESS'), 
                $appointmentCategory,  
                [],
                '', 
                $this->http_codes['HTTP_OK']
            );
        }else{
            return  $this->resultResponse(
                Config::get('restresponsecode.ERROR'), 
                [], 
                [],
                trans('AppointmentCategory::messages.app_cate_failed'), 
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
        $validate = $this->AppointmentCategoryValidator($requestData);
        if($validate["error"])
        {
            return $this->resultResponse(
                Config::get('restresponsecode.ERROR'), 
                [], 
                $validate['errors'],
                trans('AppointmentCategory::messages.app_cate_failed'), 
                $this->http_codes['HTTP_OK']
            ); 
        }
        $requestData['user_id'] = $request->user()->user_id;
       // Create service in database 
        $isAppointmentCategoryCreated = $this->appointmentCategoryModelObj->createAppointmentCategory($requestData);
        // validate, is query executed successfully 
        if(!empty($isAppointmentCategoryCreated))
        {
            return  $this->resultResponse(
                Config::get('restresponsecode.SUCCESS'), 
                $isAppointmentCategoryCreated, 
                [],
                trans('AppointmentCategory::messages.app_cate_save'), 
                $this->http_codes['HTTP_OK']
            );

        }else{
            return  $this->resultResponse(
                Config::get('restresponsecode.ERROR'), 
                [], 
                [],
                trans('AppointmentCategory::messages.app_cate_failed'), 
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
    private function AppointmentCategoryValidator(array $data)
    {
        $error      = false;
        $errors     = [];
        $rules      = ['appointment_cat_name'     => 'required'];
        $messages   = ['appointment_cat_name.required'     => "The appointment category name is required."];
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
        $validate = $this->AppointmentCategoryValidator($requestData);
        if($validate["error"])
        {
            return $this->resultResponse(
                Config::get('restresponsecode.ERROR'), 
                [], 
                $validate['errors'],
                trans('AppointmentCategory::messages.app_cate_failed'), 
                $this->http_codes['HTTP_OK']
            ); 
        }
        $requestData['user_id']     = $request->user()->user_id;
        // Update service detail in database 
        $isAppointmentCategoryUpdate = $this->appointmentCategoryModelObj->updateAppointmentCategory($requestData);
        // validate, is query executed successfully 
        if(!empty($isAppointmentCategoryUpdate))
        {
            return  $this->resultResponse(
                Config::get('restresponsecode.SUCCESS'), 
                $isAppointmentCategoryUpdate, 
                [],
                trans('AppointmentCategory::messages.app_cate_update'), 
                $this->http_codes['HTTP_OK']
            );
        }else{
            return  $this->resultResponse(
                Config::get('restresponsecode.ERROR'), 
                [], 
                [],
                trans('AppointmentCategory::messages.app_cate_failed'), 
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
        $primaryKey = $this->appointmentCategoryModelObj->getTablePrimaryIdColumn();
        $primaryId = $this->securityLibObj->decrypt($requestData[$primaryKey]);
        $isPrimaryIdExist = $this->appointmentCategoryModelObj->isPrimaryIdExist($primaryId);
        
        if(!$isPrimaryIdExist){
            return $this->resultResponse(
                Config::get('restresponsecode.ERROR'),
                [], 
                [$primaryKey=> [trans('AppointmentCategory::messages.app_cate_not_found')]],
                trans('AppointmentCategory::messages.app_cate_not_found'), 
                $this->http_codes['HTTP_OK']
            ); 
        }

        $isAppointmentCategoryDeleted=$this->appointmentCategoryModelObj->deleteAppointmentCategory($primaryId);
        // validate, is query executed successfully 
        if(!empty($isAppointmentCategoryDeleted))
        {
            return  $this->resultResponse(
                Config::get('restresponsecode.SUCCESS'), 
                [], 
                [],
                trans('AppointmentCategory::messages.app_cate_delete'), 
                $this->http_codes['HTTP_OK']
            );
        }else{
            return  $this->resultResponse(
                Config::get('restresponsecode.ERROR'), 
                [], 
                trans('AppointmentCategory::messages.app_cate_failed'), 
                [],
                $this->http_codes['HTTP_OK']
            );
        }
    }

    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request service
     * @return \Illuminate\Http\Response
     */
    public function getAppointmentReasons(Request $request)
    {
        $requestData = $this->getRequestData($request);
        $requestData['user_id'] = !empty($requestData['user_id']) ? $this->securityLibObj->decrypt($requestData['user_id']) : $request->user()->user_id; 
        $appointmentCategory =  $this->appointmentCategoryModelObj->getAppointmentReasons($requestData);
        // validate, is query executed successfully 
        if($appointmentCategory)
        {
            return  $this->resultResponse(
                Config::get('restresponsecode.SUCCESS'), 
                $appointmentCategory,  
                [],
                '', 
                $this->http_codes['HTTP_OK']
            );
        }else{
            return  $this->resultResponse(
                Config::get('restresponsecode.ERROR'), 
                [], 
                trans('AppointmentCategory::messages.app_cate_failed'), 
                [],
                $this->http_codes['HTTP_OK']
            );
        }
    }
}
