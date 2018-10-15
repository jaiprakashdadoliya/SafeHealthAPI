<?php

namespace App\Modules\MedicalCertificates\Controllers;

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
use App\Modules\MedicalCertificates\Models\MedicalCertificates;

/**
 * MedicalCertificatesController
 *
 * @package                Safehealth
 * @subpackage             MedicalCertificatesController
 * @category               Controller
 * @DateOfCreation         27 june 2018
 * @ShortDescription       This controller to handle all the operation related to 
                           MedicalCertificates
 **/

class MedicalCertificatesController extends Controller
{
    use SessionTrait, RestApi;

    // @var Array $http_codes
    // This protected member contains Http Status Codes
    protected $http_codes = [];
    protected $primaryKey = 'mc_id';
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct() {
        $this->http_codes = $this->http_status_codes();
        
        // Init security library object
        $this->securityLibObj = new SecurityLib();

        // Init exception library object
        $this->exceptionLibObj = new ExceptionLib();
        // Init exception library object
        $this->medicalCertificatesModelObj = new MedicalCertificates();
    }

    /**
     * @DateOfCreation        26 Sept 2018
     * @ShortDescription      This function is responsible for getting Medical Certificates Data record
     * @param                 Array $request   
     * @return                Array of status and message
     */
    public function getMedicalCertificatesData(Request $request) 
    {
        $requestData = $this->getRequestData($request);
        $requestData['user_id'] = (in_array($request->user()->user_type, Config::get('constants.USER_TYPE_STAFF'))) ? $request->user()->created_by : $request->user()->user_id;

        $getMedicalCertificatesDataRecord = $this->medicalCertificatesModelObj->getMedicalCertificatesData($requestData);

        return $this->resultResponse(
                Config::get('restresponsecode.SUCCESS'), 
                $getMedicalCertificatesDataRecord, 
                [],
                trans('Doctors::messages.medical_certificates_data_fetched_success'),
                $this->http_codes['HTTP_OK']
            );    
    }
    /**
     * @DateOfCreation      11 July 2018
     * @ShortDescription    This function is responsible for creating new or updating an 
                            existing MedicalCertificates details.
     * @param               $request - Request object for request data
     * @return              \Illuminate\Http\Response
     */
    public function saveMedicalCertificatesData(Request $request) {
        $extra = [];
        $requestData = $this->getRequestData($request);
        $requestData['user_id'] = (in_array($request->user()->user_type, Config::get('constants.USER_TYPE_STAFF'))) ? $request->user()->created_by : $request->user()->user_id;

        unset($requestData['mc_text_old']);

        $mcId = (array_key_exists('mc_id', $requestData)) ? $requestData['mc_id'] : false;

        if($mcId && !empty($mcId)){
            $requestData['mc_id'] = $this->securityLibObj->decrypt($requestData['mc_id']);
            $new = false;
        }else{
            $new = true;
        }

        $requestData['resource_type'] = Config::get('constants.RESOURCE_TYPE_WEB');
        
        // validate, is query executed successfully
        $medicalCertificatesDataSaved = $this->medicalCertificatesModelObj->saveMedicalCertificatesData($requestData);

        // validate, is query executed successfully
        if($medicalCertificatesDataSaved) {
            return $this->resultResponse(
                        Config::get('restresponsecode.SUCCESS'),
                        $medicalCertificatesDataSaved,
                        [],
                        ($new) ? trans('MedicalCertificates::messages.medical_certificates_data_added') :  trans('MedicalCertificates::messages.medical_certificates_data_updated'),
                        '',
                        $this->http_codes['HTTP_OK']
                    );
        }else{
            return $this->resultResponse(
                        Config::get('restresponsecode.ERROR'),
                        [],
                        trans('MedicalCertificates::messages.medical_certificates_data_failed'),
                        [],
                        $this->http_codes['HTTP_OK']
                    );
        }
    }
}
