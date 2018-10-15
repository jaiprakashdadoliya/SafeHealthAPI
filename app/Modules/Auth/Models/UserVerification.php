<?php

namespace App\Modules\Auth\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Passport\HasApiTokens;
use App\Traits\Encryptable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
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
class UserVerification extends Model{

    use Notifiable,HasApiTokens,Encryptable;
    
    // @var Array $encryptedFields
    // This protected member contains fields that need to encrypt while saving in database
    protected $encryptable = [];   

    // @var string $table
    // This protected member contains table name
    protected $table = 'user_verifications';

    // @var string $primaryKey
    // This protected member contains primary key
    protected $primaryKey = 'user_ver_id';

    
    /**
    * @DateOfCreation        23 Apr 2018
    * @ShortDescription      This function is responsible for saving otp to database
    * @param                 Array $data This contains full user input data 
    * @return                True/False
    */
    public function saveOTPInDatabase($data){
        // @var Boolean $response
        // This variable contains insert query response
        $response = false;
        
        if($this->isMobileNumberExist($data['user_ver_object'])){
            // Unset creation data
            unset($data['created_at']);
            unset($data['created_by']);
            
            // Update record if exist
            $response = DB::table($this->table)
                        ->where('user_ver_object', $data['user_ver_object'])
                        ->update($data);
        }else{
            // Prepair insert query
            $response = DB::table($this->table)->insert(
                        $data
                    );
        }       
        
        return $response;
    }
    
    /**
    * @DateOfCreation        23 Apr 2018
    * @ShortDescription      This function is responsible for saving link hash to database
    * @param                 Array $data This contains full user input data 
    * @return                True/False
    */
    public function saveLinkHashInDatabase($data){
        // @var Boolean $response
        // This variable contains insert query response
        $response = false;
        
        // Prepair insert query
        $response = DB::table($this->table)->insert(
                    $data
                );       
        
        return $response;
    }
    
    /**
    * @DateOfCreation        24 May 2018
    * @ShortDescription      This function is to check mobile number exist in user_verification table
    * @param                 Array $data This contains full user input data 
    * @return                True/False
    */
    public function isMobileNumberExist($mobileNumber){
        $recordsCount = DB::table($this->table)
                        ->select('user_ver_id')
                        ->where('user_ver_object', $mobileNumber)
                        ->count();
        if($recordsCount > 0){
            return true;
        }else{
            return false;
        }
    }
    
    /**
    * @DateOfCreation        24 May 2018
    * @ShortDescription      This function is to get the otp by mobile number
    * @param                 String $mobileNumber This contains user mobile number
    * @return                Result Object
    */
    public function getVerificationDetailByMob($mobileNumber){
        $result = DB::table($this->table)
                        ->select('user_ver_hash_otp', 'user_ver_expiredat')
                        ->where('user_ver_object', $mobileNumber)
                        ->first();
        return $result;
    }

    /**
     * @DateOfCreation        31 July 2018
     * @ShortDescription      This function is to get the otp by mobile number
     * @param                 String $mobileNumber This contains user mobile number
     * @return                Result Object
     */
    public function getVerificationDetailByhashAndUserId($hashToken,$userID,$currentTime){
        $result = DB::table($this->table)
                        ->select('user_ver_id', 'user_ver_obj_type', 'user_ver_expiredat')
                        ->where('user_ver_hash_otp', $hashToken)
                        ->where('user_id', $userID)
                        ->where('is_deleted', Config::get('constants.IS_DELETED_NO'))
                        ->where('user_ver_expiredat','>=', $currentTime)
                        ->first();
        return $result;
    }
    
    /**
     * @DateOfCreation        23 July 2018
     * @ShortDescription      This function is to get the otp by mobile number
     * @param                 String $mobileNumber This contains user mobile number
     * @return                Result Object
     */
    public function getVerificationDetailByhashAndUserEmail($hashToken, $email, $currentTime){
        $result = DB::table($this->table)
                        ->select('user_ver_id', 'user_ver_obj_type', 'user_id', 'user_ver_expiredat')
                        ->where('user_ver_hash_otp', $hashToken)
                        ->where('user_ver_object', $email)
                        ->where('is_deleted', Config::get('constants.IS_DELETED_NO'))
                        ->where('user_ver_expiredat','>=', $currentTime)
                        ->first();
        return $result;
    }

    /**
     * @DateOfCreation        31 July 2018
     * @ShortDescription      This function is to delete token link
     * @param                 integer $userVerId 
     */
    public function deleteTokenLink($userVerId){
        $whereData  = ['user_ver_id'=> $userVerId,'is_deleted'=>  Config::get('constants.IS_DELETED_NO')];
        $updateDate = ['is_deleted' =>  Config::get('constants.IS_DELETED_YES')];
        $result = $this->dbUpdate($this->table,$updateDate,$whereData);
        return $result;
    }
    
}
