<?php 
namespace App\Traits;
use DB;
use Config;
use App\Traits\RestApi;
use Illuminate\Support\Facades\Validator;
use App\Libraries\SecurityLib;
use App\Libraries\DateTimeLib;
use App\Libraries\UtilityLib;
use App\Libraries\FileLib;
use File;

/**
 * FxFormHandler
 *
 * @package                ILD Registry
 * @subpackage             FxFormHandler
 * @category               Trait
 * @DateOfCreation         28 June 2018
 * @ShortDescription       This trait is responsible to post form data input validation check and   input data and return fillter data to ready for insert into DB
 **/
trait FxFormHandler
{
    /**
    * @DateOfCreation        28 June 2018
    * @ShortDescription       post form data input validation check and   input data and return fillter data to ready for insert into DB
    * @param                 Array $requestData   
    * @return                Response (Submit attributes)
    */
    public function postValidatorForm($postConfig,$request){
        $requestData = $this->getRequestData($request);
        $securityObj    = new SecurityLib;
        $dateTimeObj    = new DateTimeLib;
        $utilityLibObj = new UtilityLib();
        $fileLibObj = new FileLib();
        $fillAbleDataKey   = [];
        $noFillAbleDataKey = [];

        $fillAbleData   = [];
        $noFillAbleData = [];
        
        if(empty($postConfig) || !is_array($postConfig) || !is_array($requestData)){
             $message = __('messages.3001');
             $errorResponse = $this->errorResponse($message);
            return ['status' => false,'response' => $errorResponse];
        }

        $rulesValidation = [];
        $validationCustomMessage = [];
        $fileUploadDataKey = [];
        $fileUploadPathDataKey = [];
        $dateDataKey = [];
        $dateTimeDataKey = [];
        $tableNameDataKey = [];

        foreach($postConfig as $tableName => $fieldDetial) {
            if(isset($errorResponse)){
                break;
            }
            if(!is_array($fieldDetial)){
                $message = __('messages.3001');
                $errorResponse = $this->errorResponse($message);
                break;
            }
            $tableNameDataKey[] = $tableName;
            foreach ($fieldDetial as $fieldName => $fieldValue) {
                if(isset($errorResponse)){
                    break;
                }

                // decrypt value if decrypt option value is true
                if($fieldValue['decrypt'] && isset($requestData[$fieldName]) && !empty($requestData[$fieldName])) {
                   $requestData[$fieldName] =  $securityObj->decrypt($requestData[$fieldName]);
                }

                if(isset($fieldValue['valueOverwrite'])){
                    $requestData[$fieldName] = $fieldValue['valueOverwrite'];
                }
                // check only isRequired option true 
                if ( isset($fieldValue['validation']) && 
                    !empty($fieldValue['validation']) && 
                    isset($fieldValue['isRequired']) && 
                    $fieldValue['isRequired']
                ) {
                    $rulesValidation[$fieldName] = $fieldValue['validation'];

                    //cutom validation message show in array
                    if(isset($fieldValue['validationRulesMessege']) && 
                        is_array($fieldValue['validationRulesMessege']) && 
                        !empty($fieldValue['validationRulesMessege'])
                    ) {

                        $validationCustomMessage = array_merge($validationCustomMessage,$fieldValue['validationRulesMessege']);
                    }
                    
                }

                // check only isRequired option false and form data not empty
                if( isset($fieldValue['isRequired']) &&
                    !$fieldValue['isRequired'] && 
                    !empty($requestData[$fieldName]) && 
                    !is_null($requestData[$fieldName])

                ){
                    $rulesValidation[$fieldName] = isset($fieldValue['validation']) && !empty($fieldValue['validation']) ? (is_array($fieldValue['validation']) ? array_merge($fieldValue['validation'],array('required')):$fieldValue['validation'] .'|required') : 'required'; 
                    
                        //cutom validation message show in array
                        if(isset($fieldValue['validationRulesMessege']) && 
                            is_array($fieldValue['validationRulesMessege']) && 
                            !empty($fieldValue['validationRulesMessege'])
                        ) {

                            $validationCustomMessage = array_merge($validationCustomMessage,$fieldValue['validationRulesMessege']);
                        }
                }

                //input type handel for date and file type
                if ( isset($fieldValue['type']) && 
                    !empty($fieldValue['type'])
                ) {
                    switch (strtolower($fieldValue['type'])) {
                        case 'file':
                            if($request->hasfile($fieldName)){
                                if( !isset($fieldValue['uploaded_path']) ||  
                                    ( isset($fieldValue['uploaded_path']) &&
                                       empty($fieldValue['uploaded_path'])       
                                    )
                                ){
                                    $message = __('messages.3002');
                                    $errorResponse = $this->errorResponse($message);
                                    break;
                                }
                                $fileUploadDataKey[$tableName][] = $fieldName;
                                $fileUploadPathDataKey[$tableName][$fieldName] = $fieldValue['uploaded_path'];
                            }
                            break;
                        case 'date':
                            if(!empty($requestData[$fieldName])){

                                $dateDataKey[$tableName][] = $fieldName;
                                $dateDataFormatKey[$tableName][$fieldName] = isset($fieldValue['currentDateFormat']) && !empty($fieldValue['currentDateFormat']) ? $fieldValue['currentDateFormat'] : 'dd/mm/YY';
                            }
                            break;
                        case 'datetime':
                            if(!empty($requestData[$fieldName])){
                                $dateTimeDataKey[$tableName][] = $fieldName;
                            }
                            break;
                        default:
                            break;
                    }
                }

                if (isset($fieldValue['fillable']) && $fieldValue['fillable']) {
                    $fillAbleDataKey[$tableName][] = $fieldName;
                }else{
                    $noFillAbleDataKey[$tableName][] = $fieldName;
                }
            }

            $fillAbleData[$tableName] = !empty($fillAbleDataKey[$tableName]) ?  $utilityLibObj->fillterArrayKey($requestData,$fillAbleDataKey[$tableName]) : [];


            $noFillAbleData[$tableName] = !empty($noFillAbleDataKey[$tableName]) ? $utilityLibObj->fillterArrayKey($requestData,$noFillAbleDataKey[$tableName]) : [];
        }

        if (isset($errorResponse)) {
            return ['status' => false, 'response' => $errorResponse];
        } 

        $responseValidation = [];
        if (!empty($rulesValidation)) {
            $responseValidation = $this->validationCheck($rulesValidation, $requestData, $validationCustomMessage);
            if(!$responseValidation['status'] && !empty($responseValidation['data'])){
                $errorResponse = $this->errorResponse(__('messages.5033'),$responseValidation['data']);
                return ['status' => false, 'response' => $errorResponse];
            }
        } 

        // input type data value formate change and file upload handel after validation success
        if(!empty($tableNameDataKey)){
            foreach ($tableNameDataKey as  $tableNameKey) {
                if (isset($dateDataKey[$tableNameKey]) && 
                    !empty($dateDataKey[$tableNameKey])
                ) {
                    foreach ($dateDataKey[$tableNameKey] as $key => $dateDataKey) {
                        $fieldDate = $requestData[$dateDataKey];
                        $currentDateFormat = $dateDataFormatKey[$tableNameKey][$dateDataKey];
                        $changeFormat = 'Y-m-d';    
                        $dateResponse = $dateTimeObj->covertUserDateToServerType($fieldDate, $currentDateFormat, $changeFormat);
                        if ($dateResponse["code"] == '5000') {
                                $errorResponse = $this->errorResponse($dateResponse["message"], [$dateDataKey => [$dateResponse["message"]]]);
                                return ['status' => false, 'response' => $errorResponse];
                        }

                        if(isset($fillAbleData[$tableNameKey][$dateDataKey])){
                            $fillAbleData[$tableNameKey][$dateDataKey] = $dateResponse['result'];
                        }

                        if(isset($noFillAbleDataKey[$tableNameKey][$dateDataKey])){
                            $noFillAbleDataKey[$tableNameKey][$dateDataKey] = $dateResponse['result'];
                        }

                    }
                }
                   
                if (isset($fileUploadDataKey[$tableNameKey]) && 
                    !empty($fileUploadDataKey[$tableNameKey])
                ) {
                    foreach ($fileUploadDataKey[$tableNameKey] as $key => $uploadDataKey) {
                        $fileUpload = $fileLibObj->fileUpload($requestData[$uploadDataKey], $fileUploadPathDataKey[$tableNameKey][$uploadDataKey]);
                            if($fileUpload["code"] == '5000'){
                                $errorResponse = $this->errorResponse($fileUpload["message"], [$uploadDataKey => [$fileUpload["message"]]]);
                                return ['status' => false, 'response' => $errorResponse];
                            }

                        if(isset($fillAbleData[$tableNameKey][$uploadDataKey])){
                            $fillAbleData[$tableNameKey][$uploadDataKey] = $fileUpload['uploaded_file'];
                        }
        
                        if(isset($noFillAbleDataKey[$tableNameKey][$uploadDataKey])){
                            $noFillAbleDataKey[$tableNameKey][$uploadDataKey] = $fileUpload['uploaded_file'];
                        }
                    }
                }
               
            }
        }

        return ['status' => true, 'response' =>['fillable' => $fillAbleData, 'nonFillable' => $noFillAbleData,'originalRequestDataWithDecrypted' =>$requestData]];  
    }

    /**
    * @DateOfCreation        28 June 2018
    * @ShortDescription      all error message respose handel
    * @param                 string $messageStringError   
    * @param                 Array $validationInputerror   
    * @return                Response (errorResponse)
    */
    protected function errorResponse($messageStringError,$validationInputerror = []) {
        $messageArrayError = empty($validationInputerror) ? ['user' => $messageStringError] : $validationInputerror;
        $errorResponse = $this->resultResponse(
                        Config::get('restresponsecode.ERROR'), 
                        [], 
                        $messageArrayError,
                        $messageStringError, 
                        $this->http_codes['HTTP_OK']
                    );
        return $errorResponse;
    }

    /**
    * @DateOfCreation        28 June 2018
    * @ShortDescription      all validation check 
    * @param                 string $messageStringError   
    * @param                 Array $validationInputerror   
    * @return                Response (errorResponse)
    */
    protected function validationCheck($validationRules, $requestData, $validationCustomMessage){
        $validator = !empty($validationCustomMessage) ? Validator::make($requestData, $validationRules, $validationCustomMessage) : Validator::make($requestData, $validationRules); 
        $status = true;
        $response = [];  
        if($validator->fails()){
            $status = false;
            $response = $validator->errors();
        }
        return ["status" => $status,"data" => $response]; 
    }
     
}   