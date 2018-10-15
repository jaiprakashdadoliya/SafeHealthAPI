<?php

namespace App\Modules\AppointmentCategory\Models;

use Illuminate\Database\Eloquent\Model;
use App\Libraries\SecurityLib;
use Illuminate\Support\Facades\DB;
use App\Traits\Encryptable;
use Config;

/**
 * AppointmentCategory Class
 *
 * @package                AppointmentCategory
 * @subpackage             Doctor AppointmentCategory
 * @category               Model
 * @DateOfCreation         7 june 2018
 * @ShortDescription       This is model which need to perform the options related to 
                           AppointmentCategory table
 */
class AppointmentCategory extends Model 
{
	use Encryptable;
    /**
     * The attributes that should be override default primary key.
     *
     * @var string 
     */
    protected $primaryKey = 'appointment_cat_id';

    /**
     * The attributes that should be override default table name.
     *
     * @var string 
     */
    protected $table = 'appointment_category';

    // @var Array $encryptedFields
    // This protected member contains fields that need to encrypt while saving in database
    protected $encryptable = [];

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // Init security library object
        $this->securityLibObj = new SecurityLib();  
    }

    /**
     * Create doctor appointment reason list with regarding details
     *
     * @param array $data appointment reason data
     * @return int doctor member id if inserted otherwise false
     */
    public function getList($requestData)
    {
       	$selectData  =  ['appointment_cat_id', 'appointment_cat_name'];
        $whereData   =  [
                        'user_id'=> $requestData['user_id'],
                        'is_deleted'=>  Config::get('constants.IS_DELETED_NO'),
                        ];
        $query =  DB::table($this->table)
                    ->select($selectData)
                    ->where($whereData);
        /* Condition for Filtering the result */
            if(!empty($requestData['filtered'])){
                foreach ($requestData['filtered'] as $key => $value) {
                    $query = $query->where('appointment_cat_name', 'ilike', "%".$value['value']."%");
                }
            }
        
        /* Condition for Sorting the result */
        if(!empty($requestData['sorted'])){
            foreach ($requestData['sorted'] as $key => $value) {
                $orderBy = $value['desc'] ? 'desc' : 'asc';
                $query = $query->orderBy($value['id'], $orderBy);
            }
        }
        if(!empty($requestData['page']) && $requestData['page'] > 0){
            $offset = $requestData['page']*$requestData['pageSize'];
        }else{
            $offset = 0;
        }
        $queryResult['pages'] = intval($query->count()/$requestData['pageSize']);
        $queryResult['result'] = $query
                    ->offset($offset)
                    ->limit($requestData['pageSize'])
                    ->get()
                    ->map(function ($appointmentCategory) {
                            $appointmentCategory->appointment_cat_id = $this->securityLibObj->encrypt($appointmentCategory->appointment_cat_id);
                            return $appointmentCategory;
                        });
        return $queryResult;
    }

    /**
     * Create doctor service with regarding details
     *
     * @param array $data service data
     * @return Array doctor member if inserted otherwise false
     */

    public function createAppointmentCategory($requestData=array())
    {
        unset($requestData['appointment_cat_id']);
		$queryResult = $this->dbInsert($this->table, $requestData);
        if($queryResult){
            $appointmentCategoryData = $this->getAppointmentCategoryById(DB::getPdo()->lastInsertId());
            // Encrypt the ID
            $appointmentCategoryData->appointment_cat_id = $this->securityLibObj->encrypt(DB::getPdo()->lastInsertId());
            return $appointmentCategoryData;
        }
        return false;
    }

   /**
    * @DateOfCreation        22 May 2018
    * @ShortDescription      This function is responsible to get the service by id
    * @param                 String $appointment_cat_id   
    * @return                Array of service
    */
    public function getAppointmentCategoryById($appointment_cat_id)
    {   
    	$selectData = ['appointment_cat_id', 'appointment_cat_name'];
        $whereData = array(
                        'appointment_cat_id' =>  $appointment_cat_id, 
                        'is_deleted' => Config::get('constants.IS_DELETED_NO')
                    );
        $queryResult = $this->dbSelect($this->table, $selectData, $whereData);
        return $queryResult;
    }

    /**
     * Update doctor service with regarding details
     *
     * @param array $data service data
     * @return boolean true if updated otherwise false
     */
    public function updateAppointmentCategory($requestData=array())
    {
        $appointment_cat_id = $this->securityLibObj->decrypt($requestData['appointment_cat_id']);
        unset($requestData['appointment_cat_id']);
        $whereData =  array('appointment_cat_id' => $appointment_cat_id);
        $queryResult =  $this->dbUpdate($this->table, $requestData, $whereData);
        if($queryResult){
            $appointmentCategoryUpdateData = $this->getAppointmentCategoryById($appointment_cat_id);
            $appointmentCategoryUpdateData->appointment_cat_id = $this->securityLibObj->encrypt($appointment_cat_id);
            return $appointmentCategoryUpdateData;
        }
        return false;
    }
    /**
     * delete doctor service with regarding id
     *
     * @param int $id service id
     * @return boolean particular doctor service detail delete or not
     */
    public function deleteAppointmentCategory($appointment_cat_id='')
    {
        $updateData = array('is_deleted' => Config::get('constants.IS_DELETED_YES'));
        $whereData = array('appointment_cat_id' => $appointment_cat_id);
        $queryResult =  $this->dbUpdate($this->table, $updateData, $whereData);
        if($queryResult){
            return true;
        }
        return false;
    }

    /**
     * get doctor appointment reason list with regarding details
     *
     * @param array $data appointment reason data
     * @return int doctor member id if inserted otherwise false
     */
    public function getAppointmentReasons($requestData,$encrypt=true)
    {
        $selectData  =  ['appointment_cat_id', 'appointment_cat_name'];
        $whereData   =  [
                        'user_id'=> $requestData['user_id'],
                        'is_deleted'=>  Config::get('constants.IS_DELETED_NO'),
                        ];
        $queryResult =  DB::table($this->table)
                    ->select($selectData)
                    ->where($whereData)
                    ->get()
                    ->map(function ($appointmentCategory) use($encrypt) {
                        if($encrypt){
                            $appointmentCategory->appointment_cat_id = $this->securityLibObj->encrypt($appointmentCategory->appointment_cat_id);
                        }
                        return $appointmentCategory;
                    });
        return $queryResult;
    }

    /**
     * @DateOfCreation        08 Sept 2018
     * @ShortDescription      This function is to get the Primary key name
     * @return                integer primary key name id
     */
    public function getTablePrimaryIdColumn()
    {
        return $this->primaryKey;
    }
    
    /**
     * @DateOfCreation        08 Sept 2018
     * @ShortDescription      This function is responsible to check the primary value exist in the system or not
     * @param                 integer $primaryId   
     * @return                boolean
     */
    public function isPrimaryIdExist($primaryId){
        $primaryIdExist = DB::table($this->table)
                        ->where($this->primaryKey, $primaryId)
                        ->exists();
        return $primaryIdExist;
    }
}
