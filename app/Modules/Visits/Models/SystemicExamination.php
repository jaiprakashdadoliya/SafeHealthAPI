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
class SystemicExamination extends Model {

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
    protected $table = 'systemic_examination';
    
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
                            'systemic_exam_type',
                            'systemic_exam_type_id',
                            'systemic_exam_value',
                            'resource_type',
                            'ip_address'
                        ];

    /**
     *@ShortDescription Override the primary key.
     *
     * @var string
     */
    protected $primaryKey = 'systemic_exam_id';

    /**
     * @DateOfCreation        25 June 2018
     * @ShortDescription      This function is responsible to get the patient domestic fector record
     * @param                 integer $vistId   
     * @return                object Array of DomesticFactor records
     */
    public function getSystemicExaminationRecord($vistId) 
    {        
        $queryResult = DB::table($this->table)
            ->select('systemic_exam_id','systemic_exam_type', 'systemic_exam_type_id', 'systemic_exam_value','visit_id','resource_type', 'ip_address') 
            ->where('is_deleted', Config::get('constants.IS_DELETED_NO'))
            ->where('visit_id',$vistId);
               
        $queryResult = $queryResult->get()
            ->map(function($symptomsTestRecord){
            $symptomsTestRecord->systemic_exam_id = $this->securityLibObj->encrypt($symptomsTestRecord->systemic_exam_id);
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
    public function updateSystemicExaminationTest($requestData,$whereData)
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
    * @ShortDescription      This function is responsible to multiple add Domestic Factor Record
    * @param                 Array  $requestData   
    * @return                Array of status and message
    */
    public function addSystemicExaminationTest($insertData)
    {
        $response = $this->dbBatchInsert($this->table, $insertData);
        if($response){
            return true;
        }
        return false;
    }

}
