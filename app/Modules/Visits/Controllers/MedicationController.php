<?php

namespace App\Modules\Visits\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Auth;
use Session;
use Config;
use DB;
use File;
use Response;
use Carbon\Carbon;
use App\Traits\SessionTrait;
use App\Traits\RestApi;
use App\Traits\FxFormHandler;
use App\Traits\Encryptable;
use App\Libraries\SecurityLib;
use App\Libraries\ExceptionLib;
use App\Libraries\FileLib;
use App\Libraries\UtilityLib;
use App\Libraries\DateTimeLib;
use App\Modules\Visits\Models\Medication;
use App\Modules\Patients\Models\PatientsActivities;
use App\Modules\Doctors\Models\ManageDrugs;
use App\Modules\Doctors\Models\DrugType;

/**
 * MedicationController
 *
 * @package                Safe Health
 * @subpackage             MedicationController
 * @category               Controller
 * @DateOfCreation         7 Aug 2018
 * @ShortDescription       This controller to handle all the operation related to 
                           medications 
 */
class MedicationController extends Controller
{

    use SessionTrait, RestApi, FxFormHandler, Encryptable;

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

        // Init Utility Library object
        $this->utilityLibObj = new UtilityLib();

        // Init exception library object
        $this->exceptionLibObj = new ExceptionLib();   

        // Init dateTime library object
        $this->dateTimeLibObj = new DateTimeLib();        

        // Init medication Model Object
        $this->medicationObj = new Medication();

        // Init Patients Activities Model Object
        $this->patientActivitiesModelObj = new PatientsActivities();

        // Init DrugType model object
        $this->drugTypeObj = new DrugType(); 
    }

    /**
     * @DateOfCreation        14 July 2018
     * @ShortDescription      This function is responsible to get medicine list
     * @return                Array of medicines and message
     */
    public function getMedicineListData(Request $request)
    {
        $requestData    = $this->getRequestData($request);        

        $userId = (in_array($request->user()->user_type, Config::get('constants.USER_TYPE_STAFF'))) ? $request->user()->created_by : $request->user()->user_id;

         //$medicationList = $this->medicationObj->getMedicineListData();
        $medicationList['dose_unit'] = $this->medicationObj->getDoseUnit();
        $medicationList['drug_type']=$this->drugTypeObj->getAllDrugType();
        
        return $this->resultResponse(
                Config::get('restresponsecode.SUCCESS'), 
                $medicationList, 
                [],
                trans('Visits::messages.medication_medicine_list_successfull'),
                $this->http_codes['HTTP_OK']
            );
    }

    /**
     * @DateOfCreation        14 July 2018
     * @ShortDescription      This function is responsible to get medicine list
     * @return                Array of medicines and message
     */
    public function getPatientMedicationData(Request $request)
    {
        $requestData    = $this->getRequestData($request);

        $visitId = $this->securityLibObj->decrypt($requestData['visitId']);
        $patId   = $this->securityLibObj->decrypt($requestData['patientId']);
        $medicationList = $this->medicationObj->getPatientMedicationData($visitId, $patId);
        
        return $this->resultResponse(
                Config::get('restresponsecode.SUCCESS'), 
                $medicationList, 
                [],
                trans('Visits::messages.patient_medicine_data_fetched_successfully'),
                $this->http_codes['HTTP_OK']
            );
    }

    /**
     * @DateOfCreation        14 July 2018
     * @ShortDescription      This function is responsible to delete patient Medication record
     * @return                Array of medicines and message
     */
    public function deletePatientMedicationData(Request $request)
    {
        $requestData    = $this->getRequestData($request);

        $medicationId   = $this->securityLibObj->decrypt($requestData['medicationId']);
        try{
            DB::beginTransaction();

            $updateData =  ['is_deleted' => Config::get('constants.IS_DELETED_YES')];                
            $whereData  =  ['pmh_id' => $medicationId];
            $deletedRecord = $this->medicationObj->deletePatientMedicationData($updateData, $whereData);
            
            if($deletedRecord){
                DB::commit();
                return $this->resultResponse(
                    Config::get('restresponsecode.SUCCESS'), 
                    [], 
                    [],
                    trans('Visits::messages.patient_medicine_data_deleted_successfully'), 
                    $this->http_codes['HTTP_OK']
                );
            }else{
                DB::rollback();

                return $this->resultResponse(
                    Config::get('restresponsecode.ERROR'), 
                    [], 
                    [],
                    trans('Visits::messages.patient_medicine_data_deleted_fail'), 
                    $this->http_codes['HTTP_OK']
                );
            }                   
        } catch (\Exception $ex) {
            //user pat_consent_file unlink
            
            $eMessage = $this->exceptionLibObj->reFormAndLogException($ex,'MedicationController', 'deletePatientMedicationData');
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
     * @DateOfCreation        14 July 2018
     * @ShortDescription      This function is responsible to delete patient Medication record
     * @return                Array of medicines and message
     */
    public function discontinuePatientMedicationData(Request $request)
    {
        $requestData    = $this->getRequestData($request);

        $medicationId   = $this->securityLibObj->decrypt($requestData['medicationId']);

        try{
            DB::beginTransaction();
            $checkCurrentStatus = $this->medicationObj->checkDiscontinueStatus($medicationId);

            $updateData = ['is_discontinued' => ($checkCurrentStatus->is_discontinued == 1) ? 2: 1, 'medicine_end_date' => date(Config::get('constants.DB_SAVE_DATE_FORMAT'))];
            $whereData  = ['pmh_id' => $medicationId];
            $deletedRecord = $this->medicationObj->deletePatientMedicationData($updateData, $whereData);
            
            if($deletedRecord){
                DB::commit();
                return $this->resultResponse(
                    Config::get('restresponsecode.SUCCESS'), 
                    [], 
                    [],
                    trans('Visits::messages.patient_medicine_data_discontinued_successfully'), 
                    $this->http_codes['HTTP_OK']
                );
            }else{
                DB::rollback();

                return $this->resultResponse(
                    Config::get('restresponsecode.ERROR'), 
                    [], 
                    [],
                    trans('Visits::messages.patient_medicine_data_discontinued_fail'), 
                    $this->http_codes['HTTP_OK']
                );
            }                   
        } catch (\Exception $ex) {
            //user pat_consent_file unlink
            
            $eMessage = $this->exceptionLibObj->reFormAndLogException($ex,'MedicationController', 'deletePatientMedicationData');
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
     * @DateOfCreation        28 Sept 2018
     * @ShortDescription      This function is responsible to save and update patient Medication record
     * @return                Array of medicines and message
     */
    public function saveMultipleMedicationData(Request $request)
    {
        $requestData = $this->getRequestData($request);

        $requestData['user_id']         = $request->user()->user_id;
        $requestData['resource_type']   = Config::get('constants.RESOURCE_TYPE_WEB');   
        $requestData['is_deleted']      = Config::get('constants.IS_DELETED_NO');  
        $requestData['pat_id']          = $this->securityLibObj->decrypt($requestData['pat_id']);
        $requestData['visit_id']        = $this->securityLibObj->decrypt($requestData['visit_id']);
        $userId                         = (in_array($request->user()->user_type, Config::get('constants.USER_TYPE_STAFF'))) ? $request->user()->created_by : $requestData['user_id'];
        
        $medicationDataArray = [];
        if(isset($requestData['data_array']) && !empty($requestData['data_array'])){ 
            foreach ($requestData['data_array'] as $key => $dataValue) {
                $data = json_decode($dataValue, true);
                
                $validate = $this->multiMedicationValidations($data);
                if($validate["error"]){
                    return $this->resultResponse(
                        Config::get('restresponsecode.ERROR'), 
                        [], 
                        $validate['errors'],
                        trans('Visits::messages.medication_history_validation_failed'), 
                        $this->http_codes['HTTP_OK']
                    ); 
                }

                $medicationDataArray[] = $data;
            }
        }

        $medicationBatchArray = [];
        $activityBatchArray   = [];
        if(!empty($medicationDataArray)){
            foreach ($medicationDataArray as $dataValue) {
                $dataValue['user_id']        = $requestData['user_id'];
                $dataValue['resource_type']  = $requestData['resource_type'];
                $dataValue['is_deleted']     = $requestData['is_deleted'];
                $dataValue['pat_id']         = $requestData['pat_id'];
                $dataValue['visit_id']       = $requestData['visit_id'];
                
                $medicationData = $this->medicationObj->prepareMultipleMedicationData($dataValue, $userId);
                $medicationBatchArray[] = $medicationData['medicationData'];
                $activityBatchArray[]   = $medicationData['activityData'];
            }
        }

        $response = null;
        if(!empty($medicationBatchArray)){
            $response = $this->medicationObj->saveMultipleMedicationData($medicationBatchArray, $activityBatchArray);            
        }

        try{
            DB::beginTransaction();

            $successMessage = trans('Visits::messages.medication_medicine_data_insert_successfull');
            $errorMessage = trans('Visits::messages.medication_medicine_data_insert_fail');
            if($response){
                DB::commit();
                return $this->resultResponse(
                    Config::get('restresponsecode.SUCCESS'), 
                    [], 
                    [],
                    $successMessage, 
                    $this->http_codes['HTTP_OK']
                );
            }else{
                DB::rollback();

                return $this->resultResponse(
                    Config::get('restresponsecode.ERROR'), 
                    [], 
                    [],
                    $errorMessage, 
                    $this->http_codes['HTTP_OK']
                );
            }                       
        } catch (\Exception $ex) {
            $eMessage = $this->exceptionLibObj->reFormAndLogException($ex,'MedicationController', 'saveMultipleMedicationData');
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
    * @DateOfCreation        13 June 2018
    * @ShortDescription      Get a validator for an incoming Patients request
    * @param                 \Illuminate\Http\Request  $request
    * @return                \Illuminate\Contracts\Validation\Validator
    */
    protected function multiMedicationValidations(array $requestData){
        $errors         = [];
        $error          = false;
        
        $rules = [
                    'medicine_name'           => 'required',
                    'medicine_type'           => 'required',
                    'medicine_duration'       => 'required',
                    'medicine_duration_unit'  => 'required',
                    'medicine_dose'           => 'required_without_all:medicine_dose2,medicine_dose3',
                    'medicine_dose2'          => 'required_without_all:medicine_dose,medicine_dose3',
                    'medicine_dose3'          => 'required_without_all:medicine_dose2,medicine_dose',
                    'medicine_dose_unit'      => 'required'
                ];

        $validator = Validator::make($requestData, $rules);

        if($validator->fails()){
            $error = true;
            $errors = $validator->errors();
        }
        return ["error" => $error,"errors" => $errors]; 
    }

    /**
     * @DateOfCreation        14 July 2018
     * @ShortDescription      This function is responsible to save and update patient Medication record
     * @return                Array of medicines and message
     */
    public function saveMedicationData(Request $request)
    {
        $requestData = $this->getRequestData($request);

        $requestData['user_id']         = $request->user()->user_id;
        $requestData['resource_type']   = Config::get('constants.RESOURCE_TYPE_WEB');   
        $requestData['is_deleted']      = Config::get('constants.IS_DELETED_NO');  
        $requestData['pat_id']          = $this->securityLibObj->decrypt($requestData['pat_id']);
        $requestData['visit_id']        = $this->securityLibObj->decrypt($requestData['visit_id']);
        $pmhId                          = isset($requestData['pmh_id']) ? $this->securityLibObj->decrypt($requestData['pmh_id']) : NULL;
        $requestData['medicine_id']     = (!empty($requestData['medicine_id']) && $requestData['medicine_id'] != 'undefined') ? $this->securityLibObj->decrypt($requestData['medicine_id']) : $this->securityLibObj->decrypt($requestData['prev_medicine_id']);
        
        if(empty($requestData['medicine_id'])){
            return $this->resultResponse(
                Config::get('restresponsecode.ERROR'), 
                [], 
                ['medicine_id' => [trans('Visits::messages.medication_validation_medicine_not_found')]],
                __('messages.5033'), 
                $this->http_codes['HTTP_OK']
            );
        }
        
        $posConfig = 
        [            
            'patient_medication_history'=>
            [
                'medicine_duration'=>
                [   
                    'type'          => 'input',
                    'isRequired'    => true,
                    'validation'    => 'required',
                    'decrypt'       => false,
                    'fillable'      => true,
                ],
                'resource_type'=>
                [   
                    'type'          => 'input',
                    'isRequired'    => true,
                    'decrypt'       => false,
                    'validation'    => 'required',
                    'fillable'      => true,
                ],
                'ip_address'=>
                [   
                    'type'          => 'input',
                    'isRequired'    => true,
                    'decrypt'       => false,
                    'validation'    => 'required',
                    'fillable'      => true,
                ],
                'medicine_duration_unit' =>
                [   
                    'type'          => 'input',
                    'isRequired'    => true,
                    'validation'    => 'required',
                    'decrypt'       => false,
                    'fillable'      => true,
                ],
                'medicine_frequency' =>
                [   
                    'type'          => 'input',
                    'isRequired'    => false,
                    'validation'    => 'required',
                    'decrypt'       => false,
                    'fillable'      => true,
                ],
                'medicine_dose' =>
                [   
                    'type'          => 'input',
                    'isRequired'    => true,
                    'validation'    => 'required_without_all:medicine_dose2,medicine_dose3',
                    'decrypt'       => false,
                    'fillable'      => true,
                ],
                'medicine_dose2' =>
                [   
                    'type'          => 'input',
                    'isRequired'    => true,
                    'validation'    => 'required_without_all:medicine_dose,medicine_dose3',
                    'decrypt'       => false,
                    'fillable'      => true,
                ],
                'medicine_dose3' =>
                [   
                    'type'          => 'input',
                    'isRequired'    => true,
                    'validation'    => 'required_without_all:medicine_dose2,medicine_dose',
                    'decrypt'       => false,
                    'fillable'      => true,
                ],
                'medicine_dose_unit' =>
                [   
                    'type'          => 'input',
                    'isRequired'    => true,
                    'validation'    => 'required_with:medicine_frequency',
                    'decrypt'       => true,
                    'fillable'      => true,
                ],
                'medicine_start_date' =>
                [   
                    'type'          => 'date',
                    'isRequired'    => true,
                    'validation'    => 'required',
                    'decrypt'       => false,
                    'fillable'      => true,
                ],
                'medicine_end_date' =>
                [   
                    'type'          => 'date',
                    'isRequired'    => false,
                    'validation'    => '',
                    'decrypt'       => false,
                    'fillable'      => true,
                ],
                'medicine_meal_opt' =>
                [   
                    'type'          => 'checkbox',
                    'isRequired'    => false,
                    'validation'    => '',
                    'decrypt'       => false,
                    'fillable'      => true,
                ],
                'medicine_instractions' =>
                [   
                    'type'          => 'text',
                    'isRequired'    => false,
                    'validation'    => '',
                    'decrypt'       => false,
                    'fillable'      => true,
                ],
                'medicine_route' =>
                [   
                    'type'          => 'input',
                    'isRequired'    => false,
                    'validation'    => '',
                    'decrypt'       => false,
                    'fillable'      => true,
                ]
            ],
        ];
        $responseValidatorForm = $this->postValidatorForm($posConfig, $request);

        if (!$responseValidatorForm['status']) {
            return $responseValidatorForm['response'];
        }

        if($responseValidatorForm['status']){
            $medicationData                 = $responseValidatorForm['response']['fillable']['patient_medication_history'];
            $medicationData['pat_id']       = $requestData['pat_id'];
            $medicationData['visit_id']     = $requestData['visit_id'];
            $medicationData['medicine_id']  = $requestData['medicine_id'];

            $medicationData['medicine_dose']         = isset($medicationData['medicine_dose']) ? $medicationData['medicine_dose'] : 0;
            $medicationData['medicine_dose2']        = isset($medicationData['medicine_dose2']) ? $medicationData['medicine_dose2'] : 0;
            $medicationData['medicine_dose3']        = isset($medicationData['medicine_dose3']) ? $medicationData['medicine_dose3'] : 0;
            $medicationData['medicine_route']        = isset($medicationData['medicine_route']) && $medicationData['medicine_route'] != 'undefined' ? $medicationData['medicine_route'] : NULL;
            $medicationData['medicine_instractions'] = (isset($medicationData['medicine_instractions']) && $medicationData['medicine_instractions'] != 'undefined' && $medicationData['medicine_instractions'] != 'null') ? json_encode($medicationData['medicine_instractions']) : NULL;
            $medicationData['medicine_meal_opt']     =  isset($medicationData['medicine_meal_opt']) ? $this->utilityLibObj->arrayToStringVal($medicationData['medicine_meal_opt']) : NULL;
            $duration       = $medicationData['medicine_duration'];
            $durationUnit   = $medicationData['medicine_duration_unit'];
            $dateFormat     = Carbon::createFromFormat('Y-m-d', $medicationData['medicine_start_date']);
            if($durationUnit == Config::get('dataconstants.MEDICINE_DURATION_UNIT_WEEKS')){
                $medicationData['medicine_end_date'] = $dateFormat->addWeek($duration)->toDateString(); 
            } else if($durationUnit == Config::get('dataconstants.MEDICINE_DURATION_UNIT_MONTHS')){
                $medicationData['medicine_end_date'] = $dateFormat->addMonth($duration)->toDateString(); 
            }else{
                $medicationData['medicine_end_date'] = $dateFormat->addDays($duration)->toDateString();                    
            } 

            try{
                DB::beginTransaction();

                if(!empty($pmhId)){
                    $response = $this->medicationObj->updateMedicationData($medicationData, $pmhId);
                    $successMessage = trans('Visits::messages.medication_medicine_data_update_successfull');
                    $errorMessage = trans('Visits::messages.medication_medicine_data_update_fail');
                } else {
                    $response = $this->medicationObj->saveMedicationData($medicationData);
                    
                    if($response){
                        $userId = (in_array($request->user()->user_type, Config::get('constants.USER_TYPE_STAFF'))) ? $request->user()->created_by : $requestData['user_id'];
                        $activityData = [ 'pat_id' => $requestData['pat_id'], 'user_id' => $userId, 'activity_table' => 'patient_medication_history', 'visit_id' => $requestData['visit_id'] ];
                        $response = $this->patientActivitiesModelObj->insertActivity($activityData);
                    }

                    $successMessage = trans('Visits::messages.medication_medicine_data_insert_successfull');
                    $errorMessage = trans('Visits::messages.medication_medicine_data_insert_fail');
                }

                if($response){
                    DB::commit();
                    return $this->resultResponse(
                        Config::get('restresponsecode.SUCCESS'), 
                        [], 
                        [],
                        $successMessage, 
                        $this->http_codes['HTTP_OK']
                    );
                }else{
                    DB::rollback();

                    //user pat_consent_file unlink
                    if(!empty($pdfPath) && file_exists($pdfPath)){
                        unlink($pdfPath);
                    }
                    return $this->resultResponse(
                        Config::get('restresponsecode.ERROR'), 
                        [], 
                        [],
                        $errorMessage, 
                        $this->http_codes['HTTP_OK']
                    );
                }                       
            } catch (\Exception $ex) {
                //user pat_consent_file unlink
                
                if(!empty($pdfPath) && file_exists($pdfPath)){
                    unlink($pdfPath);
                }
                $eMessage = $this->exceptionLibObj->reFormAndLogException($ex,'MedicationController', 'saveMedicationData');
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

    /**
     * @DateOfCreation        14 July 2018
     * @ShortDescription      This function is responsible to delete patient Medication record
     * @return                Array of medicines and message
     */
    public function getPatientMedicineChart(Request $request)
    {
        $requestData    = $this->getRequestData($request);

        $requestData['resource_type']   = Config::get('constants.RESOURCE_TYPE_WEB');   
        $requestData['pat_id']          = $this->securityLibObj->decrypt($requestData['pat_id']);
        $requestData['visit_id']        = $this->securityLibObj->decrypt($requestData['visit_id']);

        try{
            DB::beginTransaction();

            $updateData =  ['is_deleted' => Config::get('constants.IS_DELETED_YES')];                
            $whereData  =  ['pmh_id' => $medicationId];
            $deletedRecord = $this->medicationObj->deletePatientMedicationData($updateData, $whereData);
            
            if($deletedRecord){
                DB::commit();
                return $this->resultResponse(
                    Config::get('restresponsecode.SUCCESS'), 
                    [], 
                    [],
                    trans('Visits::messages.patient_medicine_data_deleted_successfully'), 
                    $this->http_codes['HTTP_OK']
                );
            }else{
                DB::rollback();

                return $this->resultResponse(
                    Config::get('restresponsecode.ERROR'), 
                    [], 
                    [],
                    trans('Visits::messages.patient_medicine_data_deleted_fail'), 
                    $this->http_codes['HTTP_OK']
                );
            }                   
        } catch (\Exception $ex) {
            //user pat_consent_file unlink
            
            $eMessage = $this->exceptionLibObj->reFormAndLogException($ex,'MedicationController', 'deletePatientMedicationData');
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
     * @DateOfCreation        14 July 2018
     * @ShortDescription      This function is responsible to save patient Medication record
     * @return                Array of medicines and message
     */
    public function saveMedicationTemplate(Request $request)
    {
        $requestData    = $this->getRequestData($request);

        $requestData['resource_type']   = Config::get('constants.RESOURCE_TYPE_WEB');   
        $requestData['pat_id']          = $this->securityLibObj->decrypt($requestData['pat_id']);
        $requestData['visit_id']        = $this->securityLibObj->decrypt($requestData['visit_id']);
        $requestData['user_id']         = $request->user()->user_id;

        try{
            DB::beginTransaction();

            $templateRecord = $this->medicationObj->saveMedicationTemplate($requestData);
            
            if($templateRecord){
                DB::commit();
                return $this->resultResponse(
                    Config::get('restresponsecode.SUCCESS'), 
                    $templateRecord, 
                    [],
                    trans('Visits::messages.patient_medicine_template_save_successfully'), 
                    $this->http_codes['HTTP_OK']
                );
            }else{
                DB::rollback();

                return $this->resultResponse(
                    Config::get('restresponsecode.ERROR'), 
                    [], 
                    [],
                    trans('Visits::messages.patient_medicine_template_save_fail'), 
                    $this->http_codes['HTTP_OK']
                );
            }                   
        } catch (\Exception $ex) {
            //user pat_consent_file unlink
            
            $eMessage = $this->exceptionLibObj->reFormAndLogException($ex,'MedicationController', 'deletePatientMedicationData');
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
     * @DateOfCreation        14 July 2018
     * @ShortDescription      This function is responsible to get patient Medication record
     * @return                Array of medicines and message
     */
    public function getPatientMedicationTemplate(Request $request)
    {
        $requestData    = $this->getRequestData($request);

        $requestData['user_id']= $request->user()->user_id; 

        try{
            DB::beginTransaction();

            $templateRecord = $this->medicationObj->getPatientMedicationTemplate($requestData);
            
            if($templateRecord){
                DB::commit();
                return $this->resultResponse(
                    Config::get('restresponsecode.SUCCESS'), 
                    $templateRecord, 
                    [],
                    trans('Visits::messages.patient_medicine_template_fetch_successfully'), 
                    $this->http_codes['HTTP_OK']
                );
            }else{
                DB::rollback();

                return $this->resultResponse(
                    Config::get('restresponsecode.ERROR'), 
                    [], 
                    [],
                    trans('Visits::messages.patient_medicine_template_not_found'), 
                    $this->http_codes['HTTP_OK']
                );
            }                   
        } catch (\Exception $ex) {
            //user pat_consent_file unlink
            
            $eMessage = $this->exceptionLibObj->reFormAndLogException($ex,'MedicationController', 'getPatientMedicationTemplate');
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
     * @DateOfCreation        14 July 2018
     * @ShortDescription      This function is responsible to get patient Medication record
     * @return                Array of medicines and message
     */
    public function getMedicationTemplate(Request $request)
    {
        $requestData    = $this->getRequestData($request);

        $requestData['user_id']= $request->user()->user_id; 
        $requestData['pat_med_temp_id'] = $this->securityLibObj->decrypt($requestData['pat_med_temp_id']);
        $requestData['pat_id'] = $this->securityLibObj->decrypt($requestData['pat_id']);
        $requestData['visit_id'] = $this->securityLibObj->decrypt($requestData['visit_id']);
        try{
            DB::beginTransaction();

            $templateRecord = $this->medicationObj->getMedicationTemplate($requestData);
            if($templateRecord){
                DB::commit();
                return $this->resultResponse(
                    Config::get('restresponsecode.SUCCESS'), 
                    $templateRecord, 
                    [],
                    trans('Visits::messages.patient_medicine_template_fetch_successfully'), 
                    $this->http_codes['HTTP_OK']
                );
            }else{
                DB::rollback();

                return $this->resultResponse(
                    Config::get('restresponsecode.ERROR'), 
                    [], 
                    [],
                    trans('Visits::messages.patient_medicine_template_not_found'), 
                    $this->http_codes['HTTP_OK']
                );
            }                   
        } catch (\Exception $ex) {
            //user pat_consent_file unlink
            
            $eMessage = $this->exceptionLibObj->reFormAndLogException($ex,'MedicationController', 'getMedicationTemplate');
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
     * @DateOfCreation        20 July 2018
     * @ShortDescription      This function is responsible to get patient's current Medication record
     * @return                Array of medicines and message
     */
    public function patientCurrentMedications(Request $request){
        $requestData    = $this->getRequestData($request);

        $requestData['created_by'] = $request->user()->user_id;
        $requestData['user_type']  = $request->user()->user_type;
        $requestData['pat_id']     = $this->securityLibObj->decrypt($requestData['pat_id']);
        $medicationList            = $this->medicationObj->patientCurrentMedications($requestData);
        
        return $this->resultResponse(
                Config::get('restresponsecode.SUCCESS'), 
                $medicationList, 
                [],
                trans('Visits::messages.patient_medicine_data_fetched_successfully'),
                $this->http_codes['HTTP_OK']
            );
    }

    /**
     * @DateOfCreation        22 July 2018
     * @ShortDescription      This function is responsible to get patient's current Medication record
     * @return                Array of medicines and message
     */
    public function getMedicineData(Request $request){
        $requestData    = $this->getRequestData($request);

        $requestData['user_id']    = $request->user()->user_id;
        $requestData['user_type']  = $request->user()->user_type;
        $requestData['medicine_id']= $this->securityLibObj->decrypt($requestData['medicine_id']);

        $data = [];
        $medicineDetails = $this->medicationObj->getMedicineData($requestData);

        $data['medicine_data'] = isset($medicineDetails[0]) ? $medicineDetails[0]: $medicineDetails;
        $data['dose_unit'] = $this->medicationObj->getDoseUnit();

        return $this->resultResponse(
                Config::get('restresponsecode.SUCCESS'), 
                $data, 
                [],
                trans('Visits::messages.medicine_data_fetched_successfully'),
                $this->http_codes['HTTP_OK']
            );
    }

    /**
     * @DateOfCreation        20 Sept 2018
     * @ShortDescription      This function is responsible to get patient's current Medication record
     * @return                Array of medicines and message
     */
    public function searchMedicine(Request $request){
        $requestData    = $this->getRequestData($request);

        $requestData['user_id']      = $request->user()->user_id;
        $requestData['user_type']    = $request->user()->user_type;
        $requestData['medicine_name']= $requestData['medicine_name'];

        $data = [];
        $medicineDetails = $this->medicationObj->searchMedicineRecord($requestData);

        return $this->resultResponse(
                Config::get('restresponsecode.SUCCESS'), 
                $medicineDetails, 
                [],
                trans('Visits::messages.medicine_data_fetched_successfully'),
                $this->http_codes['HTTP_OK']
            );
    }
}
