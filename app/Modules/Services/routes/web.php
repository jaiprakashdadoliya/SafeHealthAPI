<?php

Route::group(['module' => 'Services', 'middleware' => ['web'], 'namespace' => 'App\Modules\Services\Controllers'], function() {

    Route::resource('services', 'ServicesController');

});
