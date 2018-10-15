<?php

namespace App\Modules\CheckupType\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\HasApiTokens;
use App\Traits\Encryptable;
use Illuminate\Support\Facades\DB;
use App\Libraries\SecurityLib;
use App\Libraries\UtilityLib;
use App\Libraries\DateTimeLib;
use Config;

/**
 * CheckupType Class
 *
 * @package                ILD INDIA
 * @subpackage             CheckupType
 * @category               Model
 * @DateOfCreation         04 Oct 2018
 * @ShortDescription       This is model which need to perform the options related to 
                           CheckupType info

 */
class CheckupType extends Model {

    use Encryptable;

    // @var string $table
    // This protected member contains table name
    protected $table = 'checkup_type';

    // @var string $primaryKey
    // This protected member contains primary key
    protected $primaryKey = 'checkup_type_id';  


    protected $encryptable = [];

    protected $fillable = ['user_id', 'checkup_type_id','checkup_type', 'ip_address', 'resource_type'];

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
     * @DateOfCreation        04 Oct 2018
     * @ShortDescription      This function is responsible for get all Patients Records by user_id and list fillter and sorting apply for selected column
     * @param                 Array $data This contains full Patient user input data 
     * @return                True/False
     */
    public function getCheckupTypeList($requestData)
    {
        //DB::enableQueryLog();
        $listQuery = $this->checkupTypeListQuery($requestData['user_id']);

        if(!empty($requestData['filtered'])){
            foreach ($requestData['filtered'] as $key => $value) {

                if(!empty($value['value'])){
                    $listQuery = $listQuery->where(function ($listQuery) use ($value){
                                    $listQuery
                                    ->where('checkup_type', 'ilike', "%".$value['value']."%");
                                });
                }
            }
        }

        if(!empty($requestData['sorted'])){
            foreach ($requestData['sorted'] as $sortKey => $sortValue) {
                $orderBy = $sortValue['desc'] ? 'desc' : 'asc';
                $listQuery->orderBy($sortValue['id'], $orderBy);
            }
        }

        if($requestData['page'] > 0){
            $offset = $requestData['page'] * $requestData['pageSize'];
        }else{
            $offset = 0;            
        }

        $list['pages']   = ceil($listQuery->count()/$requestData['pageSize']);
        
        $list['result']  = $listQuery
                                ->offset($offset)
                                ->limit($requestData['pageSize'])
                                ->get()
                                ->map(function($listData){
                                    $listData->checkup_type_id = $this->securityLibObj->encrypt($listData->checkup_type_id);
                                    return $listData;
                                }); 
        return $list;
    }

    /**
     * @DateOfCreation        04 Oct 2018
     * @ShortDescription      This function is responsible for patient list query from user and patient tables
     * @param                 Array $data This contains full Patient user input data 
     * @return                Array of patients
     */
    public function checkupTypeListQuery($userId){
        
        $selectData = [$this->table.'.checkup_type',$this->table.'.checkup_type_id'];
        

        $whereData = array(
                        $this->table.'.is_deleted'      => Config::get('constants.IS_DELETED_NO'),
                        $this->table.'.user_id'         => $userId
                    );
        $listQuery = DB::table($this->table)
                        ->select($selectData)
                        ->where($whereData);     
                                  
        return $listQuery;
    }
    
    /**
     * @DateOfCreation        04 Oct 2018
     * @ShortDescription      This function is responsible to save record for the Patient Medication History
     * @param                 array $requestData   
     * @return                integer Patient Medication History id
     */
    public function getTableName()
    {
        return $this->table;
    }

    /**
     * @DateOfCreation        04 Oct 2018
     * @ShortDescription      This function is responsible to save record for the Patient Medication History
     * @param                 array $requestData   
     * @return                integer Patient Medication History id
     */
    public function getTablePrimaryIdColumn()
    {
        return $this->primaryKey;
    }

    /**
     * @DateOfCreation        04 Oct 2018
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
     * @DateOfCreation        04 Oct 2018
     * @ShortDescription      This function is responsible to Delete Checkup Type data
     * @param                 integer $wefId   
     * @return                Array of status and message
     */
    public function doDeleteRequest($primaryId)
    {
        $queryResult = $this->dbUpdate( $this->table, 
                                        [ 'is_deleted' => Config::get('constants.IS_DELETED_YES') ], 
                                        [$this->primaryKey => $primaryId]
                                    );

        if($queryResult){
            return true;
        }
        return false;
    }

    /**
    * @DateOfCreation        04 Oct 2018
    * @ShortDescription      This function is responsible to update Checkup Type Record
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

    public function getAllCheckupType($param=[],$encrypt=true) 
    {   
        $name = isset($param['checkup_type']) ? $param['checkup_type'] :'';   
        
        $selectData = ['checkup_type_id','checkup_type'];
        $whereData = ['is_deleted'=>Config::get('constants.IS_DELETED_NO'), 'user_id' => $param['user_id']];
        $queryResult = DB::table($this->table)
                        ->select($selectData)
                        ->where($whereData);     
        if($name!=''){
            $queryResult = $queryResult->where('checkup_type','ilike',$name);
        }
        $queryResult = $queryResult->get();
        if(count($queryResult)>0 && $encrypt){
            $queryResult = $queryResult->map(function($dataList){ 
                $dataList->checkup_type_id = $this->securityLibObj->encrypt($dataList->checkup_type_id);
                return $dataList;
            });
        }
        return $queryResult;
    }

    /**
     * @DateOfCreation        04 Oct 2018
     * @ShortDescription      This function is responsible to save record for the Checkup Type
     * @param                 array $requestData   
     * @return                integer auto increment id
     */
    public function saveCheckupType($inserData)
    {
        // @var Boolean $response
        // This variable contains insert query response
        $response = false;
        unset($inserData['checkup_type_id']);

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


}
