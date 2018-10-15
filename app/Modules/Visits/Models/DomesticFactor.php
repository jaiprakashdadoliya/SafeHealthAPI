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
 * DomesticFactor
 *
 * @package                ILD India Registry
 * @subpackage             DomesticFactor
 * @category               Model
 * @DateOfCreation         11 june 2018
 * @ShortDescription       This Model to handle database operation with current table
                           patient_domestic_factors_condition
 **/
class DomesticFactor extends Model {

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
    protected $table = 'patient_domestic_factors_condition';
    protected $tablePlace = 'patient_residence';
    
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
                            'domestic_factor_id',
                            'domestic_factor_value',
                            'resource_type',
                            'ip_address'
                        ];

    protected $fillablePlace = [ 'pat_id', 
                            'visit_id',
                            'residence_id',
                            'residence_value',
                            'ip_address',
                            'resource_type'
                        ];

    /**
     *@ShortDescription Override the primary key.
     *
     * @var string
     */
    protected $primaryKey = 'pdfc_id';
    protected $primaryKeyPlace = 'pr_id';

    /**
     * @DateOfCreation        25 June 2018
     * @ShortDescription      This function is responsible to get the patient domestic fector record
     * @param                 integer $vistId   
     * @return                object Array of DomesticFactor records
     */
    public function getPatientDomesticFactorRecord($vistId) 
    {        
        $queryResult = DB::table($this->table)
            ->select( 'pdfc_id','domestic_factor_id', 'domestic_factor_value', 'resource_type', 'ip_address') 
            ->where('is_deleted', Config::get('constants.IS_DELETED_NO'))
            ->where('visit_id',$vistId);
               
        $queryResult = $queryResult->get()
            ->map(function($domesticFactorRecord){
            $domesticFactorRecord->pdfc_id = $this->securityLibObj->encrypt($domesticFactorRecord->pdfc_id);
            return $domesticFactorRecord;
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
            ->select( 'pdfc_id' ) 
            ->where('is_deleted', Config::get('constants.IS_DELETED_NO'))
            ->where('visit_id', $vistId)
            ->where('domestic_factor_id', $fectorId);
        return $queryResult->get()->count();
    }

    /**
    * @DateOfCreation        27 June 2018
    * @ShortDescription      This function is responsible to update Domestic Factor Record
    * @param                 Array  $requestData   
    * @return                Array of status and message
    */
    public function updateDomesticFactor($requestData,$whereData)
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
    public function addDomesticFactor($insertData)
    {
        $response = $this->dbBatchInsert($this->table, $insertData);
        if($response){
            return true;
        }
        return false;
    }

    /**
     * @DateOfCreation        25 June 2018
     * @ShortDescription      This function is responsible to get the patient domestic fector record
     * @param                 integer $vistId   
     * @return                object Array of DomesticFactor records
     */
    public function getPatientResidenceRecord($vistId) 
    {        
        $queryResult = DB::table($this->tablePlace)
            ->select( 'pr_id','residence_id', 'residence_value', 'resource_type', 'ip_address') 
            ->where('is_deleted', Config::get('constants.IS_DELETED_NO'))
            ->where('visit_id',$vistId);
               
        $queryResult = $queryResult->get()
            ->map(function($patientResidenceRecord){
            $patientResidenceRecord->pr_id = $this->securityLibObj->encrypt($patientResidenceRecord->pr_id);
            return $patientResidenceRecord;
        });
        return $queryResult;

    }

    /**
    * @DateOfCreation        27 June 2018
    * @ShortDescription      This function is responsible to update Domestic Factor Record
    * @param                 Array  $requestData   
    * @return                Array of status and message
    */
    public function updatePatientResidence($requestData,$whereData)
    {
        $updateData = $this->utilityLibObj->fillterArrayKey($requestData, $this->fillablePlace);
        $response = $this->dbUpdate($this->tablePlace, $updateData, $whereData);
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
    public function addPatientResidence($insertData)
    {
        $response = $this->dbBatchInsert($this->tablePlace, $insertData);
        if($response){
            return true;
        }
        return false;
    }




}
