<?php

namespace App\Modules\LaboratoryTests\Controllers;

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
use App\Modules\LaboratoryTests\Models\LaboratoryTests as LaboratoryTests;
use App\Traits\FxFormHandler;

/**
 * LaboratoryTestsController
 *
 * @package                ILD India Registry
 * @subpackage             LaboratoryTestsController
 * @category               Controller
 * @DateOfCreation         18 june 2018
 * @ShortDescription       This controller to handle all the operation related to 
                           setup LaboratoryTests
 **/
class LaboratoryTestsController extends Controller
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

        // Init LaboratoryTests Model Object
        $this->laboratoryTestsObj = new LaboratoryTests();

        // Init exception library object
        $this->exceptionLibObj = new ExceptionLib();

        // Init exception library object
        $this->dateTimeLibObj = new DateTimeLib();  

         // Init exception library object
        $this->utilityLibObj = new UtilityLib();  

    }

    /**
     * @DateOfCreation        21 May 2018
     * @ShortDescription      This function is responsible to get the Symptoms add
     * @return                Array of status and message
     */
    public function optionList(Request $request)
    {
        $optionData = [];
        $optionData['mlt_name']=$this->laboratoryTestsObj->getAllUniqueLaboratoryTestsName();
        return $this->resultResponse(
            Config::get('restresponsecode.SUCCESS'), 
            $optionData, 
            [],
            trans('LaboratoryTests::messages.laboratory_test_options_list'),
            $this->http_codes['HTTP_OK']
        );
    }

    /**
     * @DateOfCreation        15 June 2018
     * @ShortDescription      This function is responsible for get Laboratory Tests list 
     * @param                 Array $request   
     * @return                Array of status and message
     */
    public function getLaboratoryTestsList(Request $request)
    {
        $requestData = $this->getRequestData($request);
        $requestData['user_id'] = ($request->user()->user_type == Config::get('constants.USER_TYPE_DOCTOR')) ? $request->user()->user_id : $request->user()->created_by;
        $getLaboratoryTestsList = $this->laboratoryTestsObj->getLaboratoryTestsList($requestData);

        return $this->resultResponse(
                Config::get('restresponsecode.SUCCESS'), 
                $getLaboratoryTestsList, 
                [],
                trans('LaboratoryTests::messages.laboratory_tests_list'),
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
        $primaryKey = $this->laboratoryTestsObj->getTablePrimaryIdColumn();
        $primaryId = $requestData[$primaryKey];
        $primaryId = $this->securityLibObj->decrypt($primaryId);
        $isPrimaryIdExist = $this->laboratoryTestsObj->isPrimaryIdExist($primaryId);
        if(!$isPrimaryIdExist){
            return $this->resultResponse(
                Config::get('restresponsecode.ERROR'), 
                [], 
                [$primaryKey => [trans('LaboratoryTests::messages.laboratory_tests_not_exist')]],
                trans('LaboratoryTests::messages.laboratory_tests_not_exist'), 
                $this->http_codes['HTTP_OK']
            ); 
        }

        $deleteDataResponse   = $this->laboratoryTestsObj->doDeleteRequest($primaryId);
        if($deleteDataResponse){
            return $this->resultResponse(
                Config::get('restresponsecode.SUCCESS'), 
                [], 
                [],
                trans('LaboratoryTests::messages.laboratory_test_deleted'),
                $this->http_codes['HTTP_OK']
            );
        }
        return $this->resultResponse(
            Config::get('restresponsecode.ERROR'), 
            [], 
            [],
            trans('LaboratoryTests::messages.laboratory_test_not_deleted'),
            $this->http_codes['HTTP_OK']
        );

    }

    /**
     * @DateOfCreation        21 May 2018
     * @ShortDescription      This function is responsible to get the WorkEnvironment add
     * @return                Array of status and message
     */
    public function store(Request $request)
    {
        $userId = ($request->user()->user_type == Config::get('constants.USER_TYPE_DOCTOR')) ? $request->user()->user_id : $request->user()->created_by;
        $tableName   = $this->laboratoryTestsObj->getTableName();
        $primaryKey  = $this->laboratoryTestsObj->getTablePrimaryIdColumn();
        $requestData = $this->getRequestData($request);

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
            'laboratory_tests' =>[
                'mlt_name'=>
                [   
                    'type'=>'input',
                    'decrypt'=>false,
                    'isRequired' =>true,
                    'validation'=>'required',
                    'validationRulesMessege' => [
                    'mlt_name.required'   => trans('LaboratoryTests::messages.laboratory_tests_validation_mlt_name_reurired'),
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
                ]
            ]

        ];

        $responseValidatorForm = $this->postValidatorForm($posConfig,$request);

        if (!$responseValidatorForm['status']) {
            return $responseValidatorForm['response'];
        }

        if($responseValidatorForm['status']){
            $fillableDataMedicineRelation = $responseValidatorForm['response']['fillable'][$tableName];
            $fillableDataMedicine = $responseValidatorForm['response']['fillable']['laboratory_tests'];
            
            try{
                DB::beginTransaction();
                                
                $paramMedicine = ['mlt_name'=>$fillableDataMedicine['mlt_name']];
                $fetchMedicine = $this->laboratoryTestsObj->getAllLaboratoryTests($paramMedicine,false);

                if(count($fetchMedicine)>0){
                    $fetchMedicine = $this->utilityLibObj->changeObjectToArray($fetchMedicine);
                    $fillableDataMedicineRelation['mlt_id'] = current($fetchMedicine)['mlt_id'];
                }else{
                    $storePrimaryId = $this->laboratoryTestsObj->saveLaboratoryTest($fillableDataMedicine); 
                    if(!$storePrimaryId){
                        $dberror = true;
                    }
                    $fillableDataMedicineRelation['mlt_id'] = $storePrimaryId;
                }

                if(isset($dberror) && $dberror){
                    DB::rollback();
                    return $this->resultResponse(
                        Config::get('restresponsecode.ERROR'), 
                        [], 
                        [],
                        trans('LaboratoryTests::messages.laboratory_test_fail_add'), 
                        $this->http_codes['HTTP_OK']
                    );
                }

                $fillableDataMedicineRelation['user_id'] = $userId;

                if (isset($fillableDataMedicineRelation[$primaryKey]) && !empty($fillableDataMedicineRelation[$primaryKey])){

                    // Error Primary key resulting wrong

                    $message = '_update';
                    $paramMedicineRelation = ['mlt_id'=>$fillableDataMedicineRelation['mlt_id'],'user_id'=>$fillableDataMedicineRelation['user_id'], 'is_deleted'=>2];
                    $fetchMedicineRelations = $this->laboratoryTestsObj->getAllLaboratoryTestsRelation($paramMedicineRelation,false, $fillableDataMedicineRelation[$primaryKey]);
                    if(count($fetchMedicineRelations)>0){
                        DB::rollback();
                        return $this->resultResponse(
                            Config::get('restresponsecode.ERROR'), 
                            [], 
                            ['messages'=>[trans('LaboratoryTests::messages.laboratory_test_already_added')]],
                            trans('LaboratoryTests::messages.laboratory_test_already_added'), 
                            $this->http_codes['HTTP_OK']
                        );                        
                    }
                    $whereData = [];
                    $whereData[$primaryKey]  = $fillableDataMedicineRelation[$primaryKey];
                    $storePrimaryId = $this->laboratoryTestsObj->updateRequest($fillableDataMedicineRelation,$whereData);
                } else {
                    $message = '_add';
                    $paramMedicineRelation = ['mlt_id'=>$fillableDataMedicineRelation['mlt_id'],'user_id'=>$fillableDataMedicineRelation['user_id'], 'is_deleted'=>2];
                    $fetchMedicineRelations = $this->laboratoryTestsObj->getAllLaboratoryTestsRelation($paramMedicineRelation,false);
                    if(count($fetchMedicineRelations)>0){
                        DB::rollback();
                        return $this->resultResponse(
                            Config::get('restresponsecode.ERROR'), 
                            [], 
                            ['messages'=>[trans('LaboratoryTests::messages.laboratory_test_already_added')]],
                            trans('LaboratoryTests::messages.laboratory_test_already_added'), 
                            $this->http_codes['HTTP_OK']
                        );

                    }
                    $storePrimaryId = $this->laboratoryTestsObj->addRequest($fillableDataMedicineRelation);
                }

                if($storePrimaryId){
                    DB::commit();
                    $storePrimaryIdEncrypted = $this->securityLibObj->encrypt($storePrimaryId);
                    return $this->resultResponse(
                        Config::get('restresponsecode.SUCCESS'), 
                        [$primaryKey => $storePrimaryIdEncrypted], 
                        [],
                        trans('LaboratoryTests::messages.laboratory_test_successfull'.$message), 
                        $this->http_codes['HTTP_OK']
                    );
                }else{
                    DB::rollback();
                    return $this->resultResponse(
                        Config::get('restresponsecode.ERROR'), 
                        [], 
                        ['messages'=>[trans('LaboratoryTests::messages.laboratory_test_fail'.$message)]],
                        trans('LaboratoryTests::messages.laboratory_test_fail'.$message), 
                        $this->http_codes['HTTP_OK']
                    );
                }
            } catch (\Exception $ex) {
                DB::rollback();
                $eMessage = $this->exceptionLibObj->reFormAndLogException($ex,'LaboratoryTestsController', 'store');
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
