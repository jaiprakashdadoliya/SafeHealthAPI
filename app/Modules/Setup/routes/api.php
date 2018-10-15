<?php

Route::group(['module' => 'Setup', 'middleware' => ['api'], 'namespace' => 'App\Modules\Setup\Controllers'], function() {

    Route::resource('Setup', 'SetupController');

});
