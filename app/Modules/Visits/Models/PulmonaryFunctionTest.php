<?php
namespace App\Modules\Visits\Models;
use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\HasApiTokens;
use App\Traits\Encryptable;
use App\Libraries\SecurityLib;
use Config;
use App\Libraries\UtilityLib;
use DB;
use App\Modules\Visits\Models\Visits;

/**
 * PulmonaryFunctionTest
 *
 * @package                ILD India Registry
 * @subpackage             PulmonaryFunctionTest
 * @category               Model
 * @DateOfCreation         26 July 2018
 * @ShortDescription       This Model to handle database operation of spirometry
 **/

class PulmonaryFunctionTest extends Model {

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

        // Init Visits model object
        $this->visitsModelObj = new Visits();
    }
    /**
    *@ShortDescription Table for the Users.
    *
    * @var String
    */
    protected $table                             = 'patient_pulmonary_function_test';
    protected $tablePatientPulmonaryFunctionTest = 'patient_pulmonary_function_test';
    
    // This protected member contains fields that need to encrypt while saving in database
    protected $encryptable = [];
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [ 'pat_id',
                            'visit_id',
                            'pulmonary_function_test_status',
                            'resource_type',
                            'ip_address'
                        ];

    /**
     *@ShortDescription Override the primary key.
     *
     * @var string
     */
    protected $primaryKey = 'pft_id';

    /**
     * @DateOfCreation        26 July 2018
     * @ShortDescription      This function is responsible to get the patient pulmonary function record
     * @param                 integer $visitId,$patientId, $encrypt   
     * @return                object Array of pulmonary function records
     */
    public function getPulmonaryFunctionByVistID($visitId, $patientId = '', $encrypt = true) 
    {       
        $selectData = ['pft_id', 'pat_id', 'visit_id', 'pulmonary_function_test_status', 'resource_type', 'ip_address'];
        $whereData  = ['visit_id'=> $visitId, 'is_deleted' => Config::get('constants.IS_DELETED_NO')];
        if(!empty($patientId)){
            $whereData ['pat_id'] = $patientId;
        }
        $queryResult = $this->dbBatchSelect($this->table, $selectData, $whereData);
            if($encrypt && !empty($queryResult)){
                $queryResult = $queryResult->map(function($dataList){ 
                $dataList->pft_id = $this->securityLibObj->encrypt($dataList->pft_id);
                $dataList->pat_id = $this->securityLibObj->encrypt($dataList->pat_id);
                $dataList->visit_id = $this->securityLibObj->encrypt($dataList->visit_id);
                return $dataList;
            });
        }
        return $queryResult;        
    }

    /**
     * @DateOfCreation        26 July 2018
     * @ShortDescription      This function is responsible to save / update Pulmonary Function data
     * @param                 array $insertData   
     * @return                object Array of medical history records
     */
    public function savePulmonaryFunctionTestData($insertData){
       
        $whereCheck = ['pat_id' => $insertData['pat_id'],'visit_id' => $insertData['visit_id']];
        $checkDataExist = $this->visitsModelObj->checkIfRecordExist($this->tablePatientPulmonaryFunctionTest, 'pft_id', $whereCheck);
        $insertData['pulmonary_function_test_status'] = !empty($insertData['pulmonary_function_test_status']) ? $insertData['pulmonary_function_test_status'] : NULL;
        if($checkDataExist){
            // Update
            $response = $this->dbUpdate($this->tablePatientPulmonaryFunctionTest, $insertData, $whereCheck);
        }else{
            // insert
            $response = $this->dbInsert($this->tablePatientPulmonaryFunctionTest, $insertData);
        }

        if($response){
            return true;
        }
        return false;   
    }
}
