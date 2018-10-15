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
 * SleepStudy
 *
 * @package                Safe Health
 * @subpackage             SleepStudy
 * @category               Model
 * @DateOfCreation         5 Oct 2018
 * @ShortDescription       This Model to handle database operation of Sleep Study table
 **/

class SleepStudy extends Model {

    use HasApiTokens, Encryptable;

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

        // Init Visit model object
        $this->visitModelObj = new Visits();
    }

    /**
    *@ShortDescription Table for the Users.
    *
    * @var String
    */
    protected $table          = 'investigation_sleep_study';
    
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
                            'ip_address',
                            'resource_type',
                            'is_deleted',
                            'investigation_ahi',
                            'investigation_ri',
                            'investigation_conclusion'
                        ];

    /**
     *@ShortDescription Override the primary key.
     *
     * @var string
     */
    protected $primaryKey = 'iss_id';

    /**
     * @DateOfCreation        5 Oct 2018
     * @ShortDescription      This function is responsible to get Sleep Study
     * @param                 
     * @return                object Array of all medicines
     */
    public function getSleepStudyData($requestData) 
    {   
        $queryResult = DB::table( $this->table )
                        ->select( 
                                'iss_id',
                                'investigation_ahi',
                                'investigation_ri',
                                'investigation_conclusion'
                            ) 
                        ->where( 'is_deleted', Config::get('constants.IS_DELETED_NO') )
                        ->where( 'pat_id', $requestData['pat_id'] )
                        ->where( 'visit_id', $requestData['visit_id'] );
               
        $queryResult = $queryResult->first();

        if(!empty($queryResult)){
            $queryResult->iss_id = $this->securityLibObj->encrypt($queryResult->iss_id);
        }
        return $queryResult;
    }

    /**
    * @DateOfCreation        5 Oct 2018
    * @ShortDescription      This function is responsible to save Sleep Study data
    * @param                 Array  $requestData   
    * @return                Array of status and message
    */
    public function saveSleepStudyData($requestData)
    {
        $response  = $this->dbInsert($this->table, $requestData);

        if($response){
            $id = DB::getPdo()->lastInsertId();
            return $id;
            
        }else{
            return $response;
        }
    }

    /**
    * @DateOfCreation        5 Oct 2018
    * @ShortDescription      This function is responsible to update Sleep Study data
    * @param                 Array  $requestData   
    * @return                Array of status and message
    */
    public function updateSleepStudyData($requestData, $id)
    {
        $whereData = [ 'iss_id' => $id ];
        
        // Prepare update query
        $response = $this->dbUpdate($this->table, $requestData, $whereData);
        
        if($response){
            return true;
        }
        return false;
    }

    /**
    * @DateOfCreation        5 Oct 2018
    * @ShortDescription      This function is responsible to update Sleep Study data
    * @param                 Array  $requestData   
    * @return                Array of status and message
    */
    public function checkAndUpdateSleepStudy($requestData)
    {
        $response = false;
        if(!empty($requestData['investigation_ahi'] || $requestData['investigation_ri'] || $requestData['investigation_conclusion'])){

            $whereCheck = ['pat_id' => $requestData['pat_id'],'visit_id' => $requestData['visit_id']];
            $checkDataExist = $this->visitModelObj->checkIfRecordExist($this->table, 'iss_id', $whereCheck);

            if($checkDataExist){
                $response = $this->dbUpdate($this->table, $requestData, $whereCheck);
            }else{
                $response = $this->dbInsert($this->table, $requestData);
            }
           
            if($response){
                return true;
            }
            return false;
        }
        return true;
    }
}
