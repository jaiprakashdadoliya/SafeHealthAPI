<?php
namespace App\Modules\Visits\Models;
use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\HasApiTokens;
use App\Traits\Encryptable;
use Illuminate\Support\Facades\DB;
use App\Libraries\SecurityLib;
use Config;
use App\Libraries\UtilityLib;
use App\Modules\Setup\Models\Symptoms as SymptomsSetup;

/**
 * Symptoms
 *
 * @package                ILD
 * @subpackage             Symptoms
 * @category               Model
 * @DateOfCreation         11 june 2018
 * @ShortDescription       This Model to handle database operation with current table
                           City
 **/
class Symptoms extends Model {

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

        // Init Symptoms Setup model object
        $this->SymptomsSetupObj = new SymptomsSetup();
    }

    /**
    *@ShortDescription Table for the Users.
    *
    * @var String
    */
    protected $table = 'visit_symptoms';
    protected $hopiTable = 'history_of_patient_illness';
    protected $tableJoin = 'symptoms';
    
    // @var Array $encryptedFields
    // This protected member contains fields that need to encrypt while saving in database
    protected $encryptable = [];
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['pat_id','visit_id','symptom_id','since_date','comment','resource_type','ip_address'];

    protected $hopiFillable = [ 'pat_id', 
                            'visit_id',
                            'hopi_type',
                            'hopi_type_id',
                            'hopi_value',
                            'resource_type',
                            'ip_address'
                        ];

    /**
     *@ShortDescription Override the primary key.
     *
     * @var string
     */
    protected $primaryKey = 'visit_symptom_id';

    /**
     * @DateOfCreation        18 June 2018
     * @ShortDescription      This function is responsible to save record for the symptoms
     * @param                 array $requestData   
     * @return                integer symptoms id
     */
    public function addSymptom($requestData)
    {
        // @var Boolean $response
        // This variable contains insert query response
        $response = false;

        // @var Array $inserData
        // This Array contains insert data for Patient
        $inserData = $this->utilityLibObj->fillterArrayKey($requestData, $this->fillable);
                
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
     * @DateOfCreation        18 June 2018
     * @ShortDescription      This function is responsible to get the symptoms visit data
     * @param                 integer $patId   
     * @return                object Array of symptoms records
     */
    public function getSymptomsDataByPatientIdAndVistId($requestData) {

        $patId       = $this->securityLibObj->decrypt($requestData['patId']);
        $visitId     = $this->securityLibObj->decrypt($requestData['visitId']);

        $onConditionLeftSide    = $this->table.'.symptom_id';
        $onConditionRightSide   = $this->tableJoin.'.symptom_id';

        $query = DB::table($this->table)
                            ->select($this->table.'.visit_symptom_id', 
                                    $this->table.'.pat_id', 
                                    $this->table.'.visit_id', 
                                    $this->table.'.symptom_id', 
                                    $this->table.'.since_date', 
                                    $this->table.'.comment',
                                    $this->tableJoin.'.symptom_name'
                                )
                            ->join($this->tableJoin,$onConditionLeftSide, '=', $onConditionRightSide)
                            ->where($this->table.'.is_deleted', Config::get('constants.IS_DELETED_NO'))
                            ->where($this->table.'.visit_id',$visitId)
                            ->where($this->table.'.pat_id', $patId);
        
        /* Condition for Filtering the result */
        if(!empty($requestData['filtered'])){
            foreach ($requestData['filtered'] as $key => $value) {
                $query = $query->where(function ($query) use ($value){
                                $query
                                ->where($this->tableJoin.'.symptom_name', 'ilike', "%".$value['value']."%")
                                ->orWhere($this->table.'.comment', 'ilike', '%'.$value['value'].'%');
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
            ->map(function($symptomLists){
                $symptomLists->visit_symptom_id = $this->securityLibObj->encrypt($symptomLists->visit_symptom_id);
                $symptomLists->symptom_id = $this->securityLibObj->encrypt($symptomLists->symptom_id);
                $symptomLists->pat_id = $this->securityLibObj->encrypt($symptomLists->pat_id);
                $symptomLists->visit_id = $this->securityLibObj->encrypt($symptomLists->visit_id);
                $symptomLists->comment = empty($symptomLists->comment) ? '' : $symptomLists->comment;
                return $symptomLists;
            });
        
        return $queryResult;

    }

    /**
     * @DateOfCreation        11 June 2018
     * @ShortDescription      This function is responsible to get the SymptomsData by VisitSymptomId
     * @param                 integer $VisitSymptomId   
     * @return                Array of SymptomsData
     */
    public function getSymptomsDataByVisitSymptomId($visitSymptomId)
    {
        $onConditionLeftSide = $this->table.'.symptom_id';
        $onConditionRightSide = $this->tableJoin.'.symptom_id';
        return DB::table($this->table)
            ->join($this->tableJoin,$onConditionLeftSide, '=', $onConditionRightSide)
            ->select($this->table.'.visit_symptom_id', $this->table.'.pat_id', $this->table.'.visit_id', $this->table.'.symptom_id', $this->table.'.since_date', $this->table.'.comment',$this->tableJoin.'.symptom_name')
            ->where('visit_symptom_id', $visitSymptomId)
            ->first();
    }

    /**
     * @DateOfCreation        18 June 2018
     * @ShortDescription      This function is responsible to get the symptoms visit data
     * @param                 integer $symptomVisitId   
     * @return                object Array of symptoms visit records
     */
    public function getSymptomsVisitData($symptomVisitId, $encrypt = true) {
        $whereData  = ['visit_symptom_id'=> $symptomVisitId,'is_deleted'=>  Config::get('constants.IS_DELETED_NO')];
        $selectData = ['visit_symptom_id', 'pat_id', 'visit_id', 'symptom_id', 'since_date', 'comment'];
        $queryResult = $this->dbSelect($this->table, $selectData, $whereData);
        if($encrypt){
            $queryResult->visit_symptom_id = $this->securityLibObj->encrypt($queryResult->visit_symptom_id);
            $queryResult->pat_id = $this->securityLibObj->encrypt($queryResult->pat_id);
            $queryResult->visit_id = $this->securityLibObj->encrypt($queryResult->visit_id);
            $queryResult->symptom_id = $this->securityLibObj->encrypt($queryResult->symptom_id);
        }
        return $queryResult;
    }

    /**
     * @DateOfCreation        18 June 2018
     * @ShortDescription      This function is responsible to get the symptoms visit data
     * @param                 integer $symptomVisitId   
     * @return                object Array of symptoms visit records
     */
    public function getSymptomsDataBySymptomName($symptomName) {
        $symptomName = trim($symptomName);
        $queryResult = DB::table($this->tableJoin)
                    ->select('symptom_id')
                    ->where('symptom_name', 'ILIKE', $symptomName)
                    ->where('is_deleted',Config::get('constants.IS_DELETED_NO'))
                    ->first();
        return $queryResult;
    }

    /**
     * @DateOfCreation        11 June 2018
     * @ShortDescription      This function is responsible to insert master Symptom data if Symptom name not exists
     * @param                 Array  $requestData
     * @return                Array of id
     */
    public function createSymptomId($requestData) {
        $symptomName = trim($requestData['symptom_name']);
        $symptomSelectId = isset($requestData['symptom_id_select']) ? trim($requestData['symptom_id_select']) :'';
        $symptomId = trim($requestData['symptom_id']);
        $symptomId = !empty($symptomId) && !is_numeric($symptomId) ? $this->securityLibObj->decrypt($symptomId) : $symptomId; 
        $symptomSelectId = !empty($symptomSelectId) && !is_numeric($symptomSelectId) ? $this->securityLibObj->decrypt($symptomSelectId) : $symptomSelectId;
        $resultSymptom = $this->getSymptomsDataBySymptomName($symptomName);
        if(!empty($resultSymptom) && isset($resultSymptom->symptom_id)){
            return $resultSymptom->symptom_id;
        }else if(!empty($symptomSelectId)){
            $symptomIds = explode('_', $symptomSelectId);
            $requestData['snomedct_concept_id'] = $symptomIds[0];
            $requestData['snomedct_id'] = $symptomIds[1];
            $filldata = ['symptom_name','ip_address','resource_type','snomedct_concept_id','snomedct_id'];
            $inserData = $this->utilityLibObj->fillterArrayKey($requestData, $filldata);
            $response = $this->dbInsert($this->tableJoin, $inserData);
            if($response){
                $id = DB::getPdo()->lastInsertId();
                return $id;
            }else{
                return $response;
            }  
        }else{
            return false;
        }

    }

    /**
     * @DateOfCreation        11 June 2018
     * @ShortDescription      This function is responsible to update visit Symptom data
     * @param                 Array  $requestData
     * @return                Array of status
     */
    public function updateSymptom($requestData)
    {
        $visitSymptomId = $requestData['visit_symptom_id'];
        unset($requestData['visit_symptom_id']);

        $updateData = $this->utilityLibObj->fillterArrayKey($requestData, $this->fillable);
        $whereData = ['visit_symptom_id' => $visitSymptomId];
        // Prepair update query
        $response = $this->dbUpdate($this->table, $updateData,$whereData);
        
        if($response){
            return $visitSymptomId;
        }
        return false;
    }

    /**
     * @DateOfCreation        21 June 2018
     * @ShortDescription      This function is responsible to check the Visit  Symptom exist in the system or not
     * @param                 Array $visitSymptomId   
     * @return                Array of status and message
     */
    public function isVisitSymptomIdExist($visitSymptomId){
        $visitSymptomIdExist = DB::table($this->table)
                        ->where('visit_symptom_id', $visitSymptomId)
                        ->exists();
        return $visitSymptomIdExist;
    }

    /**
     * @DateOfCreation        11 June 2018
     * @ShortDescription      This function is responsible to Delete visit Symptom data
     * @param                 Array $visitSymptomId   
     * @return                Array of status and message
     */
    public function doDeletesymptom($visitSymptomId)
    {
        $queryResult = $this->dbUpdate( $this->table, 
                                        [ 'is_deleted' => Config::get('constants.IS_DELETED_YES') ], 
                                        ['visit_symptom_id' => $visitSymptomId]
                                    );

        if($queryResult){
            return true;
        }
        return false;
    }

    /**
     * @DateOfCreation        16 July 2018
     * @ShortDescription      This function is responsible to get the Symptoms Name by visit id
     * @param                 integer $VisitSymptomId   
     * @return                Array of SymptomsData
     */
    public function getVisitSymptomsByVisitId($visitId)
    {
        $onConditionLeftSide = $this->table.'.symptom_id';
        $onConditionRightSide = $this->tableJoin.'.symptom_id';
        $symptomData = DB::table($this->table)
            ->join($this->tableJoin,$onConditionLeftSide, '=', $onConditionRightSide)
            ->select("symptom_name")
            ->where('visit_id', $visitId)
            ->where( $this->table.'.is_deleted', Config::get('constants.IS_DELETED_NO'))
            ->groupBy($this->tableJoin.'.symptom_id')
            ->get();
        return $symptomData;
    }

    /**
     * @DateOfCreation        25 June 2018
     * @ShortDescription      This function is responsible to get the patient domestic fector record
     * @param                 integer $vistId   
     * @return                object Array of DomesticFactor records
     */
    public function getPatientSymptomsTestRecord($vistId) 
    {        
        $queryResult = DB::table($this->hopiTable)
            ->select('hopi_id','hopi_type', 'hopi_type_id', 'hopi_value','resource_type', 'ip_address') 
            ->where('is_deleted', Config::get('constants.IS_DELETED_NO'))
            ->where('visit_id',$vistId);
               
        $queryResult = $queryResult->get()
            ->map(function($symptomsTestRecord){
            $symptomsTestRecord->hopi_id = $this->securityLibObj->encrypt($symptomsTestRecord->hopi_id);
            return $symptomsTestRecord;
        });
        return $queryResult;
    }

    /**
    * @DateOfCreation        27 June 2018
    * @ShortDescription      This function is responsible to update Domestic Factor Record
    * @param                 Array  $requestData   
    * @return                Array of status and message
    */
    public function updatePatientSymptomsTest($requestData,$whereData)
    {
        $updateData = $this->utilityLibObj->fillterArrayKey($requestData, $this->hopiFillable);
        $response = $this->dbUpdate($this->hopiTable, $updateData, $whereData);
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
    public function addPatientSymptomsTest($insertData)
    {
        $response = $this->dbBatchInsert($this->hopiTable, $insertData);
        if($response){
            return true;
        }
        return false;
    }

    /**
     * @DateOfCreation        04 Oct 2018
     * @ShortDescription      This function is responsible to get symptoms list
     * @param                 NULL
     * @return                Array of status and message
     */
    public function getSymptomsList($select = array(), $where = array())
    {
        $queryResult = DB::table('symptoms')
            ->select($select) 
            ->where($where);
               
        $queryResult = $queryResult->get()
                                    ->map(function($symptomsList){
                                        $symptomsList->symptom_id = $this->securityLibObj->encrypt($symptomsList->symptom_id);
                                        return $symptomsList;
                                    });
        return $queryResult;       
    }

    /**
     * @DateOfCreation        08 Sept 2018
     * @ShortDescription      This function is to get the Primary key name
     * @return                integer primary key name id
     */
    public function getTablePrimaryIdColumn()
    {
        return $this->primaryKey;
    }
    
    /**
     * @DateOfCreation        08 Sept 2018
     * @ShortDescription      This function is responsible to check the primary value exist in the system or not
     * @param                 integer $primaryId   
     * @return                boolean
     */
    public function isPrimaryIdExist($primaryId){
        $primaryIdExist = DB::table($this->table)
                        ->where($this->primaryKey, $primaryId)
                        ->exists();
        return $primaryIdExist;
    }
}
