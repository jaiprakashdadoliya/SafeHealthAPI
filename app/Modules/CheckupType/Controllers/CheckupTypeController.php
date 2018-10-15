<?php

namespace App\Modules\CheckupType\Controllers;

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
use App\Modules\CheckupType\Models\CheckupType as CheckupType;
use App\Traits\FxFormHandler;

/**
 * CheckupTypeController
 *
 * @package                ILD India Registry
 * @subpackage             CheckupTypeController
 * @category               Controller
 * @DateOfCreation         04 Oct 2018
 * @ShortDescription       This controller to handle all the operation related to 
                           setup CheckupType
 **/
class CheckupTypeController extends Controller
{

    use SessionTrait, RestApi,FxFormHandler;

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

        // Init CheckupType Model Object
        $this->checkupTypeObj = new CheckupType();

        // Init exception library object
        $this->exceptionLibObj = new ExceptionLib();

         // Init exception library object
        $this->utilityLibObj = new UtilityLib();  

    }

    /**
     * @DateOfCreation        04 Oct 2018
     * @ShortDescription      This function is responsible for get Checkup Type list 
     * @param                 Array $request   
     * @return                Array of status and message
     */
    public function getCheckupTypeList(Request $request)
    {
        $requestData = $this->getRequestData($request);
        $requestData['user_id'] = ($request->user()->user_type == Config::get('constants.USER_TYPE_DOCTOR')) ? $request->user()->user_id : $request->user()->created_by;
        $getCheckupTypeList = $this->checkupTypeObj->getCheckupTypeList($requestData);

        return $this->resultResponse(
                Config::get('restresponsecode.SUCCESS'), 
                $getCheckupTypeList, 
                [],
                trans('CheckupType::messages.checkup_type_list'),
                $this->http_codes['HTTP_OK']
            );    
    }

    /**
    * @DateOfCreation        04 Oct 2018
    * @ShortDescription      This function is responsible for delete visit WorkEnvironment Data 
    * @param                 Array $wefId   
    * @return                Array of status and message
    */
    public function destroy(Request $request)
    {   
        $requestData = $this->getRequestData($request);
        $primaryKey = $this->checkupTypeObj->getTablePrimaryIdColumn();
        $primaryId = $requestData[$primaryKey];
        $primaryId = $this->securityLibObj->decrypt($primaryId);
        $isPrimaryIdExist = $this->checkupTypeObj->isPrimaryIdExist($primaryId);
        if(!$isPrimaryIdExist){
            return $this->resultResponse(
                Config::get('restresponsecode.ERROR'), 
                [], 
                [$primaryKey => [trans('CheckupType::messages.checkup_type_not_exist')]],
                trans('CheckupType::messages.checkup_type_not_exist'), 
                $this->http_codes['HTTP_OK']
            ); 
        }

        $deleteDataResponse   = $this->checkupTypeObj->doDeleteRequest($primaryId);
        if($deleteDataResponse){
            return $this->resultResponse(
                Config::get('restresponsecode.SUCCESS'), 
                [], 
                [],
                trans('CheckupType::messages.checkup_type_deleted'),
                $this->http_codes['HTTP_OK']
            );
        }
        return $this->resultResponse(
            Config::get('restresponsecode.ERROR'), 
            [], 
            [],
            trans('CheckupType::messages.checkup_type_not_deleted'),
            $this->http_codes['HTTP_OK']
        );

    }

    /**
     * @DateOfCreation        04 Oct 2018
     * @ShortDescription      This function is responsible to get the WorkEnvironment add
     * @return                Array of status and message
     */
    public function store(Request $request)
    {
        $userId = ($request->user()->user_type == Config::get('constants.USER_TYPE_DOCTOR')) ? $request->user()->user_id : $request->user()->created_by;
        $tableName   = $this->checkupTypeObj->getTableName();
        $primaryKey  = $this->checkupTypeObj->getTablePrimaryIdColumn();
        $requestData = $this->getRequestData($request);

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
                ],
                'checkup_type'=>
                [   
                    'type'=>'input',
                    'decrypt'=>false,
                    'isRequired' =>true,
                    'validation'=>'required',
                    'validationRulesMessege' => [
                    'checkup_type.required'   => trans('CheckupType::messages.checkup_type_validation_checkup_type_reurired'),
                    ],
                    'fillable' => true,
                ],
            ],
        ];

        $responseValidatorForm = $this->postValidatorForm($posConfig,$request);
        if (!$responseValidatorForm['status']) {
            return $responseValidatorForm['response'];
        }

        if($responseValidatorForm['status']){
            $fillableDataCheckup = $responseValidatorForm['response']['fillable']['checkup_type'];
            $fillableDataCheckup['user_id'] = $userId;
            
            try{
                DB::beginTransaction();
                $checkup_type_id = $fillableDataCheckup['checkup_type_id'];
                $paramCheckup = ['checkup_type'=>$fillableDataCheckup['checkup_type']];
                if(!empty($checkup_type_id)){
                    $whereData = [];
                    $whereData[$primaryKey]  = $checkup_type_id;
                    $storePrimaryId = $this->checkupTypeObj->updateRequest($paramCheckup,$whereData);
                    $message = '_update';
                }else{
                    $fetchCheckup = $this->checkupTypeObj->getAllCheckupType($fillableDataCheckup,false);
                    if(count($fetchCheckup)>0){
                        $alreadyExistError = true;
                    }else{
                        $storePrimaryId = $this->checkupTypeObj->saveCheckupType($fillableDataCheckup); 
                        $message = '_add';
                        if(!$storePrimaryId){
                            $dberror = true;
                        }
                    }
                }

                if(isset($dberror) && $dberror){
                    DB::rollback();
                    return $this->resultResponse(
                        Config::get('restresponsecode.ERROR'), 
                        [], 
                        [],
                        trans('CheckupType::messages.checkup_type_fail_add'), 
                        $this->http_codes['HTTP_OK']
                    );
                }

                if(isset($alreadyExistError) && $alreadyExistError){
                    DB::rollback();
                    return $this->resultResponse(
                        Config::get('restresponsecode.ERROR'), 
                        [], 
                        ['messages'=>[trans('CheckupType::messages.checkup_type_already_added')]],
                        trans('CheckupType::messages.checkup_type_already_added'), 
                        $this->http_codes['HTTP_OK']
                    );
                }

                if($storePrimaryId){
                    DB::commit();
                    $storePrimaryIdEncrypted = $this->securityLibObj->encrypt($storePrimaryId);
                    return $this->resultResponse(
                        Config::get('restresponsecode.SUCCESS'), 
                        [$primaryKey => $storePrimaryIdEncrypted], 
                        [],
                        trans('CheckupType::messages.checkup_type_successfull'.$message), 
                        $this->http_codes['HTTP_OK']
                    );
                }else{
                    DB::rollback();
                    return $this->resultResponse(
                        Config::get('restresponsecode.ERROR'), 
                        [], 
                        ['messages'=>[trans('CheckupType::messages.checkup_type_fail'.$message)]],
                        trans('CheckupType::messages.checkup_type_fail'.$message), 
                        $this->http_codes['HTTP_OK']
                    );
                }
            } catch (\Exception $ex) {
                DB::rollback();
                $eMessage = $this->exceptionLibObj->reFormAndLogException($ex,'CheckupTypeController', 'store');
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

    
}
