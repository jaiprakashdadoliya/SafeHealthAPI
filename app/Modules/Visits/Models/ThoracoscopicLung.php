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
 * ThoracoscopicLung
 *
 * @package                ILD India Registry
 * @subpackage             ThoracoscopicLung
 * @category               Model
 * @DateOfCreation         11 june 2018
 * @ShortDescription       This Model to handle database operation of Physical Examinations
 **/

class ThoracoscopicLung extends Model {

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
    protected $table         = 'patient_thoracoscopic_lung_biopsy';
    
    // This protected member contains fields that need to encrypt while saving in database
    protected $encryptable = [];
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [ 'pat_id', 
                            'visit_id',
                            'ptlb_date',
                            'ptlb_is_happen',
                            'ptlb_is_left_lung',
                            'ptlb_is_right_lung',
                            'ptlb_left_lung_lobe',
                            'ptlb_right_lung_lobe',
                            'ip_address',
                            'resource_type'
                        ];

    /**
     *@ShortDescription Override the primary key.
     *
     * @var string
     */
    protected $primaryKey = 'ptlb_id';

    /**
     * @DateOfCreation        26 June 2018
     * @ShortDescription      This function is responsible to get the patient Physical Examinations record
     * @param                 integer $visitId,$patientId, $encrypt   
     * @return                object Array of Physical Examinations records
     */
    public function getThoracoscopicLungByVistID($visitId,$patientId = '',$encrypt = true) 
    {       
        $selectData = ['ptlb_id','pat_id','visit_id','ptlb_date','ptlb_is_happen','ptlb_is_left_lung','ptlb_is_right_lung','ptlb_left_lung_lobe','ptlb_right_lung_lobe','ip_address','resource_type'];
        $whereData  = ['visit_id'=> $visitId,'is_deleted'=>  Config::get('constants.IS_DELETED_NO')];
        if(!empty($patientId)){
            $whereData ['pat_id'] = $patientId;
        }
        $queryResult = $this->dbBatchSelect($this->table, $selectData, $whereData);
            if($encrypt && !empty($queryResult)){
                $queryResult = $queryResult->map(function($dataList){ 
                $dataList->ptlb_id = $this->securityLibObj->encrypt($dataList->ptlb_id);
                $dataList->pat_id = $this->securityLibObj->encrypt($dataList->pat_id);
                $dataList->visit_id = $this->securityLibObj->encrypt($dataList->visit_id);
                return $dataList;
            });
        }
        return $queryResult;
        
    }

    
    

    /**
    * @DateOfCreation        27 June 2018
    * @ShortDescription      This function is responsible to update family Physical Examinations Record
    * @param                 Array  $requestData   
    * @return                Array of status and message
    */
    public function updateThoracoscopicLungByVistID($requestData,$whereData)
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
    * @DateOfCreation        27 June 2018
    * @ShortDescription      This function is responsible to multiple add family Physical Examinations Record
    * @param                 Array  $insertData   
    * @return                Array of status and message
    */
    public function addThoracoscopicLungByVistID($requestData)
    {
        
        $insertData = $this->utilityLibObj->fillterArrayKey($requestData, $this->fillable);
        $response = $this->dbInsert($this->table, $insertData);
        if($response){
            $id = DB::getPdo()->lastInsertId();
            return $id;
        }
        return false;
    }

}
