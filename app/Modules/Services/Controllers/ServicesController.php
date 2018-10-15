<?php

namespace App\Modules\Services\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Traits\RestApi;
use Config;
use App\Modules\Services\Models\Services as Services;
use App\Libraries\SecurityLib;

class ServicesController extends Controller
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
        // Init Services model object
        $this->servicesModelObj = new Services();

        // Init security library object
        $this->securityLibObj = new SecurityLib(); 
    }

    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request service
     * @return \Illuminate\Http\Response
     */
    public function servicesList(Request $request)
    {
        $requestData = $this->getRequestData($request);
        $requestData['user_id'] = $request->user()->user_id;
        $services =  $this->servicesModelObj->servicesList($requestData);
        // validate, is query executed successfully 
        if($services)
        {
            return  $this->resultResponse(
                Config::get('restresponsecode.SUCCESS'), 
                $services,  
                [],
                '', 
                $this->http_codes['HTTP_OK']
            );
        }else{
            return  $this->resultResponse(
                Config::get('restresponsecode.ERROR'), 
                [], 
                trans('Services::messages.service_failed'), 
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
        $validate = $this->ServicesValidator($requestData);
        if($validate["error"])
        {
            return $this->resultResponse(
                Config::get('restresponsecode.ERROR'), 
                [], 
                $validate['errors'],
                trans('Services::messages.service_failed'), 
                $this->http_codes['HTTP_OK']
            ); 
        }
        $requestData['user_id'] = $request->user()->user_id;
       // Create service in database 
        $isServicesCreated = $this->servicesModelObj->createService($requestData);
        // validate, is query executed successfully 
        if(!empty($isServicesCreated))
        {
            return  $this->resultResponse(
                Config::get('restresponsecode.SUCCESS'), 
                $isServicesCreated, 
                [],
                trans('Services::messages.service_save'), 
                $this->http_codes['HTTP_OK']
            );

        }else{
            return  $this->resultResponse(
                Config::get('restresponsecode.ERROR'), 
                [], 
                [],
                trans('Services::messages.service_failed'), 
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
    private function ServicesValidator(array $data)
    {
        $error      = false;
        $errors     = [];
        $rules      = [
                        'srv_name'     => 'required',
                        'srv_cost'     => 'required',
                        'srv_duration' => 'required',
                        'srv_unit'     => 'required'
                      ];
        $messages   = [
                        'srv_name.required'     => "The service name field is required.",
                        'srv_cost.required'     => "The service cost field is required.",
                        'srv_duration.required' => "The service duration field is required.",
                        'srv_unit.required'     => "The service duration unit field is required."
                      ];
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
        $validate = $this->ServicesValidator($requestData);
        if($validate["error"])
        {
            return $this->resultResponse(
                Config::get('restresponsecode.ERROR'), 
                [], 
                $validate['errors'],
                trans('Services::messages.service_failed'), 
                $this->http_codes['HTTP_OK']
            ); 
        }
        $requestData['user_id']     = $request->user()->user_id;
        // Update service detail in database 
        $servicesUpdateData = $this->servicesModelObj->updateService($requestData);
        // validate, is query executed successfully 
        if(!empty($servicesUpdateData))
        {
            return  $this->resultResponse(
                Config::get('restresponsecode.SUCCESS'), 
                $servicesUpdateData, 
                [],
                trans('Services::messages.service_update'), 
                $this->http_codes['HTTP_OK']
            );
        }else{
            return  $this->resultResponse(
                Config::get('restresponsecode.ERROR'), 
                [], 
                [],
                trans('Services::messages.service_failed'), 
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
        $primaryKey = $this->servicesModelObj->getTablePrimaryIdColumn();
        $primaryId = $this->securityLibObj->decrypt($requestData[$primaryKey]);
        $isPrimaryIdExist = $this->servicesModelObj->isPrimaryIdExist($primaryId);
        
        if(!$isPrimaryIdExist){
            return $this->resultResponse(
                Config::get('restresponsecode.ERROR'),
                [], 
                [$primaryKey=> [trans('Services::messages.service_not_found')]],
                trans('Services::messages.service_not_found'), 
                $this->http_codes['HTTP_OK']
            ); 
        }
        $isServicesDeleted=$this->servicesModelObj->deleteService($primaryId);
        // validate, is query executed successfully 
        if(!empty($isServicesDeleted))
        {
            return  $this->resultResponse(
                Config::get('restresponsecode.SUCCESS'), 
                [], 
                [],
                trans('Services::messages.service_delete'), 
                $this->http_codes['HTTP_OK']
            );
        }else{
            return  $this->resultResponse(
                Config::get('restresponsecode.ERROR'), 
                [], 
                trans('Services::messages.service_failed'), 
                [],
                $this->http_codes['HTTP_OK']
            );
        }
    }
}
