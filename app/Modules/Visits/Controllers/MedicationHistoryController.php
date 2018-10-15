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
use App\Modules\Visits\Models\MedicationHistory;
use App\Modules\Visits\Models\MedicationAntibioticHistory;
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
class MedicationHistoryController extends Controller
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
        $this->medicationHistoryObj = new MedicationHistory();

        // Init MedicationHistory Model Object
        $this->medicationAntibioticHistoryObj = new MedicationAntibioticHistory();

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
        $requestDataOnly = $request->only('medicine_dose','medicine_dose_unit', 'prev_medicine_id');
        $previousMedicineId = !empty($requestDataOnly['prev_medicine_id']) ? $this->securityLibObj->decrypt($requestDataOnly['prev_medicine_id']) : NULL;

        $tableName = $this->medicationHistoryObj->getTableName();
        $primaryKey = $this->medicationHistoryObj->getTablePrimaryIdColumn();
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
                'medicine_id'=>
                [   
                    'type'=>'input',
                    'isRequired' =>true,
                    'validation'=>'required',
                    'validationRulesMessege' => [
                    'medicine_name.required'   => trans('Visits::messages.medication_history_validation_required'),
                    'medicine_name.min'   => trans('Visits::messages.medication_history_validation_medicine_name_min'),
                    'medicine_name.max'   => trans('Visits::messages.medication_history_validation_required_medicine_name_max'),
                    ],
                    'decrypt'=>true,
                    'fillable' => true,
                ],
                'medicine_start_date'=>
                [   
                    'type'=>'date',
                    'isRequired' =>true,
                    'validation'=>'required|date_format:"d/m/Y"',
                    'validationRulesMessege' => [
                    'medicine_start_date.required'   => trans('Visits::messages.medication_history_validation_required'),
                    'medicine_start_date.date_format'   => trans('Visits::messages.medication_history_validation_date_format'),
                    ],
                    'decrypt'=>false,
                    'fillable' => true,
                ],
                'medicine_end_date'=>
                [   
                    'type'=>'date',
                    'isRequired' =>false,
                    'validation'=>'date_format:"d/m/Y"|after_or_equal:medicine_start_date',
                    'validationRulesMessege' => [
                    'medicine_end_date.required'   => trans('Visits::messages.medication_history_validation_required'),
                    'medicine_end_date.date_format'   => trans('Visits::messages.medication_history_validation_date_format'),
                    'medicine_end_date.after_or_equal'   => trans('Visits::messages.medication_history_validation_after_or_equal'),
                    ],
                    'decrypt'=>false,
                    'fillable' => true,
                ],
                'medicine_dose'=>
                [   
                    'type'=>'input',
                    'isRequired' =>false,
                    'validation'=>'required_with:medicine_dose_unit',
                    'validationRulesMessege' => [
                    'medicine_dose_unit.required_with'   => trans('Visits::messages.medication_history_validation_dose_required_with'),
                    ],
                    'decrypt'=>false,                    
                    'fillable' => true,
                ],
                'medicine_dose_unit'=>
                [   
                    'type'=>'input',
                    'isRequired' =>false,
                    'decrypt'=>true,
                    'validation'=>'required_with:medicine_dose|numeric',
                    'validationRulesMessege' => [
                    'medicine_dose_unit.numeric'   => trans('Visits::messages.medication_history_validation_dose_unit_numeric'),
                    'medicine_dose.required_with'   => trans('Visits::messages.medication_history_validation_dose_unit_required_with'),
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
                'prev_medicine_id'=>
                [   
                    'type'=>'input',
                    'isRequired' =>true,
                    'decrypt'=>true,
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
                    $whereData['visit_id'] = $fillableData['visit_id'];
                    $whereData['pat_id']  = $fillableData['pat_id'];
                    $whereData[$primaryKey]  = $fillableData[$primaryKey];

                    $fillableData['medicine_id'] = !empty($fillableData['medicine_id']) ? $fillableData['medicine_id'] : $previousMedicineId;
                    $storePrimaryId = $this->medicationHistoryObj->updateRequest($fillableData,$whereData);
                } else {
                    $storePrimaryId = $this->medicationHistoryObj->addRequest($fillableData);
                }

                 if($storePrimaryId){
                        $storePrimaryIdEncrypted = $this->securityLibObj->encrypt($storePrimaryId);
                        return $this->resultResponse(
                            Config::get('restresponsecode.SUCCESS'), 
                            [$primaryKey => $storePrimaryIdEncrypted], 
                            [],
                            trans('Visits::messages.medication_history_add_successfull'), 
                            $this->http_codes['HTTP_OK']
                        );
                    }else{
                        return $this->resultResponse(
                            Config::get('restresponsecode.ERROR'), 
                            [], 
                            [],
                            trans('Visits::messages.medication_history_add_fail'), 
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
        $requestData['userId'] = (in_array($request->user()->user_type, Config::get('constants.USER_TYPE_STAFF'))) ? $request->user()->created_by : $request->user()->user_id;
        $getListDataResponse = $this->medicationHistoryObj->getListData($requestData);
        
        return $this->resultResponse(
            Config::get('restresponsecode.SUCCESS'), 
            $getListDataResponse, 
            [],
            trans('Visits::messages.medication_history_list_successfull'),
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
        $primaryKey = $this->medicationHistoryObj->getTablePrimaryIdColumn();
        $primaryId = $requestData[$primaryKey];
        $primaryId = $this->securityLibObj->decrypt($primaryId);
        $isPrimaryIdExist = $this->medicationHistoryObj->isPrimaryIdExist($primaryId);
        if(!$isPrimaryIdExist){
            return $this->resultResponse(
                Config::get('restresponsecode.ERROR'), 
                [], 
                [$primaryKey => [trans('Visits::messages.medication_history_not_exist')]],
                trans('Visits::messages.medication_history_not_exist'), 
                $this->http_codes['HTTP_OK']
            ); 
        }

        $deleteDataResponse   = $this->medicationHistoryObj->doDeleteRequest($primaryId);
        if($deleteDataResponse){
            return $this->resultResponse(
                Config::get('restresponsecode.SUCCESS'), 
                [], 
                [],
                trans('Visits::messages.medication_history_data_deleted'),
                $this->http_codes['HTTP_OK']
            );
        }
        return $this->resultResponse(
            Config::get('restresponsecode.ERROR'), 
            [], 
            [],
            trans('Visits::messages.medication_history_data_not_deleted'),
            $this->http_codes['HTTP_OK']
        );

    }

    /**
    * @DateOfCreation        11 June 2018
    * @ShortDescription      This function is responsible for  get Antibiotic data by visitID and PatientId
    * @param                 Array $visit_id and pat_id  
    * @return                Array of status and message
    */
    public function getAntibiotic(Request $request){

        $requestData        = $this->getRequestData($request);
        $patId              = $this->securityLibObj->decrypt($requestData['pat_id']);
        $visitId            = $this->securityLibObj->decrypt($requestData['visit_id']);
        $resAntibioticData  = $this->medicationAntibioticHistoryObj->getListData($visitId,$patId);
        return $this->resultResponse(
            Config::get('restresponsecode.SUCCESS'), 
            $resAntibioticData, 
            [],
            trans('Visits::messages.medication_antibiotic_history_data_successfull'),
            $this->http_codes['HTTP_OK']
        );
    }

    /**
     * @DateOfCreation        21 May 2018
     * @ShortDescription      This function is responsible to get the WorkEnvironment add
     * @return                Array of status and message
     */
    public function storeAntibiotic(Request $request)
    {
        $requestData        = $this->getRequestData($request);
        $patId              = $this->securityLibObj->decrypt($requestData['pat_id']);
        $visitId            = $this->securityLibObj->decrypt($requestData['visit_id']);
        $resAntibioticData  = $this->medicationAntibioticHistoryObj->getListData($visitId,$patId);
        $tableName = $this->medicationAntibioticHistoryObj->getTableName();
        $primaryKey = $this->medicationAntibioticHistoryObj->getTablePrimaryIdColumn();
        $posConfig =
        [   $tableName =>
            [
                $primaryKey=>
                [   
                    'type'=>'input',
                    'decrypt'=>true,
                    'isRequired' =>false,
                    'fillable' => true,
                    'valueOverwrite' => isset($resAntibioticData) && !empty($resAntibioticData) && isset($resAntibioticData->ph_id) ? $this->securityLibObj->decrypt($resAntibioticData->ph_id) : ''
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
                'number_of_antibiotics_course'=>
                [   
                    'type'=>'input',
                    'isRequired' =>true,
                    'validation'=>'required|numeric',
                    'validationRulesMessege' => [
                    'number_of_antibiotics_course.required'   => trans('Visits::messages.medication_antibiotic_history_validation_required'),
                    'number_of_antibiotics_course.numeric'   => trans('Visits::messages.medication_antibiotic_history_validation_medicine_name_min')
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
                    $whereData['visit_id'] = $fillableData['visit_id'];
                    $whereData['pat_id']  = $fillableData['pat_id'];
                    $whereData[$primaryKey]  = $fillableData[$primaryKey];
                    $storePrimaryId = $this->medicationAntibioticHistoryObj->updateRequest($fillableData,$whereData);
                } else {
                    $storePrimaryId = $this->medicationAntibioticHistoryObj->addRequest($fillableData);
                }

                 if($storePrimaryId){
                        $storePrimaryIdEncrypted = $this->securityLibObj->encrypt($storePrimaryId);
                        return $this->resultResponse(
                            Config::get('restresponsecode.SUCCESS'), 
                            [$primaryKey => $storePrimaryIdEncrypted], 
                            [],
                            trans('Visits::messages.medication_antibiotic_history_add_successfull'), 
                            $this->http_codes['HTTP_OK']
                        );
                    }else{
                        return $this->resultResponse(
                            Config::get('restresponsecode.ERROR'), 
                            [], 
                            [],
                            trans('Visits::messages.medication_antibiotic_history_add_fail'), 
                            $this->http_codes['HTTP_OK']
                        );
                    }
            } catch (\Exception $ex) {
                $eMessage = $this->exceptionLibObj->reFormAndLogException($ex,'MedicationHistoryController', 'storeAntibiotic');
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
