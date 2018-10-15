<?php 
namespace App\Traits;
use Response;
use Config;

/**
 * RestApi
 *
 * @package                Safe Health
 * @subpackage             RestApi
 * @category               Trait
 * @DateOfCreation         23 April 2018
 * @ShortDescription       This trait is responsible to Access the config of rest 
                            and also generate the response for each request
 **/
trait RestApi
{
     /**
    * @DateOfCreation        23 Apr 2018
    * @ShortDescription      This function is responsible to get the rest_configration 
    * @return                Array 
    */
    protected function rest_config(){
        return Config::get('rest.rest_config');
    }

     /**
    * @DateOfCreation        23 Apr 2018
    * @ShortDescription      This function is responsible to get the http_status codes  
    * @return                Array
    */
    protected function http_status_codes(){
        return Config::get('rest.http_status_codes');
    }
    
    /**
    * @DateOfCreation        10 May 2018
    * @ShortDescription      This function is responsible for getting full request data 
    */
    protected function getRequestData($request){
        $requestData = $request->all();
        $requestData['ip_address'] = $request->ip();
        return $requestData;
    }

    /**
    * @DateOfCreation        23 Apr 2018
    * @ShortDescription      This function is responsible for generating the resposnse for each array 
    * @param                 Integer $code
                             Array $data
                             String $error - Default "Unknown Error"
                             String $msg
                             Integer $http_status - Default 3000   
    * @return                Response (Submit attributes)
    */
     protected function resultResponse($code, $data, $errors = [], $msg, $http_status = 3000)
     {   
        $rest_status_field    =  Config::get('rest.rest_config.rest_status_field_name');
        $rest_data_field      =  Config::get('rest.rest_config.rest_data_field_name');
        $rest_message_field   =  Config::get('rest.rest_config.rest_message_field_name');
        $rest_error_field     =  Config::get('rest.rest_config.rest_error_field_name');
        $rest_http_status     =  Config::get('rest.rest_config.rest_http_status_field_name');
        $rest_config          =  $this->rest_config();
        
        if($rest_config['rest_default_format'] == 'json'){
            $response = response()->json([
                $rest_status_field => $code,
                $rest_data_field => $data,
                $rest_message_field => $msg,
                $rest_error_field => $errors,
                $rest_http_status => $http_status
            ]);
        }
        if($rest_config['rest_default_format'] == 'xml'){
            $response = response()->xml([
                $rest_status_field => $code,
                $rest_data_field => $data,
                $rest_message_field => $msg,
                $rest_error_field => $errors,
                $rest_http_status => $http_status
            ]);
        }
        return $response;
    }

    public function curl_call($url,$param=[],$type='post',$extar_parms=[]){
        $params=!empty($param) && is_array($param) ? $param:[];
        $headers = [
                // Set here requred headers
                'content-type'    => "Content-type:application/json",
            ];
        $headersOptions = isset($extar_parms['header']) ? $extar_parms['header'] :[];
        $headers = !empty($headersOptions) ? array_merge($headers,$headersOptions) : $headers;
        $returnTransfer = isset($extar_parms['CURLOPT_RETURNTRANSFER']) ? $extar_parms['CURLOPT_RETURNTRANSFER'] : true;
        $encoding = isset($extar_parms['CURLOPT_ENCODING']) ? $extar_parms['CURLOPT_ENCODING'] : '';
        $timeOut = isset($extar_parms['CURLOPT_TIMEOUT']) ? $extar_parms['CURLOPT_TIMEOUT'] : 30000;
        $url = strtolower($type) ==strtolower('get') && !empty($params) ? $url.'?'.http_build_query($params)  :$url;
        $defaults = array(
            CURLOPT_URL => $url, 
            CURLOPT_POST => strtolower($type) == strtolower('post') ? true: false,
            CURLOPT_HTTPHEADER => array_values($headers),
            CURLOPT_RETURNTRANSFER => $returnTransfer,
            CURLOPT_ENCODING => $encoding,
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => (int) $timeOut,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        );
        if(strtolower($type) == strtolower('post')){
            $defaults[CURLOPT_POSTFIELDS] = http_build_query($params);
        }
        $ch = curl_init();
        curl_setopt_array($ch, $defaults);
        $response = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        if ($err) {
            $result=['status'=>false,'data'=>$err];
        } else {
             $result=['status'=>true,'data'=>!empty($response) ? json_decode($response,True):[]];
        }
        return $result;
    }
}