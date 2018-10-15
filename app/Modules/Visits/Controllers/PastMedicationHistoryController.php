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
use App\Modules\Visits\Models\PastMedicationHistory;
use App\Traits\FxFormHandler;


/**
 * MedicationHistoryController
 *
 * @package                ILD India Registry
 * @subpackage             MedicationHistoryController
 * @category               Controller
 * @DateOfCreation         18 june 2018
 * @ShortDescription       This controller to handle all the operation related to 
                           setup WorkEnvironment
 **/
class PastMedicationHistoryController extends Controller
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
        $this->pastMedicationHistoryObj = new PastMedicationHistory();

        // Init exception library object
        $this->exceptionLibObj = new ExceptionLib();

        // Init exception library object
        $this->dateTimeLibObj = new DateTimeLib();  

        $this->utilityLibObj = new UtilityLib();        
    }
    
    /**
     * @DateOfCreation        21 May 2018
     * @ShortDescription      This function is responsible to get the WorkEnvironment add
     * @return                Array of status and message
     */
    public function store(Request $request)
    {
        $requestDataOnly = $request->only('disease_id','disease_onset','disease_status','disease_duration');
        $tableName = $this->pastMedicationHistoryObj->getTableName();
        $primaryKey = $this->pastMedicationHistoryObj->getTablePrimaryIdColumn();
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
                'visit_id'=>
                [   
                    'type'=>'input',
                    'decrypt'=>true,
                    'isRequired' =>true,
                    'validation'=>'required',
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
                'disease_id'=>
                [   
                    'type'=>'input',
                    'isRequired' =>true,
                    'validation'=>'required',
                    'validationRulesMessege' => [
                    'medicine_name.required'   => trans('Visits::messages.past_medication_history_validation_required')
                    ],
                    'decrypt'=>true,
                    'fillable' => true,
                ],
                'disease_onset'=>
                [   
                    'type'=>'input',
                    'isRequired' =>true,
                    'validation'=>'required|numeric|max:100',
                    'validationRulesMessege' => [
                    'disease_onset.required'   => trans('Visits::messages.past_medication_history_validation_required')
                    ],
                    'decrypt'=>false,
                    'fillable' => true,
                ],
                'disease_status'=>
                [   
                    'type'=>'input',
                    'isRequired' =>true,
                    'validationRulesMessege' => [
                    'disease_status.required'   => trans('Visits::messages.past_medication_history_validation_required')
                    ],
                    'decrypt'=>false,
                    'fillable' => true,
                ],
                'disease_duration'=>
                [   
                    'type'=>'input',
                    'isRequired' =>false,
                    'decrypt'=>false,
                    'validation'=>'required_with:disease_onset|numeric',
                    'validationRulesMessege' => [
                    'disease_duration.numeric'    => trans('Visits::messages.medication_history_validation_dose_unit_numeric'),
                    'disease_onset.required_with' => trans('Visits::messages.medication_history_validation_dose_unit_required_with'),
                    ],
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
                ],
                'disease_end_date'=>
                [   
                    'type'          => 'input',
                    'isRequired'    => true,
                    'decrypt'       => false,
                    'validation'    => 'nullable|date_format:"d/m/Y"',
                    'fillable'      => true,
                    'validationRulesMessege' => [
                        'disease_end_date.date_format' => trans('Visits::messages.past_medication_history_validation_valid_date')
                    ],
                ]
            ],
        ];
        $responseValidatorForm = $this->postValidatorForm($posConfig,$request);
        if (!$responseValidatorForm['status']) {
            return $responseValidatorForm['response'];
        }

        if($responseValidatorForm['status']){
            $fillableData = $responseValidatorForm['response']['fillable'][$tableName];

            $fillableData['disease_end_date'] = !empty($fillableData['disease_end_date']) ? $this->dateTimeLibObj->covertUserDateToServerType($fillableData['disease_end_date'],'dd/mm/YY','Y-m-d')['result'] : NULL;

            if(empty($fillableData['disease_end_date']) && ($fillableData['disease_status'] == Config::get('dataconstants.DISEASE_STATUS_INACTIVE')) ){
                $fillableData['disease_end_date'] = date('Y-m-d');
            }

            if(!empty($fillableData['disease_end_date']) && (strtotime($fillableData['disease_end_date']) < strtotime(date('Y-m-d')) )) {
                $fillableData['disease_status'] = Config::get('dataconstants.DISEASE_STATUS_INACTIVE');
            }

            if(empty($fillableData['disease_end_date']) && empty($fillableData['disease_status'])) {
                $fillableData['disease_status'] = Config::get('dataconstants.DISEASE_STATUS_ACTIVE');
            }
            
            try{
                if (isset($fillableData[$primaryKey]) && !empty($fillableData[$primaryKey])){
                    $whereData = [];
                    $whereData['visit_id'] = $fillableData['visit_id'];
                    $whereData['pat_id']  = $fillableData['pat_id'];
                    $whereData[$primaryKey]  = $fillableData[$primaryKey];
                    $storePrimaryId = $this->pastMedicationHistoryObj->updateRequest($fillableData,$whereData);
                } else {
                    $storePrimaryId = $this->pastMedicationHistoryObj->addRequest($fillableData);
                }

                 if($storePrimaryId){
                        $storePrimaryIdEncrypted = $this->securityLibObj->encrypt($storePrimaryId);
                        return $this->resultResponse(
                            Config::get('restresponsecode.SUCCESS'), 
                            [$primaryKey => $storePrimaryIdEncrypted], 
                            [],
                            trans('Visits::messages.past_medication_history_add_successfull'), 
                            $this->http_codes['HTTP_OK']
                        );
                    }else{
                        return $this->resultResponse(
                            Config::get('restresponsecode.ERROR'), 
                            [], 
                            [],
                            trans('Visits::messages.past_medication_history_add_fail'), 
                            $this->http_codes['HTTP_OK']
                        );
                    }
            } catch (\Exception $ex) {
                $eMessage = $this->exceptionLibObj->reFormAndLogException($ex,'MedicationHistoryController', 'store');
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
     * @DateOfCreation        19 June 2018
     * @ShortDescription      This function is responsible for get WorkEnvironment Data by patId and visitId
     * @param                 encrypted integer $patId   
     * @param                 encrypted integer $visitId   
     * @return                Array of status and message
     */
    public function getListData(Request $request)
    {
        $requestData = $this->getRequestData($request);
        $getListDataResponse = $this->pastMedicationHistoryObj->getListData($requestData);
        
        return $this->resultResponse(
            Config::get('restresponsecode.SUCCESS'), 
            $getListDataResponse, 
            [],
            trans('Visits::messages.past_medication_history_list_successfull'),
            $this->http_codes['HTTP_OK']
        );
    }

    /**
     * @DateOfCreation        19 June 2018
     * @ShortDescription      This function is responsible for get WorkEnvironment Data by patId and visitId
     * @param                 encrypted integer $patId   
     * @param                 encrypted integer $visitId   
     * @return                Array of status and message
     */
    public function getDiseaseList(Request $request)
    {
        $requestData = $this->getRequestData($request);
        $getDiseaseListDataResponse = $this->pastMedicationHistoryObj->getDiseaseListData();
        
        return $this->resultResponse(
            Config::get('restresponsecode.SUCCESS'), 
            $getDiseaseListDataResponse, 
            [],
            trans('Visits::messages.disease_list_successfull'),
            $this->http_codes['HTTP_OK']
        );
    }

    /**
    * @DateOfCreation        11 June 2018
    * @ShortDescription      This function is responsible for delete visit WorkEnvironment Data 
    * @param                 Array $wefId   
    * @return                Array of status and message
    */
    public function destroy(Request $request)
    {   
        $requestData = $this->getRequestData($request);
        $primaryKey = $this->pastMedicationHistoryObj->getTablePrimaryIdColumn();
        $primaryId = $requestData[$primaryKey];
        $primaryId = $this->securityLibObj->decrypt($primaryId);
        $isPrimaryIdExist = $this->pastMedicationHistoryObj->isPrimaryIdExist($primaryId);
        if(!$isPrimaryIdExist){
            return $this->resultResponse(
                Config::get('restresponsecode.ERROR'), 
                [], 
                [$primaryKey => [trans('Visits::messages.past_medication_history_not_exist')]],
                trans('Visits::messages.medication_history_not_exist'), 
                $this->http_codes['HTTP_OK']
            ); 
        }

        $deleteDataResponse   = $this->pastMedicationHistoryObj->doDeleteRequest($primaryId);
        if($deleteDataResponse){
            return $this->resultResponse(
                Config::get('restresponsecode.SUCCESS'), 
                [], 
                [],
                trans('Visits::messages.past_medication_history_data_deleted'),
                $this->http_codes['HTTP_OK']
            );
        }
        return $this->resultResponse(
            Config::get('restresponsecode.ERROR'), 
            [], 
            [],
            trans('Visits::messages.past_medication_history_data_not_deleted'),
            $this->http_codes['HTTP_OK']
        );

    }
}
