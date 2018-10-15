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
 * HRCT
 *
 * @package                ILD India Registry
 * @subpackage             HRCT
 * @category               Model
 * @DateOfCreation         11 june 2018
 * @ShortDescription       This Model to handle database operation of HRCT
 **/

class HRCT extends Model {

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
    protected $table         = 'patient_hrct';
    protected $tableJoin     = 'patient_hrct_factors';
    
    // This protected member contains fields that need to encrypt while saving in database
    protected $encryptable = [];
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [ 'pat_id',
                            'visit_id',
                            'phrct_date',
                            'phrct_report',
                            'resource_type',
                            'ip_address'
                        ];

    protected $fillableJoin = [ 'phrct_id',
                            'phrct_factor_id',
                            'phrct_factor_value',
                            'resource_type',
                            'ip_address'
                        ];

    /**
     *@ShortDescription Override the primary key.
     *
     * @var string
     */
    protected $primaryKey = 'phrct_id';

    /**
     * @DateOfCreation        26 June 2018
     * @ShortDescription      This function is responsible to get the patient HRCT record
     * @param                 integer $visitId,$patientId, $encrypt   
     * @return                object Array of HRCT records
     */
    public function getHRCTByVistID($visitId,$patientId = '',$encrypt = true) 
    {       
        $onConditionLeftSide = $this->table.'.phrct_id';
        $onConditionRightSide = $this->tableJoin.'.phrct_id';
        $queryResult = DB::table($this->table)
            ->leftJoin($this->tableJoin,function($join) use ($onConditionLeftSide,$onConditionRightSide){
                $join->on($onConditionLeftSide, '=', $onConditionRightSide)
                ->where($this->tableJoin.'.is_deleted', '=', Config::get('constants.IS_DELETED_NO'), 'and');
            })
            ->select($this->table.'.pat_id', $this->table.'.visit_id', $this->table.'.phrct_date', $this->table.'.phrct_report', $this->table.'.phrct_id', $this->tableJoin.'.phrct_factor_id', $this->tableJoin.'.phrct_factor_value', $this->tableJoin.'.phrctf_id')
            ->where($this->table.'.visit_id', $visitId)
            ->where($this->table.'.is_deleted', Config::get('constants.IS_DELETED_NO'));
        if(!empty($patientId)){
         $queryResult = $queryResult->where($this->table.'.pat_id', $patientId);
        }
        $queryResult =$queryResult->get();
        if($encrypt && !empty($queryResult)){
            $queryResult = $queryResult->map(function($dataList){ 
                $dataList->phrct_id = $this->securityLibObj->encrypt($dataList->phrct_id);
                $dataList->pat_id = $this->securityLibObj->encrypt($dataList->pat_id);
                $dataList->visit_id = $this->securityLibObj->encrypt($dataList->visit_id);
                $dataList->phrct_factor_id = $this->securityLibObj->encrypt($dataList->phrct_factor_id);
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
    public function saveHRCTDate($inserData)
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
    public function updateHRCTDate($requestData,$whereData)
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
    public function updateHRCTFactor($requestData,$whereData)
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
    public function addHRCTFactor($insertData)
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
    public function saveHRCT($requestData,$visitId,$patientId){

        $formValuDataDate = $this->getHRCTByVistID($visitId,$patientId,false);
        $formValuData = !empty($formValuDataDate) ? $this->utilityLibObj->changeArrayKey($formValuDataDate,'phrct_factor_id'): [];
        $formValuDataVisitId = !empty($formValuDataDate) ? $this->utilityLibObj->changeArrayKey($formValuDataDate,'visit_id'): [];
        $phrctDate = $requestData['phrct_date'];
        $phrctReport = $requestData['phrct_report'];
        $phrct_id = !empty($formValuDataVisitId) && isset($formValuDataVisitId[$visitId]) ? $formValuDataVisitId[$visitId]['phrct_id']:'';

        $staticDataFactor = $this->staticDataObj->getHRCT();
        $staticDataFactor = !empty($staticDataFactor) ? $this->utilityLibObj->changeArrayKey($staticDataFactor,'id'): [];

        if(empty($phrctDate)){
            $valueData= [];
             foreach ($staticDataFactor as $factorKey => $factorValue) {
                $factorKeyEncrypted = $this->securityLibObj->encrypt($factorKey);
                $valueData[] = isset($requestData['phrct_factor_'.$factorKeyEncrypted]) ? $requestData['phrct_factor_'.$factorKeyEncrypted] :'';
             }
            $valueData = array_filter($valueData);
            $phrctDate = (!empty($valueData) || !empty($phrctReport) ) ? date('Y-m-d') :'';
        }else{
            $dateResponse = $this->dateTimeLibObj->covertUserDateToServerType($phrctDate,'dd/mm/YY','Y-m-d');
            if($dateResponse['code']=='5000'){
                return false;
            }
            $phrctDate = $dateResponse['result'];
        }

        if(empty($phrctDate) && empty($phrctReport)){
            return true;
        }
        $temp =[];
        $temp['pat_id'] = $patientId;
        $temp['visit_id'] = $visitId;
        $temp['resource_type'] = $requestData['resource_type'];
        $temp['ip_address'] = $requestData['ip_address'];
        $temp['phrct_date'] = $phrctDate;
        $temp['phrct_report'] = $phrctReport;

        if(empty($phrct_id)){
            $responseData = $this->saveHRCTDate($temp);
            if(!$responseData){
                return $responseData;
            }
            $phrct_id = $responseData;
        }elseif(!empty($phrct_id) && !empty($phrctDate)){
            $whereData = [];
            $whereData['phrct_id'] = $phrct_id;
            $whereData['pat_id'] = $patientId;
            $whereData['visit_id'] = $visitId;
            $responseData = $this->updateHRCTDate($temp,$whereData);
            if(!$responseData){
                return $responseData;
            }
        }
        $insertData = [];
        
        foreach ($staticDataFactor as $factorKey => $factorValue) {
            $factorKeyEncrypted = $this->securityLibObj->encrypt($factorKey);
            $phrctf_id = !empty($formValuData) && isset($formValuData[$factorKey]['phrctf_id']) ? $formValuData[$factorKey]['phrctf_id'] :'';
            $value = isset($requestData['phrct_factor_'.$factorKeyEncrypted]) ? $requestData['phrct_factor_'.$factorKeyEncrypted] :'';
            $temp =[];
            $temp['phrct_id'] = $phrct_id;
            $temp['phrct_factor_id'] = $factorKey;
            $temp['phrct_factor_value'] = $value;
            $temp['resource_type'] = $requestData['resource_type'];
            $temp['ip_address'] = $requestData['ip_address'];

            if(!empty($phrctf_id)){
                $whereData = [];
                $whereData['phrctf_id'] = $phrctf_id;
                $whereData['phrct_id'] = $phrct_id;
                $responseData = $this->updateHRCTFactor($temp,$whereData);
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
            $responseData = $this->addHRCTFactor($insertData);
            return $responseData;
        }
        return true;
    }

}
