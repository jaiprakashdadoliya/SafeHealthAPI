<?php

namespace App\Modules\Referral\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Traits\RestApi;
use Config;
use App\Modules\Referral\Models\Referral as Referral;
use App\Libraries\SecurityLib;

class ReferralController extends Controller
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
        // Init Referral model object
        $this->referralModelObj = new Referral();
        // Init security library object
        $this->securityLibObj = new SecurityLib();  
    }

    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request service
     * @return \Illuminate\Http\Response
     */
    public function referralList(Request $request)
    {
        $requestData = $this->getRequestData($request);
        $requestData['user_id'] = $request->user()->user_id;
        $referralList =  $this->referralModelObj->getList($requestData);
        // validate, is query executed successfully 
        if($referralList)
        {
            return  $this->resultResponse(
                Config::get('restresponsecode.SUCCESS'), 
                $referralList,  
                [], 
                trans('Referral::messages.doc_ref_fetch'), 
                $this->http_codes['HTTP_OK']
            );
        }else{
            return  $this->resultResponse(
                Config::get('restresponsecode.ERROR'), 
                [], 
                trans('Referral::messages.doc_ref_failed'), 
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
        $validate = $this->ReferralValidator($requestData);
        if($validate["error"])
        {
            return $this->resultResponse(
                Config::get('restresponsecode.ERROR'), 
                [], 
                $validate['errors'],
                trans('Referral::messages.doc_ref_failed'), 
                $this->http_codes['HTTP_OK']
            ); 
        }
        $requestData['user_id'] = $request->user()->user_id;

       
       // Create service in database 
        $isReferralCreated = $this->referralModelObj->createReferral($requestData);
        // validate, is query executed successfully 
        if(!empty($isReferralCreated))
        {
            return  $this->resultResponse(
                Config::get('restresponsecode.SUCCESS'), 
                $isReferralCreated, 
                [],
                trans('Referral::messages.doc_ref_save'), 
                $this->http_codes['HTTP_OK']
            );

        }else{
            return  $this->resultResponse(
                Config::get('restresponsecode.ERROR'), 
                [], 
                [],
                trans('Referral::messages.doc_ref_failed'), 
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
    private function ReferralValidator(array $data)
    {
        $error      = false;
        $errors     = [];
        $rules      = ['doc_ref_name'     => 'required'];
        $messages   = ['doc_ref_name.required'     => "The referral doctor name is required."];
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
        $validate = $this->ReferralValidator($requestData);
        if($validate["error"])
        {
            return $this->resultResponse(
                Config::get('restresponsecode.ERROR'), 
                [], 
                $validate['errors'],
                trans('Referral::messages.doc_ref_failed'), 
                $this->http_codes['HTTP_OK']
            ); 
        }
        $requestData['user_id']     = $request->user()->user_id;
        // Update service detail in database 
        $isReferralUpdate = $this->referralModelObj->updateReferral($requestData);
        // validate, is query executed successfully 
        if(!empty($isReferralUpdate))
        {
            return  $this->resultResponse(
                Config::get('restresponsecode.SUCCESS'), 
                $isReferralUpdate, 
                [],
                trans('Referral::messages.doc_ref_update'), 
                $this->http_codes['HTTP_OK']
            );
        }else{
            return  $this->resultResponse(
                Config::get('restresponsecode.ERROR'), 
                [], 
                [],
                trans('Referral::messages.doc_ref_failed'), 
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
        $primaryKey = $this->referralModelObj->getTablePrimaryIdColumn();
        $primaryId = $this->securityLibObj->decrypt($requestData[$primaryKey]);
        $isPrimaryIdExist = $this->referralModelObj->isPrimaryIdExist($primaryId);
        
        if(!$isPrimaryIdExist){
            return $this->resultResponse(
                Config::get('restresponsecode.ERROR'),
                [], 
                [$primaryKey=> [trans('Referral::messages.doc_ref_not_found')]],
                trans('Referral::messages.doc_ref_not_found'), 
                $this->http_codes['HTTP_OK']
            ); 
        }
        $isReferralDeleted=$this->referralModelObj->deleteReferral($primaryId);
        // validate, is query executed successfully 
        if(!empty($isReferralDeleted))
        {
            return  $this->resultResponse(
                Config::get('restresponsecode.SUCCESS'), 
                [], 
                [],
                trans('Referral::messages.doc_ref_delete'), 
                $this->http_codes['HTTP_OK']
            );
        }else{
            return  $this->resultResponse(
                Config::get('restresponsecode.ERROR'), 
                [], 
                trans('Referral::messages.doc_ref_failed'), 
                [],
                $this->http_codes['HTTP_OK']
            );
        }
    }
}
