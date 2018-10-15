<?php

namespace App\Modules\Referral\Models;

use Illuminate\Database\Eloquent\Model;
use App\Libraries\SecurityLib;
use Illuminate\Support\Facades\DB;
use App\Traits\Encryptable;
use Config;

/**
 * Referral Class
 *
 * @package                Referral
 * @subpackage             Doctor Referral
 * @category               Model
 * @DateOfCreation         7 june 2018
 * @ShortDescription       This is model which need to perform the options related to 
                           Referral table
 */
class Referral extends Model 
{
	use Encryptable;
    /**
     * The attributes that should be override default primary key.
     *
     * @var string 
     */
    protected $primaryKey = 'doc_ref_id';

    /**
     * The attributes that should be override default table name.
     *
     * @var string 
     */
    protected $table = 'doctor_referral';

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
    public function getList($requestData)
    {
       	$selectData  =  ['doc_ref_id', 'doc_ref_name', 'doc_ref_mobile'];
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
                    $query = $query->where('doc_ref_name', 'ilike', "%".$value['value']."%");
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
        
                   
        $queryResult['result']  = $query->get()
                                ->map(function ($referral) {
                                    $referral->doc_ref_id = $this->securityLibObj->encrypt($referral->doc_ref_id);
                                    return $referral;
                                });
        return $queryResult;
    }

    /**
     * Create doctor service with regarding details
     *
     * @param array $data service data
     * @return Array doctor member if inserted otherwise false
     */

    public function createReferral($requestData=array())
    {
        if(!empty($requestData['city_id'])){
            unset($requestData['city_id']);
        }
        if(array_key_exists('pat_group_id',$requestData)){
            unset($requestData['pat_group_id']);
        }
        if(array_key_exists('pat_group_name', $requestData)){
            unset($requestData['pat_group_name']);
        }
        unset($requestData['doc_ref_id']);
		$queryResult = $this->dbInsert($this->table, $requestData);
        if($queryResult){
            $referralData = $this->getReferralById(DB::getPdo()->lastInsertId());
            // Encrypt the ID
            $referralData->doc_ref_id = $this->securityLibObj->encrypt(DB::getPdo()->lastInsertId());
            return $referralData;
        }
        return false;
    }

   /**
    * @DateOfCreation        22 May 2018
    * @ShortDescription      This function is responsible to get the service by id
    * @param                 String $doc_ref_id   
    * @return                Array of service
    */
    public function getReferralById($doc_ref_id)
    {   
    	$selectData = ['doc_ref_id', 'doc_ref_name', 'doc_ref_mobile'];
        $whereData = array(
                        'doc_ref_id' =>  $doc_ref_id, 
                        'is_deleted' => Config::get('constants.IS_DELETED_NO')
                    );
        $queryResult = $this->dbSelect($this->table, $selectData, $whereData);
        return $queryResult;
    }

   /**
    * @DateOfCreation        03 Sept 2018
    * @ShortDescription      This function is responsible to get the referral id by name
    * @param                 String $doc_ref_name
    * @return                Array of service
    */
    public function getReferralIdByName($doc_ref_name)
    {   
        $selectData = ['doc_ref_id'];
        $whereData = array(
                        'doc_ref_name' =>  $doc_ref_name, 
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
    public function updateReferral($requestData=array())
    {
        $doc_ref_id = $this->securityLibObj->decrypt($requestData['doc_ref_id']);
        unset($requestData['doc_ref_id']);
        $whereData =  array('doc_ref_id' => $doc_ref_id);
        $queryResult =  $this->dbUpdate($this->table, $requestData, $whereData);
        if($queryResult){
            $referralUpdateData = $this->getReferralById($doc_ref_id);
            $referralUpdateData->doc_ref_id = $this->securityLibObj->encrypt($doc_ref_id);
            return $referralUpdateData;
        }
        return false;
    }
    /**
     * delete doctor service with regarding id
     *
     * @param int $id service id
     * @return boolean particular doctor service detail delete or not
     */
    public function deleteReferral($doc_ref_id='')
    {
        $updateData = array('is_deleted' => Config::get('constants.IS_DELETED_YES'));
        $whereData = array('doc_ref_id' => $doc_ref_id);
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
