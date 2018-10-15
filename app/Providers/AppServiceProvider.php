<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);
        
        Validator::extend('greater_than_check', function($attribute, $value, $parameters, $validator) {
            $doc_exp_start_year = $parameters[0];
            $data = $validator->getData();
            $start_year = $data[$doc_exp_start_year];
            return $value > $start_year;
        });  

        Validator::extend('greater_than_or_equal_check', function($attribute, $value, $parameters, $validator) {
            $doc_exp_start_year = $parameters[0];
            $data = $validator->getData();
            $start_year = $data[$doc_exp_start_year];
            return $value >= $start_year;
        });

        Validator::extend('slot_exists_check', function($attribute, $value, $parameters, $validator) {
            $isSlotValid = $parameters[1];
            if($isSlotValid == 'valid'){
                return true;
            }
            return false;
        });

        Validator::extend('week_slot_exists_check', function($attribute, $value, $parameters, $validator) {
            $weekday = $parameters[1];
            if($weekday == '0'){
                return true;
            }
            return false;
        });  

        Validator::extend('booking_available_check', function($attribute, $value, $parameters, $validator) {
            $isSlotValid = $parameters[1];
            if($isSlotValid == 'VALID'){
                return true;
            }
            return false;
        });  

        Validator::replacer('greater_than_check', function($message, $attribute, $rule, $parameters) {
            $message  = str_replace('doc_exp_', ' ' , 'The '. $attribute .' must be greater than the ' .$parameters[0]);
            return str_replace('_', ' ', $message);
        });

        Validator::replacer('greater_than_or_equal_check', function($message, $attribute, $rule, $parameters) {
            $message  = str_replace('doc_exp_', ' ' , 'The '. $attribute .' must be greater than or equal to the ' .$parameters[0]);
            return str_replace('_', ' ', $message);
        });

        Validator::replacer('slot_exists_check', function($message, $attribute, $rule, $parameters) {
            $message  = 'Start Time or End Time is already equiped for the day';
            return $message;
        });

        Validator::replacer('week_slot_exists_check', function($message, $attribute, $rule, $parameters) {
            $weekday = $parameters[1];
            $weekdayName = jddayofweek ( ($weekday-1), 1 );
            $message  = 'Start Time or End Time is already equiped for '.$weekdayName;
            return $message;
        });

        Validator::replacer('booking_available_check', function($message, $attribute, $rule, $parameters) {
            $slotInvalidMessage = $parameters[1];
            $message  = $slotInvalidMessage;
            return $message;
        });

    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
