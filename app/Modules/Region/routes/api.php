<?php

Route::group(['module' => 'Region', 'middleware' => ['api'], 'namespace' => 'App\Modules\Region\Controllers'], function() {

    Route::resource('region', 'RegionController');

});
