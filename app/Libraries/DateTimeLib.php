<?php
namespace App\Libraries;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

/**
 * DateTimeLib Class
 *
 * @package                Safe Health
 * @subpackage             DateTimeLib
 * @category               Library
 * @DateOfCreation         05 Apr 2018
 * @ShortDescription       This Library is responsible for all security functions
 */
class DateTimeLib {

    /**
    * @DateOfCreation        16 Apr 2018
    * @ShortDescription      This function is responsible to check the date is valid or not 
    * @param                 Date $date ( date you need to check )
                             String $format ( Current date format you are using in date parameter ) 
    * @return                Array with status, message and result() 
    */
    public function validateDate($date, $format = 'Y-m-d H:i:s')
    {
        $dateTimeObj = new \DateTime();
        $result = $dateTimeObj->createFromFormat($format, $date);
        if($result){
            return ['code' => '1000','message' => __('messages.1016'),'result' => $result->format($format)];
        }else{
            return ['code' => '5000','message' => __('messages.5025'),'result' => ''];
        }
    }
    /**
    * @DateOfCreation        06 Apr 2018
    * @ShortDescription      This function is responsible to convert string to date according to 
                             the format given .
    * @param                 String $timestamp ( String must be in unixtimestamp)
                             
                             String $format ( options - dd/mm/yy H:i:s,dd/mm/YY H:i:s,dd-mm-yy H:i:s,dd-mm-YY H:i:s You can change the position of date 
                             month and year according to need) You can remove hours minute and secods also
    * @return                Array with status, message and result(Date in the format provided 
                                                                   in the parameter) 
    */
   public function timestampToDateTime($timestamp,$format)
    {
        if(!empty($timestamp) && !empty($format)){
            return ['code' => '1000','message' => __('messages.1017'),'result' => date($format,$timestamp)];
        }else{
            return ['code' => '5000','message' => __('messages.5020'),'result' => '' ];
        }
    }

     /**
    * @DateOfCreation        06 Apr 2018
    * @ShortDescription      This function is responsible to convert date to string.
    * @param                 Timestamp $date (Date to convert)
                             
                             String $format ( options - dd/mm/yy H:i:s,dd/mm/YY H:i:s,
                             dd-mm-yy H:i:s,dd-mm-YY H:i:s You can change the position of date month and year according to need) You can remove hours minute and secods also
    * @return                Array with status, message and result( timestamp )
    */
    public function dateTimeTotimestamp($date,$format)
    {
        $converted_date = $this->validateDate($date,$format);
        if(!empty($date) && !empty($converted_date['result'])){
            return ['code' => '1000','message' => __('messages.1018'),'result' => strtotime($converted_date['result'])];
        }else{
            return ['code' => '5000','message' => __('messages.5021'),'result' => ''];
        } 
    }

     /**
    * @DateOfCreation        06 Apr 2018
    * @ShortDescription      This function is responsible to convert date format.
    * @param                 Timestamp $date (Current date)
                             String $currentFormat ( options - dd/mm/yy H:i:s,dd/mm/YY H:i:s,
                             dd-mm-yy H:i:s,dd-mm-YY H:i:s You can change the position of date month and year according to need) You can remove hours minute and secods also
                             
                             String  $newformat ( options - dd/mm/yy H:i:s,dd/mm/YY H:i:s,
                             dd-mm-yy H:i:s,dd-mm-YY H:i:s You can change the position of date month and year according to need) You can remove hours minute and secods also
    * @return                Array with status, message and result ( Date in the 
                             format provided in the parameter )
    */
    public function changeFormat($date,$currentFormat,$newformat)
    {
        $converted_date = $this->validateDate($date,$currentFormat);
        if(!empty($newformat) && !empty($converted_date['result'])){
            return ['code' => '1000','message' => __('messages.1019'),'result' => date($newformat,$this->dateTimeTotimestamp($converted_date['result']))];
        }else{
            return ['code' => '5000','message' => __('messages.5021'),'result' => ''];
        }
    }
    
     /**
    * @DateOfCreation        06 Apr 2018
    * @ShortDescription      This function is responsible to change the timezone of 
                             thedatetime and given new datetime in response with new 
                             timezone.
    * @param                 Timestamp $datetime ( options - dd/mm/yy H:i:s,dd/mm/YY H:i:s,
                             dd-mm-yy H:i:s,dd-mm-YY H:i:s You can change the position of date month and year according to need) You can remove hours minute and secods also
                             
                             String $currentFormat ( options - dd/mm/yy H:i:s,dd/mm/YY H:i:s,
                             dd-mm-yy H:i:s,dd-mm-YY H:i:s You can change the position of date month and year according to need) You can remove hours minute and secods also
                             
                             String $oldtimezone ( PHP standard time zones)
                             String $newtimezone ( PHP standard time zones)  
    
    * @return                Array with status, message and result ( Datetime with new timezone )
    */
    public function convertTimeZone($datetime,$currentFormat,$oldtimezone,$newtimezone)
    {
        $converted_date = $this->validateDate($datetime,$currentFormat);
        if(!empty($converted_date['result']) && !empty($oldtimezone) && !empty($newtimezone)){
            $date = new \DateTime($datetime, new \DateTimeZone($oldtimezone));
            $date->setTimezone(new \DateTimeZone($newtimezone));
            return ['code' => '1000','message' => __('messages.1001'),'result' => $date->format($currentFormat)];
        }else{
            return ['code' => '5000','message' => __('messages.5023'),'result' => '' ];
        }
    }

     /**
    * @DateOfCreation        16 Apr 2018
    * @ShortDescription      This function is responsible to activity time  
    * @param                 TimeStamp $datetime ( date you need to check )
                             Boolean $full ( default false ) false for Approx time and 
                             true for exact time
                             String $currentFormat ( options - dd/mm/yy H:i:s,dd/mm/YY H:i:s,
                             dd-mm-yy H:i:s,dd-mm-YY H:i:s You can change the position of date month and year according to need) You can remove hours minute and secods also
    * @return                Array with status, message and result() 
    */
    function timeElapsed($datetime,$currentFormat, $full = false) {

        $converted_date = $this->validateDate($datetime,$currentFormat);
        if(!empty($converted_date['result'])){  
            $now = new \DateTime;
            $ago = new \DateTime($converted_date['result']);
            $diff = $now->diff($ago);

            $diff->w = floor($diff->d / 7);
            $diff->d -= $diff->w * 7;

            $string = array(
                'y' => 'year',
                'm' => 'month',
                'w' => 'week',
                'd' => 'day',
                'h' => 'hour',
                'i' => 'minute',
                's' => 'second',
            );
            foreach ($string as $k => &$v) {
                if ($diff->$k) {
                    $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
                } else {
                    unset($string[$k]);
                }
            }

            if (!$full) $string = array_slice($string, 0, 1);
            $result = $string ? implode(', ', $string) . ' ago' : 'just now';
            return ['code' => '1000','message' => __('messages.1020'),'result' => $result];
       }else{
            return ['code' => '5000','message' => __('messages.5021'),'result' => ''];
       } 
    } 

     /**
    * @DateOfCreation        16 Apr 2018
    * @ShortDescription      This function is responsible to get different between two dates.
    * @param                 Timestamp $startDatetime
                             String $format    
                             Timestamp $endDatetime
    * @return                Array with status, message and result( timestamp )
    */
    public function dateDifference($startDatetime,$startDateFormat,$endDatetime,$endDateFormat)
     {
        $toDate = $this->validateDate($startDatetime,$startDateFormat);
        $fromDate = $this->validateDate($endDatetime,$endDateFormat);

        if(empty($toDate['result'])){
            return ['code' => '5000','message' => __('messages.5022'),'result' => ''];
        }
        if(empty($fromDate['result'])){
            return ['code' => '5000','message' => __('messages.5026'),'result' => ''];
        }
        $timeFirst  = $this->dateTimeTotimestamp($toDate['result'],$startDateFormat);
        $timeSecond = $this->dateTimeTotimestamp($fromDate['result'],$endDateFormat);
        $differenceInSeconds = $timeFirst['result'] - $timeSecond['result'];
        return ['code' => '1000','message' => __('messages.1021'),'result' => $differenceInSeconds];
     } 

    /**
    * @DateOfCreation        16 Apr 2018
    * @ShortDescription      This function is responsible to add days in the date  
    * @param                 TimeStamp $datetime ( date you need to check )
                             Boolean $full ( default false ) false for Approx time and 
                             true for exact time
                             String $currentFormat ( options - dd/mm/yy H:i:s,dd/mm/YY H:i:s,
                             dd-mm-yy H:i:s,dd-mm-YY H:i:s You can change the position of date month and year according to need) You can remove hours minute and secods also
    * @return                Array with status, message and result() 
    */
     public function addDaysTodate($date,$currentFormat)
     {
        $converted_date = $this->validateDate($date,$currentFormat);
        if(!empty($converted_date['result'])){
            $dateTimeTotimestamp = $this->dateTimeTotimestamp($converted_date['result'],$currentFormat);
            $newDate = date('Y-m-d', strtotime('+'.$days.' days', $dateTimeTotimestamp['result']));
            return ['code' => '1000', 'message' => __('message.1022'), 'result' => $newDate];
        }else{
            return ['code' => '5000', 'message' => __('message.5021'), 'result' => ''];
        }
     }
    
    /**
    * @DateOfCreation        16 Apr 2018
    * @ShortDescription      This function is responsible to substract days from date  
    * @param                 TimeStamp $datetime ( date you need to check )
                             Boolean $full ( default false ) false for Approx time and 
                             true for exact time
                             String $currentFormat ( options - dd/mm/yy H:i:s,dd/mm/YY H:i:s,
                             dd-mm-yy H:i:s,dd-mm-YY H:i:s You can change the position of date month and year according to need) You can remove hours minute and secods also
    * @return                Array with status, message and result() 
    */
    public function substractDaysFromdate($date,$currentFormat)
    {
        $converted_date = $this->validateDate($date,$currentFormat);
        if(!empty($converted_date['result'])){
            $dateTimeTotimestamp = $this->dateTimeTotimestamp($converted_date['result'],$currentFormat);
            $new_ago = date('Y-m-d', strtotime('-'.$days.' days', $dateTimeTotimestamp['result']));
            return ['code' => '1000', 'message' => __('message.1023'), 'result' => $newDate];
        }else{
            return ['code' => '5000', 'message' => __('message.5021'), 'result' => ''];
        }  
    }

    /**
    * @DateOfCreation        16 Apr 2018
    * @ShortDescription      This function is responsible to get the Date or Month or year
                             in numeric  
    * @param                 TimeStamp $datetime ( date you need to check )
   
                             String $currentFormat ( options - dd/mm/yy H:i:s,dd/mm/YY H:i:s,
                             dd-mm-yy H:i:s,dd-mm-YY H:i:s You can change the position of date month and year according to need) You can remove hours minute and secods also

                             String $type  (d = date,m = month , Y = Year)
    * @return                Array with status, message and result() 
    */
    public function getYearMonthDate($datetime,$currentFormat,$type = 'd')
    {
        $converted_date = $this->validateDate($datetime,$currentFormat);
        if(!empty($converted_date['result'])){
            $timestamp = $this->dateTimeTotimestamp($datetime,$currentFormat);
            return ['code' => '1000','message' => __('message.1001'),'result' => date($type,$timestamp['result'])];
        }else{
            return ['code' => '5000','message' => __('message.5021'),'result' => ''];
        }
    }
    
    
    /**
    * @DateOfCreation        24 May 2018
    * @ShortDescription      This function is responsible to get pgsql timestamp after x min
    * @param                 $minutes_to_add, stores minute 
    * @return                return postgres timestamp
    */
    public function getPostgresTimestampAfterXmin($minutes_to_add = 0){
        $format =  'Y-m-d H:i:s';
        $time = new \DateTime();
        $time->add(new \DateInterval('PT' . $minutes_to_add . 'M'));

        $stamp = $time->format($format);
        
        //get timezone from setting
        $oldtimezone = Config::get('app.timezone');
        $newtimezone = Config::get('app.database_timezone');
        
        //convert time into database timezone
        $newTimeResp = $this->convertTimeZone($stamp,$format,$oldtimezone,$newtimezone);
        
        if($newTimeResp['code'] == Config::get('restresponsecode.SUCCESS')){
            return $newTimeResp['result'];
        }else{
            Log::error('Error in function DateTimeLib.getPostgresTimestampAfterXmin: '+$newTimeResp['result']);
            return false;
        }        
    }
    
    /**
    * @DateOfCreation        25 May 2018
    * @ShortDescription      This function is responsible to check datetime passed
    * @param                 $dateTime, Pass this parameter always in 'Y-m-d H:i:s' 
    * @return                return true/false
    */
    public function isTimePassed($dateTime){
        $format =  'Y-m-d H:i:s';
        //get timezone from setting
        $oldtimezone = Config::get('app.timezone');
        $newtimezone = Config::get('app.database_timezone');
        
         //convert time into database timezone
        $dateTimeInDBTZRes    = $this->convertTimeZone($dateTime,$format,$oldtimezone,$newtimezone);    
        if($dateTimeInDBTZRes['code'] == Config::get('restresponsecode.SUCCESS')){
            $dateTimeInDBTZ = $dateTimeInDBTZRes['result'];
        }else{
            Log::error('Error in function DateTimeLib.isTimePassed: '+$dateTimeInDBTZRes['result']);
            return true;
        }        
        $currentTimeInDBTZ = $this->getPostgresTimestampAfterXmin(); 

        // Compare date time
        if(strtotime($dateTimeInDBTZ) >= strtotime($currentTimeInDBTZ)){
            return false;
        }else{
            return true;
        }
    }

    /**
    * @DateOfCreation        16 Apr 2018
    * @ShortDescription      This function is responsible to activity time  
    * @param                 TimeStamp $datetime ( date you need to check )
                             Boolean $full ( default false ) false for Approx time and 
                             true for exact time
                             String $currentFormat ( options - dd/mm/yy H:i:s,dd/mm/YY H:i:s,
                             dd-mm-yy H:i:s,dd-mm-YY H:i:s You can change the position of date month and year according to need) You can remove hours minute and secods also
    * @return                Array with status, message and result() 
    */
    function ageCalculation($datetime,$currentFormat,$onlySpeficType = false) {

        if(Carbon::createFromFormat($currentFormat, $datetime) !== false){
            $date = Carbon::createFromFormat($currentFormat, $datetime)->format(Config::get('constants.DB_SAVE_DATE_FORMAT'));
            $dt = Carbon::parse($date);
            $age= Carbon::createFromDate($dt->year,$dt->month,$dt->day)->diff(Carbon::now());
           
            $ageString = !$onlySpeficType ? (!empty($age->y) ? $age->y.' Years' : (!empty($age->m) ? $age->m.' Months': $age->d.' Days')): (isset($age->$onlySpeficType) ? $age->$onlySpeficType : $age->y);
             return ['code' => '1000','message' => __('messages.1020'),'result' => $ageString];
        }else{
            return ['code' => '5000','message' => __('messages.5021'),'result' => ''];
       } 
    } 

    /**
    * @DateOfCreation        16 Apr 2018
    * @ShortDescription      This function is responsible to user date format convert to database store format date
    * @param                 date $date ( date you need to check )
                             $currentFormat like dd/mm/YYYY
                             $convertedFormat like Y-m-d
    * @return                Array with status, message and result() 
    */
    function covertUserDateToServerType($date,$currentFormat='dd/mm/YYYY',$convertedFormat='Y-m-d') {

        if(!empty($date)){
            $exportDate = array();
            if(strpos($currentFormat,'d') === 0 && 
                (
                    strpos($currentFormat,'-')!== false ||
                    strpos($currentFormat,'/')!== false || 
                    strpos($currentFormat,'.')!== false  
                )
            ){
                if (strpos($date,'/') !== false) {
                    $exportDate = explode('/', $date);
                } elseif (strpos($date,'-') !== false) {
                    $exportDate = explode('-', $date);
                } elseif (strpos($date,'.') !== false) {
                    $exportDate = explode('.', $date);
                }
                $newDates = $exportDate[1].'/'.$exportDate[0].'/'.$exportDate[2]; 
            }else{
               $newDates = str_replace(['/','-','.'], '/', $date);
            }
            $dateConverted = date($convertedFormat,strtotime($newDates));

            return ['code' => '1000','message' => __('messages.1019'),'result' => $dateConverted];
        }else{
            return ['code' => '5000','message' => __('messages.5021'),'result' => ''];
        }

    }

     /**
    * @DateOfCreation        06 Apr 2018
    * @ShortDescription      This function is responsible to convert date format.
    * @param                 Timestamp $date (Current date)
                             String $currentFormat ( options - dd/mm/yy H:i:s,dd/mm/YY H:i:s,
                             dd-mm-yy H:i:s,dd-mm-YY H:i:s You can change the position of date month and year according to need) You can remove hours minute and secods also
                             
                             String  $newformat ( options - dd/mm/yy H:i:s,dd/mm/YY H:i:s,
                             dd-mm-yy H:i:s,dd-mm-YY H:i:s You can change the position of date month and year according to need) You can remove hours minute and secods also
    * @return                Array with status, message and result ( Date in the 
                             format provided in the parameter )
    */
    function changeSpecificFormat($datetime,$currentFormat,$newformat) {

        if(Carbon::createFromFormat($currentFormat, $datetime) !== false){
            $date = Carbon::createFromFormat($currentFormat, $datetime)->format($newformat);
             return ['code' => '1000','message' => __('messages.1020'),'result' => $date ];
        }else{
            return ['code' => '5000','message' => __('messages.5021'),'result' => ''];
       } 
    } 

     /**
    * @DateOfCreation        06 Apr 2018
    * @ShortDescription      This function is responsible get return number of days between two dates.
    * @param                 $startDate
    * @param                 $endDate
    * @param                 $dateFormat
    * @return                Array with status, message and result (days in return)
    */
    function getDaysBetweenTwodates($startDate, $endDate,$dateFormat='Y-m-d'){
        if(Carbon::createFromFormat($dateFormat, $startDate) !== false && Carbon::createFromFormat($dateFormat, $endDate) !== false){
            $start = Carbon::createFromFormat($dateFormat, $startDate);
            $end = Carbon::createFromFormat($dateFormat, $endDate);
            $diffInDays = $start->diffInDays($end);
            return ['code' => '1000','message' => __('messages.1030'),'result' => $diffInDays ];
        }else{
            return ['code' => '5000','message' => __('messages.5021'),'result' => ''];
        }
    }

    /**
    * @DateOfCreation        06 Apr 2018
    * @ShortDescription      This function is responsible get return week days between two dates.
    * @param                 $startDate
    * @param                 $endDate
    * @param                 $dateFormat
    * @return                Array with status, message and result (days in return in array format)
    */
    function getWeekDayBetweenTwodates($startDate, $endDate,$dateFormat='Y-m-d'){
        if(Carbon::createFromFormat($dateFormat, $startDate) !== false && Carbon::createFromFormat($dateFormat, $endDate) !== false){
            $weekArray  = ['1'=>'Mon','2'=>'Tue','3'=>'Wed','4'=>'Thu','5'=>'Fri','6'=>'Sat','7'=>'Sun'];
            $start      = Carbon::createFromFormat($dateFormat, $startDate);
            $end        = Carbon::createFromFormat($dateFormat, $endDate);
            $diffInDays = $start->diffInDays($end); 
            $startDay   = $start->format('N');
            $endDay     = $end->format('N');
            $res =[];
            if($diffInDays>=7){
                $res = $weekArray;
            }elseif($diffInDays<=0){
                $res = array_slice($weekArray, $startDay-1, 1, true);
            }elseif($diffInDays>0 && $startDay< $endDay){
                $res = array_slice($weekArray, $startDay-1, $diffInDays+1 , true);
            }elseif($diffInDays>0 && $startDay> $endDay){
                $end =array_slice($weekArray,$startDay-1 , $diffInDays, true);
                $start= array_slice($weekArray, 0,$endDay, true);
                $res = $end+$start;
            }
            return ['code' => '1000','message' => __('messages.1031'),'result' => $res ];
        }else{
            return ['code' => '5000','message' => __('messages.5021'),'result' => ''];
        }
    }   
}