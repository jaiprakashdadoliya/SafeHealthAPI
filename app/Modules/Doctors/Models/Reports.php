<?php

namespace App\Modules\Doctors\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Libraries\SecurityLib;
use App\Traits\Encryptable;
use App\Libraries\UtilityLib;
use Config;
use Carbon\Carbon;
use DateTime;
use stdClass;
/**
 * Doctors Class
 *
 * @package                Safe Health
 * @subpackage             Doctors
 * @category               Model
 * @DateOfCreation         10 May 2018
 * @ShortDescription       This is model which need to perform the options related to 
                           Doctors info

 */
class Reports extends Model {
    use Encryptable;
    // @var string $table
    // This protected member contains table name
    protected $table = 'doctors';
    protected $encryptable = [];

    // @var string $primaryKey
    // This protected member contains primary key
    protected $primaryKey = 'doc_id';  
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct() {
        // Init security library object
        $this->securityLibObj = new SecurityLib();
        // Init utility library object
        $this->utilityLibObj = new UtilityLib();

    }

    /**
    * @DateOfCreation        30 Aug 2018
    * @ShortDescription      This function is responsible for getting patients for a month
    * @param                 Array $data This contains full user input data 
    * @return                True/False
    */

    public function getPatientsReportForMonth($data, $userId)
    {
        $thisYear = $data['year'];
        $toYear = ($data['month'] != 12) ? $data['year'] : ($data['year']+1);
        $thisMonth = $data['month'];
        $nextMonth = ($data['month'] != 12) ? ($data['month']+1) : '01';
        $from = $thisYear.'-'.$thisMonth.'-01';
        $to = $toYear.'-'.$nextMonth.'-01';
        $dateObj   = DateTime::createFromFormat('!m', $thisMonth);
        $thisMonthName = $dateObj->format('M');
        // @var Boolean $response
        // This variable contains select response
        $result = DB::table('patients_visits')
                ->select(DB::raw("COUNT(visit_id) AS patients"),DB::raw("EXTRACT(DAY FROM created_at) AS name"))
                ->whereBetween('created_at', [$from, $to])
                ->where('user_id', $userId)
                ->groupBy('name')
                ->orderby('name', 'ASC')
                ->get();
        $data = array();
        foreach ($result as $key => $value) {
            $data[$result[$key]->name]['name'] = $result[$key]->name;
            $data[$result[$key]->name]['patients'] = (int)$result[$key]->patients;
        }
        $days = cal_days_in_month(CAL_GREGORIAN,$thisMonth,$thisYear);

        for( $i = 1; $i<=$days; $i++ ) {
            if(!array_key_exists($i, $data)) {
                $data[$i]['name'] = $i;
                $data[$i]['patients'] = 0;
            }
        }
        $output = array();
        asort($data);
        foreach ($data as $key => $value)
        {
        $object = new stdClass();
            $object->name = $value['name'];
            $object->patients = $value['patients'];
            $output[] = $object;
        }

        return $output;
    }

    /**
    * @DateOfCreation        30 Aug 2018
    * @ShortDescription      This function is responsible for getting patients for a year
    * @param                 Array $data This contains full user input data 
    * @return                True/False
    */
    public function getPatientsReportForYear($data, $userId)
    {
        $thisYear = $data['year'];
        $nextYear = ($thisYear+1);
        $from = $thisYear.'-01-01';
        $to = $nextYear.'-01-01';
        // @var Boolean $response
        // This variable contains select response
        $result = DB::table('patients_visits')
                ->select(DB::raw("COUNT(created_at) AS patients"),DB::raw("EXTRACT(MONTH FROM created_at) AS name"))
                ->whereBetween('created_at', [$from, $to])
                ->where('user_id', $userId)
                ->groupBy('name')
                ->orderby('name', 'ASC')
                ->get();
        $data = array();
        foreach ($result as $key => $value) {
            $data[$result[$key]->name]['name'] = $result[$key]->name;
            $data[$result[$key]->name]['patients'] = (int)$result[$key]->patients;
        }

        $totalMonths = 12;

        for( $i = 1; $i<=$totalMonths; $i++ ) {
            if(!array_key_exists($i, $data)) {
                $data[$i]['name'] = $i;
                $data[$i]['patients'] = 0;
            }
        }
        asort($data);
        $output = array();
        foreach ($data as $key => $value)
        {
            $object = new stdClass();
            $dateObj   = DateTime::createFromFormat('!m', $value['name']);
            $monthName = $dateObj->format('M'); 
            $object->name = $monthName;
            $object->patients = $value['patients'];
            $output[] = $object;
        }
        return $output;
    }

    /**
    * @DateOfCreation        30 Aug 2018
    * @ShortDescription      This function is responsible for getting patients for a month
    * @param                 Array $data This contains full user input data 
    * @return                True/False
    */

   public function getIncomeReportForMonth($data, $userId)
    {
        $thisYear = $data['year'];
        $toYear = ($data['month'] != 12) ? $data['year'] : ($data['year']+1);
        $thisMonth = $data['month'];
        $nextMonth = ($data['month'] != 12) ? ($data['month']+1) : '01';
        $from = $thisYear.'-'.$thisMonth.'-01';
        $to = $toYear.'-'.$nextMonth.'-01';
        $dateObj   = DateTime::createFromFormat('!m', $thisMonth);
        $thisMonthName = $dateObj->format('M');
        // @var Boolean $response
        // This variable contains select response
        $result = DB::table('payments_history')
                ->select(DB::raw("SUM(amount) AS income"),DB::raw("EXTRACT(DAY FROM created_at) AS name"))
                ->whereBetween('created_at', [$from, $to])
                ->where('user_id', $userId)
                ->groupBy('name')
                ->orderby('name', 'ASC')
                ->get();
        $data = array();
        foreach ($result as $key => $value) {
            $data[$result[$key]->name]['name'] = $result[$key]->name;
            $data[$result[$key]->name]['income'] = (int)$result[$key]->income;
        }
        $days = cal_days_in_month(CAL_GREGORIAN,$thisMonth,$thisYear);

        for( $i = 1; $i<=$days; $i++ ) {
            if(!array_key_exists($i, $data)) {
                $data[$i]['name'] = $i;
                $data[$i]['income'] = 0;
            }
        }
        $output = array();
        asort($data);
        foreach ($data as $key => $value)
        {
        $object = new stdClass();
            $object->name = $value['name'];
            $object->income = $value['income'];
            $output[] = $object;
        }

        return $output;
    }

    /**
    * @DateOfCreation        30 Aug 2018
    * @ShortDescription      This function is responsible for getting patients for a year
    * @param                 Array $data This contains full user input data 
    * @return                True/False
    */
   public function getIncomeReportForYear($data, $userId)
    {
        $thisYear = $data['year'];
        $nextYear = ($thisYear+1);
        $from = $thisYear.'-01-01';
        $to = $nextYear.'-01-01';
        // @var Boolean $response
        // This variable contains select response
        $result = DB::table('payments_history')
                ->select(DB::raw("SUM(amount) AS income"),DB::raw("EXTRACT(MONTH FROM created_at) AS name"))
                ->whereBetween('created_at', [$from, $to])
                ->where('user_id', $userId)
                ->groupBy('name')
                ->orderby('name', 'ASC')
                ->get();
        $data = array();
        foreach ($result as $key => $value) {
            $data[$result[$key]->name]['name'] = $result[$key]->name;
            $data[$result[$key]->name]['income'] = (int)$result[$key]->income;
        }

        $totalMonths = 12;

        for( $i = 1; $i<=$totalMonths; $i++ ) {
            if(!array_key_exists($i, $data)) {
                $data[$i]['name'] = $i;
                $data[$i]['income'] = 0;
            }
        }
        asort($data);
        $output = array();
        foreach ($data as $key => $value)
        {
            $object = new stdClass();
            $dateObj   = DateTime::createFromFormat('!m', $value['name']);
            $monthName = $dateObj->format('M'); 
            $object->name = $monthName;
            $object->income = $value['income'];
            $output[] = $object;
        }
        return $output;
    }

}
