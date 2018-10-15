<?php

namespace App\Modules\Bookings\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\HasApiTokens;
use App\Traits\Encryptable;
use Illuminate\Support\Facades\DB;
use App\Libraries\SecurityLib;
use App\Libraries\DateTimeLib;
use Config;
use Carbon\Carbon;
use App\Libraries\UtilityLib;
use App\Modules\Search\Models\Search;
use App\Modules\Patients\Models\Patients;
use App\Modules\Setup\Models\StaticDataConfig;
use App\Modules\DoctorProfile\Models\DoctorProfile;
use App\Modules\AppointmentCategory\Models\AppointmentCategory;
use App\Modules\Doctors\Models\ManageCalendar;
use App\Modules\DoctorProfile\Models\Timing;

/**
 * Bookings
 *
 * @package                 Safehealth
 * @subpackage              Bookings
 * @category                Model
 * @DateOfCreation          12 July 2018
 * @ShortDescription        This Model to handle database operation with current table
                            bookings
 **/
class Bookings extends Model {

    use HasApiTokens,Encryptable;

    // @var string $table
    // This protected member contains table name
    protected $table = 'bookings';

    // This protected member contains table name use for Calendar data get
    protected $_timingSlotTable = 'timing';
    protected $_clinicTable = 'clinics';
    protected $_bookingReasonTable = 'appointment_category';
    protected $_bookingTable = 'bookings';
    protected $_userTable = 'users';
    protected $_patientTable = 'patients';

    // @var string $primaryKey
    // This protected member contains primary key
    protected $primaryKey = 'booking_id';

    // @var Array $encryptedFields
    // This protected member contains fields that need to encrypt while saving in database
    protected $encryptable = [];

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct() {
        // Init security library object
        $this->securityLibObj = new SecurityLib();

        // Init DateTimeLib library object
        $this->dateTimeLibObj = new DateTimeLib();

        // Init Search model object
        $this->searchObj = new Search();

        // Init UtilityLib library object
        $this->utilityLibObj = new UtilityLib();

        // Init Patients model object
        $this->patientsObj = new Patients();

        // Init StaticDataConfig model object
        $this->staticDataObj = new StaticDataConfig();

        // Init DoctorProfile model object
        $this->doctorprofileObj = new DoctorProfile();

        // Init DoctorProfile model object
        $this->appointmentCategoryObj = new AppointmentCategory();

        // Init ManageCalendar model object
        $this->manageCalendarObj = new ManageCalendar();

        // Init ManageCalendar model object
        $this->timingObj = new Timing();
    }

    /**
    * @DateOfCreation        12 July 2018
    * @ShortDescription      This function is responsible to get the Booking by id
    * @param                 String $booking_id
    * @return                Array of time
    */
    public function getBookingById($booking_id)
    {
        $queryResult = DB::table($this->table)
                        ->select('bookings.booking_id', 'bookings.user_id', 'bookings.pat_id', 'bookings.clinic_id', 'bookings.booking_date', 'bookings.booking_time', 'bookings.booking_reason',DB::raw("CONCAT(users.user_firstname,' ',users.user_lastname) AS pat_name"))
                        ->join('users', 'bookings.pat_id', '=', 'users.user_id')
                        ->where([
                            'bookings.booking_id' => $booking_id,
                        ])
                        ->get()->first();

        return $queryResult;
    }

    /**
    * @DateOfCreation        29 Apr 2018
    * @ShortDescription      This function is responsible for creating new booking in DB
    * @param                 Array $data This contains full user input data 
    * @return                True/False
    */
    public function createBooking($requestData=array())
    {
        $requestData['created_by'] = $requestData['user_id'];
        $requestData['created_at'] = Carbon::now();
        $requestData = $this->encryptData($requestData);
        $isInserted = DB::table($this->table)->insert($requestData);
        if(!empty($isInserted)) {
             $bookingData = $this->getBookingById(DB::getPdo()->lastInsertId());

        // Encrypt the ID
        $bookingData->booking_id = $this->securityLibObj->encrypt(DB::getPdo()->lastInsertId());
        $bookingData->user_id    = $this->securityLibObj->encrypt($bookingData->user_id);
        $bookingData->pat_id     = $this->securityLibObj->encrypt($bookingData->pat_id);
        $bookingData->clinic_id  = $this->securityLibObj->encrypt($bookingData->clinic_id);
        return $bookingData;
        }
        return false;
    }

    /**
    * @DateOfCreation        29 Apr 2018
    * @ShortDescription      This function is responsible for patient detail
    * @param                 user id and patient id
    * @return                True/False
    */
    public function getPatientDetail($patId)
    {
        $patientDetail = DB::table('users')
                        ->select('users.user_firstname','users.user_lastname','patients.pat_code')
                        ->leftjoin('patients','patients.user_id','=','users.user_id')
                        ->where([
                            'users.user_id'=> $patId,
                            'users.is_deleted' => Config::get('constants.IS_DELETED_NO')
                        ])->first();
        if($patientDetail){
            return $patientDetail;
        }else{
            return false;
        }
    }

    /**
    * @DateOfCreation        29 Apr 2018
    * @ShortDescription      This function is responsible for appointment exist
    * @param                 user id and patient id
    * @return                True/False
    */
    public function isAppointmentExist($userId, $patId)
    {
        $total_booked = DB::table('bookings')
                        ->where([
                            'user_id'=> $userId,
                            'pat_id'=> $patId,
                            'is_deleted' => Config::get('constants.IS_DELETED_NO')
                        ])
                        ->where('booking_date', '>', date('Y-m-d'))
                        ->count();
        if($total_booked > 0){
            return true;
        }else{
            return false;
        }
    }

    /**
    * @DateOfCreation        29 Apr 2018
    * @ShortDescription      This function is responsible for doctor clinic
    * @param                 user id and patient id
    * @return                True/False
    */
    public function getDoctorClinic($userId)
    {
        $clinics = DB::table('clinics')->select('clinic_id')->where(['user_id'=> $userId, 'is_deleted' => Config::get('constants.IS_DELETED_NO')])->first();
        if($clinics){
            return $clinics->clinic_id;
        }else{
            return false;
        }
    }

    /**
    * @DateOfCreation        29 Apr 2018
    * @ShortDescription      This function is responsible for appointment category
    * @param                 user id and patient id
    * @return                True/False
    */
    public function getAppointmentCategory($userId)
    {
        $appointmentCategories = DB::table('appointment_category')->select('appointment_cat_id')->where(['user_id'=> $userId, 'is_deleted' => Config::get('constants.IS_DELETED_NO')])->first();
        if($appointmentCategories){
            return $appointmentCategories->appointment_cat_id;
        }else{
            return false;
        }
    }

    /**
    * @DateOfCreation        29 Apr 2018
    * @ShortDescription      This function is responsible for get timing id
    * @param                 user id and patient id
    * @return                True/False
    */
    public function getTimingId($userId,$weekDay,$finishTime)
    {
        $timingData = DB::table('timing')
                                ->select('timing_id')
                                ->where([
                                    'user_id'=> $userId, 
                                    'week_day'=> $weekDay,
                                    'is_deleted' => Config::get('constants.IS_DELETED_NO')])
                                ->where('start_time','<',$finishTime)
                                ->where('end_time','>',$finishTime)
                                ->first();
        if($timingData){
            return $timingData->timing_id;
        }else{
            return false;
        }
    }
    

    /**
    * @DateOfCreation        22 May 2018
    * @ShortDescription      This function is responsible to get the Timing by id
    * @param                 String $timing_id
    * @return                Array of time
    */
    public function isSlotAvailable($timing_id, $booking_date, $booking_time)
    {
        $whereDataBooking = array(
                        'timing_id'    => $timing_id,
                        'booking_date' => $booking_date,
                        'booking_time' => $booking_time,
                    );
        $result = array();

        $isSlotValid = Config::get('constants.NO_BOOKINGS_AVAILABLE');
        $total_booked = '0';

        $total_slot = DB::table('timing')
                        ->select('patients_per_slot')
                        ->where('timing_id', '=', $timing_id)
                        ->first();

        if(!empty($total_slot)){
            $total_booked = DB::table('bookings')
                            ->where($whereDataBooking)
                            ->count();
            if($total_booked < $total_slot->patients_per_slot){
                $isSlotValid = true;
            }
        }

        return $isSlotValid;
    }

    /**
    * @DateOfCreation        09 Aug 2018
    * @ShortDescription      This function is responsible to get the Timing by id
    * @param                 String $timing_id
    * @return                Array of time
    */
    public function userAlreadyBooked($timing_id, $booking_date, $booking_time, $user_id)
    {
        $whereDataBooking = array(
                        'timing_id'    => $timing_id,
                        'booking_date' => $booking_date,
                        'pat_id'       => $user_id,
                        'is_deleted'   => Config::get('constants.IS_DELETED_NO'),
                    );
        $result = array();

        $userAlreadyBooked = false;
        $total_booked = '0';

        $bookings = DB::table('bookings')
                ->select('booking_time')
                ->where($whereDataBooking)
                ->get();
        $total_booked = sizeof($bookings);
        if($total_booked > 0){
            $userAlreadyBooked = Config::get('constants.PATIENT_ALREADY_BOOKED_DAY');
            foreach($bookings as $slot){
                if($slot->booking_time == $booking_time){
                    $userAlreadyBooked = Config::get('constants.PATIENT_ALREADY_BOOKED_SLOT');
                    break;
                }
            }
        }
        return $userAlreadyBooked;
    }

    /**
    * @DateOfCreation        30 july 2018
    * @ShortDescription      This function is responsible to get the appointment by user id and user type
    * @return                Array of appointment
    */
    public function getAppointmentList($requestData)
    {  
        $data_limit = $requestData['pageSize']; 
        if($requestData['user_type'] == Config::get('constants.USER_TYPE_DOCTOR') || in_array($requestData['user_type'], Config::get('constants.USER_TYPE_STAFF')))
        {
            $whereData = [
                'bookings.user_id'=>$requestData['user_id'],
                'bookings.is_deleted'=>Config::get('constants.IS_DELETED_NO'),
                'booking_date'=>$requestData['appointmentDate']
            ];
        }

        if($requestData['user_type'] == Config::get('constants.USER_TYPE_PATIENT'))
        {
            $whereData = [
                'bookings.pat_id'=>$requestData['user_id'],
                'bookings.is_deleted'=>Config::get('constants.IS_DELETED_NO'),
                'booking_date'=>$requestData['appointmentDate']
            ];
        }

        $query = DB::table('bookings')
                ->join('clinics', 'bookings.clinic_id', '=', 'clinics.clinic_id')
                ->join('appointment_category', 'appointment_category.appointment_cat_id', '=', 'bookings.booking_reason');
        if($requestData['user_type'] == Config::get('constants.USER_TYPE_DOCTOR')){
            $query = $query->join('users', 'bookings.pat_id', '=', 'users.user_id');

        }else{
            $query = $query->join('users', 'bookings.user_id', '=', 'users.user_id');
        }

        $query =
            $query->leftjoin('booking_visit_relation', 'bookings.booking_id', '=', 'booking_visit_relation.booking_id')
                ->join('patients', 'bookings.pat_id', '=', 'patients.user_id')
                ->leftjoin('patients_visits', 'booking_visit_relation.visit_id', '=', 'patients_visits.visit_id')
                ->select('bookings.booking_id','bookings.booking_date', 'bookings.booking_time','bookings.user_id','bookings.pat_id','bookings.booking_status','clinics.clinic_address_line1', 'clinics.clinic_address_line2', 'clinics.clinic_landmark', 'clinics.clinic_pincode','booking_visit_relation.visit_id', 'patients_visits.status as visit_status',
                    'appointment_category.appointment_cat_name as booking_reason',DB::raw("CONCAT(users.user_firstname,' ',users.user_lastname) AS doc_name"), 'users.user_mobile', 'patients.pat_code','patients_visits.created_at', 'patients_visits.visit_number')
                ->where($whereData);
        $bookingsResult = array();

                /* Condition for Filtering the result */
        if(!empty($requestData['filtered'])){
            foreach ($requestData['filtered'] as $key => $value) {
                 $query = $query->where(function ($query) use ($value){
                                $query
                                ->orWhere('appointment_category.appointment_cat_name', 'ilike', "%".$value['value']."%");
                                if(!empty($value['value']) && strpos($value['value'], ':') !== false) {
                                    $format_time = $value['value'];
                                    if(strpos($format_time,'pm') !== false){
                                        $format_time = date('Hi',strtotime($format_time));
                                    }else if(strpos($format_time,'am') !== false){
                                        $format_time = date('Hi',strtotime($format_time));
                                    }
                                     $query
                                        ->orWhere('bookings.booking_time', 'ilike', "%".$format_time."%");
                                }else{
                                    $query->orWhere('bookings.booking_time', 'ilike', "%".$value['value']."%")
                                    ->orWhere('bookings.booking_time', 'ilike', "%".((int)$value['value']+12)."%");
                                }
                                $query
                                    ->orWhere('users.user_firstname', 'ilike', "%".$value['value']."%")
                                    ->orWhere('users.user_lastname', 'ilike', "%".$value['value']."%");
                            });
            }
        }


        if($requestData['page'] > 0){
            $offset = $requestData['page']*$data_limit;
        }else{
            $offset = 0;
        }
       
        $bookingsResult['pages'] = ceil($query->count()/$data_limit);
        $bookingsResult['date'] = $requestData['appointmentDate'];
        $bookingsResult['result'] = $query->orderBy('bookings.booking_time', 'asc')
                                    ->offset($offset)
                                    ->limit($data_limit)
                                    ->get()
                                    ->map(function($bookings){
                                        $bookings->user_id = $this->securityLibObj->encrypt($bookings->user_id);
                                        $bookings->booking_id = $this->securityLibObj->encrypt($bookings->booking_id);
                                        if($bookings->visit_id){
                                            $bookings->visit_id = $this->securityLibObj->encrypt($bookings->visit_id);
                                        }
                                        $bookings->pat_id = $this->securityLibObj->encrypt($bookings->pat_id);
                                        
                                        return $bookings;
                                    });
       if(!empty($bookingsResult)){
            return $bookingsResult;
        }
        return false;
    }

    /**
    * @DateOfCreation        30 july 2018
    * @ShortDescription      This function is responsible to get the appointment by user id and user type
    * @return                Array of appointment
    */
    public function getTodayAppointmentList($user_id, $user_type)
    {
        if($user_type == Config::get('constants.USER_TYPE_DOCTOR') || in_array($user_type, Config::get('constants.USER_TYPE_STAFF')))
        {
            $whereData = [
                'bookings.user_id'=>$user_id,
                'bookings.is_deleted'=>Config::get('constants.IS_DELETED_NO'),
                'booking_date'=>date(Config::get('constants.DB_SAVE_DATE_FORMAT'))
            ];
        }

        $bookings = DB::table('bookings')
            ->select('bookings.booking_id','bookings.booking_date', 'bookings.booking_time','users.user_firstname','users.user_lastname','bookings.user_id','bookings.pat_id','clinics.clinic_address_line1', 'clinics.clinic_address_line2', 'clinics.clinic_landmark', 'clinics.clinic_pincode','booking_visit_relation.visit_id')
            ->where($whereData)
            ->leftjoin('booking_visit_relation', 'bookings.booking_id', '=', 'booking_visit_relation.booking_id')
            ->join('users', 'bookings.pat_id', '=', 'users.user_id')
            ->join('clinics', 'bookings.clinic_id', '=', 'clinics.clinic_id')
            ->orderBy('bookings.booking_time', 'asc')
            ->get()
            ->map(function($bookings){
                $bookings->user_id = $this->securityLibObj->encrypt($bookings->user_id);
                $bookings->booking_id = $this->securityLibObj->encrypt($bookings->booking_id);
                if($bookings->visit_id){
                    $bookings->visit_id = $this->securityLibObj->encrypt($bookings->visit_id);
                }
                $bookings->pat_id = $this->securityLibObj->encrypt($bookings->pat_id);
                $bookings->address = $bookings->clinic_address_line1.', '.$bookings->clinic_address_line2.' '.$bookings->clinic_landmark.', '.$bookings->clinic_pincode;
                return $bookings;
            });
        if(!empty($bookings)){
            return $bookings;
        }
        return false;
    }

    /**
     * @DateOfCreation        2 Aug 2018
     * @ShortDescription      This function is responsible to create relation between doctor ans patient
     * @param                 $tablename - insertion table name 
     * @param                 Array $insertData 
     * @return                String {booking visit relatrion id}
     */
    public function createBookingVisitRelation($tablename, $insertData){
        // @var Boolean $response
        // This variable contains insert query response
        $response = false;
        
        $response = $this->dbInsert($tablename, $insertData);
        if($response){
            $relId = DB::getPdo()->lastInsertId();
            return $relId;
        }else{
            return $response;
        }     
    }

    /**
     * @DateOfCreation        6 Aug 2018
     * @ShortDescription      This function is responsible to change booking status on 
                              patient visit
     * @param                 $tablename - insertion table name 
     * @param                 Array $insertData 
     * @return                String {booking visit relatrion id}
     */
    public function updateBookingState($bookingId, $bookingStatus = 0){
        // @var Boolean $response
        // This variable contains update query response
        $tablename = 'bookings';
        $requestData = $whereData = [];
        $response = false;
        if(!empty($bookingId) && $bookingStatus != 0){
            $requestData['booking_status'] = $bookingStatus;
            $whereData['booking_id'] = $bookingId;
            $response = $this->dbUpdate($tablename, $requestData, $whereData);
        }
        return $response;
    }

    /**
     * @DateOfCreation        6 Aug 2018
     * @ShortDescription      This function is responsible to get Appointment List of user
                              patient visit
     * @param                 $startDate - Calendar show start date  
     * @param                 $endDate - Calendar show end date    
     * @param                 $userId - Calendar show for doctor id    
     * @param                 Array $extra  if specail functionlity performing otherwise empty array set
     * @return                array resousece and events
     */
    public function getTimeSlot($startDate,$endDate,$userId,$extra=[]){
        $slotSettings = $this->manageCalendarObj->getManageCalendarRecordByUserId($userId);
        $timeing = [
            'start_time' => !empty($slotSettings) &&  isset($slotSettings->mcs_start_time) ? $slotSettings->mcs_start_time : Config::get('constants.CLINIC_DEFAULT_START_TIME'),
            'end_time' => !empty($slotSettings) && isset($slotSettings->mcs_end_time) ? $slotSettings->mcs_end_time :Config::get('constants.CLINIC_DEFAULT_END_TIME'),
            'slot_duration' =>!empty($slotSettings) && isset($slotSettings->mcs_slot_duration)?  $slotSettings->mcs_slot_duration : Config::get('constants.CALENDAR_SLOT_DURATION')
        ];
        $extraTimeSlotCreat = [
        'time_slot_format' => 'h:i A',
        'booking_calculation_disable' => '1',
        ];
        $timeSlots = $this->searchObj->createTimeSlot((object) $timeing, date(Config::get('constants.DB_SAVE_DATE_FORMAT')),$extraTimeSlotCreat);
        $arrangeSlot = !empty($timeSlots) ? array_pluck($timeSlots,'slot_time_format','slot_time') :[];
        return ['slots' => $arrangeSlot,'slots_duration'=>$timeing['slot_duration']];
    }

    /**
     * @DateOfCreation        6 Aug 2018
     * @ShortDescription      This function is responsible to get Appointment List of user
                              patient visit
     * @param                 $startDate - Calendar show start date  
     * @param                 $endDate - Calendar show end date    
     * @param                 $userId - Calendar show for doctor id    
     * @param                 Array $extra  if specail functionlity performing otherwise empty array set
     * @return                array resousece and events
     */
    public function getAppointmentListCalendar($startDate,$endDate,$userId,$extra=[]){
        $clinicId = isset($extra['clinic_id']) && $extra['clinic_id']!='' ? $extra['clinic_id'] :''; 
        $viewType = isset($extra['view_type']) && $extra['view_type']!='' ? $extra['view_type'] :''; 
        $timeSlotsExtara = [];
     
        $timeSlots = $this->getTimeSlot($startDate,$endDate,$userId,$timeSlotsExtara);
        $extraEvents = [
            'clinic_id' => $clinicId,
            'slot_data' => $timeSlots['slots'],
            'slot_duration' => $timeSlots['slots_duration'],

        ];
        $eventsData = $this->getAppointmentListEvents($startDate,$endDate,$userId,$extraEvents);
        $eventsCalendarData = [];
        $resourcesCalendarData = [];
        $checkEventData = count($eventsData)>0 ? count(array_filter((array)$eventsData[0])) > 2?[1]:[] :[];

        if (count($eventsData)>0 && !empty($checkEventData)) {
            $colorDataProcess = ['1'=>Config::get('constants.CALENDAR_NOT_STARTED_COLOR'),'2'=>Config::get('constants.CALENDAR_INPROGRESS_COLOR'),'3'=>Config::get('constants.CALENDAR_COMPLETED_COLOR')];
            $eventsData = $this->utilityLibObj->changeObjectToArray($eventsData);
            $events     = $eventsData[0];
            $doctorDetails  = $this->doctorprofileObj->getProfileDetail($userId);
            $doctorDetails  = $this->utilityLibObj->changeObjectToArray($doctorDetails); 
            $doctorName     = trans('Doctors::messages.doctors_title_name').' '.$doctorDetails['user_firstname'].' '.$doctorDetails['user_lastname'];

            $patientDetails = $this->patientsObj->patientListQuery($userId);
            $patientDetails = $patientDetails->get()->toArray();
            $patientDetails = !empty($patientDetails) ? $this->utilityLibObj->changeArrayKey($patientDetails,'user_id'):[]; //patientDetails array index arrange by user id
            $patAppointmentReasonsData = $this->appointmentCategoryObj->getAppointmentReasons(['user_id'=>$userId],false);
            $patAppointmentReasonsData = $this->utilityLibObj->changeObjectToArray($patAppointmentReasonsData);
            $patAppointmentReasonsData = array_pluck($patAppointmentReasonsData,'appointment_cat_name','appointment_cat_id');

            $patTimingSlotData = $this->timingObj->getAllTimingListByUserId($userId,false);
            $patTimingSlotData = $this->utilityLibObj->changeObjectToArray($patTimingSlotData);
            $patTimingSlotData = array_pluck($patTimingSlotData,'slot_duration','timing_id');
            $patVisitData = $this->getBookingVisitsIdByUserId($userId,false);
            $patTimingSlotData = $this->utilityLibObj->changeArrayKey($patVisitData,'booking_id');


            foreach ($timeSlots['slots'] as $key => $value) {
                $resourcesCalendarData[] = ['id' => $key,'name' => $value];
                if((isset($events['slot'.$key]) && empty($events['slot'.$key])) || (!isset($events['slot'.$key]))){
                    continue;
                }
               $data            = explode('#',$events['slot'.$key]);
               $bookingIds      = isset($data[0]) ? explode(',', $data[0]) : [];
               $clinicsIds      = isset($data[1]) ? explode(',', $data[1]) : ['0'];
               $bookingTime     = isset($data[2]) ? explode(',', $data[2]) : [];
               $bookingDate     = isset($data[3]) ? explode(',', $data[3]) : [];
               $bookingStatus   = isset($data[4]) ? explode(',', $data[4]) : [];
               $bookingreason   = isset($data[5]) ? explode(',', $data[5]) : [];
               $patId           = isset($data[6]) ? explode(',', $data[6]) : [];
               $timingId           = isset($data[7]) ? explode(',', $data[7]) : [];
               if (!empty($bookingIds)) {
                foreach ($bookingIds as $keyBooking => $rowBooking) {

                    // IF BOOKING DATE IS PASSED AND VISIT NOT STARTED, THEN RECORD NOT SHOWING ON CALENDER 
                    if( (isset($bookingDate[$keyBooking]) && strtotime($bookingDate[$keyBooking]) < strtotime(date('Y-m-d'))) && $bookingStatus[$keyBooking] == Config::get('constants.BOOKING_NOT_STARTED') ){
                        continue;
                    }

                    $temp = [];
                    $temp['id'] = $rowBooking;
                    $slotTiming = '15';
                    $start_time = isset($bookingDate[$keyBooking]) && isset($bookingTime[$keyBooking]) ? $bookingDate[$keyBooking].' '. $this->utilityLibObj->changeTimingFormat($bookingTime[$keyBooking],'H:i:s') : '';
                    $end_time = date('Y-m-d H:i:s',strtotime('+'.$slotTiming.' minutes', strtotime($start_time)));
                    $patName = isset($patientDetails[$patId[$keyBooking]]) ? $this->staticDataObj->getTitleNameById($patientDetails[$patId[$keyBooking]]['pat_title']).' '.$patientDetails[$patId[$keyBooking]]['user_firstname'].' '.$patientDetails[$patId[$keyBooking]]['user_lastname'] : '';
                    $patCode = isset($patientDetails[$patId[$keyBooking]]) ? $patientDetails[$patId[$keyBooking]]['pat_code'] :'';
                    $patGender = isset($patientDetails[$patId[$keyBooking]]) ? $patientDetails[$patId[$keyBooking]]['user_gender'] :'';
                    $patGenderName = isset($patientDetails[$patId[$keyBooking]]) ? $this->staticDataObj->getGenderNameById($patGender) :'';
                    $patMobile = isset($patientDetails[$patId[$keyBooking]]) ? $patientDetails[$patId[$keyBooking]]['user_mobile'] : '';
                    $patMobileCode = isset($patientDetails[$patId[$keyBooking]]) ? $patientDetails[$patId[$keyBooking]]['user_country_code'] :'';
                    $patEmail = isset($patientDetails[$patId[$keyBooking]]) ? $patientDetails[$patId[$keyBooking]]['user_email'] : '';
                    $patDob = isset($patientDetails[$patId[$keyBooking]]) ? $patientDetails[$patId[$keyBooking]]['pat_dob']:'';
                    $param= isset($patientDetails[$patId[$keyBooking]]) && !empty($patientDetails[$patId[$keyBooking]]['pat_profile_img']) ? $patientDetails[$patId[$keyBooking]]['pat_profile_img']:Config::get('constants.DEFAULT_IMAGE_NAME');
                    $imgUrl = url('api/patient-profile-image/'.$this->securityLibObj->encrypt($param));
                    $patAppointment = date(Config::get('constants.CALENDAR_PATIENT_POPUP_DATE'),strtotime($start_time));
                    $patAge = !empty($patDob) ? $this->dateTimeLibObj->ageCalculation($patDob,Config::get('constants.DB_SAVE_DATE_FORMAT'),'y') :'';
                    $patAge= !empty($patAge) && isset($patAge['code']) && $patAge['code']==Config::get('restresponsecode.SUCCESS') ? $patAge['result'] : '';
                    $patAppointmentReasons = isset($patAppointmentReasonsData[$bookingreason[$keyBooking]]) ? $patAppointmentReasonsData[$bookingreason[$keyBooking]]:'';
                    
                    $temp['start'] = $start_time;
                    $temp['end'] = $start_time;
                    $temp['resourceId'] = $key;
                    if(strtolower($viewType) == strtolower('Day')){
                        $temp['groupId'] = 'r1';
                        $temp['groupName'] = 'Appointment';
                        $temp['end'] = $end_time;
                    }
                    $temp['title'] = $patName;
                    $temp['bgColor'] =  isset($colorDataProcess[$bookingStatus[$keyBooking]]) ? '#'.$colorDataProcess[$bookingStatus[$keyBooking]] :'#D9D9D9';
                    $temp['movable'] =  false;
                    $temp['details'] = [
                    'name' => $patName,
                    'code' => $patCode,
                    'gender' => $patGenderName,
                    'mobile' => $patMobileCode.$patMobile,
                    'email' => $patEmail,
                    'age' => $patAge.' Year\'s',
                    'appointment_data' => $patAppointment,
                    'image' => $imgUrl,
                    'doctor_name' => $doctorName,
                    'appointment_reason' => $patAppointmentReasons,
                    'booking_id' => $this->securityLibObj->encrypt($rowBooking),
                    'pat_id' => $this->securityLibObj->encrypt($patId[$keyBooking]),
                    'visit_id' => isset($patTimingSlotData[$rowBooking]) && isset($patTimingSlotData[$rowBooking]['visit_id']) ? $this->securityLibObj->encrypt($patTimingSlotData[$rowBooking]['visit_id']):null
                    ] ;
                    $eventsCalendarData[] = $temp; 


                }

               }
            }
        }else{
            foreach ($timeSlots['slots'] as $key => $value) {
                $resourcesCalendarData[] = ['id' => $key,'name' => $value];
            }
            if(strtolower($viewType) == strtolower('Day')){
                $eventsCalendarData[] = [
                'id'=>'0',
                'groupId'=>'r1',
                'groupName'=>'Appointment',
                'resourceId' => $key,
                'start' =>'2018-09-04 00:00:00',
                'end'=>'2018-09-04 00:00:00',
                'title' => ''

                ];
            }

        }
        return ['calendarEvents' => $eventsCalendarData , 'calendarResources' => $resourcesCalendarData,'calendarSlotDuration' => $timeSlots['slots_duration']];
    }

    /**
     * @DateOfCreation        26 June 2018
     * @ShortDescription      This function is responsible to get all information  for Analytics show
     * @param                 integer $visitId   
     * @return                object Array of medical history records
     */
    public function getAppointmentListEvents($startDate,$endDate,$userId,$extraEvents) 
    {  
        $slotData = isset($extraEvents['slot_data']) ?  $extraEvents['slot_data']:[];
        $clinicId = isset($extraEvents['clinic_id']) ?  $extraEvents['clinic_id']:0;
        $slotDuration = isset($extraEvents['slot_duration']) ?  $extraEvents['slot_duration']:Config::get('constants.CALENDAR_SLOT_DURATION');
        $slotDuration = $slotDuration -1;
        $whereData = [
           'isDeleted' => Config::get('constants.IS_DELETED_NO'),
           'startDate' => $startDate,
           'endDate' => $endDate,
           'userId' => $userId,
           
         ];
         $startslots = array_keys($slotData);
         $endslots = array_map(function($row) use($slotDuration){
            $duration = $row+$slotDuration;
            if(strlen($duration)==0){
                $duration = '0000';
            }elseif(strlen($duration)==1){
                $duration = '000'.$duration;
            }elseif(strlen($duration)==2){
                $duration = '00'.$duration;
            }elseif(strlen($duration)==3){
                $duration = '0'.$duration;
            }
            return $duration;
         }, $startslots);
         
        $prefixedArrayStart = preg_filter('/^/', 'startslot', $startslots);
        $prefixedArrayEnd = preg_filter('/^/', 'endslot', $startslots);
        $slotDatasEnd = array_combine($prefixedArrayEnd, $endslots);
        $slotDatasStart = array_combine($prefixedArrayStart, $startslots);
        $whereData = array_merge($whereData,$slotDatasStart,$slotDatasEnd);

        $dataQuery = "Select concat_ws('#','booking_id','clinic_id','booking_time','booking_date','booking_status','booking_reason','pat_id') as format,k.* From ( select user_id, ";

        $clinicIdCondition = !empty($clinicId) && is_numeric($clinicId) ? " and d.clinic_id=:clinicId ": " ";
        if(!empty($clinicId)){
            $whereData['clinicId'] = $clinicId;
        }
        
        foreach ($startslots as $key => $row) {

            $dataQuery .= "( select concat_ws('#',STRING_AGG(booking_id::character varying,','), STRING_AGG(clinic_id::character varying,','), STRING_AGG(booking_time::character varying,','), STRING_AGG(booking_date::character varying,','), STRING_AGG(booking_status::character varying,','), STRING_AGG(booking_reason::character varying,','), STRING_AGG(pat_id::character varying,','),STRING_AGG(timing_id::character varying,',') ) as slot from bookings as d where d.booking_time between :startslot".$row." and :endslot".$row." and d.is_deleted=:isDeleted and b.user_id=d.user_id ".$clinicIdCondition." and d.booking_date between :startDate and :endDate ) as slot".$row." ,";
        }
        $dataQuery = rtrim($dataQuery,',');
        $dataQuery .= " from bookings b where b.user_id=:userId and b.booking_date between :startDate and :endDate group by user_id) as k";

        $res =  DB::select(DB::raw($dataQuery), $whereData);
        return $res; 
    }

    public function getTimeSlotForBooking($startTime,$endTime,$userId,$extra){
        $clinicId = isset($extra['clinic_id']) ? $extra['clinic_id'] : '';
        $weekDay = isset($extra['week_day']) ? $extra['week_day'] : [];
        $weekDay = !empty($weekDay) && !is_array($weekDay) && is_numeric($weekDay) ? [$weekDay] :[];
        $whereData = [
            'is_deleted' => Config::get('constants.IS_DELETED_NO'),
            'user_id' => $userId
        ];
        if(!empty($clinicId)){
            $whereData['clinic_id']  = $clinicId;
        }
        $timingRes = DB::table($this->_timingSlotTable)
            ->select('timing_id','user_id', 'start_time','end_time','slot_duration','patients_per_slot','clinic_id')
            ->where($whereData)
            ->where('start_time','<',$endTime)
            ->where('end_time','>',$startTime);
        if(!empty($weekDay)){
            $timingRes = $timingRes->whereIn('week_day',$weekDay);
        }
        $timingRes = $timingRes->orderBy('start_time','asc')->get()
        ->map(function($timings){
            $timings->timing_id = $this->securityLibObj->encrypt($timings->timing_id);
            $timings->user_id = $this->securityLibObj->encrypt($timings->user_id);
            $timings->clinic_id = $this->securityLibObj->encrypt($timings->clinic_id);
            return $timings;
        });
        return $timingRes;
    }

    /**
     * @DateOfCreation        18 June 2018
     * @ShortDescription      This function is responsible to save record for the Patient Medication History
     * @param                 array $requestData   
     * @return                integer Patient Medication History id
     */
    public function getTableName()
    {
        return $this->table;
    }

    /**
     * @DateOfCreation        18 June 2018
     * @ShortDescription      This function is responsible to save record for the Patient Medication History
     * @param                 array $requestData   
     * @return                integer Patient Medication History id
     */
    public function getTablePrimaryIdColumn()
    {
        return $this->primaryKey;
    }

    /**
     * @DateOfCreation        21 June 2018
     * @ShortDescription      This function is responsible to check the Visit  wefId exist in the system or not
     * @param                 integer $wefId   
     * @return                Array of status and message
     */
    public function isPrimaryIdExist($primaryId){
        $primaryIdExist = DB::table($this->table)
                        ->where($this->primaryKey, $primaryId)
                        ->exists();
        return $primaryIdExist;
    }

    /**
     * @DateOfCreation        11 June 2018
     * @ShortDescription      This function is responsible to Delete Work Environment data
     * @param                 integer $wefId   
     * @return                Array of status and message
     */
    public function doDeleteRequest($primaryId,$updateData=[])
    {
        $updateDataInit = [ 'is_deleted' => Config::get('constants.IS_DELETED_YES') ];
        $updateDataInit = array_merge($updateDataInit,$updateData);
        $queryResult = $this->dbUpdate( $this->table, $updateDataInit
                                        , 
                                        [$this->primaryKey => $primaryId]
                                    );

        if($queryResult){
            return true;
        }
        return false;
    }

    /**
    * @DateOfCreation        12 July 2018
    * @ShortDescription      This function is responsible to get the Booking by id
    * @param                 String $booking_id
    * @return                Array of time
    */
    public function getBookingVisitsIdByUserId($userId,$encrypt=true)
    {
        $queryResult = DB::table($this->table)
                        ->select('bookings.booking_id', 'bookings.user_id', 'bookings.pat_id', 'bookings.clinic_id', 'bookings.booking_date', 'bookings.booking_time', 'bookings.booking_reason','booking_visit_relation.visit_id', 'bookings.booking_status')
                        ->join('booking_visit_relation', 'bookings.booking_id', '=', 'booking_visit_relation.booking_id')
                        ->where([
                            'bookings.is_deleted' => Config::get('constants.IS_DELETED_NO'),
                            'booking_visit_relation.is_deleted' =>Config::get('constants.IS_DELETED_NO'),
                            'bookings.user_id'=>$userId
                        ])
                        ->get();
        if($encrypt){
            $queryResult = $queryResult->map(function($bookings){
                $bookings->user_id = $this->securityLibObj->encrypt($bookings->user_id);
                $bookings->booking_id = $this->securityLibObj->encrypt($bookings->booking_id);
                if($bookings->visit_id){
                    $bookings->visit_id = $this->securityLibObj->encrypt($bookings->visit_id);
                }
                $bookings->pat_id = $this->securityLibObj->encrypt($bookings->pat_id);
                
                return $bookings;
            });
        }
        return $queryResult;
    }

    /**
    * @DateOfCreation        12 July 2018
    * @ShortDescription      This function is responsible to get the Booking by id
    * @param                 String $booking_id
    * @return                Array of time
    */
    public function getPatientNextVisitSchedule($patId){
        $queryResult = DB::table($this->table)
                    ->select('booking_date','booking_time')
                    ->where('booking_date','>',date('Y-m-d'))
                    ->where([
                        'pat_id' => $patId, 
                        'is_deleted' => Config::get('constants.IS_DELETED_NO'),
                        'booking_status' => Config::get('constants.BOOKING_NOT_STARTED')
                    ])
                    ->first();
        if(!empty($queryResult)){
            return $queryResult;
        }else{
            return false;
        }
    }
}

