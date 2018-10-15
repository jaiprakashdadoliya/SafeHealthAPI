<?php

namespace App\Modules\Search\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Modules\Search\Models\Search;
use App\Traits\RestApi;
use App\Libraries\SecurityLib;
use App\Libraries\ExceptionLib;
use File;
use Response;
use Config;

class SearchController extends Controller
{

    use RestApi;

    // @var Array $http_codes
    // This protected member contains Http Status Codes
    protected $http_codes = [];

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->http_codes = $this->http_status_codes();

        // Init security library object
        $this->securityLibObj = new SecurityLib(); 
        $this->searchModelObj = new Search();
    }

   /**
    * @DateOfCreation        12 July 2018
    * @ShortDescription      This function is responsible to get the city list
    * @param                 Array $requestData   
    * @return                cities
    */
    public function index(Request $request)
    {
       $searchResult = $this->searchModelObj->getSearchCityResult($request);
       if($searchResult){
                return  $this->resultResponse(
                            Config::get('restresponsecode.SUCCESS'), 
                            $searchResult,  
                            [],
                             trans('Search::messages.get_city_successfully'), 
                            $this->http_codes['HTTP_OK']
                        );
        }else{
                return $this->resultResponse(
                    Config::get('restresponsecode.ERROR'), 
                    [], 
                    [],
                    trans('Search::messages.not_able_to_get_cities'),
                    $this->http_codes['HTTP_OK']
                );            
        }
    }

     /**
    * @DateOfCreation        12 July 2018
    * @ShortDescription      This function is responsible to specailisation list
    * @param                 Array $requestData   
    * @return                specility
    */
    public function doctorsSpecialisation(Request $request)
    {
       $searchResult = $this->searchModelObj->doctorsSpecialisation($request);
       if($searchResult){
                return  $this->resultResponse(
                            Config::get('restresponsecode.SUCCESS'), 
                            $searchResult,  
                            [],
                             trans('Search::messages.get_specaility_successfully'), 
                            $this->http_codes['HTTP_OK']
                        );
        }else{
                return $this->resultResponse(
                    Config::get('restresponsecode.ERROR'), 
                    [], 
                    [],
                    trans('Search::messages.not_able_to_get_search_result'),
                    $this->http_codes['HTTP_OK']
                );    
        }   
    }

     /**
    * @DateOfCreation        16 July 2018
    * @ShortDescription      Search doctor, clinic, speciality
    * @param                 Array $requestData   
    * @return                specility
    */
    public function getDoctorsList(Request $request)
    {
       $searchResult = $this->searchModelObj->getDoctorsList($request);
       if($searchResult){
                return  $this->resultResponse(
                            Config::get('restresponsecode.SUCCESS'), 
                            $searchResult,  
                            [],
                             trans('Search::messages.get_specaility_successfully'), 
                            $this->http_codes['HTTP_OK']
                        );
        }else{
                return $this->resultResponse(
                    Config::get('restresponsecode.ERROR'), 
                    [], 
                    [],
                    trans('Search::messages.not_able_to_get_search_result'),
                    $this->http_codes['HTTP_OK']
                );    
        }   
    }

     /**
    * @DateOfCreation        18 july 2018
    * @ShortDescription      Get a validator for an incoming User request
    * @param                 \Illuminate\Http\Request  $request
    * @return                \Illuminate\Contracts\Validation\Validator
    */
    public function getDoctorsTimeSlots(Request $request)
    {
        $requestData = $this->getRequestData($request);
        $clinicId = $this->securityLibObj->decrypt($requestData['clinic_id']);
        $filter_hours_before_10 = !empty($requestData['filters']['filter_hours_before_10']) ? $requestData['filters']['filter_hours_before_10'] : '';
        $filter_hours_after_05 = !empty($requestData['filters']['filter_hours_after_05']) ? $requestData['filters']['filter_hours_after_05'] : '';
        $slot = $requestData['slot'];
        $slotDate = $requestData['slotDate'];
        if($slot == ''){
            $inputDate = date('Y/m/d', strtotime($slotDate));
            $slotDate = $this->searchModelObj->nextAvailableSlot($inputDate, $clinicId);
        }
        if($slot == Config::get('constants.PREVIOUS_SLOT')){
          $slotDate = date('Y-m-d', strtotime($slotDate .' -1 day'));
        }
        if($slot == Config::get('constants.NEXT_SLOT')){
          $slotDate = date('Y-m-d',strtotime($slotDate .' +1 day'));
        }
        $timeSlots = $this->searchModelObj->doctorTimeSlotList($clinicId, $slotDate, $filter_hours_before_10, $filter_hours_after_05);
        if($timeSlots){
            return $this->resultResponse(
                    Config::get('restresponsecode.SUCCESS'), 
                    $timeSlots, 
                    [],
                    trans('Doctors::messages.doctors_clinic_detail'),
                    $this->http_codes['HTTP_OK']
                );
        }else{
            return $this->resultResponse(
                Config::get('restresponsecode.SUCCESS'), 
                ['date'=>$slotDate,'clinic_id'=>$this->securityLibObj->encrypt($clinicId)], 
                [],
                trans('Doctors::messages.doctors_clinic_error'),
                $this->http_codes['HTTP_OK']
            );
        }
    }

}
