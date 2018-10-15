<?php

Route::group(['module' => 'DoctorProfile', 'middleware' => ['web'],  'namespace' => 'App\Modules\DoctorProfile\Controllers'], function() {

    Route::resource('DoctorProfile', 'DoctorProfileController');

});
