<?php

namespace App\Modules\Auth\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Passport\HasApiTokens;
use App\Traits\Encryptable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Libraries\SecurityLib;
use Config;
/**
 * Auth
 *
 * @package                Safe Health
 * @subpackage             Auth
 * @category               Model
 * @DateOfCreation         09 May 2018
 * @ShortDescription       This is model which need to perform the options related to 
                           users table

 */
class Auth extends Authenticatable {

    use Notifiable,HasApiTokens,Encryptable;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // Init security library object
        $this->securityLibObj = new SecurityLib();
    }
    // @var Array $encryptedFields
    // This protected member contains fields that need to encrypt while saving in database
    protected $encryptable = [];
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_firstname', 'user_lastname', 'user_email', 'user_password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    // @var string $table
    // This protected member contains table name
    protected $table = 'users';

    // @var string $primaryKey
    // This protected member contains primary key
    protected $primaryKey = 'user_id';

    // This public member over ride the password field
    public function getAuthPassword() {
        return $this->user_password;
    }

    
    /**
    * @DateOfCreation        10 Apr 2018
    * @ShortDescription      This function is responsible for creating new user in DB
    * @param                 Array $data This contains full user input data 
    * @return                True/False
    */
    public function createUser($data)
    {
        // @var Boolean $response
        // This variable contains insert query response
        $response = false;
        // @var Array $inserData
        // This Array contains insert data for users
        $insertData = array(
            'user_firstname'        => $data['user_firstname'],
            'user_lastname'         => $data['user_lastname'],
            'user_mobile'           => $data['user_mobile'],
            'user_country_code'     => $data['user_country_code'],
            'user_gender'           => $data['user_gender'],
            'user_email'            => $data['user_email'],
            'user_status'           => $data['user_status'],
            'user_password'         => Hash::make($data['user_password']),
            'user_adhaar_number'    => $data['user_adhaar_number'],
            'user_type'             => $data['user_type'],
            'resource_type'         => $data['resource_type'],
            'ip_address'            => $data['ip_address']
        ); 
       
        // Prepair insert query
        $response = $this->dbInsert($this->table, $insertData);
        if($response){
            $id = DB::getPdo()->lastInsertId(); 
            return $id;
        }else{
            return $response;
        }        
    }


    /**
    * @DateOfCreation        18 May 2018
    * @ShortDescription      Get the Aceess token on behalf of user id 
    * @return                Array
    */
    public function authAccessToken(){
         return $this->hasMany('App\Modules\Auth\Models\OauthAccessToken','user_id','user_id');
    }

    /**
    * @DateOfCreation        22 May 2018
    * @ShortDescription      This function is responsible for get the user info for user_id
    * @param                 Integer $user_id Currect user ID
    * @return                Array userinfo or False
    */
    public function getUserInfo($authUser)
    {   
     if($authUser->user_type == Config::get('constants.USER_TYPE_PATIENT')){
            $joinTableName = "patients";
            $prefix = "pat_";
            $joinTableName2 = "patients_visits";
            $user = DB::table('users')
            ->join($joinTableName,$joinTableName.'.user_id', '=', 'users.user_id')
            ->join($joinTableName2,$joinTableName2.'.pat_id', '=', 'users.user_id')
            ->select(
                    'users.user_id',
                    'users.user_firstname',
                    'users.user_lastname',
                    'users.user_email',
                    'users.user_mobile',
                    'users.user_type',
                    $joinTableName2.'.visit_id',
                    'patients.pat_profile_img'
                )
                ->where('users.user_id',$authUser->user_id)
                ->where($joinTableName2.'.user_id','0')
                ->first();
            if($user){
                $profile_img = $prefix.'profile_img';
                $user->user_id = $this->securityLibObj->encrypt($user->user_id);
                $user->visit_id = !empty($user->visit_id) ? $this->securityLibObj->encrypt($user->visit_id) : '';
                //$user->$profile_img = !empty($user->$profile_img) ? $this->securityLibObj->encrypt($user->$profile_img) : '';
                $user->pat_profile_img = !empty($user->pat_profile_img) ? url('api/patient-profile-image/'.$this->securityLibObj->encrypt($user->pat_profile_img)) : '';
                
                
                return $user;
            }else{
                return false;
            }
        }else if($authUser->user_type == Config::get('constants.USER_TYPE_DOCTOR')){
            $joinTableName = "doctors";
            $prefix = "doc_";
            $user = DB::table('users')
                ->join($joinTableName,$joinTableName.'.user_id', '=', 'users.user_id')
                ->select(
                        'users.user_id',
                        'users.user_firstname',
                        'users.user_lastname',
                        'users.user_email',
                        'users.user_mobile',
                        'users.user_type',
                        'doctors.doc_profile_img'
                    )
                    ->where('users.user_id',$authUser->user_id)
                    ->first();
            if($user){
                $profile_img = $prefix.'profile_img';
                $user->user_id = $this->securityLibObj->encrypt($user->user_id);
                //$user->$profile_img = !empty($user->$profile_img) ? $this->securityLibObj->encrypt($user->$profile_img) : '';
                $user->doc_profile_img = !empty($user->doc_profile_img) ? url('api/profile-image/'.$this->securityLibObj->encrypt($user->doc_profile_img)) : '';
                
                return $user;
            }else{
                return false;
            }
        }else if(in_array($authUser->user_type, Config::get('constants.USER_TYPE_STAFF'))){
            $joinTableName = "doctors_staff";
            $prefix = "doc_staff_";
            $user = DB::table('users')
                ->join($joinTableName,$joinTableName.'.user_id', '=', 'users.user_id')
                ->select(
                        'users.user_id',
                        'users.user_firstname',
                        'users.user_lastname',
                        'users.user_email',
                        'users.user_mobile',
                        'users.user_type',
                        'doctors_staff.doc_user_id',
                        'doctors_staff.doc_staff_profile_image'
                    )
                    ->where('users.user_id',$authUser->user_id)
                    ->first();
                if($user){
                    $profile_img = $prefix.'profile_image';
                    $user->user_id = $this->securityLibObj->encrypt($user->user_id);
                    $user->profile_img = !empty($user->profile_img) ? url('api/profile-image/'.$this->securityLibObj->encrypt($user->profile_img)) : '';

                    return $user;
                }else{
                    return false;
                }
        }else if($authUser->user_type == Config::get('constants.USER_TYPE_LAB_MANAGER')){
            $joinTableName = "laboratories";
            $user = DB::table('users')
                ->join($joinTableName,$joinTableName.'.user_id', '=', 'users.user_id')
                ->select(
                        'users.user_id',
                        'users.user_firstname',
                        'users.user_lastname',
                        'users.user_email',
                        'users.user_mobile',
                        'users.user_type',
                        'laboratories.lab_id',
                        'laboratories.lab_featured_image'
                    )
                    ->where('users.user_id',$authUser->user_id)
                    ->first();
                if($user){
                    $user->user_id = $this->securityLibObj->encrypt($user->user_id);
                    $user->lab_featured_image = !empty($user->lab_featured_image) ? url('api/lab-featured-image/'.$this->securityLibObj->encrypt($user->lab_featured_image)) : '';

                    return $user;
                }else{
                    return false;
                }
        }
    }


     /**
    * @DateOfCreation        19 July 2018
    * @ShortDescription      Test data inserting function 
    * @return                Array
    */
    public function createSpecaility($insertData){
        $response = $this->dbInsert("doctors_specialisations", $insertData);
        return true;
    }

     /**
    * @DateOfCreation        19 July 2018
    * @ShortDescription      Test data inserting function 
    * @return                Array
    */
    public function createDoctor($insertData)
    {
        $response = $this->dbInsert("doctors", $insertData);
        return true;
    }

    /**
    * @DateOfCreation        19 July 2018
    * @ShortDescription      Test data inserting function 
    * @return                Array
    */
    public function createAward($insertData)
    {
        $response = $this->dbInsert("doctors_awards", $insertData);
        return true;
    }


    /**
     * @DateOfCreation        12 June 2018
     * @ShortDescription      update verifiction email/mobile according to userVerObjType
     * @param                 Integer $userId email/mobile verification user ID
     * @param                 Integer $userVerObjType email/mobile verification type
     * @return                Array
     */
    public function updateUserVerficationData($userId,$userVerObjType) 
    {
        $updateData = [];
        $whereData  = ['user_id'=> $userId,'is_deleted'=>  Config::get('constants.IS_DELETED_NO')];
        if( Config::get('constants.USER_VERI_OBJECT_TYPE_EMAIL') == $userVerObjType){        
            $updateData = ['user_is_email_verified'=>1];
        }
        if( Config::get('constants.USER_VERI_OBJECT_TYPE_MOBILE') == $userVerObjType){        
            $updateData = ['user_is_mob_verified'=>Config::get('constants.USER_MOB_VERIFIED_YES')];
        }
        $result = $this->dbUpdate('users',$updateData,$whereData);
        return $result;
    }
    
    /**
     * @DateOfCreation        14 June 2018
     * @ShortDescription      function update user data by requested column and condition
     * @param                 array $updateData
     * @param                 array $where
     * @return                Array
     */
    public function userDataUpdate($updateData,$where) 
    {
        $result = $this->dbUpdate($this->table, $updateData, $where);
        return $result;
    }
}
