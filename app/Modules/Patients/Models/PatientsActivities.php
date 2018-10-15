<?php
namespace App\Modules\Patients\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Laravel\Passport\HasApiTokens;
use App\Traits\Encryptable;
use App\Libraries\SecurityLib;
use App\Libraries\UtilityLib;
use Config;


/**
 * PatientsActivities
 *
 * @package                Safe health
 * @subpackage             PatientsActivities
 * @category               Model
 * @DateOfCreation         03 August 2018
 * @ShortDescription       This Model to handle database operation with current table
                           City
 **/
class PatientsActivities extends Model {

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
    protected $table = 'patient_activity';
    
    // @var Array $encryptedFields
    // This protected member contains fields that need to encrypt while saving in database
    protected $encryptable = [];
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['pat_id','user_id','activity_table'];

    /**
     *@ShortDescription Override the primary key.
     *
     * @var string
     */
    protected $primaryKey = 'pat_act_id';

    /**
     * @DateOfCreation        03 August 2018
     * @ShortDescription      This function is responsible to save record for the Patient Activity
     * @param                 array $requestData   
     * @return                integer Patient Activity id
     */
    public function insertActivity($requestData)
    {      
        // Prepare insert query
        $response = $this->dbInsert($this->table, $requestData);
        if($response){
            return true;
        }else{
            return $response;
        }
    }

    /**
     * @DateOfCreation        03 September 2018
     * @ShortDescription      This function is responsible to get Patient Activity records
     * @param                 array $requestData   
     * @return                integer Patient Activity id
     */
    public function getActivityRecords($requestData)
    {      
        $activityHistoryVisits = DB::table('patient_activity')
                        ->select(DB::raw("STRING_AGG(DISTINCT visit_id::character varying,',') as visits") )
                        ->where([
                                'patient_activity.user_id'    => $requestData['user_id'], 
                                'patient_activity.pat_id'     => $requestData['pat_id'], 
                                'patient_activity.is_deleted' => Config::get('constants.IS_DELETED_NO')
                            ])
                        ->where('patient_activity.activity_table', '!=', 'patients_visits')
                        ->first();
        return $activityHistoryVisits;
    }

    /**
     * @DateOfCreation        1 Oct 2018
     * @ShortDescription      This function is responsible to get Patient Activity records
     * @param                 array $requestData   
     * @return                integer Patient Activity id
     */
    public function insertActivityBatch($activityBatchArray){
        $response = $this->dbBatchInsert($this->table, $activityBatchArray);
        if($response){
            return true;
        }
        return false;
    }
}
