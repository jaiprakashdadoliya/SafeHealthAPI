<?php 
namespace App\Traits;
use Illuminate\Support\Facades\Crypt;
use App\Libraries\SecurityLib;
use DB;
use Auth;
use App\Libraries\DateTimeLib;
use App\Libraries\Fxlogs;
use Config;
/**
 * Encryptable
 *
 * @package                ILD Registry
 * @subpackage             Encryptable
 * @category               Trait
 * @DateOfCreation         11 June 2018
 * @ShortDescription       This trait is responsible to Encrypt the input and decrypt 
                           the output when the request is made by user
 **/
trait Encryptable
{

     /**
    * @DateOfCreation        11 June 2018
    * @ShortDescription      This function is responsible for Encrypt the input value 
    * @param                 Array $requestData   
    * @return                Response (Submit attributes)
    */
    public function encryptData($requestData){
        $securityObj = new SecurityLib;
        foreach ($requestData as $key => $value) {
            if(in_array($key, $this->encryptable)){
                 $requestData[$key] = $securityObj->encrypt($value);
            }
        }
        return $requestData;
    }

    /**
    * @DateOfCreation        11 June 2018
    * @ShortDescription      This function is responsible for decrypt the output value 
    * @param                 Array $requestData
    * @return                Response (Retrive attributes)
    */
    public function decryptMultipleData($requestData){
        $securityObj = new SecurityLib;
        foreach ($requestData as $value) {
            $this->decryptSingleData($value);
        }
        return $requestData;
    }

    /**
    * @DateOfCreation        11 June 2018
    * @ShortDescription      This function is responsible for decrypt the output value 
    * @param                 Array object type $requestData
    * @return                Response (Retrive attributes)
    */
    public function decryptSingleData($requestData){
        $securityObj = new SecurityLib;
        foreach ($requestData as $key => $value) {
            if(in_array($key, $this->encryptable)){
                 $requestData->$key = $securityObj->decrypt($value);
            }
        }
        return $requestData;
    }


    /**
    * @DateOfCreation        11 June 2018
    * @ShortDescription      This function is responsible for insert data in the Database 
    * @param                 String $tableName
                             Array  $requestData   
    * @return                Response True/False
    */
    public function dbInsert($tableName, $requestData){
        $dateTimeObj = new DateTimeLib();
         $fxlogsObj   = new Fxlogs();

        if(auth()->guard('api')->check()){
            $user_id = auth()->guard('api')->user()->user_id;
        }else{
            $user_id  = 0;
        }
        $requestData['created_by'] = $user_id;
        $requestData['updated_by'] = $user_id;
        $requestData['created_at'] = $dateTimeObj->getPostgresTimestampAfterXmin(0);
        $requestData['updated_at'] = $dateTimeObj->getPostgresTimestampAfterXmin(0);
        
        $insertData = $this->encryptData($requestData);
        $fxlogsObj->insertUpdateLog($tableName,['requestData'=>$insertData,Config::get('constants.LOG_OPERATION_INDEX_NAME')=>Config::get('constants.LOG_TABLE_INSERT_OPERATION_DATA_TYPE')]);
        return DB::table($tableName)->insert($insertData);
    }

    /**
    * @DateOfCreation        11 June 2018
    * @ShortDescription      This function is responsible for update data in the Database 
    * @param                 String $tableName
                             Array  $requestData
                             Array  $whereData   
    * @return                Response True/False
    */
    public function dbUpdate($tableName, $requestData, $whereData){
        $dateTimeObj = new DateTimeLib();
         $fxlogsObj   = new Fxlogs();

        if(auth()->guard('api')->check()){
            $user_id = auth()->guard('api')->user()->user_id;
        }else{
            $user_id  = 0;
        }

        $requestData['updated_by'] = $user_id;
        $requestData['updated_at'] = $dateTimeObj->getPostgresTimestampAfterXmin(0);

        $updateData = $this->encryptData($requestData);
        $fxlogsObj->insertUpdateLog($tableName,['requestData' => $updateData,Config::get('constants.LOG_OPERATION_INDEX_NAME') => Config::get('constants.LOG_TABLE_UPDATE_OPERATION_DATA_TYPE'),'whereData' => $whereData]);
        return DB::table($tableName)
                        ->where($whereData)
                        ->update($updateData);
    }

    /**
    * @DateOfCreation        11 June 2018
    * @ShortDescription      This function is responsible for Fetch single data from the Database 
    * @param                 String $tableName
                             Array  $selectData
                             Array  $whereData   
    * @return                Response True/False
    */
    public function dbSelect($tableName, $selectData, $whereData){
        $result = DB::table($tableName)
        ->select($selectData)
        ->where($whereData)
        ->first();
        return !empty($result) ? $this->decryptSingleData($result):[];
    }

    /**
    * @DateOfCreation        11 June 2018
    * @ShortDescription      This function is responsible for Fetch multiple data from the Database 
    * @param                 String $tableName
                             Array  $selectData
                             Array  $whereData   
    * @return                Response True/False
    */
    public function dbBatchSelect($tableName, $selectData, $whereData){
        $result = DB::table($tableName)
        ->select($selectData)
        ->where($whereData)
        ->get();
        return !empty($result) ? $this->decryptMultipleData($result):[];
    }

    /**
    * @DateOfCreation        11 June 2018
    * @ShortDescription      This function is responsible for multiple insert data in the Database 
    * @param                 String $tableName
                             multiple dimension Array $requestData   
    * @return                Response True/False
    */
    public function dbBatchInsert($tableName, $requestData){
        $dateTimeObj = new DateTimeLib();
         $fxlogsObj   = new Fxlogs();

        if(auth()->guard('api')->check()){
            $user_id = auth()->guard('api')->user()->user_id;
        }else{
            $user_id  = 0;
        }
        $createdAt = $dateTimeObj->getPostgresTimestampAfterXmin(0);
        $updatedAt = $dateTimeObj->getPostgresTimestampAfterXmin(0);

        $extraData = [];
        $extraData['created_by'] = $user_id;
        $extraData['updated_by'] = $user_id;
        $extraData['created_at'] = $createdAt;
        $extraData['updated_at'] = $updatedAt;
        $extraDataMerge = array_fill(0,count($requestData),$extraData);
        $insertData = array_map(function($row,$extraRow){
            $row = array_merge($row,$extraRow);
            $rowData = $this->encryptData($row);
            return $rowData;
        },$requestData, $extraDataMerge);
        $fxlogsObj->insertUpdateLog($tableName,['requestData'=>$insertData,Config::get('constants.LOG_OPERATION_INDEX_NAME')=>Config::get('constants.LOG_TABLE_INSERT_BATCH_OPERATION_DATA_TYPE')]);
        return DB::table($tableName)->insert($insertData);
    }
}   