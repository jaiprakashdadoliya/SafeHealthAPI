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
use App\Modules\Visits\Models\FamilyMedicalHistory as FamilyMedicalHistory;
use App\Modules\Setup\Models\StaticDataConfig as StaticData;

/**
 * FamilyMedicalHistoryController
 *
 * @package                ILD India Registry
 * @subpackage             FamilyMedicalHistoryController
 * @category               Controller
 * @DateOfCreation         18 june 2018
 * @ShortDescription       This controller to handle all the operation related to 
                           setup Medical History
 **/
class FamilyMedicalHistoryController extends Controller
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
        $this->familyMedicalHistoryObj = new FamilyMedicalHistory();

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
    public function getFamilyMedicalHistoryByVisitID(Request $request)
    {
        $requestData        = $this->getRequestData($request);
        $visitId            = $requestData['visit_id'];
        $patientId          = $requestData['pat_id'];
        $visitId                    = $this->securityLibObj->decrypt($visitId);
        $diseaseTypeOption = $this->familyMedicalHistoryObj->getFamilyMedicalInShowType();
        $familyMedicalHistoryData  = $this->familyMedicalHistoryObj->getFamilyMedicalHistory($visitId,$diseaseTypeOption);
        $medicalHistoryRecord = [];
        $medicalHistoryWithDiseaseKey = [];
        $staticData                             = $this->staticDataObj->getStaticDataConfigList();
        $familyRelationData                 = $staticData['family_relation_type'];
        $yesNoOptionData                 = $staticData['yes_no_option'];
        if(!empty($familyMedicalHistoryData))
        {
            foreach ($familyMedicalHistoryData as $medicalHistory) 
            {   
                $diseaseTypeDb = explode(',', $medicalHistory->is_show_in_type);
                $diseaseType = array_intersect($diseaseTypeDb,$diseaseTypeOption);
                $diseaseID = $this->securityLibObj->encrypt($medicalHistory->disease_id);
                $familyRelation = $medicalHistory->family_relation;
                $familyRelation = !empty($familyRelation) ? explode(',', $familyRelation) : [];
                $medicalHistoryRecord[] = ['disease_id' => $diseaseID,
                                         'disease_status'=> !is_null($medicalHistory->disease_status) ? [(string)$medicalHistory->disease_status] : [],
                                         'disease_name' => $medicalHistory->disease_name, 
                                         'family_relation' => $familyRelation,
                                         'family_relation_id' => 'family_relation_'.$diseaseID,
                                         'disease_type' => is_array($diseaseType) && !empty($diseaseType) ? current($diseaseType):$diseaseTypeDb[0],
                                        ];
            
            }
        }
        $medicalHistoryRecordData = ['record'=>$medicalHistoryRecord,'family_relation_option'=>$this->typeConversion($familyRelationData),'yes_no_option'=>$this->typeConversion($yesNoOptionData)];
        return $this->resultResponse(
                Config::get('restresponsecode.SUCCESS'), 
                $medicalHistoryRecordData, 
                [],
                trans('Visits::messages.family_medical_get_data_successfull'),
                $this->http_codes['HTTP_OK']
            );
    }

    public function typeConversion($data){
        return array_map(function($row){
            if(isset($row['id'])){
                $row['id'] = (string) $row['id'];
            }
            return $row;
        }, $data);
    }

    public function addUpdateFamilyMedicalHistory(Request $request){

        $requestData                = $this->getRequestData($request);
        $visitIdEncrypted           = $requestData['visit_id'];
        $requestData['visit_id']    = $this->securityLibObj->decrypt($visitIdEncrypted);
        $requestData['pat_id']      = $this->securityLibObj->decrypt($requestData['pat_id']);
        $patientId                  = $requestData['pat_id'];
        $visitId                    = $requestData['visit_id'];
        try{
            DB::beginTransaction();
            $diseaseTypeOption = $this->familyMedicalHistoryObj->getFamilyMedicalInShowType();
            $familyMedicalHistoryData  = $this->familyMedicalHistoryObj->getFamilyMedicalHistory($visitId,$diseaseTypeOption);
            $insertData =[];
            if(!empty($familyMedicalHistoryData))
            {
                foreach ($familyMedicalHistoryData as $medicalHistory) {
                    $temp = [];
                    $fmhId = !is_null($medicalHistory->fmh_id) ? $this->securityLibObj->decrypt($medicalHistory->fmh_id) : '';
                    $diseaseID = $medicalHistory->disease_id;
                    $diseaseEncryptdID = $this->securityLibObj->encrypt($diseaseID);
                    $diseaseStatusValue = isset($requestData[$diseaseEncryptdID]) ?  $requestData[$diseaseEncryptdID] : '';
                    $familyRelationValue = isset($requestData['family_relation_'.$diseaseEncryptdID]) ?  $requestData['family_relation_'.$diseaseEncryptdID] : '';
                    $fmhDiseaseId = $diseaseID;
                    $temp = [
                        'pat_id'                =>  $requestData['pat_id'],
                        'visit_id'              =>  $visitId,
                        'fmh_disease_id'        =>  $diseaseID,
                        'disease_status'        =>  $diseaseStatusValue,
                        'family_relation'       =>  $familyRelationValue,
                        'ip_address'            =>  $requestData['ip_address'],
                        'resource_type'         =>  $requestData['resource_type']
                    ];
                    if(!empty($fmhId)){
                        $whereData =[];
                        $whereData = [
                            'pat_id'    =>  $requestData['pat_id'],
                            'visit_id'  =>  $requestData['visit_id'],
                            'fmh_id'    =>  $fmhId,
                        ];
                        $updateData = $this->familyMedicalHistoryObj->updateFamilyMedicalHistory($temp,$whereData);
                        if(!$updateData){
                            $dataDbStatus = true;
                            $dbCommitStatus = false;
                            break;
                        }else{
                            $dbCommitStatus = true;
                        }
                    }
                    if((!empty($diseaseStatusValue) || !empty($familyRelationValue) ) && empty($fmhId)){
                            $insertData[] = $temp;
                    }
                }
                if(isset($dataDbStatus) && $dataDbStatus){
                    DB::rollback();
                    return $this->resultResponse(
                        Config::get('restresponsecode.ERROR'), 
                        [], 
                        [],
                        trans('Visits::messages.family_medical_add_fail'), 
                        $this->http_codes['HTTP_OK']
                    );
                }
                if(!empty($insertData)){
                    $addData = $this->familyMedicalHistoryObj->addFamilyMedicalHistory($insertData);
                    if(!$addData){
                        DB::rollback();
                        return $this->resultResponse(
                            Config::get('restresponsecode.ERROR'), 
                            [], 
                            [],
                            trans('Visits::messages.family_medical_add_fail'), 
                            $this->http_codes['HTTP_OK']
                        );
                    }else{
                        DB::commit();
                        $dbCommitStatus = false;
                        return $this->resultResponse(
                            Config::get('restresponsecode.SUCCESS'), 
                            [], 
                            [],
                            trans('Visits::messages.family_medical_add_success'), 
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
                        trans('Visits::messages.family_medical_add_success'), 
                        $this->http_codes['HTTP_OK']
                    );
                }
            }
        } catch (\Exception $ex) {
            DB::rollback();
            $eMessage = $this->exceptionLibObj->reFormAndLogException($ex,'FamilyMedicalHistoryController', 'addUpdateFamilyMedicalHistory');
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
