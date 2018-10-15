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
 * PhysicalExaminations
 *
 * @package                ILD India Registry
 * @subpackage             PhysicalExaminations
 * @category               Model
 * @DateOfCreation         11 june 2018
 * @ShortDescription       This Model to handle database operation of Physical Examinations
 **/

class TreatmentRequirement extends Model {

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
    protected $table = 'treatment_requirement';
    
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
                        'requirement_fector_type',
                        'requirement_fector_id',
                        'requirement_fector_value',
                        'resource_type',
                        'ip_address'
                        ];

    /**
     *@ShortDescription Override the primary key.
     *
     * @var string
     */
    protected $primaryKey = 'treatment_requirement_id';

    /**
     * @DateOfCreation        12 July 2018
     * @ShortDescription      This function is responsible to get the patient Treatment requirement record
     * @param                 integer $visitId,$patientId, $encrypt   
     * @return                object Array of Treatment requirement records
     */
    public function getTreatmentRequirementsInfo($visitId, $patientId = '', $encrypt = true) 
    {
        $selectData = ['treatment_requirement_id', 'pat_id', 'visit_id', 'fector_id', 'fector_value'];
        $whereData  = ['visit_id'=> $visitId, 'is_deleted'=>  Config::get('constants.IS_DELETED_NO')];
        
        if(!empty($patientId)){
            $whereData ['pat_id'] = $patientId;
        }
        $queryResult = $this->dbBatchSelect($this->table, $selectData, $whereData);
            if($encrypt && !empty($queryResult)){
                $queryResult = $queryResult->map(function($dataList){ 
                $dataList->treatment_requirement_id = $this->securityLibObj->encrypt($dataList->treatment_requirement_id);
                $dataList->pat_id                   = $this->securityLibObj->encrypt($dataList->pat_id);
                $dataList->visit_id                 = $this->securityLibObj->encrypt($dataList->visit_id);
                $dataList->fector_id                = $this->securityLibObj->encrypt($dataList->fector_id);
                return $dataList;
            });
        }
        return $queryResult;        
    }

    
    

    /**
    * @DateOfCreation        27 June 2018
    * @ShortDescription      This function is responsible to update Patient Death Info Record
    * @param                 Array  $requestData   
    * @return                Array of status and message
    */
    public function updateTreatmentRequirementsInfo($requestData, $whereData)
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
    * @ShortDescription      This function is responsible to multiple add Patient Death Info Record
    * @param                 Array  $insertData   
    * @return                Array of status and message
    */
    public function addTreatmentRequirementsInfo($insertData)
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
