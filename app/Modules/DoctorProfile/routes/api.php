<?php

Route::group(['module' => 'DoctorProfile', 'middleware' => ['api'], 'namespace' => 'App\Modules\DoctorProfile\Controllers'], function() {

    Route::resource('DoctorProfile', 'DoctorProfileController');

});
