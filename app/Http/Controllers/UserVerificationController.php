<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Investigator;
use App\Traits\RestApi;
use Config;
use DB;
use App\Libraries\SecurityLib;
use App\Libraries\ExceptionLib;
use App\Libraries\DateTimeLib;
use App\Modules\Auth\Models\UserVerification;
use App\Modules\Auth\Models\Auth as Users;

/**
 * UserVerificationController
 *
 * @package                ILD India registry
 * @subpackage             UserVerificationController
 * @category               Controller
 * @DateOfCreation         12 June 2018
 * @ShortDescription       email/mobile user Verification
 */
class UserVerificationController extends Controller
{
    use RestApi;
    protected $http_codes = [];
    
    public function __construct(Request $request)
    {
        $this->http_codes = $this->http_status_codes();

        // Init security library object
        $this->securityLibObj = new SecurityLib(); 

        // Init exception library object
        $this->exceptionLibObj = new ExceptionLib();

        // Init datetime library object
        $this->dateTimeLibObj = new DateTimeLib();

        // Init UserVerification model object
        $this->userVerificationObj = new UserVerification(); 

        // Init UserVerification model object
        $this->authObj = new Users();
    }

    /**
     * @DateOfCreation        12 june 2018
     * @ShortDescription      This function is responsible for verify email
     * @param                 Array $userId   
     * @param                 Array $hashToken   
     */
    public function verifyUserEmail($userId,$hashToken ){
        $userIdDecrypt = $this->securityLibObj->decrypt($userId);
        $hashTokenDecrypt = $this->securityLibObj->decrypt($hashToken);
        $currentTime = $this->dateTimeLibObj->getPostgresTimestampAfterXmin();
        $verifyResult = $this->userVerificationObj->getVerificationDetailByhashAndUserId($hashTokenDecrypt,$userIdDecrypt,$currentTime);
    	if (!empty($verifyResult)){
            $userVerObjType =$verifyResult->user_ver_obj_type;
            $this->authObj->updateUserVerficationData($userIdDecrypt,$userVerObjType);
            $this->userVerificationObj->deleteTokenLink($verifyResult->user_ver_id);
            $message = ($userVerObjType == Config::get('constants.USER_VERI_OBJECT_TYPE_EMAIL') ? 'Email' : ($userVerObjType == Config::get('constants.USER_VERI_OBJECT_TYPE_MOBILE') ? 'Mobile':'') ).' '. trans('register_investigator.user_verification_successfully'); 
    	}else {
    		$message = trans('register_investigator.user_verification_unsuccessfully');
    	}
        
        return view('frontend.user_verification_process',['message'=>$message]);
    }
}
