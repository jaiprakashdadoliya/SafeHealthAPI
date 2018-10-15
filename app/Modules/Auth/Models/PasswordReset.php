<?php
namespace App\Modules\Auth\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use App\Traits\Encryptable;

/**
 * PasswordReset Class
 *
 * @package                Safe Health
 * @subpackage             PasswordReset
 * @category               Model
 * @DateOfCreation         16 May 2018
 * @ShortDescription       This is model which need to perform the options related to 
                           PasswordReset table

 */
class PasswordReset extends Model {
    
    use Notifiable,HasApiTokens,Encryptable;

    // @var Array $encryptedFields
    // This protected member contains fields that need to encrypt while saving in database
    protected $encryptable = [];
   
     // @var string $table
    // This protected member contains primary key
    protected $primaryKey = 'email';

     // @var string $table
    // This protected member contains table name
    protected $table = 'password_resets';

    /**
     * @DateOfCreation        31 July 2018
     * @ShortDescription      This function is responsible for check if reset password token exist in database
     * @param                 @email
     * @return                array
     */
    public function checkTokenExist($email)
    {   
        return PasswordReset::where('email', $email)->first();
    }

    /**
     * @DateOfCreation        28 June 2018
     * @ShortDescription      This function is responsible for check if reset password token validity (expired or active)
     * @param                 @token
     * @return                array of token details
     */
    public function checkTokenValidity($token){
        DB::enableQueryLog();

        $tokenDetails = DB::table($this->table)
                        ->where('token','=',$token)
                        ->where('created_at','>',Carbon::now()->subHours(3))
                        ->first();

        return $tokenDetails;
    }
}