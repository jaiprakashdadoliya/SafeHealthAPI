<?php

namespace App\Modules\Auth\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Modules\Auth\Models\Auth as Users;
use App\Modules\Auth\Models\PasswordReset;
use Auth;
use App\Traits\RestApi;
use Config;
use Session;
use App\Libraries\SecurityLib;
use App\Libraries\EmailLib;
use App\Libraries\ExceptionLib;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Support\Facades\Password;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Contracts\Hashing\Hasher as HasherContract;
use App\Libraries\UtilityLib;
use App\Libraries\DateTimeLib;
use App\Modules\Auth\Models\UserVerification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Sabberworm\CSS\Value\URL;
use App\Modules\Doctors\Models\Doctors;
use App\Modules\Clinics\Models\Clinics;
use App\Modules\Patients\Models\Patients;
use App\Modules\DoctorProfile\Models\Timing;
use App\Modules\Visits\Models\Visits;
use App\Traits\Encryptable;
use Spatie\Activitylog\Models\Activity;
use App\Modules\Laboratories\Models\Laboratories;
use Lcobucci\JWT\Parser;
use File;
/**
 * AuthController
 *
 * @package                SafeHealth
 * @subpackage             AuthController
 * @category               Controller
 * @DateOfCreation         09 May 2018
 * @ShortDescription       This class is responsiable for login, register, forgot password
 */
class AuthController extends Controller
{

     use RestApi, SendsPasswordResetEmails;

    // @var Array $http_codes
    // This protected member contains Http Status Codes
    protected $http_codes = [];

    // @var Array $hasher
    // This protected member used for forgot password token
    protected $hasher;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(HasherContract $hasher)
    {
        $this->hasher = $hasher;
        $this->http_codes = $this->http_status_codes();
        
        // Init security library object
        $this->securityLibObj = new SecurityLib();  
        
        // Init utility library object
        $this->utilityLibObj = new UtilityLib();
        
        // Init datetime library object
        $this->dateTimeLibObj = new DateTimeLib();
        
        // Init Auth model object
        $this->authModelObj = new Users();
        
        // Init UserVerification model object
        $this->userVerificationObj = new UserVerification();
        
        // Init exception library object
        $this->exceptionLibObj = new ExceptionLib();
        
        // Init Doctor model object
        $this->doctorModelObj = new Doctors();
        
        // Init Clinics model object
        $this->clinicsModelObj = new Clinics();

        // Init patient model object
        $this->patientModelObj = new Patients();

        // Init timing model object
        $this->timingObj = new Timing();

        // Init Visits model object
        $this->visitsModelObj = new Visits();

        // Init Visits model object
        $this->labModelObj = new Laboratories();
    }
    /**
    * @DateOfCreation        09 May 2018
    * @ShortDescription      Get a validator for an incoming User request
    * @param                 \Illuminate\Http\Request  $request
    * @return                \Illuminate\Contracts\Validation\Validator
    */
    protected function loginValidations($requestData, $type){
        $errors         = [];
        $error          = false;
        $validationData = [];

        // Check the login type is Email or Mobile
        if(is_numeric($requestData['user_username'])){
            $validationData = [
                'user_username' => 'required|max:10',
            ];
        }else{
            $validationData = [
                'user_username' => 'required|email|max:150',
            ];
        }

        //  For Login method only
        if($type == 'login'){
            $validationData['user_password'] = 'required';
        }
        
        // For Reset Password method only
        if($type == "resetpassword"){
            $validationData['token']    = 'required';
            $validationData['password'] = 'required';
        }

        //  For update password method only
        if($type == 'updatePassword'){
            $validationData['user_password'] = 'required|min:6|regex:/^.*(?=.{3,})(?=.*[a-zA-Z])(?=.*[0-9])(?=.*[\d\X])(?=.*[!@$#%]).*$/';
        }
        $validator  = Validator::make(
            $requestData,
            $validationData
        );

        // finally check the Validation corect or not
        if($validator->fails()){
            $error  = true;
            $errors = $validator->errors();
        }
        return ["error" => $error,"errors"=>$errors];
    }

    /**
    * @DateOfCreation        09 May 2018
    * @ShortDescription      This function is responsible for check the login data 
    * @param                 Array $request   
    * @return                Array of status and message
    */
    public function postLogin(Request $request)
    {
        $userInfo = [];
        $requestData = $this->getRequestData($request);
        $validate    = $this->loginValidations($requestData, 'login');
        if($validate["error"]){
            return $this->resultResponse(
                Config::get('restresponsecode.ERROR'), 
                [], 
                $validate['errors'],
                trans('Auth::messages.user_validation_error'), 
                $this->http_codes['HTTP_OK']
            ); 
        }

        // Check the login type is Email or Mobile
        $requestType = $this->checkRequestType($requestData['user_username']);
        
        $user = Users::where($requestType,$requestData['user_username'])->first();
        if($user){
            // Check if user is not active or deleted
            if( $user->is_deleted == Config::get('constants.IS_DELETED_YES') OR 
                $user->user_status != Config::get('constants.USER_STATUS_ACTIVE')
            ){
                    return $this->resultResponse(
                            Config::get('restresponsecode.ERROR'), 
                            [], 
                            ["user" => [trans('Auth::messages.user_not_active_or_deleted')]],
                            trans('Auth::messages.user_not_active_or_deleted'), 
                            $this->http_codes['HTTP_NOT_FOUND']
                        );
            }

            $inputData = array(
                $requestType => $requestData['user_username'],
                'password' => $requestData['user_password']
               );
            if (Auth::attempt($inputData)) {
                if($request->route()->getPrefix() == Config::get('constants.API_PREFIX')){
                    $userInfo = $this->authModelObj->getUserInfo(Auth::user());
                    return $this->resultResponse(
                        Config::get('restresponsecode.SUCCESS'), 
                        [
                            "accessToken" => $user->createToken('Auth::messages.app_name')->accessToken
                            ,"user" => $userInfo], 
                        [],
                        trans('Auth::messages.user_verified'), 
                        $this->http_codes['HTTP_OK']
                    );
                }else{
                    return $this->resultResponse(
                        Config::get('restresponsecode.SUCCESS'), 
                        Auth::user(), 
                        [],
                        trans('Auth::messages.user_verified'),
                        $this->http_codes['HTTP_OK']
                    );
                }
            }else{
                return $this->resultResponse(
                    Config::get('restresponsecode.ERROR'), 
                    [], 
                    ["user_password" => [trans('Auth::messages.incorrect_password')]],
                    trans('Auth::messages.incorrect_password'), 
                    $this->http_codes['HTTP_NOT_ACCEPTABLE']
                );  
            }
        }else{
            return $this->resultResponse(
                Config::get('restresponsecode.ERROR'), 
                [], 
                ["user_email" => [trans('Auth::messages.user_not_found')]],
                trans('Auth::messages.user_not_found'), 
                $this->http_codes['HTTP_NOT_FOUND']
            );
        }
    }

     /**
    * @DateOfCreation        24 May 2018
    * @ShortDescription      This function is responsible check the request type and return 
                             correct one
    * @param                 String/Number $user_username   
    * @return                String/number $requestType
    */
    protected function checkRequestType($user_username){
        if(is_numeric($user_username)){
            $requestType = "user_mobile";
        }else{
            $requestType = "user_email";
        }
        return $requestType;
    }
    
    /**
    * @DateOfCreation        31 July 2018
    * @ShortDescription      This function is responsible for generate the Reset tocken 
    * @param                 Array $request   
    * @return                Array of status and message
    */
    public function getResetToken(Request $request)
    {
        $requestData = $this->getRequestData($request);
        $validate    = $this->loginValidations($requestData, 'forgot');
        if($validate["error"]){
            return $this->resultResponse(
                Config::get('restresponsecode.ERROR'), 
                [], 
                $validate['errors'],
                trans('Auth::messages.user_validation_error'), 
                $this->http_codes['HTTP_OK']
            ); 
        }
        $requestType = $this->checkRequestType($requestData['user_username']);
        $user = Users::where($requestType, $requestData['user_username'])->where('is_deleted',Config::get('constants.IS_DELETED_NO'))->first();
        
        
        if (!$user) {
            return $this->resultResponse(
                Config::get('restresponsecode.ERROR'), 
                [], 
                ["email" => [trans('Auth::messages.user_not_found')]],
                trans('Auth::messages.user_not_found'), 
                $this->http_codes['HTTP_NOT_FOUND']
            );
        }
        $isMailSent = $this->sendVerificationLink($user, $user['user_id'], $resetType = 'resetPassword');
        
        if($isMailSent){
            return $this->resultResponse(
                Config::get('restresponsecode.SUCCESS'), 
                [], 
                [],
                trans('Auth::messages.forgot_link_sent'),
                $this->http_codes['HTTP_OK']
            );
        }else{
            DB::rollback();
            return $this->resultResponse(
                Config::get('restresponsecode.ERROR'), 
                [], 
                [],
                trans('Auth::messages.forgot_link_sent_failed'), 
                $this->http_codes['HTTP_OK']
            );        
        }
    }
   
    /**
    * @DateOfCreation        31 July 2018
    * @ShortDescription      This function is responsible for reset password 
    * @param                 Array $request   
    * @return                Array of status and message
    */
    public function reset(Request $request)
    {
        $requestData = $this->getRequestData($request);
        $validate    =  $this->resetPasswordValidations($requestData);
        if($validate["error"]){
            return $this->resultResponse(
                Config::get('restresponsecode.ERROR'), 
                [], 
                $validate['errors'],
                trans('Auth::messages.user_validation_error'), 
                $this->http_codes['HTTP_OK']
            ); 
        }
        $userEmail          = $this->securityLibObj->decrypt($requestData['user_token']);
        $hashTokenDecrypt   = $this->securityLibObj->decrypt($requestData['token']);
        $currentTime        = $this->dateTimeLibObj->getPostgresTimestampAfterXmin();
        $verifyResult = $this->userVerificationObj->getVerificationDetailByhashAndUserEmail($hashTokenDecrypt, $userEmail, $currentTime);
        if(!empty($verifyResult)) 
        {
            $updateUserPassword = $this->authModelObj->userDataUpdate(['user_password' => bcrypt($requestData['password'])], ['user_email' => $userEmail, 'user_id' => $verifyResult->user_id, 'user_status'=>Config::get('constants.USER_STATUS_ACTIVE')]);
            if($updateUserPassword)
            {
                $this->userVerificationObj->deleteTokenLink($verifyResult->user_ver_id);
                return $this->resultResponse(
                    Config::get('restresponsecode.SUCCESS'), 
                    [], 
                    [],
                    trans('Auth::messages.password_reset_success'),
                    $this->http_codes['HTTP_OK']
                );                    
            }else{
                return $this->resultResponse(
                    Config::get('restresponsecode.ERROR'), 
                    [], 
                    [],
                    trans('Auth::messages.password_invalid_token_message'),
                    $this->http_codes['HTTP_OK']
                );
            }
            
        } else {
            return $this->resultResponse(
                Config::get('restresponsecode.ERROR'), 
                [], 
                ["email" => [trans('Auth::messages.password_invalid_token_message')]],
                trans('Auth::messages.password_invalid_token_message'), 
                $this->http_codes['HTTP_NOT_FOUND']
            );
        }
    }

    /**
    * @DateOfCreation        31 July 2018
    * @ShortDescription      Get a validator for an incoming reset password request
    * @param                 \Illuminate\Http\Request  $request
    * @return                \Illuminate\Contracts\Validation\Validator
    */
    protected function resetPasswordValidations($requestData){
        $errors         = [];
        $error          = false;
        $validationData = [];
        // Check the login type is Email or Mobile
        $validationData = [
            'password' => 'required|min:6|regex:'.Config::get('constants.REGEX_PASSWORD'),
        ];
        $validationMessageData = [
            'password.required' => trans('passwords.password_required'),
            'password.min'      => trans('passwords.password_validate_message'),
            'password.regex'    => trans('passwords.password_validate_message'),
        ];
      
        $validator  = Validator::make(
            $requestData,
            $validationData,
            $validationMessageData
        );
        if($validator->fails()){
            $error  = true;
            $errors = $validator->errors();
        }
        return ["error" => $error,"errors"=>$errors];
    }


    /**
    * @DateOfCreation        22 May 2018
    * @ShortDescription      This function is responsible to delete access token 
                             for current user
    * @param                 String $id   
    * @return                Array of status and message
    */
    public function logout($id, Request $request)
    { 
        $user_id = $this->securityLibObj->decrypt($id);
        
        $value  = $request->bearerToken();
        $id     = (new Parser( ))->parse($value)->getHeader('jti'); 
        $user   = Users::find($user_id);
        $token  = $user->authAccessToken()->find($id);

        if($token){
            if($request->route()->getPrefix() == Config::get('constants.API_PREFIX')){
                $token->delete();
                return $this->resultResponse(
                    Config::get('restresponsecode.SUCCESS'), 
                    [], 
                    [],
                    trans('Auth::messages.user_logged_out'),
                    $this->http_codes['HTTP_OK']
                );
            }else{
                Auth::logout();
                Session::flush();
                return $this->resultResponse(
                    Config::get('restresponsecode.SUCCESS'), 
                    [], 
                    ['user' => [trans('Auth::messages.user_logged_out')]],
                    trans('Auth::messages.user_logged_out'),
                    $this->http_codes['HTTP_OK']
                );
            }
        }
        return $this->resultResponse(
            Config::get('restresponsecode.ERROR'), 
            [], 
            ["email" => [trans('Auth::messages.user_logged_out')]],
            trans('Auth::messages.user_logged_out'), 
            $this->http_codes['HTTP_NOT_FOUND']
        );
    }
    
    /**
    * @DateOfCreation        10 May 2018
    * @ShortDescription      This function is responsible to register doctors 
    * @param                 Array $request   
    * @return                Array of status and message
    */
    public function postDoctorRegistration(Request $request)
    {        
        $requestData = $this->getRequestData($request);        
        $extra = [];
        
        // Validate request
        if($requestData['send_otp'] == 'n'){
            $extra['user_otp'] = 'required';
        } 
        
        if($requestData['user_email'] != ''){
            $extra['user_email'] =  'string|email|max:150|unique:users';
        }
        $validate = $this->doctorRegistrationValidator($requestData, $extra);
        if($validate["error"]){
            return $this->resultResponse(
                    Config::get('restresponsecode.ERROR'), 
                    [], 
                    $validate['errors'],
                    trans('Auth::messages.doctor_registration_validation_failed'), 
                    $this->http_codes['HTTP_OK']
                  ); 
        }
        
        // Send OTP 
        if($requestData['send_otp'] == 'y'){
            if($this->sendOtpToVerifyMobile($requestData)){
                return $this->resultResponse(
                        Config::get('restresponsecode.SUCCESS'), 
                        [], 
                        [],
                        trans('Auth::messages.doctor_otp_sent_successfully'), 
                        $this->http_codes['HTTP_OK']
                  );
            }else{
                return $this->resultResponse(                
                        Config::get('restresponsecode.ERROR'), 
                        [],
                        [
                           'user_otp' => [trans('Auth::messages.doctor_error_in_otp_genration')]
                        ], 
                        trans('Auth::messages.doctor_error_in_otp_genration'), 
                        $this->http_codes['HTTP_OK']
                  );
            }
        }else if(($otpErrorMsg = $this->isDoctorOTPValid($requestData)) != ''){
            return $this->resultResponse(                
                Config::get('restresponsecode.ERROR'), 
                [],
                [
                   'user_otp' => [$otpErrorMsg]
                ], 
                $otpErrorMsg, 
                $this->http_codes['HTTP_OK']
              );
        }        
        // Make user type as doctor
        $requestData['user_status']   = Config::get('constants.USER_STATUS_ACTIVE');
        $requestData['user_is_mob_verified']   = Config::get('constants.USER_MOB_VERIFIED_YES');
        
        // Create user in database 
        try {    
            DB::beginTransaction();
            $createdUserId = $this->authModelObj->createUser($requestData);

            // validate, is query executed successfully 
            if($createdUserId){
                // We are not paasing email error to user, we are logging error 
                if($requestData['user_type'] == Config::get('constants.USER_TYPE_DOCTOR')){
                    $this->doctorModelObj->createDoctor($requestData, $createdUserId);
                    $this->clinicsModelObj->createClinic($requestData, $createdUserId);
                }if($requestData['user_type'] == Config::get('constants.USER_TYPE_LAB_MANAGER')){
                    $this->labModelObj->createLaboratory($requestData, $createdUserId);
                }else{
                    $requestData['pat_code'] = $this->utilityLibObj->patientsCodeGenrator(6);
                    $this->patientModelObj->createPatient($requestData, $createdUserId);

                    $visitData = [
                        'user_id'       => Config::get('constants.DEFAULT_USER_VISIT_ID'),
                        'pat_id'        => $createdUserId,
                        'visit_type'    => Config::get('constants.PROFILE_VISIT_TYPE'),
                        'visit_number'  => Config::get('constants.INITIAL_VISIT_NUMBER'),
                        'resource_type' => $requestData['resource_type'],
                        'ip_address'    => $requestData['ip_address'],
                        'is_deleted'    => Config::get('constants.IS_DELETED_NO'),
                        'status'        => Config::get('constants.VISIT_COMPLETED'),
                    ];
                    
                    // Create default visit
                    $visitId = '';
                    $visitId = $this->visitsModelObj->createPatientDoctorVisit('patients_visits', $visitData);
                }

                $isMailSent = $this->sendVerificationLink($requestData, $createdUserId);
                if($isMailSent){
                    DB::commit();
                    // return success response 
                    return $this->resultResponse(
                            Config::get('restresponsecode.SUCCESS'), 
                            [], 
                            [],
                            trans('Auth::messages.doctor_registration_successfull'), 
                            $this->http_codes['HTTP_OK']
                          );
                }else{
                    DB::rollback();
                    return $this->resultResponse(
                            Config::get('restresponsecode.ERROR'), 
                            [], 
                            [],
                            trans('Auth::messages.doctor_registration_fail'), 
                            $this->http_codes['HTTP_OK']
                          );
                }                
            }else{
                DB::rollback();
                return $this->resultResponse(
                        Config::get('restresponsecode.ERROR'), 
                        [], 
                        [],
                        trans('Auth::messages.doctor_registration_fail'), 
                        $this->http_codes['HTTP_OK']
                      );
            }           
            
        } catch (\Exception $ex) {
            DB::rollback();
            $eMessage = $this->exceptionLibObj->reFormAndLogException($ex,'AuthController', 'postDoctorRegistration');            
            return $this->resultResponse(
                    Config::get('restresponsecode.EXCEPTION'), 
                    [], 
                    [],
                    $eMessage, 
                    $this->http_codes['HTTP_OK']
                  );
        }        
    }
    
    /**
    * @DateOfCreation        28 May 2018
    * @ShortDescription      This function is responsible for sending verification link
    * @param                 Array $requestData This contains full request data
    * @return                true/false
    */ 
    public function sendVerificationLink($requestData, $userId, $resetType = 'registration'){
       
        // get otp expiry time
        $expiryDatetime = $this->dateTimeLibObj->getPostgresTimestampAfterXmin(Config::get('app.link_expiry_time_in_minuit'));  
        if(!$expiryDatetime){
            return false;
        }
        
        // get six digit random otp
        $linkhash = $this->utilityLibObj->randomNumericInteger();
        
        // Make insert data to store otp in database
        $inserData = array(
            'user_id'               => $userId,  
            'user_ver_object'       => $requestData['user_email'],
            'user_ver_obj_type'     => Config::get('constants.USER_VERI_OBJECT_TYPE_EMAIL'),
            'user_ver_hash_otp'     => $linkhash,
            'user_ver_expiredat'    => $expiryDatetime,           
            'resource_type'         => $requestData['resource_type'],
            'ip_address'            => $requestData['ip_address'],
            'created_by'            => 0, // This record genrated by system it self
            'updated_by'            => 0
        );  
        
        // store hash in database 
        $isHashStored = $this->userVerificationObj->saveLinkHashInDatabase($inserData);
        if($isHashStored){
            if($resetType == 'resetPassword'){
                $encryptEmailID = $this->securityLibObj->encrypt($requestData['user_email']);
                $encryptedLinkHash = $this->securityLibObj->encrypt($linkhash);
                // Send Email to user
                $emailConfig = [
                    'viewData'      => [
                                        'user' => $requestData,
                                        'reset_url' => url('/forgot-password-verification/'.$encryptEmailID.'/'.$encryptedLinkHash),
                                        'info_email' => Config::get('constants.INFO_EMAIL')
                                    ],
                    'emailTemplate' => 'emails.forgotpassword',
                    'subject'       => trans('frontend.site_title').' | '.trans('emailmessage.subject_reset_password')
                ];
            } else if($resetType == 'patientPassword'){
                $encryptEmailID = $this->securityLibObj->encrypt($requestData['user_email']);
                $encryptedLinkHash = $this->securityLibObj->encrypt($linkhash);
                // Send Email to user
                $emailConfig = [
                    'viewData'      => [
                                        'user' => $requestData,
                                        'generate_password_url' => url('/generate-password/'.$encryptEmailID.'/'.$encryptedLinkHash),
                                        'info_email' => Config::get('constants.INFO_EMAIL')
                                    ],
                    'emailTemplate' => 'emails.patientpassword',
                    'subject'       => trans('frontend.site_title').' | '.trans('emailmessage.subject_new_password')
                ];
            } else {
                // Create verification link
                $encryptedUserId = $this->securityLibObj->encrypt($userId);
                $encryptedLinkHash = $this->securityLibObj->encrypt($linkhash);
                $verification_link = url('/')."/verify/".$encryptedUserId."/".$encryptedLinkHash;

                // Prepare email config
                $userPrefix = isset($requestData['user_type']) && $requestData['user_type'] == Config::get('constants.USER_TYPE_PATIENT') ? '' : 'Dr. ';
                $emailConfig = [
                    'viewData' => [
                            'name'              => $userPrefix.$requestData['user_firstname'].' '.$requestData['user_lastname'],
                            'verification_link' => $verification_link,
                            'app_name'          => Config::get('constants.APP_NAME'),
                            'app_url'           => Config::get('constants.APP_URL'),
                            'support_email'     => Config::get('constants.SUPPORT_EMAIL'),
                            'unsubscribe_email' => Config::get('constants.UNSUBSCRIBE_EMAIL'),
                        ],
                    'emailTemplate' => 'emails.doctorregistration',
                    'subject' => 'Please verify your SafeHealth account'
                ];
            }

            // Send verification mail          
            Mail::to($requestData['user_email'])
                ->send(new EmailLib($emailConfig));
                
            if (count(Mail::failures()) > 0) {
                return false;
            }
            return true;
        }else{
            return false;
        }
    }


    /**
    * @DateOfCreation        14 May 2018
    * @ShortDescription      This function is responsible for validating Doctor OTP
    * @param                 Array $requestData This contains full request data
    * @return                Error Array
    */ 
    protected function isDoctorOTPValid($requestData){
        $errorMsg = "";
        $otpDetail = $this->userVerificationObj->getVerificationDetailByMob($requestData['user_mobile']);      
        
        // Check otp
        if($requestData['user_otp'] != $otpDetail->user_ver_hash_otp){
            $errorMsg = trans('Auth::messages.doctor_wrong_otp');
        }else if($this->dateTimeLibObj->isTimePassed($otpDetail->user_ver_expiredat)){
            $errorMsg = trans('Auth::messages.doctor_otp_expired');
        }
         
        return $errorMsg;
    }

  
    /**
    * @DateOfCreation        11 May 2018
    * @ShortDescription      This function is responsible for validating Doctor data
    * @param                 Array $data This contains full request data
    * @param                 Array $extra extra validation rules 
    * @return                Error Array
    */ 
    protected function doctorRegistrationValidator(array $data, $extra = [])
    {
        $error = false;
        $errors = [];
        $rules =  [
            'user_firstname' => 'required|string|max:150|min:3',
            'user_lastname' => 'required|string|max:150|min:3',
            'user_country_code' => 'required',
            'user_gender'   => 'required',
            'user_mobile' => 'required|numeric|regex:/[0-9]{10}/|unique:users',
            'user_adhaar_number'=> 'required|numeric|regex:/[0-9]{12}/|unique:users',
            'user_email' => 'required',
            'user_password' => 'required|min:6|regex:/^.*(?=.{3,})(?=.*[a-zA-Z])(?=.*[0-9])(?=.*[\d\X])(?=.*[!@$#%]).*$/',
            'resource_type' => 'required|integer'
        ];
        $rules = array_merge($rules,$extra);
        
        $validator = Validator::make($data, $rules);        
        if($validator->fails()){
            $error = true;
            $errors = $validator->errors();
        }
        return ["error" => $error,"errors" => $errors]; 
    }
    
    
    /**
    * @DateOfCreation        23 May 2018
    * @ShortDescription      This function is responsible for sending otp
    * @param                 Array $data This contains full request data
    * @return                Error Array
    */ 
    protected function sendOtpToVerifyMobile($requestData){
        // get otp expiry time
        $expiryDatetime = $this->dateTimeLibObj->getPostgresTimestampAfterXmin(Config::get('app.otp_expiry_time_in_minuit'));  
        if(!$expiryDatetime){
            return false;
        }
        // get six digit random otp
        //$otp = $this->utilityLibObj->randomNumericInteger();
        // By pass otp as currently we have not message gateway
        $otp = 123456;
        
        // Make insert data to store otp in database
        $inserData = array(
            'user_id'               => 0, // we are not registring user without otp verification 
            'user_ver_object'       => $requestData['user_mobile'],
            'user_ver_obj_type'     => Config::get('constants.USER_VERI_OBJECT_TYPE_MOBILE'),
            'user_ver_hash_otp'     => $otp,
            'user_ver_expiredat'    => $expiryDatetime,           
            'resource_type'         => $requestData['resource_type'],
            'ip_address'            => $requestData['ip_address'],
            'created_by'            => 0, // This record genrated by system it self
            'updated_by'            => 0
        );  
        
        // store otp in database 
        $isOTPStored = $this->userVerificationObj->saveOTPInDatabase($inserData);
        
        return $isOTPStored;
    }


    public function createUsersTemp(){
        $dateTimeObj = new DateTimeLib();
        $city_id               = "2353";
        $state_id              = "20";
        for ($i=724; $i <=5000 ; $i++) {

            if($i > 500 && $i < 800){
                $city_id               = "2313";
                $state_id              = "20";
            }
            if($i > 800 && $i < 1500){
                $city_id               = "2707";
                $state_id              = "21";
            }
            if($i > 1500 && $i < 2000){
                $city_id               = "2763";
                $state_id              = "21";
            }
            if($i > 2000 && $i < 3500){
                $city_id               = "1041";
                $state_id              = "12";
            } 
            if($i > 3500){
                $city_id               = "3438";
                $state_id              = "29";
            } 
            $insertData = array(
                'user_firstname'        => "shailendra_".$i,
                'user_lastname'         => "rathore_".$i,
                'user_mobile'           => "9755_".$i,
                'user_adhaar_number'    => "123456789540",
                'user_country_code'     => "91",
                'user_gender'           => "1",
                'user_email'            => "fxbytesratthore".$i."@gmail.com",
                'user_status'           => "2",
                'user_password'         => Hash::make("fxbytes@123"),
                'user_type'             => "2",
                'resource_type'         => "1",
                'ip_address'            => "1:1:1",
                'is_deleted'            => "2"
            ); 
            
            $clinicData = array(
                'user_firstname'           => "shailendra_".$i,
                'user_lastname'            => "shailendra_".$i,
                'clinic_phone'             => "9755_".$i,
                'clinic_address_line1'     => "addre_".$i,
                'is_deleted'               => "2",
                'ip_address'               => "1:1:1",
                "resource_type"            => "1"
            ); 


            if($i % 5 == 0){
                $spl_id = 1; 
            }else{
                $spl_id = $i % 5;
            }

            if($i % 7 == 0){
                $weekday = 1; 
            }else{
                $weekday = $i % 7;
            }
            $clinicData['created_by'] = $insertData['created_by'] = $insertData['created_by'] = '8';
            $clinicData['updated_by'] = $doctorData['updated_by'] = $insertData['updated_by'] = '8';
            $clinicData['created_at'] = $doctorData['created_at'] = $insertData['created_at'] = $dateTimeObj->getPostgresTimestampAfterXmin(0);
            $clinicData['updated_at'] = $doctorData['updated_at'] = $insertData['updated_at'] = $dateTimeObj->getPostgresTimestampAfterXmin(0);

            $createdUserId = $this->authModelObj->createUser($insertData);
            $slug = str_slug("shailendra_".$i.' '."rathore_".$i).$this->utilityLibObj->alphabeticString(6);
            $doctorData = array(
                'doc_short_info'        => "info_".$i,
                'doc_pincode'           => "111".$i,
                'doc_address_line1'     => "addre_".$i,
                'doc_slug'              => $slug,
                'city_id'               => $city_id,
                'state_id'              => $state_id,
                'is_deleted'            => "2",
                'resource_type'         => "1",
                'user_id'               => $createdUserId,
                'ip_address'            => "1:1:1",
                'doc_profile_img'       => ' ',

            ); 
            $this->authModelObj->createDoctor($doctorData);
            $this->clinicsModelObj->createClinic($clinicData,$createdUserId);
            $clinic_id = DB::getPdo()->lastInsertId();
            $specailityData = array(
                'user_id'       => $createdUserId,
                'spl_id'        => $spl_id,
                'user_type'     => "2",
                'resource_type' => "1",
                'ip_address'    => "1:1:1"

            );

             $awardData = array(
                'user_id'       => $createdUserId,
                'doc_award_name'=> "ayush_".$i,
                "doc_award_year" => "1975",
                'user_type'     => "2",
                'resource_type' => "1",
                'ip_address'    => "1:1:1"

            );
            $timingData = array(
                'user_id'           => $createdUserId,
                'week_day'          => $weekday,
                'start_time'        => "1000",
                'end_time'          => "1800",
                'slot_duration'     => "30",
                'patients_per_slot' => "4",
                'clinic_id'         =>  $clinic_id,
                'is_deleted'        => "2",
                'resource_type'     => "1",
                'user_id'           => $createdUserId,
                'ip_address'        => "1:1:1"

            );
            $this->timingObj->createTimingDemo($timingData,$createdUserId);
            $this->authModelObj->createSpecaility($specailityData);
            $this->authModelObj->createAward($awardData);
        }
    }

    /**
     * @DateOfCreation        22 May 2018
     * @ShortDescription      This function is responsible to get the image path
     * @param                 String $imageName
     * @return                response
     */
    public function getLogo(Request $request)
    {
        $requestData = $this->getRequestData($request);
        $imagePath = env('SAFE_HEALTH_APP_URL').'app/public/images/Rxlogo.png';
        $file = File::get($imagePath);
        $type = File::mimeType($imagePath);
        $response = Response::make($file, 200);
        $response->header("Content-Type", $type);
        return $response;
    }
}
