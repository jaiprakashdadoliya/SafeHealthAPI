<?php

namespace App\Modules\LaboratoryTests\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\HasApiTokens;
use App\Traits\Encryptable;
use Illuminate\Support\Facades\DB;
use App\Libraries\SecurityLib;
use App\Libraries\UtilityLib;
use App\Libraries\DateTimeLib;
use Config;

/**
 * LaboratoryTests Class
 *
 * @package                ILD INDIA
 * @subpackage             LaboratoryTests
 * @category               Model
 * @DateOfCreation         13 June 2018
 * @ShortDescription       This is model which need to perform the options related to 
                           LaboratoryTests info

 */
class LaboratoryTests extends Model {

    use Encryptable;

    // @var string $table
    // This protected member contains table name
    protected $table = 'master_laboratory_tests_relation';
    protected $tableLaboratoryTests = 'master_laboratory_tests';

    // @var string $primaryKey
    // This protected member contains primary key
    protected $primaryKey = 'lab_test_relation_id';  


    protected $encryptable = [];

    protected $fillable = ['user_id', 'mlt_id','mlt_name', 'ip_address', 'resource_type'];

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
     * @DateOfCreation        19 June 2018
     * @ShortDescription      This function is responsible for get all Patients Records by user_id and list fillter and sorting apply for selected column
     * @param                 Array $data This contains full Patient user input data 
     * @return                True/False
     */
    public function getLaboratoryTestsList($requestData)
    {
        //DB::enableQueryLog();
        $listQuery = $this->laboratoryTestsListQuery($requestData['user_id']);

        if(!empty($requestData['filtered'])){
            foreach ($requestData['filtered'] as $key => $value) {

                if(!empty($value['value'])){
                    $listQuery = $listQuery->where(function ($listQuery) use ($value){
                                    $listQuery
                                    ->where('mlt_name', 'ilike', "%".$value['value']."%");
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
                                    $listData->lab_test_relation_id = $this->securityLibObj->encrypt($listData->lab_test_relation_id);
                                    return $listData;
                                }); 
        return $list;
    }

    /**
     * @DateOfCreation        20 June 2018
     * @ShortDescription      This function is responsible for patient list query from user and patient tables
     * @param                 Array $data This contains full Patient user input data 
     * @return                Array of patients
     */
    public function laboratoryTestsListQuery($userId){
        
        $selectData = [$this->tableLaboratoryTests.'.mlt_name',$this->table.'.lab_test_relation_id'];
        

        $whereData = array(
                        $this->tableLaboratoryTests.'.is_deleted'      => Config::get('constants.IS_DELETED_NO')
                    );
        $listQuery = DB::table($this->tableLaboratoryTests)
                        ->join($this->table,function($join) use($userId){
                            $join->on($this->table.'.mlt_id', '=', $this->tableLaboratoryTests.'.mlt_id')
                            ->where($this->table.'.is_deleted', '=', Config::get('constants.IS_DELETED_NO'), 'and')
                            ->where($this->table.'.user_id', '=', $userId, 'and');
                        })
                        ->select($selectData)
                        ->where($whereData);     
                                  
        return $listQuery;
    }
    
    /**
     * @DateOfCreation        18 June 2018
     * @ShortDescription      This function is responsible to save record for the Patient Medication History
     * @param                 array $requestData   
     * @return                integer Patient Medication History id
     */
    public function getTableName()
    {
        return $this->table;
    }

    /**
     * @DateOfCreation        18 June 2018
     * @ShortDescription      This function is responsible to save record for the Patient Medication History
     * @param                 array $requestData   
     * @return                integer Patient Medication History id
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
     * @DateOfCreation        11 June 2018
     * @ShortDescription      This function is responsible to Delete medicines data
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
     * @DateOfCreation        18 June 2018
     * @ShortDescription      This function is responsible to save record for the medicine
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
    * @ShortDescription      This function is responsible to update medicine Record
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

    /**
     * @DateOfCreation        26 June 2018
     * @ShortDescription      This function is responsible to get the all Medicines record
     * @return                object Array of HRCT records
     */
    public function getAllLaboratoryTestsRelation($param=[],$encrypt=true, $lab_test_relation_id=0) 
    {   
        $mltId = isset($param['mlt_id']) ? $param['mlt_id'] :'';   
        $userId = isset($param['user_id']) ? $param['user_id'] :'';   
        $selectData = ['lab_test_relation_id','mlt_id','user_id'];
        $whereData = ['is_deleted'=>Config::get('constants.IS_DELETED_NO')];
        $queryResult = DB::table($this->table)
                        ->select($selectData)
                        ->where($whereData);
        if($lab_test_relation_id != 0){
            $queryResult->where('lab_test_relation_id', '!=', $lab_test_relation_id);
        }
        if($mltId!=''){
            $queryResult = $queryResult->where('mlt_id','=',$mltId);
        }
        if($userId!=''){
            $queryResult = $queryResult->where('user_id','=',(int) $userId);
        }
        $queryResult = $queryResult->get();
        if(count($queryResult)>0 && $encrypt){
            $queryResult = $queryResult->map(function($dataList){ 
                $dataList->mlt_id = $this->securityLibObj->encrypt($dataList->mlt_id);
                return $dataList;
            });
        }
        return $queryResult;
    }



    /**
     * @DateOfCreation        26 June 2018
     * @ShortDescription      This function is responsible to get the all Medicines record
     * @return                object Array of HRCT records
     */
    public function getAllUniqueLaboratoryTestsName($param=[],$encrypt=true) 
    {   
        $name = isset($param['mlt_name']) ? $param['mlt_name'] :'';    
        $selectData = "DISTINCT ON (mlt_name) mlt_name, mlt_id";
        $whereData = ['is_deleted'=>Config::get('constants.IS_DELETED_NO')];
        $queryResult = DB::table($this->tableLaboratoryTests)
                        ->select(DB::raw($selectData))
                        ->where($whereData);     
        if($name!=''){
            $queryResult = $queryResult->where('mlt_name','ilike',$name);
        }
        $queryResult = $queryResult->get();
        if(count($queryResult)>0 && $encrypt){
            $queryResult = $queryResult->map(function($dataList){ 
                $dataList->mlt_id = $this->securityLibObj->encrypt($dataList->mlt_id);
                return $dataList;
            });
        }
        return $queryResult;
    }

    public function getAllLaboratoryTests($param=[],$encrypt=true) 
    {   
        $name = isset($param['mlt_name']) ? $param['mlt_name'] :'';   
        
        $selectData = ['mlt_id','mlt_name'];
        $whereData = ['is_deleted'=>Config::get('constants.IS_DELETED_NO')];
        $queryResult = DB::table($this->tableLaboratoryTests)
                        ->select($selectData)
                        ->where($whereData);     
        if($name!=''){
            $queryResult = $queryResult->where('mlt_name','ilike',$name);
        }
        $queryResult = $queryResult->get();
        if(count($queryResult)>0 && $encrypt){
            $queryResult = $queryResult->map(function($dataList){ 
                $dataList->mlt_id = $this->securityLibObj->encrypt($dataList->mlt_id);
                return $dataList;
            });
        }
        return $queryResult;
    }

    /**
     * @DateOfCreation        18 June 2018
     * @ShortDescription      This function is responsible to save record for the Medicines
     * @param                 array $requestData   
     * @return                integer auto increment id
     */
    public function saveLaboratoryTest($inserData)
    {
        // @var Boolean $response
        // This variable contains insert query response
        $response = false;

        // @var Array $inserData
        // This Array contains insert data for Patient
        $inserData = $this->utilityLibObj->fillterArrayKey($inserData, $this->fillable);
                
        // Prepair insert query
        $response = $this->dbInsert($this->tableLaboratoryTests, $inserData);
        if($response){
            $id = DB::getPdo()->lastInsertId();
            return $id;
            
        }else{
            return $response;
        }          
    }


}
