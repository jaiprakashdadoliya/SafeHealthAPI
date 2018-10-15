<?php

namespace App\Modules\DoctorProfile\Controllers;
use App\Modules\DoctorProfile\Models\DoctorProfile as Doctors;
use App\Modules\DoctorProfile\Models\DoctorExperience;
use App\Modules\DoctorProfile\Models\DoctorAward;
use App\Modules\DoctorProfile\Models\DoctorDegree;
use App\Modules\DoctorProfile\Models\DoctorMedia;
use App\Modules\DoctorProfile\Models\DoctorMembership;
use App\Modules\DoctorProfile\Models\DoctorSpecialisations;

use App\Modules\DoctorProfile\Models\States as States;
use App\Modules\DoctorProfile\Models\Cities as Cities;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Traits\RestApi;
use App\Libraries\SecurityLib;
use App\Libraries\ExceptionLib;
use File;
use Response;
use Config;
/**
 * DoctorProfileController
 *
 * @package                Safe health
 * @subpackage             DoctorProfileController
 * @category               Controller
 * @DateOfCreation         21 may 2018
 * @ShortDescription       This controller to get all the info related to the doctor and 
                           also need to check the authentication 
 **/
class DoctorProfileController extends Controller
{

    use RestApi;

    // @var Array $http_codes
    // This protected member contains Http Status Codes
    protected $http_codes = [];

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->http_codes = $this->http_status_codes();

        // Init security library object
        $this->securityLibObj = new SecurityLib(); 
        $this->profileModelObj = new Doctors();
          $this->statesModelObj = new States();
        $this->cityModelObj = new Cities();
        // Init exception library object
        $this->exceptionLibObj = new ExceptionLib();
    }

    /**
    * This function is responsible for validating membership data
    * 
    * @param  Array $data This contains full member input data 
    * @return Array $error status of error
    */ 
    private function DoctorProfileValidator(array $requestData)
    {
        $error  = false;
        $errors = [];
        $validationData  = [  
            'user_firstname' => 'required', 
            'user_lastname' => 'required', 
            'user_mobile' => 'required'
        ];
        $validator  = Validator::make(
            $requestData,
            $validationData
        );
        if($validator->fails()){
            $error = true;
            $errors = $validator->errors();
        }
        return ["error" => $error,"errors" => $errors];
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateProfile(Request $request)
    {
        $requestData = $this->getRequestData($request);
        // Validate request
        $validate = $this->DoctorProfileValidator($requestData);
        if($validate["error"])
        {
            return $this->resultResponse(
                Config::get('restresponsecode.ERROR'), 
                [], 
                $validate['errors'],
                trans('DoctorProfile::messages.profile_update_fail'), 
                $this->http_codes['HTTP_OK']
            ); 
        }
        try {    
            DB::beginTransaction();
            $doctorProfile = $this->profileModelObj->updateProfile($requestData);
            // validate, is query executed successfully 
            if($doctorProfile)
            {
                DB::commit();
                return  $this->resultResponse(
                            Config::get('restresponsecode.SUCCESS'), 
                            $doctorProfile,  
                            [],
                             trans('DoctorProfile::messages.profile_update_success'), 
                            $this->http_codes['HTTP_OK']
                        );

            }else{
                DB::rollback();
                return  $this->resultResponse(
                            Config::get('restresponsecode.ERROR'), 
                            [], 
                            [],
                            trans('DoctorProfile::messages.profile_fatch_fail'), 
                            $this->http_codes['HTTP_OK']
                        );
            }
        } catch (\Exception $ex) {
            DB::rollback();
            $eMessage = $this->exceptionLibObj->reFormAndLogException($ex,'DoctorProfileController', 'updateProfile');            
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
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateImage(Request $request)
    {
        $requestData = $this->getRequestData($request);
        $user_id = $request->user()->user_id;
        $uploadedImage = $this->profileModelObj->updateProfileImage($requestData,$user_id);
        
        // validate, is query executed successfully
        if($uploadedImage){
            return $this->resultResponse(
                Config::get('restresponsecode.SUCCESS'),
                $uploadedImage,
                [],
                trans('DoctorProfile::messages.profile_image_success'),
                $this->http_codes['HTTP_OK']
            );
        }else{
            return $this->resultResponse(
                Config::get('restresponsecode.ERROR'),
                [],
                [],
                trans('DoctorProfile::messages.profile_image_error'),
                $this->http_codes['HTTP_OK']
            );
        }
    }

    /**
     * @DateOfCreation        22 May 2018
     * @ShortDescription      This function is responsible to get the image path
     * @param                 String $imageName
     * @return                response
     */
    public function getProfileImage($imageName, Request $request)
    {
        $requestData = $this->getRequestData($request);
        $imageName = $this->securityLibObj->decrypt($imageName);
        $imagePath =  'app/public/'.Config::get('constants.DOCTOR_MEDIA_PATH');
        $path = storage_path($imagePath) . $imageName;
        if(!File::exists($path)){
            $path = public_path(Config::get('constants.DEFAULT_IMAGE_PATH'));
        }
        $file = File::get($path);
        $type = File::mimeType($path);
        $response = Response::make($file, 200);
        $response->header("Content-Type", $type);
        return $response;
    }

     /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request memberlist of doctor
     * @return \Illuminate\Http\Response
     */
    public function getProfileDetail(Request $request)
    {
        $profileDetail = [];
        $user_id = $request->user()->user_id;
        $profileDetail     =  $this->profileModelObj->getProfileDetail($user_id);
        // validate, is query executed successfully 
        if($profileDetail)
        {
            return  $this->resultResponse(
                Config::get('restresponsecode.SUCCESS'), 
                $profileDetail,  
                '', 
                trans('DoctorProfile::messages.profile_fetch_success'), 
                $this->http_codes['HTTP_OK']
            );
        }else{
            return  $this->resultResponse(
                Config::get('restresponsecode.ERROR'), 
                [], 
                [],
                trans('DoctorProfile::messages.profile_fatch_fail'), 
                $this->http_codes['HTTP_OK']
            );
        }
    }

    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request memberlist of doctor
     * @return \Illuminate\Http\Response
     */
    public function states()
    {
        $states =  $this->statesModelObj->getAllStates();

        // validate, is query executed successfully 
        if($states)
        {
            return  $this->resultResponse(
                Config::get('restresponsecode.SUCCESS'), 
                $states,  
                [],
                '', 
                $this->http_codes['HTTP_OK']
            );
        }else{
            return  $this->resultResponse(
                Config::get('restresponsecode.ERROR'), 
                [], 
                trans('DoctorProfile::messages.state_not_found'), 
                [],
                $this->http_codes['HTTP_OK']
            );
        }
    }

    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request memberlist of doctor
     * @return \Illuminate\Http\Response
     */
    public function cities(Request $request)
    {
        $requestData = $this->getRequestData($request);
        $cities =  $this->cityModelObj->getCityByState($requestData);
        // validate, is query executed successfully 
        if($cities)
        {
            return  $this->resultResponse(
                Config::get('restresponsecode.SUCCESS'), 
                $cities,  
                [],
                '', 
                $this->http_codes['HTTP_OK']
            );
        }else{
            return  $this->resultResponse(
                Config::get('restresponsecode.ERROR'), 
                [], 
                trans('DoctorProfile::messages.city_not_found'), 
                [],
                $this->http_codes['HTTP_OK']
            );
        }
    }
    /**
    * @DateOfCreation        11 May 2018
    * @ShortDescription      This function is responsible for update password 
    * @param                 Array $request   
    * @return                Array of status and message
    */
    public function passwordUpdate(Request $request)
    {
        $requestData = $this->getRequestData($request);
        $validate = $this->_passwordValidator($requestData);
        if($validate["error"])
        {
            return $this->resultResponse(
                Config::get('restresponsecode.ERROR'), 
                [], 
                $validate['errors'],
                trans('DoctorProfile::messages.password_updation_failed'), 
                $this->http_codes['HTTP_OK']
            ); 
        }
        $existingPassword = $request->user()->user_password;
        $isPasswordNotExist = $this->profileModelObj->isPasswordExist($requestData,$existingPassword);

        if($isPasswordNotExist){
             return $this->resultResponse(
                Config::get('restresponsecode.ERROR'), 
                [], 
                ["user_password" => [trans('DoctorProfile::messages.password_not_exist')]],
                trans('DoctorProfile::messages.password_not_exist'), 
                $this->http_codes['HTTP_NOT_FOUND']
            );
        }   
        $userId = $request->user()->user_id;
        $isUpdated = $this->profileModelObj->passwordUpdate($requestData,$userId); 
         if(!empty($isUpdated)){
             return $this->resultResponse(
                Config::get('restresponsecode.SUCCESS'), 
                [], 
                [],
                trans('DoctorProfile::messages.password_updation_successfull'), 
                $this->http_codes['HTTP_OK']
              );
        }else{
             return $this->resultResponse(
                Config::get('restresponsecode.ERROR'), 
                [], 
                [],
                trans('DoctorProfile::messages.password_updation_failed'), 
                $this->http_codes['HTTP_OK']
              );
        }
        
    }

    /**
    * This function is responsible for validating password data
    * 
    * @param  Array $data This contains password input data 
    * @return Array $error status of error
    */ 
    private function _passwordValidator(array $requestData)
    {
        $error = false;
        $errors = [];
        $validationData = [];
        $validationData = [
            'user_password' => 'required|min:6|regex:/^.*(?=.{3,})(?=.*[a-zA-Z])(?=.*[0-9])(?=.*[\d\X])(?=.*[!@$#%]).*$/'
         ];
       
        $validator  = Validator::make(
            $requestData,
            $validationData
        );
        if($validator->fails()){
            $error = true;
            $errors = $validator->errors();
        }
        return ["error" => $error,"errors" => $errors];
    }
}
