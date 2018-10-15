<?php
namespace App\Modules\ManageStaff\Models;
use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\HasApiTokens;
use App\Traits\Encryptable;
use Illuminate\Support\Facades\DB;
use App\Libraries\SecurityLib;
use Config;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use App\Modules\Setup\Models\StaticDataConfig as StaticData;

/**
 * ManageStaff
 *
 * @package                 Safehealth
 * @subpackage              ManageStaff
 * @category                Model
 * @DateOfCreation          08 June 2018
 * @ShortDescription        This Model to handle database operation with current table
                            doctors staff
 **/
class ManageStaff extends Model {

    use HasApiTokens,Encryptable;

    /**
     * The attributes to declare primary key for the table.
     *
     * @var string
     */
    protected $primaryKey = 'doc_staff_id';

    /**
     * The attributes to declare table name to store data.
     *
     * @var string
     */
    protected $table = 'doctors_staff';

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

        // Init General staticData Model Object
        $this->staticDataObj = new StaticData();
    }    

 
    /**
     * Create Doctor Staff List with regarding details
     *
     * @param array $data staff data
     * @return int doctor staff id if inserted otherwise false
     */
    public function getStaffList($requestData) { 

        //DB::enableQueryLog();
        $query = $this->staffListQuery($requestData['doc_user_id']);

        if(!empty($requestData['filtered'])){
            foreach ($requestData['filtered'] as $key => $value) {
                
                $whereGender = $value['value'];
                if(stripos($value['value'], 'male') !== false)
                {
                    $whereGender = 1;
                } else if( stripos($value['value'], 'female' !== false) ) {
                    $whereGender = 2;                    
                } else if( stripos($value['value'], 'other') !== false ){
                    $whereGender = 3;                    
                }

                if(!empty($value['value'])){
                    $query = $query->where(function ($query) use ($value, $whereGender){
                                    $query
                                    ->where('user_firstname', 'ilike', "%".$value['value']."%")
                                    ->orWhere('user_lastname', 'ilike', "%".$value['value']."%")
                                    ->orWhere(DB::raw('CAST(user_mobile AS TEXT)'), 'like', '%'.$value['value'].'%')
                                    ->orWhere(DB::raw('CAST(user_gender AS TEXT)'), 'like', '%'.$whereGender.'%');
                                });
                }
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
        $staffData['pages'] = ceil($query->count()/$requestData['pageSize']);
        $staffData['result'] = $query
                    ->offset($offset)
                    ->limit($requestData['pageSize'])
                    ->get()
                    ->map(function ($staffList) {
                        $staffList->doc_staff_id= $this->securityLibObj->encrypt($staffList->doc_staff_id);
                        $staffList->user_id     = $this->securityLibObj->encrypt($staffList->user_id);
                        $staffList->doc_user_id = $this->securityLibObj->encrypt($staffList->doc_user_id);
                        $staffList->user_gender_id = $staffList->user_gender;
                        $staffList->user_type_id   = $staffList->user_type;
                        $staffList->user_type   = $this->staticDataObj->getStaffRoleById($staffList->user_type);
                        $staffList->user_gender = $this->staticDataObj->getGenderNameById($staffList->user_gender);
                        return $staffList;
                    });
        return $staffData;
    }

    /**
    * @DateOfCreation        08 June 2018
    * @ShortDescription      This function is responsible to get the staff record by id
    * @param                 String $doc_staff_id   
    * @return                Array of staff data
    */
    public function getStaffById($doc_staff_id)
    {
        $joinTable = 'users';
        $queryResult = DB::table($this->table)
            ->select('users.user_firstname','users.user_lastname','users.user_id','doctors_staff.doc_staff_id','doctors_staff.doc_user_id', 'users.user_gender', 'users.user_mobile', 'users.user_email', 'users.user_type', 'users.user_adhaar_number')
            ->leftJoin('users', 'users.user_id', '=', 'doctors_staff.user_id')
            ->where('doc_staff_id', $doc_staff_id)
            ->first();
        $queryResult->user_gender_id = $queryResult->user_gender;
        $queryResult->user_type_id   = $queryResult->user_type;
        $queryResult->user_type      = $this->staticDataObj->getStaffRoleById($queryResult->user_type);
        $queryResult->user_gender    = $this->staticDataObj->getGenderNameById($queryResult->user_gender);
        return $this->decryptSingleData($queryResult);
    }

    /**
     * @DateOfCreation        08 June 2018
     * Create or Edit doctor staff with regarding details
     * @param array $data membership data
     * @return Array doctor member if inserted otherwise false
     */
    public function saveStaff($requestData=array()) {
        $requestData['updated_at']    = Carbon::now();
        $requestData['updated_by']    = $requestData['doc_user_id'];
        
        if(array_key_exists('doc_staff_id', $requestData) && !empty($requestData['doc_staff_id'])) {
            $requestData = $this->encryptData($requestData);
            $isUpdated = DB::table($this->table)
                        ->where('doc_staff_id', $requestData['doc_staff_id'])
                        ->update($requestData);
            if(!empty($isUpdated)) {
                $staffData = $this->getStaffById($requestData['doc_staff_id']);
                $staffData->doc_staff_id = $this->securityLibObj->encrypt($requestData['doc_staff_id']);
                $staffData->user_id = $this->securityLibObj->encrypt($staffData->user_id);
                return $staffData;
            }
        }else{
            unset($requestData['doc_staff_id']);
            $requestData['created_by'] = $requestData['doc_user_id'];
            $requestData['created_at'] = Carbon::now();
            $requestData = $this->encryptData($requestData);
            $isInserted = DB::table($this->table)->insert($requestData); 
            if(!empty($isInserted)) {
                 $staffData = $this->getStaffById(DB::getPdo()->lastInsertId());
            
                // Encrypt the ID
                $staffData->doc_staff_id = $this->securityLibObj->encrypt(DB::getPdo()->lastInsertId());
                $staffData->user_id = $this->securityLibObj->encrypt($staffData->user_id);
                return $staffData;
            }
        }
        return false;
    }

    /**
     * @DateOfCreation        08 June 2018
     * delete doctor staff with regarding id
     * @param int $id staff id
     * @return boolean perticular doctor staff detail delete or not
     */
    public function deleteStaff($doc_staff_id) {
        $updateData = array(
                        'is_deleted' => Config::get('constants.IS_DELETED_YES')
                        );
        $whereData = array( 'doc_staff_id' => $doc_staff_id );
        
        $queryResult =  $this->dbUpdate($this->table, $updateData, $whereData);
        if($queryResult){
            return true;
        }
        return false;
    }

    /**
     * @DateOfCreation        17 Aug 2018
     * @ShortDescription      This function is responsible for staff list query from user and staff tables
     * @param                 Array $data This contains full Patient user input data 
     * @return                Array of patients
     */
    public function staffListQuery($docUserId){
        
        $selectData = ['users.user_firstname','users.user_lastname','users.user_id','doctors_staff.doc_staff_id','doctors_staff.doc_user_id', 'users.user_gender', 'users.user_mobile', 'users.user_email', 'users.user_type', 'users.user_adhaar_number'];

        $whereData = array(
                        'users.is_deleted'      => Config::get('constants.IS_DELETED_NO'),
                        'doctors_staff.is_deleted'   => Config::get('constants.IS_DELETED_NO'),
                        'doctors_staff.doc_user_id'   => $docUserId,
                    );
        $listQuery = DB::table('users')
                        ->join('doctors_staff', 'doctors_staff.user_id', '=', 'users.user_id')
                        ->select($selectData)
                        ->where($whereData);     
                                  
        return $listQuery;
    }


    
    /**
    * @DateOfCreation        10 Apr 2018
    * @ShortDescription      This function is responsible for creating new user in DB
    * @param                 Array $data This contains full user input data 
    * @return                True/False
    */
    public function updateStaffUser($updateData, $whereData)
    {
        // @var Boolean $response
        // This variable contains insert query response
        $response = false;
        // @var Array $inserData
        // Prepair update query
        $response = $this->dbUpdate('users', $updateData, $whereData);
            return $response;
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