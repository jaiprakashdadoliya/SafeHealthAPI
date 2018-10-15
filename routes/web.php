<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::get('/', 'HomeController@index');

Route::get('/classification-of-ild', 'ClassificationController@index');
Route::get('/become-an-investigator', 'InvestigatorController@index');
Route::get('/term-and-condition', 'TermsAndConditionController@index');
Route::get('/contact-us', 'ContactController@index');

Route::get('/get-degrees/{id}', '\App\Modules\DoctorProfile\Controllers\DoctorDegreeController@getDegreeList');
Route::post('/save-degree', '\App\Modules\DoctorProfile\Controllers\DoctorDegreeController@store');
Route::delete('/delete-degree/{id}', '\App\Modules\DoctorProfile\Controllers\DoctorDegreeController@destroy');

Route::get('/get-membership/{id}', '\App\Modules\DoctorProfile\Controllers\DoctorMembershipController@list');
Route::post('/save-membership', '\App\Modules\DoctorProfile\Controllers\DoctorMembershipController@store');
Route::delete('/delete-membership/{id}', '\App\Modules\DoctorProfile\Controllers\DoctorMembershipController@destroy');

Route::get('/get-job-profiles/{id}', '\App\Modules\DoctorProfile\Controllers\DoctorExperienceController@getExperienceList');
Route::post('/save-job-profile', '\App\Modules\DoctorProfile\Controllers\DoctorExperienceController@store');
Route::delete('/delete-job-profile/{id}', '\App\Modules\DoctorProfile\Controllers\DoctorExperienceController@destroy');

Route::post('/get-states', '\App\Modules\Region\Controllers\RegionController@getStates');
Route::post('/get-city', '\App\Modules\Region\Controllers\RegionController@getCity');

Route::post('doctor/registration', '\App\Modules\Auth\Controllers\AuthController@postDoctorRegistration');
Route::get('/verify/{userId}/{hashToken}', 'UserVerificationController@verifyUserEmail');
Route::get('forgot-password-verification/{emailToken}/{hashToken}', 'ForgotPasswordVerificationController@verifyToken');
Route::get('generate-password/{emailToken}/{hashToken}', 'ForgotPasswordVerificationController@verifyToken');
Route::post('password/reset', '\App\Modules\Auth\Controllers\AuthController@reset');