<?php

namespace App\Modules\Bookings\Controllers;

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
use App\Libraries\DateTimeLib;
use App\Libraries\EmailLib;
use Illuminate\Support\Facades\Mail;
use App\Modules\Auth\Models\Auth as Users;
use App\Modules\Bookings\Models\Bookings;
use App\Modules\Patients\Models\Patients;

/**
 * BookingsController
 *
 * @package                Safehealth
 * @subpackage             BookingsController
 * @category               Controller
 * @DateOfCreation         11 July 2018
 * @ShortDescription       This controller to handle all the operation related to 
                           bookings
 **/
class BookingsController extends Controller
{
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

        // Init dateTime library object
        $this->dateTimeLibObj = new DateTimeLib();  

        // Init Bookings model object
        $this->bookingsModelObj = new Bookings();

        // Init exception library object
        $this->exceptionLibObj = new ExceptionLib();

        // Init Patients model object
        $this->patientsModelObj = new Patients();
    }

    /**
    * @DateOfCreation        11 July 2018
    * @ShortDescription      This function is responsible for creating new Appointment
    * @param $request - Request object for request data
    * @return \Illuminate\Http\Response
    */
    public function createBooking(Request $request)
    {
        $requestData = $this->getRequestData($request);
        $userType = $request->user()->user_type;
        $extra['user_type'] = $userType;

        // Create timing in database
        $logginUserId = (in_array($request->user()->user_type, Config::get('constants.USER_TYPE_STAFF'))) ? $request->user()->created_by : $request->user()->user_id;
        if($request->user()->user_type == Config::get('constants.USER_TYPE_PATIENT')){
            $patId = $request->user()->user_id;
        }else{
            $patId = $this->securityLibObj->decrypt($requestData['pat_id']);
        }
        
        $appointmentExist = $this->bookingsModelObj->isAppointmentExist($logginUserId,$patId); 

        if($requestData['timing_id'] == Config::get('constants.NEXT_VISIT') && !$appointmentExist){
            $requestData['user_id']             = $logginUserId;
            $requestData['pat_id']              = $patId;
            $requestData['is_profile_visible']  = Config::get('constants.IS_VISIBLE_YES');
            $nextVisitBookingDate               = strtotime('+'.Config::get('constants.NEXT_VISIT_DAYS').' days', strtotime(date('Y-m-d')));
            $requestData['booking_date']        = date('Y-m-d',$nextVisitBookingDate);
            $week_day = date('w',$nextVisitBookingDate);
            $requestData['clinic_id'] = $this->bookingsModelObj->getDoctorClinic($logginUserId);
            $requestData['booking_reason'] = $this->bookingsModelObj->getAppointmentCategory($logginUserId);
            $requestData['booking_time'] =  date("Hi", strtotime("+5 hour +30 minutes"));
            $requestData['timing_id'] = $this->bookingsModelObj->getTimingId($logginUserId,$week_day,$requestData['booking_time']); 
            if(!$requestData['timing_id']){
            $requestData['timing_id']; 
                $doctorDetail = Users::where('user_id', $requestData['user_id'])->first();
                $patientDetail = $this->bookingsModelObj->getPatientDetail($requestData['pat_id']);
                $emailDetail['doctorDetail'] = $doctorDetail;
                $emailDetail['patientDetail'] = $patientDetail;
                $emailConfigDoctor = [
                    'viewData'  =>  [
                                        'emailDetail'=>$emailDetail,
                                        'app_name' => Config::get('constants.APP_NAME'),
                                        'app_url' => Config::get('constants.APP_URL'),
                                        'info_email' => Config::get('constants.INFO_EMAIL')
                                    ],
                    'emailTemplate' => 'emails.nextBookingFailure',
                    'subject'       => trans('Bookings::messages.next_booking_unavailable')
                ];
                $emailSend = Mail::to($doctorDetail->user_email)
                        ->send(new EmailLib($emailConfigDoctor));
                return $this->resultResponse(
                    Config::get('restresponsecode.SUCCESS'),
                    [],
                    [],
                    trans('Bookings::messages.next_booking_unavailable'),
                    $this->http_codes['HTTP_OK']
                );
                

            }
            unset($requestData['visit_id']);
            unset($requestData['user_type']);

        }else{
             if($userType == Config::get('constants.USER_TYPE_PATIENT')){
               $requestData['user_id'] = $this->securityLibObj->decrypt($requestData['user_id']);
               $requestData['pat_id'] = $request->user()->user_id;
            } else {
                $requestData['user_id']             = $logginUserId;
                $requestData['pat_id']              = $patId;
                $requestData['is_profile_visible']  = Config::get('constants.IS_VISIBLE_YES');
                $requestData['booking_date']        = isset($requestData['booking_date']) && !empty($requestData['booking_date']) ? $this->dateTimeLibObj->covertUserDateToServerType($requestData['booking_date'],'dd/mm/YYYY','Y-m-d')['result'] : $requestData['booking_date'];

                unset($requestData['visit_id']);
                unset($requestData['user_type']);
            }
            $requestData['timing_id'] = $this->securityLibObj->decrypt($requestData['timing_id']);
            $requestData['clinic_id'] = $this->securityLibObj->decrypt($requestData['clinic_id']);
            $requestData['booking_reason'] = $this->securityLibObj->decrypt($requestData['booking_reason']);
        }

        unset($requestData['booking_id']);
        unset($requestData['payment_mode']);
        unset($requestData['clinic_address']);
       
        $validate = $this->BookingsValidator($requestData, $extra);
        if($validate["error"]){
            return $this->resultResponse(
                    Config::get('restresponsecode.ERROR'),
                    [],
                    $validate['errors'],
                    trans('Bookings::messages.booking_validation_failed'),
                    $this->http_codes['HTTP_OK']
                  );
        }
        try {
            DB::beginTransaction();
            $isBookingCreated = $this->bookingsModelObj->createBooking($requestData);
            
            $patDocRelData = [
                'user_id'       => $requestData['user_id'],
                'pat_id'        => $requestData['pat_id'],
                'assign_by_doc' => $requestData['pat_id'],
                'ip_address'    => $requestData['ip_address'],
                'is_deleted'    => Config::get('constants.IS_DELETED_NO'),
            ];
            
            // Create Doctor-Patienr relation
            $patDocRelId = '';
            $patDocRelId = $this->patientsModelObj->createPatientDoctorRelation('doctor_patient_relation', $patDocRelData);

            // validate, is query executed successfully
            if(!empty($isBookingCreated) && !empty($patDocRelId))
            {
                $emailDetail = array();
                $doctorDetail = Users::where('user_id', $requestData['user_id'])->first();
                $patientDetail = Users::where('user_id', $requestData['pat_id'])->first();
                $emailDetail['doctorDetail'] = $doctorDetail;
                $emailDetail['patientDetail'] = $patientDetail;
                $emailDetail['bookingDetail']  = $isBookingCreated;
                $emailConfigPatient = [
                    'viewData'  =>  [
                                        'emailDetail'=>$emailDetail,
                                        'app_name' => Config::get('constants.APP_NAME'),
                                        'app_url' => Config::get('constants.APP_URL'),
                                        'info_email' => Config::get('constants.INFO_EMAIL')
                                    ],
                    'emailTemplate' => 'emails.bookingsuccessfulpatient',
                    'subject'       => trans('Bookings::messages.booking_email_subject')
                ];
                $emailConfigDoctor = [
                    'viewData'  =>  [
                                        'emailDetail'=>$emailDetail,
                                        'app_name' => Config::get('constants.APP_NAME'),
                                        'app_url' => Config::get('constants.APP_URL'),
                                        'info_email' => Config::get('constants.INFO_EMAIL')
                                    ],
                    'emailTemplate' => 'emails.bookingsuccessfuldoctor',
                    'subject'       => trans('Bookings::messages.booking_email_subject')
                ];

                try{
                    $emailSend = Mail::to($doctorDetail->user_email)
                        ->send(new EmailLib($emailConfigDoctor));
                    $emailSend = Mail::to($patientDetail->user_email)
                        ->send(new EmailLib($emailConfigPatient));
                    DB::commit();
                    return  $this->resultResponse(
                        Config::get('restresponsecode.SUCCESS'),
                        $isBookingCreated,
                        [],
                        trans('Bookings::messages.booking_added'),
                        $this->http_codes['HTTP_OK']
                    );
                } catch (\Exception $ex) {
                    DB::rollback();
                    $eMessage = $this->exceptionLibObj
                                     ->reFormAndLogException($ex,'BookingsController', 'createBooking');
                    return $this->resultResponse(
                    Config::get('restresponsecode.EXCEPTION'), 
                    [], 
                    [],
                    $eMessage, 
                    $this->http_codes['HTTP_OK']
                    );
                }  
            }else{
                DB::rollback();
                return $this->resultResponse(
                    Config::get('restresponsecode.ERROR'),
                    [],
                    [],
                    trans('Bookings::messages.booking_failed'),
                    $this->http_codes['HTTP_OK']
                );
            }
        } catch (\Exception $ex) {
            DB::rollback();
            $eMessage = $this->exceptionLibObj->reFormAndLogException($ex,'BookingsController', 'createBooking');            
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
    * @DateOfCreation        11 July 2018
    * @ShortDescription      This function is responsible for validating booking data
    * @param                 Array $data This contains full request data
    * @param                 Array $extra extra validation rules
    * @return                VIEW
    */
    protected function BookingsValidator(array $data, $extra = []) {
        $error = false;
        $errors = [];
        $userType = $extra['user_type'];
        $slotValidationCheck = $this->bookingsModelObj->isSlotAvailable($data['timing_id'], $data['booking_date'], $data['booking_time']);
        $isSlotValid = Config::get('constants.SLOT_IS_VALID');
        if($slotValidationCheck === true){
            $patId = ($userType == Config::get('constants.USER_TYPE_PATIENT')) ? $data['pat_id'] : $data['user_id'];
            $userAlreadyBooked = $this->bookingsModelObj->userAlreadyBooked($data['timing_id'], $data['booking_date'], $data['booking_time'], $patId); 
            if($userAlreadyBooked == Config::get('constants.PATIENT_ALREADY_BOOKED_SLOT')){
                $isSlotValid = trans('Bookings::messages.user_already_booked_slot_patient');
            }else if($userAlreadyBooked == Config::get('constants.PATIENT_ALREADY_BOOKED_DAY')){
                $isSlotValid = Config::get('constants.SLOT_IS_VALID');
            }
        }
        $extra = [];
        $rules = [
            'user_id' => 'required',
            'pat_id'  => 'required',
            'clinic_id' => 'required',
            'timing_id' => 'required',
            'booking_date' => 'required',
            'booking_time' => 'required|booking_available_check:booking_date,'.$isSlotValid,
            'is_profile_visible' => 'required',
        ];
        $rules = array_merge($rules,$extra);
        $validator = Validator::make($data, $rules);
        if($validator->fails()) {
            $error = true;
            $errors = $validator->errors();
        }
        return ["error" => $error,"errors" => $errors];
    }

    /**
    * @DateOfCreation        24 July 2018
    * @ShortDescription      This function is responsible for checking slot availability
    * @param                 Array $data This contains full request data
    * @param                 Array $extra extra validation rules
    * @return                VIEW
    */
    protected function isSlotAvailable(Request $request) {
        $requestData = $this->getRequestData($request);
        $requestData['timing_id'] = $this->securityLibObj->decrypt($requestData['timing_id']);
        $requestData['user_id'] = $this->securityLibObj->decrypt($requestData['user_id']);
        $isSlotValid = false;
        $isSlotValid = $this->bookingsModelObj->isSlotAvailable($requestData['timing_id'], $requestData['booking_date'], $requestData['booking_time'], $requestData['timing_id']);
        if($isSlotValid === true){
            $userAlreadyBooked = $this->bookingsModelObj->userAlreadyBooked($requestData['timing_id'], $requestData['booking_date'], $requestData['booking_time'], $requestData['user_id']);
            if($userAlreadyBooked == Config::get('constants.PATIENT_ALREADY_BOOKED_SLOT')){
                return $this->resultResponse(
                    Config::get('restresponsecode.ERROR'),
                    [],
                    [],
                    trans('Bookings::messages.user_already_booked_slot'),
                    $this->http_codes['HTTP_OK']
                );
            }else if($userAlreadyBooked == Config::get('constants.PATIENT_ALREADY_BOOKED_DAY')){
                return $this->resultResponse(
                    Config::get('restresponsecode.SUCCESS'),
                    trans('Bookings::messages.user_already_booked_day'),
                    [],
                    '',
                    $this->http_codes['HTTP_OK']
                );
            }else{
                return $this->resultResponse(
                    Config::get('restresponsecode.SUCCESS'),
                    $isSlotValid,
                    [],
                    '',
                    $this->http_codes['HTTP_OK']
                );
            }
        }else{
            return $this->resultResponse(
                Config::get('restresponsecode.ERROR'),
                [],
                [],
                trans('Bookings::messages.booking_unavailable'),
                $this->http_codes['HTTP_OK']
              );
        }
    }

    /**
    * @DateOfCreation        30 July 2018
    * @ShortDescription      Get a validator for an incoming User request
    * @param                 \Illuminate\Http\Request  $request
    * @return                \Illuminate\Contracts\Validation\Validator
    */
    public function getAppointmentList(Request $request)
    {

        $user_id     = (in_array($request->user()->user_type, Config::get('constants.USER_TYPE_STAFF'))) ? $request->user()->created_by : $request->user()->user_id;
        $user_type   = $request->user()->user_type;

        $requestData = $this->getRequestData($request);
        $requestData['user_id'] = $user_id;
        $requestData['user_type'] = $user_type;
        if($user_type == Config::get('constants.USER_TYPE_PATIENT') && empty($requestData['date'])){
            $date = date('Y-m-d'); 
        }else if($user_type == Config::get('constants.USER_TYPE_PATIENT') && !empty($requestData['date'])){
            $type = strtotime($requestData['date']);
            if($requestData['appointmentPage'] == 'next'){
                $type = strtotime($requestData['date'] . "+1 days");
            }elseif($requestData['appointmentPage'] == 'previous'){
                $type = strtotime($requestData['date'] . "-1 days");
            }
            $date = date('Y-m-d', $type);
        }else{
            $date = $requestData['date'];
        }
        if(!empty($date)){

            $appointmentDate = $date;
            $requestData['appointmentDate'] = date('Y-m-d',strtotime($appointmentDate));
            
            $appointmentList = $this->bookingsModelObj->getAppointmentList($requestData);
            if($appointmentList){
                return $this->resultResponse(
                        Config::get('restresponsecode.SUCCESS'), 
                        $appointmentList, 
                        [],
                        trans('Bookings::messages.appointment_list_success'),
                        $this->http_codes['HTTP_OK']
                    );
            }else{
                return $this->resultResponse(
                    Config::get('restresponsecode.ERROR'), 
                    [], 
                    [],
                    trans('Bookings::messages.appointment_list_error'),
                    $this->http_codes['HTTP_OK']
                );
            }
        }else{
            return $this->resultResponse(
                Config::get('restresponsecode.ERROR'), 
                [], 
                [],
                trans('Bookings::messages.appointment_date_required'),
                $this->http_codes['HTTP_OK']
            );
            
        }
    }


    /**
    * @DateOfCreation        30 July 2018
    * @ShortDescription      Get a validator for an incoming User request
    * @param                 \Illuminate\Http\Request  $request
    * @return                \Illuminate\Contracts\Validation\Validator
    */
    public function getTodayAppointmentList(Request $request)
    {
        $user_id     = (in_array($request->user()->user_type, Config::get('constants.USER_TYPE_STAFF'))) ? $request->user()->created_by : $request->user()->user_id;
        $user_type   = $request->user()->user_type;
        $appointmentList = $this->bookingsModelObj->getTodayAppointmentList($user_id, $user_type);
        if($appointmentList){
            return $this->resultResponse(
                    Config::get('restresponsecode.SUCCESS'), 
                    $appointmentList, 
                    [],
                    trans('Bookings::messages.today_appointment_success'),
                    $this->http_codes['HTTP_OK']
                );
        }else{
            return $this->resultResponse(
                Config::get('restresponsecode.ERROR'), 
                [], 
                [],
                trans('Bookings::messages.today_appointment_error'),
                $this->http_codes['HTTP_OK']
            );
        }
    }

    /**
    * @DateOfCreation        30 July 2018
    * @ShortDescription      Get a validator for an incoming User request
    * @param                 \Illuminate\Http\Request  $request
    * @return                \Illuminate\Contracts\Validation\Validator
    */
    public function getAppointmentListCalendar(Request $request)
    {
        $user_id     = $request->user()->user_id;
        $user_type   = $request->user()->user_type;
        $requestData = $this->getRequestData($request);
        $requestData['user_id'] = (in_array($request->user()->user_type, Config::get('constants.USER_TYPE_STAFF'))) ? $request->user()->created_by : $user_id;
        $requestData['user_type'] = $user_type;
        $startDate = $requestData['startDate'];
        $endDate = $requestData['endDate'];
        $viewType = $requestData['view_type'];
        $userId = $requestData['user_id'];
        $extra =[];
        $extra['view_type'] = $viewType;
        $extra['clinic_id'] = isset($requestData['clinic_id']) && !empty($requestData['clinic_id']) ? $this->securityLibObj->decrypt($requestData['clinic_id']) : '';
        $extra = array_filter($extra);
        $appointmentList = $this->bookingsModelObj->getAppointmentListCalendar($startDate,$endDate,$userId,$extra);
        
        if($appointmentList){
            return $this->resultResponse(
                    Config::get('restresponsecode.SUCCESS'), 
                    $appointmentList, 
                    [],
                    trans('Bookings::messages.appointment_list_success'),
                    $this->http_codes['HTTP_OK']
                );
        }else{
            return $this->resultResponse(
                Config::get('restresponsecode.ERROR'), 
                [], 
                [],
                trans('Bookings::messages.appointment_list_error'),
                $this->http_codes['HTTP_OK']
            );
        }
    }

    /**
    * @DateOfCreation        30 July 2018
    * @ShortDescription      Get a validator for an incoming User request
    * @param                 \Illuminate\Http\Request  $request
    * @return                \Illuminate\Contracts\Validation\Validator
    */
   
   public function getPatientNextVisitSchedule(Request $request){
        $requestData = $this->getRequestData($request);
        $patId = $this->securityLibObj->decrypt($requestData['pat_id']);
        $nextbooking = $this->bookingsModelObj->getPatientNextVisitSchedule($patId);
        return $this->resultResponse(
            Config::get('restresponsecode.SUCCESS'), 
            $nextbooking, 
            [],
            trans('Bookings::messages.patient_next_booking_success'),
            $this->http_codes['HTTP_OK']
        );

   }
}
