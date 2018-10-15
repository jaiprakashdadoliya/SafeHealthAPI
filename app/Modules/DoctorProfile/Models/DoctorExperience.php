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
 * @subpackage             DoctorExperience
 * @category               Model
 * @DateOfCreation         21 may 2018
 * @ShortDescription       This Model to handle database operation with current table
                           doctors experience
 **/
class DoctorExperience extends Model {

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
    protected $table = 'doctors_experience';
    
    // @var Array $encryptedFields
    // This protected member contains fields that need to encrypt while saving in database
    protected $encryptable = [];
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'doc_id', 'doc_exp_organisation_name', 'doc_exp_designation', 'doc_sexp_tart_year', 'doc_exp_end_year', 'doc_exp_organisation_type'];
    /**
     *@ShortDescription Override the primary key.
     *
     * @var string
     */
    protected $primaryKey = 'doc_exp_id';

    /**
    * @DateOfCreation        22 May 2018
    * @ShortDescription      This function is responsible to get the experience list
    * @param                 String $user_id   
    * @return                Array of status and message
    */
    public function getExperienceList($requestData, $method='POST')
    {
        if($method == Config::get('constants.REQUEST_TYPE_GET')){
            $whereData  = ['user_id'=> $requestData['user_id'],'is_deleted'=>  Config::get('constants.IS_DELETED_NO')];
            $selectData = ['doc_exp_id', 'doc_exp_organisation_name', 'doc_exp_designation', 'doc_exp_start_year', 'doc_exp_end_year', 'doc_exp_start_month', 'doc_exp_end_month', 'doc_exp_organisation_type'];
            $queryResult = $this->dbBatchSelect($this->table, $selectData, $whereData)
                                ->map(function ($doctorExperience) {
                                    $doctorExperience->doc_exp_id = $this->securityLibObj->encrypt($doctorExperience->doc_exp_id);
                                    return $doctorExperience;
                                });
            return $queryResult;
        }

        // GRID LISTING QUERY
        $selectData  =  ['doc_exp_id', 'doc_exp_organisation_name', 'doc_exp_designation', 
                            'doc_exp_start_year', 'doc_exp_end_year', 'doc_exp_start_month', 'doc_exp_end_month', 'doc_exp_organisation_type'];
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
                                ->where('doc_exp_organisation_name', 'like', "%".$value['value']."%")
                                ->orWhere('doc_exp_designation', 'like', "%".$value['value']."%")
                                ->orWhere(DB::raw('CAST(doc_exp_start_year AS TEXT)'), 'like', '%'.$value['value'].'%')
                                ->orWhere(DB::raw('CAST(doc_exp_start_month AS TEXT)'), 'like', '%'.$value['value'].'%')
                                ->orWhere(DB::raw('CAST(doc_exp_end_year AS TEXT)'), 'like', '%'.$value['value'].'%')
                                ->orWhere(DB::raw('CAST(doc_exp_end_month AS TEXT)'), 'like', '%'.$value['value'].'%');
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
                        ->map(function ($doctorExperience) {
                            $doctorExperience->doc_exp_id = $this->securityLibObj->encrypt($doctorExperience->doc_exp_id);
                            return $doctorExperience;
                        });
            return $queryResult;
    }

     /**
    * @DateOfCreation        22 May 2018
    * @ShortDescription      This function is responsible to get the experience by id
    * @param                 String $doc_exp_id   
    * @return                Array of experience
    */
    public function getExperienceById($doc_exp_id)
    {   $selectData = ['doc_exp_organisation_name', 'doc_exp_designation', 
            'doc_exp_start_year', 'doc_exp_end_year', 'doc_exp_start_month', 'doc_exp_end_month', 'doc_exp_organisation_type'];

        $whereData = array(
                        'doc_exp_id' =>  $doc_exp_id, 
                        'is_deleted' => Config::get('constants.IS_DELETED_NO')
                    );
        $queryResult = $this->dbSelect($this->table, $selectData, $whereData);
        return $queryResult;
    }

    /**
    * @DateOfCreation        22 May 2018
    * @ShortDescription      This function is responsible to update experience data
    * @param                 String $doc_exp_id
                             Array  $requestData   
    * @return                Array of status and message
    */
    public function doUpdateExperience($requestData)
    {
        $doc_exp_id = $this->securityLibObj->decrypt($requestData['doc_exp_id']);
        unset($requestData['doc_exp_id']);
        
        $whereData =  array('doc_exp_id' => $doc_exp_id);
        $queryResult =  $this->dbUpdate($this->table, $requestData, $whereData);

        if($queryResult){
            $experienceUpdateData = $this->getExperienceById($doc_exp_id);
            $experienceUpdateData->doc_exp_id = $this->securityLibObj->encrypt($doc_exp_id);
            return $experienceUpdateData;
        }
        return false;
    }

    /**
    * @DateOfCreation        24 May 2018
    * @ShortDescription      This function is responsible to insert experience data
    * @param                 Array $requestData   
    * @return                Array of status and message
    */
    public function doInsertExperience($requestData)
    {
        $queryResult = $this->dbInsert($this->table, $requestData);

        if($queryResult){
            $experienceUpdateData = $this->getExperienceById(DB::getPdo()->lastInsertId());
            
            // Encrypt the ID
            $experienceUpdateData->doc_exp_id = $this->securityLibObj->encrypt(DB::getPdo()->lastInsertId());
            return $experienceUpdateData;
        }
        return false;
    }

    /**
    * @DateOfCreation        24 May 2018
    * @ShortDescription      This function is responsible to Delete experience data
    * @param                 Array $doc_exp_id   
    * @return                Array of status and message
    */
    public function doDeleteExperience($doc_exp_id)
    {
        $updateData = array(
                        'is_deleted' => Config::get('constants.IS_DELETED_YES')
                        );
        $whereData = array( 'doc_exp_id' => $doc_exp_id );
        
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
