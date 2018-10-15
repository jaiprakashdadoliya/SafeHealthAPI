<?php

Route::group(['module' => 'ReviewRating', 'middleware' => ['web'], 'namespace' => 'App\Modules\ReviewRating\Controllers'], function() {

    Route::resource('ReviewRating', 'ReviewRatingController');

});
