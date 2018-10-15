<?php
namespace App\Modules\Visits\Models;
use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\HasApiTokens;
use App\Traits\Encryptable;
use App\Libraries\SecurityLib;
use Config;
use App\Libraries\UtilityLib;
use App\Libraries\DateTimeLib;
use App\Modules\Setup\Models\StaticDataConfig;
use DB;

/**
 * FiberopticBronchoscopy
 *
 * @package                ILD India Registry
 * @subpackage             FiberopticBronchoscopy
 * @category               Model
 * @DateOfCreation         11 june 2018
 * @ShortDescription       This Model to handle database operation of FiberopticBronchoscopy
 **/

class FiberopticBronchoscopy extends Model {

    use HasApiTokens,Encryptable;

    /**
     * Create a new model instance.
     *
     * @return void
     */
    public function __construct()
    {
        // Init security library object
        $this->securityLibObj = new SecurityLib();

        // Init exception library object
        $this->utilityLibObj = new UtilityLib();

        // Init dateTimeLibObj library object
        $this->dateTimeLibObj = new DateTimeLib();

        // Init StaticDataConfig model object
        $this->staticDataObj = new StaticDataConfig();
    }
    /**
    *@ShortDescription Table for the Users.
    *
    * @var String
    */
    protected $table         = 'patient_fiberoptic_bronchoscopy';
    protected $tableJoin     = 'patient_fiberoptic_bronchoscopy_detail';
    
    // This protected member contains fields that need to encrypt while saving in database
    protected $encryptable = [];
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [ 'pat_id',
                            'visit_id',
                            'pfb_date',
                            'pfb_is_happen',
                            'resource_type',
                            'ip_address'
                        ];

    protected $fillableJoin = [ 'pfb_id',
                            'pfbd_test_id',
                            'pfbd_type',
                            'pfbd_value',
                            'pfbd_per_suggestive',
                            'resource_type',
                            'ip_address'
                        ];

    /**
     *@ShortDescription Override the primary key.
     *
     * @var string
     */
    protected $primaryKey = 'pfb_id';

    /**
     * @DateOfCreation        26 June 2018
     * @ShortDescription      This function is responsible to get the patient HRCT record
     * @param                 integer $visitId,$patientId, $encrypt   
     * @return                object Array of HRCT records
     */
    public function getFiberopticBronchoscopyByVistID($visitId,$patientId,$encrypt = true) 
    {       
        $onConditionLeftSide = $this->table.'.pfb_id';
        $onConditionRightSide = $this->tableJoin.'.pfb_id';
        $queryResult = DB::table($this->table)
            ->leftJoin($this->tableJoin,function($join) use ($onConditionLeftSide,$onConditionRightSide){
                $join->on($onConditionLeftSide, '=', $onConditionRightSide)
                ->where($this->tableJoin.'.is_deleted', '=', Config::get('constants.IS_DELETED_NO'), 'and');
            })
            ->select($this->table.'.pat_id', $this->table.'.visit_id', $this->table.'.pfb_date', $this->table.'.pfb_is_happen', $this->table.'.pfb_id', $this->tableJoin.'.pfbd_test_id', $this->tableJoin.'.pfbd_type',$this->tableJoin.'.pfbd_value',$this->tableJoin.'.pfbd_per_suggestive', $this->tableJoin.'.pfbd_id',DB::raw("CONCAT(patient_fiberoptic_bronchoscopy_detail.pfbd_test_id,'_', patient_fiberoptic_bronchoscopy_detail.pfbd_type ,'_', patient_fiberoptic_bronchoscopy_detail.pfbd_value) AS factor_id_value"),DB::raw("CONCAT(patient_fiberoptic_bronchoscopy_detail.pfbd_test_id,'_', patient_fiberoptic_bronchoscopy_detail.pfbd_type) AS factor_id"))
            ->where($this->table.'.visit_id', $visitId)
            ->where($this->table.'.is_deleted', Config::get('constants.IS_DELETED_NO'));
        
         $queryResult = $queryResult->where($this->table.'.pat_id', $patientId);
        
        $queryResult =$queryResult->get();
        if($encrypt && !empty($queryResult)){
            $queryResult = $queryResult->map(function($dataList){ 
                $dataList->pfb_id = $this->securityLibObj->encrypt($dataList->pfb_id);
                $dataList->pat_id = $this->securityLibObj->encrypt($dataList->pat_id);
                $dataList->visit_id = $this->securityLibObj->encrypt($dataList->visit_id);
                $dataList->pfbd_value = $this->securityLibObj->encrypt($dataList->pfbd_value);
                return $dataList;
            });

        }
        return $queryResult;
    }

    /**
     * @DateOfCreation        18 June 2018
     * @ShortDescription      This function is responsible to save record for the symptoms
     * @param                 array $requestData   
     * @return                integer hrct id
     */
    public function saveFiberopticBronchoscopyDate($inserData)
    {
        // @var Boolean $response
        // This variable contains insert query response
        $response = false;

        // @var Array $inserData
        // This Array contains insert data for Patient
        $inserData = $this->utilityLibObj->fillterArrayKey($inserData, $this->fillable);
                
        // Prepair insert query
        $response = $this->dbInsert($this->table, $inserData);
        if($response){
            $id = DB::getPdo()->lastInsertId();
            return $id;
            
        }else{
            return $response;
        }          
    }

    /**
    * @DateOfCreation        27 June 2018
    * @ShortDescription      This function is responsible to update Social Addiction Record
    * @param                 Array  $requestData   
    * @return                Array of status and message
    */
    public function updateFiberopticBronchoscopyDate($requestData,$whereData)
    {
        $updateData = $this->utilityLibObj->fillterArrayKey($requestData, $this->fillable);
        $response = $this->dbUpdate($this->table, $updateData, $whereData);
        if($response){
            return true;
        }
        return false;
    }

    /**
    * @DateOfCreation        27 June 2018
    * @ShortDescription      This function is responsible to update Social Addiction Record
    * @param                 Array  $requestData   
    * @return                Array of status and message
    */
    public function updateFiberopticBronchoscopyFactor($requestData,$whereData)
    {
        $updateData = $this->utilityLibObj->fillterArrayKey($requestData, $this->fillableJoin);
        $response = $this->dbUpdate($this->tableJoin, $updateData, $whereData);
        if($response){
            return true;
        }
        return false;
    }

    /**
    * @DateOfCreation        27 June 2018
    * @ShortDescription      This function is responsible to multiple add Social Addiction Record
    * @param                 Array  $requestData   
    * @return                Array of status and message
    */
    public function addFiberopticBronchoscopyFactor($insertData)
    {
        $response = $this->dbBatchInsert($this->tableJoin, $insertData);
        if($response){
            return true;
        }
        return false;
    }

    /**
     * @DateOfCreation        10 July 2018
     * @ShortDescription      This function is responsible to call model function for save Hospitalizations Extra Info
     * @param                 array $data   
     * @return                boolean true / false
     */
    public function saveFiberopticBronchoscopy($requestData,$visitId,$patientId){

        $formValuDataDate = $this->getFiberopticBronchoscopyByVistID($visitId,$patientId,false);
        $formValuData = !empty($formValuDataDate) ? $this->utilityLibObj->changeArrayKey($formValuDataDate,'factor_id'): [];
        $formValuDataType = !empty($formValuDataDate) ? $this->utilityLibObj->changeArrayKey($formValuDataDate,'factor_id_value'): [];
        $formValuDataVisitId = !empty($formValuDataDate) ? $this->utilityLibObj->changeArrayKey($formValuDataDate,'visit_id'): [];
        $pfbDate = $requestData['pfb_date'];
        $pfbHappen = $requestData['pfb_is_happen'];
        $staticDataFactor = $this->staticDataObj->getFiberopticBronchoscopyType();
        $dataValue = [];
        foreach ($staticDataFactor as $keyType => $valueType) {
            $encryptType = $this->securityLibObj->encrypt($valueType['type']);
            $encryptId = $this->securityLibObj->encrypt($valueType['id']);
            $tempName = 'suggestive_value_'.$encryptType.'_'.$encryptId;
            foreach ($valueType['option'] as $key => $value) {
               $findValueById = $tempName.'_'.$this->securityLibObj->encrypt($value['id']);
               $findCustomValueById = 'custom_'.$tempName.'_'.$this->securityLibObj->encrypt($value['id']);
               $temp = [];
               $temp['resource_type'] = $requestData['resource_type'];
               $temp['ip_address'] = $requestData['ip_address'];
               $temp['pfbd_test_id'] = $valueType['id'];
               $temp['pfbd_type'] = $valueType['type'];
               if($valueType['type'] != '1' ){
                
               $pfbd_id = !empty($formValuData) && isset($formValuData[$temp['pfbd_test_id'].'_'.$temp['pfbd_type']]['pfbd_id']) ? $formValuData[$temp['pfbd_test_id'].'_'.$temp['pfbd_type']]['pfbd_id'] : '';
               }else{
                
               $pfbd_id = !empty($formValuDataType) && isset($formValuDataType[$temp['pfbd_test_id'].'_'.$temp['pfbd_type'].'_'.$value['id']]['pfbd_id']) ? $formValuDataType[$temp['pfbd_test_id'].'_'.$temp['pfbd_type'].'_'.$value['id']]['pfbd_id'] : '';
               }
               $pfbdValue = isset($requestData[$findCustomValueById]) && !empty($requestData[$findCustomValueById]) ? $requestData[$findCustomValueById] :'';
               $suggestiveValue = isset($requestData[$findValueById]) && !empty($requestData[$findValueById]) ? $requestData[$findValueById] :'';
               $temp['pfbd_value'] = $valueType['type'] == '1' ? $value['id'] : $pfbdValue;
               $temp['pfbd_per_suggestive'] =  $suggestiveValue;
               if(!empty($pfbd_id)){
                $temp['pfbd_id'] = $pfbd_id;
               }
               if((!empty($suggestiveValue) ||!empty($pfbdValue)) ||(!empty($pfbd_id))){
                    $dataValue[] = $temp;
               }
            }
        }

        if(empty($pfbDate)){
            $pfbDate = null;
        }else{
            $dateResponse = $this->dateTimeLibObj->covertUserDateToServerType($pfbDate,'dd/mm/YY','Y-m-d');
            if($dateResponse['code']=='5000'){
                return false;
            }
            $pfbDate = $dateResponse['result'];
        }

        $pfb_id = !empty($formValuDataVisitId) && isset($formValuDataVisitId[$visitId]['pfb_id']) ? $formValuDataVisitId[$visitId]['pfb_id'] : '';
        if(empty($pfb_id) && empty($dataValue) && empty($pfbDate) && empty($pfbHappen)){
            return true;
        }
        $temp =[];
        $temp['pat_id'] = $patientId;
        $temp['visit_id'] = $visitId;
        $temp['resource_type'] = $requestData['resource_type'];
        $temp['ip_address'] = $requestData['ip_address'];
        $temp['pfb_date'] = $pfbDate;
        $temp['pfb_is_happen'] = $pfbHappen;

        if(empty($pfbd_id) && ((!empty($dataValue)) || (!empty($pfbDate) || !empty($pfbHappen)))){
            $responseData = $this->saveFiberopticBronchoscopyDate($temp);
            if(!$responseData){
                return $responseData;
            }
            $pfb_id = $responseData;
        }elseif (!empty($pfb_id)) {
            $whereData = [];
            $whereData['pfb_id'] = $pfb_id;
            $whereData['pat_id'] = $patientId;
            $whereData['visit_id'] = $visitId;
            $responseData = $this->updateFiberopticBronchoscopyDate($temp,$whereData);
            if(!$responseData){
                return $responseData;
            }
        }

        $insertData = [];
        if(!empty($dataValue)){
            foreach ($dataValue as $key => $value) {
                $value['pfb_id'] = $pfb_id;
                if(isset($value['pfbd_id'])){
                    $whereData = [];
                    $whereData['pfb_id'] =  $pfb_id;
                    $whereData['pfbd_id'] = $value['pfbd_id'];
                    unset($value['pfbd_id']);
                    $responseData = $this->updateFiberopticBronchoscopyFactor($value,$whereData);
                    if(!$responseData){
                        $dbstaus = false;
                        break;
                    }

                }else{
                   $insertData [] = $value;
                }
            }
        }

        if(isset($dbstaus)){
            return false;
        }
        
        if(!empty($insertData)){
            $responseData = $this->addFiberopticBronchoscopyFactor($insertData);
            return $responseData;
        }
        return true;
    }

}
