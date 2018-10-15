<?php

namespace App\Modules\MedicalHistory\Models;

use Illuminate\Database\Eloquent\Model;
use App\Libraries\SecurityLib;
use Illuminate\Support\Facades\DB;
use App\Traits\Encryptable;
use Config;

/**
 * MedicalHistory Class
 *
 * @package                MedicalHistory
 * @subpackage             Medical History
 * @category               Model
 * @DateOfCreation         7 june 2018
 * @ShortDescription       This is model which need to perform the options related to 
                           diseases table
 */
class MedicalHistory extends Model 
{
	use Encryptable;
    /**
     * The attributes that should be override default primary key.
     *
     * @var string 
     */
    protected $primaryKey = 'disease_id';

    /**
     * The attributes that should be override default table name.
     *
     * @var string 
     */
    protected $table = 'diseases';

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
    public function diseasesList($requestData)
    {
       	$selectData  =  ['disease_id', 'disease_name'];
        $whereData   =  [
                        'is_deleted'=>  Config::get('constants.IS_DELETED_NO'),
                        ];
        $query =  DB::table($this->table)
                    ->select($selectData)
                    ->where($whereData);
        /* Condition for Filtering the result */
        if(!empty($requestData['filtered'])){
            foreach ($requestData['filtered'] as $key => $value) {
                $query = $query->where(function ($query) use ($value){
                                $query
                                ->where('disease_name', 'ilike', "%".$value['value']."%");
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
        $queryResult['pages'] = ceil($query->count()/$requestData['pageSize']);
        $queryResult['result'] = $query
                    ->offset($offset)
                    ->limit($requestData['pageSize'])
                    ->get()
                    ->map(function ($disease) {
                            $disease->disease_id = $this->securityLibObj->encrypt($disease->disease_id);
                            return $disease;
                        });
        return $queryResult;
    }

    /**
     * Create doctor disease with regarding details
     *
     * @param array $data disease data
     * @return Array doctor member if inserted otherwise false
     */

    public function saveDisease($requestData=array())
    {
        $disease_id = $this->securityLibObj->decrypt($requestData['disease_id']);
        unset($requestData['disease_id']);
        if(!empty($disease_id)){
            $whereData =  array('disease_id' => $disease_id);
            $queryResult =  $this->dbUpdate($this->table, $requestData, $whereData);
            if($queryResult){
                $diseaseUpdateData = $this->getDiseaseById($disease_id);
                $diseaseUpdateData->disease_id = $this->securityLibObj->encrypt($disease_id);
                return $diseaseUpdateData;
            }
        }else{
            $queryResult = $this->dbInsert($this->table, $requestData);
            if($queryResult){
                $diseaseData = $this->getDiseaseById(DB::getPdo()->lastInsertId());
                // Encrypt the ID
                $diseaseData->disease_id = $this->securityLibObj->encrypt(DB::getPdo()->lastInsertId());
                return $diseaseData;
            }
        }
        return false;
    }

   /**
    * @DateOfCreation        22 May 2018
    * @ShortDescription      This function is responsible to get the disease by id
    * @param                 String $disease_id   
    * @return                Array of disease
    */
    public function getDiseaseById($disease_id)
    {   
    	$selectData = ['disease_id', 'disease_name'];
        $whereData = array(
                        'disease_id' =>  $disease_id, 
                        'is_deleted' => Config::get('constants.IS_DELETED_NO')
                    );
        $queryResult = $this->dbSelect($this->table, $selectData, $whereData);
        return $queryResult;
    }
    
    /**
     * delete doctor disease with regarding id
     *
     * @param int $id disease id
     * @return boolean particular doctor disease detail delete or not
     */
    public function deleteDisease($disease_id='')
    {
        $updateData = array('is_deleted' => Config::get('constants.IS_DELETED_YES'));
        $whereData = array('disease_id' => $disease_id );
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
