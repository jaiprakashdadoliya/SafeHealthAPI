<?php
/**
 *@ShortDescription Define all constants that going to use in the Application.
 *
 * @var Array
 */
return [
     // Sercurity keys
    'APP_NAME'          =>    env('SAFE_HEALTH_APP_NAME'),
    'APP_URL'           =>    env('SAFE_HEALTH_APP_URL'),
    'INFO_EMAIL'        =>    'info@rxhealth.in',
    'SUPPORT_EMAIL'     =>    'support@rxhealth.in',
    'UNSUBSCRIBE_EMAIL' =>    'unsubscribe@rxhealth.in',
    'ENCRYPTION_KEY1'   =>    env('SAFE_HEALTH_Encryption_key1'),
    'ENCRYPTION_KEY2'   =>    env('SAFE_HEALTH_Encryption_key2'),
    'REACT_APP_PATH'    =>    env('SAFE_HEALTH_REACT_APP_PATH'),
    'ENVIRONMENT' 	    =>    "local",
    'FILEPREFIX'	    =>    3,
    'FILEPERMISSION'    =>    0755,
    'URLEXPIRY'		    =>    5, // in minutes 
    'API_PREFIX'	    =>    'api',
    'WEB_PREFIX'	    =>    'web',
    'OPTION_YES'        =>    1,
    'OPTION_NO'         =>    2,
    'LOG_SAVE_TABLE_NAME'      => 'dbwrite_opertaion_log',
    'LOG_OPERATION_INDEX_NAME' => 'log_operation_type',
    'LOG_REQUEST_RESOURCE_TYPE_INDEX_NAME' => 'resource_type',
    'LOG_REQUEST_RESOURCE_TYPE_DEFAULT_VALUE' => 0,
    'LOG_TABLE_INSERT_OPERATION_DATA_TYPE' => 1,
    'LOG_TABLE_UPDATE_OPERATION_DATA_TYPE' => 2,
    'LOG_TABLE_INSERT_BATCH_OPERATION_DATA_TYPE' => 3,

    //SNOMEDCT API for Symptoms search
    'SNOMEDCT_API_URL'  =>    'http://13.127.176.249:8080/snomedct/api/search/search',     
    'SNOMEDCT_STATE_AVTIVE'             =>    'active',     
    'SNOMEDCT_SEMANTICTAG'              =>    'finding',     
    'SNOMEDCT_ACCEPTABILITY_PREFERRED'  =>    'preferred',     
    'SNOMEDCT_RETURNLIMIT_UNLIMITED'    => -1,     
    'SNOMEDCT_GROUP_BY_CONCEPT_FALSE'   => false,     
    'SNOMEDCT_GROUP_BY_CONCEPT_TRUE'    => true,  

    // User Type Constant    
    //'USER_TYPE_SUPER_ADMIN'    =>    1,
    'USER_TYPE_ADMIN'        =>    1,
    'USER_TYPE_DOCTOR'       =>    2,
    'USER_TYPE_PATIENT'      =>    3,
    'USER_TYPE_STAFF'        =>    [5,6,7,8],
    'USER_TYPE_LAB_MANAGER'  =>    9,
    
    // User Status Constant    
    'USER_STATUS_PENDING'   =>    1,
    'USER_STATUS_ACTIVE'    =>    2,
    'USER_STATUS_DELETED'   =>    3,
    
    'IS_DELETED_YES'        =>    1,
    'IS_DELETED_NO'         =>    2,

    'WEB_RESOURCE'                  => 1,
    'ANDROID_RESOURCE'              => 2,
    'IOS_RESOURCE'                  => 3,
    'IS_DISCONTINUED_YES'   =>    1,
    'IS_DISCONTINUED_NO'    =>    2,
    
    'USER_MOB_VERIFIED_YES' =>    1,
    'USER_MOB_VERIFIED_NO'  =>    2,
    
    'IS_ACTIVE_YES'                => 2,
    'IS_ACTIVE_NO'                  => 1,  
    // User email verification
    'USER_EMAIL_VERIFIED_YES' =>    1,
    'USER_EMAIL_VERIFIED_NO'  =>    2,
    
    // User verification object type
    'USER_VERI_OBJECT_TYPE_MOBILE' =>   1,
    'USER_VERI_OBJECT_TYPE_EMAIL'  =>   2,
    
    'SAFE_HEALTH_CSS_PATH'          =>  'public/assets/css/',
    'SAFE_HEALTH_JS_PATH'           =>  'public/assets/js/',
    'SAFE_HEALTH_IMAGE_PATH'        =>  'public/assets/images/',
    'DEFAULT_IMAGE_PATH'            =>  '/images/no-doctor-pic.png',
    'DEFAULT_SMALL_PATH'            =>  '/images/no-picx50.png',
    'DEFAULT_MEDIUM_PATH'           =>  '/images/no-picx100.png',
    'MEDIUM_THUMB_SIZE'             =>  100,
    'SMALL_THUMB_SIZE'              =>  50,

    //Resource type
    'RESOURCE_TYPE_WEB'             =>  1,
    'RESOURCE_TYPE_ANDROID'         =>  2,
    'RESOURCE_TYPE_IOS'             =>  3,
    'DEFAULT_IMAGE'                 => 'profile_image/no-doctor-pic.png',

    //Gender type
    'USER_GENDER_MALE'              => 1,
    'USER_GENDER_FEMALE'            => 2,
    'USER_GENDER_TRANSGENDER'       => 3,

    //Image & File upload 
    'IMAGE_MIME_TYPE_ALLOW'         => 'png,jpg,jpeg',
    'IMAGE_MAX_WIDTH'               => 1920,
    'IMAGE_MAX_HEIGHT'              => 1200,
    'STORAGE_MEDIA_PATH'            => 'app/public/',
    'PROFILE_IMAGE_PATH'            => 'doctor/profile-image/',
    'DOCTOR_MEDIA_PATH'             => 'doctor/doctorprofile/',
    'DOCTOR_MEDIA_THUMB_PATH'       => 'doctor/doctor-media/thumbnail/',
    'INVESTIGATION_REPORT_PATH'     => 'investigation/report/',
    'PATIENT_REPORT_PATH'           => 'patient/report/',
    'EXPORT_FILE_NAME'              => 'file_export_',
    'EXPORT_CSV_FILE_EXTENSTION'    => '.csv',
    'EXPORT_DEFAULT_CSV_FILE_NAME'  => 'file.csv',
    'EXPORT_PDF_FILE_EXTENSTION'    => '.pdf',
    'EXPORT_DEFAULT_PDF_FILE_NAME'  => 'file.pdf',

    'DOCTOR_MEDIA_ACTIVE'           => 1,
    'DOCTOR_MEDIA_PENDING'          => 2,
    'OTHER_CITY_ID'                 => '0',
    'OTHER_CITY_TEXT'               => 'Other',
    'DEFAULT_COUNTRY_NAME_SELECTED' => 'india',
    'PATIENTS_CONSENT_MIME_TYPE'    => 'pdf',
    'PATIENTS_MEDIA_PATH'           => 'patient/consent',
    'DEFAULT_IMAGE_PATH'            => 'profile-image/no-doctor-pic.png',
    'DEFAULT_IMAGE_NAME'            => 'no-doctor-pic.png',
    'PATIENTS_LABORATORY_MIME_TYPE' => 'pdf,csv,doc,docx,txt,xls,xlsx,png,jpg,jpeg,',
    'INVESTIGATION_REPORT_MIME_TYPE'=> 'pdf,png,jpg,jpeg',
    'PATIENTS_LABORATORY_PATH'      => 'patient/laboratory',
    'COUNTRY_CODE'                  => '+91',
    'DEFAULT_DOCTOR_COUNTRY_ID'     => 1,
    //Doctor Center Code
    'DOCTOR_CENTER_CODE_START'      => '100000',
    'DEFAULT_SLOT_DURATION'         => 30,
    'DEFAULT_PATIENTS_PER_SLOT'     => 4,	
    //user token verify forgot password link
    'IS_TOKEN_VALID_FORGOTPASSWORD'        => '1',
    'EXPIRE_IS_TOKEN_VALID_FORGOTPASSWORD' => '2',
    'PREVIOUS_SLOT'                 => 'previous',	
    'INITIAL_VISIT_TYPE'            => '1',
    'FOLLOW_VISIT_TYPE'             => '2',
    'PROFILE_VISIT_TYPE'            => '3',
    'INITIAL_VISIT_NUMBER'          => 1,
    'NEXT_SLOT'                     => 'next',
    //Regex exprission
    'REGEX_PASSWORD'                => '/^.*(?=.{3,})(?=.*[a-zA-Z])(?=.*[0-9])(?=.*[\d\X])(?=.*[!@*$#%]).*$/',
    'REGEX_MOBILE'                  => '/[0-9]{10}/',
    'REGEX_NUMERIC'                 => '/[0-9]/',
    'DATA_LIMIT'                    => 10,

    'SEARCH_SPECIALISATIONS_LIMIT'  => 5,
    'SEARCH_DOCTORS_LIMIT'          => 5,
    'SEARCH_CLINIC_LIMIT'           => 5,
    // Disease
    'IS_SHOW_IN_TYPE_MEDICAL_HISTORY'               => 1, // 1 for all disease of medical history
    'IS_SHOW_IN_TYPE_VISIT_FORM'                    => 1, // 1 for all disease extra factor in visit form
    'IS_SHOW_IN_TYPE_VISIT_DIAGNOSIS'               => 5, // 1 for all disease of medical history
    'IS_SHOW_IN_TYPE_FAMILY_MEDICAL_HISTORY_PART_1' => 2, 
    'IS_SHOW_IN_TYPE_FAMILY_MEDICAL_HISTORY_PART_2' => 3, 
    'IS_SHOW_IN_TYPE_FAMILY_MEDICAL_HISTORY_PART_3' => 4,
    'IS_SHOW_IN_TYPE_DIAGNOSIS_FORM'                => 6, // 6 for disease showing in diagnosis factor form only

    //DateFormat
    'DB_SAVE_DATE_FORMAT'           => 'Y-m-d', 
    'REQUEST_TYPE_GET'              => 'GET',
    'REQUEST_TYPE_POST'             => 'POST',
    'USER_VIEW_DATE_FORMAT'         => 'dd/mm/YY', 
    'REACT_WEB_DATE_FORMAT'         => 'DD/MM/YYYY',
    'USER_PROFILE_DATE_FORMAT'      => 'F d, Y',
    'DB_SAVE_DATE_TIME_FORMAT' => 'Y-m-d H:i:s', 
    'USER_VIEW_DATE_FORMAT' => 'dd/mm/YY', 
    'USER_VIEW_DATE_FORMAT_CARBON' => 'd/m/Y', 
    'REACT_WEB_DATE_FORMAT' => 'DD/MM/YYYY', 
    'REQUEST_TYPE_GET'  => 'GET',
    'REQUEST_TYPE_POST' => 'POST',
    'PATIENTS_PROFILE_IMG_PATH'         => 'patient/patientprofile/',
    'PATIENTS_PROFILE_STHUMB_IMG_PATH'  => 'patient/patientprofile/sthumb/',
    'PATIENTS_PROFILE_MTHUMB_IMG_PATH'  => 'patient/patientprofile/mthumb/',
    
    // Booking Status Constants
    'BOOKING_NOT_STARTED'   =>    1,
    'BOOKING_IN_PROGRESS'   =>    2,
    'BOOKING_COMPLETED'     =>    3,
    'BOOKING_CANCELLED'     =>    4,

    // Visit status constants
    'VISIT_COMPLETED'   => 3,
    'VISIT_IN_PROGRESS' => 2,

    // Auth token name
    'AUTH_TOKEN_NAME' => env('SAFE_HEALTH_AUTH_TOKEN_NAME'),

    // Visible in profile and visit component setting
    'IS_VISIBLE_NO'  => 1,
    'IS_VISIBLE_YES' => 2,

    'SHOW_IN_FOLLOWUP_YES'=> 2,
    'SHOW_IN_FOLLOWUP_NO' => 1,

    // Slot availability check
    'PATIENT_ALREADY_BOOKED_SLOT' => 'PATIENT_ALREADY_BOOKED_SLOT',
    'PATIENT_ALREADY_BOOKED_DAY' => 'PATIENT_ALREADY_BOOKED_DAY',

    'TIMING_SLOT_OFF' => 'Off',
    'DOCTOR_STAFF_PERMISSIONS' => [1,3,4,5,6],

    'SLOT_IS_VALID' => 'VALID',
    'SLOT_IS_INVALID' => 'INVALID',
    'TIMING_ALREADY_EQUIPPED' => 'Start Time or End Time is already equipped for the day',
    'NO_BOOKINGS_AVAILABLE' => 'No bookings are available for the selected slot',

    //CALENDAR Setting 
    'CALENDAR_SLOT_DURATION' => '30',//30 minute
    'CLINIC_DEFAULT_START_TIME' => '0900',//30 minute
    'CLINIC_DEFAULT_END_TIME' => '2000',//30 minute
    'MANGE_DEFAULT_START_TIME' => '0000',
    'MANGE_DEFAULT_END_TIME' => '2359',
    'MANGE_DEFAULT_SLOT_DURATION' => '15',
    'TIMESLOTFORMATSHOWWISE' => 'h:i A',
    'TIMESLOTIDSTORE' => 'Hi',
    'CALENDAR_PATIENT_POPUP_DATE' => 'M d,Y \a\t g:i A',
    'CALENDAR_NOT_STARTED_COLOR' => 'ec4061',//booking_status not started 
    'CALENDAR_INPROGRESS_COLOR' => 'ffb803',//booking_status in progress
    'CALENDAR_COMPLETED_COLOR' => '56af43',//booking_status completed
    'CONSULT_FEE_100'=>100,
    'CONSULT_FEE_500'=>500,
    'START_TIME'=>0,
    'END_TIME'=>1,
    'USER_ID'=>2,
    'SLOT_DURATION'=>3,
    'PATIENTS_PER_SLOT'=>4,
    'TIMING_ID'=>5,
    'CLINIC_ID'=>6,
    'WEEK_DAY'=>7,
    'DEFAULT_USER_VISIT_ID'=>0,
    'NEXT_VISIT'           => 'yes',
    'NEXT_VISIT_DAYS'      => '14',
    'INDIA_COUNTRY_CODE'  => '91',
    'PAYMENT_VALID_DAYS'  => 10,
];
