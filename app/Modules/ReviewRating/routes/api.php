<?php

Route::group(['module' => 'ReviewRating', 'middleware' => ['api'], 'namespace' => 'App\Modules\ReviewRating\Controllers'], function() {

    Route::resource('ReviewRating', 'ReviewRatingController');

});
