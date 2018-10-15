<?php

namespace App\Modules\ConsentForms\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Traits\RestApi;
use App\Libraries\PdfLib;
use PDF;
use Config;
use App\Libraries\SecurityLib;
use App\Modules\ConsentForms\Models\ConsentForms as ConsentForms;

class ConsentFormsController extends Controller
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

        // Init ConsentForms model object
        $this->consentFormsModelObj = new ConsentForms();
        
        // Init security library object
        $this->securityLibObj = new SecurityLib();
    }

    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request consentForm
     * @return \Illuminate\Http\Response
     */
    public function consentFormsList(Request $request)
    {
        $requestData = $this->getRequestData($request);
        $requestData['user_id'] = $request->user()->user_id;
        $consentForms =  $this->consentFormsModelObj->consentFormsList($requestData);
        // validate, is query executed successfully 
        if($consentForms)
        {
            return  $this->resultResponse(
                Config::get('restresponsecode.SUCCESS'), 
                $consentForms,
                [],
                '', 
                $this->http_codes['HTTP_OK']
            );
        }else{
            return  $this->resultResponse(
                Config::get('restresponsecode.ERROR'), 
                [], 
                trans('ConsentForms::messages.consent_form_failed'), 
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
        $requestData['user_id'] = $request->user()->user_id;
        $validate = $this->ConsentFormsValidator($requestData);
        if($validate["error"])
        {
            return $this->resultResponse(
                Config::get('restresponsecode.ERROR'), 
                [], 
                $validate['errors'],
                trans('ConsentForms::messages.consent_form_failed'), 
                $this->http_codes['HTTP_OK']
            ); 
        }
            $new = $requestData['consent_form_id'] && !empty($requestData['consent_form_id']) ? false : true;
        
       // Create consentForm in database 
        $isConsentFormsSaved = $this->consentFormsModelObj->saveConsentForm($requestData);
        // validate, is query executed successfully 
        if(!empty($isConsentFormsSaved))
        {
            return  $this->resultResponse(
                Config::get('restresponsecode.SUCCESS'), 
                $isConsentFormsSaved, 
                [],
                ($new) ? trans('ConsentForms::messages.consent_form_save') : trans('ConsentForms::messages.consent_form_update'), 
                $this->http_codes['HTTP_OK']
            );

        }else{
            return  $this->resultResponse(
                Config::get('restresponsecode.ERROR'), 
                [], 
                [],
                trans('ConsentForms::messages.consent_form_failed'), 
                $this->http_codes['HTTP_OK']
            );
        }
    }

    /**
    * This function is responsible for validating consentForm data
    * 
    * @param  Array $data This contains full member input data 
    * @return Array $error status of error
    */ 
    private function ConsentFormsValidator(array $data)
    {
        $error      = false;
        $errors     = [];
        $rules      = [
                        'consent_form_title'     => 'required',
                        'consent_form_content'     => 'required',
                      ];
        $messages   = [
                        'consent_form_title.required'     => "The Form Title field is required.",
                        'consent_form_Content.required'     => "The Content field is required.",
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
     * @param  int  $id consentForm id for particular consentForm
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {   
        $requestData = $this->getRequestData($request);
        $primaryKey = $this->consentFormsModelObj->getTablePrimaryIdColumn();
        $primaryId = $this->securityLibObj->decrypt($requestData[$primaryKey]);
        $isPrimaryIdExist = $this->consentFormsModelObj->isPrimaryIdExist($primaryId);
        
        if(!$isPrimaryIdExist){
            return $this->resultResponse(
                Config::get('restresponsecode.ERROR'),
                [], 
                [$primaryKey=> [trans('ConsentForms::messages.consent_form_not_found')]],
                trans('ConsentForms::messages.consent_form_not_found'), 
                $this->http_codes['HTTP_OK']
            ); 
        }
        $isConsentFormsDeleted=$this->consentFormsModelObj->deleteConsentForm($primaryId);
        // validate, is query executed successfully 
        if(!empty($isConsentFormsDeleted))
        {
            return  $this->resultResponse(
                Config::get('restresponsecode.SUCCESS'), 
                [], 
                [],
                trans('ConsentForms::messages.consent_form_delete'), 
                $this->http_codes['HTTP_OK']
            );
        }else{
            return  $this->resultResponse(
                Config::get('restresponsecode.ERROR'), 
                [], 
                trans('ConsentForms::messages.consent_form_failed'), 
                [],
                $this->http_codes['HTTP_OK']
            );
        }
    }

    public function generatePdf($consentFormId)
    {
        $consentFormId = $this->securityLibObj->decrypt($consentFormId);
        $consentFormsData = $this->consentFormsModelObj->getConsentFormById($consentFormId);
        if($consentFormsData){
            $content = str_replace("\n", '<br>', $consentFormsData->consent_form_content);
            $pdfLibObj = new PdfLib();
            $data = ['title' => $consentFormsData->consent_form_title,'content' => $content];
            $view = 'ConsentForms::consentFormPdf';
            $pdf = $pdfLibObj->genrateAndShowPdf($view, $data);
            return $pdf;
        }else{
            return false;
        }
    }
}
