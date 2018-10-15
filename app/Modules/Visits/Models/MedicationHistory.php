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
 * MedicationHistory
 *
 * @package                ILD
 * @subpackage             MedicationHistory
 * @category               Model
 * @DateOfCreation         11 june 2018
 * @ShortDescription       This Model to handle database operation with current table
                           City
 **/
class MedicationHistory extends Model {

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
    protected $table                        = 'patient_medication_history';
    protected $tableMedicines               = 'medicines';
    protected $tablePatientVisit            = 'patients_visits';
    protected $tableDoctorPatientRelation   = 'doctor_patient_relation';
    protected $tableDrugType                = 'drug_type';
    protected $tableDrugDoseUnit            = 'drug_dose_unit';
    
    // @var Array $encryptedFields
    // This protected member contains fields that need to encrypt while saving in database
    protected $encryptable = [];
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['pat_id','visit_id','medicine_id','medicine_start_date','medicine_end_date','medicine_dose','medicine_dose_unit','resource_type','ip_address'];

    /**
     *@ShortDescription Override the primary key.
     *
     * @var string
     */
    protected $primaryKey = 'pmh_id';

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
    public function getMeasurementTypeData(){
        $res = $this->staticDataConfigObj->getDoseMeasurementTypeData();
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
     * @DateOfCreation        18 June 2018
     * @ShortDescription      This function is responsible to get the Patient Medication History data
     * @param                 array $requestData patId, $visitId  
     * @return                object Array of Patient Medication History records
     */
    public function getListData($requestData) {

        $patId   = $this->securityLibObj->decrypt($requestData['patId']);
        $visitId = $this->securityLibObj->decrypt($requestData['visitId']);
        $userId  = $requestData['userId'];
        $measurementTypeData = $this->getMeasurementTypeData();

        $visitType = $this->getVisitType($patId, $visitId);
        $query = DB::table($this->table)
                        ->leftJoin($this->tableMedicines, function($join) use ($visitId){
                                $join->on($this->table.'.medicine_id', '=', $this->tableMedicines.'.medicine_id')
                                ->where($this->tableMedicines.'.is_deleted',Config::get('constants.IS_DELETED_NO'),'AND');
                            })
                        ->join($this->tablePatientVisit,function($join) {
                                $join->on($this->tablePatientVisit.'.visit_id', '=', $this->table.'.visit_id')
                                ->where($this->tablePatientVisit.'.is_deleted', '=', Config::get('constants.IS_DELETED_NO'), 'and');
                            });
        if($patId != $userId){
            $query = $query->join($this->tableDoctorPatientRelation,function($join) use($patId, $userId) {
                                $join->on($this->tableDoctorPatientRelation.'.pat_id', '=', $this->tablePatientVisit.'.pat_id', 'and')
                                ->where($this->tableDoctorPatientRelation.'.user_id', '=', $userId)
                                ->where($this->tableDoctorPatientRelation.'.is_deleted', '=', Config::get('constants.IS_DELETED_NO'), 'and');
                            });
        }
            $query = $query->leftJoin($this->tableDrugDoseUnit, function($join) {
                            $join->on($this->table.'.medicine_dose_unit', '=', $this->tableDrugDoseUnit.'.drug_dose_unit_id')
                                ->where($this->tableDrugDoseUnit.'.is_deleted', '=', Config::get('constants.IS_DELETED_NO'), 'and');
                        })
                        ->select($this->tableMedicines.'.medicine_name',
                                $this->table.'.pmh_id', 
                                $this->table.'.pat_id', 
                                $this->table.'.visit_id', 
                                $this->table.'.medicine_id', 
                                $this->table.'.medicine_start_date', 
                                $this->table.'.medicine_end_date',
                                $this->table.'.medicine_dose', 
                                $this->table.'.medicine_dose_unit',
                                $this->tableDrugDoseUnit.'.drug_dose_unit_name'
                            )
                        ->where($this->table.'.is_deleted', Config::get('constants.IS_DELETED_NO'))
                        // ->where($this->table.'.visit_id',$visitId)
                        ->where($this->table.'.pat_id', $patId);
        
        /* Condition for Filtering the result */
        if(!empty($requestData['filtered'])){
            foreach ($requestData['filtered'] as $key => $value) {
                $query = $query->where(function ($query) use ($value){
                                $query
                                ->where($this->tableMedicines.'.medicine_name', 'ilike', "%".$value['value']."%")
                                ->orWhere($this->table.'.medicine_dose', 'ilike', '%'.$value['value'].'%');
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
            ->map(function($dataLists) use ($measurementTypeData) {
                $dataLists->pmh_id              = $this->securityLibObj->encrypt($dataLists->pmh_id);
                $dataLists->pat_id              = $this->securityLibObj->encrypt($dataLists->pat_id);
                $dataLists->medicine_id         = $this->securityLibObj->encrypt($dataLists->medicine_id);
                $dataLists->prev_medicine_id    = $dataLists->medicine_id;
                $dataLists->visit_id            = $this->securityLibObj->encrypt($dataLists->visit_id);
                $dataLists->medicine_name       = empty($dataLists->medicine_name) ? '' : $dataLists->medicine_name;
                $dataLists->medicine_end_date   = empty($dataLists->medicine_end_date) ? '' : $dataLists->medicine_end_date;
                $dataLists->medicine_dose_unit_value = !empty($dataLists->medicine_dose_unit) && isset($measurementTypeData[$dataLists->medicine_dose_unit]) ? $measurementTypeData[$dataLists->medicine_dose_unit]['value'] : '';
                $dataLists->medicine_dose_unit = empty($dataLists->medicine_dose_unit) ? '' : $this->securityLibObj->encrypt($dataLists->medicine_dose_unit);
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

    public function getVisitType($patId='', $visitId=''){
        $result = false;
        if(!empty($patId) && !empty($visitId)){
            $queryResult = DB::table($this->tablePatientVisit)
                        ->select('visit_type')
                        ->where('is_deleted', Config::get('constants.IS_DELETED_NO'))
                        ->where('visit_id',$visitId)
                        ->where('pat_id', $patId)
                        ->first();
            if($queryResult){
                $result = $queryResult->visit_type;
            }
            return $result;
        }
    }
}
