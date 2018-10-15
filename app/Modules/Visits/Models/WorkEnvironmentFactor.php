<?php
namespace App\Modules\Visits\Models;
use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\HasApiTokens;
use App\Traits\Encryptable;
use Illuminate\Support\Facades\DB;
use App\Libraries\SecurityLib;
use Config;
use App\Libraries\UtilityLib;

/**
 * WorkEnvironmentFactor
 *
 * @package                ILD
 * @subpackage             WorkEnvironmentFactor
 * @category               Model
 * @DateOfCreation         11 june 2018
 * @ShortDescription       This Model to handle database operation with current table
                           City
 **/
class WorkEnvironmentFactor extends Model {

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

        // Init exception library object
        $this->utilityLibObj = new UtilityLib();
    }

    /**
    *@ShortDescription Table for the Users.
    *
    * @var String
    */
    protected $table = 'work_environment_factor';
    
    // @var Array $encryptedFields
    // This protected member contains fields that need to encrypt while saving in database
    protected $encryptable = [];
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['pat_id','visit_id','wef_is_working_location_outside','wef_is_smoky_dust','wef_use_of_protective_masks','wef_occupation','wef_worked_from_month','wef_worked_from_year','wef_worked_to_month','wef_worked_to_year','wef_exposures','resource_type','ip_address'];

    /**
     *@ShortDescription Override the primary key.
     *
     * @var string
     */
    protected $primaryKey = 'wef_id';

    /**
     * @DateOfCreation        18 June 2018
     * @ShortDescription      This function is responsible to save record for the WorkEnvironment
     * @param                 array $requestData   
     * @return                integer WorkEnvironment id
     */
    public function addWorkEnvironment($inserData)
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
     * @DateOfCreation        11 June 2018
     * @ShortDescription      This function is responsible to update WorkEnvironment data
     * @param                 Array  $requestData
     * @return                Array of status
     */
    public function updateWorkEnvironment($updateData,$whereData)
    {
        
        if(isset($updateData['wef_id'])){
            unset($updateData['wef_id']);
        }


        $updateData = $this->utilityLibObj->fillterArrayKey($updateData, $this->fillable);
       
        // Prepair update query
        $response = $this->dbUpdate($this->table, $updateData,$whereData);
        
        if($response){
            return isset($whereData['wef_id']) ? $whereData['wef_id'] : 0;
        }
        return false;
    }

    /**
     * @DateOfCreation        18 June 2018
     * @ShortDescription      This function is responsible to get the WorkEnvironment data
     * @param                 array $requestData patId, $visitId  
     * @return                object Array of WorkEnvironment records
     */
    public function getWorkEnvironmentDataByPatientIdAndVistId($requestData) {

        $patId       = $this->securityLibObj->decrypt($requestData['patId']);
        $visitId     = $this->securityLibObj->decrypt($requestData['visitId']);

        $query = DB::table($this->table)
                            ->select($this->table.'.wef_id', 
                                    $this->table.'.pat_id', 
                                    $this->table.'.visit_id', 
                                    $this->table.'.wef_is_working_location_outside', 
                                    $this->table.'.wef_is_smoky_dust', 
                                    $this->table.'.wef_use_of_protective_masks',
                                    $this->table.'.wef_occupation', 
                                    $this->table.'.wef_worked_from_month', 
                                    $this->table.'.wef_worked_from_year',
                                    $this->table.'.wef_worked_to_month', 
                                    $this->table.'.wef_worked_to_year', 
                                    $this->table.'.wef_exposures'
                                )
                            ->where($this->table.'.is_deleted', Config::get('constants.IS_DELETED_NO'))
                            ->where($this->table.'.visit_id',$visitId)
                            ->where($this->table.'.pat_id', $patId);
        
        /* Condition for Filtering the result */
        if(!empty($requestData['filtered'])){
            foreach ($requestData['filtered'] as $key => $value) {
                $query = $query->where(function ($query) use ($value){
                                $query
                                ->where($this->table.'.wef_occupation', 'ilike', "%".$value['value']."%")
                                ->orWhere($this->table.'.wef_exposures', 'ilike', '%'.$value['value'].'%');
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

        $queryResult['result'] = 
            $query->offset($offset)
            ->limit($requestData['pageSize'])
            ->get()
            ->map(function($wefLists){
                $wefLists->wef_id = $this->securityLibObj->encrypt($wefLists->wef_id);
                $wefLists->pat_id = $this->securityLibObj->encrypt($wefLists->pat_id);
                $wefLists->visit_id = $this->securityLibObj->encrypt($wefLists->visit_id);
                $wefLists->wef_exposures = empty($wefLists->wef_exposures) ? '' : $wefLists->wef_exposures;
                return $wefLists;
            });
        
        return $queryResult;

    }

    /**
     * @DateOfCreation        11 June 2018
     * @ShortDescription      This function is responsible to Delete Work Environment data
     * @param                 integer $wefId   
     * @return                Array of status and message
     */
    public function doDeleteWorkEnvironment($wefId)
    {
        $queryResult = $this->dbUpdate( $this->table, 
                                        [ 'is_deleted' => Config::get('constants.IS_DELETED_YES') ], 
                                        ['wef_id' => $wefId]
                                    );

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
