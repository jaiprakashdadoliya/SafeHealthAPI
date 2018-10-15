<?php

namespace App\Modules\Visits\Controllers;

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
use App\Libraries\DateTimeLib;
use App\Libraries\UtilityLib;
use App\Modules\Visits\Models\Diagnosis;
use App\Modules\Visits\Models\Visits;
use App\Modules\Setup\Models\StaticDataConfig as StaticData;
use App\Modules\Patients\Models\PatientsActivities;

/**
 * DiagnosisController
 *
 * @package                SafeHealth
 * @subpackage             DiagnosisController
 * @category               Controller
 * @DateOfCreation         18 june 2018
 * @ShortDescription       This controller to handle all the operation related to 
                           setup Symptoms
 **/
class DiagnosisController extends Controller
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
    public function __construct(Request $request)
    {
        $this->http_codes = $this->http_status_codes();

        // Init security library object
        $this->securityLibObj = new SecurityLib(); 

        // Init exception library object
        $this->exceptionLibObj = new ExceptionLib();

        // Init exception library object
        $this->dateTimeLibObj = new DateTimeLib();  
        $this->utilityLibObj = new UtilityLib();        

        // Init Diagnosis Model Object
        $this->diagnosisObj = new Diagnosis();

        // Init Visits Model Object
        $this->visitsModelObj = new Visits();

        // Init General staticData Model Object
        $this->staticDataConfigModelObj = new StaticData();

        // Init Patients Activities Model Object
        $this->patientActivitiesModelObj = new PatientsActivities();
    }

    /**
    * @DateOfCreation        13 June 2018
    * @ShortDescription      Get a validator for an incoming Symptoms request
    * @param                 \Illuminate\Http\Request  $request
    * @return                \Illuminate\Contracts\Validation\Validator
    */
    protected function addDiagnosisValidations(array $requestData, $extra = []){
        $errors         = [];
        $error          = false;
        $rules = [];

        // Check the required validation rule
        $rules = [
            'pat_id'            => 'required',
            'user_id'           => 'required',
            'visit_id'          => 'required',
            'disease_name'      => 'required',
            'date_of_diagnosis' => 'required',
        ];
        $rules = array_merge($rules,$extra);
      
        $validator = Validator::make($requestData, $rules);        
        if($validator->fails()){
            $error = true;
            $errors = $validator->errors();
        }
        return ["error" => $error,"errors" => $errors]; 
    }

    /**
     * @DateOfCreation        08 Aug 2018
     * @ShortDescription      This function is responsible to get the Symptoms add
     * @return                Array of status and message
     */
    public function addUpdatePatientDiagnosis(Request $request)
    {
        $requestData = $this->getRequestData($request);
        $requestData['user_id']       = $request->user()->user_id;
        $requestData['resource_type'] = $requestData['resource_type'];   
        $requestData['is_deleted']    = Config::get('constants.IS_DELETED_NO');  
        $extra = [];
        $validate = $this->addDiagnosisValidations($requestData, $extra); 

        $requestData['disease_id']  = !empty($requestData['disease_id']) && ($requestData['disease_id'] != 'undefined') ? $this->securityLibObj->decrypt($requestData['disease_id']) : $this->diagnosisObj->createDiseaseId($requestData) ;
        $requestData['pat_id']      = $this->securityLibObj->decrypt($requestData['pat_id']);
        $requestData['visit_id']    = $this->securityLibObj->decrypt($requestData['visit_id']);
        
        $dateResponse    = $this->dateTimeLibObj->covertUserDateToServerType($requestData['date_of_diagnosis'],'dd/mm/YY','Y-m-d');
        $endDateResponse = !empty($requestData['diagnosis_end_date']) ? $this->dateTimeLibObj->covertUserDateToServerType($requestData['diagnosis_end_date'],'dd/mm/YY','Y-m-d') : NULL;

        if ($dateResponse["code"] == '5000') {
                $errorResponseString = $dateResponse["message"];
                $errorResponseArray = ['date_of_diagnosis' => [$dateResponse["message"]]];
                return $this->resultResponse(
                Config::get('restresponsecode.ERROR'), 
                [], 
                $errorResponseArray,
                $errorResponseString, 
                $this->http_codes['HTTP_OK']
            );
        }
        $requestData['date_of_diagnosis']  = $dateResponse['result'];
        $requestData['diagnosis_end_date'] = $endDateResponse['result'];
        
        if($validate["error"]){
            return $this->resultResponse(
                Config::get('restresponsecode.ERROR'), 
                [], 
                $validate['errors'],
                trans('Visits::messages.diseases_add_validation_failed'), 
                $this->http_codes['HTTP_OK']
            ); 
        }

        try{
             /*$getVisitDiagnosisId = $this->visitsModelObj->checkIfRecordExist('patients_visit_diagnosis', ['visit_diagnosis_id'], ['visit_id' => $requestData['visit_id'], 'disease_id' => $requestData['disease_id']], 'get_data');
            $getDiagnosisId = json_decode(json_encode($getVisitDiagnosisId));*/
            if(!empty($requestData['visit_diagnosis_id'])){

               $createdPatientsVisitDiagnosisId = $this->securityLibObj->decrypt($requestData['visit_diagnosis_id']); 
                $updateReco = $this->diagnosisObj->addUpdatePatientVisitDiagnosis($requestData, $createdPatientsVisitDiagnosisId);
                
                $successMessage = trans('Visits::messages.diseases_update_successfull');
                $errorMessage   = trans('Visits::messages.diseases_update_fail');
            }else{

                $getVisitDiagnosisId = $this->visitsModelObj->checkIfRecordExist('patients_visit_diagnosis', ['visit_diagnosis_id'], ['visit_id' => $requestData['visit_id'], 'disease_id' => $requestData['disease_id']], 'get_data');
                $getDiagnosisId = json_decode(json_encode($getVisitDiagnosisId));

                if(!empty($getDiagnosisId)){
                    return $this->resultResponse(
                        Config::get('restresponsecode.ERROR'), 
                        [], 
                        ['msg'=>[trans('Visits::messages.disease_already_exist')]],
                        trans('Visits::messages.disease_already_exist'), 
                        $this->http_codes['HTTP_OK']
                    );
                }

                $createdPatientsVisitDiagnosisId = $this->diagnosisObj->addUpdatePatientVisitDiagnosis($requestData); 

                if($createdPatientsVisitDiagnosisId){
                    $userId = (in_array($request->user()->user_type, Config::get('constants.USER_TYPE_STAFF'))) ? $request->user()->created_by : $requestData['user_id'];
                    $activityData = [ 'pat_id' => $requestData['pat_id'], 'user_id' => $userId, 'activity_table' => 'patients_visit_diagnosis', 'visit_id' => $requestData['visit_id'] ];
                    $response = $this->patientActivitiesModelObj->insertActivity($activityData);
                }

                $successMessage = trans('Visits::messages.diseases_add_successfull');
                $errorMessage   = trans('Visits::messages.diseases_add_fail');
            }

            // validate, is query executed successfully 
            if($createdPatientsVisitDiagnosisId){
                $createdPatientsVisitDiagnosisId = $this->securityLibObj->encrypt($createdPatientsVisitDiagnosisId);
                return $this->resultResponse(
                    Config::get('restresponsecode.SUCCESS'), 
                    ['visit_diagnosis_id' => $createdPatientsVisitDiagnosisId], 
                    [],
                    $successMessage, 
                    $this->http_codes['HTTP_OK']
                );                           
            }else{                
                return $this->resultResponse(
                    Config::get('restresponsecode.ERROR'), 
                    [], 
                    [],
                    $errorMessage, 
                    $this->http_codes['HTTP_OK']
                );
            }           
        }catch (\Exception $ex) {
            $eMessage = $this->exceptionLibObj->reFormAndLogException($ex,'DiagnosisController', 'addUpdatePatientDiagnosis');
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
     * @DateOfCreation        08 Aug 2018
     * @ShortDescription      This function is responsible to get the Symptoms add
     * @return                Array of status and message
     */
    public function deletePatientDiagnosis(Request $request){
        $requestData = $this->getRequestData($request);

        try{
            $requestData['visit_diagnosis_id'] = $this->securityLibObj->decrypt($requestData['visit_diagnosis_id']);
            $deleteData = $this->diagnosisObj->deletePatientDiagnosis($requestData );

            if($deleteData){
                return $this->resultResponse(
                    Config::get('restresponsecode.SUCCESS'), 
                    [], 
                    [],
                    trans('Visits::messages.diseases_data_deleted'), 
                    $this->http_codes['HTTP_OK']
                );                           
            }else{                
                return $this->resultResponse(
                    Config::get('restresponsecode.ERROR'), 
                    [], 
                    [],
                    trans('Visits::messages.diseases_data_not_deleted'),
                    $this->http_codes['HTTP_OK']
                );
            }           
        }catch (\Exception $ex) {
            $eMessage = $this->exceptionLibObj->reFormAndLogException($ex,'DiagnosisController', 'deletePatientDiagnosis');
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
     * @DateOfCreation        08 Aug 2018
     * @ShortDescription      This function is responsible for get Symptoms Data by patId and visitId
     * @param                 encrypted integer $patId   
     * @param                 encrypted integer $visitId   
     * @return                Array of status and message
     */
    public function getPatientDiagnosisHistoryList(Request $request)
    {
        $requestData = $this->getRequestData($request);

        $requestData['visit_id'] = $this->securityLibObj->decrypt($requestData['visitId']);
        $requestData['pat_id']   = $this->securityLibObj->decrypt($requestData['patId']);
        
        $patientDiagnosisHistoryList = $this->diagnosisObj->getPatientDiagnosisHistoryList($requestData);

        return $this->resultResponse(
            Config::get('restresponsecode.SUCCESS'), 
            $patientDiagnosisHistoryList, 
            [],
            trans('Visits::messages.diagnosis_list_successfull'),
            $this->http_codes['HTTP_OK']
        );
    }

    /**
     * @DateOfCreation        08 Aug 2018
     * @ShortDescription      This function is responsible for get Symptoms Data by patId and visitId
     * @param                 encrypted integer $patId   
     * @param                 encrypted integer $visitId   
     * @return                Array of status and message
     */
    public function getDiagnosisOptionList(Request $request)
    {
        $requestData = $this->getRequestData($request);
        $patientDiagnosisOptionList = $this->diagnosisObj->patientDiagnosisOptionList($requestData);

        return $this->resultResponse(
            Config::get('restresponsecode.SUCCESS'), 
            $patientDiagnosisOptionList, 
            [],
            trans('Visits::messages.diseases_option_list_successfull'),
            $this->http_codes['HTTP_OK']
        );
    }
}
