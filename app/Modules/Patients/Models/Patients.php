<?php

namespace App\Modules\Patients\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\HasApiTokens;
use App\Traits\Encryptable;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Libraries\SecurityLib;
use App\Libraries\UtilityLib;
use App\Libraries\DateTimeLib;
use App\Libraries\FileLib;
use App\Libraries\ImageLib;
use App\Modules\Setup\Models\StaticDataConfig;
use App\Modules\Bookings\Models\Bookings;
use App\Modules\Patients\Models\PatientsActivities;
use App\Modules\Patients\Models\PatientsAllergies;
use App\Modules\PatientGroups\Models\PatientGroups;
use App\Modules\Accounts\Models\Accounts;
use Config;
use File;

/**
 * Patients Class
 *
 * @package                ILD INDIA
 * @subpackage             Patients
 * @category               Model
 * @DateOfCreation         13 June 2018
 * @ShortDescription       This is model which need to perform the options related to 
                           Patients info

 */
class Patients extends Model {

    use Encryptable;

    // @var string $table
    // This protected member contains table name
    protected $table = 'patients';

    // @var string $primaryKey
    // This protected member contains primary key
    protected $primaryKey = 'pat_id';  

    protected $encryptable = [];

    protected $fillable = ['user_id','pat_title', 'pat_code', 'pat_dob', 'pat_address_line1', 'pat_address_line1',
            'pat_phone_num','pat_locality', 'city_id', 'pat_other_city', 'state_id', 'pat_pincode', 'pat_status', 'ip_address', 'resource_type', 'is_deleted', 'pat_address_line2','pat_blood_group', 'pat_marital_status', 'pat_number_of_children', 'pat_religion', 'pat_informant', 'pat_reliability', 'pat_occupation', 'pat_education', 'doc_ref_id', 'pat_group_id', 'pat_emergency_contact_number'];

    /**
     * Create a new model instance.
     *
     * @return void
     */
    public function __construct()
    {
        // Init exception library object
        $this->utilityLibObj = new UtilityLib();

        // Init security library object
        $this->securityLibObj = new SecurityLib();

        $this->FileLib = new FileLib(); 

        // Init dateTime library object
        $this->dateTimeLibObj = new DateTimeLib();

        //Init StaticDataConfig model object
        $this->staticDataConfigObj = new StaticDataConfig();

        // Init Patients Activities Model Object
        $this->patientActivitiesModelObj = new PatientsActivities();  

        // Init Patients Allergies Model Object
        $this->patientAllergiesModelObj = new PatientsAllergies(); 
        
        // Init Patients Groups Model Object
        $this->patientGroupsModelObj = new PatientGroups();
        
        // Init Patients Groups Model Object
        $this->accountsModelObj = new Accounts();     
    }

    /**
     * @DateOfCreation        13 June 2018
     * @ShortDescription      This function is responsible for creating new Patient in DB
     * @param                 Array $data This contains full Patient user input data 
     * @return                True/False
     */
    public function createPatient($data, $userId)
    {
       
        // @var Boolean $response
        // This variable contains insert query response
        $response = false;
        // @var Array $inserData
        // This Array contains insert data for users
        $inserData = array(
            'user_id'      => $userId,
            'pat_code'     => $data['pat_code'],
            'resource_type'=> $data['resource_type'],
            'ip_address'   => $data['ip_address'],
            'created_by'   => $userId,
            'updated_by'   => $userId
        );        
        
        // Prepair insert query
        $response = DB::table('patients')->insert(
                        $inserData
                    );
        return $response;        
    }

    /**
     * @DateOfCreation        15 June 2018
     * @ShortDescription      This function is responsible for update Patient Records
     * @param                 Array $data This contains full Patient user input data 
     * @return                True/False
     */
    public function updatePatientData($requestData, $whereData)
    {
        // This Array contains update data for Patient
        $updateData = $this->utilityLibObj->fillterArrayKey($requestData, $this->fillable);
                
        // Prepare update query
        $response = $this->dbUpdate($this->table, $updateData, $whereData);
        return $response;
    }

    /**
     * @DateOfCreation        15 June 2018
     * @ShortDescription      This function is responsible for update Patient Records
     * @param                 Array $data This contains full Patient user input data 
     * @return                True/False
     */
    public function getPatientProfileData($patientId)
    {
        $whereProfileData  = ['users.user_id' => $patientId];
        $allergyListData   = $this->patientAllergiesModelObj->getListData(['patId' => $this->securityLibObj->encrypt($patientId), 'page' => 0, 'pageSize' => -1, 'sort' => [], 'filtered' => [] ]);
        
        $patientAllergyData = [];
        if(!empty($allergyListData['result'])){
            foreach ($allergyListData['result'] as $allergyData) {
                $patientAllergyData[] = $allergyData->allergy_type_value;
            }
        }

        $response = DB::table($this->table)
                    ->select($this->table.'.pat_id','pat_title','users.user_id','pat_profile_img','pat_phone_num','pat_code', 'pat_dob', 'pat_address_line1', 'pat_address_line2', 'pat_locality', 'city_id', 'pat_other_city', $this->table.'.state_id', 'pat_pincode', 'pat_status', 'user_gender', 'user_country_code','states.country_id', 'user_mobile', 'user_email','users.user_firstname','users.user_lastname', 'pat_blood_group', 'states.name as state_name', 'country_name', 'cities.name as city_name', 'users.user_adhaar_number', 'pat_marital_status', 'pat_number_of_children', 'pat_religion', 'pat_informant', 'pat_reliability', 'pat_occupation', 'pat_education', $this->table.'.doc_ref_id', $this->table.'.pat_group_id', 'patient_groups.pat_group_name', 'doctor_referral.doc_ref_name', 'pat_emergency_contact_number')
                    ->join('users', $this->table.'.user_id', '=', 'users.user_id')
                    ->leftJoin('states', $this->table.'.state_id', '=' ,'states.id')
                    ->leftJoin('cities', $this->table.'.city_id', '=' ,'cities.id')
                    ->leftJoin('country', 'states.country_id', '=' ,'country.country_id')
                    ->leftJoin('patient_groups', $this->table.'.pat_group_id', '=' ,'patient_groups.pat_group_id')
                    ->leftJoin('doctor_referral', $this->table.'.doc_ref_id', '=' ,'doctor_referral.doc_ref_id')
                    ->where($whereProfileData)
                    ->get()
                    ->map(function($patientProfileData) use($patientAllergyData){
                        $patientProfileData->allergy_type_value     = !empty($patientAllergyData) ? implode(', ', $patientAllergyData) : '';
                        $patientProfileData->user_id                = $this->securityLibObj->encrypt($patientProfileData->user_id);
                        $patientProfileData->pat_id                 = $this->securityLibObj->encrypt($patientProfileData->pat_id);
                        $patientProfileData->doc_ref_id             = !empty($patientProfileData->doc_ref_id) ? $this->securityLibObj->encrypt($patientProfileData->doc_ref_id) : '';
                        $patientProfileData->pat_group_id           = !empty($patientProfileData->pat_group_id) ? $this->securityLibObj->encrypt($patientProfileData->pat_group_id) : '';
                        $patientProfileData->doc_ref_name           = !empty($patientProfileData->doc_ref_name) ? $patientProfileData->doc_ref_name : '';
                        $patientProfileData->pat_group_name         = !empty($patientProfileData->pat_group_name) ? $patientProfileData->pat_group_name : '';
                        $patientProfileData->pat_profile_img        = 
                        !empty($patientProfileData->pat_profile_img) ? url('api/patient-profile-image/'.$this->securityLibObj->encrypt($patientProfileData->pat_profile_img)) : '';
                        $patientProfileData->country_id             = $this->securityLibObj->encrypt($patientProfileData->country_id);
                        $patientProfileData->city_id                = $this->securityLibObj->encrypt($patientProfileData->city_id);
                        $patientProfileData->state_id               = $this->securityLibObj->encrypt($patientProfileData->state_id);
                        $patientProfileData->pat_dob                = !empty($patientProfileData->pat_dob) ? date(Config::get('constants.DB_SAVE_DATE_FORMAT'), strtotime($patientProfileData->pat_dob)) : '';
                        $patientProfileData->pat_blood_group_name   = $this->staticDataConfigObj->getBloodGroupNameById($patientProfileData->pat_blood_group);
                        $patientProfileData->pat_aadhar_no_formatted= !empty($patientProfileData->user_adhaar_number) ? chunk_split($patientProfileData->user_adhaar_number, 4, ' ') : '';
                        $patientProfileData->pat_marital_status     = !empty($patientProfileData->pat_marital_status) ? [(string) $patientProfileData->pat_marital_status] : [];
                        $patientProfileData->pat_religion           = $patientProfileData->pat_religion;
                        $patientFullAddress = '';
                        if(!empty($patientProfileData->pat_address_line1)) $patientFullAddress .= $patientProfileData->pat_address_line1;
                        if(!empty($patientProfileData->pat_address_line2)) $patientFullAddress .= ', '.$patientProfileData->pat_address_line2;
                        $patientProfileData->pat_full_address_line1 = $patientFullAddress;
                        
                        $patientFullAddressCity = '';
                        if(!empty($patientProfileData->pat_other_city)) {
                            $patientFullAddressCity .= $patientProfileData->pat_other_city;
                        }else{
                            $patientFullAddressCity .= $patientProfileData->city_name;
                        }

                        if(!empty($patientProfileData->pat_pincode)) $patientFullAddressCity .= ' '.$patientProfileData->pat_pincode;
                        if(!empty($patientProfileData->state_name)) $patientFullAddressCity .= ', '.$patientProfileData->state_name;
                        $patientProfileData->pat_full_address_line2 = $patientFullAddressCity;
                        return $patientProfileData;
                    })->first();
        return $response;
    }

    /**
     * @DateOfCreation        19 June 2018
     * @ShortDescription      This function is responsible for get all Patients Records by user_id and list fillter and sorting apply for selected column
     * @param                 Array $data This contains full Patient user input data 
     * @return                True/False
     */
    public function getPatientList($requestData)
    {
        $listQuery = $this->patientListQuery($requestData['user_id']);

        if(!empty($requestData['filtered'])){
            foreach ($requestData['filtered'] as $key => $value) {
                
                $whereGender = $value['value'];
                if(stripos($value['value'], 'male') !== false)
                {
                    $whereGender = 1;
                } else if( stripos($value['value'], 'female' !== false) ) {
                    $whereGender = 2;                    
                } else if( stripos($value['value'], 'other') !== false ){
                    $whereGender = 3;                    
                }

                if(!empty($value['value'])){
                    $listQuery = $listQuery->where(function ($listQuery) use ($value, $whereGender){
                                    $listQuery
                                    ->where('user_email', 'ilike', "%".$value['value']."%")
                                    ->orWhere('pat_locality', 'ilike', '%'.$value['value'].'%')
                                    ->orWhere('patient_groups.pat_group_name', 'ilike', '%'.$value['value'].'%')
                                    ->orWhere(DB::raw('CAST(user_mobile AS TEXT)'), 'ilike', '%'.$value['value'].'%')
                                    ->orWhere(DB::raw('CAST(pat_pincode AS TEXT)'), 'ilike', '%'.$value['value'].'%')
                                    ->orWhere(DB::raw('CAST(pat_code AS TEXT)'), 'ilike', '%'.$value['value'].'%')
                                    ->orWhere(DB::raw('CAST(user_gender AS TEXT)'), 'ilike', '%'.$whereGender.'%');
                                });
                }
            }
        }

        if(!empty($requestData['sorted'])){
            foreach ($requestData['sorted'] as $sortKey => $sortValue) {
                $orderBy = $sortValue['desc'] ? 'desc' : 'asc';
                $listQuery->orderBy($sortValue['id'], $orderBy);
            }
        } else {
            $listQuery->orderBy('users.user_id', 'desc');            
        }

        if($requestData['page'] > 0){
            $offset = $requestData['page'] * $requestData['pageSize'];
        }else{
            $offset = 0;            
        }

        $patientList['pages']   = ceil($listQuery->count()/$requestData['pageSize']);
        
        $patientList['result']  = $listQuery
                                ->offset($offset)
                                ->limit($requestData['pageSize'])
                                ->get()
                                ->map(function($patientListData){
                                    $patientListData->pat_profile_img = (!empty($patientListData->pat_profile_img) ? $patientListData->pat_profile_img : '');
                                    $patientListData->pat_id = $this->securityLibObj->encrypt($patientListData->pat_id);
                                    $patientListData->user_id = $this->securityLibObj->encrypt($patientListData->user_id);
                                    $patientListData->visit_id = $this->securityLibObj->encrypt($patientListData->visit_id);
                                    $patientListData->pat_profile_img = !empty($patientListData->pat_profile_img) ? $this->securityLibObj->encrypt($patientListData->pat_profile_img) : 'default';
                                    return $patientListData;
                                }); 
        return $patientList;
    }

    /**
     * @DateOfCreation        20 June 2018
     * @ShortDescription      This function is responsible for patient list query from user and patient tables
     * @param                 Array $data This contains full Patient user input data 
     * @return                Array of patients
     */
    public function patientListQuery($userId){
        
        $selectData = ['users.user_firstname','users.user_lastname','users.user_id','patients.pat_phone_num', 'patients.pat_id', 'patients.pat_code', 'patients.pat_profile_img', 'patients.pat_locality', 'patients.pat_pincode', 'users.user_gender', 'users.user_mobile', 'users.user_email','patients_visits.visit_id','patients.pat_title','users.user_country_code','patients.pat_dob', 'patient_groups.pat_group_name', 'patients.pat_emergency_contact_number'];
        

        $whereData = array(
                        'users.is_deleted'      => Config::get('constants.IS_DELETED_NO'),
                        'patients.is_deleted'   => Config::get('constants.IS_DELETED_NO'),
                        'doctor_patient_relation.user_id'   => $userId,
                        'users.user_type'       => Config::get('constants.USER_TYPE_PATIENT'),
                        'patients_visits.visit_type'=> Config::get('constants.PROFILE_VISIT_TYPE'),
                    );
        $listQuery = DB::table('users')
                        ->join('patients', 'patients.user_id', '=', 'users.user_id')
                        ->join('doctor_patient_relation', 'doctor_patient_relation.pat_id', '=','users.user_id' )
                        ->join('patients_visits', 'patients_visits.pat_id', '=','users.user_id' )
                        ->leftJoin('patient_groups',function($join) {
                            $join->on('patient_groups.pat_group_id', '=', "patients.pat_group_id")
                                ->where('patient_groups.is_deleted', Config::get('constants.IS_DELETED_NO'), 'and');
                        })
                        ->select($selectData)
                        ->where($whereData);     
        return $listQuery;
    }

    /**
     * @DateOfCreation        21 June 2018
     * @ShortDescription      This function is responsible for get patient's visit id
     * @param                 Array $data This contains full Patient user input data 
     * @return                String {patient visit id}
     */
    public function getPatientVisitId($requestData)
    {
        $patientUserId = $this->securityLibObj->decrypt($requestData['patientUserId']);
        $userId = $requestData['user_id'];
        $visitIdQuery = $this->checkPatientVisitId($patientUserId,$userId);

        if( !empty($visitIdQuery) )
        {
            $visitId = $visitIdQuery->visit_id;
        } else {

            // Insert New Visit
            $inserData = [
                            'user_id'       => $userId, 
                            'pat_id'        => $patientUserId,
                            'visit_type'    => Config::get('constants.PROFILE_VISIT_TYPE'),
                            'visit_number'  => Config::get('constants.INITIAL_VISIT_NUMBER')
                        ];
            $newVisit = $this->dbInsert('patients_visits', $inserData);
            if($newVisit){
                $visitId = DB::getPdo()->lastInsertId();                
            } else {
                $visitId = 0;                
            }    
        }
        return $this->securityLibObj->encrypt($visitId);
    }

    /**
     * @DateOfCreation        29 June 2018
     * @ShortDescription      This function is responsible for creating new Patient in DB
     * @param                 Array $data This contains full Patient user input data 
     * @return                True/False
     */
    public function createPatientUser($tablename,$insertData)
    {
        // @var Boolean $response
        // This variable contains insert query response
        $response = false;
                
        // Prepare insert query
        $response = $this->dbInsert($tablename, $insertData);
        if($response){
            $id = DB::getPdo()->lastInsertId();
            return $id;
        }else{
            return $response;
        }          
    }

    /**
     * @DateOfCreation        2 aug 2018
     * @ShortDescription      This function is responsible for creating doctor patient in visit 
                              on new Patient in DB
     * @param                 Array $data This contains full Patient user input data 
     * @return                True/False
     */
    public function createPatientDoctorVisit($tablename,$insertData)
    {
        $queryResult = $this->dbInsert($tablename, $insertData);
        if($queryResult){
            return $this->securityLibObj->encrypt(DB::getPdo()->lastInsertId());
        }
        return false;
    }


    /**
     * @DateOfCreation        21 June 2018
     * @ShortDescription      This function is responsible for get patient's visit id
     * @param                 Array $data This contains full Patient user input data 
     * @return                String {patient visit id}
     */
    public function getPatientFollowUpVisitId($requestData)
    {
        //Init StaticDataConfig model object
        $this->bookingsObj = new Bookings();
        
        $patientUserId = $this->securityLibObj->decrypt($requestData['patientUserId']);
        $patientBookingId = !empty($requestData['patientBookingId']) ? $this->securityLibObj->decrypt($requestData['patientBookingId']) : '';
        $userId = $requestData['user_id'];
        
        $checkVisit = $this->checkInProgressVisitExist($patientUserId, $userId);
        if(!empty($checkVisit)){
            $booking_id = !empty($checkVisit->booking_id) ? $this->securityLibObj->encrypt($checkVisit->booking_id) : '';
            return ['visit_id'=> $this->securityLibObj->encrypt($checkVisit->visit_id), 'visit_type'=> Config::get('constants.FOLLOW_VISIT_TYPE'), 'is_pending' => true,'booking_id'=>$booking_id];
        }
        
        $visitIdQuery = $this->checkPatientVisitId($patientUserId, $userId);
        if( !empty($visitIdQuery) )
        {
            $visitId = $visitIdQuery->visit_id;
            $insertData = ['user_id'         => $userId, 
                          'pat_id'          => $patientUserId,
                          'visit_type'      => Config::get('constants.FOLLOW_VISIT_TYPE'),
                          'visit_number'    => $visitIdQuery->visit_number+1,
                          'resource_type'   => $requestData['resource_type'],
                          'ip_address'       => $requestData['ip_address']
                        ];
            $visitType = Config::get('constants.FOLLOW_VISIT_TYPE');
        } else {
            // Insert New Visit
            $insertData = ['user_id'      => $requestData['user_id'], 
                          'pat_id'       => $patientUserId,
                          'visit_type'   => Config::get('constants.INITIAL_VISIT_TYPE'),
                          'visit_number' => Config::get('constants.INITIAL_VISIT_NUMBER'),
                          'resource_type'   => $requestData['resource_type'],
                          'ip_address'       => $requestData['ip_address']
                        ];
            $visitType = Config::get('constants.INITIAL_VISIT_TYPE');
        }
        $newVisit = $this->dbInsert('patients_visits', $insertData);
        if($newVisit){
            $visitId = DB::getPdo()->lastInsertId();
            $createPaymentsHistory = $this->accountsModelObj->createPaymentsHistoryFromVisit($insertData);
            if(!empty($patientBookingId)){
                $bookingVisitRelationData = ['visit_id' => $visitId,
                                             'booking_id' => $patientBookingId 
                                            ];                
                $bookingVisitRelation = $this->dbInsert('booking_visit_relation', $bookingVisitRelationData);
                $bookingInProgress = $this->bookingsObj->updateBookingState($patientBookingId, Config::get('constants.BOOKING_IN_PROGRESS'));
            }
        } else {
            $visitId = 0;
        }   
        
        return ['visit_id'=> $this->securityLibObj->encrypt($visitId),'visit_type'=> $visitType, 'is_pending' => false];
    }

    /**
     * @DateOfCreation        21 June 2018
     * @ShortDescription      This function is responsible for get patient's last visit id 
     * @param                 $user id, $doctor id 
     * @return                array
     */
    public function checkPatientVisitId($patientUserId,$doctorUserId){
        $visitIdQuery = DB::table('patients_visits')
                        ->select(['visit_id', 'visit_number'])
                        ->where([
                                'user_id'    => $doctorUserId, 
                                'pat_id'     => $patientUserId,
                                'is_deleted' => Config::get('constants.IS_DELETED_NO')]
                        )
                        ->where('visit_type', '!=', Config::get('constants.PROFILE_VISIT_TYPE'))
                        ->orderBy('visit_id', 'desc')
                        ->first();
        return $visitIdQuery;
    }

    /**
     * @DateOfCreation        21 June 2018
     * @ShortDescription      This function is responsible for get any in progress visit exist or not
     * @param                 $patientUserId, $doctorUserId 
     * @return                array
     */
    private function checkInProgressVisitExist($patientUserId, $doctorUserId){
        $visitIdQuery = DB::table('patients_visits')
                        ->select(['patients_visits.visit_id', 'patients_visits.visit_number', 'booking_visit_relation.booking_id'])
                        ->leftJoin('booking_visit_relation', 'patients_visits.visit_id', '=', 'booking_visit_relation.visit_id')
                        ->where([
                                'patients_visits.user_id'    => $doctorUserId, 
                                'patients_visits.pat_id'     => $patientUserId,
                                'patients_visits.is_deleted' => Config::get('constants.IS_DELETED_NO')]
                        )
                        ->where('patients_visits.visit_type', Config::get('constants.FOLLOW_VISIT_TYPE'))
                        ->where('patients_visits.status', '!=' ,3)
                        ->orderBy('patients_visits.visit_id', 'desc')
                        ->first();
        return $visitIdQuery;
    }

    /**
     * @DateOfCreation        2 Aug 2018
     * @ShortDescription      This function is responsible to create relation between doctor ans patient
     * @param                 $tablename - insertion table name 
     * @param                 Array $insertData 
     * @return                String {doctor patient relatrion id}
     */
    public function createPatientDoctorRelation($tablename, $insertData){
        // @var Boolean $response
        // This variable contains insert query response
        $response = false;
        
        // Prepare insert query
        $result = DB::table($tablename)
                        ->select(['rel_id'])
                        ->where([
                                'user_id'    => $insertData['user_id'], 
                                'pat_id'     => $insertData['pat_id'], 
                                'is_deleted' => Config::get('constants.IS_DELETED_NO')]
                        )
                        ->first();
        if(!empty($result->rel_id)){
            return $result->rel_id;
        }
        $response = $this->dbInsert($tablename, $insertData);
        if($response){
            $relId = DB::getPdo()->lastInsertId();
            return $relId;
        }else{
            return $response;
        } 
    }

    protected function generateThumbWithImage($mainImage)
    {
        $imageLibObj = new ImageLib();
        $thumb = [];
        $thumb = array(
            ['thumb_name' => $mainImage,'thumb_path' => Config::get('constants.PATIENTS_PROFILE_MTHUMB_IMG_PATH'),'width' => Config::get('constants.MEDIUM_THUMB_SIZE') , 'height' => Config::get('constants.MEDIUM_THUMB_SIZE')],
            ['thumb_name' => $mainImage,'thumb_path' => Config::get('constants.PATIENTS_PROFILE_STHUMB_IMG_PATH'),'width' => Config::get('constants.SMALL_THUMB_SIZE') , 'height' => Config::get('constants.SMALL_THUMB_SIZE')],
        );
        $thumbGenerate = $imageLibObj->genrateThumbnail(Config::get('constants.PATIENTS_PROFILE_IMG_PATH').$mainImage,$thumb);
        return $thumbGenerate;
    }  

    /**
     * Update doctor image with regarding details
     *
     * @param array $data image data and patent id
     * @return array profile image
     */
    public function updateProfileImage($requestData, $loggedInUserId)
    {
        $pat_id = $this->securityLibObj->decrypt($requestData['pat_id']);
        $isExist = DB::table('patients')->select('pat_profile_img')->where('pat_id', $pat_id)->first();
        $destination = storage_path('app/public/'.Config::get('constants.PATIENTS_PROFILE_IMG_PATH'));    
        $this->FileLib->createDirectory($destination);
        $data = $this->FileLib->base64ToPng($requestData['pat_profile_img'], $destination, 'png');
        $imageData = array();
        $uploadImage = $data['uploaded_file'];
        if(!empty($data['uploaded_file'])) {
            $imageData = array(
                "pat_profile_img"   => $data['uploaded_file'],
                "created_by"        => $loggedInUserId,
                "updated_by"        => $loggedInUserId,
            );
            try {
                DB::beginTransaction();
                $thumbStatus = $this->generateThumbWithImage($data['uploaded_file']);
                if($thumbStatus[0]['code'] == Config::get('restresponsecode.SUCCESS')){
                    $isUpdated = DB::table('patients')->where('user_id', $pat_id)->update($imageData);

                    if(!empty($isUpdated)){
                        if(!empty($isExist->pat_profile_img) && File::exists($destination.$isExist->pat_profile_img)) {
                            File::delete($destination.$isExist->pat_profile_img);
                        }
                        DB::commit();
                        return url('api/patient-profile-image/'.$this->securityLibObj->encrypt($data['uploaded_file']));
                      }
                }else{
                    if(File::exists($destination.$uploadImage)){
                        File::delete($destination.$uploadImage);
                    }
                    return false;
                }
            } catch (\Exception $e) {
                if(File::exists($destination.$uploadImage)){
                    File::delete($destination.$uploadImage);
                }
              DB::rollback();  
            }
        }    
    }

    /**
     * @DateOfCreation        3 Sept 2018
     * @ShortDescription      This function is responsible for get Patient Activity History
     * @param                 $patientUserId, $doctorUserId 
     * @return                array
     */
    public function getPatientActivityHistory($requestData)
    {
        $getActivityVisits = $this->patientActivitiesModelObj->getActivityRecords($requestData);
    
        $getSymptoms = $getVitals = $getDiagnosis = $getClinicalNotes = $getPrescribedMedicine = [];
        if(!empty($getActivityVisits))
        {
            $visitIdArr = explode(',', $getActivityVisits->visits);
            // GET SYMPTOMS
            $getSymptoms            = $this->getVisitSymptoms($visitIdArr); 
            $getSymptoms            = !empty($getSymptoms) ? $this->utilityLibObj->changeMultidimensionalArrayKey($getSymptoms, 'visit_id') : [];
            if(!empty($getSymptoms)){
                foreach ($getSymptoms as $symptomsVisitKey => $diagnosis) {
                    $getSymptoms[$symptomsVisitKey] = !empty($diagnosis)  ? $this->utilityLibObj->changeMultidimensionalArrayKey($diagnosis, 'created_at') : [];
                }
            }
            // GET VITALS
            $getVitals              = $this->getVisitVitals($visitIdArr);
            $getVitals              = !empty($getVitals) ? $this->utilityLibObj->changeMultidimensionalArrayKey($getVitals, 'visit_id') : [];
            // GET DIAGNOSIS
            $getDiagnosis           = $this->getVisitDiagnosis($visitIdArr);
            $getDiagnosis           = !empty($getDiagnosis) ? $this->utilityLibObj->changeMultidimensionalArrayKey($getDiagnosis, 'visit_id') : [];
            if(!empty($getDiagnosis)){
                foreach ($getDiagnosis as $diagnosisVisitKey => $diagnosis) {
                    $getDiagnosis[$diagnosisVisitKey] = !empty($diagnosis)  ? $this->utilityLibObj->changeMultidimensionalArrayKey($diagnosis, 'created_at') : [];
                }
            }
            // GET CLINICAL NOTES
            $getClinicalNotes       = $this->getVisitClinicalNotes($visitIdArr);
            $getClinicalNotes       = !empty($getClinicalNotes)  ? $this->utilityLibObj->changeArrayKey($getClinicalNotes, 'visit_id') : [];
            // GET PRESCRIBED MEDICINES
            $getPrescribedMedicine  = $this->getVisitPrescribedMedicine($visitIdArr);
            $getPrescribedMedicine  = !empty($getPrescribedMedicine) ? $this->utilityLibObj->changeMultidimensionalArrayKey($getPrescribedMedicine, 'visit_id') : [];
            if(!empty($getPrescribedMedicine)){
                foreach ($getPrescribedMedicine as $medicineVisitKey => $medicines) {
                    $getPrescribedMedicine[$medicineVisitKey] = !empty($medicines)  ? $this->utilityLibObj->changeMultidimensionalArrayKey($medicines, 'created_at') : [];
                }
            }
        }
        $activityHistory = DB::table('patient_activity')
                        ->select(DB::raw("user_id, pat_id, activity_table, visit_id, Date(created_at) as created_at"))
                        ->where([
                                'patient_activity.user_id'    => $requestData['user_id'], 
                                'patient_activity.pat_id'     => $requestData['pat_id'], 
                                'patient_activity.is_deleted' => Config::get('constants.IS_DELETED_NO')
                            ])
                        ->where('patient_activity.activity_table', '!=', 'patients_visits')
                        ->orderBy('patient_activity', 'desc')
                        ->get()
                        ->map(function($activityRecord) use($getSymptoms, $getVitals, $getDiagnosis, $getClinicalNotes, $getPrescribedMedicine){
                            $activityRecord->visit_id = $this->securityLibObj->encrypt($activityRecord->visit_id);
                            if($activityRecord->activity_table == 'visit_symptoms' && array_key_exists($activityRecord->visit_id, $getSymptoms)){
                                if(array_key_exists($activityRecord->created_at, $getSymptoms[$activityRecord->visit_id])){
                                    $activityRecord->symptoms_data = $getSymptoms[$activityRecord->visit_id][$activityRecord->created_at];                                    
                                }
                            }
                            if($activityRecord->activity_table == 'vitals' && array_key_exists($activityRecord->visit_id, $getVitals)){
                                $activityRecord->vitals_data = $getVitals[$activityRecord->visit_id];
                            }
                            if($activityRecord->activity_table == 'patients_visit_diagnosis' && array_key_exists($activityRecord->visit_id, $getDiagnosis)){
                                if(array_key_exists($activityRecord->created_at, $getDiagnosis[$activityRecord->visit_id])){
                                    $activityRecord->diagnosis_data = $getDiagnosis[$activityRecord->visit_id][$activityRecord->created_at];                                    
                                }
                            }
                            if($activityRecord->activity_table == 'clinical_notes' && array_key_exists($activityRecord->visit_id, $getClinicalNotes)){
                                $activityRecord->clinicalNotes_data = $getClinicalNotes[$activityRecord->visit_id];
                            }
                            if($activityRecord->activity_table == 'patient_medication_history' && array_key_exists($activityRecord->visit_id, $getPrescribedMedicine)){
                                if(array_key_exists($activityRecord->created_at, $getPrescribedMedicine[$activityRecord->visit_id])){
                                    $activityRecord->prescribedMedicine_data = $getPrescribedMedicine[$activityRecord->visit_id][$activityRecord->created_at];                                    
                                }
                            }
                            $activityRecord->created_at = date('d M, Y', strtotime($activityRecord->created_at));
                            return $activityRecord;                            
                        });
        return $activityHistory;
    }
    private function getVisitSymptoms($visitIdArray=[]){
        $visitIdArray = !empty(array_filter($visitIdArray)) ? $visitIdArray : [0];
        return DB::table('visit_symptoms as vs')
                    ->select(DB::raw("symptoms.symptom_name, vs.since_date, vs.comment, vs.visit_id, Date(vs.created_at) as created_at"))
                    ->join('symptoms',function($join) {
                        $join->on('symptoms.symptom_id', '=', 'vs.symptom_id')
                            ->where('symptoms.is_deleted', '=', Config::get('constants.IS_DELETED_NO'), 'and');
                    })
                    ->where('vs.is_deleted', Config::get('constants.IS_DELETED_NO'))
                    ->whereIn('vs.visit_id', $visitIdArray)
                    ->orderBy('visit_symptom_id', 'desc')
                    ->get()
                    ->map(function($symptomsData){
                        $symptomsData->visit_id = $this->securityLibObj->encrypt($symptomsData->visit_id);
                        return $symptomsData;
                    });
    }
    private function getVisitVitals($visitIdArray=[]){
        $visitIdArray = !empty(array_filter($visitIdArray)) ? $visitIdArray : [0];
        return DB::table('vitals')
                ->select(DB::raw("vitals.visit_id, vitals.fector_id as vitals_factor_id, vitals.fector_value as vitals_factor_value, pe.fector_value as physical_weight, pe.fector_id as physical_fector_id, Date(vitals.created_at) as created_at"))
                ->leftJoin('physical_examinations as pe',function($join) {
                    $join->on('pe.visit_id', '=', "vitals.visit_id")
                        ->where('pe.fector_id', Config::get('dataconstants.VISIT_PHYSICAL_WEIGHT'), 'and');
                })
                ->whereIn('vitals.visit_id', $visitIdArray)
                ->where('vitals.is_deleted', Config::get('constants.IS_DELETED_NO'))
                ->get()
                ->map(function($vitalsData){
                    if($vitalsData->vitals_factor_id == Config::get('dataconstants.VISIT_VITALS_PULSE')){
                        $vitalsData->title = trans('Setup::StaticDataConfigMessage.visit_vitals_label_pulse');
                    } else if($vitalsData->vitals_factor_id == Config::get('dataconstants.VISIT_VITALS_BP_SYS')){
                        $vitalsData->title = trans('Setup::StaticDataConfigMessage.visit_vitals_label_bp_sys');
                    } else if($vitalsData->vitals_factor_id == Config::get('dataconstants.VISIT_VITALS_BP_DIA')){
                        $vitalsData->title = trans('Setup::StaticDataConfigMessage.visit_vitals_label_bp_dia');
                    } else if($vitalsData->vitals_factor_id == Config::get('dataconstants.VISIT_VITALS_SPO2')){
                        $vitalsData->title = trans('Setup::StaticDataConfigMessage.visit_vitals_label_spo2_lable');
                    } else if($vitalsData->vitals_factor_id == Config::get('dataconstants.VISIT_VITALS_RESPIRATORY_RATE')){
                        $vitalsData->title = trans('Setup::StaticDataConfigMessage.visit_vitals_label_respiratory_rate');
                    } else{
                        $vitalsData->title = trans('Setup::StaticDataConfigMessage.visit_vitals_label_weight');
                    }
                    $vitalsData->vitals_factor_id   = $this->securityLibObj->encrypt($vitalsData->vitals_factor_id);
                    $vitalsData->physical_fector_id = $this->securityLibObj->encrypt($vitalsData->physical_fector_id);
                    $vitalsData->visit_id = $this->securityLibObj->encrypt($vitalsData->visit_id);
                    return $vitalsData;
                });
    }
    private function getVisitDiagnosis($visitIdArray=[]){
        $visitIdArray = !empty(array_filter($visitIdArray)) ? $visitIdArray : [0];
        return DB::table('patients_visit_diagnosis as pvd')
                ->select(DB::raw("pvd.date_of_diagnosis, diseases.disease_name, pvd.visit_id, Date(pvd.created_at) as created_at"))
                ->join('diseases',function($join) {
                    $join->on('diseases.disease_id', '=', 'pvd.disease_id')
                        ->where('diseases.is_deleted', '=', Config::get('constants.IS_DELETED_NO'), 'and');
                })
                ->where('pvd.is_deleted', Config::get('constants.IS_DELETED_NO'))
                ->whereIn('pvd.visit_id', $visitIdArray)
                ->orderBy('visit_diagnosis_id', 'desc')
                ->get()
                ->map(function($diagnosisData){
                    $diagnosisData->visit_id = $this->securityLibObj->encrypt($diagnosisData->visit_id);
                    return $diagnosisData;
                });
    }
    private function getVisitClinicalNotes($visitIdArray=[]){
        $visitIdArray = !empty(array_filter($visitIdArray)) ? $visitIdArray : [0];
        return DB::table('clinical_notes')
                ->select(DB::raw("clinical_notes, Date(created_at) as created_at, visit_id"))
                ->where('is_deleted', Config::get('constants.IS_DELETED_NO'))
                ->whereIn('visit_id', $visitIdArray)
                ->get()
                ->map(function($clinicalNote){
                    $clinicalNote->clinical_notes   = !empty($clinicalNote->clinical_notes) ? json_decode($clinicalNote->clinical_notes) : $clinicalNote->clinical_notes;
                    $clinicalNote->visit_id         = $this->securityLibObj->encrypt($clinicalNote->visit_id);
                    return $clinicalNote;
                });
    }

    private function getVisitPrescribedMedicine($visitIdArray=[]){
        $visitIdArray = !empty(array_filter($visitIdArray)) ? $visitIdArray : [0];
        return DB::table( 'patient_medication_history' )
                ->select( 
                        DB::raw("
                        medicines.medicine_name,
                        medicines.medicine_dose as drug_dose,
                        patient_medication_history.pmh_id, 
                        patient_medication_history.pat_id, 
                        patient_medication_history.visit_id, 
                        patient_medication_history.medicine_id,
                        patient_medication_history.medicine_start_date,
                        patient_medication_history.medicine_end_date,
                        patient_medication_history.medicine_dose,
                        patient_medication_history.medicine_dose2,
                        patient_medication_history.medicine_dose3,
                        patient_medication_history.medicine_dose_unit,
                        patient_medication_history.medicine_duration,
                        patient_medication_history.medicine_duration_unit,
                        patient_medication_history.medicine_frequency,
                        patient_medication_history.medicine_meal_opt,
                        patient_medication_history.medicine_instractions,
                        patient_medication_history.is_discontinued,
                        patient_medication_history.medicine_route,
                        Date(patient_medication_history.created_at) as created_at,
                        drug_type.drug_type_name,
                        drug_dose_unit.drug_dose_unit_name
                        ")
                    ) 
                ->leftJoin('medicines', function($join) {
                        $join->on('patient_medication_history.medicine_id', '=', 'medicines.medicine_id');
                    })
                ->leftJoin('drug_type', function($join) {
                        $join->on('medicines.drug_type_id', '=', 'drug_type.drug_type_id')
                            ->where('drug_type.is_deleted', '=', Config::get('constants.IS_DELETED_NO'), 'and');
                    })
                ->leftJoin('drug_dose_unit', function($join) {
                        $join->on('medicines.drug_dose_unit_id', '=', 'drug_dose_unit.drug_dose_unit_id')
                            ->where('drug_dose_unit.is_deleted', '=', Config::get('constants.IS_DELETED_NO'), 'and');
                    })
                ->where( 'patient_medication_history.is_deleted',  Config::get('constants.IS_DELETED_NO') )
                ->whereIn( 'patient_medication_history.visit_id',  $visitIdArray)
                ->orderBy('pmh_id', 'desc')
                ->get()
                ->map(function($patientMedication){
                    $patientMedication->pmh_id                          = $this->securityLibObj->encrypt($patientMedication->pmh_id);
                    $patientMedication->pat_id                          = $this->securityLibObj->encrypt($patientMedication->pat_id);
                    $patientMedication->visit_id                        = $this->securityLibObj->encrypt($patientMedication->visit_id);
                    $patientMedication->medicine_id                     = $this->securityLibObj->encrypt($patientMedication->medicine_id);
                    $patientMedication->medicine_start_date             = $patientMedication->medicine_start_date;
                    $patientMedication->medicine_start_date_formatted   = !empty($patientMedication->medicine_start_date) ? date('d/m/Y', strtotime($patientMedication->medicine_start_date)) : $patientMedication->medicine_start_date;
                    $patientMedication->medicine_end_date               = $patientMedication->medicine_end_date;
                    $patientMedication->medicine_end_date_formatted     = !empty($patientMedication->medicine_end_date) ? date('d/m/Y', strtotime($patientMedication->medicine_end_date)) : $patientMedication->medicine_end_date;
                    $patientMedication->medicine_frequency              = $patientMedication->medicine_frequency;
                    $patientMedication->medicine_frequencyVal           = $this->staticDataConfigObj->getMedicationsFector('medicine_frequency', $patientMedication->medicine_frequency);
                    $patientMedication->medicine_duration_unitVal       = $this->staticDataConfigObj->getMedicationsFector('medicine_duration_unit', $patientMedication->medicine_duration_unit);
                    $patientMedication->medicine_duration_unit          = $patientMedication->medicine_duration_unit;
                    $patientMedication->medicine_dose_unitVal           = $patientMedication->drug_dose_unit_name; 
                    $patientMedication->medicine_dose_unit              = $patientMedication->medicine_dose_unit;
                    $patientMedication->medicine_meal_optVal            = $this->staticDataConfigObj->getMedicationsFector('medicine_meal_opt', $patientMedication->medicine_meal_opt);
                    $patientMedication->medicine_meal_opt               = (string) $patientMedication->medicine_meal_opt;
                    $patientMedication->is_end_date_past                = (!empty($patientMedication->medicine_end_date) && (strtotime($patientMedication->medicine_end_date) < strtotime(date('Y-m-d')))) ? 1 : 0 ;  
                    $patientMedication->medicine_instractions           = !empty($patientMedication->medicine_instractions) ? json_decode($patientMedication->medicine_instractions) : [] ;  
                    return $patientMedication;
                });
    }

    // FUNCTION WILL BE USEING FOR PATIENT LIST FILTERING FROM REPORTS COMPONENT
    public function getPatientListForReportFilter($requestData){

        $selectData = [DB::raw('count(*) OVER() AS total'), 'users.created_at', 'users.user_firstname','users.user_lastname','users.user_id','patients.pat_phone_num', 'patients.pat_id', 'patients.pat_code', 'patients.pat_profile_img', 'patients.pat_locality', 'patients.pat_pincode', 'users.user_gender', 'users.user_mobile', 'users.user_email','patients_visits.visit_id','patients.pat_title','users.user_country_code','patients.pat_dob', 'patient_groups.pat_group_name', 'patients.pat_emergency_contact_number'];
        
        $whereData = array(
                        'users.is_deleted'                  => Config::get('constants.IS_DELETED_NO'),
                        'patients.is_deleted'               => Config::get('constants.IS_DELETED_NO'),
                        'patients_visits.is_deleted'        => Config::get('constants.IS_DELETED_NO'),
                        'doctor_patient_relation.user_id'   => $requestData['user_id'],
                        'users.user_type'                   => Config::get('constants.USER_TYPE_PATIENT'),
                        'patients_visits.visit_type'        => Config::get('constants.PROFILE_VISIT_TYPE'),
                    );

        $listQuery = DB::table('users')
                        ->join('patients', 'patients.user_id', '=', 'users.user_id')
                        ->join('doctor_patient_relation', 'doctor_patient_relation.pat_id', '=','users.user_id' )
                        ->join('patients_visits', 'patients_visits.pat_id', '=','users.user_id' )
                        ->leftJoin('patient_groups',function($join) {
                            $join->on('patient_groups.pat_group_id', '=', "patients.pat_group_id")
                                ->where('patient_groups.is_deleted', Config::get('constants.IS_DELETED_NO'), 'and');
                        })
                        ->leftJoin('visit_symptoms',function($join) {
                            $join->on('visit_symptoms.pat_id', '=', "users.user_id")
                                ->where('visit_symptoms.is_deleted', Config::get('constants.IS_DELETED_NO'), 'and');
                        })
                        ->leftJoin('patients_visit_diagnosis',function($join) {
                            $join->on('patients_visit_diagnosis.pat_id', '=', "users.user_id")
                                ->where('patients_visit_diagnosis.is_deleted', Config::get('constants.IS_DELETED_NO'), 'and');
                        })
                        ->select($selectData)
                        ->where($whereData);    

        if(!empty($requestData['filtered'])){
            foreach ($requestData['filtered'] as $key => $value) {
                
                $whereGender = $value['value'];
                if(stripos($value['value'], 'male') !== false)
                {
                    $whereGender = 1;
                } else if( stripos($value['value'], 'female' !== false) ) {
                    $whereGender = 2;                    
                } else if( stripos($value['value'], 'other') !== false ){
                    $whereGender = 3;                    
                }

                if(!empty($value['value'])){
                    $listQuery = $listQuery->where(function ($listQuery) use ($value, $whereGender){
                                    $listQuery
                                    ->where('user_email', 'ilike', "%".$value['value']."%")
                                    ->orWhere('users.user_firstname', 'ilike', '%'.$value['value'].'%')
                                    ->orWhere('pat_locality', 'ilike', '%'.$value['value'].'%')
                                    ->orWhere('patient_groups.pat_group_name', 'ilike', '%'.$value['value'].'%')
                                    ->orWhere(DB::raw('CAST(user_mobile AS TEXT)'), 'ilike', '%'.$value['value'].'%')
                                    ->orWhere(DB::raw('CAST(pat_pincode AS TEXT)'), 'ilike', '%'.$value['value'].'%')
                                    ->orWhere(DB::raw('CAST(pat_code AS TEXT)'), 'ilike', '%'.$value['value'].'%')
                                    ->orWhere(DB::raw('CAST(user_gender AS TEXT)'), 'ilike', '%'.$whereGender.'%');
                                });
                }
            }
        }

        $extraFilterArray = [];
        if(isset($requestData['state_id']) && !empty($requestData['state_id'])){
            $extraFilterArray = array_merge($extraFilterArray, ['patients.state_id' => $this->securityLibObj->decrypt($requestData['state_id'])]);
        }
        if(isset($requestData['city_id']) && !empty($requestData['city_id'])){
            $extraFilterArray = array_merge($extraFilterArray, ['patients.city_id' => $this->securityLibObj->decrypt($requestData['city_id'])]);
        }
        if(isset($requestData['group_id']) && !empty($requestData['group_id'])){
            $extraFilterArray = array_merge($extraFilterArray, ['patients.pat_group_id' => $this->securityLibObj->decrypt($requestData['group_id'])]);
        }
        if(isset($requestData['doc_ref_id']) && !empty($requestData['doc_ref_id'])){
            $extraFilterArray = array_merge($extraFilterArray, ['patients.doc_ref_id' => $this->securityLibObj->decrypt($requestData['doc_ref_id'])]);
        }
        if(isset($requestData['symptoms_id']) && !empty($requestData['symptoms_id'])){
            $extraFilterArray = array_merge($extraFilterArray, ['visit_symptoms.symptom_id' => $this->securityLibObj->decrypt($requestData['symptoms_id'])]);
        }
        if(isset($requestData['diagnosis_id']) && !empty($requestData['diagnosis_id'])){
            $extraFilterArray = array_merge($extraFilterArray, ['patients_visit_diagnosis.visit_diagnosis_id' => $this->securityLibObj->decrypt($requestData['diagnosis_id'])]);
        }

        if(!empty($extraFilterArray)){
            $listQuery->where($extraFilterArray);
        }

        $fromDate = NULL;
        $toDate   = NULL;
        $today    = Carbon::now();
        if(isset($requestData['from_date']) && !empty($requestData['from_date'])){
            $fromDate = Carbon::parse($requestData['from_date'])->format('Y-m-d 00:00:00'); // date('Y-m-d 00:00:00', strtotime());
        } 
        
        if(isset($requestData['to_date']) && !empty($requestData['to_date'])){
            $toDate = Carbon::parse($requestData['to_date'])->format('Y-m-d 23:59:59');
        }

        if(isset($requestData['from_date']) && !empty($requestData['from_date']) && empty($requestData['to_date']))
        {
            $toDate = Carbon::parse($today)->format('Y-m-d 23:59:59');
        }

        if(isset($requestData['to_date']) && !empty($requestData['to_date']) && empty($requestData['from_date']))
        {
            $fromDate = Carbon::parse($toDate)->subMonth()->format('Y-m-d 00:00:00');
        }

        if(!empty($fromDate) && !empty($toDate)){
            $listQuery->whereBetween('users.created_at', [$fromDate, $toDate]);
        }

        if(!empty($requestData['sorted'])){
            foreach ($requestData['sorted'] as $sortKey => $sortValue) {
                $orderBy = $sortValue['desc'] ? 'desc' : 'asc';

                if($sortValue['id'] == 'created_at'){
                    $listQuery->orderBy('users.created_at', $orderBy);                    
                } else {
                    $listQuery->orderBy($sortValue['id'], $orderBy);                    
                }
            }
        } else {
            $listQuery->orderBy('users.user_id', 'desc');            
        }

        $listQuery->groupBy('patients.pat_id', 'users.user_id', 'patients_visits.visit_id', 'patient_groups.pat_group_name');

        if($requestData['page'] > 0){
            $offset = $requestData['page'] * $requestData['pageSize'];
        }else{
            $offset = 0;            
        }
        $count = $listQuery->count();

        $patientList['result']  = $listQuery
                                ->offset($offset)
                                ->limit($requestData['pageSize'])
                                ->get()
                                ->map(function($patientListData){
                                    $patientListData->pat_profile_img = (!empty($patientListData->pat_profile_img) ? $patientListData->pat_profile_img : '');
                                    $patientListData->pat_id = $this->securityLibObj->encrypt($patientListData->pat_id);
                                    $patientListData->user_id = $this->securityLibObj->encrypt($patientListData->user_id);
                                    $patientListData->visit_id = $this->securityLibObj->encrypt($patientListData->visit_id);
                                    $patientListData->pat_profile_img = !empty($patientListData->pat_profile_img) ? $this->securityLibObj->encrypt($patientListData->pat_profile_img) : 'default';
                                    return $patientListData;
                                }); 

        $count = isset($patientList['result'][0]) ? $patientList['result'][0]->total : 0;
        $patientList['count']   = $count; 
        $patientList['pageSize']= $requestData['pageSize'];
        $patientList['pages']   = ceil($count/$requestData['pageSize']);  

        return $patientList;
    }
}
