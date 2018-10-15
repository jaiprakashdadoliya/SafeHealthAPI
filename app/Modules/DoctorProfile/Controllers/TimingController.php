<?php

namespace App\Modules\DoctorProfile\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Auth;
use Session;
use App\Traits\SessionTrait;
use App\Traits\RestApi;
use Config;
use DB;
use Illuminate\Support\Facades\Validator;
use App\Libraries\SecurityLib;
use App\Libraries\ExceptionLib;
use App\Modules\DoctorProfile\Models\DoctorProfile as Doctors;
use App\Modules\DoctorProfile\Models\Timing;

/**
 * TimingController
 *
 * @package                ILD India Registry
 * @subpackage             TimingController
 * @category               Controller
 * @DateOfCreation         21 june 2018
 * @ShortDescription       This controller to handle all the operation related to 
                           timing
 **/
class TimingController extends Controller {
    use SessionTrait, RestApi;

    // @var Array $http_codes
    // This protected member contains Http Status Codes
    protected $http_codes = []; 

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct() {
        $this->http_codes = $this->http_status_codes();
        
        // Init security library object
        $this->securityLibObj = new SecurityLib(); 

        // Init Timing model object
        $this->timingModelObj = new Timing(); 

        // Init exception library object
        $this->exceptionLibObj = new ExceptionLib();
    }

    /**
     * @DateOfCreation      21 June 2018
     * @ShortDescription    This function is responsible to display a listing of the Timing 
                            for a Doctor.
     * @param               $request - Request object for request data
     * @return              \Illuminate\Http\Response
     */
    public function getTimingList(Request $request) {

        $userId = (in_array($request->user()->user_type, Config::get('constants.USER_TYPE_STAFF'))) ? $request->user()->created_by : $request->user()->user_id;
        $timing = $this->timingModelObj->getTimingList($userId);
        // validate, is query executed successfully 
        if($timing)
        {
            return $this->resultResponse(
                        Config::get('restresponsecode.SUCCESS'), 
                        $timing,
                        [],
                        '', 
                        $this->http_codes['HTTP_OK']
                    );
        }else{
            return $this->resultResponse(
                        Config::get('restresponsecode.ERROR'), 
                        [], 
                        trans('DoctorProfile::messages.timing_failed'), 
                        [],
                        $this->http_codes['HTTP_OK']
                    );
        }
    }

    /**
     * @DateOfCreation      21 June 2018
     * @ShortDescription    This function is responsible to store a newly created resource in storage.
     * @param               $request - Request object for request data
     * @return              \Illuminate\Http\Response
     */
    public function createTiming(Request $request)
    {
        $requestData = $this->getRequestData($request);
        $requestData['user_id'] = (in_array($request->user()->user_type, Config::get('constants.USER_TYPE_STAFF'))) ? $request->user()->created_by : $request->user()->user_id;
        $requestData['slot_duration'] = ($requestData['slot_duration']) ? $requestData['slot_duration'] : Config::get('constants.DEFAULT_SLOT_DURATION');
        $requestData['patients_per_slot'] = ($requestData['patients_per_slot']) ? $requestData['patients_per_slot'] : Config::get('constants.DEFAULT_PATIENTS_PER_SLOT');
        $requestData['clinic_id'] = $this->securityLibObj->decrypt($requestData['clinic_id']);
        $validate = $this->TimingValidator($requestData);
        if($validate["error"]){
            return $this->resultResponse(
                    Config::get('restresponsecode.ERROR'), 
                    [], 
                    $validate['errors'],
                    trans('DoctorProfile::messages.timing_validation_failed'), 
                    $this->http_codes['HTTP_OK']
                  ); 
        }
        // Create timing in database 
        $isDoctorTimingCreated = $this->timingModelObj->createTiming($requestData);
        // validate, is query executed successfully 
        if(!empty($isDoctorTimingCreated))
        {
            return  $this->resultResponse(
                Config::get('restresponsecode.SUCCESS'), 
                $isDoctorTimingCreated, 
                [],
                trans('DoctorProfile::messages.timing_added'), 
                $this->http_codes['HTTP_OK']
            );

        }else{
            return  $this->resultResponse(
                Config::get('restresponsecode.ERROR'), 
                [], 
                [],
                trans('DoctorProfile::messages.timing_failed'), 
                $this->http_codes['HTTP_OK']
            );
        }
    }

    /**
     * @DateOfCreation      21 June 2018
     * @ShortDescription    This function is responsible to update an existing Timing.
     * @param               $request - Request object for request data
     * @return              \Illuminate\Http\Response
     */
    public function updateTiming(Request $request) {

        $requestData = $this->getRequestData($request);
        $requestData['user_id']     = (in_array($request->user()->user_type, Config::get('constants.USER_TYPE_STAFF'))) ? $request->user()->created_by : $request->user()->user_id;
        $requestData['slot_duration'] = ($requestData['slot_duration']) ? $requestData['slot_duration'] : Config::get('constants.DEFAULT_SLOT_DURATION');
        $requestData['patients_per_slot'] = ($requestData['patients_per_slot']) ? $requestData['patients_per_slot'] : Config::get('constants.DEFAULT_PATIENTS_PER_SLOT');
        
        // Update timing detail in database 
        $validate = $this->TimingValidator($requestData);
        if($validate["error"]){
            return $this->resultResponse(
                    Config::get('restresponsecode.ERROR'), 
                    [], 
                    $validate['errors'],
                    trans('DoctorProfile::messages.timing_validation_failed'), 
                    $this->http_codes['HTTP_OK']
                  ); 
        }
        $isDoctorTimingUpdate = $this->timingModelObj->updateTiming($requestData);
        // validate, is query executed successfully 
        if(!empty($isDoctorTimingUpdate))
        {
            return  $this->resultResponse(
                Config::get('restresponsecode.SUCCESS'), 
                $isDoctorTimingUpdate, 
                [],
                trans('DoctorProfile::messages.timing_update'), 
                $this->http_codes['HTTP_OK']
            );
        }else{
            return  $this->resultResponse(
                Config::get('restresponsecode.ERROR'), 
                [], 
                [],
                trans('DoctorProfile::messages.timing_failed'), 
                $this->http_codes['HTTP_OK']
            );
        }
    }

    /**
    * @DateOfCreation        21 June 2018
    * @ShortDescription      This function is responsible for validating blog data
    * @param                 Array $data This contains full request data
    * @param                 Array $extra extra validation rules 
    * @return                VIEW
    */ 
    protected function TimingValidator(array $data, $extra = []) {
        $error = false;
        $errors = [];
        $timing_id = (isset($data['timing_id'])) ? $data['timing_id'] : '';
        if($data['end_time'] < $data['start_time']){
            $timing_validation = 'greater_than_check';
        }else{
            $timing_validation = 'slot_exists_check';
            if($data['week_day'] == '0'){
                for($day=1; $day<=7; $day++){
                    $weekTimingData = $this->timingModelObj->getWeekTiming($data['user_id'], $day, $timing_id);
                    if($weekTimingData){
                        $slotValidationCheck = $this->checkSlotValidity($weekTimingData, $data['start_time'], $data['end_time']);
                        $isSlotValid = ($slotValidationCheck) ? 'valid' : 'invalid';
                        if($isSlotValid == 'invalid'){
                            $isSlotValid = $day;
                            $timing_validation = 'week_slot_exists_check';
                            break;
                        }
                    }else{
                        $isSlotValid = 'valid';
                    }
                }
            }else{
                $weekTimingData = $this->timingModelObj->getWeekTiming($data['user_id'], $data['week_day'], $timing_id);
                if($weekTimingData){
                    $slotValidationCheck = $this->checkSlotValidity($weekTimingData, $data['start_time'], $data['end_time']);
                    $isSlotValid = ($slotValidationCheck) ? 'valid' : 'invalid';
                }else{
                    $isSlotValid = 'valid';
                }
            }
        }
        $rules = [
            'week_day' => 'required',
            'start_time' => 'required',
            'end_time' => 'required|'.$timing_validation.':start_time,'.$isSlotValid,
            'clinic_id' => 'required',
        ];
        $rules = array_merge($rules,$extra);
        $validator = Validator::make($data, $rules);
        if($validator->fails()) {
            $error = true;
            $errors = $validator->errors();
        }
        // if($errors->has('end_time') && $data['week_day'] == '0'){
        //     $validator->errors()->add('end_time', 'Something is wrong with this field!');
        // }
        return ["error" => $error,"errors" => $errors];
    }

    /**
    * @DateOfCreation       29 June 2018
    * @ShortDescription     This function is responsible for validating the timing slot 
                            selected for a weekday
    * @param                 Array $data This contains full request data
    * @param                 $start_time timing start time
    * @param                 $end_time timing end time
    * @return               boolean
    */ 
    protected function checkSlotValidity(array $data, $start_time='', $end_time='') {
        if(!empty($data)){
            foreach($data as $slot){
                if($slot->start_time != Config::get('constants.TIMING_SLOT_OFF') && $slot->end_time != Config::get('constants.TIMING_SLOT_OFF')){
                    if((($start_time > $slot->start_time)&&($start_time < $slot->end_time)) || (($end_time > $slot->start_time)&&($end_time < $slot->end_time))){
                        return false;
                    }else if(($start_time <= $slot->start_time)&&($end_time >= $slot->end_time)){
                        return false;
                    }
                }
            }
        }
        return true;
    }
}
