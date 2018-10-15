<?php

namespace App\Modules\Doctors\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\HasApiTokens;
use App\Traits\Encryptable;
use Illuminate\Support\Facades\DB;
use App\Libraries\SecurityLib;
use App\Libraries\UtilityLib;
use App\Libraries\DateTimeLib;
use Config;

/**
 * ManageCalendar Class
 *
 * @package                ILD INDIA
 * @subpackage             ManageCalendar
 * @category               Model
 * @DateOfCreation         13 June 2018
 * @ShortDescription       This is model which need to perform the options related to 
                           ManageCalendar info

 */
class ManageCalendar extends Model {

    use Encryptable;

    // @var string $table
    // This protected member contains table name
    protected $table = 'manage_caledar_setting';


    // @var string $primaryKey
    // This protected member contains primary key
    protected $primaryKey = 'mcs_id';  


    protected $encryptable = [];

    protected $fillable = ['user_id', 'mcs_slot_duration', 'mcs_start_time','mcs_end_time', 'ip_address', 'resource_type'];

    /**
     * Create a new model instance.
     *
     * @return void
     */
    public function __construct()
    {
        // Init exception library object
        $this->utilityLibObj = new UtilityLib();

        // Init security library object
        $this->securityLibObj = new SecurityLib();

        // Init dateTime library object
        $this->dateTimeLibObj = new DateTimeLib();

    }

    
    /**
     * @DateOfCreation        18 June 2018
     * @ShortDescription      This function is responsible to save record for the manage Calendar setting
     * @param                 array $requestData   
     * @return                integer manage Calendar setting id
     */
    public function getTableName()
    {
        return $this->table;
    }

    /**
     * @DateOfCreation        18 June 2018
     * @ShortDescription      This function is responsible to save record for the manage Calendar setting
     * @param                 array $requestData   
     * @return                integer manage Calendar setting id
     */
    public function getTablePrimaryIdColumn()
    {
        return $this->primaryKey;
    }

    /**
     * @DateOfCreation        21 June 2018
     * @ShortDescription      This function is responsible to check the Visit  wefId exist in the system or not
     * @param                 integer $wefId   
     * @return                Array of status and message
     */
    public function isPrimaryIdExist($primaryId){
        $primaryIdExist = DB::table($this->table)
                        ->where($this->primaryKey, $primaryId)
                        ->exists();
        return $primaryIdExist;
    }

    /**
     * @DateOfCreation        25 June 2018
     * @ShortDescription      This function is responsible to get manage calendar setting
     * @param                 integer $userId   
     * @return                object Array of DomesticFactor records
     */
    public function getManageCalendarRecordByUserId($userId) 
    {        
        $queryResult = DB::table($this->table)
            ->select( 'mcs_id','user_id', 'mcs_slot_duration', 'mcs_start_time','mcs_end_time', 'ip_address', 'resource_type') 
            ->where('is_deleted', Config::get('constants.IS_DELETED_NO'))
            ->where('user_id',$userId);
               
        $queryResult = $queryResult->get()->first();
        return $queryResult;

    }

    

     /**
     * @DateOfCreation        18 June 2018
     * @ShortDescription      This function is responsible to save record for the manage Calendar setting
     * @param                 array $requestData   
     * @return                integer auto increment id
     */
    public function addRequest($inserData)
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
    * @DateOfCreation        27 June 2018
    * @ShortDescription      This function is responsible to update manage Calendar setting Record
    * @param                 Array  $requestData   
    * @return                Array of status and message
    */
    public function updateRequest($requestData,$whereData)
    {
        $updateData = $this->utilityLibObj->fillterArrayKey($requestData, $this->fillable);
        $response = $this->dbUpdate($this->table, $updateData, $whereData);
        if($response){
            return true;
        }
        return false;
    }

}
