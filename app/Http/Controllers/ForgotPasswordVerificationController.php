<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Investigator;
use App\Traits\RestApi;
use Config;
use DB;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Support\Facades\Password;
use Illuminate\Foundation\Auth\ResetsPasswords;
use App\Libraries\SecurityLib;
use App\Libraries\ExceptionLib;
use App\Libraries\DateTimeLib;
use App\Modules\Auth\Models\PasswordReset;
use App\Modules\Auth\Models\UserVerification;
use Illuminate\Contracts\Hashing\Hasher as HasherContract;

/**
 * ForgotPasswordVerificationController
 *
 * @package                ILD India registry
 * @subpackage             ForgotPasswordVerificationController
 * @category               Controller
 * @DateOfCreation         12 June 2018
 * @ShortDescription       reset password Verification
 */
class ForgotPasswordVerificationController extends Controller
{
    use RestApi;
    protected $http_codes = [];

    // @var Array $hasher
    // This protected member used for forgot password token
    protected $hasher;
    
    public function __construct(HasherContract $hasher)
    {
    	$this->hasher = $hasher;

        $this->http_codes = $this->http_status_codes();

        // Init security library object
        $this->securityLibObj = new SecurityLib(); 

        // Init exception library object
        $this->exceptionLibObj = new ExceptionLib();

        // Init datetime library object
        $this->dateTimeLibObj = new DateTimeLib();

        // Init forward password verification model object
        $this->passwordResetObj = new PasswordReset(); 

        // Init UserVerification model object
        $this->userVerificationObj = new UserVerification();
    }

    /**
     * @DateOfCreation        14 June 2018
     * @ShortDescription      This function is responsible for verify reset password token
     * @param                 Array $request   
     * @return                status of verified token
     */
    public function verifyToken($emailToken, $token)
    {   
        $userEmail          = $this->securityLibObj->decrypt($emailToken);
        $hashTokenDecrypt   = $this->securityLibObj->decrypt($token);
        $currentTime        = $this->dateTimeLibObj->getPostgresTimestampAfterXmin();
        $verifyResult = $this->userVerificationObj->getVerificationDetailByhashAndUserEmail($hashTokenDecrypt, $userEmail, $currentTime);
        if (!empty($verifyResult)){
            $result = ['isTokenValid' => Config::get('constants.IS_TOKEN_VALID_FORGOTPASSWORD'), 'token' => $token, 'emailToken' => $emailToken];
        }else {
            $result = ['isTokenValid' => Config::get('constants.EXPIRE_IS_TOKEN_VALID_FORGOTPASSWORD')];
        }
        return view('frontend.forget_password', $result);
    }
}
