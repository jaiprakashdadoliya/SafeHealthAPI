<?php
namespace App\Modules\DoctorProfile\Models;
use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\HasApiTokens;
use App\Traits\Encryptable;
use Illuminate\Support\Facades\DB;
use App\Libraries\SecurityLib;
use Config;

/**
 * DoctorExperience
 *
 * @package                ILD India Registry
 * @subpackage             DoctorDegree
 * @category               Model
 * @DateOfCreation         21 may 2018
 * @ShortDescription       This Model to handle database operation with current table
                           doctors degree
 **/
class DoctorDegree extends Model {

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
    }

    /**
    *@ShortDescription Table for the Users.
    *
    * @var String
    */
    protected $table = 'doctors_degrees';
    
    // @var Array $encryptedFields
    // This protected member contains fields that need to encrypt while saving in database
    protected $encryptable = [];
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'doc_deg_name', 'doc_deg_passing_year', 'doc_deg_institute', 'resource_type', 'is_deleted'
    ];
    /**
     *@ShortDescription Override the primary key.
     *
     * @var string
     */
    protected $primaryKey = 'doc_deg_id';

    /**
    * @DateOfCreation        30 May 2018
    * @ShortDescription      This function is responsible to get the degree list
    * @param                 String $user_id   
    * @return                Array of degree records
    */
    public function getDegreeList($requestData, $method='POST')
    {
        if($method == Config::get('constants.REQUEST_TYPE_GET')){
            $whereData   = array('user_id' => $requestData['user_id'], 'is_deleted' => Config::get('constants.IS_DELETED_NO'));
            $selectData  = ['doc_deg_id', 'user_id', 'doc_deg_name', 'doc_deg_passing_year', 'doc_deg_institute'];
            $queryResult = $this->dbBatchSelect($this->table, $selectData, $whereData)
                            ->map(function ($doctorDegree) {
                                $doctorDegree->doc_deg_id = $this->securityLibObj->encrypt($doctorDegree->doc_deg_id);
                                return $doctorDegree;
                            });
            return $queryResult;
        }

        $selectData  =  ['doc_deg_id', 'user_id', 'doc_deg_name','doc_deg_passing_year', 
                            'doc_deg_institute', 'resource_type'];
        $whereData   =  array(
                            'user_id' => $requestData['user_id'],
                            'is_deleted' => Config::get('constants.IS_DELETED_NO')
                        ); 
        $query =  DB::table($this->table)
                    ->select($selectData)
                    ->where($whereData);
        /* Condition for Filtering the result */
        if(!empty($requestData['filtered'])){
            foreach ($requestData['filtered'] as $key => $value) {
                $query = $query->where(function ($query) use ($value){
                                $query
                                ->where('doc_deg_name', 'ilike', "%".$value['value']."%")
                                ->orWhere('doc_deg_institute', 'ilike', '%'.$value['value'].'%')
                                ->orWhere(DB::raw('CAST(doc_deg_passing_year AS TEXT)'), 'ilike', '%'.$value['value'].'%');
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
                       ->map(function ($doctorDegree) {
                            $doctorDegree->doc_deg_id = $this->securityLibObj->encrypt($doctorDegree->doc_deg_id);
                            $doctorDegree->user_id = $this->securityLibObj->encrypt($doctorDegree->user_id);
                            return $doctorDegree;
                        });
        return $queryResult;
    }

     /**
    * @DateOfCreation        30 May 2018
    * @ShortDescription      This function is responsible to get the degree record by id
    * @param                 String $doc_deg_id   
    * @return                Array of degree data
    */
    public function getDegreeById($doc_deg_id)
    {   
        $selectData = ['doc_deg_id', 'user_id', 'doc_deg_name','doc_deg_passing_year', 'doc_deg_institute', 'resource_type'];

        $whereData = array(
                        'doc_deg_id' => $doc_deg_id, 
                        'is_deleted' => Config::get('constants.IS_DELETED_NO')
                    );
        return $this->dbSelect($this->table, $selectData, $whereData);
    }

    /**
    * @DateOfCreation        30 May 2018
    * @ShortDescription      This function is responsible to update degree data
    * @param                 String $doc_deg_id
                             Array  $requestData   
    * @return                Array of status and message
    */
    public function doUpdateDegree($requestData)
    {
        $requestData = $this->encryptData($requestData);
        $doc_deg_id = $this->securityLibObj->decrypt($requestData['doc_deg_id']);
        unset($requestData['doc_deg_id']);

        $whereData =  array('doc_deg_id' => $doc_deg_id);
        $queryResult =  $this->dbUpdate($this->table, $requestData, $whereData);
     
        if($queryResult){
            $degreeUpdateData = $this->getDegreeById($doc_deg_id);
            $degreeUpdateData->doc_deg_id = $this->securityLibObj->encrypt($doc_deg_id);
            return $degreeUpdateData;
        }
        return false;
    }

    /**
    * @DateOfCreation        30 May 2018
    * @ShortDescription      This function is responsible to insert degree data
    * @param                 Array $requestData   
    * @return                Array of status and message
    */
    public function doInsertDegree($requestData)
    {
        $queryResult = $this->dbInsert($this->table, $requestData);

        if($queryResult){
            $degreeUpdateData = $this->getDegreeById(DB::getPdo()->lastInsertId());
            
            // Encrypt the ID
            $degreeUpdateData->doc_deg_id = $this->securityLibObj->encrypt(DB::getPdo()->lastInsertId());
            return $degreeUpdateData;
        }
        return false;
    }

    /**
    * @DateOfCreation        30 May 2018
    * @ShortDescription      This function is responsible to Delete degree data
    * @param                 Array $doc_deg_id   
    * @return                Array of status and message
    */
    public function doDeleteDegree($doc_deg_id)
    {   
        $updateData = array(
                        'is_deleted' => Config::get('constants.IS_DELETED_YES')
                        );
        $whereData = array( 'doc_deg_id' => $doc_deg_id );
        
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
