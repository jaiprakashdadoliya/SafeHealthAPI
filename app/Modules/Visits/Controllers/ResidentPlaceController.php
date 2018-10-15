<?php

namespace App\Modules\Visits\Controllers;

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
use App\Libraries\DateTimeLib;
use App\Libraries\UtilityLib;
use App\Modules\Visits\Models\ResidentPlace;
use App\Traits\FxFormHandler;


/**
 * ResidentPlaceController
 *
 * @package                ILD India Registry
 * @subpackage             ResidentPlaceController
 * @category               Controller
 * @DateOfCreation         18 june 2018
 * @ShortDescription       This controller to handle all the operation related to 
                           setup WorkEnvironment
 **/
class ResidentPlaceController extends Controller
{

    use SessionTrait, RestApi, FxFormHandler;

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

        // Init ResidentPlace Model Object
        $this->residentPlaceObj = new ResidentPlace();

        // Init exception library object
        $this->exceptionLibObj = new ExceptionLib();

        // Init exception library object
        $this->dateTimeLibObj = new DateTimeLib();  

        $this->utilityLibObj = new UtilityLib();        
    }
    
    /**
     * @DateOfCreation        21 May 2018
     * @ShortDescription      This function is responsible to get the WorkEnvironment add
     * @return                Array of status and message
     */
    public function store(Request $request)
    {
        $requestDataOnly = $request->only('residence_value');
        $tableName = $this->residentPlaceObj->getTableName();
        $primaryKey = $this->residentPlaceObj->getTablePrimaryIdColumn();
        $posConfig =
        [   $tableName =>
            [
                $primaryKey=>
                [   
                    'type'=>'input',
                    'decrypt'=>true,
                    'isRequired' =>false,
                    'fillable' => true,
                ],
                'visit_id'=>
                [   
                    'type'=>'input',
                    'decrypt'=>true,
                    'isRequired' =>true,
                    'validation'=>'required',
                    'fillable' => true,
                ],
                 'pat_id'=>
                [   
                    'type'=>'input',
                    'decrypt'=>true,
                    'isRequired' =>true,
                    'validation'=>'required',
                    'fillable' => true,
                ],
                'residence_value'=>
                [   
                    'type'=>'input',
                    'isRequired' =>true,
                    'validation'=>'required',
                    'validationRulesMessege' => [
                    'residence_value.required'   => trans('Visits::messages.residence_place_validation_required'),
                    ],
                    'decrypt'=>false,
                    'fillable' => true,
                ],
                'resource_type'=>
                [   
                    'type'=>'input',
                    'isRequired' =>true,
                    'decrypt'=>false,
                    'validation'=>'required',
                    'fillable' => true,
                ],
                'ip_address'=>
                [   
                    'type'=>'input',
                    'isRequired' =>true,
                    'decrypt'=>false,
                    'validation'=>'required',
                    'fillable' => true,
                ]
            ],
        ];
        $responseValidatorForm = $this->postValidatorForm($posConfig,$request);
        if (!$responseValidatorForm['status']) {
            return $responseValidatorForm['response'];
        }

        if($responseValidatorForm['status']){
            $fillableData = $responseValidatorForm['response']['fillable'][$tableName];
            try{
                if (isset($fillableData[$primaryKey]) && !empty($fillableData[$primaryKey])){
                    $whereData = [];
                    $whereData['visit_id'] = $fillableData['visit_id'];
                    $whereData['pat_id']  = $fillableData['pat_id'];
                    $whereData[$primaryKey]  = $fillableData[$primaryKey];
                    $storePrimaryId = $this->residentPlaceObj->updateRequest($fillableData,$whereData);
                } else {
                    $storePrimaryId = $this->residentPlaceObj->addRequest($fillableData);
                }

                 if($storePrimaryId){
                        $storePrimaryIdEncrypted = $this->securityLibObj->encrypt($storePrimaryId);
                        return $this->resultResponse(
                            Config::get('restresponsecode.SUCCESS'), 
                            [$primaryKey => $storePrimaryIdEncrypted], 
                            [],
                            trans('Visits::messages.residence_place_add_successfull'), 
                            $this->http_codes['HTTP_OK']
                        );
                    }else{
                        return $this->resultResponse(
                            Config::get('restresponsecode.ERROR'), 
                            [], 
                            [],
                            trans('Visits::messages.residence_place_add_fail'), 
                            $this->http_codes['HTTP_OK']
                        );
                    }
            } catch (\Exception $ex) {
                $eMessage = $this->exceptionLibObj->reFormAndLogException($ex,'ResidentPlaceController', 'store');
                return $this->resultResponse(
                    Config::get('restresponsecode.EXCEPTION'), 
                    [], 
                    [],
                    $eMessage, 
                    $this->http_codes['HTTP_OK']
                );
            }
        }
    }

    /**
     * @DateOfCreation        19 June 2018
     * @ShortDescription      This function is responsible for get WorkEnvironment Data by patId and visitId
     * @param                 encrypted integer $patId   
     * @param                 encrypted integer $visitId   
     * @return                Array of status and message
     */
    public function getListData(Request $request)
    {
        $requestData = $this->getRequestData($request);
        $getListDataResponse = $this->residentPlaceObj->getListData($requestData);
        
        return $this->resultResponse(
            Config::get('restresponsecode.SUCCESS'), 
            $getListDataResponse, 
            [],
            trans('Visits::messages.residence_place_list_successfull'),
            $this->http_codes['HTTP_OK']
        );
    }

    /**
    * @DateOfCreation        11 June 2018
    * @ShortDescription      This function is responsible for delete visit WorkEnvironment Data 
    * @param                 Array $wefId   
    * @return                Array of status and message
    */
    public function destroy(Request $request)
    {   
        $requestData = $this->getRequestData($request);
        $primaryKey = $this->residentPlaceObj->getTablePrimaryIdColumn();
        $primaryId = $requestData[$primaryKey];
        $primaryId = $this->securityLibObj->decrypt($primaryId);
        $isPrimaryIdExist = $this->residentPlaceObj->isPrimaryIdExist($primaryId);
        if(!$isPrimaryIdExist){
            return $this->resultResponse(
                Config::get('restresponsecode.ERROR'), 
                [], 
                [$primaryKey => [trans('Visits::messages.residence_place_not_exist')]],
                trans('Visits::messages.residence_place_not_exist'), 
                $this->http_codes['HTTP_OK']
            ); 
        }

        $deleteDataResponse   = $this->residentPlaceObj->doDeleteRequest($primaryId);
        if($deleteDataResponse){
            return $this->resultResponse(
                Config::get('restresponsecode.SUCCESS'), 
                [], 
                [],
                trans('Visits::messages.residence_place_data_deleted'),
                $this->http_codes['HTTP_OK']
            );
        }
        return $this->resultResponse(
            Config::get('restresponsecode.ERROR'), 
            [], 
            [],
            trans('Visits::messages.residence_place_data_not_deleted'),
            $this->http_codes['HTTP_OK']
        );

    }
}
