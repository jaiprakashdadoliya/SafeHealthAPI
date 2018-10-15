<?php

namespace App\Modules\DoctorProfile\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\HasApiTokens;
use App\Traits\Encryptable;
use Illuminate\Support\Facades\DB;
use App\Libraries\SecurityLib;
use Config;
use App\Libraries\ExceptionLib;
use App\Libraries\DateTimeLib;

/**
 * DoctorSpecialisations
 *
 * @package                ILD India Registry
 * @subpackage             DoctorSpecialisations
 * @category               Model
 * @DateOfCreation         30 may 2018
 * @ShortDescription       This Model to handle database operation with current table
                           doctors specialisations
 **/
class DoctorSpecialisations extends Model {

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
        $this->exceptionLibObj = new ExceptionLib();
        $this->dateTimeObj = new DateTimeLib();
    }

    /**
    *@ShortDescription Table for the Users.
    *
    * @var String
    */
    protected $table = 'doctors_specialisations';
    protected $tableJoin = 'specialisations';
    protected $tableSpecialisation = 'specialisations_tags';

    // @var Array $encryptedFields
    // This protected member contains fields that need to encrypt while saving in database
    protected $encryptable = [];
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'doc_spl_id', 'user_id', 'spl_id'
    ];
    /**
     *@ShortDescription Override the primary key.
     *
     * @var string
     */
    protected $primaryKey = 'doc_spl_id';

    /**
    * @DateOfCreation        30 May 2018
    * @ShortDescription      This function is responsible to get the specialisations option list
    * @return                Array of status and message
    */
    public function getSpecialisationsOptionList()
    {
        $queryResult = DB::table($this->tableJoin)
            ->select($this->tableJoin.'.spl_name', $this->tableJoin.'.spl_id')
            ->where($this->tableJoin.'.is_deleted',Config::get('constants.IS_DELETED_NO'))
            ->get();

        $result =   $this->decryptMultipleData($queryResult) 
                    ->map(function ($doctorSpecialisation) {
                        $doctorSpecialisation->spl_id = $this->securityLibObj->encrypt($doctorSpecialisation->spl_id);
                        return $doctorSpecialisation;
                    });
            return $result;
    }

    /**
    * @DateOfCreation        30 May 2018
    * @ShortDescription      This function is responsible to get the specialisations option list
    * @return                Array of status and message
    */
    public function getSpecialisationsTagList($requestData)
    {

        $spl_id = (int)($this->securityLibObj->decrypt($requestData['spl_id']));
        $queryResult = DB::table($this->tableSpecialisation)
            ->select($this->tableSpecialisation.'.specailisation_tag as text', $this->tableSpecialisation.'.spl_tag_id as id')
            ->where($this->tableSpecialisation.'.is_deleted',Config::get('constants.IS_DELETED_NO'))
            ->where($this->tableSpecialisation.'.spl_id',$spl_id)
            ->get();
        $result =   $this->decryptMultipleData($queryResult) 
                    ->map(function ($specialisation_tags) {
                        $specialisation_tags->id = $this->securityLibObj->encrypt($specialisation_tags->id);
                        return $specialisation_tags;
                    });
        return $result;
    }

    
    /**
    * @DateOfCreation        30 May 2018
    * @ShortDescription      This function is responsible to get the doctor specialisations list
    * @param                 String $user_id   
    * @return                Array of status and message
    */
    public function getSpecialisationsList($requestData)
    {
        $user_id = $requestData['user_id'];
        $onConditionLeftSide = $this->table.'.spl_id';
        $onConditionRightSide = $this->tableJoin.'.spl_id';
        $query = DB::table($this->table)
                       ->select($this->table.'.doc_spl_id', $this->table.'.user_id', $this->table.'.spl_id',$this->tableJoin.'.spl_name')
                       ->join($this->tableJoin,$onConditionLeftSide, '=', $onConditionRightSide)
                       ->where($this->table.'.user_id', $user_id)
                       ->where($this->table.'.is_deleted',Config::get('constants.IS_DELETED_NO'));
        /* Condition for Filtering the result */
        if(!empty($requestData['filtered'])){
            foreach ($requestData['filtered'] as $key => $value) {
                 $query = $query->where(function ($query) use ($value){
                                $query
                                ->where($this->tableJoin.'.spl_name', 'ilike', '%'.$value['value'].'%');
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
        $result['pages'] = ceil($query->count()/$requestData['pageSize']);
        $queryResult['result'] =  $query
                        ->offset($offset)
                        ->limit($requestData['pageSize'])
                        ->get();
        $result['result'] =   $this->decryptMultipleData($queryResult['result'])
                        ->map(function ($doctorSpecialisation) use($user_id) {
                            $doctorSpecialisation->specialisation_tags =  $this->getSpecialisationTagById($doctorSpecialisation->doc_spl_id, $user_id);
                            $doctorSpecialisation->doc_spl_id = $this->securityLibObj->encrypt($doctorSpecialisation->doc_spl_id);
                            $doctorSpecialisation->spl_id = $this->securityLibObj->encrypt($doctorSpecialisation->spl_id);
                            return $doctorSpecialisation;
                        });
        return $result;
    }


    protected function getSpecialisationTagById($doc_spl_id, $user_id){
        return DB::table('doctor_specialisations_tags')
            ->select('specailisation_tag as text', 'doc_spl_tag_id as id')
            ->where('is_deleted',Config::get('constants.IS_DELETED_NO'))
            ->where('user_id',$user_id)
            ->where('doc_spl_id',$doc_spl_id)
            ->get()
            ->map(function ($doctor_specialisations_tags) {
                $doctor_specialisations_tags->id = $this->securityLibObj->encrypt($doctor_specialisations_tags->id);
                return $doctor_specialisations_tags;
            });
    }

    /**
    * @DateOfCreation        30 May 2018
    * @ShortDescription      This function is responsible to insert Specialisations data
    * @param                 Array $requestData   
    * @return                Array of status and message
    */
    public function doInsertSpecialisations($requestData)
    {   
        $doctors_specialisations_tags = $requestData['specialisation_tags'];
        unset($requestData['specialisation_tags']);
        try{
         DB::beginTransaction();
         $queryResult = $this->dbInsert($this->table, $requestData);
            if($queryResult){
                $doc_spl_id = DB::getPdo()->lastInsertId();
                $insertData = [];
                if(is_array($doctors_specialisations_tags)){
                    
                    foreach ($doctors_specialisations_tags as $tags_data) {
                        $insertData[] = [
                                            'doc_spl_id' => $doc_spl_id,
                                            'specailisation_tag' => $tags_data['text'],
                                            'user_id'=>$requestData['user_id'],
                                            'created_by'=>$requestData['user_id'],
                                            'updated_by'=>$requestData['user_id'],
                                            'created_at'=>$this->dateTimeObj->getPostgresTimestampAfterXmin(0),
                                            'updated_at'=>$this->dateTimeObj->getPostgresTimestampAfterXmin(0)
                                        ];
                    }
                }
                $tagsResult = DB::table("doctor_specialisations_tags")->insert($insertData);
                if($tagsResult){
                    DB::commit();
                    $specialisationUpdateData = $this->getSpecialisationById($doc_spl_id);
                    // Encrypt the ID
                    $specialisationUpdateData->doc_spl_id = $this->securityLibObj->encrypt($doc_spl_id);
                    $specialisationUpdateData->spl_id = $this->securityLibObj->encrypt($specialisationUpdateData->spl_id);
                    return $specialisationUpdateData;
                }
            }
        }
        catch (\Exception $ex) {
            DB::rollback();
            return false;           
        }    
        return false;
    }

     /**
    * @DateOfCreation        30 May 2018
    * @ShortDescription      This function is responsible to get the Specialisations by id
    * @param                 String $doc_spl_id   
    * @return                Array of Specialisations
    */
    public function getSpecialisationById($doc_spl_id)
    {
        $onConditionLeftSide = $this->table.'.spl_id';
        $onConditionRightSide = $this->tableJoin.'.spl_id';
        $result = DB::table($this->table)
        ->join($this->tableJoin,$onConditionLeftSide, '=', $onConditionRightSide)
            ->select($this->table.'.doc_spl_id', $this->table.'.user_id', $this->table.'.spl_id',$this->tableJoin.'.spl_name')
            ->where('doc_spl_id', $doc_spl_id)
            ->first();
        $result->specialisation_tags =  $this->getSpecialisationTagById($doc_spl_id, $result->user_id);
        return $this->decryptSingleData($result);
    }

    /**
    * @DateOfCreation        30 May 2018
    * @ShortDescription      This function is responsible to update Specialisations data
    * @param                 String $doc_spl_id
                             Array  $requestData   
    * @return                Array of status and message
    */
    public function doUpdateSpecialisations($requestData)
    {   
        $doctors_specialisations_tags = $requestData['specialisation_tags'];
        unset($requestData['specialisation_tags']);
        $doc_spl_id = $this->securityLibObj->decrypt($requestData['doc_spl_id']);
        unset($requestData['doc_spl_id']);
        $whereData =  array('doc_spl_id' => $doc_spl_id);
        $queryResult =  $this->dbUpdate($this->table, $requestData, $whereData);
        if($queryResult){
           $insertData = [];
            $this->deleteDoctorsTag($doc_spl_id,$requestData['user_id']);
            foreach ($doctors_specialisations_tags as $tags_data) {
                $insertData[] = [
                                    'doc_spl_id' => $doc_spl_id,
                                    'specailisation_tag' => $tags_data['text'],
                                    'user_id'=>$requestData['user_id'],
                                    'created_by'=>$requestData['user_id'],
                                    'updated_by'=>$requestData['user_id'],
                                    'created_at'=>$this->dateTimeObj->getPostgresTimestampAfterXmin(0),
                                    'updated_at'=>$this->dateTimeObj->getPostgresTimestampAfterXmin(0)
                                ];
            }
            $tagsResult = DB::table("doctor_specialisations_tags")->insert($insertData);
            $specialisationUpdateData = $this->getSpecialisationById($doc_spl_id);
            $specialisationUpdateData->doc_spl_id = $this->securityLibObj->encrypt($doc_spl_id);
            $specialisationUpdateData->spl_id = $this->securityLibObj->encrypt($specialisationUpdateData->spl_id);
            return $specialisationUpdateData;
        }
        return false;
    }

     /**
    * @DateOfCreation        16 August 2018
    * @ShortDescription      This function is responsible to Tags data
    * @param                 Integer $doc_spl_id
                             Integer $user_id   
    * @return                Response
    */
    protected function deleteDoctorsTag($doc_spl_id, $user_id){
        return DB::table('doctor_specialisations_tags')
        ->where('doc_spl_id', $doc_spl_id)
        ->where('user_id', $user_id)
        ->delete();
    }

    /**
    * @DateOfCreation        30 May 2018
    * @ShortDescription      This function is responsible to Delete Specialisations data
    * @param                 Array $doc_spl_id   
    * @return                Array of status and message
    */
    public function doDeleteSpecialisations($doc_spl_id)
    {   
        $updateData = array(
                        'is_deleted' => Config::get('constants.IS_DELETED_YES')
                        );
        $whereData = array( 'doc_spl_id' => $doc_spl_id );
        
        $queryResult =  $this->dbUpdate($this->table, $updateData, $whereData);
        if($queryResult){
            return true;
        }
        return false;
    }

    /**
    * @DateOfCreation        30 May 2018
    * @ShortDescription      This function is responsible to check the Specialisations exist in the system or not
    * @param                 Array $doc_spl_id   
    * @return                Array of status and message
    */
    public function isSpecialisationsExist($doc_spl_id){
        $SpecialisationsExist = DB::table($this->table)
                        ->where('doc_spl_id', $doc_spl_id)
                        ->exists();
        return $SpecialisationsExist;
    }

    /**
    * @DateOfCreation        01 Jun 2018
    * @ShortDescription      This function is responsible to check the Specialisations exist for user or not
    * @param                 Array $doc_spl_id   
    * @return                Array of status and message
    */
    public function checkSpecialisationsOptionExistsToUser($splId, $userId, $docSplId = 0) {

        $SpecialisationsExist = DB::table($this->table)
                                ->where('spl_id', $splId)
                                ->where('user_id', $userId)
                                ->where('is_deleted', Config::get('constants.IS_DELETED_NO'));
        if(!empty($docSplId)){
            $SpecialisationsExist = $SpecialisationsExist->where('doc_spl_id','!=',$docSplId);
        }              
        $SpecialisationsExist =  $SpecialisationsExist->exists();
        return $SpecialisationsExist;

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
