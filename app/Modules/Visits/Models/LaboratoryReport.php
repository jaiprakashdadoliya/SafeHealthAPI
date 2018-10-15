<?php
namespace App\Modules\Visits\Models;
use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\HasApiTokens;
use App\Traits\Encryptable;
use Illuminate\Support\Facades\DB;
use App\Libraries\SecurityLib;
use Config;
use App\Libraries\UtilityLib;
use App\Libraries\DateTimeLib;
use Response;

/**
 * LaboratoryReport
 *
 * @package                ILD
 * @subpackage             LaboratoryReport
 * @category               Model
 * @DateOfCreation         11 june 2018
 * @ShortDescription       This Model to handle database operation with current table
                           City
 **/
class LaboratoryReport extends Model {

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

        // Init DateTimeLib library object
        $this->dateTimeLibObj = new DateTimeLib();
        
    }

    /**
    *@ShortDescription Table for the Users.
    *
    * @var String
    */
    protected $table = 'laboratory_report';
    protected $tableLabTemplate = 'laboratory_templates';
    protected $tablePatientsVisitDiagnosis = 'patients_visit_diagnosis';
    protected $tableVisitSymptoms = 'visit_symptoms';
    
    // @var Array $encryptedFields
    // This protected member contains fields that need to encrypt while saving in database
    protected $encryptable = [];
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['pat_id','visit_id','lab_report_name','lab_report_file','resource_type','ip_address'];

    /**
     *@ShortDescription Override the primary key.
     *
     * @var string
     */
    protected $primaryKey = 'lr_id';

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

        $patId       = $this->securityLibObj->decrypt($requestData['patId']);
        $visitId     = $this->securityLibObj->decrypt($requestData['visitId']);
        $oldTimeZone = Config::get('app.database_timezone');
        $newTimeZone = date_default_timezone_get();
        $Format = 'Y-m-d H:i:s';

        $query = DB::table($this->table)
                            ->select($this->table.'.lr_id', 
                                    $this->table.'.pat_id', 
                                    $this->table.'.visit_id', 
                                    $this->table.'.lab_report_name', 
                                    $this->table.'.lab_report_file', 
                                    $this->table.'.created_at' 
                                )
                            ->where($this->table.'.is_deleted', Config::get('constants.IS_DELETED_NO'))
                            ->where($this->table.'.visit_id',$visitId)
                            ->where($this->table.'.pat_id', $patId);
        
        /* Condition for Filtering the result */
        if(!empty($requestData['filtered'])){
            foreach ($requestData['filtered'] as $key => $value) {
                $query = $query->where(function ($query) use ($value){
                                $query
                                ->where($this->table.'.lab_report_name', 'ilike', "%".$value['value']."%");
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
            ->map(function($dataLists) use ($oldTimeZone,$newTimeZone,$Format){
                $dataLists->lr_id = $this->securityLibObj->encrypt($dataLists->lr_id);
                $dataLists->pat_id = $this->securityLibObj->encrypt($dataLists->pat_id);
                $dataLists->visit_id = $this->securityLibObj->encrypt($dataLists->visit_id);
                $dataLists->lab_report_name = empty($dataLists->lab_report_name) ? '' : $dataLists->lab_report_name;
                $dateResponse = $this->dateTimeLibObj->convertTimeZone($dataLists->created_at,$Format,$oldTimeZone,$newTimeZone);
                 $dataLists->created_at = $dateResponse['code'] === '1000' ? $dateResponse['result'] : '';
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

    /**
     * @DateOfCreation        11 June 2018
     * @ShortDescription      This function is responsible to get laboratery report file path data
     * @param                 integer $wefId   
     * @return                Array of status and message
     */
    public function getFilePath($primaryId) {
        $path = Config::get('constants.STORAGE_MEDIA_PATH').Config::get('constants.PATIENTS_LABORATORY_PATH').'/';    
        $primaryIdData = DB::table($this->table)
                        ->select('lab_report_file')
                        ->where($this->primaryKey, $primaryId)
                        ->first();
        $filePath = isset($primaryIdData->lab_report_file) && !empty($primaryIdData->lab_report_file) ? storage_path($path.$primaryIdData->lab_report_file) : '';
        return $filePath;
    }

    /**
     * @DateOfCreation        13 Sep 2018
     * @ShortDescription      This function is responsible to get template
     * @param                 
     * @return                object Array of all lab templates
     */
    public function getLabTemplate($requestData)
    {
         $queryResult = DB::table($this->tableLabTemplate)
            ->select( 'lab_temp_id as value', 'temp_name as label' ) 
            ->where(['user_id'=>$requestData['user_id'], 'is_deleted'=> Config::get('constants.IS_DELETED_NO')])
            ->get()
            ->map(function($labtemplates){
                $labtemplates->value  = $this->securityLibObj->encrypt($labtemplates->value);
                return $labtemplates;
            });
        if(!empty($queryResult)){
            return $queryResult;
        }else{
            return false;
        }
    }

    /**
     * @DateOfCreation        13 Sep 2018
     * @ShortDescription      This function is responsible to get template
     * @param                 
     * @return                object Array of all lab templates
     */
    public function showLaboratoryReportBySymptoms($requestData)
    {
        $pat_id = $this->securityLibObj->decrypt($requestData['patId']);
        $visit_id = $this->securityLibObj->decrypt($requestData['visitId']);

        $whereData = [
            'pat_id'=>$pat_id, 
            'visit_id'=> $visit_id,
            'is_deleted'=> Config::get('constants.IS_DELETED_NO')
        ];

        $symptomsResult = DB::table($this->tableVisitSymptoms)
            ->select('symptom_id as id') 
            ->where($whereData)
            ->get()
            ->toArray();
        
        $diagnosisResult = DB::table($this->tablePatientsVisitDiagnosis)
            ->select('disease_id as id') 
            ->where($whereData)
            ->get()
            ->toArray();

        if(!empty($symptomsResult) && !empty($diagnosisResult)){
            $symptomsJson =  json_encode($symptomsResult); 
            $diagnosisJson = json_encode($diagnosisResult);
             $prepareQuery = "SELECT laboratory_test_data FROM laboratory_templates 
             WHERE (symptoms_data IN {$symptomsJson}) 
             AND  (diagnosis_data IN {$diagnosisJson})";
             $labtemplates =  DB::select(DB::raw($prepareQuery));

            /*$labtemplates =DB::table($this->tableLabTemplate)
                            ->select('laboratory_test_data')
                            ->where('symptoms_data','@>',$symptomsJson)
                            ->where('diagnosis_data','@>', $diagnosisJson)
                            ->where('is_deleted',Config::get('constants.IS_DELETED_NO'))
                            ->get();*/
            // print_r($labtemplates); die;
            return $queryResult;
        }else{
            return false;
        }
    }
}
