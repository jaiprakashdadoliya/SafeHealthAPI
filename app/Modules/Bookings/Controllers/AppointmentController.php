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
use App\Libraries\UtilityLib;
use App\Modules\AppointmentCategory\Models\AppointmentCategory;
use App\Modules\Setup\Models\StaticDataConfig as StaticData;
use App\Modules\Clinics\Models\Clinics;
use App\Modules\Doctors\Models\ManageCalendar as ManageCalendarSetting;
use App\Modules\Search\Models\Search;

/**
 * AppointmentController
 *
 * @package                Safehealth
 * @subpackage             AppointmentController
 * @category               Controller
 * @DateOfCreation         11 July 2018
 * @ShortDescription       This controller to handle all the operation related to 
                           bookings
 **/
class AppointmentController extends Controller
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
        $this->patientsObj = new Patients();

        // Init exception library object
        $this->utilityLibObj = new UtilityLib();   

        // Init exception library object
         $this->staticDataObj = new StaticData();

        // Init DoctorProfile model object
        $this->appointmentCategoryObj = new AppointmentCategory();

        // Init DoctorProfile model object
        $this->clinicsObj = new Clinics();

        // Init DoctorProfile model object
        $this->manageCalendarSettingObj = new ManageCalendarSetting();
        
        // Init Search model object
        $this->searchObj = new Search();
    }

    /**
     * @DateOfCreation        21 May 2018
     * @ShortDescription      This function is responsible to get the Symptoms add
     * @return                Array of status and message
     */
    public function getAppointmentDetails(Request $request)
    {
        $userId = ($request->user()->user_type == Config::get('constants.USER_TYPE_DOCTOR')) ? $request->user()->user_id : $request->user()->created_by;
        $requestData = $this->getRequestData($request);
        $clinicIdEncrypt = $requestData['clinic_id'];
        $viewType = $requestData['view_type'];
        $clinicIdDecrypt = !empty($clinicIdEncrypt) && !is_numeric($clinicIdEncrypt) ? $this->securityLibObj->decrypt($clinicIdEncrypt) : 0;
        $selectedDateStart = $selectedDateEnd = $requestData['start_date'];
        $selectedDate = $this->dateTimeLibObj->changeSpecificFormat($selectedDateStart,Config::get('constants.DB_SAVE_DATE_TIME_FORMAT'),Config::get('constants.USER_VIEW_DATE_FORMAT_CARBON'));
        $selectedDate = $selectedDate['code'] == Config::get('restresponsecode.SUCCESS') ? $selectedDate['result'] : date(Config::get('constants.DB_SAVE_DATE_TIME_FORMAT'));

        $manageCalendarRecord = [
            'booking_date' => $selectedDate,
            'clinic_id' => $clinicIdEncrypt,
            'pat_id' => '',
            'booking_reason' => '',
            'booking_time' => ''
        ];

        $slotDurationData = $this->manageCalendarSettingObj->getManageCalendarRecordByUserId($userId);
        $slotDurationData = !empty($slotDurationData) ? (array) $slotDurationData : [];

        if(strtolower($viewType) == 'day'){
            $slotDuration = 30;
        }else{
            $slotDuration = isset($slotDurationData['mcs_slot_duration']) ? $slotDurationData['mcs_slot_duration'] :Config::get('constants.MANGE_DEFAULT_SLOT_DURATION');
        }
        $slotDurationStart = $requestData['slot_data'];
        $selectedTime = substr($slotDurationStart, 0, 2).':'.substr($slotDurationStart, 2);

        $slotDurationEnd =date(Config::get('constants.TIMESLOTIDSTORE'),strtotime($selectedTime . ' +'.$slotDuration.' minutes'));
        $weekDayData = $this->dateTimeLibObj->getWeekDayBetweenTwodates($selectedDateStart,$selectedDateEnd,Config::get('constants.DB_SAVE_DATE_TIME_FORMAT'));
        $weekDay = $weekDayData['code'] == Config::get('restresponsecode.SUCCESS') && !empty($weekDayData['result']) ? key($weekDayData['result']) : date('D',strtotime($selectedDateStart));  
        $extraDataTimeSlot = [
        'clinic_id' => $clinicIdDecrypt,
        'week_day' => $weekDay
        ];
        $resTimeSlot = $this->bookingsModelObj->getTimeSlotForBooking($slotDurationStart,$slotDurationEnd,$userId,$extraDataTimeSlot);
        $resTimeSlot = $this->utilityLibObj->changeObjectToArray($resTimeSlot);
        $timingSlot = [];
        if(count($resTimeSlot)>0){
            $todayDateData = $this->dateTimeLibObj->convertTimeZone(date(Config::get('constants.DB_SAVE_DATE_FORMAT')),Config::get('constants.DB_SAVE_DATE_FORMAT'),date_default_timezone_get(),Config::get('app.user_timezone'));
            $currentDate = $todayDateData['code'] == config::get('restresponsecode.SUCCESS') ? $todayDateData['result'] : date(Config::get('constants.DB_SAVE_DATE_FORMAT'));
            $currentDateCheck = strtotime($selectedDateStart) == strtotime($currentDate) ? true:false;
            $currentTimeCheckData = $this->dateTimeLibObj->convertTimeZone(date(Config::get('constants.TIMESLOTIDSTORE')),Config::get('constants.TIMESLOTIDSTORE'),date_default_timezone_get(),Config::get('app.user_timezone'));
            $currentTimeCheck = $currentTimeCheckData['code'] == config::get('restresponsecode.SUCCESS') ? $currentTimeCheckData['result'] : date(Config::get('constants.TIMESLOTIDSTORE'));
            foreach ($resTimeSlot as $key => $rowTimeSlot) {
                $start_time = $rowTimeSlot['start_time'] <= $slotDurationStart && $rowTimeSlot['end_time'] >= $slotDurationStart ? $slotDurationStart : $rowTimeSlot['start_time'];
                $end_time = $rowTimeSlot['start_time'] <= $slotDurationEnd && $rowTimeSlot['end_time'] >= $slotDurationEnd ? $slotDurationEnd : $rowTimeSlot['end_time'];
                $timeing = [
                            'start_time' => $start_time,
                            'end_time' => $end_time,
                            'slot_duration' => $rowTimeSlot['slot_duration']
                        ];
                $extraTimeSlotCreat = [
                'time_slot_format' => Config::get('constants.TIMESLOTFORMATSHOWWISE'),
                'booking_calculation_disable' => '1',
                ];
                $timeSlots = $this->searchObj->createTimeSlot((object) $timeing, date(Config::get('constants.DB_SAVE_DATE_FORMAT')),$extraTimeSlotCreat);
                $timeSlots = array_map(function($row) use($rowTimeSlot,$currentTimeCheck,$currentDateCheck){
                    $newRow = [];
                    if($currentDateCheck && $row['slot_time']< $currentTimeCheck){
                        return $newRow;
                    }else{
                        $newRow['value'] = $rowTimeSlot['timing_id'].'@##@'.$row['slot_time'];
                        $newRow['label'] = $row['slot_time_format'];
                        return $newRow;
                    }
                }, $timeSlots);
                $timeSlots = array_filter($timeSlots);
                $timingSlot = !empty($timeSlots) ? array_merge($timingSlot,$timeSlots) : $timingSlot;
            }
        }

        if(empty($timingSlot)){
            $timingSlot[]=['value'=>'','label' =>'Slot not available'];
        }

        $patientDetails = $this->patientsObj->patientListQuery($userId);
        $patientDetails = $patientDetails->get();
        $patientDetails = $this->utilityLibObj->changeObjectToArray($patientDetails);
        $patientDetails = !empty($patientDetails) ? array_map(function($tag) {
            return array(
                'value' => $this->securityLibObj->encrypt($tag['user_id']),
                'label' => trim($this->staticDataObj->getTitleNameById($tag['pat_title']).' '.$tag['user_firstname'].' '.$tag['user_lastname'])
            );
            }, $patientDetails):[];
        $patAppointmentReasonsData = $this->appointmentCategoryObj->getAppointmentReasons(['user_id'=>$userId],false);
        $patAppointmentReasonsData = $this->utilityLibObj->changeObjectToArray($patAppointmentReasonsData);
        $patAppointmentReasonsData = !empty($patAppointmentReasonsData) ? array_map(function($tag) {
            return array(
                'value' => $this->securityLibObj->encrypt($tag['appointment_cat_id']),
                'label' => $tag['appointment_cat_name']
            );
            }, $patAppointmentReasonsData):[];

        $clinicData = $this->clinicsObj->getClinicById($clinicIdDecrypt);
        $clinicData = !empty($clinicData) ? (array) $clinicData :[];
        $clinicData = !empty($clinicData) ? [
                [
                    'value' => $this->securityLibObj->encrypt($clinicData['clinic_id']),
                    'label' => $clinicData['clinic_name']
                ]
            ] : [];

        
        $staticDataKey        = $this->staticDataObj->getManageCalendarAppointmentData();
        $staticDataArrWithCustomKey = $this->utilityLibObj->changeArrayKey($staticDataKey, 'id');
        $optionData =[
            'patientDetails'    =>  $patientDetails,
            'patAppointmentReasonsData' =>  $patAppointmentReasonsData,
            'clinicData'    =>  $clinicData,
            'bookingTimeData'    =>  $timingSlot
        ];

        $finalCheckupRecords = [];
        $tempData = [];
        if(!empty($staticDataArrWithCustomKey)){
            foreach ($staticDataArrWithCustomKey as $mcTypeIdKey => $mcValue) {
                $temp = [];
                $encryptMcTypeIdKey = $this->securityLibObj->encrypt($mcTypeIdKey);
                $valuesData = isset($manageCalendarRecord[$mcValue['input_name']]) ? $manageCalendarRecord[$mcValue['input_name']] : '';
                $temp = [  
                'showOnForm'=>true,
                'name' => $mcValue['input_name'],
                'title' => $mcValue['value'],
                'type' => $mcValue['input_type'],
                'value' => $mcValue['input_type'] === 'customcheckbox' ? [(string) $valuesData] : $valuesData,
                'cssClasses' => $mcValue['cssClasses'],
                'clearFix' => $mcValue['isClearfix'],


            ];
            if($mcValue['input_type'] === 'date'){
                $temp['format'] =  isset($mcValue['format']) ?  $mcValue['format'] : Config::get('constants.REACT_WEB_DATE_FORMAT');
            }
            if(isset($mcValue['validations_required']) && $mcValue['validations_required']){
                $temp['validations'] = [['isRequired'=>true,'msg'=>'This field is required.']];
            }
            if(isset($mcValue['readOnly'])){
                $temp['readOnly'] = $mcValue['readOnly'];
            }
            
            $tempData[$mcValue['input_name'].'_data'] = isset($mcValue['input_type_option']) && !empty($mcValue['input_type_option']) && isset($optionData[$mcValue['input_type_option']]) && !empty($optionData[$mcValue['input_type_option']]) ? $optionData[$mcValue['input_type_option']]:[] ;
                
            $finalCheckupRecords['form']['fields'][] = $temp;
            $finalCheckupRecords['form']['data'] = $tempData;
            $finalCheckupRecords['form']['handlers'] = [];
            }
        }

        return $this->resultResponse(
                Config::get('restresponsecode.SUCCESS'), 
                $finalCheckupRecords, 
                [],
                trans('Bookings::messages.appointment_list_success'),
                $this->http_codes['HTTP_OK']
            );
    } 
    /**
    * @DateOfCreation        11 June 2018
    * @ShortDescription      This function is responsible for delete visit WorkEnvironment Data 
    * @param                 Array $wefId   
    * @return                Array of status and message
    */
    public function destroy(Request $request)
    {   
        $requestData = $this->getRequestData($request);
        $primaryKey = $this->bookingsModelObj->getTablePrimaryIdColumn();
        $primaryId = $requestData[$primaryKey];
        $primaryId = $this->securityLibObj->decrypt($primaryId);
        $isPrimaryIdExist = $this->bookingsModelObj->isPrimaryIdExist($primaryId);
        if(!$isPrimaryIdExist){
            return $this->resultResponse(
                Config::get('restresponsecode.ERROR'), 
                [], 
                [$primaryKey => [trans('Bookings::messages.booking_not_found')]],
                trans('Bookings::messages.booking_not_found'), 
                $this->http_codes['HTTP_OK']
            ); 
        }
        $extraUpdateData = ['booking_reason'=>Config::get('constants.BOOKING_CANCELLED')];
        $deleteDataResponse   = $this->bookingsModelObj->doDeleteRequest($primaryId,$extraUpdateData);
        if($deleteDataResponse){
            return $this->resultResponse(
                Config::get('restresponsecode.SUCCESS'), 
                [], 
                [],
                trans('Bookings::messages.booking_deleted'),
                $this->http_codes['HTTP_OK']
            );
        }
        return $this->resultResponse(
            Config::get('restresponsecode.ERROR'), 
            [], 
            [],
            trans('Bookings::messages.booking_delete_failed'),
            $this->http_codes['HTTP_OK']
        );

    }
}
