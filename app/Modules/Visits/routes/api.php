<?php

Route::group(['module' => 'Visits', 'middleware' => ['api'], 'namespace' => 'App\Modules\Visits\Controllers'], function() {

    Route::resource('Visits', 'VisitsController');

});
