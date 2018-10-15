<?php

Route::group(['module' => 'Services', 'middleware' => ['api'], 'namespace' => 'App\Modules\Services\Controllers'], function() {

    Route::resource('services', 'ServicesController');

});
