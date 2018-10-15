<?php

namespace App\Modules\Region\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Traits\RestApi;
use Config;
use DB;
use App\Libraries\SecurityLib;
use App\Libraries\ExceptionLib;
use App\Modules\Region\Models\City as City;
use App\Modules\Region\Models\State as State;
use App\Modules\Region\Models\Country as Country;

class RegionController extends Controller
{
    use RestApi;
    protected $http_codes = [];

    public function __construct(Request $request)
    {
        $this->http_codes = $this->http_status_codes();

        // Init security library object
        $this->securityLibObj = new SecurityLib(); 

        // Init City Model Object
        $this->cityObj = new City();

        // Init State Model Object
        $this->stateObj = new State();

        // Init Country Model Object
        $this->countryObj = new Country();
        
        // Init exception library object
        $this->exceptionLibObj = new ExceptionLib();        
    }

    /**
     * @DateOfCreation        11 june 2018
     * @ShortDescription      This function is responsible for get state list data 
     * @param                 Array $request   
     * @param                 Array $request['country_id']   
     * @return                Array of status and message and state list
     */
    public function getStates(Request $request){
        $requestData = $request->only('country_id');
        $countryId = $requestData['country_id'];
        $countryId = !empty($countryId) && !is_numeric($countryId) ? $this->securityLibObj->decrypt($countryId) : $countryId;
        $statesList = $this->stateObj->getStateListByCountryId(Config::get('constants.DEFAULT_DOCTOR_COUNTRY_ID'));
        return $this->resultResponse(
            Config::get('restresponsecode.SUCCESS'), 
            $statesList, 
            [],
            trans('register_investigator.get_states_list'),
            $this->http_codes['HTTP_OK']
        );

    }

    /**
     * @DateOfCreation        11 june 2018
     * @ShortDescription      This function is responsible for get city list data 
     * @param                 Array $request   
     * @param                 Array $request['state_id']   
     * @return                Array of status and message and city list
     */
    public function getCity(Request $request){
        $requestData = $request->only('state_id');
        $stateId = $requestData['state_id'];
        $stateId = !empty($stateId) && !is_numeric($stateId) ? $this->securityLibObj->decrypt($stateId) : $stateId;
        $cityList = !empty($stateId) && is_numeric($stateId) ?  $this->cityObj->getCityListByStateId($stateId) : [];
        return $this->resultResponse(
            Config::get('restresponsecode.SUCCESS'), 
            $cityList, 
            [],
            trans('register_investigator.get_city_list'),
            $this->http_codes['HTTP_OK']
        );  
    }

    /**
     * @DateOfCreation        11 june 2018
     * @ShortDescription      This function is responsible for get Country list data 
     * @return                Array of status and message and state list
     */
    public function getCountry(){
        $countryList = $this->countryObj->getCountryList();
        return $this->resultResponse(
            Config::get('restresponsecode.SUCCESS'), 
            $countryList, 
            [],
            trans('register_investigator.get_country_list'),
            $this->http_codes['HTTP_OK']
        );

    }
}
