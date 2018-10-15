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
use App\Modules\Visits\Models\Consultant;
use App\Modules\Setup\Models\StaticDataConfig as StaticData;
use DB;
use App\Libraries\FileLib;
use App\Libraries\UtilityLib;
use App\Libraries\DateTimeLib;
use File;

/**
 * ConsultantController
 *
 * @package                ILD INDIA
 * @subpackage             ConsultantController
 * @category               Controller
 * @DateOfCreation         02 July 2018
 * @ShortDescription       This controller to handle all the operation related to 
                           Patients Consultant
 */
class ConsultantController extends Controller
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

         // Init Consultant model object
        $this->consultantModelObj = new Consultant(); 

        // Init Utility Library object
        $this->utilityLibObj = new UtilityLib();

        // Init exception library object
        $this->exceptionLibObj = new ExceptionLib();   

        // Init dateTime library object
        $this->dateTimeLibObj = new DateTimeLib();        

        // Init General staticData Model Object
        $this->staticDataObj = new StaticData();
    }

    /**
     * @DateOfCreation        2 July 2018
     * @ShortDescription      This function is responsible to get the Consultant field value
     * @return                Array of status and message
     */
    public function getConsultantByVisitID(Request $request)
    {
        $requestData        = $this->getRequestData($request);
        $visitId            = $requestData['visit_id'];
        $patientId          = $requestData['pat_id'];
        $visitId            = $this->securityLibObj->decrypt($visitId);

        $patientconsultant  = $this->consultantModelObj->getPatientConsultantRecord($visitId);
        $consultantRecordWithFectorKey = !empty($patientconsultant) && count($patientconsultant)>0 ? $this->utilityLibObj->changeArrayKey(json_decode(json_encode($patientconsultant),true), 'pcio_type_id'):[];
        
        $staticDataKey      = $this->staticDataObj->getStaticDataConfigList()['consultant_impression_list'];
        $staticDataArrWithCustomKey = $this->utilityLibObj->changeArrayKey($staticDataKey, 'id');
       
        $finalCheckupRecords = [];
        $tempData = [];
        if(!empty($staticDataArrWithCustomKey)){
            foreach ($staticDataArrWithCustomKey as $pcioTypeIdKey => $pcioValue) {
                $temp = [];
                $encryptpcioTypeIdKey = $this->securityLibObj->encrypt($pcioTypeIdKey);
                $consultantValuesData = ( array_key_exists($pcioTypeIdKey, $consultantRecordWithFectorKey) ? $consultantRecordWithFectorKey[$pcioTypeIdKey]['pcio_value'] : '');
                $temp = [  
                'showOnForm'=>true,
                'name' => 'consultant_'.$encryptpcioTypeIdKey,
                'title' => $pcioValue['value'],
                'type' => $pcioValue['input_type'],
                'value' => $pcioValue['input_type'] === 'customcheckbox' ? [(string) $consultantValuesData] : $consultantValuesData,
                'cssClasses' => $pcioValue['cssClasses'],
                'clearFix' => $pcioValue['isClearfix'],
            ];
            $tempData['consultant_'.$encryptpcioTypeIdKey.'_data'] = isset($pcioValue['input_type_option']) && !empty($pcioValue['input_type_option']) ? $this->getOption($pcioValue['input_type'],$pcioValue['input_type_option']):[] ;
                
            $finalCheckupRecords['form_consultant']['fields'][] = $temp;
            $finalCheckupRecords['form_consultant']['data'] = $tempData;
            $finalCheckupRecords['form_consultant']['handlers'] = [];
            }
        }

        return $this->resultResponse(
                Config::get('restresponsecode.SUCCESS'), 
                $finalCheckupRecords, 
                [],
                trans('Visits::messages.consultant_get_data_successfull'),
                $this->http_codes['HTTP_OK']
            );
    }

    /**
     * @DateOfCreation        13 june 2018
     * @ShortDescription      This function is responsible for insert Patient Consultant Data 
     * @param                 Array $request   
     * @return                Array of status and message
     */    
    public function getOption($inputType = 'text',$inputTypeOption ='')
    {
        $returnResponse = [];
        if(empty($inputTypeOption)){
            return $returnResponse;
        }
        $staticDataKey = $this->staticDataObj->getStaticDataConfigList();
        $requestData = isset($staticDataKey[$inputTypeOption]) ? $staticDataKey[$inputTypeOption] : [];
        if(empty($requestData)){
            return $requestData;
        }
        switch($inputType){
            case 'customcheckbox':
            $returnResponse = array_map(function($tag) {
            return array(
                'value' => (string) $tag['id'],
                'label' => $tag['value']
            );
            }, $requestData);
            break;
            case 'select':
            $returnResponse = array_map(function($tag) {
            return array(
                'value' => $tag['id'],
                'label' => $tag['value']
            );
            }, $requestData);
            break;
        }
            
        return $returnResponse;
    }
    /**
     * @DateOfCreation        13 june 2018
     * @ShortDescription      This function is responsible for insert Patient Consultant Data 
     * @param                 Array $request   
     * @return                Array of status and message
     */
    public function addUpdateConsultant(Request $request)
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
            $patientconsultant  = $this->consultantModelObj->getPatientConsultantRecord($visitId);
            $consultantRecordWithFectorKey = !empty($patientconsultant) && count($patientconsultant)>0 ? $this->utilityLibObj->changeArrayKey(json_decode(json_encode($patientconsultant),true), 'pcio_type_id'):[];
        
            $staticDataKey              = $this->staticDataObj->getStaticDataConfigList()['consultant_impression_list'];
            $staticDataArrWithCustomKey = $this->utilityLibObj->changeArrayKey($staticDataKey, 'id');
            $insertData = [];
            $insertDataPlace = [];
            if(!empty($staticDataArrWithCustomKey)){
                foreach ($staticDataArrWithCustomKey as $pcioTypeIdKey => $pcioValue) {
                    $pltTypeIdEncrypted = $this->securityLibObj->encrypt($pcioTypeIdKey);
                    $temp = [];
                    $pcioTypeValue = isset($requestData['consultant_'.$pltTypeIdEncrypted]) ? $requestData['consultant_'.$pltTypeIdEncrypted] : '';
                    $temp = [
                            'pat_id'    =>  $requestData['pat_id'],
                            'visit_id'  =>  $requestData['visit_id'],
                            'pcio_type_id'  =>  $pcioTypeIdKey,
                            'pcio_value'  =>  $pcioTypeValue,
                            'ip_address'  =>  $requestData['ip_address'],
                            'resource_type'  =>  $requestData['resource_type'],
                    ];
                    $pcioId = (isset($consultantRecordWithFectorKey[$pcioTypeIdKey]['pcio_id']) && 
                                !empty($consultantRecordWithFectorKey[$pcioTypeIdKey]['pcio_id']) )
                                ? $this->securityLibObj->decrypt($consultantRecordWithFectorKey[$pcioTypeIdKey]['pcio_id']) : '';
                   if(array_key_exists($pcioTypeIdKey, $consultantRecordWithFectorKey) && !empty($pcioId)){
                        $whereData =[];
                        $whereData = [
                            'pat_id'    =>  $requestData['pat_id'],
                            'visit_id'  =>  $requestData['visit_id'],
                            'pcio_id'  =>  $pcioId,
                        ];
                        $updateData = $this->consultantModelObj->updateConsultant($temp,$whereData);
                        if(!$updateData){
                            $dataDbStatus = true;
                            $dbCommitStatus = false;
                            break;
                        }else{
                            $dbCommitStatus = true;
                        }
                   }
                   if(!empty($pcioTypeValue) && empty($pcioId)){
                        $insertData[] = $temp;
                   }
                }

                if(isset($dataDbStatus) && $dataDbStatus){
                    DB::rollback();
                    return $this->resultResponse(
                        Config::get('restresponsecode.ERROR'), 
                        [], 
                        (isset($errorResponseArray) ? $errorResponseArray:[]),
                        (isset($errorResponseString) ? $errorResponseString :'').trans('Visits::messages.consultant_add_fail'), 
                        $this->http_codes['HTTP_OK']
                    );
                }
                if(!empty($insertData)){
                    $addData = $this->consultantModelObj->addConsultant($insertData);
                    if(!$addData){
                        DB::rollback();
                        return $this->resultResponse(
                            Config::get('restresponsecode.ERROR'), 
                            [], 
                            [],
                            trans('Visits::messages.consultant_add_fail'), 
                            $this->http_codes['HTTP_OK']
                        );
                    }else{
                        DB::commit();
                        $dbCommitStatus = false;
                        return $this->resultResponse(
                            Config::get('restresponsecode.SUCCESS'), 
                            [], 
                            [],
                            trans('Visits::messages.consultant_add_success'), 
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
                        trans('Visits::messages.consultant_add_success'), 
                        $this->http_codes['HTTP_OK']
                    );
                }
            }else{
                 DB::rollback();
                        return $this->resultResponse(
                            Config::get('restresponsecode.SUCCESS'), 
                            [], 
                            [],
                            trans('Visits::messages.consultant_add_fail'), 
                            $this->http_codes['HTTP_OK']
                        );
            }
        } catch (\Exception $ex) {
            DB::rollback();
            $eMessage = $this->exceptionLibObj->reFormAndLogException($ex,'ConsultantController', 'addUpdateConsultant');
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
