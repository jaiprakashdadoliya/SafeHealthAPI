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
use App\Libraries\UtilityLib;
use App\Modules\Visits\Models\SystemicExamination as SystemicExamination;
use App\Modules\Setup\Models\StaticDataConfig as StaticData;
use App\Modules\Patients\Models\PatientsActivities;

/**
 * SystemicExaminationController
 *
 * @package                ILD India Registry
 * @subpackage             SystemicExaminationController
 * @category               Controller
 * @DateOfCreation         18 june 2018
 * @ShortDescription       This controller to handle all the operation related to 
                           setup SystemicExamination
 **/
class SystemicExaminationController extends Controller
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

         $this->utilityLibObj = new UtilityLib();

        // Init SystemicExamination Model Object
        $this->systemicExaminationObj = new SystemicExamination();

        // Init exception library object
        $this->exceptionLibObj = new ExceptionLib();

        // Init exception library object
        $this->staticDataObj = new StaticData();          
    }

    

    /**
     * @DateOfCreation        2 July 2018
     * @ShortDescription      This function is responsible to get the Domestic factor field value
     * @return                Array of status and message
     */
    public function getDetail(Request $request)
    {
        $requestData        = $this->getRequestData($request);
        $encryptedVisitId   = $requestData['visit_id'];
        $patientId          = $this->securityLibObj->decrypt($requestData['pat_id']);
        $visitId            = $this->securityLibObj->decrypt($encryptedVisitId);


        $patientSystemicExaminationTest  = $this->systemicExaminationObj->getSystemicExaminationRecord($visitId);
        $patientSystemicExaminationWithFectorKey = !empty($patientSystemicExaminationTest) && count($patientSystemicExaminationTest)>0 ? $this->utilityLibObj->changeArrayKey(json_decode(json_encode($patientSystemicExaminationTest),true), 'systemic_exam_type_id'):[];
        
        $staticDataKey              = $this->staticDataObj->getStaticDataFunction(['getSystemicExamination']);
        $staticDataArrWithCustomKey = $this->utilityLibObj->changeArrayKey($staticDataKey, 'id');
       
        $finalCheckupRecords = [];
        $tempData = [];
        $handlers = [];
        $handler = [];
        $handlerName = [];
        if(!empty($staticDataArrWithCustomKey)){
            foreach ($staticDataArrWithCustomKey as $systemicExaminationTypeIdKey => $systemicExaminationValue) {
                $temp = [];
                $encryptsystemicExaminationTypeIdKey = $this->securityLibObj->encrypt($systemicExaminationTypeIdKey);

                $symptomsTestValuesData = ( array_key_exists($systemicExaminationTypeIdKey, $patientSystemicExaminationWithFectorKey) ? $patientSystemicExaminationWithFectorKey[$systemicExaminationTypeIdKey]['systemic_exam_value'] : '');
                $fieldName = 'systemic_exam_type_'.$encryptsystemicExaminationTypeIdKey;
                $temp = [  
                    'showOnForm'=>true,
                    'name' => $fieldName,
                    'title' => $systemicExaminationValue['value'],
                    'type' => $systemicExaminationValue['input_type'],
                    'value' => $systemicExaminationValue['input_type'] === 'customcheckbox' ? [(string) $symptomsTestValuesData] : $symptomsTestValuesData,
                    'cssClasses' => $systemicExaminationValue['cssClasses'],
                    'clearFix' => $systemicExaminationValue['isClearfix'],
                    'fieldName' => (!empty($systemicExaminationValue['field_name'])) ? $systemicExaminationValue['field_name'] : '',
                    'showHideTrigger' => (!empty($systemicExaminationValue['show_hide_trigger'])) ? $systemicExaminationValue['show_hide_trigger'] : '',
                    'showHideTriggerId' => (!empty($systemicExaminationValue['show_hide_trigger_id'])) ? $systemicExaminationValue['show_hide_trigger_id'] : '',

                ];
                if(isset($systemicExaminationValue['handlers'])  && !empty($systemicExaminationValue['handlers']))
                        { 
                            $handlers[$fieldName.'_handle'] =  $systemicExaminationValue['handlers'];
                            $handler[$fieldName] =  $systemicExaminationValue['handlers'];
                            $handlerName[$fieldName] =  $systemicExaminationValue['field_name'];
                        }
                if($systemicExaminationValue['input_type'] === 'date'){
                    $temp['format'] =  isset($systemicExaminationValue['format']) ?  $systemicExaminationValue['format'] : Config::get('constants.REACT_WEB_DATE_FORMAT');
                }
                $tempData['systemic_exam_type_'.$encryptsystemicExaminationTypeIdKey.'_data'] = isset($systemicExaminationValue['input_type_option']) && !empty($systemicExaminationValue['input_type_option']) ? $systemicExaminationValue['input_type_option']:[] ;
                    
                $finalCheckupRecords['form_'.$systemicExaminationValue['type']]['fields'][] = $temp;
                $finalCheckupRecords['form_'.$systemicExaminationValue['type']]['data'] = $tempData;
                $finalCheckupRecords['form_'.$systemicExaminationValue['type']]['handlers'] = $handlers;
                $finalCheckupRecords['form_'.$systemicExaminationValue['type']]['handlerData']  = $handler;
                $finalCheckupRecords['form_'.$systemicExaminationValue['type']]['handlerName']  = $handlerName;
            }
        }
 
       
        return $this->resultResponse(
                Config::get('restresponsecode.SUCCESS'), 
                $finalCheckupRecords, 
                [],
                trans('Visits::messages.systemic_exam_get_data_successfull'),
                $this->http_codes['HTTP_OK']
            );
    }

    /**
     * @DateOfCreation        13 june 2018
     * @ShortDescription      This function is responsible for insert Patient Data 
     * @param                 Array $request   
     * @return                Array of status and message
     */
    public function addUpdateSystemicExamination(Request $request)
    { 
        $requestData = $this->getRequestData($request);
        $encryptedVisitId               = $requestData['visit_id'];
        $requestData['user_id']         = $request->user()->user_id;
        $requestData['is_deleted']      = Config::get('constants.IS_DELETED_NO');  
        $requestData['pat_id']          = $this->securityLibObj->decrypt($requestData['pat_id']);
        $requestData['visit_id']        = $this->securityLibObj->decrypt($requestData['visit_id']);
        $visitId                        = $requestData['visit_id'];
        $patientId                       = $requestData['pat_id'];


        try{
            DB::beginTransaction();
            $patientSystemicExaminationTest  = $this->systemicExaminationObj->getSystemicExaminationRecord($visitId);
            $patientSystemicExaminationRecordWithFectorKey = !empty($patientSystemicExaminationTest) && count($patientSystemicExaminationTest)>0 ? $this->utilityLibObj->changeArrayKey(json_decode(json_encode($patientSystemicExaminationTest),true), 'systemic_exam_type_id'):[];
        
            $staticDataKey              = $this->staticDataObj->getStaticDataFunction(['getSystemicExamination']);
            $staticDataArrWithCustomKey = $this->utilityLibObj->changeArrayKey($staticDataKey, 'id');

            $insertData = [];
            $insertDataPlace = [];
            $systemicExaminationId = '';
            if(!empty($staticDataArrWithCustomKey)){
                foreach ($staticDataArrWithCustomKey as $systemicExaminationTypeIdKey => $systemicExaminationValue) {
                    $systemicExaminationTypeIdEncrypted = $this->securityLibObj->encrypt($systemicExaminationTypeIdKey);
                    $temp = [];
                    $systemicExaminationTypeValue = isset($requestData['systemic_exam_type_'.$systemicExaminationTypeIdEncrypted]) ? $requestData['systemic_exam_type_'.$systemicExaminationTypeIdEncrypted] : '';
                    if($systemicExaminationValue['input_type'] === 'date' && !empty($systemicExaminationTypeValue)){
                        $dateResponse = $this->dateTimeLibObj->covertUserDateToServerType($systemicExaminationTypeValue,'dd/mm/YY','Y-m-d');
                        if ($dateResponse["code"] == '5000') {
                                $errorResponseString = $dateResponse["message"];
                                $errorResponseArray = [$systemicExaminationValue['value'] => [$dateResponse["message"]]];
                                $dataDbStatus = true;
                                break;
                        }
                        $systemicExaminationTypeValue = $dateResponse['result'];
                    }
                    $temp = [
                            'pat_id'    =>  $requestData['pat_id'],
                            'visit_id'  =>  $requestData['visit_id'],
                            'systemic_exam_type_id'  =>  $systemicExaminationTypeIdKey,
                            'systemic_exam_type'  =>  $systemicExaminationValue['type'],
                            'systemic_exam_value'  =>  $systemicExaminationTypeValue,
                            'ip_address'  =>  $requestData['ip_address'],
                            'resource_type'  =>  $requestData['resource_type'],
                    ];
                    $systemicExaminationId = (isset($patientSystemicExaminationRecordWithFectorKey[$systemicExaminationTypeIdKey]['systemic_exam_id']) && 
                                !empty($patientSystemicExaminationRecordWithFectorKey[$systemicExaminationTypeIdKey]['systemic_exam_id']) )
                                ? $this->securityLibObj->decrypt($patientSystemicExaminationRecordWithFectorKey[$systemicExaminationTypeIdKey]['systemic_exam_id']) : '';
                    
                    if(array_key_exists($systemicExaminationTypeIdKey, $patientSystemicExaminationRecordWithFectorKey) && !empty($systemicExaminationId)){
                        $whereData =[];
                        $whereData = [
                            'pat_id'    =>  $requestData['pat_id'],
                            'visit_id'  =>  $requestData['visit_id'],
                            'systemic_exam_id'  =>  $systemicExaminationId,
                        ];
                        $updateData = $this->systemicExaminationObj->updateSystemicExaminationTest($temp,$whereData);
                        if(!$updateData){
                            $dataDbStatus = true;
                            $dbCommitStatus = false;
                            break;
                        }else{
                            $dbCommitStatus = true;
                        }
                    }
                    if(!empty($systemicExaminationTypeValue) && empty($systemicExaminationId)){
                        $insertData[] = $temp;
                    }
                }

                if(isset($dataDbStatus) && $dataDbStatus){
                    DB::rollback();
                    return $this->resultResponse(
                        Config::get('restresponsecode.ERROR'), 
                        [], 
                        (isset($errorResponseArray) ? $errorResponseArray:[]),
                        (isset($errorResponseString) ? $errorResponseString :'').trans('Visits::messages.systemic_exam_add_fail'), 
                        $this->http_codes['HTTP_OK']
                    );
                }
                if(!empty($insertData)){
                    $addData = $this->systemicExaminationObj->addSystemicExaminationTest($insertData);
                    if(!$addData){
                        DB::rollback();
                        return $this->resultResponse(
                            Config::get('restresponsecode.ERROR'), 
                            [], 
                            [],
                            trans('Visits::messages.systemic_exam_add_fail'), 
                            $this->http_codes['HTTP_OK']
                        );
                    }else{
                        DB::commit();
                        $dbCommitStatus = false;
                        return $this->resultResponse(
                            Config::get('restresponsecode.SUCCESS'), 
                            [], 
                            [],
                            trans('Visits::messages.systemic_exam_add_success'), 
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
                        trans('Visits::messages.systemic_exam_add_success'), 
                        $this->http_codes['HTTP_OK']
                    );
                }
            }else{
                 DB::rollback();
                        return $this->resultResponse(
                            Config::get('restresponsecode.SUCCESS'), 
                            [], 
                            [],
                            trans('Visits::messages.systemic_exam_add_fail'), 
                            $this->http_codes['HTTP_OK']
                        );
            }
        } catch (\Exception $ex) {
            DB::rollback();
            $eMessage = $this->exceptionLibObj->reFormAndLogException($ex,'SystemicExaminationController', 'addUpdateSystemicExamination');
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
