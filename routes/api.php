<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
// Routes for Auth controller for login, register, forgot etc. which not needed the login
Route::group(['module' => 'Auth','middleware' => ['api'],'namespace' => 'App\Modules\Auth\Controllers'], function() {
    Route::post('login', '\App\Modules\Auth\Controllers\AuthController@postLogin');
    Route::get('logout/{id}', '\App\Modules\Auth\Controllers\AuthController@logout');
    Route::post('password/resetToken', '\App\Modules\Auth\Controllers\AuthController@getResetToken');
    Route::get('password/reset/{userId}/{token}', '\App\Modules\Auth\Controllers\AuthController@reset');
    Route::post('doctor/registration', '\App\Modules\Auth\Controllers\AuthController@postDoctorRegistration');
    Route::get('avatars/{imageName}', 'UserController@getAvatar');
    Route::get('demoData', '\App\Modules\Auth\Controllers\AuthController@createUsersTemp');
    Route::get('test','\App\Modules\Auth\Controllers\AuthController@test');
});

Route::group(['module' => 'Doctors','middleware' => ['api'],'namespace' => 'App\Modules\Doctors\Controllers'], function() {
    Route::get('doctor/{doctor_name}', '\App\Modules\Doctors\Controllers\DoctorsController@doctorPublicProfile');
    Route::post('doctor/public/clinic', '\App\Modules\Doctors\Controllers\DoctorsController@doctorBookingDetail');
    
});

// Routes for Doctors profile experience module
Route::group(['module' => 'DoctorProfile','prefix' =>'doctors/profile','middleware' => ['auth:api'],'namespace' => 'App\Modules\DoctorProfile\Controllers'], function() {
  /*change password*/
  Route::post('password/update', '\App\Modules\DoctorProfile\Controllers\DoctorProfileController@passwordUpdate');
  /*profile update*/
  Route::get('detail', '\App\Modules\DoctorProfile\Controllers\DoctorProfileController@getProfileDetail');
  Route::get('states', '\App\Modules\DoctorProfile\Controllers\DoctorProfileController@states');
  Route::put('update', '\App\Modules\DoctorProfile\Controllers\DoctorProfileController@updateProfile');
  Route::post('cities', '\App\Modules\DoctorProfile\Controllers\DoctorProfileController@cities');
  
    Route::post('experience', '\App\Modules\DoctorProfile\Controllers\DoctorExperienceController@getExperienceList');
    Route::put('experience/update', '\App\Modules\DoctorProfile\Controllers\DoctorExperienceController@update');
    Route::post('experience/insert', '\App\Modules\DoctorProfile\Controllers\DoctorExperienceController@store');
    Route::delete('experience/delete', '\App\Modules\DoctorProfile\Controllers\DoctorExperienceController@destroy');
    
    Route::post('media/add', '\App\Modules\DoctorProfile\Controllers\DoctorMediaController@addMedia');
    Route::delete('media/delete', '\App\Modules\DoctorProfile\Controllers\DoctorMediaController@deleteMedia');
    Route::post('media/get', '\App\Modules\DoctorProfile\Controllers\DoctorMediaController@getAllMedia');
    
    Route::post('membership/list', '\App\Modules\DoctorProfile\Controllers\DoctorMembershipController@list');
    Route::post('membership/insert', '\App\Modules\DoctorProfile\Controllers\DoctorMembershipController@store');
    Route::put('membership/update', '\App\Modules\DoctorProfile\Controllers\DoctorMembershipController@update');
    Route::delete('membership/delete', '\App\Modules\DoctorProfile\Controllers\DoctorMembershipController@destroy');

    Route::post('degree', '\App\Modules\DoctorProfile\Controllers\DoctorDegreeController@getDegreeList');
    Route::post('degree/insert', '\App\Modules\DoctorProfile\Controllers\DoctorDegreeController@store');
    Route::put('degree/update', '\App\Modules\DoctorProfile\Controllers\DoctorDegreeController@update');
    Route::delete('degree/delete', '\App\Modules\DoctorProfile\Controllers\DoctorDegreeController@destroy');
    
    Route::post('specialisation', '\App\Modules\DoctorProfile\Controllers\DoctorSpecialisationsController@getSpecialisationsList');
    Route::post('specialisation/insert', '\App\Modules\DoctorProfile\Controllers\DoctorSpecialisationsController@store');
    Route::put('specialisation/update', '\App\Modules\DoctorProfile\Controllers\DoctorSpecialisationsController@update');
    Route::delete('specialisation/delete', '\App\Modules\DoctorProfile\Controllers\DoctorSpecialisationsController@destroy');
    Route::get('specialisation/master', '\App\Modules\DoctorProfile\Controllers\DoctorSpecialisationsController@getSpecialisationsOptionList');
    Route::post('specialisation/tags', '\App\Modules\DoctorProfile\Controllers\DoctorSpecialisationsController@getSpecialisationsTagList');
    
    Route::post('awards', '\App\Modules\DoctorProfile\Controllers\DoctorAwardController@showAwardList');
    Route::post('saveAward', '\App\Modules\DoctorProfile\Controllers\DoctorAwardController@saveAward');
    Route::put('saveAward', '\App\Modules\DoctorProfile\Controllers\DoctorAwardController@saveAward');
    Route::delete('deleteAward', '\App\Modules\DoctorProfile\Controllers\DoctorAwardController@deleteAward');

    /*Doctor profile route*/
    Route::get('detail', '\App\Modules\DoctorProfile\Controllers\DoctorProfileController@getProfileDetail');
    Route::post('update', '\App\Modules\DoctorProfile\Controllers\DoctorProfileController@updateProfile');
    Route::post('update-image', '\App\Modules\DoctorProfile\Controllers\DoctorProfileController@updateImage');
    
    /*timing route*/  
    Route::post('timing', '\App\Modules\DoctorProfile\Controllers\TimingController@getTimingList');
    Route::put('timing/update', '\App\Modules\DoctorProfile\Controllers\TimingController@updateTiming');
    Route::post('timing/insert', '\App\Modules\DoctorProfile\Controllers\TimingController@createTiming');
    Route::get('deleteTiming', '\App\Modules\DoctorProfile\Controllers\TimingController@deleteTiming');
    Route::get('disease-list', '\App\Modules\Visits\Controllers\PastMedicationHistoryController@getDiseaseList');
});

// Routes for Patients module
Route::group(['module' => 'Patients','prefix' =>'patients/profile','middleware' => ['auth:api','App\Http\Middleware\DoctorsStaffMiddleware'],'namespace' => 'App\Modules\Patients\Controllers'], function() {
    Route::post('insert', '\App\Modules\Patients\Controllers\PatientsController@store');
    Route::put('update', '\App\Modules\Patients\Controllers\PatientsController@update');
    Route::post('/list', '\App\Modules\Patients\Controllers\PatientsController@getPatientList');
    Route::post('/visit-id', '\App\Modules\Patients\Controllers\PatientsController@getPatientVisitId');
    Route::post('/new-visit-id', '\App\Modules\Patients\Controllers\PatientsController@createPatientFollowUpVisitId');
    Route::post('update-image', '\App\Modules\Patients\Controllers\PatientProfileController@updateImage');
});

// Routes for Patients module
Route::group(['module' => 'Accounts','prefix' =>'accounts','middleware' => ['auth:api','App\Http\Middleware\DoctorsStaffMiddleware'],'namespace' => 'App\Modules\Accounts\Controllers'], function() {
    Route::post('payments-history', '\App\Modules\Accounts\Controllers\AccountsController@paymentsHistory');
    Route::post('invoices-history', '\App\Modules\Accounts\Controllers\AccountsController@invoicesHistory');

});

Route::post('/get-states', '\App\Modules\Region\Controllers\RegionController@getStates');
Route::post('/get-city', '\App\Modules\Region\Controllers\RegionController@getCity');
Route::get('/get-country', '\App\Modules\Region\Controllers\RegionController@getCountry');

// Routes for setup module
Route::group(['module' => 'Setup','prefix' =>'setup','middleware' => ['auth:api'],'namespace' => 'App\Modules\Setup\Controllers'], function() {
    Route::get('symptoms/{symptomName?}', '\App\Modules\Setup\Controllers\SymptomsController@getSymptomsOptionList');
    Route::get('staticdata', '\App\Modules\Setup\Controllers\staticDataConfigController@getStaticDataConfigList');  
    Route::post('symptoms/search', '\App\Modules\Setup\Controllers\SymptomsController@getSymptomsOptionListSearch');
});

// Routes for visits module
Route::group(['module' => 'Visits','prefix' =>'visit','middleware' => ['auth:api'],'namespace' => 'App\Modules\Visits\Controllers'], function() {
    Route::post('symptoms/add', '\App\Modules\Visits\Controllers\SymptomsController@addSymptom');
    Route::post('symptoms/list', '\App\Modules\Visits\Controllers\SymptomsController@getSymptomsData');
    Route::put('symptoms/update', '\App\Modules\Visits\Controllers\SymptomsController@updateSymptom');
    Route::delete('symptoms/delete', '\App\Modules\Visits\Controllers\SymptomsController@destroy');
    Route::post('symptoms/details', '\App\Modules\Visits\Controllers\SymptomsController@getPatientSymptomsDetail');
    Route::post('symptoms/save-hopi', '\App\Modules\Visits\Controllers\SymptomsController@addUpdateHopi');

    Route::post('systemicexamination/details', '\App\Modules\Visits\Controllers\SystemicExaminationController@getDetail');
    Route::post('systemicexamination/save', '\App\Modules\Visits\Controllers\SystemicExaminationController@addUpdateSystemicExamination');
    
    Route::post('generalcheckup/add_edit', '\App\Modules\Visits\Controllers\GeneralCheckupController@addGeneralCheckup');
    Route::get('generalcheckup/get-checkup-records/{visitId}/{patientId}', '\App\Modules\Visits\Controllers\GeneralCheckupController@getGeneralCheckupByVisitID');

    Route::post('medicalhistory/details', '\App\Modules\Visits\Controllers\MedicalHistoryController@getMedicalHistoryByVisitID');
    Route::post('medicalhistory/add_edit', '\App\Modules\Visits\Controllers\MedicalHistoryController@addUpdateMedicalHistory');

    Route::get('domesticfactor/get-domestic-factor-records/{visitId}/{patientId}', '\App\Modules\Visits\Controllers\DomesticFactorsController@getDomesticFactorByVisitID');
    Route::post('domesticfactor/save', '\App\Modules\Visits\Controllers\DomesticFactorsController@addUpdateDomesticFactor');

    Route::get('socialaddiction/get-social-addiction-records/{visitId}/{patientId}', '\App\Modules\Visits\Controllers\SocialAddictionController@getSocialAddictionVisitID');
    Route::post('socialaddiction/save', '\App\Modules\Visits\Controllers\SocialAddictionController@addUpdateSocialAddiction');
     
    Route::post('familymedicalhistory/details', '\App\Modules\Visits\Controllers\FamilyMedicalHistoryController@getFamilyMedicalHistoryByVisitID');
    Route::post('familymedicalhistory/save', '\App\Modules\Visits\Controllers\FamilyMedicalHistoryController@addUpdateFamilyMedicalHistory');

    Route::post('laboratorytest/details', '\App\Modules\Visits\Controllers\LaboratoryTestController@getLabortyTestVisitID');
    Route::post('laboratorytest/save', '\App\Modules\Visits\Controllers\LaboratoryTestController@addUpdateLabortyTest');
    Route::get('laboratorytemplates', '\App\Modules\Visits\Controllers\LaboratoryTestController@getLabTemplate');

    Route::post('consultant/details', '\App\Modules\Visits\Controllers\ConsultantController@getConsultantByVisitID');
    Route::post('consultant/save', '\App\Modules\Visits\Controllers\ConsultantController@addUpdateConsultant');

    Route::post('save', '\App\Modules\Visits\Controllers\VisitsController@add_edit');
    Route::post('edit', '\App\Modules\Visits\Controllers\VisitsController@add_edit');

    Route::post('workenvironment/save', '\App\Modules\Visits\Controllers\WorkEnvironmentFactorController@store');
    Route::post('workenvironment/list', '\App\Modules\Visits\Controllers\WorkEnvironmentFactorController@getWorkEnvironmentData');
    Route::delete('workenvironment/delete', '\App\Modules\Visits\Controllers\WorkEnvironmentFactorController@destroy');

    Route::post('medication-history/save', '\App\Modules\Visits\Controllers\MedicationHistoryController@store');
    Route::post('medication-history/list', '\App\Modules\Visits\Controllers\MedicationHistoryController@getListData');
    Route::delete('medication-history/delete', '\App\Modules\Visits\Controllers\MedicationHistoryController@destroy');

    Route::post('past-medication-history/save', '\App\Modules\Visits\Controllers\PastMedicationHistoryController@store');
    Route::post('past-medication-history/list', '\App\Modules\Visits\Controllers\PastMedicationHistoryController@getListData');
    Route::delete('past-medication-history/delete', '\App\Modules\Visits\Controllers\PastMedicationHistoryController@destroy');

    Route::get('disease-list', '\App\Modules\Visits\Controllers\PastMedicationHistoryController@getDiseaseList');
    Route::post('resident-place/save', '\App\Modules\Visits\Controllers\ResidentPlaceController@store');
    Route::post('resident-place/list', '\App\Modules\Visits\Controllers\ResidentPlaceController@getListData');
    Route::delete('resident-place/delete', '\App\Modules\Visits\Controllers\ResidentPlaceController@destroy');

    Route::post('laboratoryreport/save', '\App\Modules\Visits\Controllers\LaboratoryTestController@store');
    Route::post('laboratoryreport/list', '\App\Modules\Visits\Controllers\LaboratoryTestController@getListData');
    Route::delete('laboratoryreport/delete', '\App\Modules\Visits\Controllers\LaboratoryTestController@destroy');
    Route::post('laboratoryreport/show', '\App\Modules\Visits\Controllers\LaboratoryTestController@showLaboratoryReportBySymptoms');


    Route::post('get-medicine-list', '\App\Modules\Visits\Controllers\MedicationController@getMedicineListData');
    Route::post('medication/add-edit', '\App\Modules\Visits\Controllers\MedicationController@saveMedicationData');
    Route::post('medication/multiple-add-edit', '\App\Modules\Visits\Controllers\MedicationController@saveMultipleMedicationData');
    Route::post('medication/get-patient-medication-record', '\App\Modules\Visits\Controllers\MedicationController@getPatientMedicationData');
    Route::post('medication/delete-patient-medication-record', '\App\Modules\Visits\Controllers\MedicationController@deletePatientMedicationData');
    Route::post('medication/discontinue-patient-medication-record', '\App\Modules\Visits\Controllers\MedicationController@discontinuePatientMedicationData');
    Route::post('medication/current-medications', '\App\Modules\Visits\Controllers\MedicationController@patientCurrentMedications');
    Route::post('get-medicine-data', '\App\Modules\Visits\Controllers\MedicationController@getMedicineData');
    Route::post('medication/search-medicine', '\App\Modules\Visits\Controllers\MedicationController@searchMedicine');

    Route::post('medication/save-template', '\App\Modules\Visits\Controllers\MedicationController@saveMedicationTemplate');
    Route::get('medication/templates', '\App\Modules\Visits\Controllers\MedicationController@getPatientMedicationTemplate');
    Route::post('medication/get-template', '\App\Modules\Visits\Controllers\MedicationController@getMedicationTemplate');

        
    Route::post('list', '\App\Modules\Visits\Controllers\VisitsController@getPatientVisitList');
    Route::post('get-visits-factor', '\App\Modules\Visits\Controllers\VisitsController@getNewVisitFormFector');

    Route::post('diagnosis/list', '\App\Modules\Visits\Controllers\DiagnosisController@getPatientDiagnosisHistoryList');
    Route::post('diagnosis/option-list', '\App\Modules\Visits\Controllers\DiagnosisController@getDiagnosisOptionList');
    Route::post('diagnosis/add-edit', '\App\Modules\Visits\Controllers\DiagnosisController@addUpdatePatientDiagnosis');
    Route::post('diagnosis/delete', '\App\Modules\Visits\Controllers\DiagnosisController@deletePatientDiagnosis');

    Route::post('appointments/time-slot', '\App\Modules\Visits\Controllers\VisitsController@getAppointmentTimeSlot'); 

    Route::post('clinical-notes/get-clinical-notes-list', '\App\Modules\Visits\Controllers\ClinicalNotesController@getClinicalNotesList');    
    Route::post('clinical-notes/add-edit', '\App\Modules\Visits\Controllers\ClinicalNotesController@addUpdateClinicalNotes'); 
    Route::post('get-visits-components','\App\Modules\Visits\Controllers\VisitsController@getVisitComponents');
    Route::post('visits-components','\App\Modules\Visits\Controllers\VisitsController@MasterVisitComponentsList');
    Route::post('visits-setting_components','\App\Modules\Visits\Controllers\VisitsController@UpdateVisitSettingComponent');

    Route::post('vaccination-history/save','\App\Modules\Visits\Controllers\VaccinationHistoryController@addUpdateVaccinationHistory');
    Route::post('vaccination-history/list','\App\Modules\Visits\Controllers\VaccinationHistoryController@getVaccinationHistory');
    Route::post('vaccination-history/delete','\App\Modules\Visits\Controllers\VaccinationHistoryController@deleteVaccinationHistory');

});

// Routes for Doctors Staff module
Route::group(['module' => 'ManageStaff','prefix' =>'doctors','middleware' => ['auth:api','App\Http\Middleware\DoctorsStaffMiddleware'],'namespace' => 'App\Modules\ManageStaff\Controllers'],function() {

  Route::post('getStaffList', '\App\Modules\ManageStaff\Controllers\ManageStaffController@getStaffList');
  Route::post('saveStaff', '\App\Modules\ManageStaff\Controllers\ManageStaffController@addStaff');
  Route::put('saveStaff', '\App\Modules\ManageStaff\Controllers\ManageStaffController@saveStaff');
  Route::delete('deleteStaff', '\App\Modules\ManageStaff\Controllers\ManageStaffController@deleteStaff');
});


//Clinic module
Route::group(['module' => 'Clinics','prefix' =>'clinics','middleware' => ['auth:api'],'namespace' => 'App\Modules\Clinics\Controllers'], function() {
    Route::get('list', '\App\Modules\Clinics\Controllers\ClinicsController@getClinicListForTiming');
    Route::post('getClinicList', '\App\Modules\Clinics\Controllers\ClinicsController@getClinicList');
    Route::post('saveClinic', '\App\Modules\Clinics\Controllers\ClinicsController@saveClinic');
    Route::delete('deleteClinic', '\App\Modules\Clinics\Controllers\ClinicsController@deleteClinic');
});

//bookings module
Route::group(['module' => 'Bookings','prefix' =>'bookings','middleware' => ['auth:api'],'namespace' => 'App\Modules\Bookings\Controllers'], function() {
    Route::post('add', '\App\Modules\Bookings\Controllers\BookingsController@createBooking');
    Route::post('appointments', '\App\Modules\Bookings\Controllers\BookingsController@getAppointmentList');
    Route::post('calendarlist', '\App\Modules\Bookings\Controllers\BookingsController@getAppointmentListCalendar');
    Route::get('todaysAppointments', '\App\Modules\Bookings\Controllers\BookingsController@getTodayAppointmentList');
    Route::post('patient-next-visit', '\App\Modules\Bookings\Controllers\BookingsController@getPatientNextVisitSchedule');

});

//search module
Route::group(['module' => 'Search','prefix' =>'search','namespace' => 'App\Modules\Services\Controllers'],function() {
    Route::post('cities', '\App\Modules\Search\Controllers\SearchController@index');
    Route::post('doctors/specialisation', '\App\Modules\Search\Controllers\SearchController@doctorsSpecialisation');
    Route::post('doctors', '\App\Modules\Search\Controllers\SearchController@getDoctorsList');
    Route::post('/clinics', '\App\Modules\Search\Controllers\SearchController@clinicDoctorSearch');
    Route::post('doctors/timeslots', '\App\Modules\Search\Controllers\SearchController@getDoctorsTimeSlots');
});

// Routes for services module
Route::group(['module' => 'Services','prefix' =>'doctor','middleware' => ['auth:api','App\Http\Middleware\DoctorsStaffMiddleware'],'namespace' => 'App\Modules\Services\Controllers'],function() {
  Route::post('service/list', '\App\Modules\Services\Controllers\ServicesController@servicesList');
  Route::post('service/insert', '\App\Modules\Services\Controllers\ServicesController@store');
  Route::put('service/update', '\App\Modules\Services\Controllers\ServicesController@update');
  Route::delete('service/delete', '\App\Modules\Services\Controllers\ServicesController@destroy');
});

// Routes for Medical History module
Route::group(['module' => 'Disease','prefix' =>'doctor','middleware' => ['auth:api','App\Http\Middleware\DoctorsStaffMiddleware'],'namespace' => 'App\Modules\MedicalHistory\Controllers'],function() {
  Route::post('disease/list', '\App\Modules\MedicalHistory\Controllers\MedicalHistoryController@diseasesList');
  Route::post('disease/save', '\App\Modules\MedicalHistory\Controllers\MedicalHistoryController@save');
  Route::put('disease/save', '\App\Modules\MedicalHistory\Controllers\MedicalHistoryController@save');
  Route::delete('disease/delete', '\App\Modules\MedicalHistory\Controllers\MedicalHistoryController@destroy');
});

// Routes for Consent Forms module
Route::group(['module' => 'ConsentForms','prefix' =>'doctor','middleware' => ['auth:api','App\Http\Middleware\DoctorsStaffMiddleware'],'namespace' => 'App\Modules\ConsentForms\Controllers'],function() {
  Route::get('consentForm/list', '\App\Modules\ConsentForms\Controllers\ConsentFormsController@ConsentFormsList');
  Route::post('consentForm/save', '\App\Modules\ConsentForms\Controllers\ConsentFormsController@save');
  Route::put('consentForm/save', '\App\Modules\ConsentForms\Controllers\ConsentFormsController@save');
  Route::delete('consentForm/delete', '\App\Modules\ConsentForms\Controllers\ConsentFormsController@destroy');
});

// Routes for appointment category module
Route::group(['module' => 'AppointmentCategory','prefix' =>'appointment','middleware' => ['auth:api','App\Http\Middleware\DoctorsStaffMiddleware'],'namespace' => 'App\Modules\AppointmentCategory\Controllers'],function() {
  Route::post('category/list', '\App\Modules\AppointmentCategory\Controllers\AppointmentCategoryController@appointmentCategoryList');
  Route::post('category/insert', '\App\Modules\AppointmentCategory\Controllers\AppointmentCategoryController@store');
  Route::put('category/update', '\App\Modules\AppointmentCategory\Controllers\AppointmentCategoryController@update');
  Route::delete('category/delete', '\App\Modules\AppointmentCategory\Controllers\AppointmentCategoryController@destroy');
  Route::post('reason/list', '\App\Modules\AppointmentCategory\Controllers\AppointmentCategoryController@getAppointmentReasons');
  
});
// Routes for appointment category module
Route::group(['module' => 'Settings','prefix' =>'settings','middleware' => ['auth:api','App\Http\Middleware\DoctorsStaffMiddleware'],'namespace' => 'App\Modules\Settings\Controllers'],function() {
    Route::post('lab-templates/list', '\App\Modules\Settings\Controllers\SettingsController@getLabtemplatesList');
    Route::post('lab-templates/insert', '\App\Modules\Settings\Controllers\SettingsController@store');
    Route::put('lab-templates/update', '\App\Modules\Settings\Controllers\SettingsController@update');
    Route::delete('lab-templates/delete', '\App\Modules\Settings\Controllers\SettingsController@destroy');
    Route::post('medication-templates', '\App\Modules\Settings\Controllers\SettingsController@getMedicineTemplate');
    Route::post('medication-templates/list', '\App\Modules\Settings\Controllers\SettingsController@getMedicineTemplateList');
    Route::post('template-medicine-list', '\App\Modules\Settings\Controllers\SettingsController@getMedicineListData');

    Route::post('medication-template/save', '\App\Modules\Settings\Controllers\SettingsController@saveMedicineTemplate');
    Route::post('medication-template/update', '\App\Modules\Settings\Controllers\SettingsController@updateMedicineTemplate');
     Route::post('medication-template/delete', '\App\Modules\Settings\Controllers\SettingsController@deleteMedicineTemplate');
    
});

// Routes for Patient Groups module
Route::group(['module' => 'PatientGroups','prefix' =>'patient_groups','middleware' => ['auth:api'],'namespace' => 'App\Modules\PatientGroups\Controllers'],function() {
  Route::post('list', '\App\Modules\PatientGroups\Controllers\PatientGroupsController@patientGroupsList');
  Route::post('insert', '\App\Modules\PatientGroups\Controllers\PatientGroupsController@store');
  Route::put('update', '\App\Modules\PatientGroups\Controllers\PatientGroupsController@update');
  Route::delete('delete', '\App\Modules\PatientGroups\Controllers\PatientGroupsController@destroy');  
});

// Routes for appointment category module for frontend
Route::group(['module' => 'AppointmentCategory','prefix' =>'appointment','middleware' => ['auth:api'],'namespace' => 'App\Modules\AppointmentCategory\Controllers'],function() {
  Route::post('reason/list', '\App\Modules\AppointmentCategory\Controllers\AppointmentCategoryController@getAppointmentReasons');
  
});

// Routes for appointment category module
Route::group(['module' => 'Referral','prefix' =>'referral','middleware' => ['auth:api'],'namespace' => 'App\Modules\Referral\Controllers'],function() {
  Route::post('doctor/list', '\App\Modules\Referral\Controllers\ReferralController@referralList');
  Route::post('doctor/insert', '\App\Modules\Referral\Controllers\ReferralController@store');
  Route::put('doctor/update', '\App\Modules\Referral\Controllers\ReferralController@update');
  Route::delete('doctor/delete', '\App\Modules\Referral\Controllers\ReferralController@destroy');

});

Route::post('bookings/isSlotAvailable', '\App\Modules\Bookings\Controllers\BookingsController@isSlotAvailable');

// Routes for Patients module
Route::group(['module' => 'Patients','prefix' =>'patients/profile','middleware' => ['auth:api'],'namespace' => 'App\Modules\Patients\Controllers'], function() {
    Route::get('/edit-profile/{userId}', '\App\Modules\Patients\Controllers\PatientsController@edit');
    Route::post('/dashboard', '\App\Modules\Patients\Controllers\PatientProfileController@getDashboard');
    Route::put('update', '\App\Modules\Patients\Controllers\PatientsController@update');
    Route::post('allergies/save', '\App\Modules\Patients\Controllers\PatientsAllergiesController@store');
    Route::post('allergies/list', '\App\Modules\Patients\Controllers\PatientsAllergiesController@getListData');
    Route::delete('allergies/delete', '\App\Modules\Patients\Controllers\PatientsAllergiesController@destroy');
    Route::post('update-image', '\App\Modules\Patients\Controllers\PatientProfileController@updateImage');

    Route::post('activity-history', '\App\Modules\Patients\Controllers\PatientsController@getPatientActivityHistory');
    Route::post('allergies/history', '\App\Modules\Patients\Controllers\PatientsAllergiesController@getAllergiesHistory');
    Route::post('allergies/save-history', '\App\Modules\Patients\Controllers\PatientsAllergiesController@addUpdateAllergiesHistory');

});

Route::get('media/{imagePath}/{imageType}', '\App\Modules\DoctorProfile\Controllers\DoctorMediaController@getMedia');
Route::get('logo', '\App\Modules\Auth\Controllers\AuthController@getLogo');
Route::get('checkCookie', '\App\Modules\Visits\Controllers\PrescriptionController@checkCookies');
Route::get('profile-image/{imagePath}', '\App\Modules\Doctors\Controllers\DoctorsController@getProfileImage');

// Routes for download
Route::group(['middleware' => ['auth:api','App\Http\Middleware\AuthTokenMiddleware']], function() {
    Route::get('visit/generate-prescription/{userId}/{visitId}/{isPrintSymptom}/{isPrintDiagnosis}/{isPrintLabTest}', '\App\Modules\Visits\Controllers\PrescriptionController@generatePrescriptionPdf');
    Route::get('doctor/consentForm/generatePdf/{consentFormId}', '\App\Modules\ConsentForms\Controllers\ConsentFormsController@generatePdf');
    Route::get('visit/laboratoryreport/download/{lr_id}', '\App\Modules\Visits\Controllers\LaboratoryTestController@downloadFile');
    Route::get('visit/laboratoryreport/view/{lr_id}/{type}', '\App\Modules\Visits\Controllers\LaboratoryTestController@downloadFile');
    Route::get('patient-profile-image/{imagePath?}', '\App\Modules\Patients\Controllers\PatientProfileController@getProfileImage');
    Route::get('patient-profile-thumb-image/{type}/{imagename}', '\App\Modules\Patients\Controllers\PatientProfileController@getThumbProfileImage');
    Route::get('visit/view-file/{id}/{filetype}', '\App\Modules\Visits\Controllers\VisitsController@viewFile');
    Route::get('report/{report_name}', '\App\Modules\Doctors\Controllers\DoctorsController@openPdfReport');
});
//Routes for Component setting module
Route::group(['module' => 'ComponentSettings', 'prefix' =>'components','middleware' => ['auth:api','App\Http\Middleware\DoctorsStaffMiddleware'], 'namespace' => 'App\Modules\ComponentSettings\Controllers'], function() {
    Route::post('list', '\App\Modules\ComponentSettings\Controllers\ComponentSettingsController@getComponentList');
});


Route::group(['module' => 'Doctors','middleware' => ['auth:api','App\Http\Middleware\DoctorsStaffMiddleware'],'namespace' => 'App\Modules\Doctors\Controllers'], function() {
    Route::post('setting/mangae-drug/save', '\App\Modules\Doctors\Controllers\ManageDrugsController@store');
    Route::post('setting/mangae-drug/list', '\App\Modules\Doctors\Controllers\ManageDrugsController@getDrugList');
    Route::delete('setting/mangae-drug/delete', '\App\Modules\Doctors\Controllers\ManageDrugsController@destroy');
    Route::get('setting/mangae-drug/optionlist', '\App\Modules\Doctors\Controllers\ManageDrugsController@optionList');
    Route::post('setting/manage-calendar', '\App\Modules\Doctors\Controllers\ManageCalendarController@getrecord');
    Route::post('setting/manage-calendar/save', '\App\Modules\Doctors\Controllers\ManageCalendarController@store');
    Route::post('manage-calendar-add', '\App\Modules\Bookings\Controllers\AppointmentController@getAppointmentDetails');
    Route::delete('manage-calendar-delete', '\App\Modules\Bookings\Controllers\AppointmentController@destroy');
    Route::post('setting/medical-certificates/get-data', '\App\Modules\MedicalCertificates\Controllers\MedicalCertificatesController@getMedicalCertificatesData');
    Route::post('setting/medical-certificates/save-data', '\App\Modules\MedicalCertificates\Controllers\MedicalCertificatesController@saveMedicalCertificatesData');
    Route::put('setting/medical-certificates/save-data', '\App\Modules\MedicalCertificates\Controllers\MedicalCertificatesController@saveMedicalCertificatesData');
});

Route::group(['module' => 'LaboratoryTests','prefix' =>'laboratory-tests','middleware' => ['auth:api'],'namespace' => 'App\Modules\LaboratoryTests\Controllers'],function() {
    Route::post('save', '\App\Modules\LaboratoryTests\Controllers\LaboratoryTestsController@store');
    Route::delete('delete', '\App\Modules\LaboratoryTests\Controllers\LaboratoryTestsController@destroy');
    Route::post('list', '\App\Modules\LaboratoryTests\Controllers\LaboratoryTestsController@getLaboratoryTestsList');
    Route::get('optionlist', '\App\Modules\LaboratoryTests\Controllers\LaboratoryTestsController@optionList');
});

Route::group(['module' => 'CheckupType','prefix' =>'checkup-type','middleware' => ['auth:api'],'namespace' => 'App\Modules\CheckupType\Controllers'],function() {
    Route::post('save', '\App\Modules\CheckupType\Controllers\CheckupTypeController@store');
    Route::delete('delete', '\App\Modules\CheckupType\Controllers\CheckupTypeController@destroy');
    Route::post('list', '\App\Modules\CheckupType\Controllers\CheckupTypeController@getCheckupTypeList');
});

Route::group(['module' => 'PaymentMode','prefix' =>'payment-mode','middleware' => ['auth:api'],'namespace' => 'App\Modules\PaymentMode\Controllers'],function() {
    Route::post('save', '\App\Modules\PaymentMode\Controllers\PaymentModeController@store');
    Route::delete('delete', '\App\Modules\PaymentMode\Controllers\PaymentModeController@destroy');
    Route::post('list', '\App\Modules\PaymentMode\Controllers\PaymentModeController@getPaymentModeList');
});

//review rating module
Route::group(['module' => 'ReviewRating','prefix' =>'patient/review','middleware' => ['auth:api'],'namespace' => 'App\Modules\ReviewRating\Controllers'],function() {
  Route::post('save', '\App\Modules\ReviewRating\Controllers\ReviewRatingController@store');
});
Route::group(['module' => 'Doctors','prefix' =>'doctor','middleware' => ['auth:api','App\Http\Middleware\DoctorMiddleware'],'namespace' => 'App\Modules\Doctors\Controllers'], function() {
  Route::post('reports', '\App\Modules\Doctors\Controllers\DoctorsController@getPatientsReport');
  Route::post('reports/filter-data', '\App\Modules\Doctors\Controllers\DoctorsController@getPatientsReportFilterData');
  Route::post('reports/get-patients', '\App\Modules\Doctors\Controllers\DoctorsController@getPatientsListData');
  Route::post('reports/income', '\App\Modules\Doctors\Controllers\DoctorsController@getIncomeReport');
});
