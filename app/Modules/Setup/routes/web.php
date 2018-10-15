<?php

Route::group(['module' => 'Setup', 'middleware' => ['web'], 'namespace' => 'App\Modules\Setup\Controllers'], function() {

    Route::resource('Setup', 'SetupController');

});
