<?php

Route::group(['module' => 'Region', 'middleware' => ['web'], 'namespace' => 'App\Modules\Region\Controllers'], function() {

    Route::resource('region', 'RegionController');

});
