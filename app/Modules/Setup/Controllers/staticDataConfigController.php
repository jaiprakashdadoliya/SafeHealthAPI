<?php

namespace App\Modules\Setup\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Auth;
use Session;
use App\Traits\SessionTrait;
use App\Traits\RestApi;
use Config;
use Illuminate\Support\Facades\Validator;
use App\Libraries\SecurityLib;
use App\Libraries\ExceptionLib;
use App\Modules\Setup\Models\StaticDataConfig as StaticDataConfig;

/**
 * staticDataConfigController
 *
 * @package                ILD India Registry
 * @subpackage             staticDataConfigController
 * @category               Controller
 * @DateOfCreation         18 june 2018
 * @ShortDescription       This controller to handle all the operation related  to setup staticData config
 **/
class staticDataConfigController extends Controller
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
    public function __construct(Request $request)
    {
        $this->http_codes = $this->http_status_codes();

        // Init security library object
        $this->securityLibObj = new SecurityLib(); 

        // Init staticDataConfig Model Object
        $this->staticDataConfigsObj = new StaticDataConfig();

        // Init exception library object
        $this->exceptionLibObj = new ExceptionLib();

        
    }
    /**
     * @DateOfCreation        22 June 2018
     * @ShortDescription      This function is responsible to get the staticData Config list
     * @return                Array of status and message
     */
    public function getStaticDataConfigList()
    {
        $getStaticDataConfig  = $this->staticDataConfigsObj->getStaticDataConfigList();
        return $this->resultResponse(
            Config::get('restresponsecode.SUCCESS'), 
            $getStaticDataConfig, 
            [],
            trans('Setup::messages.static_data_config_list'),
            $this->http_codes['HTTP_OK']
        );
    }
}
