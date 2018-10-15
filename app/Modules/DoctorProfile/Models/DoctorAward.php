<?php
namespace App\Modules\DoctorProfile\Models;
use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\HasApiTokens;
use App\Traits\Encryptable;
use Illuminate\Support\Facades\DB;
use App\Libraries\SecurityLib;
use Config;
use Carbon\Carbon;

/**
 * DoctorAwards
 *
 * @package                 ILD India Registry
 * @subpackage              DoctorAwards
 * @category                Model
 * @DateOfCreation          21 may 2018
 * @ShortDescription        This Model to handle database operation with current table
                            doctors awards
 **/
class DoctorAward extends Model {

    use HasApiTokens,Encryptable;

    /**
     * The attributes to declare primary key for the table.
     *
     * @var string
     */
    protected $primaryKey = 'doc_award_id';

    /**
     * The attributes to declare table name to store data.
     *
     * @var string
     */
    protected $table = 'doctors_awards';

    // @var Array $encryptedFields
    // This protected member contains fields that need to encrypt while saving in database
    protected $encryptable = [];
    
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct() {
        // Init security library object
        $this->securityLibObj = new SecurityLib();
    }

    /**
     * Create Doctor Awards List with regarding details
     *
     * @param array $data awards data
     * @return int doctor award id if inserted otherwise false
     */
    public function getAwardsList($requestData) { 
        // DB::enableQueryLog();
        $selectData  =  ['doc_award_id', 'doc_award_name', 'doc_award_year'];
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
                                ->orWhere('doc_award_name', 'ilike', "%".$value['value']."%")
                                ->orWhere(DB::raw('CAST(doc_award_year AS TEXT)'), 'ilike', '%'.$value['value'].'%');
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
        $doctorAward['pages'] = ceil($query->count()/$requestData['pageSize']);
        $doctorAward['result'] =  $query
                        ->offset($offset)
                        ->limit($requestData['pageSize'])
                        ->get()
                        ->map(function($doctorAwards){
                            $doctorAwards->doc_award_id = $this->securityLibObj->encrypt($doctorAwards->doc_award_id);
                            return $doctorAwards;
                        });
        return $doctorAward;
    }

     /**
    * @DateOfCreation        22 May 2018
    * @ShortDescription      This function is responsible to get the Awards by id
    * @param                 String $doc_award_id   
    * @return                Array of award
    */
    public function getAwardById($doc_award_id)
    {   $selectData = ['doc_award_id', 'doc_award_name', 'doc_award_year'];

        $whereData = array(
                        'doc_award_id' =>  $doc_award_id, 
                        'is_deleted' => Config::get('constants.IS_DELETED_NO')
                    );
        $queryResult = $this->dbSelect($this->table, $selectData, $whereData);

        return $queryResult;
    }

    /**
     * Create or Edit doctor award with regarding details
     *
     * @param array $data membership data
     * @return Array doctor member if inserted otherwise false
     */
    public function saveAward($requestData=array()) {
        if(array_key_exists('doc_award_id', $requestData) && !empty($requestData['doc_award_id'])) {
            $doc_award_id = $requestData['doc_award_id'];
            $whereData =  array('doc_award_id' => $doc_award_id);
            $queryResult =  $this->dbUpdate($this->table, $requestData, $whereData);
            
            if(!empty($queryResult)) {
                $awardUpdateData = $this->getAwardById($requestData['doc_award_id']);
                // Encrypt the ID
                $awardUpdateData->doc_award_id = $this->securityLibObj->encrypt($requestData['doc_award_id']);
                return $awardUpdateData;

            }
        }else{
            unset($requestData['doc_award_id']);
            $queryResult = $this->dbInsert($this->table, $requestData);

            if(!empty($queryResult)) {
                $awardUpdateData = $this->getAwardById(DB::getPdo()->lastInsertId());

                // Encrypt the ID
                $awardUpdateData->doc_award_id = $this->securityLibObj->encrypt(DB::getPdo()->lastInsertId());
                return $awardUpdateData;
            }
        }
        return false;
    }

    /**
     * delete doctor award with regarding id
     *
     * @param int $id award id
     * @return boolean perticular doctor award detail delete or not
     */
    public function deleteAward($doc_award_id) {
        $updateData = array(
                            'is_deleted' => Config::get('constants.IS_DELETED_YES')
                            );
        $whereData = array('doc_award_id' => $doc_award_id );
        
        $queryResult =  $this->dbUpdate($this->table, $updateData, $whereData);

        if(!empty($queryResult)) {
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
