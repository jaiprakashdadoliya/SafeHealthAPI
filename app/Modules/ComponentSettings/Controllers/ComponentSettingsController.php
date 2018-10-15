<?php

namespace App\Modules\ComponentSettings\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Auth;
use Session;
use App\Traits\SessionTrait;
use App\Traits\RestApi;
use Config;
use DB;
use Illuminate\Support\Facades\Validator;
use App\Libraries\SecurityLib;
use App\Libraries\ExceptionLib;
use App\Modules\ComponentSettings\Models\ComponentSettings;

class ComponentSettingsController extends Controller
{

    use SessionTrait, RestApi;

    // @var Array $http_codes
    // This protected member contains Http Status Codes
    protected $http_codes = [];

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct() {
        $this->http_codes = $this->http_status_codes();
      
        // Init Component Settings model object
        $this->componentSettingsModelObj = new ComponentSettings();
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getComponentList(Request $request)
    {
        $requestData = $this->getRequestData($request);
        $requestData['user_id'] = $request->user()->user_id;
        $appointmentList = $this->componentSettingsModelObj->getComponentsList($requestData);
        if($appointmentList){
            return $this->resultResponse(
                    Config::get('restresponsecode.SUCCESS'), 
                    $appointmentList, 
                    [],
                    trans('ComponentSettings::messages.components_list_success'),
                    $this->http_codes['HTTP_OK']
                );
        }else{
            return $this->resultResponse(
                Config::get('restresponsecode.ERROR'), 
                [], 
                [],
                trans('ComponentSettings::messages.components_list_error'),
                $this->http_codes['HTTP_OK']
            );
        }
    }
}