<?php
namespace App\Modules\Visits\Models;
use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\HasApiTokens;
use App\Traits\Encryptable;
use Illuminate\Support\Facades\DB;
use App\Libraries\SecurityLib;
use Config;
use App\Libraries\UtilityLib;
use App\Modules\Setup\Models\StaticDataConfig;

/**
 * PastMedicationHistory
 *
 * @package                ILD
 * @subpackage             PastMedicationHistory
 * @category               Model
 * @DateOfCreation         11 june 2018
 * @ShortDescription       This Model to handle database operation with current table
                           City
 **/
class PastMedicationHistory extends Model {

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
    protected $table = 'patient_past_medication_history';
    protected $tableDisease = 'diseases';
    
    // @var Array $encryptedFields
    // This protected member contains fields that need to encrypt while saving in database
    protected $encryptable = [];
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['pat_id','visit_id','disease_id','disease_onset','disease_status','disease_duration','resource_type','ip_address', 'disease_end_date'];

    /**
     *@ShortDescription Override the primary key.
     *
     * @var string
     */
    protected $primaryKey = 'ppmh_id';

    /**
     * @DateOfCreation        18 June 2018
     * @ShortDescription      This function is responsible to save record for the Patient Medication History
     * @param                 array $requestData   
     * @return                integer Patient Medication History id
     */
    public function getTableName()
    {
        return $this->table;
    }

    /**
     * @DateOfCreation        18 June 2018
     * @ShortDescription      This function is responsible to save record for the Patient Medication History
     * @param                 array $requestData   
     * @return                integer Patient Medication History id
     */
    public function getTablePrimaryIdColumn()
    {
        return $this->primaryKey;
    }

    /**
     * @DateOfCreation        18 June 2018
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
     * @DateOfCreation        11 June 2018
     * @ShortDescription      This function is responsible to get MeasurementType in static config data
     * @param                 Array  $requestData
     * @return                Array of status
     */
    public function getGeneralCheckupDurationData(){
        $res = $this->staticDataConfigObj->getGeneralCheckupDurationData();
        $res = $this->utilityLibObj->changeArrayKey(json_decode(json_encode($res),true),'id');
        return $res;

    }

    /**
     * @DateOfCreation        11 June 2018
     * @ShortDescription      This function is responsible to get ActiveInactiveData in static config data
     * @param                 Array  $requestData
     * @return                Array of status
     */
    public function getActiveInactiveData(){
        $res = $this->staticDataConfigObj->getActiveInactiveData();
        $res = $this->utilityLibObj->changeArrayKey(json_decode(json_encode($res),true),'id');
        return $res;

    }

    /**
     * @DateOfCreation        11 June 2018
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
     * @DateOfCreation        11 June 2018
     * @ShortDescription      This function is responsible to update Patient Medication History data
     * @param                 Array  $requestData
     * @return                Array of status
     */
    public function getDiseaseListData()
    {
        $diseaseData = DB::table($this->tableDisease)
                            ->select('disease_id', 'disease_name')
                            ->where('is_deleted', Config::get('constants.IS_DELETED_NO'))
                            ->get()
                            ->map(function($diseaselist){
                            $diseaselist->disease_id = $this->securityLibObj->encrypt($diseaselist->disease_id);
                            return $diseaselist;
                        });
        return $diseaseData;
    }


    /**
     * @DateOfCreation        18 June 2018
     * @ShortDescription      This function is responsible to get the Patient Medication History data
     * @param                 array $requestData patId, $visitId  
     * @return                object Array of Patient Medication History records
     */
    public function getListData($requestData) {

        $patId       = $this->securityLibObj->decrypt($requestData['patId']);
        $visitId     = $this->securityLibObj->decrypt($requestData['visitId']);
        $durationDataType = $this->getGeneralCheckupDurationData();
        $statusDataType = $this->getActiveInactiveData();

        $query = DB::table($this->table)
                        ->leftJoin($this->tableDisease, function($join) use ($visitId){
                                $join->on($this->table.'.disease_id', '=', $this->tableDisease.'.disease_id')
                                ->where($this->tableDisease.'.is_deleted',Config::get('constants.IS_DELETED_NO'),'AND');
                            })
                            ->select($this->tableDisease.'.disease_name',
                                    $this->table.'.ppmh_id', 
                                    $this->table.'.pat_id', 
                                    $this->table.'.visit_id', 
                                    $this->table.'.disease_id', 
                                    $this->table.'.disease_onset', 
                                    $this->table.'.disease_duration',
                                    $this->table.'.disease_status',
                                    $this->table.'.disease_end_date',
                                    $this->table.'.created_at',
                                    'users.user_firstname',
                                    'users.user_lastname'
                                )
                            ->join('users',$this->table.'.created_by','users.user_id')
                            ->where($this->table.'.is_deleted', Config::get('constants.IS_DELETED_NO'))
                            ->where($this->table.'.visit_id',$visitId)
                            ->where($this->table.'.pat_id', $patId);
        
        /* Condition for Filtering the result */
        if(!empty($requestData['filtered'])){
            foreach ($requestData['filtered'] as $key => $value) {
                $query = $query->where(function ($query) use ($value){
                                $query
                                ->where($this->tableDisease.'.disease_name', 'ilike', "%".$value['value']."%")
                                ->orWhere($this->table.'.disease_onset', 'ilike', '%'.$value['value'].'%');
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
            ->map(function($dataLists) use ($durationDataType,$statusDataType) {
                $dataLists->ppmh_id = $this->securityLibObj->encrypt($dataLists->ppmh_id);
                $dataLists->pat_id = $this->securityLibObj->encrypt($dataLists->pat_id);
                $dataLists->disease_id = $this->securityLibObj->encrypt($dataLists->disease_id);
                $dataLists->visit_id = $this->securityLibObj->encrypt($dataLists->visit_id);
                $dataLists->disease_name = empty($dataLists->disease_name) ? '' : $dataLists->disease_name;
                $dataLists->disease_onset = empty($dataLists->disease_onset) ? '' : $dataLists->disease_onset;
                
                $dataLists->disease_status_value = !empty($dataLists->disease_status) && isset($statusDataType[$dataLists->disease_status]) ? $statusDataType[$dataLists->disease_status]['value'] : '';


                $dataLists->disease_duration_value = !empty($dataLists->disease_duration) && isset($durationDataType[$dataLists->disease_duration]) ? $durationDataType[$dataLists->disease_duration]['value'] : '';
                return $dataLists;
            });
        
        return $queryResult;

    }

    /**
     * @DateOfCreation        21 June 2018
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
     * @DateOfCreation        11 June 2018
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
}
