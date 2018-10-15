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
use App\Modules\Visits\Models\WorkEnvironmentFactor;
use App\Traits\FxFormHandler;


/**
 * WorkEnvironmentController
 *
 * @package                ILD India Registry
 * @subpackage             WorkEnvironmentController
 * @category               Controller
 * @DateOfCreation         18 june 2018
 * @ShortDescription       This controller to handle all the operation related to 
                           setup WorkEnvironment
 **/
class WorkEnvironmentFactorController extends Controller
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

        // Init WorkEnvironmentFactor Model Object
        $this->workEnvironmentFactorObj = new WorkEnvironmentFactor();

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
        $tableName = 'work_environment_factor';
        $posConfig =
        [   $tableName =>
            [
                'wef_id'=>
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
                'wef_id'=>
                [   
                    'type'=>'input',
                    'decrypt'=>true,
                    'isRequired' =>false,
                    'fillable' => true,
                ],
                'wef_is_working_location_outside'=>
                [   
                    'type'=>'input',
                    'isRequired' =>false,
                    'decrypt'=>false,
                    'fillable' => true,
                ],
                'wef_is_smoky_dust'=>
                [   
                    'type'=>'input',
                    'isRequired' =>false,
                    'decrypt'=>false,
                    'fillable' => true,
                ],
                'wef_use_of_protective_masks'=>
                [   
                    'type'=>'input',
                    'isRequired' =>false,
                    'decrypt'=>false,
                    'fillable' => true,
                ],
                'wef_occupation'=>
                [   
                    'type'=>'input',
                    'isRequired' =>true,
                    'validation'=>'required|min:2|max:255',
                    'validationRulesMessege' => [
                    'wef_worked_to_year.required'   => trans('Visits::messages.work_environment_validation_required'),
                    'wef_worked_to_year.min'   => trans('Visits::messages.work_environment_validation_occupation_min'),
                    'wef_worked_to_year.max'   => trans('Visits::messages.work_environment_validation_required_occupation_max'),
                    ],
                    'decrypt'=>false,
                    'fillable' => true,
                ],
                'wef_worked_from_month'=>
                [   
                    'type'=>'input',
                    'isRequired' =>false,
                    'decrypt'=>false,
                    'fillable' => true,
                ],
                'wef_worked_from_year'=>
                [   
                    'type'=>'input',
                    'isRequired' =>false,
                    'validation'=>'date_format:"Y"|before_or_equal:wef_worked_from_year',
                    'validationRulesMessege' => [
                    'wef_worked_to_year.before_or_equal'   => trans('Visits::messages.work_environment_validation_after_or_equal'),
                    ],
                    'decrypt'=>false,
                    'fillable' => true,
                ],
                'wef_worked_to_month'=>
                [   
                    'type'=>'input',
                    'isRequired' =>false,
                    'decrypt'=>false,
                    'fillable' => true,
                ],
                'wef_worked_to_year'=>
                [   
                    'type'=>'input',
                    'isRequired' =>false,
                    'validation'=>'date_format:"Y"|after_or_equal:wef_worked_from_year',
                    'validationRulesMessege' => [
                    'wef_worked_to_year.after_or_equal'   => trans('Visits::messages.work_environment_validation_after_or_equal'),
                    ],
                    'decrypt'=>false,
                    'fillable' => true,
                ],
                'wef_exposures'=>
                [   
                    'type'=>'input',
                    'isRequired' =>false,
                    'decrypt'=>false,
                    'validation'=>'min:2|max:255',
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
                if (isset($fillableData['wef_id']) && !empty($fillableData['wef_id'])){
                    $whereData = [];
                    $whereData['visit_id'] = $fillableData['visit_id'];
                    $whereData['pat_id']  = $fillableData['pat_id'];
                    $whereData['wef_id']  = $fillableData['wef_id'];
                    $workEnvironmentId = $this->workEnvironmentFactorObj->updateWorkEnvironment($fillableData,$whereData);
                } else {
                    $workEnvironmentId = $this->workEnvironmentFactorObj->addWorkEnvironment($fillableData);
                }

                 if($workEnvironmentId){
                        $createdPatientIdEncrypted = $this->securityLibObj->encrypt($workEnvironmentId);
                        return $this->resultResponse(
                            Config::get('restresponsecode.SUCCESS'), 
                            ['wef_id' => $workEnvironmentId], 
                            [],
                            trans('Visits::messages.work_environment_add_successfull'), 
                            $this->http_codes['HTTP_OK']
                        );
                    }else{
                        //user pat_consent_file unlink
                        if(!empty($pdfPath) && file_exists($pdfPath)){
                            unlink($pdfPath);
                        }
                        return $this->resultResponse(
                            Config::get('restresponsecode.ERROR'), 
                            [], 
                            [],
                            trans('Visits::messages.work_environment_add_fail'), 
                            $this->http_codes['HTTP_OK']
                        );
                    }
            } catch (\Exception $ex) {
                //user pat_consent_file unlink
                
                if(!empty($pdfPath) && file_exists($pdfPath)){
                    unlink($pdfPath);
                }
                $eMessage = $this->exceptionLibObj->reFormAndLogException($ex,'WorkEnvironmentFactorController', 'store');
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
    public function getWorkEnvironmentData(Request $request)
    {
        $requestData = $this->getRequestData($request);
        $patientWorkEnvironmentVisitData = $this->workEnvironmentFactorObj->getWorkEnvironmentDataByPatientIdAndVistId($requestData);
        
        return $this->resultResponse(
            Config::get('restresponsecode.SUCCESS'), 
            $patientWorkEnvironmentVisitData, 
            [],
            trans('Visits::messages.work_environment_list_successfull'),
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
        $primaryKey = $this->workEnvironmentFactorObj->getTablePrimaryIdColumn();
        $primaryId = $this->securityLibObj->decrypt($requestData[$primaryKey]);
        $isPrimaryIdExist = $this->workEnvironmentFactorObj->isPrimaryIdExist($primaryId);
        if(!$isPrimaryIdExist){
            return $this->resultResponse(
                Config::get('restresponsecode.ERROR'), 
                [], 
                [$primaryKey=> [trans('Visits::messages.work_environment_not_exist')]],
                trans('Visits::messages.work_environment_not_exist'), 
                $this->http_codes['HTTP_OK']
            ); 
        }

        $workEnvironmentDeleteData   = $this->workEnvironmentFactorObj->doDeleteWorkEnvironment($primaryId);
        if($workEnvironmentDeleteData){
            return $this->resultResponse(
                Config::get('restresponsecode.SUCCESS'), 
                [], 
                [],
                trans('Visits::messages.work_environment_data_deleted'),
                $this->http_codes['HTTP_OK']
            );
        }
        return $this->resultResponse(
            Config::get('restresponsecode.ERROR'), 
            [], 
            [],
            trans('Visits::messages.work_environment_data_not_deleted'),
            $this->http_codes['HTTP_OK']
        );

    }
}
