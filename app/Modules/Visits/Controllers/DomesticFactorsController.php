<?php

namespace App\Modules\Visits\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Auth;
use Session;
use App\Traits\SessionTrait;
use App\Traits\RestApi;
use Config;
use Illuminate\Support\Facades\Validator;
use App\Libraries\SecurityLib;
use App\Libraries\ExceptionLib;
use App\Modules\Visits\Models\DomesticFactor;
use App\Modules\Setup\Models\StaticDataConfig as StaticData;
use DB;
use App\Libraries\FileLib;
use App\Libraries\UtilityLib;
use File;

/**
 * DomesticFactorsController
 *
 * @package                ILD INDIA
 * @subpackage             DomesticFactorsController
 * @category               Controller
 * @DateOfCreation         02 July 2018
 * @ShortDescription       This controller to handle all the operation related to 
                           Patients Domestic Factors
 */
class DomesticFactorsController extends Controller
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

        // Init DomesticFactor model object
        $this->domesticFactorModelObj = new DomesticFactor(); 

        // Init Utility Library object
        $this->utilityLibObj = new UtilityLib();

        // Init exception library object
        $this->exceptionLibObj = new ExceptionLib();        

        // Init General staticData Model Object
        $this->staticDataObj = new StaticData();
    }

    /**
     * @DateOfCreation        2 July 2018
     * @ShortDescription      This function is responsible to get the Domestic factor field value
     * @return                Array of status and message
     */
    public function getDomesticFactorByVisitID($visitId, $patientId)
    {
        $visitId                    = $this->securityLibObj->decrypt($visitId);
        $encryptVisitId                    = $this->securityLibObj->encrypt($visitId);
        $patientDomesticFactorData  = $this->domesticFactorModelObj->getPatientDomesticFactorRecord($visitId);
        $patientResidenceData  = $this->domesticFactorModelObj->getPatientResidenceRecord($visitId);
        $patientResidencePlaceArrWithCustomKey = !empty($patientResidenceData) && count($patientResidenceData)>0 ? $this->utilityLibObj->changeArrayKey(json_decode(json_encode($patientResidenceData),true), 'residence_id'):[];
        
        $staticDataKey              = $this->staticDataObj->getStaticDataConfigList()['domestic_factor_condition'];
        $staticDataKeyPlace          = $this->staticDataObj->getStaticDataConfigList()['domestic_factor_condition_place'];
        $staticDataArrWithCustomKey = $this->utilityLibObj->changeArrayKey($staticDataKey, 'id');
        $staticDataPlaceArrWithCustomKey = $this->utilityLibObj->changeArrayKey($staticDataKeyPlace, 'id');
        
        $domesticFactorRecord = [];
        $domesticFactorRecordWithFectorKey = [];
        if(!empty($patientDomesticFactorData)){
            foreach ($patientDomesticFactorData as $domesticFactorData) {
                $checkupRecordWithCustomKey = [];
                $fectorId = $this->securityLibObj->encrypt($domesticFactorData->domestic_factor_id);
                $checkupRecordWithCustomKey['domestic_factor_value_'.$fectorId]        = !is_null($domesticFactorData->domestic_factor_value) && !empty($domesticFactorData->domestic_factor_value) ? (string)$domesticFactorData->domestic_factor_value : '';
                $domesticFactorRecordWithFectorKey[$fectorId] = $checkupRecordWithCustomKey;
            }
        }
       
        $finalCheckupRecords = [];
        $tempData = [];
        if(!empty($staticDataArrWithCustomKey)){
            foreach ($staticDataArrWithCustomKey as $fectorKey => $fectorValue) {
                $temp = [];
                $encryptFectorKey = $this->securityLibObj->encrypt($fectorKey);
                $domesticFactorValuesData = ( array_key_exists($encryptFectorKey, $domesticFactorRecordWithFectorKey) ? $domesticFactorRecordWithFectorKey[$encryptFectorKey]['domestic_factor_value_'.$encryptFectorKey] : '');
                $temp = [  
                'showOnForm'=>true,
                'name' => 'domestic_factor_value_'.$encryptFectorKey,
                'title' => $fectorValue['value'],
                'type' => $fectorValue['isCheckBox'] === true ? 'customcheckbox' : 'text',
                'value' => $fectorValue['isCheckBox'] === true ? [$domesticFactorValuesData] : $domesticFactorValuesData
            ];
                if($fectorValue['isCheckBox']){
                
                   $temp['cssClasses'] =['inputParentClass' => 'col-md-12',
                                'labelClass'=>'col-md-9',
                                'inputContainerClass'=>'col-md-3',
                                'inputGroupClass'=>'form-group checkbox-listing checkbox-formgroup'
                            ];
                
                $tempData['domestic_factor_value_'.$encryptFectorKey.'_data'] = $fectorValue['isCheckBox'] === true ? $this->getCheckBoxOption('option') : [];
                } else {
                    $temp['cssClasses'] =['inputParentClass' => 'col-md-10 ml-15',
                                'inputContainerClass'=>'col-md-3'
                            ];
                }
            $finalCheckupRecords['form_'.$fectorValue['type']]['fields'][] = $temp;
            $finalCheckupRecords['form_'.$fectorValue['type']]['data'] = $tempData;
            $finalCheckupRecords['form_'.$fectorValue['type']]['handlers'] = [];
            if(isset($fectorValue['formName'])){
                $finalCheckupRecords['form_'.$fectorValue['type']]['formName'] = $fectorValue['formName'];
            }


            }
            /*foreach ($staticDataPlaceArrWithCustomKey as $keyFactorPlace => $valueFactorPlace) {
                $valuePlace = isset($patientResidencePlaceArrWithCustomKey[$keyFactorPlace]) ? (string) $patientResidencePlaceArrWithCustomKey[$keyFactorPlace]['residence_value'] :'';
                $encryptFectorKeyPlace = $this->securityLibObj->encrypt($keyFactorPlace);
                $finalCheckupRecords['form_'.$valueFactorPlace['type']]['fields'][] = ['showOnForm'=>true,
                    'name' => 'residence_'.$encryptFectorKeyPlace,
                    'title' => $valueFactorPlace['value'],
                    'type' =>  'text',
                    'value' => $valuePlace,
                    'cssClasses' => ['inputParentClass' => $keyFactorPlace == 1 ? 'col-md-2 ml-15' : 'col-md-2',
                                    'inputContainerClass'=>'col-md-3'
                                ]
                    ];
                $finalCheckupRecords['form_'.$valueFactorPlace['type']]['data'] = [];
                $finalCheckupRecords['form_'.$valueFactorPlace['type']]['handlers'] = [];
            }*/
        }

        return $this->resultResponse(
                Config::get('restresponsecode.SUCCESS'), 
                $finalCheckupRecords, 
                [],
                trans('Visits::messages.domestic_factor_get_data_successfull'),
                $this->http_codes['HTTP_OK']
            );
    }

    /**
     * @DateOfCreation        13 june 2018
     * @ShortDescription      This function is responsible for insert Patient Data 
     * @param                 Array $request   
     * @return                Array of status and message
     */    
    public function getCheckBoxOption($type,$extraData = [])
    {
        $returnResponse = '';
        switch($type){
            case 'option':
            $staticDataKey = $this->staticDataObj->getStaticDataConfigList()['yes_no_option'];
            $returnResponse = array_map(function($tag) {
            return array(
                'value' => $tag['id'],
                'label' => $tag['value']
            );
            }, $staticDataKey);
            break;
        }
            
        return $returnResponse;
    }
    /**
     * @DateOfCreation        13 june 2018
     * @ShortDescription      This function is responsible for insert Patient Data 
     * @param                 Array $request   
     * @return                Array of status and message
     */
    public function addUpdateDomesticFactor(Request $request)
    { 
        $requestData = $this->getRequestData($request);
        $encryptedVisitId               = $requestData['visit_id'];
        $requestData['user_id']         = $request->user()->user_id;
        $requestData['is_deleted']      = Config::get('constants.IS_DELETED_NO');  
        $requestData['pat_id']          = $this->securityLibObj->decrypt($requestData['pat_id']);
        $requestData['visit_id']        = $this->securityLibObj->decrypt($requestData['visit_id']);
        $visitId                        = $requestData['visit_id'];
        try{
            DB::beginTransaction();
            $staticDataKey                         = $this->staticDataObj->getStaticDataConfigList()['domestic_factor_condition'];
            $staticDataArrWithCustomKey            = $this->utilityLibObj->changeArrayKey($staticDataKey, 'id');
            $patientDomesticFactorData             = $this->domesticFactorModelObj->getPatientDomesticFactorRecord($visitId);
            $patientDomesticFactorDataWithKey      = $this->utilityLibObj->changeArrayKey(json_decode(json_encode($patientDomesticFactorData), true) , 'domestic_factor_id');
            $patientResidenceData                  = $this->domesticFactorModelObj->getPatientResidenceRecord($visitId);
            $patientResidencePlaceArrWithCustomKey = !empty($patientResidenceData) && count($patientResidenceData)>0 ? $this->utilityLibObj->changeArrayKey(json_decode(json_encode($patientResidenceData),true), 'residence_id'):[];
            $staticDataKeyPlace                    = $this->staticDataObj->getStaticDataConfigList()['domestic_factor_condition_place'];
            $staticDataPlaceArrWithCustomKey       = $this->utilityLibObj->changeArrayKey($staticDataKeyPlace, 'id');

            $insertData = [];
            $insertDataPlace = [];
            if(!empty($staticDataArrWithCustomKey)){
                foreach ($staticDataArrWithCustomKey as $fectorKey => $fectorValue) {
                    $fectorIdEncrypted = $this->securityLibObj->encrypt($fectorKey);
                    $temp = [];
                    $domesticFactorValue = isset($requestData['domestic_factor_value_'.$fectorIdEncrypted]) ? $requestData['domestic_factor_value_'.$fectorIdEncrypted] : '';
                    $temp = [
                            'pat_id'    =>  $requestData['pat_id'],
                            'visit_id'  =>  $requestData['visit_id'],
                            'domestic_factor_id'  =>  $fectorKey,
                            'domestic_factor_value'  =>  $domesticFactorValue,
                            'ip_address'  =>  $requestData['ip_address'],
                            'resource_type'  =>  $requestData['resource_type'],
                    ];
                    $pdfcId = (isset($patientDomesticFactorDataWithKey[$fectorKey]['pdfc_id']) && 
                                !empty($patientDomesticFactorDataWithKey[$fectorKey]['pdfc_id']) )
                                ? $this->securityLibObj->decrypt($patientDomesticFactorDataWithKey[$fectorKey]['pdfc_id']) : '';
                   if(array_key_exists($fectorKey, $patientDomesticFactorDataWithKey) && !empty($pdfcId)){
                        $whereData =[];
                        $whereData = [
                            'pat_id'    =>  $requestData['pat_id'],
                            'visit_id'  =>  $requestData['visit_id'],
                            'pdfc_id'  =>  $pdfcId,
                        ];
                        $updateData = $this->domesticFactorModelObj->updateDomesticFactor($temp,$whereData);
                        if(!$updateData){
                            $dataDbStatus = true;
                            $dbCommitStatus = false;
                            break;
                        }else{
                            $dbCommitStatus = true;
                        }
                   }
                   if(!empty($domesticFactorValue) && empty($pdfcId)){
                        $insertData[] = $temp;
                   }
                }

                if(!empty($staticDataPlaceArrWithCustomKey) && !isset($dataDbStatus)){
                    foreach ($staticDataPlaceArrWithCustomKey as $fectorKeyPlace => $fectorValuePlace) {
                        $fectorIdPlaceEncrypted = $this->securityLibObj->encrypt($fectorKeyPlace);
                        $temp = [];
                        $placeValue = isset($requestData['residence_'.$fectorIdPlaceEncrypted]) ? $requestData['residence_'.$fectorIdPlaceEncrypted] : '';
                        $temp = [
                                'pat_id'    =>  $requestData['pat_id'],
                                'visit_id'  =>  $requestData['visit_id'],
                                'residence_id'  =>  $fectorKeyPlace,
                                'residence_value'  =>  $placeValue,
                                'ip_address'  =>  $requestData['ip_address'],
                                'resource_type'  =>  $requestData['resource_type'],
                        ];
                        $prId = (isset($patientResidencePlaceArrWithCustomKey[$fectorKeyPlace]['pr_id']) && 
                                    !empty($patientResidencePlaceArrWithCustomKey[$fectorKeyPlace]['pr_id']) )
                                    ? $this->securityLibObj->decrypt($patientResidencePlaceArrWithCustomKey[$fectorKeyPlace]['pr_id']) : '';
                       if(array_key_exists($fectorKeyPlace, $patientResidencePlaceArrWithCustomKey) && !empty($prId)){
                            $whereData =[];
                            $whereData = [
                                'pat_id'    =>  $requestData['pat_id'],
                                'visit_id'  =>  $requestData['visit_id'],
                                'pr_id'  =>  $prId,
                            ];
                            $updateData = $this->domesticFactorModelObj->updatePatientResidence($temp,$whereData);
                            if(!$updateData){
                                $dataDbStatus = true;
                                $dbCommitStatus = false;
                                break;
                            }else{
                                $dbCommitStatus = true;
                            }
                       }
                       if(!empty($placeValue) && empty($prId)){
                            $insertDataPlace[] = $temp;
                       }
                        
                    }


                }
                if(isset($dataDbStatus) && $dataDbStatus){
                    DB::rollback();
                    return $this->resultResponse(
                        Config::get('restresponsecode.ERROR'), 
                        [], 
                        [],
                        trans('Visits::messages.domestic_factor_add_fail'), 
                        $this->http_codes['HTTP_OK']
                    );
                }

                if(!empty($insertDataPlace)){
                    $addData = $this->domesticFactorModelObj->addPatientResidence($insertDataPlace);
                    if(!$addData){
                        DB::rollback();
                        return $this->resultResponse(
                            Config::get('restresponsecode.ERROR'), 
                            [], 
                            [],
                            trans('Visits::messages.domestic_factor_add_fail'), 
                            $this->http_codes['HTTP_OK']
                        );
                    }else{
                        $dbCommitStatus = true;
                    }
                }
                if(!empty($insertData)){
                    $addData = $this->domesticFactorModelObj->addDomesticFactor($insertData);
                    if(!$addData){
                        DB::rollback();
                        return $this->resultResponse(
                            Config::get('restresponsecode.ERROR'), 
                            [], 
                            [],
                            trans('Visits::messages.domestic_factor_add_fail'), 
                            $this->http_codes['HTTP_OK']
                        );
                    }else{
                        DB::commit();
                        $dbCommitStatus = false;
                        return $this->resultResponse(
                            Config::get('restresponsecode.SUCCESS'), 
                            [], 
                            [],
                            trans('Visits::messages.domestic_factor_add_success'), 
                            $this->http_codes['HTTP_OK']
                        );
                    }
                }else if(!isset($dbCommitStatus)){
                    $dbCommitStatus = true;
                }

                if(isset($dbCommitStatus) && $dbCommitStatus){
                    DB::commit();
                    return $this->resultResponse(
                        Config::get('restresponsecode.SUCCESS'), 
                        [], 
                        [],
                        trans('Visits::messages.domestic_factor_add_success'), 
                        $this->http_codes['HTTP_OK']
                    );
                }
            }else{
                 DB::rollback();
                        return $this->resultResponse(
                            Config::get('restresponsecode.SUCCESS'), 
                            [], 
                            [],
                            trans('Visits::messages.domestic_factor_add_fail'), 
                            $this->http_codes['HTTP_OK']
                        );
            }
        } catch (\Exception $ex) {
            DB::rollback();
            $eMessage = $this->exceptionLibObj->reFormAndLogException($ex,'DomesticFactorsController', 'addUpdateDomesticFactor');
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
