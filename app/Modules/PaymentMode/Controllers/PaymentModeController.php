<?php

namespace App\Modules\PaymentMode\Controllers;

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
use App\Modules\PaymentMode\Models\PaymentMode as PaymentMode;
use App\Traits\FxFormHandler;

/**
 * PaymentModeController
 *
 * @package                ILD India Registry
 * @subpackage             PaymentModeController
 * @category               Controller
 * @DateOfCreation         04 Oct 2018
 * @ShortDescription       This controller to handle all the operation related to 
                           setup PaymentMode
 **/
class PaymentModeController extends Controller
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

        // Init PaymentMode Model Object
        $this->paymentModeObj = new PaymentMode();

        // Init exception library object
        $this->exceptionLibObj = new ExceptionLib();

         // Init exception library object
        $this->utilityLibObj = new UtilityLib();  

    }

    /**
     * @DateOfCreation        04 Oct 2018
     * @ShortDescription      This function is responsible for get Payment Mode list 
     * @param                 Array $request   
     * @return                Array of status and message
     */
    public function getPaymentModeList(Request $request)
    {
        $requestData = $this->getRequestData($request);
        $requestData['user_id'] = ($request->user()->user_type == Config::get('constants.USER_TYPE_DOCTOR')) ? $request->user()->user_id : $request->user()->created_by;
        $getPaymentModeList = $this->paymentModeObj->getPaymentModeList($requestData);

        return $this->resultResponse(
                Config::get('restresponsecode.SUCCESS'), 
                $getPaymentModeList, 
                [],
                trans('PaymentMode::messages.payment_mode_list'),
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
        $primaryKey = $this->paymentModeObj->getTablePrimaryIdColumn();
        $primaryId = $requestData[$primaryKey];
        $primaryId = $this->securityLibObj->decrypt($primaryId);
        $isPrimaryIdExist = $this->paymentModeObj->isPrimaryIdExist($primaryId);
        if(!$isPrimaryIdExist){
            return $this->resultResponse(
                Config::get('restresponsecode.ERROR'), 
                [], 
                [$primaryKey => [trans('PaymentMode::messages.payment_mode_not_exist')]],
                trans('PaymentMode::messages.payment_mode_not_exist'), 
                $this->http_codes['HTTP_OK']
            ); 
        }

        $deleteDataResponse   = $this->paymentModeObj->doDeleteRequest($primaryId);
        if($deleteDataResponse){
            return $this->resultResponse(
                Config::get('restresponsecode.SUCCESS'), 
                [], 
                [],
                trans('PaymentMode::messages.payment_mode_deleted'),
                $this->http_codes['HTTP_OK']
            );
        }
        return $this->resultResponse(
            Config::get('restresponsecode.ERROR'), 
            [], 
            [],
            trans('PaymentMode::messages.payment_mode_not_deleted'),
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
        $tableName   = $this->paymentModeObj->getTableName();
        $primaryKey  = $this->paymentModeObj->getTablePrimaryIdColumn();
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
                'payment_mode'=>
                [   
                    'type'=>'input',
                    'decrypt'=>false,
                    'isRequired' =>true,
                    'validation'=>'required',
                    'validationRulesMessege' => [
                    'payment_mode.required'   => trans('PaymentMode::messages.payment_mode_validation_payment_mode_reurired'),
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
            $fillableDataPayment = $responseValidatorForm['response']['fillable']['payment_mode'];
            $fillableDataPayment['user_id'] = $userId;
            
            try{
                DB::beginTransaction();
                $payment_mode_id = $fillableDataPayment['payment_mode_id'];
                $paramPayment = ['payment_mode'=>$fillableDataPayment['payment_mode']];
                if(!empty($payment_mode_id)){
                    $whereData = [];
                    $whereData[$primaryKey]  = $payment_mode_id;
                    $storePrimaryId = $this->paymentModeObj->updateRequest($paramPayment,$whereData);
                    $message = '_update';
                }else{
                    $fetchPayment = $this->paymentModeObj->getAllPaymentMode($fillableDataPayment,false);
                    if(count($fetchPayment)>0){
                        $alreadyExistError = true;
                    }else{
                        $storePrimaryId = $this->paymentModeObj->savePaymentMode($fillableDataPayment); 
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
                        trans('PaymentMode::messages.payment_mode_fail_add'), 
                        $this->http_codes['HTTP_OK']
                    );
                }

                if(isset($alreadyExistError) && $alreadyExistError){
                    DB::rollback();
                    return $this->resultResponse(
                        Config::get('restresponsecode.ERROR'), 
                        [], 
                        ['messages'=>[trans('PaymentMode::messages.payment_mode_already_added')]],
                        trans('PaymentMode::messages.payment_mode_already_added'), 
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
                        trans('PaymentMode::messages.payment_mode_successfull'.$message), 
                        $this->http_codes['HTTP_OK']
                    );
                }else{
                    DB::rollback();
                    return $this->resultResponse(
                        Config::get('restresponsecode.ERROR'), 
                        [], 
                        ['messages'=>[trans('PaymentMode::messages.payment_mode_fail'.$message)]],
                        trans('PaymentMode::messages.payment_mode_fail'.$message), 
                        $this->http_codes['HTTP_OK']
                    );
                }
            } catch (\Exception $ex) {
                DB::rollback();
                $eMessage = $this->exceptionLibObj->reFormAndLogException($ex,'PaymentModeController', 'store');
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
