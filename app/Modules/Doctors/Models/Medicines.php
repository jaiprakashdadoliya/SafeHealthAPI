<?php
namespace App\Modules\Doctors\Models;
use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\HasApiTokens;
use App\Traits\Encryptable;
use App\Libraries\SecurityLib;
use App\Libraries\UtilityLib;
use Config;
use DB;

/**
 * Medicines
 *
 * @package                ILD India Registry
 * @subpackage             Medicines
 * @category               Model
 * @DateOfCreation         11 june 2018
 * @ShortDescription       This Model to handle database operation of Medicines
 **/

class Medicines extends Model {

    use HasApiTokens,Encryptable;

    /**
     * Create a new model instance.
     *
     * @return void
     */
    public function __construct()
    {
        // Init security library object
        $this->securityLibObj = new SecurityLib();

        // Init UtilityLib library object
        $this->utilityLibObj = new UtilityLib();
    }
    /**
    *@ShortDescription Table for the Users.
    *
    * @var String
    */
    protected $table         = 'medicines';
    
    // This protected member contains fields that need to encrypt while saving in database
    protected $encryptable = [];
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['medicine_name', 'medicine_id', 'drug_type_id', 'drug_dose_unit_id', 'medicine_dose','show_in', 'ip_address', 'resource_type' ];

    /**
     *@ShortDescription Override the primary key.
     *
     * @var string
     */
    protected $primaryKey = 'medicine_id';

    /**
     * @DateOfCreation        26 June 2018
     * @ShortDescription      This function is responsible to get the all Medicines record
     * @return                object Array of HRCT records
     */
    public function getAllMedicines($param=[],$encrypt=true) 
    {   
        $name = isset($param['medicine_name']) ? $param['medicine_name'] :'';   
        $drugType = isset($param['drug_type_id']) ? $param['drug_type_id'] :'';    
        $drugDoseUnit = isset($param['drug_dose_unit_id']) ? $param['drug_dose_unit_id'] :'';    
        $medicineDose = isset($param['medicine_dose']) ? $param['medicine_dose'] :'';     
        
        $selectData = ['medicine_id','medicine_name'];
        $whereData = ['is_deleted'=>Config::get('constants.IS_DELETED_NO')];
        $queryResult = DB::table($this->table)
                        ->select($selectData)
                        ->where($whereData);     
        if($name!=''){
            $queryResult = $queryResult->where('medicine_name','ilike',$name);
        }
        if($drugType!=''){
            $queryResult = $queryResult->where('drug_type_id','=',(int) $drugType);
        }
        if($drugDoseUnit!=''){
            $queryResult = $queryResult->where('drug_dose_unit_id','=',(int) $drugDoseUnit);
        }
        if($medicineDose!=''){
            $queryResult = $queryResult->where('medicine_dose','ilike',$medicineDose);
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

    /**
     * @DateOfCreation        26 June 2018
     * @ShortDescription      This function is responsible to get the all Medicines record
     * @return                object Array of HRCT records
     */
    public function getAllUniqueMedicinesName($param=[],$encrypt=true) 
    {   
        $name = isset($param['medicine_name']) ? $param['medicine_name'] :'';    
        $drugType = isset($param['drug_type_id']) ? $param['drug_type_id'] :'';    
        $drugDoseUnit = isset($param['drug_dose_unit_id']) ? $param['drug_dose_unit_id'] :'';    
        $medicineDose = isset($param['medicine_dose']) ? $param['medicine_dose'] :'';    
        
        $selectData = "DISTINCT ON (medicine_name) medicine_name, medicine_id";
        $whereData = ['is_deleted'=>Config::get('constants.IS_DELETED_NO')];
        $queryResult = DB::table($this->table)
                        ->select(DB::raw($selectData))
                        ->where($whereData);     
        if($name!=''){
            $queryResult = $queryResult->where('medicine_name','ilike',$name);
        }
        if($drugType!=''){
            $queryResult = $queryResult->where('drug_type_id','=',$drugType);
        }
        if($drugDoseUnit!=''){
            $queryResult = $queryResult->where('drug_dose_unit_id','=',$drugDoseUnit);
        }
        if($medicineDose!=''){
            $queryResult = $queryResult->where('medicine_dose','ilike',$medicineDose);
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

    /**
     * @DateOfCreation        18 June 2018
     * @ShortDescription      This function is responsible to save record for the Medicines
     * @param                 array $requestData   
     * @return                integer auto increment id
     */
    public function saveMedicines($inserData)
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
    * @ShortDescription      This function is responsible to update Medicines Record
    * @param                 Array  $requestData   
    * @return                Array of status and message
    */
    public function updateMedicines($requestData,$whereData)
    {
        $updateData = $this->utilityLibObj->fillterArrayKey($requestData, $this->fillable);
        $response = $this->dbUpdate($this->table, $updateData, $whereData);
        if($response){
            return true;
        }
        return false;
    }
}