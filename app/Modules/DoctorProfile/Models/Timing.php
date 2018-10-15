<?php
namespace App\Modules\DoctorProfile\Models;
use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\HasApiTokens;
use App\Traits\Encryptable;
use Illuminate\Support\Facades\DB;
use App\Libraries\SecurityLib;
use Config;
use Carbon\Carbon;
use App\Modules\Clinics\Models\Clinics;

/**
 * Timing
 *
 * @package                 ILD India Registry
 * @subpackage              Timing
 * @category                Model
 * @DateOfCreation          21 may 2018
 * @ShortDescription        This Model to handle database operation with current table
                            doctors timing
 **/
class Timing extends Model {

    use HasApiTokens,Encryptable;

    /**
     * The attributes to declare primary key for the table.
     *
     * @var string
     */
    protected $primaryKey = 'timing_id';

    /**
     * The attributes to declare table name to store data.
     *
     * @var string
     */
    protected $table = 'timing';

    // @var Array $encryptedFields
    // This protected member contains fields that need to encrypt while saving in database
    protected $encryptable = [];
    
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct() {
        // Init security library object
        $this->securityLibObj = new SecurityLib();
    }

    /**
     * Create Doctor Timing List with regarding details
     *
     * @param array $data timing data
     * @return int doctor time id if inserted otherwise false
     */
    public function getTimingList($user_id = '') { 

        for ($i=1; $i <=7 ; $i++) { 
            $doctorTiming[$i]  =  DB::table($this->table)
                            ->leftjoin('clinics', 'timing.clinic_id', '=', 'clinics.clinic_id')
                            ->select('timing.timing_id', 'timing.week_day', 'timing.start_time', 'timing.end_time', 'timing.slot_duration', 'timing.patients_per_slot', 'timing.clinic_id','clinics.clinic_name')
                            ->where([
                                ['timing.user_id', '=', $user_id],
                                ['timing.is_deleted', '=', Config::get('constants.IS_DELETED_NO')],
                                ['timing.week_day', '=', $i],
                            ])
                            ->get()
                            ->map(function($timing){
                                $timing->timing_id = $this->securityLibObj->encrypt($timing->timing_id);
                                $timing->clinic_id = $this->securityLibObj->encrypt($timing->clinic_id);
                                return $timing;
                            });
        }
        return $doctorTiming;
    }

    /**
    * @DateOfCreation        22 May 2018
    * @ShortDescription      This function is responsible to get the Timing by id
    * @param                 String $timing_id   
    * @return                Array of time
    */
    public function getTimingById($timing_id)
    {   
        $queryResult = DB::table($this->table)
                        ->leftjoin('clinics', 'timing.clinic_id', '=', 'clinics.clinic_id')
                        ->select('timing.timing_id', 'timing.week_day', 'timing.start_time', 'timing.end_time', 'timing.slot_duration', 'timing.patients_per_slot', 'timing.clinic_id', 'clinics.clinic_name')
                        ->where([
                            'timing_id' =>  $timing_id,
                        ])
                        ->get()->first();

        return $queryResult;
    }

    /**
    * @DateOfCreation        22 May 2018
    * @ShortDescription      This function is responsible to get the Timing by id
    * @param                 String $timing_id   
    * @return                Array of time
    */
    public function getWeekTiming($user_id, $week_day, $encrypted_timing_id='')
    {
        $timing_id = 0;
        $whereData = array(  
                        'user_id' =>  $user_id,
                        'week_day' => $week_day
                    );
        if(!empty($encrypted_timing_id)){
            $timing_id = $this->securityLibObj->decrypt($encrypted_timing_id);
        }
        $queryResult = DB::table($this->table)
                        ->select('timing.start_time', 'timing.end_time')
                        ->where($whereData)
                        ->where('timing_id', '!=', $timing_id)
                        ->get()->toArray();
        return $queryResult;
    }

    /**
     * Create or Edit doctor timing with regarding details
     *
     * @param array $data membership data
     * @return Array doctor member if inserted otherwise false
     */
    public function updateTiming($requestData=array()) {

        $requestData['clinic_id'] = $this->securityLibObj->decrypt($requestData['clinic_id']);
        $timing_id = $this->securityLibObj->decrypt($requestData['timing_id']);
        unset($requestData['timing_id']);
        $whereData =  array('timing_id' => $timing_id);
        $queryResult =  $this->dbUpdate($this->table, $requestData, $whereData);

        if($queryResult){
            $timingUpdateData = $this->getTimingById($timing_id);
            $timingUpdateData->timing_id = $this->securityLibObj->encrypt($timing_id);
            $timingUpdateData->clinic_id = $this->securityLibObj->encrypt($requestData['clinic_id']);
            return $timingUpdateData;
        }
        return false;
    }

    /**
     * Create doctor membership with regarding details
     *
     * @param array $data membership data
     * @return Array doctor member if inserted otherwise false
     */

    public function createTiming($requestData=array())
    {
        if($requestData['week_day'] == '0'){
            $weekRequestData = [];
            $resultTimingData = [];
            for($day=1; $day<=7; $day++){
                $weekRequestData = $requestData;
                $weekRequestData['week_day'] = $day;
                $queryResult = $this->dbInsert($this->table, $weekRequestData);
                if($queryResult){
                    $timingData = $this->getTimingById(DB::getPdo()->lastInsertId());

                    // Encrypt the ID
                    $timingData->timing_id = $this->securityLibObj->encrypt(DB::getPdo()->lastInsertId());
                    $timingData->clinic_id = $this->securityLibObj->encrypt($requestData['clinic_id']);
                    $resultTimingData[] = $timingData;
                }
            }
            return $resultTimingData;
        }else{
            $queryResult = $this->dbInsert($this->table, $requestData);
            if($queryResult){
                $timingData = $this->getTimingById(DB::getPdo()->lastInsertId());

                // Encrypt the ID
                $timingData->timing_id = $this->securityLibObj->encrypt(DB::getPdo()->lastInsertId());
                $timingData->clinic_id = $this->securityLibObj->encrypt($requestData['clinic_id']);
                return $timingData;
            }
        }

        return false;
    }

    /**
    * @DateOfCreation        19 July 2018
    * @ShortDescription      This function is responsible for creating timing in DB
    * @param                 Array $data This contains full user input data 
    * @return                True/False
    */
    public function createTimingDemo($data, $userId)
    {
        // @var Boolean $response
        // This variable contains insert query response
        $response = false;
        // Prepair insert query
        $response = DB::table($this->table)->insert(
                        $data
                    );
        return $response;
    }

    /**
     * Create Doctor Timing List with regarding details
     *
     * @param array $data timing data
     * @return int doctor time id if inserted otherwise false
     */
    public function getAllTimingListByUserId($user_id,$encrypt=true) { 

            $doctorTiming =  DB::table($this->table)
                                ->select('timing.timing_id', 'timing.week_day', 'timing.start_time', 'timing.end_time', 'timing.slot_duration', 'timing.patients_per_slot', 'timing.clinic_id')
                            ->where([
                                ['timing.user_id', '=', $user_id],
                                ['timing.is_deleted', '=', Config::get('constants.IS_DELETED_NO')]
                            ])
                            ->get();
                            if($encrypt){
                                $doctorTiming = $doctorTiming->map(function($timing){
                                    $timing->timing_id = $this->securityLibObj->encrypt($timing->timing_id);
                                    return $timing;
                                });
                            }
        return $doctorTiming;
    }


}
