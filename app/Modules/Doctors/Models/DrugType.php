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
 * DrugType
 *
 * @package                ILD India Registry
 * @subpackage             DrugType
 * @category               Model
 * @DateOfCreation         11 june 2018
 * @ShortDescription       This Model to handle database operation of DrugType
 **/

class DrugType extends Model {

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
    protected $table         = 'drug_type';
    
    // This protected member contains fields that need to encrypt while saving in database
    protected $encryptable = [];
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [ 'drug_type_id',
                            'drug_type_name',
                            'resource_type',
                            'ip_address'
                        ];

    /**
     *@ShortDescription Override the primary key.
     *
     * @var string
     */
    protected $primaryKey = 'drug_type_id';

    /**
     * @DateOfCreation        26 June 2018
     * @ShortDescription      This function is responsible to get the all DrugType record
     * @return                object Array of HRCT records
     */
    public function getAllDrugType($param=[],$encrypt=true) 
    {   
        $name = isset($param['drug_type_name']) ? $param['drug_type_name'] :'';    
        $selectData = ['drug_type_id','drug_type_name'];
        $whereData = ['is_deleted'=>Config::get('constants.IS_DELETED_NO')];
        $queryResult = DB::table($this->table)
                        ->select($selectData)
                        ->where($whereData);     
        if($name!=''){
            $queryResult = $queryResult->where('drug_type_name','ilike',$name);
        }
        $queryResult = $queryResult->get();
        if(count($queryResult)>0 && $encrypt){
            $queryResult = $queryResult->map(function($dataList){ 
                $dataList->drug_type_id = $this->securityLibObj->encrypt($dataList->drug_type_id);
                return $dataList;
            });
        }
        return $queryResult;
    }

    /**
     * @DateOfCreation        18 June 2018
     * @ShortDescription      This function is responsible to save record for the DrugType
     * @param                 array $requestData   
     * @return                integer auto increment id
     */
    public function saveDrugType($inserData)
    {
        // @var Boolean $response
        // This variable contains insert query response
        $response = false;

        // @var Array $inserData
        // This Array contains insert data for Patient
        $inserData = $this->utilityLibObj->fillterArrayKey($inserData, $this->fillable);
                
        // Prepare insert query
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
    * @ShortDescription      This function is responsible to update DrugType Record
    * @param                 Array  $requestData   
    * @return                Array of status and message
    */
    public function updateDrugType($requestData,$whereData)
    {
        $updateData = $this->utilityLibObj->fillterArrayKey($requestData, $this->fillable);
        $response = $this->dbUpdate($this->table, $updateData, $whereData);
        if($response){
            return true;
        }
        return false;
    }
}