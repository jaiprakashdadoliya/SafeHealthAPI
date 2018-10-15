<?php
namespace App\Modules\Patients\Models;
use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\HasApiTokens;
use App\Traits\Encryptable;
use Illuminate\Support\Facades\DB;
use App\Libraries\SecurityLib;
use Config;
use App\Libraries\UtilityLib;
use App\Modules\Setup\Models\StaticDataConfig;

/**
 * PatientsAllergies
 *
 * @package                Safe health
 * @subpackage             PatientsAllergies
 * @category               Model
 * @DateOfCreation         03 August 2018
 * @ShortDescription       This Model to handle database operation with current table
                           City
 **/
class PatientsAllergies extends Model {

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
        $this->staticDataConfigObj = new StaticDataConfig();
        
    }

    /**
    *@ShortDescription Table for the Users.
    *
    * @var String
    */
    protected $table = 'patient_allergies';
    protected $tableAllergiesHistory = 'allergies_history';
    protected $allergiesMasterTable = 'allergies';

    
    // @var Array $encryptedFields
    // This protected member contains fields that need to encrypt while saving in database
    protected $encryptable = [];
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['pat_id','allergy_type','onset','onset_time','status','resource_type','ip_address'];

    /**
     *@ShortDescription Override the primary key.
     *
     * @var string
     */
    protected $primaryKey = 'pat_alg_id';

    /**
     * @DateOfCreation        03 August 2018
     * @ShortDescription      This function is responsible to save record for the Patient Medication History
     * @param                 array $requestData   
     * @return                integer Patient Medication History id
     */
    public function getTableName()
    {
        return $this->table;
    }

    /**
     * @DateOfCreation        03 August 2018
     * @ShortDescription      This function is responsible to save record for the Patient Medication History
     * @param                 array $requestData   
     * @return                integer Patient Medication History id
     */
    public function getTablePrimaryIdColumn()
    {
        return $this->primaryKey;
    }

    /**
     * @DateOfCreation        03 August 2018
     * @ShortDescription      This function is responsible to save record for the Patient Medication History
     * @param                 array $requestData   
     * @return                integer Patient Medication History id
     */
    public function addRequest($inserData)
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
     * @DateOfCreation        03 August 2018
     * @ShortDescription      This function is responsible to Allergies data in static config data
     * @param                 Array  $requestData
     * @return                Array of status
     */
    public function getAllergyTypeData(){
        $res = $this->staticDataConfigObj->getAllergiesData();
        $res = $this->utilityLibObj->changeArrayKey(json_decode(json_encode($res),true),'id');
        return $res;

    }
    /**
     * @DateOfCreation        03 August 2018
     * @ShortDescription      This function is responsible to Allergies data in static config data
     * @param                 Array  $requestData
     * @return                Array of status
     */
    public function getStatusData(){
        $res = $this->staticDataConfigObj->getActiveInactiveData();
        $res = $this->utilityLibObj->changeArrayKey(json_decode(json_encode($res),true),'id');
        return $res;

    }
    /**
     * @DateOfCreation        03 August 2018
     * @ShortDescription      This function is responsible to onset time in static config data
     * @param                 Array  $requestData
     * @return                Array of status
     */
    public function getOnsetTimeData(){
        $res = $this->staticDataConfigObj->getGeneralCheckupDurationData();
        $res = $this->utilityLibObj->changeArrayKey(json_decode(json_encode($res),true),'id');
        return $res;

    }

    
    /**
     * @DateOfCreation        03 August 2018
     * @ShortDescription      This function is responsible to update Patient Medication History data
     * @param                 Array  $requestData
     * @return                Array of status
     */
    public function updateRequest($updateData,$whereData)
    {
        
        if(isset($updateData[$this->primaryKey])){
            unset($updateData[$this->primaryKey]);
        }


        $updateData = $this->utilityLibObj->fillterArrayKey($updateData, $this->fillable);
       
        // Prepair update query
        $response = $this->dbUpdate($this->table, $updateData,$whereData);
        
        if($response){
            return isset($whereData[$this->primaryKey]) ? $whereData[$this->primaryKey] : 0;
        }
        return false;
    }

    /**
     * @DateOfCreation        03 August 2018
     * @ShortDescription      This function is responsible to get the Patient Medication History data
     * @param                 array $requestData patId, $visitId  
     * @return                object Array of Patient Medication History records
     */
    public function getListData($requestData) {

        $patId       = $this->securityLibObj->decrypt($requestData['patId']);
        $allergyTypeData = $this->getAllergyTypeData();
        $statusData      = $this->getStatusData();
        $onsetTimeData   = $this->getOnsetTimeData();
        $query = DB::table($this->table)
                            ->select($this->table.'.pat_alg_id', 
                                    $this->table.'.pat_id', 
                                    $this->table.'.allergy_type', 
                                    $this->table.'.onset', 
                                    $this->table.'.onset_time',
                                    $this->table.'.created_by',
                                    $this->table.'.created_at',
                                    $this->table.'.status',
                                    $this->allergiesMasterTable.'.allergy_name',
                                    $this->allergiesMasterTable.'.parent_id',
                                    'users.user_firstname',
                                    'users.user_lastname'
                                )
                            ->join($this->allergiesMasterTable, $this->table.'.allergy_type', $this->allergiesMasterTable.'.allergy_id')
                            ->join('users',$this->table.'.created_by','users.user_id')
                            ->where($this->table.'.is_deleted', Config::get('constants.IS_DELETED_NO'))
                            ->where($this->table.'.pat_id', $patId);
        
        /* Condition for Filtering the result */
        if(!empty($requestData['filtered'])){
            foreach ($requestData['filtered'] as $key => $value) {
                $query = $query->where(function ($query) use ($value){
                                $query
                                ->where($this->allergiesMasterTable.'.allergy_name', 'ilike', "%".$value['value']."%")
                                ->orWhere($this->table.'.onset', 'ilike', '%'.$value['value'].'%');
                            });
            }
        }

        /* Condition for Sorting the result */
        if(!empty($requestData['sorted'])){
            foreach ($requestData['sorted'] as $key => $value) {
                $orderBy = $value['desc'] ? 'desc' : 'asc';
                $query = $query->orderBy($value['id'], $orderBy);
            }
        }
        if($requestData['page'] > 0){
            $offset = $requestData['page']*$requestData['pageSize'];
        }else{
            $offset = 0;
        }
        $queryResult['pages'] = ceil($query->count()/$requestData['pageSize']);

        $queryResult['result'] = 
            $query->offset($offset)
            ->limit($requestData['pageSize'])
            ->get()
            ->map(function($dataLists) use ($allergyTypeData, $statusData, $onsetTimeData) {
                $dataLists->pat_alg_id          = $this->securityLibObj->encrypt($dataLists->pat_alg_id);
                $dataLists->allergy_type        = $this->securityLibObj->encrypt($dataLists->allergy_type);
                $dataLists->parent_allergy_type = $this->securityLibObj->encrypt($dataLists->parent_id);
                $dataLists->pat_id              = $this->securityLibObj->encrypt($dataLists->pat_id);
                $dataLists->allergy_type_value  = !empty($dataLists->allergy_type) ? $dataLists->allergy_name : '';
                $dataLists->status_value        = !empty($dataLists->allergy_type) && isset($statusData[$dataLists->status]) ? $statusData[$dataLists->status]['value'] : '';
                $dataLists->onset_time_value    = !empty($dataLists->onset_time) && isset($onsetTimeData[$dataLists->onset_time]) ? $onsetTimeData[$dataLists->onset_time]['value'] : '';
                return $dataLists;
            });
        
        return $queryResult;

    }

    /**
     * @DateOfCreation        03 August 2018
     * @ShortDescription      This function is responsible to check the Visit  wefId exist in the system or not
     * @param                 integer $wefId   
     * @return                Array of status and message
     */
    public function isPrimaryIdExist($primaryId){
        $primaryIdExist = DB::table($this->table)
                        ->where($this->primaryKey, $primaryId)
                        ->exists();
        return $primaryIdExist;
    }

    /**
     * @DateOfCreation        03 August 2018
     * @ShortDescription      This function is responsible to Delete Work Environment data
     * @param                 integer $wefId   
     * @return                Array of status and message
     */
    public function doDeleteRequest($primaryId)
    {
        $queryResult = $this->dbUpdate( $this->table, 
                                        [ 'is_deleted' => Config::get('constants.IS_DELETED_YES') ], 
                                        [$this->primaryKey => $primaryId]
                                    );

        if($queryResult){
            return true;
        }
        return false;
    }

    /**
     * @DateOfCreation        25 June 2018
     * @ShortDescription      This function is responsible to get the patient domestic fector record
     * @param                 integer $vistId   
     * @return                object Array of DomesticFactor records
     */
    public function getAllergiesHistoryRecord($vistId) 
    {        
        $queryResult = DB::table($this->tableAllergiesHistory)
            ->select('allergies_history_id', 'allergies_history_type_id', 'allergies_history_value','resource_type', 'ip_address') 
            ->where('is_deleted', Config::get('constants.IS_DELETED_NO'))
            ->where('visit_id',$vistId);
               
        $queryResult = $queryResult->get()
            ->map(function($allergiesHistoryRecord){
            $allergiesHistoryRecord->allergies_history_id = $this->securityLibObj->encrypt($allergiesHistoryRecord->allergies_history_id);
            return $allergiesHistoryRecord;
        });
        return $queryResult;

    }

   /**
    * @DateOfCreation        27 June 2018
    * @ShortDescription      This function is responsible to update Domestic Factor Record
    * @param                 Array  $requestData   
    * @return                Array of status and message
    */
    public function updateAllergiesHistory($requestData,$whereData)
    {
        $response = $this->dbUpdate($this->tableAllergiesHistory, $requestData, $whereData);
        if($response){
            return true;
        }
        return false;
    }

            /**
    * @DateOfCreation        27 June 2018
    * @ShortDescription      This function is responsible to multiple add Domestic Factor Record
    * @param                 Array  $requestData   
    * @return                Array of status and message
    */
    public function addAllergiesHistory($insertData)
    {
        $response = $this->dbBatchInsert($this->tableAllergiesHistory, $insertData);
        if($response){
            return true;
        }
        return false;
    }

}
