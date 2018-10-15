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
use App\Modules\Visits\Models\Symptoms as Symptoms;
use App\Modules\Setup\Models\StaticDataConfig as StaticData;
use App\Modules\Patients\Models\PatientsActivities;

/**
 * SymptomsController
 *
 * @package                ILD India Registry
 * @subpackage             SymptomsController
 * @category               Controller
 * @DateOfCreation         18 june 2018
 * @ShortDescription       This controller to handle all the operation related to 
                           setup Symptoms
 **/
class SymptomsController extends Controller
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

        // Init Symptoms Model Object
        $this->symptomsObj = new Symptoms();

        // Init exception library object
        $this->exceptionLibObj = new ExceptionLib();

        // Init exception library object
        $this->dateTimeLibObj = new DateTimeLib();  

        $this->utilityLibObj = new UtilityLib();

        // Init Patients Activities Model Object
        $this->patientActivitiesModelObj = new PatientsActivities();

        // Init exception library object
        $this->staticDataObj = new StaticData();          
    }

    /**
    * @DateOfCreation        13 June 2018
    * @ShortDescription      Get a validator for an incoming Symptoms request
    * @param                 \Illuminate\Http\Request  $request
    * @return                \Illuminate\Contracts\Validation\Validator
    */
    protected function addSymptomValidations(array $requestData, $extra = [], $extraMessages = []){
        $errors         = [];
        $error          = false;
        $rules = [];

        // Check the required validation rule
        $rules = [
            'pat_id'       => 'required',
            'user_id'      => 'required',
            'visit_id'     => 'required',
            'symptom_name' => 'required',
        ];
        $validationMessageData = [];
        $rules = array_merge($rules,$extra);
        $validationMessageData = array_merge($validationMessageData,$extraMessages);
      
        $validator = Validator::make($requestData, $rules,$validationMessageData);        
        if($validator->fails()){
            $error = true;
            $errors = $validator->errors();
        }
        return ["error" => $error,"errors" => $errors]; 
    }

    /**
        * @DateOfCreation        21 May 2018
        * @ShortDescription      This function is responsible to get the Symptoms add
        * @return                Array of status and message
        */
    public function addSymptom(Request $request)
    {
        $requestData = $this->getRequestData($request);
        $requestData['user_id']       = $request->user()->user_id;
        $requestData['resource_type'] = $requestData['resource_type'];   
        $requestData['is_deleted']    = Config::get('constants.IS_DELETED_NO');  
        $extra = ['symptom_id_select'=>'required'];
        $extraMessages = ['symptom_id_select.required'=>trans('Visits::messages.symptom_id_required')];
        $validate = $this->addSymptomValidations($requestData, $extra,$extraMessages); 
        
        $requestData['symptom_id']  = $this->symptomsObj->createSymptomId($requestData);
        if(!$requestData['symptom_id']){
            $requestData['symptom_id'] ='';
            $extra = ['symptom_id'=> 'required'];
            $extraMessages = ['symptom_id.required'=>trans('Visits::messages.symptom_id_required')];
            $validate = $this->addSymptomValidations($requestData, $extra,$extraMessages); 
        }
        $requestData['pat_id']      = $this->securityLibObj->decrypt($requestData['pat_id']);
        $requestData['visit_id']    = $this->securityLibObj->decrypt($requestData['visit_id']);
        $dateResponse = $this->dateTimeLibObj->covertUserDateToServerType($requestData['since_date'],'dd/mm/YY','Y-m-d');
        if ($dateResponse["code"] == '5000') {
                $errorResponseString = $dateResponse["message"];
                $errorResponseArray = ['since_date' => [$dateResponse["message"]]];
                return $this->resultResponse(
                Config::get('restresponsecode.ERROR'), 
                [], 
                $errorResponseArray,
                $errorResponseString, 
                $this->http_codes['HTTP_OK']
            );
        }
        $requestData['since_date'] =  $dateResponse['result'];
        if($validate["error"]){
            return $this->resultResponse(
                Config::get('restresponsecode.ERROR'), 
                [], 
                $validate['errors'],
                trans('Visits::messages.symptom_add_validation_failed'), 
                $this->http_codes['HTTP_OK']
            ); 
        }
        try{
            $createdSymptomId = $this->symptomsObj->addSymptom($requestData);

            // validate, is query executed successfully 
            if($createdSymptomId){
                $createdSymptomIdEncrypted = $this->securityLibObj->encrypt($createdSymptomId);

                if($createdSymptomIdEncrypted){
                    $userId = (in_array($request->user()->user_type, Config::get('constants.USER_TYPE_STAFF'))) ? $request->user()->created_by : $requestData['user_id'];
                    $activityData = ['pat_id' => $requestData['pat_id'], 'user_id' => $userId, 'activity_table' => 'visit_symptoms', 'visit_id' => $requestData['visit_id']];
                    $response = $this->patientActivitiesModelObj->insertActivity($activityData);
                }
                
                return $this->resultResponse(
                    Config::get('restresponsecode.SUCCESS'), 
                    ['visit_symptom_id' => $createdSymptomIdEncrypted], 
                    [],
                    trans('Visits::messages.symptom_add_successfull'), 
                    $this->http_codes['HTTP_OK']
                );
                           
            }else{
                
                return $this->resultResponse(
                    Config::get('restresponsecode.ERROR'), 
                    [], 
                    [],
                    trans('Visits::messages.symptom_add_fail'), 
                    $this->http_codes['HTTP_OK']
                );
            }           
        }catch (\Exception $ex) {
            $eMessage = $this->exceptionLibObj->reFormAndLogException($ex,'SymptomsController', 'addSymptom');
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
     * @DateOfCreation        15 June 2018
     * @ShortDescription      This function is responsible for get Symptoms Data by Symptoms_visit_id
     * @param                 Array $request   
     * @return                Array of status and message
     */
    public function edit($symptomVisitId)
    {
        $symptomVisitId = $this->securityLibObj->decrypt($symptomVisitId);

        $patientSymptomsVisitData = $this->symptomsObj->getSymptomsVisitData($symptomVisitId);
        return $this->resultResponse(
                Config::get('restresponsecode.SUCCESS'), 
                $patientSymptomsVisitData, 
                [],
                trans('Visits::messages.symptom_visit_data'),
                $this->http_codes['HTTP_OK']
            );    
    }

    /**
     * @DateOfCreation        19 June 2018
     * @ShortDescription      This function is responsible for get Symptoms Data by patId and visitId
     * @param                 encrypted integer $patId   
     * @param                 encrypted integer $visitId   
     * @return                Array of status and message
     */
    public function getSymptomsData(Request $request)
    {
        $requestData = $this->getRequestData($request);
        $patientSymptomsVisitData = $this->symptomsObj->getSymptomsDataByPatientIdAndVistId($requestData);
        
        return $this->resultResponse(
                Config::get('restresponsecode.SUCCESS'), 
                $patientSymptomsVisitData, 
                [],
                trans('Visits::messages.symptom_list_successfull'),
                $this->http_codes['HTTP_OK']
            );

    }

    /**
        * @DateOfCreation        20 june 2018
        * @ShortDescription      This function is responsible to Symptoms update
        * @return                Array of status and message
        */
    public function updateSymptom(Request $request)
    {
        $requestData = $this->getRequestData($request);
        $requestData['user_id']       = $request->user()->user_id;
        $requestData['resource_type'] = $requestData['resource_type'];   
        $requestData['is_deleted']    = Config::get('constants.IS_DELETED_NO');  
        $extra = ['visit_symptom_id' => 'required'];
        $validate = $this->addSymptomValidations($requestData, $extra);
        $requestData['symptom_id'] = $this->symptomsObj->createSymptomId($requestData);
        if(!$requestData['symptom_id']){
            $requestData['symptom_id'] ='';
            $extra = ['visit_symptom_id' => 'required','symptom_id'=> 'required'];
            $extraMessages = ['symptom_id.required'=>trans('Visits::messages.symptom_id_required')];
            $validate = $this->addSymptomValidations($requestData, $extra,$extraMessages); 
        }
        $requestData['visit_symptom_id'] = $this->securityLibObj->decrypt($requestData['visit_symptom_id']);
        $requestData['pat_id'] = $this->securityLibObj->decrypt($requestData['pat_id']);
        $requestData['visit_id'] = $this->securityLibObj->decrypt($requestData['visit_id']);
        $dateResponse = $this->dateTimeLibObj->covertUserDateToServerType($requestData['since_date'],'dd/mm/YY','Y-m-d');
        if ($dateResponse["code"] == '5000') {
                $errorResponseString = $dateResponse["message"];
                $errorResponseArray = ['since_date' => [$dateResponse["message"]]];
                return $this->resultResponse(
                Config::get('restresponsecode.ERROR'), 
                [], 
                $errorResponseArray,
                $errorResponseString, 
                $this->http_codes['HTTP_OK']
            );
        }
        $requestData['since_date'] =  $dateResponse['result'];
        if($validate["error"]){
            return $this->resultResponse(
                Config::get('restresponsecode.ERROR'), 
                [], 
                $validate['errors'],
                trans('Visits::messages.symptom_update_validation_failed'), 
                $this->http_codes['HTTP_OK']
            ); 
        }
        try{
            $updateVistSymptomId = $this->symptomsObj->updateSymptom($requestData);

            // validate, is query executed successfully 
            if($updateVistSymptomId){
                $updateVistSymptomIdEncrypted = $this->securityLibObj->encrypt($updateVistSymptomId);
                return $this->resultResponse(
                    Config::get('restresponsecode.SUCCESS'), 
                    ['visit_symptom_id' => $updateVistSymptomIdEncrypted], 
                    [],
                    trans('Visits::messages.symptom_update_successfull'), 
                    $this->http_codes['HTTP_OK']
                );
                           
            }else{
                    return $this->resultResponse(
                    Config::get('restresponsecode.ERROR'), 
                    [], 
                    [],
                    trans('Visits::messages.symptom_update_fail'), 
                    $this->http_codes['HTTP_OK']
                );
            }           
        }catch (\Exception $ex) {
            $eMessage = $this->exceptionLibObj->reFormAndLogException($ex,'SymptomsController', 'updateSymptom');
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
    * @DateOfCreation        11 June 2018
    * @ShortDescription      This function is responsible for delete visit Symptoms Data 
    * @param                 Array $visitSymptomId   
    * @return                Array of status and message
    */
    public function destroy(Request $request)
    {   
        $requestData = $this->getRequestData($request);
        $primaryKey = $this->symptomsObj->getTablePrimaryIdColumn();
        $primaryId = $this->securityLibObj->decrypt($requestData[$primaryKey]);
        $isPrimaryIdExist = $this->symptomsObj->isPrimaryIdExist($primaryId);
        if(!$isPrimaryIdExist){
            return $this->resultResponse(
                Config::get('restresponsecode.ERROR'), 
                [], 
                ['visit_symptom_id'=> [trans('Visits::messages.symptom_not_exist')]],
                trans('Visits::messages.symptom_not_exist'), 
                $this->http_codes['HTTP_OK']
            ); 
        }

        $symptomDeleteData   = $this->symptomsObj->doDeletesymptom($primaryId);
        if($symptomDeleteData){
            return $this->resultResponse(
                    Config::get('restresponsecode.SUCCESS'), 
                    [], 
                    [],
                    trans('Visits::messages.symptom_data_deleted'),
                    $this->http_codes['HTTP_OK']
                );
        }
        return $this->resultResponse(
                Config::get('restresponsecode.ERROR'), 
                [], 
                [],
                trans('Visits::messages.symptom_data_not_deleted'),
                $this->http_codes['HTTP_OK']
            );

    }

    /**
     * @DateOfCreation        2 July 2018
     * @ShortDescription      This function is responsible to get the Domestic factor field value
     * @return                Array of status and message
     */
    public function getPatientSymptomsDetail(Request $request)
    {
        $requestData        = $this->getRequestData($request);
        $visitId            = $requestData['visit_id'];
        $patientId          = $requestData['pat_id'];
        $visitId            = $this->securityLibObj->decrypt($visitId);

        $patientSymptomsTest  = $this->symptomsObj->getPatientSymptomsTestRecord($visitId);
        $symptomsTestRecordWithFectorKey = !empty($patientSymptomsTest) && count($patientSymptomsTest)>0 ? $this->utilityLibObj->changeArrayKey(json_decode(json_encode($patientSymptomsTest),true), 'hopi_type_id'):[];
        
        $staticDataKey              = $this->staticDataObj->getStaticDataFunction(['getsymptomsTestData']);
        $staticDataArrWithCustomKey = $this->utilityLibObj->changeArrayKey($staticDataKey, 'id');
       
        $finalCheckupRecords = [];
        $tempData = [];
        if(!empty($staticDataArrWithCustomKey)){
            foreach ($staticDataArrWithCustomKey as $hopiTypeIdKey => $hopiValue) {
                $temp = [];
                $encrypthopiTypeIdKey = $this->securityLibObj->encrypt($hopiTypeIdKey);
                $symptomsTestValuesData = ( array_key_exists($hopiTypeIdKey, $symptomsTestRecordWithFectorKey) ? $symptomsTestRecordWithFectorKey[$hopiTypeIdKey]['hopi_value'] : '');
                $temp = [  
                'showOnForm'=>true,
                'name' => 'hopi_type_'.$encrypthopiTypeIdKey,
                'title' => $hopiValue['value'],
                'type' => $hopiValue['input_type'],
                'value' => $hopiValue['input_type'] === 'customcheckbox' ? [(string) $symptomsTestValuesData] : $symptomsTestValuesData,
                'cssClasses' => $hopiValue['cssClasses'],
                'clearFix' => $hopiValue['isClearfix'],

            ];
            if($hopiValue['input_type'] === 'date'){
                $temp['format'] =  isset($hopiValue['format']) ?  $hopiValue['format'] : Config::get('constants.REACT_WEB_DATE_FORMAT');
            }
            $tempData['hopi_type_'.$encrypthopiTypeIdKey.'_data'] = isset($hopiValue['input_type_option']) && !empty($hopiValue['input_type_option']) ? $hopiValue['input_type_option']:[] ;
                
            $finalCheckupRecords['form_'.$hopiValue['type']]['fields'][] = $temp;
            $finalCheckupRecords['form_'.$hopiValue['type']]['data'] = $tempData;
            $finalCheckupRecords['form_'.$hopiValue['type']]['handlers'] = [];
            if(isset($hopiValue['formName'])){
                $finalCheckupRecords['form_'.$hopiValue['type']]['formName'] = $hopiValue['formName'];
            }
            }
        }

        return $this->resultResponse(
                Config::get('restresponsecode.SUCCESS'), 
                $finalCheckupRecords, 
                [],
                trans('Visits::messages.symptoms_test_get_data_successfull'),
                $this->http_codes['HTTP_OK']
            );
    }

    /**
     * @DateOfCreation        13 june 2018
     * @ShortDescription      This function is responsible for insert Patient Data 
     * @param                 Array $request   
     * @return                Array of status and message
     */
    public function addUpdateHopi(Request $request)
    { 
        $requestData = $this->getRequestData($request);
        $encryptedVisitId               = $requestData['visit_id'];
        $requestData['user_id']         = $request->user()->user_id;
        $requestData['is_deleted']      = Config::get('constants.IS_DELETED_NO');  
        $requestData['pat_id']          = $this->securityLibObj->decrypt($requestData['pat_id']);
        $requestData['visit_id']        = $this->securityLibObj->decrypt($requestData['visit_id']);
        $visitId                        = $requestData['visit_id'];
        try{
            DB::beginTransaction();
            $patientSymptomsTest  = $this->symptomsObj->getPatientSymptomsTestRecord($visitId);
            $patientSymptomsRecordWithFectorKey = !empty($patientSymptomsTest) && count($patientSymptomsTest)>0 ? $this->utilityLibObj->changeArrayKey(json_decode(json_encode($patientSymptomsTest),true), 'hopi_type_id'):[];
        
            $staticDataKey              = $this->staticDataObj->getStaticDataFunction(['getsymptomsTestData']);
            $staticDataArrWithCustomKey = $this->utilityLibObj->changeArrayKey($staticDataKey, 'id');
            $insertData = [];
            $insertDataPlace = [];
            if(!empty($staticDataArrWithCustomKey)){
                foreach ($staticDataArrWithCustomKey as $hopiTypeIdKey => $hopiValue) {
                    $hopiTypeIdEncrypted = $this->securityLibObj->encrypt($hopiTypeIdKey);
                    $temp = [];
                    $hopiTypeValue = isset($requestData['hopi_type_'.$hopiTypeIdEncrypted]) ? $requestData['hopi_type_'.$hopiTypeIdEncrypted] : '';
                    if($hopiValue['input_type'] === 'date' && !empty($hopiTypeValue)){
                        $dateResponse = $this->dateTimeLibObj->covertUserDateToServerType($hopiTypeValue,'dd/mm/YY','Y-m-d');
                        if ($dateResponse["code"] == '5000') {
                                $errorResponseString = $dateResponse["message"];
                                $errorResponseArray = [$hopiValue['value'] => [$dateResponse["message"]]];
                                $dataDbStatus = true;
                                break;
                        }
                        $hopiTypeValue = $dateResponse['result'];
                    }
                    $temp = [
                            'pat_id'    =>  $requestData['pat_id'],
                            'visit_id'  =>  $requestData['visit_id'],
                            'hopi_type_id'  =>  $hopiTypeIdKey,
                            'hopi_type'  =>  $hopiValue['type'],
                            'hopi_value'  =>  $hopiTypeValue,
                            'ip_address'  =>  $requestData['ip_address'],
                            'resource_type'  =>  $requestData['resource_type'],
                    ];
                    $hopiId = (isset($patientSymptomsRecordWithFectorKey[$hopiTypeIdKey]['hopi_id']) && 
                                !empty($patientSymptomsRecordWithFectorKey[$hopiTypeIdKey]['hopi_id']) )
                                ? $this->securityLibObj->decrypt($patientSymptomsRecordWithFectorKey[$hopiTypeIdKey]['hopi_id']) : '';
                   if(array_key_exists($hopiTypeIdKey, $patientSymptomsRecordWithFectorKey) && !empty($hopiId)){
                        $whereData =[];
                        $whereData = [
                            'pat_id'    =>  $requestData['pat_id'],
                            'visit_id'  =>  $requestData['visit_id'],
                            'hopi_id'  =>  $hopiId,
                        ];
                        $updateData = $this->symptomsObj->updatePatientSymptomsTest($temp,$whereData);
                        if(!$updateData){
                            $dataDbStatus = true;
                            $dbCommitStatus = false;
                            break;
                        }else{
                            $dbCommitStatus = true;
                        }
                   }
                   if(!empty($hopiTypeValue) && empty($hopiId)){
                        $insertData[] = $temp;
                   }
                }
                
                if(isset($dataDbStatus) && $dataDbStatus){
                    DB::rollback();
                    return $this->resultResponse(
                        Config::get('restresponsecode.ERROR'), 
                        [], 
                        (isset($errorResponseArray) ? $errorResponseArray:[]),
                        (isset($errorResponseString) ? $errorResponseString :'').trans('Visits::messages.patient_symptoms_hopi_add_fail'), 
                        $this->http_codes['HTTP_OK']
                    );
                }
                if(!empty($insertData)){
                    $addData = $this->symptomsObj->addPatientSymptomsTest($insertData);
                    if(!$addData){
                        DB::rollback();
                        return $this->resultResponse(
                            Config::get('restresponsecode.ERROR'), 
                            [], 
                            [],
                            trans('Visits::messages.patient_symptoms_hopi_add_fail'), 
                            $this->http_codes['HTTP_OK']
                        );
                    }else{
                        DB::commit();
                        $dbCommitStatus = false;
                        return $this->resultResponse(
                            Config::get('restresponsecode.SUCCESS'), 
                            [], 
                            [],
                            trans('Visits::messages.patient_symptoms_hopi_add_success'), 
                            $this->http_codes['HTTP_OK']
                        );
                    }
                }else if(!isset($dbCommitStatus)){
                    $dbCommitStatus = true;
                }

                if(isset($dbCommitStatus) && $dbCommitStatus){
                    DB::commit();
                    return $this->resultResponse(
                        Config::get('restresponsecode.SUCCESS'), 
                        [], 
                        [],
                        trans('Visits::messages.patient_symptoms_hopi_add_success'), 
                        $this->http_codes['HTTP_OK']
                    );
                }
            }else{
                 DB::rollback();
                        return $this->resultResponse(
                            Config::get('restresponsecode.SUCCESS'), 
                            [], 
                            [],
                            trans('Visits::messages.patient_symptoms_hopi_add_fail'), 
                            $this->http_codes['HTTP_OK']
                        );
            }
        } catch (\Exception $ex) {
            DB::rollback();
            $eMessage = $this->exceptionLibObj->reFormAndLogException($ex,'SymptomsController', 'addUpdateHopi');
            return $this->resultResponse(
                Config::get('restresponsecode.EXCEPTION'), 
                [], 
                [],
                $eMessage, 
                $this->http_codes['HTTP_OK']
            );
        }  
    }
    
}
