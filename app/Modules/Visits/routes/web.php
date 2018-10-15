<?php

Route::group(['module' => 'Visits', 'middleware' => ['web'], 'namespace' => 'App\Modules\Visits\Controllers'], function() {

    Route::resource('Visits', 'VisitsController');

});
