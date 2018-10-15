<?php

namespace App\Modules\PatientGroups\Models;

use Illuminate\Database\Eloquent\Model;
use App\Libraries\SecurityLib;
use Illuminate\Support\Facades\DB;
use App\Traits\Encryptable;
use Config;

/**
 * PatientGroups Class
 *
 * @package                PatientGroups
 * @subpackage             Doctor PatientGroups
 * @category               Model
 * @DateOfCreation         7 june 2018
 * @ShortDescription       This is model which need to perform the options related to 
                           PatientGroups table
 */
class PatientGroups extends Model 
{
    use Encryptable;
    /**
     * The attributes that should be override default primary key.
     *
     * @var string 
     */
    protected $primaryKey = 'pat_group_id';

    /**
     * The attributes that should be override default table name.
     *
     * @var string 
     */
    protected $table = 'patient_groups';

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
     * Create patient groups list with regarding details
     *
     * @param array $data patient groups data
     * @return array list of groups
     */
    public function getList($requestData)
    {
        $selectData  =  ['pat_group_id', 'pat_group_name'];
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
                    $query = $query->where('pat_group_name', 'ilike', "%".$value['value']."%");
                }
            }
        
        /* Condition for Sorting the result */
        if(!empty($requestData['sorted'])){
            foreach ($requestData['sorted'] as $key => $value) {
                $orderBy = $value['desc'] ? 'desc' : 'asc';
                $query = $query->orderBy($value['id'], $orderBy);
            }
        }
        $offset = 0;
        if(!empty($requestData['page']) && $requestData['page'] > 0){
            $offset = $requestData['page']*Config::get('constants.DATA_LIMIT');
            $query = $query->offset($offset)
                     ->limit(Config::get('constants.DATA_LIMIT'));
            $queryResult['pages'] = ceil($query->count()/Config::get('constants.DATA_LIMIT'));
        }
        $queryResult['result'] = $query->get()
                    ->map(function ($patientGroups) {
                            $patientGroups->pat_group_id = $this->securityLibObj->encrypt($patientGroups->pat_group_id);
                            return $patientGroups;
                        });
        return $queryResult;
    }

    /**
     * Create doctor service with regarding details
     *
     * @param array $data service data
     * @return Array doctor member if inserted otherwise false
     */

    public function createPatientGroup($requestData=array())
    {
        if(!empty($requestData['city_id'])){
            unset($requestData['city_id']);
        }
        if(array_key_exists('doc_ref_id',$requestData)){
            unset($requestData['doc_ref_id']);
        }
        if(array_key_exists('doc_ref_name', $requestData)){
            unset($requestData['doc_ref_name']);
        }
        unset($requestData['pat_group_id']);
        $queryResult = $this->dbInsert($this->table, $requestData);
        if($queryResult){
            $patientGroupData = $this->getPatientGroupById(DB::getPdo()->lastInsertId());
            // Encrypt the ID
            $patientGroupData->pat_group_id = $this->securityLibObj->encrypt(DB::getPdo()->lastInsertId());
            return $patientGroupData;
        }
        return false;
    }

   /**
    * @DateOfCreation        22 May 2018
    * @ShortDescription      This function is responsible to get the service by id
    * @param                 String $pat_group_id   
    * @return                Array of service
    */
    public function getPatientGroupById($pat_group_id)
    {   
        $selectData = ['pat_group_id', 'pat_group_name'];
        $whereData = array(
                        'pat_group_id' =>  $pat_group_id, 
                        'is_deleted' => Config::get('constants.IS_DELETED_NO')
                    );
        $queryResult = $this->dbSelect($this->table, $selectData, $whereData);
        return $queryResult;
    }

    /**
    * @DateOfCreation        26 Sept 2018
    * @ShortDescription      This function is responsible to get the groups by user id
    * @param                 String $pat_group_id   
    * @return                Array of service
    */
    public function getPatientGroupByUserId($user_id)
    {   
        $selectData = ['pat_group_id', 'pat_group_name'];
        $whereData = array(
                        'user_id'    =>  $pat_group_id, 
                        'is_deleted' => Config::get('constants.IS_DELETED_NO')
                    );
        $queryResult = $this->dbBatchSelect($this->table, $selectData, $whereData);
        return $queryResult;
    }

   /**
    * @DateOfCreation        03 Sept 2018
    * @ShortDescription      This function is responsible to get the patient group id by name
    * @param                 String $pat_group_name   
    * @return                Array of service
    */
    public function getPatientGroupIdByName($pat_group_name)
    {   
        $selectData = ['pat_group_id'];
        $whereData = array(
                        'pat_group_name' =>  $pat_group_name, 
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
    public function updatePatientGroup($requestData=array())
    {
        $pat_group_id = $this->securityLibObj->decrypt($requestData['pat_group_id']);
        unset($requestData['pat_group_id']);
        $whereData =  array('pat_group_id' => $pat_group_id);
        $queryResult =  $this->dbUpdate($this->table, $requestData, $whereData);
        if($queryResult){
            $patientGroupUpdateData = $this->getPatientGroupById($pat_group_id);
            $patientGroupUpdateData->pat_group_id = $this->securityLibObj->encrypt($pat_group_id);
            return $patientGroupUpdateData;
        }
        return false;
    }
    /**
     * delete doctor service with regarding id
     *
     * @param int $id service id
     * @return boolean particular doctor service detail delete or not
     */
    public function deletePatientGroup($pat_group_id='')
    {
        $updateData = array('is_deleted' => Config::get('constants.IS_DELETED_YES'));
        $whereData = array('pat_group_id' => $pat_group_id);
        $queryResult =  $this->dbUpdate($this->table, $updateData, $whereData);
        if($queryResult){
            return true;
        }
        return false;
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
