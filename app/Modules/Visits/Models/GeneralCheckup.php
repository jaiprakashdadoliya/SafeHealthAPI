<?php
namespace App\Modules\Visits\Models;
use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\HasApiTokens;
use App\Traits\Encryptable;
// use Illuminate\Support\Facades\DB;
use App\Libraries\SecurityLib;
use Config;
use App\Libraries\UtilityLib;
use DB;

/**
 * GeneralCheckup
 *
 * @package                ILD India Registry
 * @subpackage             GeneralCheckup
 * @category               Model
 * @DateOfCreation         11 june 2018
 * @ShortDescription       This Model to handle database operation with current table
                           City
 **/
class GeneralCheckup extends Model {

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
    }

    /**
    *@ShortDescription Table for the Users.
    *
    * @var String
    */
    protected $table = 'patients_general_checkup';
    
    // @var Array $encryptedFields
    // This protected member contains fields that need to encrypt while saving in database
    protected $encryptable = [];
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [ 'pat_id', 
                            'visit_id',
                            'checkup_factor_id',
                            'is_happend',
                            'duration',
                            'duration_unit', 
                            'remark', 
                            'resource_type',
                            'ip_address'
                        ];

    /**
     *@ShortDescription Override the primary key.
     *
     * @var string
     */
    protected $primaryKey = 'pat_checkup_id';

    /**
     * @DateOfCreation        18 June 2018
     * @ShortDescription      This function is responsible to save record for the General Checkup
     * @param                 array $requestData   
     * @return                integer checkup id
     */
    public function addGeneralCheckup($requestData)
    {
        // $response = DB::table($this->table)->insert($requestData);
        $inserData = $this->utilityLibObj->fillterArrayKey($requestData, $this->fillable);
        $response  = $this->dbInsert($this->table, $inserData);            

        if($response){
            $id = DB::getPdo()->lastInsertId();
            return $id;
            
        }else{
            return $response;
        }
    }

    /**
     * @DateOfCreation        25 June 2018
     * @ShortDescription      This function is responsible to get the patient general checkup record
     * @param                 integer $patId   
     * @return                object Array of symptoms records
     */
    public function getPatientGeneralCheckupRecord($vistId) 
    {        
        $queryResult = DB::table($this->table)
            ->select( 'pat_checkup_id','checkup_factor_id', 'is_happend', 'duration', 'duration_unit', 'remark', 'resource_type', 'ip_address') 
            ->where('is_deleted', Config::get('constants.IS_DELETED_NO'))
            ->where('visit_id',$vistId);
               
        $queryResult = $queryResult->get()
            ->map(function($generalCheckupRecord){
            $generalCheckupRecord->pat_checkup_id = $this->securityLibObj->encrypt($generalCheckupRecord->pat_checkup_id);
            return $generalCheckupRecord;
        });
        return $queryResult;

    }

    /**
     * @DateOfCreation        26 June 2018
     * @ShortDescription      This function is responsible to check if fector record is exist or not
     * @param                 integer $patId   
     * @return                object Array of symptoms records
     */
    public function checkIfFectorExist($vistId, $fectorId) 
    {        
        $queryResult = DB::table($this->table)
            ->select( 'pat_checkup_id' ) 
            ->where('is_deleted', Config::get('constants.IS_DELETED_NO'))
            ->where('visit_id', $vistId)
            ->where('checkup_factor_id', $fectorId);
               
        return $queryResult->get()->count();
    }

    /**
    * @DateOfCreation        25 June 2018
    * @ShortDescription      This function is responsible to update General Checkup data
    * @param                 String $visit_symptom_id
                             Array  $requestData   
    * @return                Array of status and message
    */
    public function updateGeneralCheckup($requestData)
    {
        $updateData = $this->utilityLibObj->fillterArrayKey($requestData, $this->fillable);
        $whereData = [
                    'checkup_factor_id' => $requestData['checkup_factor_id'],
                    'pat_id'            => $requestData['pat_id'],
                    'visit_id'          => $requestData['visit_id']
                    ];
        
        // Prepair update query
        $response = $this->dbUpdate($this->table, $updateData, $whereData);
        
        if($response){
            return true;
        }
        return false;
    }

     

}
