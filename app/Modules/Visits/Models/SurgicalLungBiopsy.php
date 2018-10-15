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
 * SurgicalLungBiopsy
 *
 * @package                ILD India Registry
 * @subpackage             SurgicalLungBiopsy
 * @category               Model
 * @DateOfCreation         11 june 2018
 * @ShortDescription       This Model to handle database operation of SurgicalLungBiopsy
 **/

class SurgicalLungBiopsy extends Model {

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
    protected $table         = 'patient_surgical_lung_biopsy';
    protected $tableJoin     = 'patient_surgical_lung_biopsy_factors';
    
    // This protected member contains fields that need to encrypt while saving in database
    protected $encryptable = [];
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [ 'pat_id',
                            'visit_id',
                            'pslb_date',
                            'pslb_is_happen',
                            'resource_type',
                            'ip_address'
                        ];

    protected $fillableJoin = [ 'pslb_id',
                            'pslbf_factor_id',
                            'pslbf_factor_value',
                            'resource_type',
                            'ip_address'
                        ];

    /**
     *@ShortDescription Override the primary key.
     *
     * @var string
     */
    protected $primaryKey = 'pslb_id';

    /**
     * @DateOfCreation        26 June 2018
     * @ShortDescription      This function is responsible to get the patient InvestigationAbg record
     * @param                 integer $visitId,$patientId, $encrypt   
     * @return                object Array of InvestigationAbg records
     */
    public function getSurgicalLungBiopsyByVistID($visitId,$patientId = '',$encrypt = true) 
    {       
        $onConditionLeftSide = $this->table.'.pslb_id';
        $onConditionRightSide = $this->tableJoin.'.pslb_id';
        $queryResult = DB::table($this->table)
            ->leftJoin($this->tableJoin,function($join) use ($onConditionLeftSide,$onConditionRightSide){
                $join->on($onConditionLeftSide, '=', $onConditionRightSide)
                ->where($this->tableJoin.'.is_deleted', '=', Config::get('constants.IS_DELETED_NO'), 'and');
            })
            ->select($this->table.'.pat_id', $this->table.'.visit_id', $this->table.'.pslb_date', $this->table.'.pslb_is_happen', $this->table.'.pslb_id', $this->tableJoin.'.pslbf_factor_id', $this->tableJoin.'.pslbf_factor_value', $this->tableJoin.'.pslbf_id')
            ->where($this->table.'.visit_id', $visitId)
            ->where($this->table.'.is_deleted', Config::get('constants.IS_DELETED_NO'));
        if(!empty($patientId)){
         $queryResult = $queryResult->where($this->table.'.pat_id', $patientId);
        }
        $queryResult =$queryResult->get();
        if($encrypt && !empty($queryResult)){
            $queryResult = $queryResult->map(function($dataList){ 
                $dataList->pslb_id = $this->securityLibObj->encrypt($dataList->pslb_id);
                $dataList->pat_id = $this->securityLibObj->encrypt($dataList->pat_id);
                $dataList->visit_id = $this->securityLibObj->encrypt($dataList->visit_id);
                $dataList->pslbf_factor_id = $this->securityLibObj->encrypt($dataList->pslbf_factor_id);
                return $dataList;
            });

        }
        return $queryResult;
    }

    /**
     * @DateOfCreation        18 June 2018
     * @ShortDescription      This function is responsible to save record for the symptoms
     * @param                 array $requestData   
     * @return                integer symptoms id
     */
    public function saveSurgicalLungBiopsyDate($inserData)
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
    public function updateSurgicalLungBiopsyDate($requestData,$whereData)
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
    public function updateSurgicalLungBiopsyFactor($requestData,$whereData)
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
    public function addSurgicalLungBiopsyFactor($insertData)
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
    public function saveSurgicalLungBiopsy($requestData,$visitId,$patientId){

        $formValuDataDate = $this->getSurgicalLungBiopsyByVistID($visitId,$patientId,false);
        $formValuData = !empty($formValuDataDate) ? $this->utilityLibObj->changeArrayKey($formValuDataDate,'pslbf_factor_id'): [];
        $formValuDataVisitId = !empty($formValuDataDate) ? $this->utilityLibObj->changeArrayKey($formValuDataDate,'visit_id'): [];
        $pslbDate = $requestData['pslb_date'];
        $pslbIsHappen = $requestData['pslb_is_happen'];
        $pslb_id = !empty($formValuDataVisitId) && isset($formValuDataVisitId[$visitId]) ? $formValuDataVisitId[$visitId]['pslb_id']:'';

        $staticDataFactor = $this->staticDataObj->getSurgicalLungBiopsy();
        $staticDataFactor = !empty($staticDataFactor) ? $this->utilityLibObj->changeArrayKey($staticDataFactor,'id'): [];

        if(empty($pslbDate)){
            $valueData= [];
             foreach ($staticDataFactor as $factorKey => $factorValue) {
                $factorKeyEncrypted = $this->securityLibObj->encrypt($factorKey);
                $valueData[] = isset($requestData['pslbf_factor_'.$factorKeyEncrypted]) ? $requestData['pslbf_factor_'.$factorKeyEncrypted] :'';
             }
            $valueData = array_filter($valueData);
            $pslbDate = (!empty($valueData) || !empty($pslbIsHappen) ) ? date('Y-m-d') :'';
        }else{
            $dateResponse = $this->dateTimeLibObj->covertUserDateToServerType($pslbDate,'dd/mm/YY','Y-m-d');
            if($dateResponse['code']=='5000'){
                return false;
            }
            $pslbDate = $dateResponse['result'];
        }

        if(empty($pslbDate) && empty($pslbIsHappen)){
            return true;
        }
        $temp =[];
        $temp['pat_id'] = $patientId;
        $temp['visit_id'] = $visitId;
        $temp['resource_type'] = $requestData['resource_type'];
        $temp['ip_address'] = $requestData['ip_address'];
        $temp['pslb_date'] = $pslbDate;
        $temp['pslb_is_happen'] = $pslbIsHappen;
        if(empty($pslb_id)){
            $responseData = $this->saveSurgicalLungBiopsyDate($temp);
            if(!$responseData){
                return $responseData;
            }
            $pslb_id = $responseData;
        }elseif(!empty($pslb_id) && !empty($abgDate)){
            $whereData = [];
            $whereData['pslb_id'] = $pslb_id;
            $whereData['pat_id'] = $patientId;
            $whereData['visit_id'] = $visitId;
            $responseData = $this->updateSurgicalLungBiopsyDate($temp,$whereData);
            if(!$responseData){
                return $responseData;
            }
        }
        $insertData = [];
        
        foreach ($staticDataFactor as $factorKey => $factorValue) {
            $factorKeyEncrypted = $this->securityLibObj->encrypt($factorKey);
            $pslbf_id = !empty($formValuData) && isset($formValuData[$factorKey]['pslbf_id']) ? $formValuData[$factorKey]['pslbf_id'] :'';
            $value = isset($requestData['pslbf_factor_'.$factorKeyEncrypted]) ? $requestData['pslbf_factor_'.$factorKeyEncrypted] :'';
            $temp =[];
            $temp['pslb_id'] = $pslb_id;
            $temp['pslbf_factor_id'] = $factorKey;
            $temp['pslbf_factor_value'] = $value;
            $temp['resource_type'] = $requestData['resource_type'];
            $temp['ip_address'] = $requestData['ip_address'];

            if(!empty($pslbf_id)){
                $whereData = [];
                $whereData['pslbf_id'] = $pslbf_id;
                $whereData['pslb_id'] = $pslb_id;
                $responseData = $this->updateSurgicalLungBiopsyFactor($temp,$whereData);
                if(!$responseData){
                    $dbstaus = false;
                    break;
                }else{
                    continue;
                }

            }elseif(!empty($value)){
               $insertData[] = $temp; 
            }
        }
        if(isset($dbstaus)){
            return false;
        }
        
        if(!empty($insertData)){
            $responseData = $this->addSurgicalLungBiopsyFactor($insertData);
            return $responseData;
        }
        return true;
    }

}
