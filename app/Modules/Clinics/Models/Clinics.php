<?php

namespace App\Modules\Clinics\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Laravel\Passport\HasApiTokens;
use App\Traits\Encryptable;
use App\Libraries\SecurityLib;
use Config;
use Carbon\Carbon;

/**
 * Clinics
 *
 * @package                 Safehealth
 * @subpackage              Clinics
 * @category                Model
 * @DateOfCreation          27 June 2018
 * @ShortDescription        This Model to handle database operation with current table
                            doctors clinics
 **/
class Clinics extends Model {

    use HasApiTokens,Encryptable;

    // @var string $table
    // This protected member contains table name
    protected $table = 'clinics';

    // @var string $primaryKey
    // This protected member contains primary key
    protected $primaryKey = 'clinic_id';

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
    * @DateOfCreation        29 Apr 2018
    * @ShortDescription      This function is responsible for creating new clinic in DB
    * @param                 Array $data This contains full user input data 
    * @return                True/False
    */
    public function createClinic($data, $userId)
    {
        // @var Boolean $response
        // This variable contains insert query response
        $response = false;

        // @var Array $inserData
        // This Array contains insert data for users
        $inserData = array(
            'clinic_name'           => $data['user_firstname']." ".$data['user_lastname']." "."Clinic",
            'user_id'               => $userId,
            'clinic_phone'          => '',
            'clinic_address_line1'  => '',
            'clinic_address_line2'  => '',
            'clinic_landmark'       => '',
            'clinic_pincode'        => '',
            'resource_type'         => $data['resource_type'],
            'ip_address'            => $data['ip_address'],
            'created_by'            => 0,
            'updated_by'            => 0
        );

        // Prepair insert query
        $response = DB::table($this->table)->insert(
                        $inserData
                    );
        return $response;
    }

    /**
     * Create Clinic List with regarding details
     *
     * @param array $data timing data
     * @return int doctor time id if inserted otherwise false
     */
    public function getClinicListForTiming($user_id = '') { 
        $selectData  =  ['clinic_id as value', 'clinic_name as label'];
        $whereData   =  array(
                            'user_id' => $user_id,
                            'is_deleted' => Config::get('constants.IS_DELETED_NO')
                        );
        $clinicList = $this->dbBatchSelect($this->table, $selectData, $whereData)
                        ->map(function($clinics){
                            $clinics->value = $this->securityLibObj->encrypt($clinics->value);
                            return $clinics;
                        });
        return $clinicList;
    }

    /**
     * Create Clinic List with regarding details
     *
     * @param array $data timing data
     * @return int doctor time id if inserted otherwise false
     */
    public function getClinicList($requestData) { 
        // DB::enableQueryLog();
        $selectData  =  ['clinic_id', 'clinic_name', 'clinic_phone', 'clinic_address_line1', 'clinic_address_line2', 'clinic_landmark', 'clinic_pincode'];
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
                                ->orWhere('clinic_name', 'ilike', "%".$value['value']."%")
                                ->orWhere(DB::raw('CAST(clinic_phone AS TEXT)'), 'like', "%".$value['value']."%")
                                ->orWhere('clinic_address_line1', 'ilike', "%".$value['value']."%");
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
        $clinic['pages'] = ceil($query->count()/$requestData['pageSize']);
        $clinic['result'] =  $query
                        ->offset($offset)
                        ->limit($requestData['pageSize'])
                        ->get()
                        ->map(function($clinics){
                            $clinics->clinic_id = $this->securityLibObj->encrypt($clinics->clinic_id);
                            return $clinics;
                        });
        return $clinic;
    }

    /**
    * @DateOfCreation        10 July 2018
    * @ShortDescription      This function is responsible to get the clinic record by id
    * @param                 String $doc_clinic_id
    * @return                Array of clinic data
    */
    public function getClinicById($clinic_id)
    {
        $queryResult = DB::table($this->table)
            ->select('clinic_id', 'clinic_name', 'clinic_phone', 'clinic_address_line1', 'clinic_address_line2', 'clinic_pincode')
            ->where('clinic_id', $clinic_id)
            ->first();
         return $this->decryptSingleData($queryResult);
    }

    /**
     * @DateOfCreation        08 June 2018
     * Create or Edit doctor clinic with regarding details
     * @param array $data clinic data
     * @return Array doctor clinic if inserted otherwise updated
     */
    public function saveClinic($requestData=array()) {
        $requestData['updated_at'] = Carbon::now();
        $requestData['updated_by'] = $requestData['user_id'];

        if($requestData['clinic_id'] && !empty($requestData['clinic_id'])) {
            $requestData = $this->encryptData($requestData);
            $isUpdated = DB::table($this->table)
                        ->where('clinic_id', $requestData['clinic_id'])
                        ->update($requestData);
            if(!empty($isUpdated)) {
                $clinicData = $this->getClinicById($requestData['clinic_id']);
                $clinicData->clinic_id = $this->securityLibObj->encrypt($clinicData->clinic_id);
                return $clinicData;
            }
        }else{
            unset($requestData['clinic_id']);
            $requestData['created_by'] = $requestData['user_id'];
            $requestData['created_at'] = Carbon::now();
            $requestData = $this->encryptData($requestData);
            $isInserted = DB::table($this->table)->insert($requestData); 
            if(!empty($isInserted)) {
                 $clinicData = $this->getClinicById(DB::getPdo()->lastInsertId());

                // Encrypt the ID
                $clinicData->clinic_id = $this->securityLibObj->encrypt(DB::getPdo()->lastInsertId());
                return $clinicData;
            }
        }
        return false;
    }

    /**
     * @DateOfCreation        08 June 2018
     * delete doctor clinic with regarding id
     * @param int $id clinic id
     * @return boolean perticular doctor clinic detail delete or not
     */
    public function deleteClinic($clinic_id) {
        $updateData = array(
                        'is_deleted' => Config::get('constants.IS_DELETED_YES')
                        );
        $whereData = array( 'clinic_id' => $clinic_id );
        
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
