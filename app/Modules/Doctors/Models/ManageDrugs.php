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
 * ManageDrugs Class
 *
 * @package                ILD INDIA
 * @subpackage             ManageDrugs
 * @category               Model
 * @DateOfCreation         13 June 2018
 * @ShortDescription       This is model which need to perform the options related to 
                           ManageDrugs info

 */
class ManageDrugs extends Model {

    use Encryptable;

    // @var string $table
    // This protected member contains table name
    protected $table = 'doctor_medicines_relation';
    protected $tableMedicien = 'medicines';
    protected $tableJoinDrugType = 'drug_type';
    protected $tableJoinDrugDoseUnit = 'drug_dose_unit';

    // @var string $primaryKey
    // This protected member contains primary key
    protected $primaryKey = 'dmr_id';  


    protected $encryptable = [];

    protected $fillable = ['user_id', 'medicine_id', 'medicine_instractions', 'ip_address', 'resource_type'];

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
    public function getDrugList($requestData)
    {
        //DB::enableQueryLog();
        $listQuery = $this->drugListQuery($requestData['user_id']);

        if(!empty($requestData['filtered'])){
            foreach ($requestData['filtered'] as $key => $value) {

                if(!empty($value['value'])){
                    $listQuery = $listQuery->where(function ($listQuery) use ($value){
                                    $listQuery
                                    ->where('medicine_name', 'ilike', "%".$value['value']."%")
                                    ->orWhere('drug_type_name', 'ilike', '%'.$value['value'].'%')
                                    ->orWhere('drug_dose_unit_name', 'ilike', '%'.$value['value'].'%');
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
                                    $listData->dmr_id = $this->securityLibObj->encrypt($listData->dmr_id);
                                     $listData->medicine_instractions = !is_null($listData->medicine_instractions) && !empty($listData->medicine_instractions) ? $listData->medicine_instractions : [] ; 
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
    public function drugListQuery($userId){
        
        $selectData = [$this->tableMedicien.'.medicine_name',$this->tableMedicien.'.medicine_dose',$this->table.'.medicine_instractions',$this->tableJoinDrugDoseUnit.'.drug_dose_unit_name',$this->tableJoinDrugType.'.drug_type_name',$this->table.'.dmr_id'];
        

        $whereData = array(
                        $this->tableMedicien.'.is_deleted'      => Config::get('constants.IS_DELETED_NO')
                    );
        $listQuery = DB::table($this->tableMedicien)
                        ->join($this->table,function($join) use($userId){
                            $join->on($this->table.'.medicine_id', '=', $this->tableMedicien.'.medicine_id')
                            ->where($this->table.'.is_deleted', '=', Config::get('constants.IS_DELETED_NO'), 'and')
                            ->where($this->table.'.user_id', '=', $userId, 'and');
                        })->leftJoin($this->tableJoinDrugType,function($join) use($userId){
                            $join->on($this->tableJoinDrugType.'.drug_type_id', '=', $this->tableMedicien.'.drug_type_id')
                            ->where($this->tableJoinDrugType.'.is_deleted', '=', Config::get('constants.IS_DELETED_NO'), 'and');
                        })
                        ->leftJoin($this->tableJoinDrugDoseUnit,function($join) use($userId){
                            $join->on($this->tableJoinDrugDoseUnit.'.drug_dose_unit_id', '=', $this->tableMedicien.'.drug_dose_unit_id')
                            ->where($this->tableJoinDrugDoseUnit.'.is_deleted', '=', Config::get('constants.IS_DELETED_NO'), 'and');
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
    public function getAllMedicinRelation($param=[],$encrypt=true, $dmr_id=0) 
    {   
        $medicineId = isset($param['medicine_id']) ? $param['medicine_id'] :'';   
        $userId = isset($param['user_id']) ? $param['user_id'] :'';   
        $selectData = ['dmr_id','medicine_id','user_id'];
        $whereData = ['is_deleted'=>Config::get('constants.IS_DELETED_NO')];
        $queryResult = DB::table($this->table)
                        ->select($selectData)
                        ->where($whereData);
        if($dmr_id != 0){
            $queryResult->where('dmr_id', '!=', $dmr_id);
        }
        if($medicineId!=''){
            $queryResult = $queryResult->where('medicine_id','=',$medicineId);
        }
        if($userId!=''){
            $queryResult = $queryResult->where('user_id','=',(int) $userId);
        }
        $queryResult = $queryResult->get();
        if(count($queryResult)>0 && $encrypt){
            $queryResult = $queryResult->map(function($dataList){ 
                $dataList->medicine_id = $this->securityLibObj->encrypt($dataList->medicine_id);
                return $dataList;
            });
        }
        return $queryResult;
    }




}
