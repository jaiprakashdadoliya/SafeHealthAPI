<?php

namespace App\Modules\Search\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Traits\Encryptable;
use App\Libraries\SecurityLib;
use App\Libraries\FileLib;
use Config;
use File;
use App\Modules\Doctors\Models\Doctors as Doctors;

class Search extends Model {
    use Encryptable;

    // @var Array $encryptedFields
    // This protected member contains fields that need to encrypt while saving in database
    protected $encryptable = [];
    
    /**
     *@ShortDescription Override the Table.
     *
     * @var string
    */
    protected $table = 'users';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // Init security library object
        $this->securityLibObj = new SecurityLib();  
        $this->FileLib = new FileLib();

        $this->doctorObj = new Doctors();
    }

    /**
    * @DateOfCreation        13 July 2018
    * @ShortDescription      This function is responsible to get doctors
    * @param                 String $searchData
                             String $city_id  
    * @return                clinics
    */
    public function getDoctors($searchData, $city_id)
    {
        $selectData =  ['users.user_id','users.user_firstname','users.user_lastname','doctors.doc_slug', 'doctors.doc_profile_img',];
        $whereData   =  array(
                            'doctors.is_deleted'      => Config::get('constants.IS_DELETED_NO'),
                            'doctors.city_id'       => $city_id
                        );
        $query =     DB::table('doctors')
                    ->join('users','users.user_id', '=', 'doctors.user_id')
                    ->select($selectData)
                    ->where($whereData)
                    ->where(function ($query) use ($searchData){
                    $query->orWhere('users.user_firstname', 'ilike', '%'.$searchData.'%');
                    $query->orWhere('users.user_lastname', 'ilike', '%'.$searchData.'%');
                            });
        return  $doctors =
                        $query
                        ->limit(Config::get('constants.SEARCH_DOCTORS_LIMIT'))
                        ->get()
                        ->map(function($doctors){
                            $doctors->doc_spec_detail = $this->doctorObj->getDoctorSpecialisation($doctors->user_id);
                            unset($doctors->user_id);
                            return $doctors;
                        });
    }

    /**
    * @DateOfCreation        13 July 2018
    * @ShortDescription      This function is responsible to get clinics
    * @param                 String $searchData
                             String $city_id   
    * @return                clinics
    */
    public function getClinics($searchData, $city_id)
    {
        $selectData =  ['clinics.clinic_id','clinics.clinic_name','clinics.clinic_address_line1','doctors.doc_slug'];
        $whereData   =  array(
                            'doctors.is_deleted'      => Config::get('constants.IS_DELETED_NO'),
                            'doctors.city_id'       => $city_id
                        );
        $query =     DB::table('doctors')
                    ->join('clinics','clinics.user_id', '=', 'doctors.user_id')
                    ->select($selectData)
                    ->where($whereData)
                    ->where(function ($query) use ($searchData){
                     $query->orWhere('clinics.clinic_name', 'ilike', '%'.$searchData.'%');
                            });
        return $clinics =  $query
                            ->limit(Config::get('constants.SEARCH_CLINIC_LIMIT'))
                            ->get()
                            ->map(function($clinic){
                            $clinic->clinic_id = $this->securityLibObj->encrypt($clinic->clinic_id);
                                return $clinic;
                            });
    }
    /**
    * @DateOfCreation        16 August 2018
    * @ShortDescription      This function is responsible to get services
    * @param                 String $searchData
                             String $city_id   
    * @return                services
    */
    public function getServices($searchData, $city_id)
    {
        $selectData =  ['services.srv_id','services.srv_name',DB::raw('services.user_id, (SELECT spl_id FROM doctors_specialisations WHERE doctors_specialisations.user_id=services.user_id limit 1) AS spl_id')];
        $whereData   =  array(
                            'doctors.is_deleted'      => Config::get('constants.IS_DELETED_NO'),
                            'doctors.city_id'         => $city_id
                        );
        $query =     DB::table('doctors')
                    ->join('services','services.user_id', '=', 'doctors.user_id')
                    ->select($selectData)
                    ->where($whereData)
                    ->where(function ($query) use ($searchData){
                     $query->orWhere('services.srv_name', 'ilike', '%'.$searchData.'%');
                            });
        return $services =  $query->get()
                                ->map(function($services){
                                $services->spl_id = $this->securityLibObj->encrypt($services->spl_id);
                                $services->user_id = $this->securityLibObj->encrypt($services->user_id);
                                $services->srv_id = $this->securityLibObj->encrypt($services->srv_id);
                                return $services;
                                });
    }
    

    /**
    * @DateOfCreation        13 July 2018
    * @ShortDescription      This function is responsible to get clinics
    * @param                 String $searchData
                             String $city_id   
    * @return                clinics
    */
    public function getCommonTags($searchData, $city_id)
    {
        $selectData =  ['doctor_specialisations_tags.doc_spl_tag_id','doctor_specialisations_tags.specailisation_tag','doctors.doc_slug','doctor_specialisations_tags.doc_spl_id',DB::raw('doctor_specialisations_tags.user_id, (SELECT spl_id FROM doctors_specialisations WHERE doctors_specialisations.doc_spl_id=doctor_specialisations_tags.doc_spl_id limit 1) AS spl_id')];
        $whereData   =  array(
                            'doctors.is_deleted'      => Config::get('constants.IS_DELETED_NO'),
                            'doctors.city_id'       => $city_id
                        );
        $query =     DB::table('doctors')
                    ->join('doctor_specialisations_tags','doctor_specialisations_tags.user_id', '=', 'doctors.user_id')
                    ->select($selectData)
                    ->where($whereData)
                    ->where(function ($query) use ($searchData){
                     $query->orWhere('doctor_specialisations_tags.specailisation_tag', 'ilike', '%'.$searchData.'%');
                            });
        return $doctor_specialisations_tags =  $query
                            ->get()
                            ->map(function($doctor_specialisations_tags){
                                $doctor_specialisations_tags->doc_spl_tag_id = $this->securityLibObj->encrypt($doctor_specialisations_tags->doc_spl_tag_id);
                                $doctor_specialisations_tags->spl_id = $this->securityLibObj->encrypt($doctor_specialisations_tags->spl_id);
                                    return $doctor_specialisations_tags;
                                });
    }



    /**
    * @DateOfCreation        12 July 2018
    * @ShortDescription      This function is responsible to specailisation list
    * @param                 Array $requestData   
    * @return                All result with clinic, speciality and doctors
    */
    public function doctorsSpecialisation($requestData)
    {
        $result = [];
        $doctors = [];
        $clinic = [];
        $services = [];
        $specialisationsTags = [];
        $city_id = $this->securityLibObj->decrypt($requestData['city_id']);
        $selectData =  ['specialisations.spl_id','specialisations.spl_name'];
        $whereData   =  array(
                            'doctors.is_deleted'      => Config::get('constants.IS_DELETED_NO'),
                            'doctors.city_id'       => $city_id
                        );        
        $query =    DB::table('doctors')
                   ->join('doctors_specialisations','doctors_specialisations.user_id', '=', 'doctors.user_id')
                    ->join('specialisations','specialisations.spl_id', '=', 'doctors_specialisations.spl_id')
                    ->select($selectData)
                    ->where($whereData)
                    ->where('doctors_specialisations.is_deleted', '=', Config::get('constants.IS_DELETED_NO'));

        if(!empty($requestData['search'])){
            $searchData = $requestData['search'];
            $doctors   = $this->getDoctors($requestData['search'], $city_id);
            $clinic = $this->getClinics($requestData['search'], $city_id);
            $services = $this->getServices($requestData['search'], $city_id);
            $specialisationsTags = $this->getCommonTags($requestData['search'], $city_id);
            $query = $query->where(function ($query) use ($searchData){
                                $query
                                ->orWhere('specialisations.spl_name', 'ilike', '%'.$searchData.'%');
                            });
        }
        $speciality = $query->distinct()->groupBy(['specialisations.spl_id','specialisations.spl_name'])
                        ->limit(Config::get('constants.SEARCH_SPECIALISATIONS_LIMIT'))
                        ->get()
                        ->map(function($specailisation){
                            $specailisation->spl_id = $this->securityLibObj->encrypt($specailisation->spl_id);
                            return $specailisation;
                        });
        $result[] = [
                        'speciality' => $speciality,
                        'doctors'    => $doctors,
                        'clinic'     => $clinic,
                        'tags'       => $specialisationsTags,
                        'services'   => $services
                    ];
        return $result;
     }

    /**
    * @DateOfCreation        12 July 2018
    * @ShortDescription      This function is responsible to get the city list
    * @param                 Array $requestData   
    * @return                cities
    */
    public function getSearchCityResult($requestData)
    {   
        $selectData =  ['cities.id as city_id', 'cities.name as city_name'];
         
        $whereData   =  array(
                            'doctors.is_deleted'   => Config::get('constants.IS_DELETED_NO')
                        );
        $query = DB::table('cities')
                    ->join('doctors','doctors.city_id', '=', 'cities.id')
                    ->select($selectData)
                    ->where($whereData);
   
        if(!empty($requestData['query'])){
            $searchData = $requestData['query'];
            $query = $query->where('cities.name', 'ilike', '%'.$searchData.'%');
        }
        $cities = $query->distinct()->groupBy(['cities.id'])
                   ->get()
                   ->map(function($cities){
                            $cities->city_id = $this->securityLibObj->encrypt($cities->city_id);
                            return $cities;
                        });
        return $cities;
    }
    
    /**
    * @DateOfCreation        16 July 2018
    * @ShortDescription      This function is responsible to doctors list
    * @param                 Array $requestData   
    * @return                specility
    */
    public function getDoctorsList($requestData)
    {   
        $city_id = $this->securityLibObj->decrypt($requestData['ids']['cityId']);
        $spl_id = $this->securityLibObj->decrypt($requestData['ids']['splId']);

        $srv_id = $this->securityLibObj->decrypt($requestData['ids']['srvId']);
        $spl_tag_id = $this->securityLibObj->decrypt($requestData['ids']['splTagId']);

        $detected_lat = $requestData['filters']['detected_lat'];
        $detected_lng= $requestData['filters']['detected_lng'];
        $filter_gender = $requestData['filters']['filter_gender'];
        $searchResult = array();
        
        $data_limit = Config::get('constants.DATA_LIMIT');
        $city = DB::table('cities')->select('name as city_name')->where(['id'=>$city_id])->first();
        $specailisation = DB::table('specialisations')->select('spl_name')->where(['spl_id'=>$spl_id])->first();
    
        $whereData = array(
                        'is_deleted'        => Config::get('constants.IS_DELETED_NO'),
                        'city_id'           => $city_id,
                        'spl_id'            => $spl_id,
                        'srv_id'            => $srv_id,
                        'spl_tag_id'        => $spl_tag_id,
                        'filter_gender'     => $filter_gender,

                    );
        DB::enableQueryLog();
        $prepareQuery = "select users.user_id, users.user_firstname, users.user_lastname, doctors.doc_consult_fee, doctors.doc_address_line1, doctors.doc_address_line2, doctors.doc_profile_img, doctors.city_id, doctors.doc_slug, specialisations.spl_name, doctors_specialisations.spl_id, 
            (SELECT string_agg(DISTINCT doctors_degrees.doc_deg_name, ', ') as doc_deg_name FROM doctors_degrees WHERE doctors_degrees.user_id = users.user_id AND doctors_degrees.is_deleted = :is_deleted) as doc_deg_name, 
            (SELECT ROUND(AVG(overall),0) as overall_average FROM review_rating WHERE review_rating.user_id = users.user_id AND review_rating.is_deleted = 2) as overall_average, 
            (SELECT COUNT(review_rating.review_user_id) as doc_review_count FROM review_rating WHERE review_rating.user_id = users.user_id AND review_rating.is_deleted = 2) as doc_review_count, 
            clinics.clinic_name, clinics.clinic_id from users 
            inner join doctors on doctors.user_id = users.user_id 
            left join clinics on clinics.user_id = users.user_id 
            inner join doctors_specialisations on doctors_specialisations.user_id = users.user_id 
            inner join specialisations on specialisations.spl_id = doctors_specialisations.spl_id";

        /* Join services table optional */            
        if(!empty($srv_id)){
            $prepareQuery = "inner join services on services.user_id = users.user_id"; 
        }
        /* Join Doctor specialisations table optional */  
        if(!empty($spl_tag_id)){
            $prepareQuery = "doctor_specialisations_tags','doctor_specialisations_tags.user_id', '=', 'users.user_id'";
        }
            
        $prepareQuery = "where (users.is_deleted =:is_deleted and doctors.city_id =:city_id and doctors_specialisations.spl_id =:spl_id and doctors_specialisations.is_deleted =:is_deleted)";

        if(!empty($srv_id)){
            $prepareQuery = " and services.srv_id =:srv_id";
        }
        if(!empty($spl_tag_id)){
            $prepareQuery = " and doctor_specialisations_tags.doc_spl_tag_id=:spl_tag_id";
        }
        /* Sorting option added */
        if(!empty($requestData['sortBy']['doctor_name']) && $requestData['sortBy']['doctor_name'] == 'name'){
            $prepareQuery = "order by users.user_firstname desc";
        }

        /* Filtering option added */
        if(!empty($requestData['filters']['filter_gender']) && $requestData['filters']['filter_gender'] != 4){
            $prepareQuery = " and users.user_gender=:filter_gender";
        }
        
        if(!empty($requestData['filters']['filter_distance'])){
            $query = $query->whereRaw("{$distanceFormula} < ?", [$requestData['filters']['filter_distance']]);
        }
        switch ($requestData['filters']['filter_consulting_fee']) {
            case 1:
                $query = $query->where('doctors.doc_consult_fee','<',100);
                break;
            case 2:
                $query = $query->whereBetween('doctors.doc_consult_fee',[100,500]);
                break;
            case 3:
                $query = $query->where('doctors.doc_consult_fee','>',500);
                break;
        }
        $result =  DB::select(DB::raw($prepareQuery), $whereData);
        $searchResult['result'] = $result;
        //print_r($result);die;
                                /*->map(function($result, $key) use($filter_hours_before_10, $filter_hours_after_05, $filter_availability,&$unsetCounter){
                            $result->doc_spec_detail = $this->doctorObj->getDoctorSpecialisation($result->user_id);
                            $result->doc_profile_img = $this->securityLibObj->encrypt($result->doc_profile_img);
                            $result->city_id = $this->securityLibObj->encrypt($result->city_id);
                            $result->spl_id = $this->securityLibObj->encrypt($result->spl_id);
                            $doctorTiming = $this->doctorTimeSlotList($result->clinic_id, '', $filter_hours_before_10, $filter_hours_after_05);
                            $timeDataArr = array();
                            if(empty($doctorTiming[0]->week_day) && ($filter_hours_before_10 != '' || $filter_hours_after_05 != '' || $filter_availability == '2')){
                                $unsetCounter++;
                                unset($result);
                            }else{
                                    if(!empty($doctorTiming)){
                                        foreach ($doctorTiming as $timingData) {
                                            if(!empty($timingData->start_time)){
                                                $timingData->slot = $this->createTimeSlot($timingData,date('Y-m-d'));
                                                unset($timingData->start_time,$timingData->end_time, $timingData->slot_duration);
                                            }
                                            $timeDataArr[] = $timingData;
                                        }
                                      $result->doc_timing_slot =  $timeDataArr;
                                    }
                                $result->user_id = $this->securityLibObj->encrypt($result->user_id);
                                $result->clinic_id = $this->securityLibObj->encrypt($result->clinic_id);
                                return $result;
                            
                        }                        
                    });*/
        $finalDataCount = $allData - $unsetCounter; 
        $searchResult['searched_city'] = !empty($city->city_name) ? $city->city_name : '';
        $searchResult['searched_spl'] = !empty($specailisation->spl_name) ? $specailisation->spl_name : '';
        $searchResult['searched_count'] = $finalDataCount;
        $searchResult['pages'] = ceil($finalDataCount/$data_limit);
        $searchResult['page'] = $requestData['page'];
         return $searchResult;
        
    }

     /**
     * Get doctor profile details
     *
     * @param string $userId doctor id
     * @param object $date booking date
     * @return array user detailed information
     */
    public function doctorTimeSlotList($clinicId, $slotDate='', $filter_hours_before_10='', $filter_hours_after_05='')
    {
        $date = ($slotDate == '') ? date('Y-m-d') : $slotDate; 
        $timingData = DB::table('timing')
                ->select('timing.timing_id', 'timing.user_id','timing.clinic_id','timing.week_day', 'timing.start_time', 'timing.end_time', 'timing.slot_duration', 'timing.patients_per_slot')
                ->where('timing.start_time','!=',Config::get('constants.TIMING_SLOT_OFF'))
                ->where([
                    'timing.week_day'=>date('w', strtotime($date)),
                    'timing.clinic_id'=>$clinicId,
                ])->where(function($query) use($filter_hours_before_10,$filter_hours_after_05){
                        if($filter_hours_before_10 != '' && $filter_hours_after_05 == ''){
                            $query = $query->where('start_time','<',$filter_hours_before_10);
                        }
                        if($filter_hours_after_05 != '' &&  $filter_hours_before_10 == ''){
                            $query = $query->where('end_time','>',$filter_hours_after_05);
                        }
                        if($filter_hours_after_05 != '' &&  $filter_hours_before_10 != ''){
                            $query = $query->where('start_time','<',$filter_hours_before_10);
                            $query = $query->orWhere('end_time','>',$filter_hours_after_05);
                        }
                    
                })
                ->orderBy('timing.start_time','ASC');
                $timingData = $timingData->get()
                            ->map(function($timing) use($date, $slotDate){
                                if(!empty($timing) && !empty($date)){
                                    $timing->slot = $this->createTimeSlot($timing,$date);
                                    unset($timing->start_time,$timing->end_time, $timing->slot_duration);
                                }
                                $timing->user_id = $this->securityLibObj->encrypt($timing->user_id);
                                $timing->timing_id = $this->securityLibObj->encrypt($timing->timing_id);
                                $timing->clinic_id = $this->securityLibObj->encrypt($timing->clinic_id);
                                $timing->date = $date;
                                return $timing;
                            })->toArray();
                    if(!empty($timingData)){
                        if(count($timingData) > 1){
                            $finalSlots = $timingData[0];
                            for ($i=1; $i < count($timingData); $i++) { 
                                foreach ($timingData[$i]->slot as $value) {
                                    array_push($finalSlots->slot, ['booking_count'=>$value['booking_count'],'slot_time'=>$value['slot_time']]); 
                                }
                            }
                        $slots[] = $finalSlots;
                        return $slots;
                        }
                        else{
                            return $timingData;
                        }
                }else{
                    $inputDate = date('Y/m/d', strtotime($date));
                    $availableDate = $this->nextAvailableSlot($inputDate, $clinicId);
                    if($availableDate){
                        $nextDate = date('d M', strtotime($availableDate));
                        $nextDay   = date('D', strtotime($nextDate));
                    }else{
                       $nextDate = 'N/A';
                       $nextDay = 'N/A'; 
                    }
                    $timingData[] = ["date"=>$date,"clinic_id"=>$this->securityLibObj->encrypt($clinicId),"nextDate"=>$nextDate,'nextDay' => $nextDay];
                    return $timingData;
                }
    }

    /**
    * @DateOfCreation        10 August 2018
    * @ShortDescription      This function is responsible to create timeslot
    * @param                 array $timing
                             Date $date optional   
    * @return                specility
    */
    public function createTimeSlot($timing,$date){
        $timeSlotArray = array ();
        $startTime    = strtotime ($timing->start_time); //change to strtotime
        $endTime = strtotime ($timing->end_time); //change to strtotime
        $duration      = $timing->slot_duration; 
        $add_mins      = $duration * 60;
        while ($startTime < $endTime) // loop between time
        {
            $bookingCount = $this->getBookingCount(date ("Hi", $startTime),$this->securityLibObj->encrypt($timing->user_id), $date);
            $timeSlotArray[] =[
            'booking_count'=> $bookingCount->booking_count,
            'slot_time'=> date ("Hi", $startTime)
            ];
            $startTime += $add_mins; // to check endtie=me
        }

        return $timeSlotArray;
    }

    /**
    * @DateOfCreation        10 August 2018
    * @ShortDescription      This function is responsible to count doctors
    * @param                 String $bookingTimeSlot
                             ineger $doctorId
                             Date $bookingDate optional   
    * @return                specility
    */
    function getBookingCount($bookingTimeSlot,$doctorId,$bookingDate=''){
        return DB::table('bookings')
                ->select(DB::raw("COUNT(booking_id) AS booking_count"))
                ->where([
                    'booking_time'=>$bookingTimeSlot,
                    'booking_date'=>!empty($bookingDate) ? $bookingDate : date('Y-m-d'),
                    'user_id'=> $this->securityLibObj->decrypt($doctorId)
                ])->first();
    }

    public function nextAvailableSlot($inputdate, $clinicId){
            $availableDays = array();
            $days = DB::table('timing')->select('week_day')->distinct()->where('clinic_id', $clinicId)->get()->toArray();

            foreach ($days as $value) {
                $availableDays[] =$value->week_day;
            }
            if(!empty($availableDays)){
                $minCount = min($availableDays);
                $date = \DateTime::createFromFormat('Y/m/d', $inputdate);
                $num = $date->format('w');
                $min = 7; 
                foreach($availableDays as $o){  //loop through all the offerdays to find the minimum difference
                    $dif = $o - $num;
                    if($dif>0 && $dif < $min){
                        $min = $dif ;
                    }
                }
                // Next week 
                if($min == 7){
                    $min = 7 - $num + min($availableDays);
                }
                //add the days till next offerday
                $add = new \DateInterval('P'.$min.'D');
                $nextAvailableDay = $date->add($add)->format('Y/m/d');
                return $nextAvailableDay;
            }else{
                return false;
            }
    }

}