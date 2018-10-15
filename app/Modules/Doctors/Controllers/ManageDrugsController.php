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
use App\Modules\Doctors\Models\ManageDrugs as ManageDrugs;
use App\Modules\Doctors\Models\DrugDoseUnit;
use App\Modules\Doctors\Models\DrugType;
use App\Modules\Doctors\Models\Medicines;
use App\Traits\FxFormHandler;

/**
 * ManageDrugsController
 *
 * @package                ILD India Registry
 * @subpackage             ManageDrugsController
 * @category               Controller
 * @DateOfCreation         18 june 2018
 * @ShortDescription       This controller to handle all the operation related to 
                           setup ManageDrugs
 **/
class ManageDrugsController extends Controller
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

        // Init ManageDrugs Model Object
        $this->manageDrugsObj = new ManageDrugs();

        // Init exception library object
        $this->exceptionLibObj = new ExceptionLib();

        // Init exception library object
        $this->dateTimeLibObj = new DateTimeLib();  

         // Init exception library object
        $this->utilityLibObj = new UtilityLib();   

        // Init DrugDoseUnit model object
        $this->drugDoseUnitObj = new DrugDoseUnit();  

        // Init DrugType model object
        $this->drugTypeObj = new DrugType();  

        // Init DrugType model object
        $this->medicinesObj = new Medicines();   

    }

    /**
     * @DateOfCreation        21 May 2018
     * @ShortDescription      This function is responsible to get the Symptoms add
     * @return                Array of status and message
     */
    public function optionList(Request $request)
    {
        $optionData = [];
        $optionData['drug_type']=$this->drugTypeObj->getAllDrugType();
        $optionData['drug_dose_unit']=$this->drugDoseUnitObj->getAllDrugDoseUnit();
        $optionData['medicine_name']=$this->medicinesObj->getAllUniqueMedicinesName();
        return $this->resultResponse(
            Config::get('restresponsecode.SUCCESS'), 
            $optionData, 
            [],
            trans('Doctors::messages.doctors_drugs_options_list'),
            $this->http_codes['HTTP_OK']
        );
    }

    /**
     * @DateOfCreation        15 June 2018
     * @ShortDescription      This function is responsible for get Drug list 
     * @param                 Array $request   
     * @return                Array of status and message
     */
    public function getDrugList(Request $request)
    {
        $requestData = $this->getRequestData($request);
        $requestData['user_id'] = ($request->user()->user_type == Config::get('constants.USER_TYPE_DOCTOR')) ? $request->user()->user_id : $request->user()->created_by;
        $getDrugList = $this->manageDrugsObj->getDrugList($requestData);

        return $this->resultResponse(
                Config::get('restresponsecode.SUCCESS'), 
                $getDrugList, 
                [],
                trans('Doctors::messages.doctors_drugs_list'),
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
        $primaryKey = $this->manageDrugsObj->getTablePrimaryIdColumn();
        $primaryId = $requestData[$primaryKey];
        $primaryId = $this->securityLibObj->decrypt($primaryId);
        $isPrimaryIdExist = $this->manageDrugsObj->isPrimaryIdExist($primaryId);
        if(!$isPrimaryIdExist){
            return $this->resultResponse(
                Config::get('restresponsecode.ERROR'), 
                [], 
                [$primaryKey => [trans('Doctors::messages.doctors_drugs_not_exist')]],
                trans('Doctors::messages.doctors_drugs_not_exist'), 
                $this->http_codes['HTTP_OK']
            ); 
        }

        $deleteDataResponse   = $this->manageDrugsObj->doDeleteRequest($primaryId);
        if($deleteDataResponse){
            return $this->resultResponse(
                Config::get('restresponsecode.SUCCESS'), 
                [], 
                [],
                trans('Doctors::messages.doctors_drugs_deleted'),
                $this->http_codes['HTTP_OK']
            );
        }
        return $this->resultResponse(
            Config::get('restresponsecode.ERROR'), 
            [], 
            [],
            trans('Doctors::messages.doctors_drugs_not_deleted'),
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
        $tableName   = $this->manageDrugsObj->getTableName();
        $primaryKey  = $this->manageDrugsObj->getTablePrimaryIdColumn();
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
                'medicine_instractions'=>
                [   
                    'type'=>'input',
                    'decrypt'=>false,
                    'isRequired' =>false,
                    'validation'=>'required',
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
            'medicines' =>[
                'medicine_name'=>
                [   
                    'type'=>'input',
                    'decrypt'=>false,
                    'isRequired' =>true,
                    'validation'=>'required',
                    'validationRulesMessege' => [
                    'medicine_name.required'   => trans('Doctors::messages.doctors_drugs_validation_medicine_name_reurired'),
                    ],
                    'fillable' => true,
                ],
                'medicine_dose'=>
                [   
                    'type'=>'input',
                    'decrypt'=>false,
                    'isRequired' =>true,
                    'validation'=>'required|numeric',
                    'validationRulesMessege' => [
                    'medicine_dose.required'   => trans('Doctors::messages.doctors_drugs_validation_medicine_dose_reurired'),
                    'medicine_dose.numeric'   => trans('Doctors::messages.doctors_drugs_validation_medicine_dose_numeric'),
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
            ],'drug_type' =>[
                'drug_type_name'=>
                [   
                    'type'=>'input',
                    'decrypt'=>false,
                    'isRequired' =>true,
                    'validation'=>'required',
                    'validationRulesMessege' => [
                    'drug_type_name.required'   => trans('Doctors::messages.doctors_drugs_validation_drug_type_name_reurired'),
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
            ],
            'drug_dose_unit' =>[
                'drug_dose_unit_name'=>
                [   
                    'type'=>'input',
                    'decrypt'=>false,
                    'isRequired' =>true,
                    'validation'=>'required',
                    'validationRulesMessege' => [
                    'drug_dose_unit_name.required'   => trans('Doctors::messages.doctors_drugs_validation_drug_dose_unit_name_reurired'),
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
            $fillableDataMedicine = $responseValidatorForm['response']['fillable']['medicines'];
            $fillableDataDrugType = $responseValidatorForm['response']['fillable']['drug_type'];
            $fillableDataDoseUnit = $responseValidatorForm['response']['fillable']['drug_dose_unit'];

            try{
                DB::beginTransaction();
                $param=['user_id'=>$userId,'drug_type_name'=>$fillableDataDrugType['drug_type_name'],'drug_dose_unit_name'=>$fillableDataDoseUnit['drug_dose_unit_name']];
                $fetchDrugType = $this->drugTypeObj->getAllDrugType($param,false);
                $fetchDoseUnit = $this->drugDoseUnitObj->getAllDrugDoseUnit($param,false);

                if(count($fetchDrugType)>0){
                    $fetchDrugType = $this->utilityLibObj->changeObjectToArray($fetchDrugType);
                    $fillableDataMedicine['drug_type_id'] = current($fetchDrugType)['drug_type_id'];
                }else{
                    $storePrimaryId = $this->drugTypeObj->saveDrugType($fillableDataDrugType); 
                    if(!$storePrimaryId){
                        $dberror = true;
                    }
                    $fillableDataMedicine['drug_type_id'] = $storePrimaryId;
                }

                if(count($fetchDoseUnit)>0){
                    $fetchDoseUnit = $this->utilityLibObj->changeObjectToArray($fetchDoseUnit);
                    $fillableDataMedicine['drug_dose_unit_id'] = current($fetchDoseUnit)['drug_dose_unit_id'];
                }else{
                    $fillableDataDoseUnit['user_id'] = $userId;
                    $storePrimaryId =$this->drugDoseUnitObj->saveDrugDoseUnit($fillableDataDoseUnit);
                    if(!$storePrimaryId){
                        $dberror = true;
                    }
                    $fillableDataMedicine['drug_dose_unit_id'] = $storePrimaryId;
                }
                
                if(!isset($dberror)){

                    $paramMedicine = ['medicine_name'=>$fillableDataMedicine['medicine_name'],'drug_dose_unit_id'=>$fillableDataMedicine['drug_dose_unit_id'] , 'drug_type_id'=>$fillableDataMedicine['drug_type_id'],'medicine_dose'=>$fillableDataMedicine['medicine_dose']];
                    $fetchMedicine = $this->medicinesObj->getAllMedicines($paramMedicine,false);

                    if(count($fetchMedicine)>0){
                        $fetchMedicine = $this->utilityLibObj->changeObjectToArray($fetchMedicine);
                        $fillableDataMedicineRelation['medicine_id'] = current($fetchMedicine)['medicine_id'];
                    }else{
                        $storePrimaryId = $this->medicinesObj->saveMedicines($fillableDataMedicine); 
                        if(!$storePrimaryId){
                            $dberror = true;
                        }
                        $fillableDataMedicineRelation['medicine_id'] = $storePrimaryId;
                    }
                }

                if(isset($dberror) && $dberror){
                    DB::rollback();
                    return $this->resultResponse(
                        Config::get('restresponsecode.ERROR'), 
                        [], 
                        [],
                        trans('Doctors::messages.doctors_drugs_add_fail'), 
                        $this->http_codes['HTTP_OK']
                    );
                }

                $fillableDataMedicineRelation['user_id'] = $userId;
                $fillableDataMedicineRelation['medicine_instractions'] = isset($fillableDataMedicineRelation['medicine_instractions']) && !empty($fillableDataMedicineRelation['medicine_instractions']) && is_array($fillableDataMedicineRelation['medicine_instractions']) ? json_encode($fillableDataMedicineRelation['medicine_instractions']) : null;

                if (isset($fillableDataMedicineRelation[$primaryKey]) && !empty($fillableDataMedicineRelation[$primaryKey])){
                    $message = '_update';
                    $paramMedicineRelation = ['medicine_id'=>$fillableDataMedicineRelation['medicine_id'],'user_id'=>$fillableDataMedicineRelation['user_id'], 'is_deleted'=>2];
                    $fetchMedicineRelations = $this->manageDrugsObj->getAllMedicinRelation($paramMedicineRelation,false, $fillableDataMedicineRelation[$primaryKey]);
                    if(count($fetchMedicineRelations)>0){
                        DB::rollback();
                        return $this->resultResponse(
                            Config::get('restresponsecode.ERROR'), 
                            [], 
                            ['messages'=>[trans('Doctors::messages.doctors_drugs_alredy_added')]],
                            trans('Doctors::messages.doctors_drugs_alredy_added'), 
                            $this->http_codes['HTTP_OK']
                        );

                    }
                    $whereData = [];
                    $whereData[$primaryKey]  = $fillableDataMedicineRelation[$primaryKey];
                    $storePrimaryId = $this->manageDrugsObj->updateRequest($fillableDataMedicineRelation,$whereData);
                } else {
                    $message = '_add';
                    $paramMedicineRelation = ['medicine_id'=>$fillableDataMedicineRelation['medicine_id'],'user_id'=>$fillableDataMedicineRelation['user_id'], 'is_deleted'=>2];
                    $fetchMedicineRelations = $this->manageDrugsObj->getAllMedicinRelation($paramMedicineRelation,false);
                    if(count($fetchMedicineRelations)>0){
                        DB::rollback();
                        return $this->resultResponse(
                            Config::get('restresponsecode.ERROR'), 
                            [], 
                            ['messages'=>[trans('Doctors::messages.doctors_drugs_alredy_added')]],
                            trans('Doctors::messages.doctors_drugs_alredy_added'), 
                            $this->http_codes['HTTP_OK']
                        );

                    }
                    $storePrimaryId = $this->manageDrugsObj->addRequest($fillableDataMedicineRelation);
                }

                if($storePrimaryId){
                    DB::commit();
                    $storePrimaryIdEncrypted = $this->securityLibObj->encrypt($storePrimaryId);
                    return $this->resultResponse(
                        Config::get('restresponsecode.SUCCESS'), 
                        [$primaryKey => $storePrimaryIdEncrypted], 
                        [],
                        trans('Doctors::messages.doctors_drugs_successfull'.$message), 
                        $this->http_codes['HTTP_OK']
                    );
                }else{
                    DB::rollback();
                    return $this->resultResponse(
                        Config::get('restresponsecode.ERROR'), 
                        [], 
                        ['messages'=>[trans('Doctors::messages.doctors_drugs_fail'.$message)]],
                        trans('Doctors::messages.doctors_drugs_fail'.$message), 
                        $this->http_codes['HTTP_OK']
                    );
                }
            } catch (\Exception $ex) {
                DB::rollback();
                $eMessage = $this->exceptionLibObj->reFormAndLogException($ex,'ManageDrugsController', 'store');
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
