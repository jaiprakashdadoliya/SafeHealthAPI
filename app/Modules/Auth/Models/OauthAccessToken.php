<?php

namespace App\Modules\Auth\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\HasApiTokens;
use App\Traits\Encryptable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
/**
 * OauthAccessToken
 *
 * @package                Safe Health
 * @subpackage             OauthAccessToken
 * @category               Model
 * @DateOfCreation         22 May 2018
 * @ShortDescription       This model connect with the OauthAccessToken table 
 */
class OauthAccessToken extends Model {

    use HasApiTokens,Encryptable;
    // @var Array $encryptedFields
    // This protected member contains fields that need to encrypt while saving in database
    protected $encryptable = [];
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [];
}
