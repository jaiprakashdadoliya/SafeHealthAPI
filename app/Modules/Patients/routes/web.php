<?php

Route::group(['module' => 'Patients', 'middleware' => ['web'], 'namespace' => 'App\Modules\Patients\Controllers'], function() {

    Route::resource('Patients', 'PatientsController');

});
