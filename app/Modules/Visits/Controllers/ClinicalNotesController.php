<?php

namespace App\Modules\Visits\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Auth;
use Session;
use Config;
use DB;
use App\Traits\SessionTrait;
use App\Traits\RestApi;
use App\Traits\FxFormHandler;
use App\Libraries\SecurityLib;
use App\Libraries\ExceptionLib;
use App\Libraries\UtilityLib;
use App\Modules\Visits\Models\ClinicalNotes;
use App\Modules\Visits\Models\Visits;
use App\Modules\Patients\Models\PatientsActivities;

/**
 * ClinicalNotesController
 *
 * @package                Safe Health
 * @subpackage             ClinicalNotesController
 * @category               Controller
 * @DateOfCreation         21 Aug 2018
 * @ShortDescription       This controller to handle all the operation related to 
                           Clinical Notes
 */
class ClinicalNotesController extends Controller
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

        // Init Utility Library object
        $this->utilityLibObj = new UtilityLib();

        // Init exception library object
        $this->exceptionLibObj = new ExceptionLib();       

        // Init Clinical notes Model Object
        $this->clinicalNotesModelObj = new ClinicalNotes();

        // Init visit Model Object
        $this->visitModelObj = new Visits();

        // Init Patients Activities Model Object
        $this->patientActivitiesModelObj = new PatientsActivities();
    }

    /**
     * @DateOfCreation        14 July 2018
     * @ShortDescription      This function is responsible to get medicine list
     * @return                Array of medicines and message
     */
    public function getClinicalNotesList(Request $request)
    {
        $requestData    = $this->getRequestData($request);
        $medicationList = $this->clinicalNotesModelObj->getClinicalNotesListData($requestData);
        
        return $this->resultResponse(
                Config::get('restresponsecode.SUCCESS'), 
                $medicationList, 
                [],
                trans('Visits::messages.clinical_notes_list_successfull'),
                $this->http_codes['HTTP_OK']
            );
    }

    /**
     * @DateOfCreation        14 July 2018
     * @ShortDescription      This function is responsible to save and update patient Medication record
     * @return                Array of medicines and message
     */
    public function addUpdateClinicalNotes(Request $request)
    {
        $requestData = $this->getRequestData($request);
        $requestData['user_id']         = $request->user()->user_id;
        $requestData['pat_id']          = $this->securityLibObj->decrypt($requestData['pat_id']);
        $requestData['visit_id']        = $this->securityLibObj->decrypt($requestData['visit_id']);
        $pmhId                          = isset($requestData['pmh_id']) ? $this->securityLibObj->decrypt($requestData['pmh_id']) : NULL;
                
        $posConfig = 
        [            
            'clinical_notes'=>
            [
                'clinical_notes'=>
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
            ],
        ];
        $responseValidatorForm = $this->postValidatorForm($posConfig, $request);

        if (!$responseValidatorForm['status']) {
            return $responseValidatorForm['response'];
        }

        if($responseValidatorForm['status']){
            $clinicalNotesData                 = $responseValidatorForm['response']['fillable']['clinical_notes'];
            $clinicalNotesData['pat_id']       = $requestData['pat_id'];
            $clinicalNotesData['visit_id']     = $requestData['visit_id'];

            $clinicalNotesData['clinical_notes'] = (isset($clinicalNotesData['clinical_notes']) && $clinicalNotesData['clinical_notes'] != 'undefined' && $clinicalNotesData['clinical_notes'] != 'null') ? json_encode($clinicalNotesData['clinical_notes']) : NULL;
                        
            try{
                DB::beginTransaction();

                // check if record exist for visit and patient
                $where = ['pat_id' => $clinicalNotesData['pat_id'], 'visit_id' => $clinicalNotesData['visit_id']];
                $getClinicalNotesId = $this->visitModelObj->checkIfRecordExist('clinical_notes', ['clinical_notes_id'], $where, 'get_record');
                $getClinicalNotesId = json_decode(json_encode($getClinicalNotesId), true);

                if(!empty($getClinicalNotesId)){
                    $response = $this->clinicalNotesModelObj->updateClinicalNotesData($clinicalNotesData, $getClinicalNotesId[0]['clinical_notes_id']);
                    $successMessage = trans('Visits::messages.clinical_notes_data_update_successfull');
                    $errorMessage = trans('Visits::messages.clinical_notes_data_update_fail');
                } else {
                    $response = $this->clinicalNotesModelObj->saveClinicalNotesData($clinicalNotesData);  

                    if($response){
                        $userId = (in_array($request->user()->user_type, Config::get('constants.USER_TYPE_STAFF'))) ? $request->user()->created_by : $requestData['user_id'];
                        $activityData = ['pat_id' => $requestData['pat_id'], 'user_id' => $userId, 'activity_table' => 'clinical_notes', 'visit_id' => $requestData['visit_id']];
                        $response = $this->patientActivitiesModelObj->insertActivity($activityData);
                    }

                    $successMessage = trans('Visits::messages.clinical_notes_data_insert_successfull');
                    $errorMessage = trans('Visits::messages.clinical_notes_data_insert_fail');
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
                $eMessage = $this->exceptionLibObj->reFormAndLogException($ex,'ClinicalNotesController', 'addUpdateClinicalNotes');
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
