<?php

namespace App\Modules\MedicalHistory\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Traits\RestApi;
use Config;
use App\Modules\MedicalHistory\Models\MedicalHistory as MedicalHistory;
use App\Libraries\SecurityLib;

class MedicalHistoryController extends Controller
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
        // Init MedicalHistory model object
        $this->medicalHistoryModelObj = new MedicalHistory();

        // Init security library object
        $this->securityLibObj = new SecurityLib(); 
    }

    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request disease
     * @return \Illuminate\Http\Response
     */
    public function diseasesList(Request $request)
    {
        $requestData = $this->getRequestData($request);
        $diseases =  $this->medicalHistoryModelObj->diseasesList($requestData);
        // validate, is query executed successfully 
        if($diseases)
        {
            return  $this->resultResponse(
                Config::get('restresponsecode.SUCCESS'), 
                $diseases,  
                [],
                '', 
                $this->http_codes['HTTP_OK']
            );
        }else{
            return  $this->resultResponse(
                Config::get('restresponsecode.ERROR'), 
                [], 
                trans('MedicalHistory::messages.disease_failed'), 
                [],
                $this->http_codes['HTTP_OK']
            );
        }
    }

    /**
     * Store a newly created or Edit an existing resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function save(Request $request)
    {
        $requestData = $this->getRequestData($request);
        // Validate request
        $validate = $this->MedicalHistoryValidator($requestData);
        if($validate["error"])
        {
            return $this->resultResponse(
                Config::get('restresponsecode.ERROR'), 
                [], 
                $validate['errors'],
                trans('MedicalHistory::messages.disease_failed'), 
                $this->http_codes['HTTP_OK']
            ); 
        }
            $new = $requestData['disease_id'] && !empty($requestData['disease_id']) ? false : true;
        
       // Create disease in database 
        $isMedicalHistorySaved = $this->medicalHistoryModelObj->saveDisease($requestData);
        // validate, is query executed successfully 
        if(!empty($isMedicalHistorySaved))
        {
            return  $this->resultResponse(
                Config::get('restresponsecode.SUCCESS'), 
                $isMedicalHistorySaved, 
                [],
                ($new) ? trans('MedicalHistory::messages.disease_save') : trans('MedicalHistory::messages.disease_update'), 
                $this->http_codes['HTTP_OK']
            );

        }else{
            return  $this->resultResponse(
                Config::get('restresponsecode.ERROR'), 
                [], 
                [],
                trans('MedicalHistory::messages.disease_failed'), 
                $this->http_codes['HTTP_OK']
            );
        }
    }

    /**
    * This function is responsible for validating disease data
    * 
    * @param  Array $data This contains full member input data 
    * @return Array $error status of error
    */ 
    private function MedicalHistoryValidator(array $data)
    {
        $error      = false;
        $errors     = [];
        $rules      = [
                        'disease_name'     => 'required',
                      ];
        $messages   = [
                        'disease_name.required'     => "The disease name field is required.",
                      ];
        $validator = Validator::make($data, $rules, $messages); 
        if($validator->fails()){
            $error = true;
            $errors = $validator->errors();
        }
        return ["error" => $error,"errors" => $errors];
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id disease id for particular disease
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {   
        $requestData = $this->getRequestData($request);
        $primaryKey = $this->medicalHistoryModelObj->getTablePrimaryIdColumn();
        $primaryId = $this->securityLibObj->decrypt($requestData[$primaryKey]);
        $isPrimaryIdExist = $this->medicalHistoryModelObj->isPrimaryIdExist($primaryId);
        
        if(!$isPrimaryIdExist){
            return $this->resultResponse(
                Config::get('restresponsecode.ERROR'),
                [], 
                [$primaryKey=> [trans('MedicalHistory::messages.disease_not_found')]],
                trans('MedicalHistory::messages.disease_not_found'), 
                $this->http_codes['HTTP_OK']
            ); 
        }
        $isMedicalHistoryDeleted=$this->medicalHistoryModelObj->deleteDisease($primaryId);
        // validate, is query executed successfully 
        if(!empty($isMedicalHistoryDeleted))
        {
            return  $this->resultResponse(
                Config::get('restresponsecode.SUCCESS'), 
                [], 
                [],
                trans('MedicalHistory::messages.disease_delete'), 
                $this->http_codes['HTTP_OK']
            );
        }else{
            return  $this->resultResponse(
                Config::get('restresponsecode.ERROR'), 
                [], 
                trans('MedicalHistory::messages.disease_failed'), 
                [],
                $this->http_codes['HTTP_OK']
            );
        }
    }
}
