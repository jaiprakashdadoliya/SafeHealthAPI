<?php
namespace App\Libraries;
use Illuminate\Http\Request;
use App\Libraries\DateTimeLib;
use Config;
use App\Modules\Doctors\Models\Doctors as Doctors;
/**
 * UtilityLib Class
 *
 * @package                Safe Health
 * @subpackage             UtilityLib
 * @category               Library
 * @DateOfCreation         13 Apr 2018
 * @ShortDescription       This Library is responsible for Utility functions that are small but usefull
 */
class UtilityLib {
	
    /**
    * @DateOfCreation        13 Apr 2018
    * @ShortDescription      This function is responsible to generate Numeric integer
    * @param                 Integer $lenght (Default Length is 6)
    * @return                Generated Numeric Integer
    */
    public function randomNumericInteger($lenght = 6){
        return str_pad(rand(0, pow(10, $lenght)-1), $lenght, '0', STR_PAD_LEFT);           
    }
    
    /**
    * @DateOfCreation        05 Apr 2018
    * @ShortDescription      This function is responsible to generate alphabetic string
    * @param                 Integer $lenght (Default Length is 6)
    * @return                Generated alphabetic String
    */
    public function alphabeticString($lenght = 6) {
        $alphaString = '';
        $keys = range('A', 'Z');
        for ($i = 0; $i < $lenght; $i++) {
            $alphaString .= $keys[array_rand($keys)];
        }
        return $alphaString;
    }

    /**
    * @DateOfCreation        05 Apr 2018
    * @ShortDescription      This function is responsible to generate alphanumeric string
    * @param                 Integer $length (Default length is 6)
    * @return                Generated alphanumeric string
    */
    public function alphanumericString($length = 6) {
        $alphaNumericString = '';
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        for ($i = 0; $i < $length; $i++) {
            $alphaNumericString .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $alphaNumericString;
    }

     /**
    * @DateOfCreation        13 Apr 2018
    * @ShortDescription      This function is responsible to generate alphanumeric string
    * @param                 String $text
    						 String $searchchar
    * @return                integer
    */
    public function countCharacterInString($text,$searchchar)
    {
        if(!empty($text) && !empty($searchchar)){
    		$count="0"; //zero
    		for($i="0"; $i<strlen($text); $i=$i+1){
    			if(substr($text,$i,1)==$searchchar){
    			    $count=$count+1;
    			}
    		}
    		return ['code' => '1000','message' => __('messages.1015'),'result' => $countreturn];
        }else{
            return ['code' => '5000','message' => __('messages.5024'),'result' => ''];
        }
    }

     /**
    * @DateOfCreation        16 Apr 2018
    * @ShortDescription      This function is responsible for Serialize data
    * @param                 Array $data
    * @return                String ( Serialize String )
    */

    public function getSerialize($data)
    {
        if (is_array($data) || is_object($data)) {
            return ['code' => '1000','message' => __('messages.1028'),'result' => serialize($data)];
        }else{
            return ['code' => '5000','message' => __('messages.5029'),'result' => ''];
        }
    }

     /**
    * @DateOfCreation        16 Apr 2018
    * @ShortDescription      This function is responsible for UnSerialize data
    * @param                 String $data
    * @return                Array
    */

    public function getUnserialize($data)
    {
        // If it isn't a string, it isn't serialized
        if (!is_string($data)) {
            return ['code' => '5000','message' => __('messages.5030'),'result' => ''];
        }

        $data = trim($data);

        // Is it the serialized NULL value?
        if ($data === 'N;') {
            return ['code' => '5000','message' => __('messages.5031'),'result' => ''];
        }

        $length = strlen($data);

        // Check some basic requirements of all serialized strings
        if ($length < 4 || $data[1] !== ':' || ($data[$length - 1] !== ';' && $data[$length - 1] !== '}')) {
            return ['code' => '5000','message' => __('messages.5032'),'result' => ''];
        }

        // $data is the serialized false value
        if ($data === 'b:0;') {
            return ['code' => '5000','message' => __('messages.5032'),'result' => ''];
        }

        // Don't attempt to unserialize data that isn't serialized
        $uns = @unserialize($data);

        // Data failed to unserialize?
        if ($uns === false) {
            $uns = @unserialize(self::fix_broken_serialization($data));

            if ($uns === false) {
                return ['code' => '5000','message' => __('messages.5032'),'result' => ''];
            } else {
                return ['code' => '1000','message' => __('messages.1029'),'result' => $uns];
            }
        } else {
            return ['code' => '1000','message' => __('messages.1029'),'result' => $uns];
        }
    }

    /**
    * @DateOfCreation        17 Apr 2018
    * @ShortDescription      This function is responsible to format the size 
    * @param                 Integer $bytes
    * @return                String
    */
    protected function formatSize($bytes){ 
        $kb = 1024;
        $mb = $kb * 1024;
        $gb = $mb * 1024;
        $tb = $gb * 1024;
        if (($bytes >= 0) && ($bytes < $kb)) {
            return $bytes . ' B';
        } elseif (($bytes >= $kb) && ($bytes < $mb)) {
            return ceil($bytes / $kb) . ' KB';
        } elseif (($bytes >= $mb) && ($bytes < $gb)) {
            return ceil($bytes / $mb) . ' MB';
        } elseif (($bytes >= $gb) && ($bytes < $tb)) {
            return ceil($bytes / $gb) . ' GB';
        } elseif ($bytes >= $tb) {
            return ceil($bytes / $tb) . ' TB';
        } else {
            return $bytes . ' B';
        }
    }

    /**
    * @DateOfCreation        17 Apr 2018
    * @ShortDescription      This function is responsible to get the size of folder 
    * @param                 Integer $bytes
    * @return                String
    */
    function folderSize($dir){
        $total_size = 0;
        $count = 0;
        $dir_array = scandir($dir);
        foreach($dir_array as $filename){
            if($filename!=".." && $filename!="."){
                if(is_dir($dir."/".$filename)){
                    $new_foldersize = $this->foldersize($dir."/".$filename);
                    $total_size = $total_size+ $new_foldersize;
                }else if(is_file($dir."/".$filename)){
                    $total_size = $total_size + filesize($dir."/".$filename);
                    $count++;
                }
            }
        }
        return $this->formatSize($total_size);
    }

    /**
     * @DateOfCreation        13 June 2018
     * @ShortDescription      This function is responsible to fetch specific value on requestData array in given on fillable array
     * @param                 array $requestData ['user_id'=>'abc','name'=>'xyz',...,'address' => 'nmp']; 
     * @param                 array $fillable ['name','user_id']
     * @return                array ['user_id'=>'abc','name'=>'xyz']
     */
    function fillterArrayKey($requestData = [], $fillable = []) {
        $resultData = [];
        if (!empty($requestData) && !empty($fillable)) {
            $resultData = array_intersect_key($requestData, array_flip($fillable));
        }
        return $resultData;
    }

    /**
    * @DateOfCreation        13 Apr 2018
    * @ShortDescription      This function is responsible to generate Numeric integer patient Code Genrator
    * @param                 Integer $lenght (Default Length is 6)
    * @return                Generated Numeric Integer
    */
    public function patientsCodeGenrator($lenght = 6){
        return str_pad(rand(0, pow(10, $lenght)-1), $lenght, '0', STR_PAD_LEFT);           
    }

    /**
    * @DateOfCreation        26 Jun 2018
    * @ShortDescription      This function is responsible to generate Numeric integer Docotor center Code Genrator
    * @param                 Integer $maxCenterCode
    * @return                Generated Numeric Integer
    */
    public function doctorCenterCodeGenrator($maxCenterCode){
        $startCenterCode = (int) Config::get('constants.DOCTOR_CENTER_CODE_START');
        $centerCode = !empty($maxCenterCode) && $maxCenterCode >= $startCenterCode ? $maxCenterCode + 1 : $startCenterCode; 
        return $centerCode;        
    }

    /**
    * @DateOfCreation        26 June 2018
    * @ShortDescription      This function is responsible to change array key according to requirement
    * @param                 Integer $lenght (Default Length is 6)
    * @return                return array form
    */
    public function changeArrayKey($array, $key){
        $array = json_decode(json_encode($array),True);
        $array = !empty($array) && is_array($array) ? $array : [];
        $combineArray = array();
        if(!empty($array)){            
            $arrayWithNewKey = array_pluck($array, $key); //This function change array key by selected key
            return $combineArray = array_combine($arrayWithNewKey, $array);
        }else{
            return $combineArray;
        }
    }

    /**
    * @DateOfCreation        4 September 2018
    * @ShortDescription      This function is responsible to change array key according to requirement for multidimensional array
    * @param                 array, key
    * @return                return array
    */
    public function changeMultidimensionalArrayKey($array=[], $key)
    {
        $new_array=array();
        if (!empty($array)) {
            foreach ($array as $arrayKey => $arrayValue) {
                if(array_key_exists($arrayValue->$key, $new_array)){
                    $new_array[$arrayValue->$key][] = $arrayValue;
                } else{
                    $new_array[$arrayValue->$key] = [];
                    $new_array[$arrayValue->$key][] = $arrayValue;                    
                }
            }
        }
        return $new_array;
    }

    /**
    * @DateOfCreation        26 June 2018
    * @ShortDescription      This function is responsible to user given date convert to save into db format
    * @param                 Integer $dateData date in dd/mm/YY and convert into Y-m-d 
    * @param                 $errorReturnType null, false etc
    * @return                Generated Numeric Integer
    */
    public function DateConversion($dateData,$errorReturnType = false ){
        $dateTimeLibObj = new DateTimeLib();
        $dateResponse = $dateTimeLibObj->covertUserDateToServerType($dateData,Config::get('constants.USER_VIEW_DATE_FORMAT'),Config::get('constants.DB_SAVE_DATE_FORMAT'));
            if($dateResponse['code']==Config::get('restresponsecode.ERROR')){
                return $errorReturnType;
            }
        return $dateResponse['result'];
    }

   /**
    * @DateOfCreation        26 June 2018
    * @ShortDescription      This function is responsible to create slug
    * @param                 Integer $id doctor id for checking existing slug
    * @param                 String $title doctor name
    * @return                Generated alphanumeric
    */
    public function createSlug($title, $id = 0)
    {
        $docSlug = str_slug($title);
        $allSlugs = $this->getRelatedSlugs($docSlug, $id);
        if (! $allSlugs->contains('doc_slug', $docSlug)){
            return $docSlug;
        }
        for ($i = 1; $i <= 10; $i++) {
            $newSlug = $docSlug.'-'.$i;
            if (! $allSlugs->contains('doc_slug', $newSlug)) {
                return $newSlug;
            }
        }
        throw new \Exception('Can not create a unique slug');
    }

   /**
    * @DateOfCreation        26 June 2018
    * @ShortDescription      This function is responsible to checking existing slug
    * @param                 Integer $id doctor id for checking existing slug
    * @param                 String $title doctor name
    * @return                Generated alphanumeric
    */
    protected function getRelatedSlugs($docSlug, $id = 0)
    {
        return Doctors::select('doc_slug')
                ->where('doc_slug', 'like', $docSlug.'%')
                ->where('user_id', '<>', $id)
                ->get();
    }

    /**
    * @DateOfCreation        27 July 2018
    * @ShortDescription      This function is responsible to calculate BMI
    * @param                 Integer $height, $weight
    * @return                $BMI
    */
    public function calculateBMI($weight, $height){
        $res = $height/100;
        $heightconvert = pow(round($res, 2), 2);
        $resData = $weight/$heightconvert;
        return round($resData, 2);
    }

    /**
     * @DateOfCreation        17 Aug 2018
     * @ShortDescription      This function is responsible to verify array input and change to string
     * @param                 array $data   
     * @return                boolean true / false
     */
    public function arrayToStringVal($data, $isAllowArrayVal = false){
        if(is_array($data) && !empty($data)){
            $dataValue = $data[0];

            if($isAllowArrayVal){
                foreach ($data as $key => $value) {
                   if(is_null($value) || $value == ''){
                        unset($data[$key]);
                   }
                }
                $dataValue = implode(',', $data);
            }
        } else if(is_array($data) && empty($data)){
            $dataValue = NULL;                    
        }else{
            $dataValue = !empty($data) ? $data : NULL;                    
        }
        return $dataValue;
    }

    /**
    * @DateOfCreation        26 June 2018
    * @ShortDescription      This function is responsible to change object array to array format
    * @param                 Integer $lenght (Default Length is 6)
    * @return                Generated Numeric Integer
    */
    public function changeObjectToArray($array){
        $array = json_decode(json_encode($array),True);
        $array = !empty($array) && is_array($array) ? $array : [];
        return $array;
    }

    /**
    * @DateOfCreation        26 June 2018
    * @ShortDescription      This function is responsible to change db military time convert to regular time
    * @param                 Integer $dbTiming (minmum 3 character or maxmimun 4 character )
    * @return                Generated Numeric Integer
    */
    public function changeTimingFormat($dbTiming,$timeFormat= 'h:i a'){
    $time = "";
    if(!empty($timeFormat) && $dbTiming != '' && strlen($dbTiming)>= 3 &&  strlen($dbTiming)<= 4 && $dbTiming <= '2359'){
        if(strpos($dbTiming,':') === false){

            $hours =  substr(strlen($dbTiming)>3 ? $dbTiming: '0'.$dbTiming, 0,2);
            $minutes =  substr(strlen($dbTiming)>3 ? $dbTiming: '0'.$dbTiming,2);
            $hh =  substr("0" .$hours,-2);
            $mm =  substr("0". $minutes,-2);
            $time = $hh . ':' . $mm ;
            $time =date($timeFormat,strtotime($time));
        } elseif (strpos($dbTiming,':') !== false && strlen($dbTiming)>2) {
            $time =date($timeFormat,strtotime($dbTiming));
        }
    }
    return $time;
} 
}    