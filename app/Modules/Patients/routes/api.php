<?php

Route::group(['module' => 'Patients', 'middleware' => ['api'], 'namespace' => 'App\Modules\Patients\Controllers'], function() {

    Route::resource('Patients', 'PatientsController');

});
