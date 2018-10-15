<?php
namespace App\Modules\Visits\Models;
use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\HasApiTokens;
use App\Traits\Encryptable;
use Illuminate\Support\Facades\DB;
use App\Libraries\SecurityLib;
use Config;
use App\Libraries\UtilityLib;

/**
 * MedicationAntibioticHistory
 *
 * @package                ILD
 * @subpackage             MedicationAntibioticHistory
 * @category               Model
 * @DateOfCreation         11 june 2018
 * @ShortDescription       This Model to handle database operation with current table
                           City
 **/
class MedicationAntibioticHistory extends Model {

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

        // Init security library object
        $this->utilityLibObj = new UtilityLib();
        
    }

    /**
    *@ShortDescription Table for the Users.
    *
    * @var String
    */
    protected $table = 'patient_history';
        
    // @var Array $encryptedFields
    // This protected member contains fields that need to encrypt while saving in database
    protected $encryptable = [];
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['pat_id','visit_id','number_of_antibiotics_course','resource_type','ip_address'];

    /**
     *@ShortDescription Override the primary key.
     *
     * @var string
     */
    protected $primaryKey = 'ph_id';

    /**
     * @DateOfCreation        18 June 2018
     * @ShortDescription      This function is responsible to save record for the Patient Medication Antibiotic History
     * @param                 array $requestData   
     * @return                table name
     */
    public function getTableName()
    {
        return $this->table;
    }

    /**
     * @DateOfCreation        18 June 2018
     * @ShortDescription      This function is responsible to save record for the Patient Medication Antibiotic History
     * @param                 array $requestData   
     * @return                column name
     */
    public function getTablePrimaryIdColumn()
    {
        return $this->primaryKey;
    }

    /**
     * @DateOfCreation        18 June 2018
     * @ShortDescription      This function is responsible to save record for the Patient Medication Antibiotic History
     * @param                 array $requestData   
     * @return                integer ph_id
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
     * @ShortDescription      This function is responsible to update Patient Medication Antibiotic History data
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
     * @ShortDescription      This function is responsible to get the Patient Medication Antibiotic History data
     * @param                 array $requestData patId, $visitId  
     * @return                object Array of Patient Medication Antibiotic History records
     */
    public function getListData($visitId,$patId,$extraData =[]) {
        if(empty($visitId) || empty($patId)){
            return [];
        }
        $selectData = ['number_of_antibiotics_course','ph_id'];
        $whereData  = ['visit_id'=> $visitId,'pat_id'=>$patId,'is_deleted'=>  Config::get('constants.IS_DELETED_NO')];
        
        $queryResult = $this->dbSelect($this->table, $selectData, $whereData);
        if(!empty($queryResult)){
            $queryResult->ph_id = $this->securityLibObj->encrypt($queryResult->ph_id);
        }

        return $queryResult;
    }
}
