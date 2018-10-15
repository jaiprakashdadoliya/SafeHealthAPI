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
 * UIP
 *
 * @package                ILD India Registry
 * @subpackage             UIP
 * @category               Model
 * @DateOfCreation         11 june 2018
 * @ShortDescription       This Model to handle database operation of UIP
 **/

class UIP extends Model {

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
    protected $table = 'patient_uip';
    
    // This protected member contains fields that need to encrypt while saving in database
    protected $encryptable = [];
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [ 'pat_id', 
                            'visit_id',
                            'puip_is_happen',
                            'resource_type',
                            'ip_address'
                        ];

    /**
     *@ShortDescription Override the primary key.
     *
     * @var string
     */
    protected $primaryKey = 'puip_id';

    /**
     * @DateOfCreation        26 June 2018
     * @ShortDescription      This function is responsible to get the patient Physical Examinations record
     * @param                 integer $visitId,$patientId, $encrypt   
     * @return                object Array of Physical Examinations records
     */
    public function getUIPByVistID($visitId,$patientId,$encrypt = true) 
    {       
        $selectData = ['puip_id','pat_id','visit_id','puip_is_happen','resource_type','ip_address'];
        $whereData  = ['visit_id'=> $visitId,'is_deleted'=>  Config::get('constants.IS_DELETED_NO')];
        $whereData ['pat_id'] = $patientId;
    
        $queryResult = $this->dbBatchSelect($this->table, $selectData, $whereData);
            if($encrypt && !empty($queryResult)){
                $queryResult = $queryResult->map(function($dataList){ 
                $dataList->puip_id = $this->securityLibObj->encrypt($dataList->puip_id);
                $dataList->pat_id = $this->securityLibObj->encrypt($dataList->pat_id);
                $dataList->visit_id = $this->securityLibObj->encrypt($dataList->visit_id);
                return $dataList;
            });
        }
        return $queryResult;        
    }

     /**
    * @DateOfCreation        12 July 2018
    * @ShortDescription      This function is responsible to update Patient UIP Record
    * @param                 Array  $requestData   
    * @return                Array of status and message
    */
    public function updatePatientUIPInfo($requestData, $whereData)
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
     * @DateOfCreation        18 June 2018
     * @ShortDescription      This function is responsible to save record for the UIP
     * @param                 array $requestData   
     * @return                integer puip id
     */
    public function addPatientUIPInfo($inserData)
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
     * @DateOfCreation        10 July 2018
     * @ShortDescription      This function is responsible to call model function for save UIP Info
     * @param                 array $data   
     * @return                boolean true / false
     */
    public function saveUIP($requestData,$visitId,$patientId){

        $formValuDataDate = $this->getUIPByVistID($visitId,$patientId,false);
        $formValuDataVisitId = !empty($formValuDataDate) ? $this->utilityLibObj->changeArrayKey($formValuDataDate,'visit_id'): [];
        $puipHappen = $requestData['puip_is_happen'];
        $puip_id = !empty($formValuDataVisitId) && isset($formValuDataVisitId[$visitId]) ? $formValuDataVisitId[$visitId]['puip_id']:'';

        $temp =[];
        $temp['pat_id'] = $patientId;
        $temp['visit_id'] = $visitId;
        $temp['resource_type'] = $requestData['resource_type'];
        $temp['ip_address'] = $requestData['ip_address'];
        $temp['puip_is_happen'] = $puipHappen;
        if(empty($puip_id) && !empty($puipHappen)){
            $responseData = $this->addPatientUIPInfo($temp);
        }elseif(!empty($puip_id)){
            $whereData = [];
            $whereData['puip_id'] = $puip_id;
            $whereData['pat_id'] = $patientId;
            $whereData['visit_id'] = $visitId;
            $responseData =$this->updatePatientUIPInfo($temp,$whereData);
        }else{
            return true;
        }
        return $requestData;
    }
}
