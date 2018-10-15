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
use App\Libraries\DateTimeLib;
use App\Modules\Visits\Models\VaccinationHistory;

/**
 * VaccinationHistoryController
 *
 * @package                Safe Health
 * @subpackage             VaccinationHistoryController
 * @category               Controller
 * @DateOfCreation         21 Sept 2018
 * @ShortDescription       This controller to handle all the operation related to Vaccination History
 */
class VaccinationHistoryController extends Controller
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

        // Init dateTime library object
        $this->dateTimeLibObj = new DateTimeLib();   

        // Init Vaccination History Model Object
        $this->vaccinationHistoryModelObj = new VaccinationHistory();
    }

    /**
     * @DateOfCreation        21 Sept 2018
     * @ShortDescription      This function is responsible to get Vaccination History
     * @return                Array of medicines and message
     */
    public function getVaccinationHistory(Request $request)
    {
        $requestData    = $this->getRequestData($request);
        $medicationList = $this->vaccinationHistoryModelObj->getVaccinationHistoryData($requestData);
        
        return $this->resultResponse(
                Config::get('restresponsecode.SUCCESS'), 
                $medicationList, 
                [],
                trans('Visits::messages.vaccination_history_list_successfull'),
                $this->http_codes['HTTP_OK']
            );
    }

    /**
     * @DateOfCreation        21 Sept 2018
     * @ShortDescription      This function is responsible to save and update patient Vaccination History
     * @return                Array of medicines and message
     */
    public function addUpdateVaccinationHistory(Request $request)
    {
        $requestData = $this->getRequestData($request);
        
        $requestData['user_id']         = $request->user()->user_id;
        $requestData['pat_id']          = $this->securityLibObj->decrypt($requestData['pat_id']);
        $requestData['visit_id']        = $this->securityLibObj->decrypt($requestData['visit_id']);
        $vaccinationId                  = isset($requestData['vaccination_id']) && !empty($requestData['vaccination_id']) ? $this->securityLibObj->decrypt($requestData['vaccination_id']) : NULL;

        $posConfig = 
        [            
            'patient_vaccination_history'=>
            [
                'vaccine_name'=>
                [   
                    'type'          => 'input',
                    'isRequired'    => true,
                    'validation'    => 'required',
                    'decrypt'       => false,
                    'fillable'      => true,
                ],
                'vaccine_date'=>
                [   
                    'type'          => 'input',
                    'isRequired'    => true,
                    'validation'    => 'required',
                    'decrypt'       => false,
                    'fillable'      => true,
                ],
                'pat_id'=>
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
            $vaccinationData                    = $responseValidatorForm['response']['fillable']['patient_vaccination_history'];
            $vaccinationData['pat_id']          = $requestData['pat_id'];
            $vaccinationData['visit_id']        = $requestData['visit_id'];
            $vaccinationData['vaccine_date']    = !empty($requestData['vaccine_date']) ? $this->dateTimeLibObj->covertUserDateToServerType($requestData['vaccine_date'],'dd/mm/YY','Y-m-d')['result'] : $requestData['vaccine_date'];
            $vaccinationId = (isset($requestData['vaccination_id']) && !empty($requestData['vaccination_id'])) ? $this->securityLibObj->decrypt($requestData['vaccination_id']) : NULL;
                                    
            try{
                DB::beginTransaction();

                if(!empty($vaccinationId)){
                    $response = $this->vaccinationHistoryModelObj->updateClinicalNotesData($vaccinationData, $vaccinationId);
                    $successMessage = trans('Visits::messages.vaccination_history_data_update_successfull');
                    $errorMessage = trans('Visits::messages.vaccination_history_data_update_fail');
                } else {
                    
                    $response = $this->vaccinationHistoryModelObj->saveVaccinationHistoryData($vaccinationData);  

                    $successMessage = trans('Visits::messages.vaccination_history_data_insert_successfull');
                    $errorMessage = trans('Visits::messages.vaccination_history_data_insert_fail');
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
                $eMessage = $this->exceptionLibObj->reFormAndLogException($ex,'VaccinationHistoryController', 'addUpdateVaccinationHistory');
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
     * @DateOfCreation        21 Sept 2018
     * @ShortDescription      This function is responsible to delete patient Vaccination History
     * @return                Array of medicines and message
     */
    public function deleteVaccinationHistory(Request $request){
        $requestData    = $this->getRequestData($request);

        $vaccinationId = $this->securityLibObj->decrypt($requestData['vaccination_id']);

        $response = $this->vaccinationHistoryModelObj->updateClinicalNotesData(['is_deleted' => Config::get('constants.IS_DELETED_YES')], $vaccinationId);
        $successMessage = trans('Visits::messages.vaccination_history_data_delete_successfull');
        $errorMessage = trans('Visits::messages.vaccination_history_data_delete_fail');

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
    }
}
