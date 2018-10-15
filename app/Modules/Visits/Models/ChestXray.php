<?php
namespace App\Modules\Visits\Models;
use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\HasApiTokens;
use App\Traits\Encryptable;
use App\Libraries\SecurityLib;
use Config;
use App\Libraries\UtilityLib;
use DB;
use App\Libraries\DateTimeLib;
use App\Modules\Setup\Models\StaticDataConfig;

/**
 * ChestXray
 *
 * @package                ILD India Registry
 * @subpackage             ChestXray
 * @category               Model
 * @DateOfCreation         11 june 2018
 * @ShortDescription       This Model to handle database operation of ChestXray
 **/

class ChestXray extends Model {

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

        // Init dateTimeLibObj library object
        $this->dateTimeLibObj = new DateTimeLib();

        // Init StaticDataConfig model object
        $this->staticDataObj = new StaticDataConfig();
    }

    /**
    *@ShortDescription Table for the Users.
    *
    * @var String
    */
    protected $table = 'patient_chest_xray';
    
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
                            'pcx_type',
                            'pcx_date',
                            'pcx_bilateral_shadows_present',
                            'resource_type',
                            'ip_address'
                        ];

    /**
     *@ShortDescription Override the primary key.
     *
     * @var string
     */
    protected $primaryKey = 'pcx_id';

    /**
     * @DateOfCreation        26 June 2018
     * @ShortDescription      This function is responsible to get the patient Physical Examinations record
     * @param                 integer $visitId,$patientId, $encrypt   
     * @return                object Array of Physical Examinations records
     */
    public function getChestXrayByVistID($visitId,$patientId = '',$encrypt = true) 
    {       
        $selectData = ['pcx_id','pcx_date','pat_id','visit_id','pcx_type','pcx_bilateral_shadows_present','resource_type','ip_address'];
        $whereData  = ['visit_id'=> $visitId,'is_deleted'=>  Config::get('constants.IS_DELETED_NO')];
        $whereData ['pat_id'] = $patientId;
        
        $queryResult = $this->dbBatchSelect($this->table, $selectData, $whereData);
            if($encrypt && !empty($queryResult)){
                $queryResult = $queryResult->map(function($dataList){ 
                $dataList->pcx_id = $this->securityLibObj->encrypt($dataList->pcx_id);
                $dataList->pat_id = $this->securityLibObj->encrypt($dataList->pat_id);
                $dataList->visit_id = $this->securityLibObj->encrypt($dataList->visit_id);
                return $dataList;
            });
        }
        return $queryResult;        
    }

    /**
    * @DateOfCreation        12 July 2018
    * @ShortDescription      This function is responsible to update Patient ChestXray Record
    * @param                 Array  $requestData   
    * @return                Array of status and message
    */
    public function updatePatientChestXrayInfo($requestData, $whereData)
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
     * @ShortDescription      This function is responsible to save record for the Patient ChestXray
     * @param                 array $requestData   
     * @return                integer symptoms id
     */
    public function addPatientChestXrayInfo($inserData)
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
     * @ShortDescription      This function is responsible to call model function for save saveThoracoscopicLung
     * @param                 array $data   
     * @return                boolean true / false
     */
    public function saveChestXray($requestData,$visitId,$patientId){

        $formValuDataDate = $this->getChestXrayByVistID($visitId,$patientId,false);
        $formValuDataVisitId = !empty($formValuDataDate) ? $this->utilityLibObj->changeArrayKey($formValuDataDate,'pcx_type'): [];

        $staticDataFactor = $this->staticDataObj->getChestXray();
        $staticDataFactor = !empty($staticDataFactor) ? $this->utilityLibObj->changeArrayKey($staticDataFactor,'id'): [];
        $typeData = []; 
        foreach ($staticDataFactor as $key => $value) {
            $typeData[$value['type']][$value['name']] =  $value;
        }
        foreach ($typeData as $factorTypeKey => $factorTypeValue) {
            $temp = [];
            $temp['visit_id'] = $visitId;
            $temp['pat_id'] = $patientId;
            $temp['resource_type'] = $requestData['resource_type'];
            $temp['ip_address'] = $requestData['ip_address'];
            $temp['pcx_type']   = $factorTypeKey;
            $keyName = $factorTypeKey == Config::get('dataconstants.CHEST_XRAY_RECENT_TYPE') ?  '_recent':'_old';
            $getDate = isset($requestData['pcx_date'.$keyName]) && !empty($requestData['pcx_date'.$keyName]) ? $this->getDateConvrtion($requestData['pcx_date'.$keyName]) : '';
            if(!$getDate && !empty($requestData['pcx_date'.$keyName])){
                $dbstaus = false;
                break;
            }
            $getDate = !empty($getDate) ? $getDate :null;
            $getshadows_present = isset($requestData['pcx_bilateral_shadows_present'.$keyName]) && !empty($requestData['pcx_bilateral_shadows_present'.$keyName]) ? $requestData['pcx_bilateral_shadows_present'.$keyName] : null;
            $temp['pcx_date']   = $getDate;
            $temp['pcx_bilateral_shadows_present']   = $getshadows_present;
            $pcx_id = !empty($formValuDataVisitId) && isset($formValuDataVisitId[$factorTypeKey]['pcx_id']) ? $formValuDataVisitId[$factorTypeKey]['pcx_id'] :'';
            if(!empty($pcx_id)){
                $whereData =[];
                $whereData['pat_id'] = $patientId;
                $whereData['visit_id'] = $visitId;
                $whereData['pcx_type'] = $factorTypeKey;
                $responseData = $this->updatePatientChestXrayInfo($temp,$whereData);
                if(!$responseData){
                    $dbstaus = false;
                    break;
                }
            }elseif(!empty($getDate) || !empty($getshadows_present)){
                $responseData = $this->addPatientChestXrayInfo($temp);
                if(!$responseData){
                    $dbstaus = false;
                    break;
                }
            }
        }
        if(isset($dbstaus)){
            return false;
        }
        return true;
    }

    public function getDateConvrtion($dateData){
        $dateResponse = $this->dateTimeLibObj->covertUserDateToServerType($dateData,'dd/mm/YY','Y-m-d');
            if($dateResponse['code']=='5000'){
                return false;
            }
            $dateData = $dateResponse['result'];
            return $dateData;
    }
}
