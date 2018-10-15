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
use App\Modules\Visits\Models\GeneralCheckup as Checkup;
use App\Modules\Setup\Models\StaticDataConfig as StaticData;

/**
 * GeneralCheckupController
 *
 * @package                ILD India Registry
 * @subpackage             GeneralCheckupController
 * @category               Controller
 * @DateOfCreation         18 june 2018
 * @ShortDescription       This controller to handle all the operation related to 
                           setup General Checkup
 **/
class GeneralCheckupController extends Controller
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

        // Init General Checkup Model Object
        $this->generalCheckupObj = new Checkup();

        // Init General staticData Model Object
        $this->staticDataObj = new StaticData();

        // Init exception library object
        $this->exceptionLibObj = new ExceptionLib();

        // Init exception library object
        $this->dateTimeLibObj = new DateTimeLib();  

        $this->utilityLibObj = new UtilityLib();        
    }

    /**
     * @DateOfCreation        21 May 2018
     * @ShortDescription      This function is responsible to get the Symptoms add
     * @return                Array of status and message
     */
    public function getGeneralCheckupByVisitID($visitId, $patientId)
    {
        $visitId                    = $this->securityLibObj->decrypt($visitId);
        $patientGeneralCheckupData  = $this->generalCheckupObj->getPatientGeneralCheckupRecord($visitId);
        
        $staticDataKey              = $this->staticDataObj->getStaticDataConfigList()['checkup_factor'];
        $staticDataArrWithCustomKey = $this->utilityLibObj->changeArrayKey($staticDataKey, 'id');
        
        $checkupRecord = [];
        $checkupRecordWithFectorKey = [];
        if(!empty($patientGeneralCheckupData)){
            foreach ($patientGeneralCheckupData as $generalCheckupData) {
                $checkupRecordWithCustomKey = [];
                $fectorId = $this->securityLibObj->encrypt($generalCheckupData->checkup_factor_id);
                $checkupRecordWithCustomKey['is_happend_'.$fectorId]        = !is_null($generalCheckupData->is_happend) ? [(string)$generalCheckupData->is_happend] : [];
                $checkupRecordWithCustomKey['duration_'.$fectorId]          = !is_null($generalCheckupData->duration) ? $generalCheckupData->duration :'';
                $checkupRecordWithCustomKey['duration_unit_'.$fectorId]     = !is_null($generalCheckupData->duration_unit)? $generalCheckupData->duration_unit :'';
                $checkupRecordWithCustomKey['remark_'.$fectorId]            = !is_null($generalCheckupData->remark)?$generalCheckupData->remark:'';
                $checkupRecordWithCustomKey['pat_checkup_id_'.$fectorId]    = !is_null($generalCheckupData->pat_checkup_id)?$generalCheckupData->pat_checkup_id:'';

                $checkupRecordWithFectorKey[$fectorId] = $checkupRecordWithCustomKey;
            }
        }
       
        $finalCheckupRecords = [];
        if(!empty($staticDataArrWithCustomKey)){
            foreach ($staticDataArrWithCustomKey as $fectorKey => $fectorValue) {
                $encryptFectorKey = $this->securityLibObj->encrypt($fectorKey);
                if(array_key_exists($encryptFectorKey, $checkupRecordWithFectorKey)){
                    $finalCheckupRecords[] = array_merge(
                                                    ['id'           => $this->securityLibObj->encrypt( $fectorValue['id'] ), 
                                                    'value'         => $fectorValue['value'], 
                                                    'onlycheckbox'  => $fectorValue['onlycheckbox'],
                                                    'key_name'      => ['is_happend_'.$encryptFectorKey,
                                                                        'duration_'.$encryptFectorKey ,
                                                                        'remark_'.$encryptFectorKey,
                                                                        'duration_unit_'.$encryptFectorKey
                                                                    ]
                                                    ], 
                                                    $checkupRecordWithFectorKey[$encryptFectorKey]
                                                );
                } else {                    
                    $finalCheckupRecords[] = [  
                                                'id'                                => $this->securityLibObj->encrypt( $fectorValue['id'] ),
                                                'value'                             => $fectorValue['value'],
                                                'onlycheckbox'                      => $fectorValue['onlycheckbox'],
                                                'is_happend_'.$encryptFectorKey     => [],
                                                'duration_'.$encryptFectorKey       => '',
                                                'duration_unit_'.$encryptFectorKey  => '',
                                                'remark_'.$encryptFectorKey         => '',
                                                'pat_checkup_id_'.$encryptFectorKey => '',
                                                'key_name'                          => ['is_happend_'.$encryptFectorKey,
                                                                                        'duration_'.$encryptFectorKey ,
                                                                                        'remark_'.$encryptFectorKey,
                                                                                        'duration_unit_'.$encryptFectorKey
                                                                                        ]
                                            ];
                }
            }
        }

        return $this->resultResponse(
                Config::get('restresponsecode.SUCCESS'), 
                $finalCheckupRecords, 
                [],
                trans('Visits::messages.general_checkup_get_data_successfull'),
                $this->http_codes['HTTP_OK']
            );
    }

    /**
     * @DateOfCreation        21 May 2018
     * @ShortDescription      This function is responsible to get the Symptoms add
     * @return                Array of status and message
     */
    public function addGeneralCheckup(Request $request)
    {
        $requestData = $this->getRequestData($request);
        $requestData['user_id']         = $request->user()->user_id;
        $requestData['resource_type']   = Config::get('constants.RESOURCE_TYPE_WEB');   
        $requestData['is_deleted']      = Config::get('constants.IS_DELETED_NO');  
        $requestData['pat_id']          = $this->securityLibObj->decrypt($requestData['pat_id']);
        $requestData['visit_id']        = $this->securityLibObj->decrypt($requestData['visit_id']);

        $staticDataKey = $this->staticDataObj->getStaticDataConfigList()['checkup_factor'];
        
        // Check if record already exist
        $patientGeneralCheckupData          = $this->generalCheckupObj->getPatientGeneralCheckupRecord($requestData['visit_id']);
        $patientGeneralCheckupDataWithKey   = $this->utilityLibObj->changeArrayKey(json_decode(json_encode($patientGeneralCheckupData), true) , 'checkup_factor_id');
        try{
            $responseSuccessMessage = trans('Visits::messages.general_checkup_add_successfull');

            foreach ($staticDataKey as $staticData) { 
                $encryptedStaticData = $this->securityLibObj->encrypt($staticData['id']);
                $isHappenedVal = (count($requestData['is_happend_'.$encryptedStaticData]) > 0) ? $requestData['is_happend_'.$encryptedStaticData][0] : NULL;
                
                $updateData = ['checkup_factor_id'    => $staticData['id'], 
                                'is_happend'          => $isHappenedVal,
                                'duration'            => $requestData['duration_'.$encryptedStaticData] ?: NULL,
                                'duration_unit'       => $requestData['duration_unit_'.$encryptedStaticData] ?: NULL,
                                'remark'              => $requestData['remark_'.$encryptedStaticData] ?: NULL,
                                'pat_id'              => $requestData['pat_id'],
                                'visit_id'            => $requestData['visit_id']
                            ];
                
                $error = false;
                if(!empty($updateData['duration'])){
                    $rules = ['duration' => 'numeric|regex:/[0-9]/'];
                    $validationMessageData = ['duration.numeric' => trans('Visits::messages.general_checkup_validation_duration')];                    
                    
                    // Added Validation in loop because key is different for all durations
                    $errors = '';
                    $validator = Validator::make($updateData, $rules, $validationMessageData);        
                    if($validator->fails()){
                        $error = true;
                        $errors = $validator->errors();
                    }
                }

                if($error){
                     return $this->resultResponse(
                        Config::get('restresponsecode.ERROR'), 
                        [], 
                        $errors,
                        trans('Visits::messages.general_checkup_validation_failed'), 
                        $this->http_codes['HTTP_OK']
                    ); 
                }
                // END Validation

                if(array_key_exists($staticData['id'], $patientGeneralCheckupDataWithKey)){
                    $createdCheckupId = $this->generalCheckupObj->updateGeneralCheckup($updateData); 

                    $responseSuccessMessage = trans('Visits::messages.general_checkup_update_successfull');
                    $responseFailedMessage  = trans('Visits::messages.general_checkup_update_fail');
                } else if(!array_key_exists($staticData['id'], $patientGeneralCheckupDataWithKey) && (!empty($updateData['is_happend']) || !empty($updateData['duration']) || !empty($updateData['duration_unit']) || !empty($updateData['remark']))) {
                    $createdCheckupId = $this->generalCheckupObj->addGeneralCheckup($updateData);
                    
                    $responseSuccessMessage = trans('Visits::messages.general_checkup_add_successfull');
                    $responseFailedMessage  = trans('Visits::messages.general_checkup_add_fail');
                }

                // Update HERE
                $createdCheckupId = true;
            }

            if($createdCheckupId){
                return $this->resultResponse(
                    Config::get('restresponsecode.SUCCESS'), 
                    [], 
                    [],
                    $responseSuccessMessage, 
                    $this->http_codes['HTTP_OK']
                );                           
            }else{
                DB::rollback();
                return $this->resultResponse(
                    Config::get('restresponsecode.ERROR'), 
                    [], 
                    [],
                    $responseFailedMessage, 
                    $this->http_codes['HTTP_OK']
                );
            }           
        }catch (\Exception $ex) {
            $eMessage = $this->exceptionLibObj->reFormAndLogException($ex,'SymptomsController', 'addGeneralCheckup');
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
