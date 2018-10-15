<?php

namespace App\Modules\Patients\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Auth;
use Session;
use App\Traits\SessionTrait;
use App\Traits\RestApi;
use App\Traits\FxFormHandler;
use Config;
use Illuminate\Support\Facades\Validator;
use App\Libraries\SecurityLib;
use App\Libraries\ExceptionLib;
use App\Libraries\DateTimeLib;
use App\Modules\Patients\Models\Patients;
use App\Modules\Region\Models\Country;
use App\Modules\Auth\Models\Auth as Users;
use App\Modules\Referral\Models\Referral as Referral;
use App\Modules\PatientGroups\Models\PatientGroups as PatientGroups;
use DB;
use App\Libraries\FileLib;
use App\Libraries\UtilityLib;
use File;
use Carbon\Carbon;
use App\Modules\Auth\Controllers\AuthController;

/**
 * PatientsController
 *
 * @package                ILD INDIA
 * @subpackage             PatientsController
 * @category               Controller
 * @DateOfCreation         13 june 2018
 * @ShortDescription       This controller to handle all the operation related to 
                           Patients profile
 */
class PatientsController extends Controller
{

    use SessionTrait, RestApi, FxFormHandler;

    // @var Array $http_codes
    // This protected member contains Http Status Codes
    protected $http_codes = [];

    // Store Post Method
    protected $method = '';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(Request $request, AuthController $authController)
    {
        $this->http_codes = $this->http_status_codes();

        // Init security library object
        $this->securityLibObj = new SecurityLib();

        // Init Patient model object
        $this->patientModelObj = new Patients(); 

        // Init User model object
        $this->userModelObj = new Users(); 

        // Init DateTime library object
        $this->dateTimeLibObj = new DateTimeLib();        

        // Init File Library object
        $this->FileLib = new FileLib();

        // Init Utility Library object
        $this->UtilityLib = new UtilityLib();

        $this->method = $request->method();

        // Init exception library object
        $this->exceptionLibObj = new ExceptionLib();        

        // Init country model object
        $this->countrytModelObj = new Country(); 

        // Init referral model object
        $this->referralModelObj = new Referral();

        // Init patient groups model object
        $this->patientGroupsModelObj = new PatientGroups();

        //init auth controller object
        $this->authControllerObject = $authController;
    }

    /**
    * @DateOfCreation        13 June 2018
    * @ShortDescription      Get a validator for an incoming Patients request
    * @param                 \Illuminate\Http\Request  $request
    * @return                \Illuminate\Contracts\Validation\Validator
    */
    protected function patientsValidations(array $requestData, $extra = [], $type = 'insert'){
        $errors         = [];
        $error          = false;
        $rules = [];

        // Check the required validation rule
        switch($this->method)
        {
            case 'POST':
            {
                $rules = [
                            'user_email'            => 'required|string|email|max:150|unique:users',
                            'user_firstname'        => 'required|string|max:100',
                            'user_lastname'         => 'required|string|max:100',
                            'user_mobile'           => 'required|numeric|regex:/[0-9]{10}/||unique:users',
                            'user_gender'           => 'required|numeric',
                        ];
            }
            case 'PUT':
            {
                $rules = [
                            'user_email'            => 'required|string|email|max:150|unique:users,user_email,'.$requestData['user_id'].',user_id',
                            'user_firstname'        => 'required|string|max:100',
                            'user_lastname'         => 'required|string|max:100',
                            'user_mobile'           => 'required|numeric|regex:/[0-9]{10}/||unique:users,user_mobile,'.$requestData['user_id'].',user_id',
                            'user_gender'           => 'required|numeric',
                        ];
            }
            default:break;
        }

        $rules = array_merge($rules, $extra);
        
        $validationMessageData = [
            'user_gender.numeric'   => trans('Patients::messages.patient_validation_gender'),
            'user_gender.numeric'   => trans('Patients::messages.patient_validation_title'),
            'pat_pincode.numeric'   => trans('Patients::messages.patient_validation_pat_pincode'),
        ];

        $validator = Validator::make($requestData, $rules, $validationMessageData);        
        if($validator->fails()){
            $error = true;
            $errors = $validator->errors();
        }
        return ["error" => $error,"errors" => $errors]; 
    }

    /**
     * @DateOfCreation        13 june 2018
     * @ShortDescription      This function is responsible for insert Patient Data 
     * @param                 Array $request   
     * @return                Array of status and message
     */
    public function store(Request $request)
    { 
        $requestData = $request->only('city_id','pat_other_city','doc_ref_id','doc_ref_name','pat_group_id','pat_group_name');
        $requestData['user_id'] = $request->user()->user_id;


        try{
            DB::beginTransaction();

            $doc_ref_id = NULL;
            if(!empty($requestData['doc_ref_id']) && $requestData['doc_ref_id'] != 'undefined') {
                $doc_ref_id = $this->securityLibObj->decrypt($requestData['doc_ref_id']); 
            }else if(!empty($requestData['doc_ref_name'])){ 
                $referralResult = $this->referralModelObj->getReferralIdByName($requestData['doc_ref_name']);
                if(!empty($referralResult)){
                   $doc_ref_id = $referralResult->doc_ref_id;
                }else{
                    $referal = $this->referralModelObj->createReferral($requestData);
                    if(!empty($referal->doc_ref_id)){
                        $doc_ref_id = $this->securityLibObj->decrypt($referal->doc_ref_id);
                    }else{
                        $doc_ref_id = NULL;
                    }
                }
            }

            $pat_group_id = NULL;
            if(!empty($requestData['pat_group_id']) && $requestData['pat_group_id'] != 'undefined') {
                $pat_group_id = $this->securityLibObj->decrypt($requestData['pat_group_id']); 
            }else if($requestData['pat_group_name']){ 
                $patGroupResult = $this->patientGroupsModelObj->getPatientGroupIdByName($requestData['pat_group_name']);
                if(!empty($patGroupResult)){
                   $pat_group_id = $patGroupResult->pat_group_id;
                }else{
                    $patientGroup = $this->patientGroupsModelObj->createPatientGroup($requestData);
                    if(!empty($patientGroup->pat_group_id)){
                        $pat_group_id = $this->securityLibObj->decrypt($patientGroup->pat_group_id);
                    }else{
                        $pat_group_id = NULL;
                    }
                }
            }

            $requestData['city_id']  = $this->securityLibObj->decrypt($requestData['city_id']); 
            
            $posConfig = 
            [
                'users'=>
                [
                    'user_email'=>
                    [   
                        'type'=>'input',
                        'isRequired' =>true,
                        'validation'=>'required|string|email|max:150|unique:users',
                        'decrypt'=>false,
                        'fillable' => true,
                    ],
                     'user_firstname'=>
                    [   
                        'type'=>'input',
                        'isRequired' =>true,
                        'validation'=>'required|string|max:100',
                        'decrypt'=>false,
                        'fillable' => true,
                    ],
                     'user_lastname'=>
                    [   
                        'type'=>'input',
                        'isRequired' =>true,
                        'validation'=>'required|string|max:100',
                        'decrypt'=>false,
                        'fillable' => true,
                    ],
                    'user_mobile'=>
                    [   
                        'type'=>'input',
                        'isRequired' =>true,
                        'validation'=>'required|numeric|regex:/[0-9]{10}/|unique:users',
                        'decrypt'=>false,
                        'fillable' => true,
                    ],
                    'user_adhaar_number'=>
                    [   
                        'type'=>'input',
                        'isRequired' =>false,
                        'decrypt'=>false,
                        'fillable' => true,
                    ],
                    
                    'user_gender'=>
                    [   
                        'type'=>'input',
                        'isRequired' =>true,
                        'validation'=>'required',
                        'decrypt'=>false,
                        'fillable' => true,
                    ],
                    'resource_type'=>
                    [   
                        'type'=>'input',
                        'isRequired' =>true,
                        'decrypt'=>false,
                        'validation'=>'required',
                        'fillable' => true,
                    ],
                    'ip_address'=>
                    [   
                        'type'=>'input',
                        'isRequired' =>true,
                        'decrypt'=>false,
                        'validation'=>'required',
                        'fillable' => true,
                    ]
                ],
                'patients'=>[
                    'pat_title'=>
                    [   
                        'type'=>'input',
                        'isRequired' =>false,
                        'validation'=>'required',
                        'decrypt'=>false,
                        'fillable' => true,
                    ],
                    'pat_address_line1'=>
                    [   
                        'type'=>'input',
                        'isRequired' =>false,
                        'decrypt'=>false,
                        'fillable' => true,
                    ],
                    'city_id'=>
                    [   
                        'type'=>'input',
                        'isRequired' =>false,
                        'decrypt'=>true,
                        'fillable' => true,
                    ],
                    'state_id'=>
                    [   
                        'type'=>'input',
                        'isRequired' =>false,
                        'decrypt'=>true,
                        'fillable' => true,
                    ],
                    'pat_dob'=>
                    [   
                        'type'=>'date',
                        'isRequired' =>false,
                        'decrypt'=>false,
                        'fillable' => true,
                        'currentDateFormat' => 'dd/mm/YY',
                    ],
                    'pat_address_line1'=>
                    [   
                        'type'=>'input',
                        'isRequired' =>false,
                        'decrypt'=>false,
                        'fillable' => true,
                    ], 
                    'pat_address_line2'=>
                    [   
                        'type'=>'input',
                        'isRequired' =>false,
                        'decrypt'=>false,
                        'fillable' => true,
                    ],
                    'pat_locality'=>
                    [   
                        'type'=>'input',
                        'decrypt'=>false,
                        'isRequired' =>false,
                        'fillable' => true,
                    ],
                    'pat_mobile_num'=>
                    [   
                        'type'=>'input',
                        'decrypt'=>false,
                        'isRequired' =>false,
                        'fillable' => true,
                    ],
                    'pat_pincode'=>
                    [   
                        'type'=>'input',
                        'isRequired' =>false,
                        'decrypt'=>false,
                        'fillable' => true,
                    ],
                    'pat_other_city'=>
                    [   
                        'type'=>'input',
                        'isRequired' =>false,
                        'validation' =>'string|max:100',
                        'validationRulesMessege' => [
                        'pat_other_city.string' => trans('Patients::messages.pat_other_city_string'),
                        ],
                        'decrypt'=>false,
                        'fillable' => true,
                    ],
                    'resource_type'=>
                    [   
                        'type'=>'input',
                        'isRequired' =>true,
                        'decrypt'=>false,
                        'validation'=>'required',
                        'fillable' => true,
                    ],
                    'ip_address'=>
                    [   
                        'type'=>'input',
                        'isRequired' =>true,
                        'decrypt'=>false,
                        'validation'=>'required',
                        'fillable' => true,
                    ],
                    'pat_emergency_contact_number'=>
                    [   
                        'type'=>'input',
                        'isRequired' =>false,
                        'decrypt'=>false,
                        'fillable' => true,
                    ]
                ]    
            ];

             if($requestData['city_id'] === '0'){
                $posConfig['patients']['pat_other_city']['isRequired'] = true;
            }else{
                if(isset($posConfig['patients']['pat_other_city']['validation'])) {
                    unset($posConfig['patients']['pat_other_city']['validation']);
                }
               $posConfig['patients']['pat_other_city']['valueOverwrite'] = '';
            }

           $responseValidatorForm = $this->postValidatorForm($posConfig,$request);

           if (!$responseValidatorForm['status']) {
                return $responseValidatorForm['response'];
           }

           if($responseValidatorForm['status']){
                $destination = Config::get('constants.PATIENTS_MEDIA_PATH');
                $storagPath = Config::get('constants.STORAGE_MEDIA_PATH');
                $patientsData = $responseValidatorForm['response']['fillable']['patients'];
                $usersData = $responseValidatorForm['response']['fillable']['users'];
                $patientsData['doc_ref_id'] = $doc_ref_id;
                $patientsData['pat_group_id'] = $pat_group_id;
                
                //file uploade path
                if(!empty($patientsData['state_id'])){
                    $countryDetailes = $this->countrytModelObj->getCountryDetailsByStateId($patientsData['state_id']);
                    $usersData['user_country_code'] = $countryDetailes->country_code;
                }else{
                    $usersData['user_country_code'] = Config::get('constants.INDIA_COUNTRY_CODE');
                }
                $usersData['user_type'] = Config::get('constants.USER_TYPE_PATIENT');

                $patientUserId = $this->patientModelObj->createPatientUser('users',$usersData);
                if($patientUserId){
                    $patientsData['pat_code'] = $this->UtilityLib->patientsCodeGenrator(6);
                    $patientsData['user_id'] = $patientUserId;

                    $patId = $this->patientModelObj->createPatientUser('patients',$patientsData);
                    if($patId){
                        $relationData = [
                            'user_id'=>$request->user()->user_id,
                            'pat_id'=>$patientUserId,
                            'assign_by_doc'=>$request->user()->user_id,
                            'resource_type'=> Config::get('constants.RESOURCE_TYPE_WEB'),
                            'is_deleted'=> Config::get('constants.IS_DELETED_NO'),
                            'ip_address'=> $request->ip()
                        ];
                        $this->patientModelObj->createPatientDoctorRelation('doctor_patient_relation',$relationData);

                        $defaultVisitData = [
                            'user_id'       => Config::get('constants.DEFAULT_USER_VISIT_ID'),
                            'pat_id'        => $patientUserId,
                            'visit_type'    => Config::get('constants.PROFILE_VISIT_TYPE'),
                            'visit_number'  => Config::get('constants.INITIAL_VISIT_NUMBER'),
                            'resource_type' => Config::get('constants.RESOURCE_TYPE_WEB'),
                            'is_deleted'    => Config::get('constants.IS_DELETED_NO'),
                            'status'        => Config::get('constants.VISIT_COMPLETED'),
                            'ip_address'    => $request->ip()
                        ];
                        $this->patientModelObj->createPatientDoctorVisit('patients_visits',$defaultVisitData);

                        $verificationLinkData = [
                            'user_firstname'=> $usersData['user_firstname'],
                            'user_lastname' => $usersData['user_lastname'],
                            'user_email'    => $usersData['user_email'],
                            'resource_type' => Config::get('constants.RESOURCE_TYPE_WEB'),
                            'ip_address'    => $request->ip(),
                            'user_type'     => Config::get('constants.USER_TYPE_PATIENT')
                        ];

                        $this->authControllerObject->sendVerificationLink($verificationLinkData, $patientUserId, $resetType = 'patientPassword');
                        DB::commit();

                        // SEND THANK YOU MESSAGE TO REFFERAL CONTACT NUMBER HERE==========
                        if(!empty($doc_ref_id)){
                            $referralResult = $this->referralModelObj->getReferralById($doc_ref_id);

                            if(!empty($referralResult['doc_ref_mobile'])){
                                $referralContactNumber = $referralResult['doc_ref_mobile'];
                            }
                        }
                        // SEND THANK YOU MESSAGE TO REFFERAL CONTACT NUMBER HERE==========
                        
                        $createdPatientIdEncrypted = $this->securityLibObj->encrypt($patientUserId);
                        return $this->resultResponse(
                            Config::get('restresponsecode.SUCCESS'), 
                            ['user_id' => $createdPatientIdEncrypted], 
                            [],
                            trans('Patients::messages.patients_add_successfull'), 
                            $this->http_codes['HTTP_OK']
                        );
                    }else{
                        DB::rollback();
                        return $this->resultResponse(
                            Config::get('restresponsecode.ERROR'), 
                            [], 
                            [],
                            trans('Patients::messages.patients_add_fail'), 
                            $this->http_codes['HTTP_OK']
                        );
                    }
                }else{
                    DB::rollback();

                    //user pat_consent_file unlink
                    if(!empty($pdfPath) && file_exists($pdfPath)){
                        unlink($pdfPath);
                    }
                    return $this->resultResponse(
                        Config::get('restresponsecode.ERROR'), 
                        [], 
                        [],
                        trans('Patients::messages.patients_add_fail'), 
                        $this->http_codes['HTTP_OK']
                    );
                }           
            }
        } catch (\Exception $ex) {
            //user pat_consent_file unlink
            
            if(!empty($pdfPath) && file_exists($pdfPath)){
                unlink($pdfPath);
            }
            $eMessage = $this->exceptionLibObj->reFormAndLogException($ex,'PatientsController', 'store');
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
     * @DateOfCreation        15 June 2018
     * @ShortDescription      This function is responsible for get Patient Data by id
     * @param                 Array $request   
     * @return                Array of status and message
     */
    public function edit($id)
    { 
       $patientID = $this->securityLibObj->decrypt($id); 

        $patientProfileData = $this->patientModelObj->getPatientProfileData($patientID);
        return $this->resultResponse(
                Config::get('restresponsecode.SUCCESS'), 
                $patientProfileData, 
                [],
                trans('Patients::messages.patient_profile_data'),
                $this->http_codes['HTTP_OK']
            );    
    }

    /**
     * @DateOfCreation        15 June 2018
     * @ShortDescription      This function is responsible for update Patient Data 
     * @param                 Array $request   
     * @return                Array of status and message
     */
    public function update(Request $request)
    {
        $requestData = $this->getRequestData($request);
        $extra = [];
        $requestData['city_id']             = $this->securityLibObj->decrypt($requestData['city_id']);
        $requestData['state_id']            = $this->securityLibObj->decrypt($requestData['state_id']); 
        $requestData['pat_dob']             = isset($requestData['pat_dob']) && !empty($requestData['pat_dob']) ? $this->dateTimeLibObj->covertUserDateToServerType($requestData['pat_dob'],'dd/mm/YY','Y-m-d')['result'] : NULL;
        $requestData['user_country_code']   = $this->securityLibObj->decrypt($requestData['user_country_code']); 
        $requestData['resource_type']       = Config::get('constants.RESOURCE_TYPE_WEB');   
        $requestData['is_deleted']          = Config::get('constants.IS_DELETED_NO');   
        $requestData['user_id']             = $this->securityLibObj->decrypt($requestData['user_id']); 
        $requestData['pat_marital_status']  = count($requestData['pat_marital_status']) > 0 ? $requestData['pat_marital_status'][0] : null; 
        
        if(isset($requestData['pat_number_of_children'])){
            $pat_number_of_children = $requestData['pat_number_of_children'];
        }else{
            $pat_number_of_children = 0;
        }
        
        $requestData['pat_number_of_children']  = $requestData['pat_marital_status'] == Config::get('dataconstants.MARITAL_STATUS_MARRIED') ? $pat_number_of_children : null;
        
        if($requestData['city_id'] === '0'){
            $extra['pat_other_city'] =  'string|max:100';
        }else{
           $requestData['pat_other_city']  = '';
        }

        $validate = $this->patientsValidations($requestData, $extra, 'update');
        if($validate["error"]){
            return $this->resultResponse(
                Config::get('restresponsecode.ERROR'), 
                [], 
                $validate['errors'],
                trans('Patients::messages.patients_add_validation_failed'), 
                $this->http_codes['HTTP_OK']
            ); 
        }

        // DOCTOR REFERANCE
        $doc_ref_id = NULL;
        if(!empty($requestData['doc_ref_id']) && $requestData['doc_ref_id'] != 'undefined') {
            $doc_ref_id = $this->securityLibObj->decrypt($requestData['doc_ref_id']); 
        }else if(!empty($requestData['doc_ref_name'])){ 
            $referralResult = $this->referralModelObj->getReferralIdByName($requestData['doc_ref_name']);
            if(!empty($referralResult)){
               $doc_ref_id = $referralResult->doc_ref_id;
            }else{
                $refferal_data = [
                                    "doc_ref_name"  => $requestData['doc_ref_name'],
                                    "user_id"       => $requestData['user_id'],
                                    "ip_address"    => $requestData['ip_address'],
                                    "resource_type" => $requestData['resource_type'],
                                    "is_deleted"    => $requestData['is_deleted'],
                                ];
                $referal = $this->referralModelObj->createReferral($refferal_data);
                if(!empty($referal->doc_ref_id)){
                    $doc_ref_id = $this->securityLibObj->decrypt($referal->doc_ref_id);
                }else{
                    $doc_ref_id = NULL;
                }
            }
        }

        // PATIENT GROUP
        $pat_group_id = NULL;
        if(!empty($requestData['pat_group_id']) && $requestData['pat_group_id'] != 'undefined') {
            $pat_group_id = $this->securityLibObj->decrypt($requestData['pat_group_id']); 
        }else if($requestData['pat_group_name']){ 
            $patGroupResult = $this->patientGroupsModelObj->getPatientGroupIdByName($requestData['pat_group_name']);
            if(!empty($patGroupResult)){
               $pat_group_id = $patGroupResult->pat_group_id;
            }else{
                $group_data = [
                                    "pat_group_name"  => $requestData['pat_group_name'],
                                    "user_id"       => $requestData['user_id'],
                                    "ip_address"    => $requestData['ip_address'],
                                    "resource_type" => $requestData['resource_type'],
                                    "is_deleted"    => $requestData['is_deleted'],
                                ];
                $patientGroup = $this->patientGroupsModelObj->createPatientGroup($group_data);
                if(!empty($patientGroup->pat_group_id)){
                    $pat_group_id = $this->securityLibObj->decrypt($patientGroup->pat_group_id);
                }else{
                    $pat_group_id = NULL;
                }
            }
        }

        try{
            $pat_id  = $this->securityLibObj->decrypt(($request->user()->user_type == Config::get('constants.USER_TYPE_PATIENT')) ? $request->user()->user_id : $requestData['pat_id']); 
            $user_id = $requestData['user_id']; 

            $whereData = ['user_id' => $user_id];

            if(!empty($requestData['state_id'])){
                    $countryDetailes = $this->countrytModelObj->getCountryDetailsByStateId($requestData['state_id']);
                    $requestData['user_country_code'] = $countryDetailes->country_code;
            }else{
                    $requestData['user_country_code'] = Config::get('constants.INDIA_COUNTRY_CODE');
                }
            $userData = ['user_email'       => $requestData['user_email'], 
                        'user_mobile'       => $requestData['user_mobile'],
                        'user_firstname'    => $requestData['user_firstname'],
                        'user_lastname'     => $requestData['user_lastname'], 
                        'user_country_code' => $requestData['user_country_code'], 
                        'user_gender'       => $requestData['user_gender']
                        ];
            if(empty($requestData['state_id'])){
                unset($requestData['state_id']);
            }

            if(empty($requestData['city_id'])){
                unset($requestData['city_id']);
            }
            unset($requestData['user_email']);
            unset($requestData['user_mobile']);
            unset($requestData['user_country_code']);
            unset($requestData['user_gender']);

            $requestData['doc_ref_id']   = $doc_ref_id;
            $requestData['pat_group_id'] = $pat_group_id;
            $updatePatientData  = $this->patientModelObj->updatePatientData($requestData, $whereData);
            $updateUserData     = $this->userModelObj->userDataUpdate($userData, $whereData);
            
            // validate, is query executed successfully 
            if($updatePatientData){
                $updatePatientDataDetails = $this->patientModelObj->getPatientProfileData($user_id);
                return $this->resultResponse(
                    Config::get('restresponsecode.SUCCESS'), 
                    $updatePatientDataDetails, 
                    [],
                    trans('Patients::messages.patients_updated_successfull'), 
                    $this->http_codes['HTTP_OK']
                );
                           
            }else{
                DB::rollback();
                //user image unlink
                if(!empty($imagePath) && file_exists($imagePath)){
                    unlink($imagePath);
                }
                return $this->resultResponse(
                    Config::get('restresponsecode.ERROR'), 
                    [], 
                    [],
                    trans('Patients::messages.patients_update_fail'), 
                    $this->http_codes['HTTP_OK']
                );
            }           
        } catch (\Exception $ex) {
            // echo $ex;
            DB::rollback();
            $eMessage = $this->exceptionLibObj->reFormAndLogException($ex,'PatientsController', 'update');
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
     * @DateOfCreation        15 June 2018
     * @ShortDescription      This function is responsible for get Patient list 
     * @param                 Array $request   
     * @return                Array of status and message
     */
    public function getPatientList(Request $request)
    {
        $requestData = $this->getRequestData($request);
        $requestData['user_id'] = (in_array($request->user()->user_type, Config::get('constants.USER_TYPE_STAFF'))) ? $request->user()->created_by : $request->user()->user_id;
        $getPatientList = $this->patientModelObj->getPatientList($requestData);

        return $this->resultResponse(
                Config::get('restresponsecode.SUCCESS'), 
                $getPatientList, 
                [],
                trans('Patients::messages.patient_list_data'),
                $this->http_codes['HTTP_OK']
            );    
    }

    /**
     * @DateOfCreation        21 June 2018
     * @ShortDescription      This function is responsible for get Patient visit id 
     * @param                 Array $request   
     * @return                Array of status and message
     */
    public function getPatientVisitId(Request $request) 
    {
        $requestData = $this->getRequestData($request);
        $requestData['user_id'] = (in_array($request->user()->user_type, Config::get('constants.USER_TYPE_STAFF'))) ? $request->user()->created_by : $request->user()->user_id;
        $getPatientVisitId = $this->patientModelObj->getPatientVisitId($requestData);

        return $this->resultResponse(
                Config::get('restresponsecode.SUCCESS'), 
                $getPatientVisitId, 
                [],
                trans('Patients::messages.patient_visit_id_fetced_success'),
                $this->http_codes['HTTP_OK']
            );    
    }

    /**
     * @DateOfCreation        21 June 2018
     * @ShortDescription      This function is responsible for get Patient visit id 
     * @param                 Array $request   
     * @return                Array of status and message
     */
    public function createPatientFollowUpVisitId(Request $request) 
    {
        $requestData = $this->getRequestData($request);
        $requestData['user_id'] = (in_array($request->user()->user_type, Config::get('constants.USER_TYPE_STAFF'))) ? $request->user()->created_by : $request->user()->user_id;
        $getPatientVisitId      = $this->patientModelObj->getPatientFollowUpVisitId($requestData);
       
        return $this->resultResponse(
                Config::get('restresponsecode.SUCCESS'), 
                $getPatientVisitId, 
                [],
                trans('Patients::messages.patient_visit_id_fetced_success'),
                $this->http_codes['HTTP_OK']
            );    
    }

    /**
     * @DateOfCreation        3 Sept 2018
     * @ShortDescription      This function is responsible for get Patient's activity history record
     * @param                 Array $request   
     * @return                Array of status and message
     */
    public function getPatientActivityHistory(Request $request) 
    {
        $requestData            = $this->getRequestData($request);
        $requestData['user_id'] = (in_array($request->user()->user_type, Config::get('constants.USER_TYPE_STAFF'))) ? $request->user()->created_by : $request->user()->user_id;
        $requestData['pat_id']  = $this->securityLibObj->decrypt($requestData['pat_id']);

        $getPatientActivityHistoryRecord = $this->patientModelObj->getPatientActivityHistory($requestData);

        return $this->resultResponse(
                Config::get('restresponsecode.SUCCESS'), 
                $getPatientActivityHistoryRecord, 
                [],
                trans('Patients::messages.patient_activity_history_fetced_success'),
                $this->http_codes['HTTP_OK']
            );    
    }
}
