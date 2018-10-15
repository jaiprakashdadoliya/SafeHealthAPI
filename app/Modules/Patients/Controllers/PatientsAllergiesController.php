<?php

namespace App\Modules\Patients\Controllers;

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
use App\Modules\Patients\Models\PatientsAllergies;
use App\Modules\Setup\Models\StaticDataConfig as StaticData;
use App\Traits\FxFormHandler;


/**
 * PatientsAllergiesController
 *
 * @package                Safe health
 * @subpackage             PatientsAllergiesController
 * @category               Controller
 * @DateOfCreation         03 August 2018
 * @ShortDescription       This controller to handle all the operation related to 
                           Allergies
 **/
class PatientsAllergiesController extends Controller
{

    use SessionTrait, RestApi, FxFormHandler;

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

        // Init MedicationHistory Model Object
        $this->patientsAllergiesObj = new PatientsAllergies();

        // Init exception library object
        $this->exceptionLibObj = new ExceptionLib();

        // Init exception library object
        $this->dateTimeLibObj = new DateTimeLib();  

        $this->utilityLibObj = new UtilityLib();
        // Init exception library object
        $this->staticDataObj = new StaticData();         
    }
    
    /**
     * @DateOfCreation        21 May 2018
     * @ShortDescription      This function is responsible to get the WorkEnvironment add
     * @return                Array of status and message
     */
    public function store(Request $request)
    {
        $requestDataOnly = $request->only('allergy_type','onset','onset_time','status');
        $tableName = $this->patientsAllergiesObj->getTableName();
        $primaryKey = $this->patientsAllergiesObj->getTablePrimaryIdColumn();
        $posConfig =
        [   $tableName =>
            [
                $primaryKey=>
                [   
                    'type'=>'input',
                    'decrypt'=>true,
                    'isRequired' =>false,
                    'fillable' => true,
                ],
                 'pat_id'=>
                [   
                    'type'=>'input',
                    'decrypt'=>true,
                    'isRequired' =>true,
                    'validation'=>'required',
                    'fillable' => true,
                ],
                'allergy_type'=>
                [   
                    'type'=>'input',
                    'isRequired' =>true,
                    'validation'=>'required',
                    'validationRulesMessege' => [
                    'medicine_name.required'   => trans('Patients::messages.allergies_validation_required'),
                    ],
                    'decrypt'  => true,
                    'fillable' => true,
                ],
                'onset'=>
                [   
                    'type'=>'input',
                    'isRequired' =>false,
                    'decrypt'  => false,
                    'fillable' => true,
                ],
                'onset_time'=>
                [   
                    'type'=>'input',
                    'isRequired' =>false,
                    'decrypt'  => false,
                    'fillable' => true,
                ],
                'status'=>
                [   
                    'type'=>'input',
                    'isRequired' =>true,
                    'validation'=>'required',
                    'validationRulesMessege' => [
                    'status.required' => trans('Patients::messages.allergies_validation_required')
                    ],
                    'decrypt'=>false,
                    'fillable' => true,
                ],
                'resource_type'=>
                [   
                    'type'=>'input',
                    'isRequired' =>true,
                    'decrypt'=>false,
                    'validation'=>'required',
                    'fillable' => true,
                ],
                'ip_address'=>
                [   
                    'type'=>'input',
                    'isRequired' =>true,
                    'decrypt'=>false,
                    'validation'=>'required',
                    'fillable' => true,
                ]
            ],
        ];
        $responseValidatorForm = $this->postValidatorForm($posConfig,$request);
        if (!$responseValidatorForm['status']) {
            return $responseValidatorForm['response'];
        }

        if($responseValidatorForm['status']){
            $fillableData = $responseValidatorForm['response']['fillable'][$tableName];
            try{
                if (isset($fillableData[$primaryKey]) && !empty($fillableData[$primaryKey])){
                    $whereData = [];
                    $whereData['pat_id']  = $fillableData['pat_id'];
                    $whereData[$primaryKey]  = $fillableData[$primaryKey];
                    $storePrimaryId = $this->patientsAllergiesObj->updateRequest($fillableData,$whereData);
                } else {
                    $storePrimaryId = $this->patientsAllergiesObj->addRequest($fillableData);
                }

                 if($storePrimaryId){
                        $storePrimaryIdEncrypted = $this->securityLibObj->encrypt($storePrimaryId);
                        return $this->resultResponse(
                            Config::get('restresponsecode.SUCCESS'), 
                            [$primaryKey => $storePrimaryIdEncrypted], 
                            [],
                            trans('Patients::messages.allergies_add_successfull'), 
                            $this->http_codes['HTTP_OK']
                        );
                    }else{
                        return $this->resultResponse(
                            Config::get('restresponsecode.ERROR'), 
                            [], 
                            [],
                            trans('Patients::messages.allergies_add_fail'), 
                            $this->http_codes['HTTP_OK']
                        );
                    }
            } catch (\Exception $ex) {
                $eMessage = $this->exceptionLibObj->reFormAndLogException($ex,'PatientsAllergiesController', 'store');
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
     * @DateOfCreation        03 August 2018
     * @ShortDescription      This function is responsible for get WorkEnvironment Data by patId and visitId
     * @param                 encrypted integer $patId   
     * @param                 encrypted integer $visitId   
     * @return                Array of status and message
     */
    public function getListData(Request $request)
    {
        $requestData = $this->getRequestData($request);
        $getListDataResponse = $this->patientsAllergiesObj->getListData($requestData);
        
        return $this->resultResponse(
            Config::get('restresponsecode.SUCCESS'), 
            $getListDataResponse, 
            [],
            trans('Patients::messages.allergies_get_data_successfull'),
            $this->http_codes['HTTP_OK']
        );
    }

    /**
    * @DateOfCreation        03 August 2018
    * @ShortDescription      This function is responsible for delete visit WorkEnvironment Data 
    * @param                 Array $wefId   
    * @return                Array of status and message
    */
    public function destroy(Request $request)
    {   
        $requestData = $this->getRequestData($request);
        $primaryKey = $this->patientsAllergiesObj->getTablePrimaryIdColumn();
        $primaryId = $this->securityLibObj->decrypt($requestData[$primaryKey]);
        $isPrimaryIdExist = $this->patientsAllergiesObj->isPrimaryIdExist($primaryId);
        if(!$isPrimaryIdExist){
            return $this->resultResponse(
                Config::get('restresponsecode.ERROR'), 
                [], 
                [$primaryKey => [trans('Patients::messages.allergies_data_not_found')]],
                trans('Patients::messages.allergies_data_not_found'), 
                $this->http_codes['HTTP_OK']
            ); 
        }

        $deleteDataResponse   = $this->patientsAllergiesObj->doDeleteRequest($primaryId);
        if($deleteDataResponse){
            return $this->resultResponse(
                Config::get('restresponsecode.SUCCESS'), 
                [], 
                [],
                trans('Patients::messages.allergies_data_deleted'),
                $this->http_codes['HTTP_OK']
            );
        }
        return $this->resultResponse(
            Config::get('restresponsecode.ERROR'), 
            [], 
            [],
            trans('Patients::messages.allergies_data_not_deleted'),
            $this->http_codes['HTTP_OK']
        );

    }

        /**
     * @DateOfCreation        2 July 2018
     * @ShortDescription      This function is responsible to get the Domestic factor field value
     * @return                Array of status and message
     */
    public function getAllergiesHistory(Request $request)
    {
        $requestData        = $this->getRequestData($request);
        $visitId            = $requestData['visit_id'];
        $patientId          = $requestData['pat_id'];
        $visitId            = $this->securityLibObj->decrypt($visitId);
        
        $allergiesHistoryRecord  = $this->patientsAllergiesObj->getAllergiesHistoryRecord($visitId);
        $allergiesHistoryWithFectorKey = !empty($allergiesHistoryRecord) && count($allergiesHistoryRecord)>0 ? $this->utilityLibObj->changeArrayKey(json_decode(json_encode($allergiesHistoryRecord),true), 'allergies_history_type_id'):[];
        
        $staticDataKey              = $this->staticDataObj->getStaticDataFunction(['getAllergiesHistoryData']);
        $staticDataArrWithCustomKey = $this->utilityLibObj->changeArrayKey($staticDataKey, 'id');
       
        $finalCheckupRecords = [];
        $tempData = [];
        if(!empty($staticDataArrWithCustomKey)){
            foreach ($staticDataArrWithCustomKey as $allergiesIdKey => $allergiesValue) {
                $temp = [];
                $encryptAllergiesTypeIdKey = $this->securityLibObj->encrypt($allergiesIdKey);
                $allergiesHistoryValuesData = ( array_key_exists($allergiesIdKey, $allergiesHistoryWithFectorKey) ? $allergiesHistoryWithFectorKey[$allergiesIdKey]['allergies_history_value'] : '');
                $temp = [  
                'showOnForm'=>true,
                'name' => 'allergies_history_type_'.$encryptAllergiesTypeIdKey,
                'title' => $allergiesValue['value'],
                'type' => $allergiesValue['input_type'],
                'value' => $allergiesValue['input_type'] === 'customcheckbox' ? [(string) $allergiesHistoryValuesData] :($allergiesValue['input_type'] === 'checkbox'  ?  explode(',', $allergiesHistoryValuesData): $allergiesHistoryValuesData),
                'cssClasses' => $allergiesValue['cssClasses'],
                'clearFix' => $allergiesValue['isClearfix'],

            ];
            $tempData['allergies_history_type_'.$encryptAllergiesTypeIdKey.'_data'] = isset($allergiesValue['input_type_option']) && !empty($allergiesValue['input_type_option']) ? $allergiesValue['input_type_option']:[] ;
                
            $finalCheckupRecords['form_'.$allergiesValue['type']]['fields'][] = $temp;
            $finalCheckupRecords['form_'.$allergiesValue['type']]['data'] = $tempData;
            $finalCheckupRecords['form_'.$allergiesValue['type']]['handlers'] = [];
            }
        }

        return $this->resultResponse(
                Config::get('restresponsecode.SUCCESS'), 
                $finalCheckupRecords, 
                [],
                trans('Patients::messages.allergies_history_get_data_successfull'),
                $this->http_codes['HTTP_OK']
            );
    }


        /**
     * @DateOfCreation        13 june 2018
     * @ShortDescription      This function is responsible for insert Patient Data 
     * @param                 Array $request   
     * @return                Array of status and message
     */
    public function addUpdateAllergiesHistory(Request $request)
    { 
        $requestData = $this->getRequestData($request);
        $requestData['user_id']         = $request->user()->user_id;
        $requestData['is_deleted']      = Config::get('constants.IS_DELETED_NO');  
        $requestData['pat_id']          = $this->securityLibObj->decrypt($requestData['pat_id']);
        $requestData['visit_id']        = $this->securityLibObj->decrypt($requestData['visit_id']);
        $visitId                        = $requestData['visit_id'];
        try{
            DB::beginTransaction();
            $allergiesHistoryRecord  = $this->patientsAllergiesObj->getAllergiesHistoryRecord($visitId);
        $allergiesHistoryWithFectorKey = !empty($allergiesHistoryRecord) && count($allergiesHistoryRecord)>0 ? $this->utilityLibObj->changeArrayKey(json_decode(json_encode($allergiesHistoryRecord),true), 'allergies_history_type_id'):[];
        
        $staticDataKey              = $this->staticDataObj->getStaticDataFunction(['getAllergiesHistoryData']);
        $staticDataArrWithCustomKey = $this->utilityLibObj->changeArrayKey($staticDataKey, 'id');

            $insertData = [];
            $insertDataPlace = [];
            if(!empty($staticDataArrWithCustomKey)){
                foreach ($staticDataArrWithCustomKey as $allergiesIdKey => $allergiesValue) {
                    $allergiesTypeIdEncrypted = $this->securityLibObj->encrypt($allergiesIdKey);
                    $temp = [];
                    $allergiesTypeValue = isset($requestData['allergies_history_type_'.$allergiesTypeIdEncrypted]) ? $requestData['allergies_history_type_'.$allergiesTypeIdEncrypted] : '';

                    $temp = [
                            'pat_id'    =>  $requestData['pat_id'],
                            'visit_id'  =>  $requestData['visit_id'],
                            'allergies_history_type_id'  =>  $allergiesIdKey,
                            'allergies_history_value'  =>  $allergiesTypeValue,
                            'user_id'                => $requestData['user_id'],
                            'ip_address'  =>  $requestData['ip_address'],
                            'resource_type'  =>  $requestData['resource_type'],
                    ];
                    $allergiesId = (isset($allergiesHistoryWithFectorKey[$allergiesIdKey]['allergies_history_id']) && 
                                !empty($allergiesHistoryWithFectorKey[$allergiesIdKey]['allergies_history_id']) )
                                ? $this->securityLibObj->decrypt($allergiesHistoryWithFectorKey[$allergiesIdKey]['allergies_history_id']) : '';
                    
                    if(array_key_exists($allergiesIdKey, $allergiesHistoryWithFectorKey) && !empty($allergiesId)){
                        $whereData =[];
                        $whereData = [
                            'pat_id'    =>  $requestData['pat_id'],
                            'visit_id'  =>  $requestData['visit_id'],
                            'allergies_history_id'  =>  $allergiesId,
                        ];
                        $updateData = $this->patientsAllergiesObj->updateAllergiesHistory($temp,$whereData);
                        if(!$updateData){
                            $dataDbStatus = true;
                            $dbCommitStatus = false;
                            break;
                        }else{
                            $dbCommitStatus = true;
                        }
                    }
                    if(!empty($allergiesTypeValue) && empty($allergiesId)){
                        $insertData[] = $temp;
                    }
                }
                
                if(isset($dataDbStatus) && $dataDbStatus){
                    DB::rollback();
                    return $this->resultResponse(
                        Config::get('restresponsecode.ERROR'), 
                        [], 
                        (isset($errorResponseArray) ? $errorResponseArray:[]),
                        (isset($errorResponseString) ? $errorResponseString :'').trans('Patients::messages.patient_allergies_history_add_fail'), 
                        $this->http_codes['HTTP_OK']
                    );
                }
                if(!empty($insertData)){
                    $addData = $this->patientsAllergiesObj->addAllergiesHistory($insertData);
                    if(!$addData){
                        DB::rollback();
                        return $this->resultResponse(
                            Config::get('restresponsecode.ERROR'), 
                            [], 
                            [],
                            trans('Patients::messages.patient_allergies_history_add_fail'), 
                            $this->http_codes['HTTP_OK']
                        );
                    }else{
                        DB::commit();
                        $dbCommitStatus = false;
                        return $this->resultResponse(
                            Config::get('restresponsecode.SUCCESS'), 
                            [], 
                            [],
                            trans('Patients::messages.patient_allergies_history_add_success'), 
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
                        trans('Patients::messages.patient_allergies_history_add_success'), 
                        $this->http_codes['HTTP_OK']
                    );
                }
            }else{
                 DB::rollback();
                        return $this->resultResponse(
                            Config::get('restresponsecode.SUCCESS'), 
                            [], 
                            [],
                            trans('Patients::messages.patient_allergies_history_add_fail'), 
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
