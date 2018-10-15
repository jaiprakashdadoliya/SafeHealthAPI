<?php
namespace App\Modules\Visits\Models;
use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\HasApiTokens;
use App\Traits\Encryptable;
use App\Libraries\SecurityLib;
use Config;
use App\Libraries\UtilityLib;
use DB;

/**
 * Investigation
 *
 * @package                ILD India Registry
 * @subpackage             Investigation
 * @category               Model
 * @DateOfCreation         11 june 2018
 * @ShortDescription       This Model to handle database operation of Investigation
 **/

class PatientSixmwts extends Model {

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
    protected $table = 'patient_sixmwts';
    protected $tableSixmwtFectors = 'patient_sixmwt_fectors';
    
    // This protected member contains fields that need to encrypt while saving in database
    protected $encryptable = [];
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [ 
                        'pat_id', 
                        'visit_id',
                        'sixmwt_date',
                        'resource_type',
                        'ip_address'
                        ];

    /**
     *@ShortDescription Override the primary key.
     *
     * @var string
     */
    protected $primaryKey = 'sixmwt_id';

    /**
     * @DateOfCreation        12 July 2018
     * @ShortDescription      This function is responsible to get the patient Sixmwts record
     * @param                 integer $visitId, $patientId, $encrypt   
     * @return                object Array of Sixmwts records
     */
    public function getPatientSixmwtsInfo($visitId, $patientId = '', $encrypt = true) 
    {
        $selectData = ['sixmwt_id', 'pat_id', 'visit_id', 'sixmwt_date'];
        $whereData  = ['visit_id'=> $visitId, 'is_deleted'=>  Config::get('constants.IS_DELETED_NO')];
        
        if(!empty($patientId)){
            $whereData ['pat_id'] = $patientId;
        }
        $queryResult = $this->dbBatchSelect($this->table, $selectData, $whereData);
            if($encrypt && !empty($queryResult)){
                $queryResult = $queryResult->map(function($dataList){ 
                $dataList->sixmwt_id    = $this->securityLibObj->encrypt($dataList->sixmwt_id);
                $dataList->pat_id       = $this->securityLibObj->encrypt($dataList->pat_id);
                $dataList->visit_id     = $this->securityLibObj->encrypt($dataList->visit_id);
                $dataList->sixmwt_date  = !empty($dataList->sixmwt_date) ? date('d/m/Y', strtotime($dataList->sixmwt_date)) : NULL;
                return $dataList;
            });
        }
        return $queryResult;        
    }

    /**
     * @DateOfCreation        12 July 2018
     * @ShortDescription      This function is responsible to get the patient Sixmwts record
     * @param                 integer $visitId, $patientId, $encrypt   
     * @return                object Array of Sixmwts records
     */
    public function getSixmwtsTableFectorsData($sixmwtId) 
    {
        $this->fillable = [];
        $sixmwtId = $this->securityLibObj->decrypt($sixmwtId);
        $selectData = ['sixmwt_fector_id', 'sixmwt_id', 'fector_type', 'fector_id', 'before_sixmwt', 'after_sixmwt'];
        $whereData  = ['sixmwt_id'=> $sixmwtId, 'is_deleted'=>  Config::get('constants.IS_DELETED_NO')];
        
        $queryResult = $this->dbBatchSelect($this->tableSixmwtFectors, $selectData, $whereData);
            if(!empty($queryResult)){
                $queryResult = $queryResult->map(function($dataList){ 
                $dataList->sixmwt_fector_id = $this->securityLibObj->encrypt($dataList->sixmwt_fector_id);
                $dataList->sixmwt_id        = $this->securityLibObj->encrypt($dataList->sixmwt_id);
                $dataList->fector_id        = $this->securityLibObj->encrypt($dataList->fector_id);
                return $dataList;
            });
        }
        return $queryResult;        
    }

    /**
    * @DateOfCreation        12 July 2018
    * @ShortDescription      This function is responsible to update Patient Sixmwts Record
    * @param                 Array  $requestData   
    * @return                Array of status and message
    */
    public function updatePatientSixmwtsInfo($requestData, $whereData)
    {
        if(!empty($whereData)){
            $updateData = $this->utilityLibObj->fillterArrayKey($requestData, $this->fillable);
            $response = $this->dbUpdate($this->table, $updateData, $whereData);
            if($response){
                return true;
            }
        }
        return false;
    }

    /**
    * @DateOfCreation        12 July 2018
    * @ShortDescription      This function is responsible to multiple add Patient Sixmwts Record
    * @param                 Array  $insertData   
    * @return                Array of status and message
    */
    public function addPatientSixmwtsInfo($insertData)
    {
        if(!empty(array_filter($insertData))){
            $response = $this->dbBatchInsert($this->table, $insertData);
            if($response){
                return true;
            }
        }
        return false;
    }
}
