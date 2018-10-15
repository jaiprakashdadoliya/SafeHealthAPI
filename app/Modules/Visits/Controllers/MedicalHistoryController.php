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
use App\Modules\Visits\Models\MedicalHistory as MedicalHistory;
use App\Modules\Setup\Models\StaticDataConfig as StaticData;

/**
 * MedicalHistoryController
 *
 * @package                ILD India Registry
 * @subpackage             MedicalHistoryController
 * @category               Controller
 * @DateOfCreation         18 june 2018
 * @ShortDescription       This controller to handle all the operation related to 
                           setup Medical History
 **/
class MedicalHistoryController extends Controller
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
        $this->medicalHistoryObj = new MedicalHistory();

        // Init General staticData Model Object
        $this->staticDataObj = new StaticData();

        // Init exception library object
        $this->exceptionLibObj = new ExceptionLib();

        // Init exception library object
        $this->dateTimeLibObj = new DateTimeLib();  

        $this->utilityLibObj = new UtilityLib();        
    }

    /**
     * @DateOfCreation        26 June 2018
     * @ShortDescription      This function is responsible to get Medical History
     * @return                Array of status and message
     */
    public function getMedicalHistoryByVisitID(Request $request)
    {
        $requestData        = $this->getRequestData($request);
        $visitId            = $requestData['visit_id'];
        $patientId          = $requestData['patient_id'];
        $resourceType       = $requestData['resource_type'];
        $loggedUserId       = $requestData['user_id'];
        $loggedUserType     = $requestData['user_type'];
        $requestIpAddress   = $requestData['ip_address'];
        
        $visitId                    = $this->securityLibObj->decrypt($visitId);
        $patientMedicalHistoryData  = $this->medicalHistoryObj->getPatientMedicalHistory($visitId);

        $medicalHistoryRecord = [];
        $medicalHistoryWithDiseaseKey = [];
        if(!empty($patientMedicalHistoryData))
        {
            foreach ($patientMedicalHistoryData as $medicalHistory) 
            {
                $diseaseID = $this->securityLibObj->encrypt($medicalHistory->disease_id);
                $medicalHistoryRecord[] = ['disease_id' => $diseaseID,
                                         'is_happend_'.$diseaseID => !is_null($medicalHistory->is_happend) ? [(string)$medicalHistory->is_happend] : [],
                                         'disease_name' => $medicalHistory->disease_name, 
                                         'key_name'     => ['is_happend_'.$diseaseID]
                                        ];
            
            }
        }

        return $this->resultResponse(
                Config::get('restresponsecode.SUCCESS'), 
                $medicalHistoryRecord, 
                [],
                trans('Visits::messages.medical_history_get_data_successfull'),
                $this->http_codes['HTTP_OK']
            );
    }

    /**
     * @DateOfCreation        21 May 2018
     * @ShortDescription      This function is responsible to get the Symptoms add
     * @return                Array of status and message
     */
    public function addUpdateMedicalHistory(Request $request)
    {
        $requestData = $this->getRequestData($request);
        $encryptedVisitId               = $requestData['visit_id'];
        $requestData['user_id']         = $request->user()->user_id;
        $requestData['resource_type']   = Config::get('constants.RESOURCE_TYPE_WEB');   
        $requestData['is_deleted']      = Config::get('constants.IS_DELETED_NO');  
        $requestData['pat_id']          = $this->securityLibObj->decrypt($requestData['pat_id']);
        $requestData['visit_id']        = $this->securityLibObj->decrypt($encryptedVisitId);

        try{
            $getDiseaseOfMedicalHistory = $this->medicalHistoryObj->getMedicalHistoryDisease();
            $patientMedicalHistoryData  = $this->medicalHistoryObj->getPatientMedicalHistory($requestData['visit_id']);

            $patientMedicalHistoryWithKey = $this->utilityLibObj->changeArrayKey(json_decode(json_encode($patientMedicalHistoryData), true) , 'disease_id');
            
            $dataStatus = false;
            $responseSuccessMessage = trans('Visits::messages.medical_history_update_successfull');
            if(count($getDiseaseOfMedicalHistory) > 0) {
                foreach ($getDiseaseOfMedicalHistory as $diseaseData) {

                    $isHappenedVal = (count($requestData['is_happend_'.$diseaseData->encryptedDiseaseId]) > 0) ? $requestData['is_happend_'.$diseaseData->encryptedDiseaseId][0] : NULL;

                    $requestData['pmh_disease_id']  = $diseaseData->disease_id;
                    $requestData['is_happend']      = $isHappenedVal;
                    
                    $dataArr =  [
                                    'pmh_disease_id' => $diseaseData->disease_id,
                                    'is_happend'     => $isHappenedVal ? $isHappenedVal : NULL,
                                    'resource_type'  => $requestData['resource_type'],
                                    'pat_id'         => $requestData['pat_id'],
                                    'visit_id'       => $requestData['visit_id'],
                                    'ip_address'     => $requestData['ip_address'],
                                ];

                    if(array_key_exists($diseaseData->disease_id, $patientMedicalHistoryWithKey) && !empty($patientMedicalHistoryWithKey[$diseaseData->disease_id]['pmh_id'])){
                        $this->medicalHistoryObj->updateMedicalHistoryRecord($dataArr);

                        $responseSuccessMessage = trans('Visits::messages.medical_history_update_successfull');
                        $responseFailedMessage  = trans('Visits::messages.medical_history_update_fail');
                    } else if(array_key_exists($diseaseData->disease_id, $patientMedicalHistoryWithKey) && empty($patientMedicalHistoryWithKey[$diseaseData->disease_id]['pmh_id']) && !empty($isHappenedVal)) {
                        $this->medicalHistoryObj->addMedicalHistoryRecord($dataArr);
                        
                        $responseSuccessMessage = trans('Visits::messages.medical_history_update_successfull');
                        $responseFailedMessage  = trans('Visits::messages.medical_history_update_fail');
                    }

                    $dataStatus = true;
                }
            }

            if($dataStatus ){
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
            $eMessage = $this->exceptionLibObj->reFormAndLogException($ex,'MedicalHistoryController', 'addUpdateMedicalHistory');
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
