<?php

namespace App\Modules\Doctors\Controllers;

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
use App\Modules\Setup\Models\StaticDataConfig as StaticData;
use App\Modules\Doctors\Models\ManageCalendar as ManageCalendar;
use App\Traits\FxFormHandler;

/**
 * ManageCalendarController
 *
 * @package                ILD India Registry
 * @subpackage             ManageCalendarController
 * @category               Controller
 * @DateOfCreation         18 june 2018
 * @ShortDescription       This controller to handle all the operation related to 
                           setup ManageCalendar
 **/
class ManageCalendarController extends Controller
{

    use SessionTrait, RestApi,FxFormHandler;

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

        // Init ManageCalendar Model Object
        $this->manageCalendarObj = new ManageCalendar();

        // Init exception library object
        $this->exceptionLibObj = new ExceptionLib();

        // Init exception library object
        $this->dateTimeLibObj = new DateTimeLib();  

         // Init exception library object
        $this->utilityLibObj = new UtilityLib();   

         $this->staticDataObj = new StaticData();
    }

    /**
     * @DateOfCreation        21 May 2018
     * @ShortDescription      This function is responsible to get the Symptoms add
     * @return                Array of status and message
     */
    public function getrecord(Request $request)
    {
        $userId = ($request->user()->user_type == Config::get('constants.USER_TYPE_DOCTOR')) ? $request->user()->user_id : $request->user()->created_by;
        $manageCalendarRecord  = $this->manageCalendarObj->getManageCalendarRecordByUserId($userId);
        $manageCalendarRecord = $this->utilityLibObj->changeObjectToArray($manageCalendarRecord);
        
        $staticDataKey              = $this->staticDataObj->getManageCalendarData();
        $staticDataArrWithCustomKey = $this->utilityLibObj->changeArrayKey($staticDataKey, 'id');
       
        $finalCheckupRecords = [];
        $tempData = [];
        if(!empty($staticDataArrWithCustomKey)){
            foreach ($staticDataArrWithCustomKey as $mcTypeIdKey => $mcValue) {
                $temp = [];
                $encryptMcTypeIdKey = $this->securityLibObj->encrypt($mcTypeIdKey);
                $valuesData = isset($manageCalendarRecord[$mcValue['input_name']]) ? $manageCalendarRecord[$mcValue['input_name']] : '';
                $temp = [  
                'showOnForm'=>true,
                'name' => $mcValue['input_name'],
                'title' => $mcValue['value'],
                'type' => $mcValue['input_type'],
                'value' => $mcValue['input_type'] === 'customcheckbox' ? [(string) $valuesData] : $valuesData,
                'cssClasses' => $mcValue['cssClasses'],
                'clearFix' => $mcValue['isClearfix'],


            ];
            if($mcValue['input_type'] === 'date'){
                $temp['format'] =  isset($mcValue['format']) ?  $mcValue['format'] : Config::get('constants.REACT_WEB_DATE_FORMAT');
            }
            if(isset($mcValue['validations_required']) && $mcValue['validations_required']){
                $temp['validations'] = [['isRequired'=>true,'msg'=>'This field is required.']];
            }
            $tempData[$mcValue['input_name'].'_data'] = isset($mcValue['input_type_option']) && !empty($mcValue['input_type_option']) ? $this->getOption($mcValue['input_type'],$mcValue['input_type_option']):[] ;
                
            $finalCheckupRecords['form']['fields'][] = $temp;
            $finalCheckupRecords['form']['data'] = $tempData;
            $finalCheckupRecords['form']['handlers'] = [];
            }
        }

        return $this->resultResponse(
                Config::get('restresponsecode.SUCCESS'), 
                $finalCheckupRecords, 
                [],
                trans('Visits::messages.manage_calendar_get_data_successfull'),
                $this->http_codes['HTTP_OK']
            );
    }

    /**
     * @DateOfCreation        13 june 2018
     * @ShortDescription      This function is responsible for get option lable changes
     * @param                 Array $request   
     * @return                Array of status and message
     */    
    public function getOption($inputType = 'text',$inputTypeOption ='')
    {
        $returnResponse = [];
        if(empty($inputTypeOption)){
            return $returnResponse;
        }
        $requestData = $this->staticDataObj->getStaticDataFunction([$inputTypeOption]);
        if(empty($requestData)){
            return $requestData;
        }
        switch($inputType){
            case 'customcheckbox':
            $returnResponse = array_map(function($tag) {
            return array(
                'value' => (string) $tag['id'],
                'label' => $tag['value']
            );
            }, $requestData);
            break;
            case 'select':
            $returnResponse = array_map(function($tag) {
            return array(
                'value' => $tag['id'],
                'label' => $tag['value']
            );
            }, $requestData);
            break;
        }
            
        return $returnResponse;
    }

     /**
     * @DateOfCreation        21 May 2018
     * @ShortDescription      This function is responsible to get the WorkEnvironment add
     * @return                Array of status and message
     */
    public function store(Request $request)
    {
        $userId = ($request->user()->user_type == Config::get('constants.USER_TYPE_DOCTOR')) ? $request->user()->user_id : $request->user()->created_by;
        $tableName = $this->manageCalendarObj->getTableName();
        $primaryKey = $this->manageCalendarObj->getTablePrimaryIdColumn();
        $posConfig =
        [   $tableName =>
            [
                'mcs_slot_duration'=>
                [   
                    'type'=>'text',
                    'isRequired' =>true,
                    'validation'=>'required',
                    'validationRulesMessege' => [
                    'mcs_slot_duration.required'   => trans('Doctors::messages.manage_calendar_slot_duration_Required'),
                    ],
                    'decrypt'=>false,
                    'fillable' => true,
                ],'mcs_start_time'=>
                [   
                    'type'=>'text',
                    'isRequired' =>true,
                    'validation'=>'required',
                    'validationRulesMessege' => [
                    'mcs_start_time.required'   => trans('Doctors::messages.manage_calendar_slot_start_time_Required'),
                    ],
                    'decrypt'=>false,
                    'fillable' => true,
                ],'mcs_end_time'=>
                [   
                    'type'=>'text',
                    'isRequired' =>true,
                    'validation'=>'required|gt:mcs_start_time',
                    'validationRulesMessege' => [
                    'mcs_end_time.required'   => trans('Doctors::messages.manage_calendar_slot_end_time_Required'),
                    'mcs_end_time.gt' =>trans('Doctors::messages.manage_calendar_slot_end_time_greater_then'),
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
            $fillableData['user_id'] = $userId;
            $manageCalendarRecord = $this->manageCalendarObj->getManageCalendarRecordByUserId($userId);

            try{
                if (!empty($manageCalendarRecord)){
                    $whereData = [];
                    $whereData[$primaryKey] = $manageCalendarRecord->$primaryKey;
                    $whereData['user_id']  = $fillableData['user_id'];
                    $storePrimaryId = $this->manageCalendarObj->updateRequest($fillableData,$whereData);
                } else {
                    $storePrimaryId = $this->manageCalendarObj->addRequest($fillableData);
                }

                 if($storePrimaryId){
                        $storePrimaryIdEncrypted = $this->securityLibObj->encrypt($storePrimaryId);
                        return $this->resultResponse(
                            Config::get('restresponsecode.SUCCESS'), 
                            [$primaryKey => $storePrimaryIdEncrypted], 
                            [],
                            trans('Doctors::messages.manage_calendar_add_successful'), 
                            $this->http_codes['HTTP_OK']
                        );
                    }else{
                        return $this->resultResponse(
                            Config::get('restresponsecode.ERROR'), 
                            [], 
                            [],
                            trans('Visits::messages.manage_calendar_add_fail'), 
                            $this->http_codes['HTTP_OK']
                        );
                    }
            } catch (\Exception $ex) {
                $eMessage = $this->exceptionLibObj->reFormAndLogException($ex,'ManageCalendarController', 'store');
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

}
