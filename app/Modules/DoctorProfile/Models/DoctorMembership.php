<?php

namespace App\Modules\DoctorProfile\Models;

use Illuminate\Database\Eloquent\Model;
use App\Libraries\SecurityLib;
use App\Traits\Encryptable;
use Illuminate\Support\Facades\DB;
use Config;
/**
 * DoctorMembership Class
 *
 * @package                Doctor Profile
 * @subpackage             Doctor Membership
 * @category               Model
 * @DateOfCreation         11 May 2018
 * @ShortDescription       This is model which need to perform the options related to 
                           doctor membership table
 */

class DoctorMembership extends Model 
{
    use Encryptable;
     /**
     * The attributes that should be override default primary key.
     *
     * @var string 
     */
    protected $primaryKey = 'doc_mem_id';

    /**
     * The attributes that should be override default table name.
     *
     * @var string 
     */
    protected $table = 'doctor_membership';

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
     * Create doctor membership list with regarding details
     *
     * @param array $data membership data
     * @return int doctor member id if inserted otherwise false
     */
    public function membershipList($requestData='', $method='POST')
    {
        if($method == Config::get('constants.REQUEST_TYPE_GET')){
            $whereData  = ['user_id'=> $requestData['user_id'],'is_deleted'=>  Config::get('constants.IS_DELETED_NO')];
            $selectData = ['doc_mem_id', 'user_id', 'doc_mem_name', 'doc_mem_no', 'doc_mem_year'];
            $queryResult = $this->dbBatchSelect($this->table, $selectData, $whereData)
                            ->map(function($doctorMembership){
                                $doctorMembership->doc_mem_id = $this->securityLibObj->encrypt($doctorMembership->doc_mem_id);
                                return $doctorMembership;
                            });
            return $queryResult;
        }

        $selectData  =  ['doc_mem_id', 'doc_mem_name', 'doc_mem_no', 'doc_mem_year'];
        $whereData   =  [
                        'user_id'       => $requestData['user_id'],
                        'is_deleted'    =>  Config::get('constants.IS_DELETED_NO'),
                        'doc_mem_status'=> Config::get('constants.IS_ACTIVE_YES')
                        ];

        $query =  DB::table($this->table)
                    ->select($selectData)
                    ->where($whereData);

        /* Condition for Filtering the result */
        if(!empty($requestData['filtered'])){
            foreach ($requestData['filtered'] as $key => $value) {
                  $query = $query->where(function ($query) use ($value){
                                $query
                                ->where('doc_mem_name', 'like', '%'.$value['value'].'%')
                                ->orWhere(DB::raw('CAST(doc_mem_year AS TEXT)'), 'like', '%'.$value['value'].'%')
                                ->orWhere(DB::raw('CAST(doc_mem_no AS TEXT)'), 'like', '%'.$value['value'].'%');
                            });
            }
           
        }

        /* Condition for Sorting the result */
        if(!empty($requestData['sorted'])){
            foreach ($requestData['sorted'] as $key => $value) {
                $orderBy = $value['desc'] ? 'desc' : 'asc';
                $query = $query->orderBy($value['id'], $orderBy);
            }
        }
        if($requestData['page'] > 0){
            $offset = $requestData['page']*$requestData['pageSize'];
        }else{
            $offset = 0;
        }
        $doctorMembership['pages'] = ceil($query->count()/$requestData['pageSize']);
        $doctorMembership['result'] = $query
                    ->offset($offset)
                    ->limit($requestData['pageSize'])
                    ->get()
                    ->map(function($doctorMembership){
                            $doctorMembership->doc_mem_id = $this->securityLibObj->encrypt($doctorMembership->doc_mem_id);
                            return $doctorMembership;
                        })->toArray();
       
        return $doctorMembership;
    }

    /**
     * Create doctor membership with regarding details
     *
     * @param array $data membership data
     * @return Array doctor member if inserted otherwise false
     */
    public function createMembership($requestData=array())
    {
        $requestData['doc_mem_status'] = Config::get('constants.IS_ACTIVE_YES');
        $queryResult = $this->dbInsert($this->table, $requestData);

        if($queryResult){
            $memberData = $this->getMembershipById(DB::getPdo()->lastInsertId());
            // Encrypt the ID
            $memberData->doc_mem_id = $this->securityLibObj->encrypt(DB::getPdo()->lastInsertId());
            return $memberData;
        }
        return false;
    }

     /**
    * @DateOfCreation        22 May 2018
    * @ShortDescription      This function is responsible to get the membership by id
    * @param                 String $doc_mem_id   
    * @return                Array of membership
    */
    public function getMembershipById($doc_mem_id)
    {   $selectData = ['doc_mem_name', 'doc_mem_no', 'doc_mem_year'];

        $whereData = array(
                        'doc_mem_id' =>  $doc_mem_id, 
                        'is_deleted' => Config::get('constants.IS_DELETED_NO'), 
                        'doc_mem_status' => Config::get('constants.IS_ACTIVE_YES')
                    );
        $queryResult = $this->dbSelect($this->table, $selectData, $whereData);
        return $queryResult;
    }

    /**
     * Update doctor membership with regarding details
     *
     * @param array $data membership data
     * @return boolean true if updated otherwise false
     */
    public function updateMembership($requestData=array())
    {

        $doc_mem_id = $this->securityLibObj->decrypt($requestData['doc_mem_id']);
        unset($requestData['doc_mem_id']);
        
        $whereData =  array('doc_mem_id' => $doc_mem_id);
        $queryResult =  $this->dbUpdate($this->table, $requestData, $whereData);

        if($queryResult){
            $membershipUpdateData = $this->getMembershipById($doc_mem_id);
            $membershipUpdateData->doc_mem_id = $this->securityLibObj->encrypt($doc_mem_id);
            return $membershipUpdateData;
        }
        return false;
    }

    /**
     * delete doctor membership with regarding id
     *
     * @param int $id membership id
     * @return boolean particular doctor membership detail delete or not
     */
    public function deleteMembership($doc_mem_id='')
    {
        $updateData = array(
                        'is_deleted' => Config::get('constants.IS_DELETED_YES')
                        );
        $whereData = array( 'doc_mem_id' => $doc_mem_id );
        
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
