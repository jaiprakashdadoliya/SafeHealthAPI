<?php
namespace App\Modules\Visits\Models;
use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\HasApiTokens;
use App\Traits\Encryptable;
use App\Libraries\SecurityLib;
use Config;
use App\Libraries\UtilityLib;
use DB;
use App\Libraries\DateTimeLib;

/**
 * PhysicalExaminations
 *
 * @package                ILD India Registry
 * @subpackage             PhysicalExaminations
 * @category               Model
 * @DateOfCreation         11 june 2018
 * @ShortDescription       This Model to handle database operation of Physical Examinations
 **/

class vitals extends Model {

    use HasApiTokens,Encryptable;

    /**
     * Create a new model instance.
     *
     * @return void
     */
    public function __construct()
    {
        // Init security library object
        $this->securityLibObj = new SecurityLib();

        // Init exception library object
        $this->utilityLibObj = new UtilityLib();

        // Init DateTime library object
        $this->dateTimeLibObj = new DateTimeLib();
    }

    /**
    *@ShortDescription Table for the Users.
    *
    * @var String
    */
    protected $table = 'vitals';
    protected $tablePatientVisit = 'patients_visits';
    protected $tableDoctorPatientRelation = 'doctor_patient_relation';
    
    // This protected member contains fields that need to encrypt while saving in database
    protected $encryptable = [];
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [ 
                        'pat_id', 
                        'visit_id',
                        'fector_id',
                        'fector_value',
                        'resource_type',
                        'ip_address'
                        ];

    /**
     *@ShortDescription Override the primary key.
     *
     * @var string
     */
    protected $primaryKey = 'vitals_id';

    /**
     * @DateOfCreation        12 July 2018
     * @ShortDescription      This function is responsible to get the patient Physical Vitals record
     * @param                 integer $visitId, $patientId, $encrypt   
     * @return                object Array of Physical Vitals records
     */
    public function getPatientVitalsInfo($visitId, $patientId = '', $encrypt = true) 
    {
        $selectData = ['vitals_id','pat_id','visit_id','fector_id','fector_value'];
        $whereData  = ['visit_id'=> $visitId, 'is_deleted'=>  Config::get('constants.IS_DELETED_NO')];
        
        if(!empty($patientId)){
            $whereData ['pat_id'] = $patientId;
        }
        $queryResult = $this->dbBatchSelect($this->table, $selectData, $whereData);
            if($encrypt && !empty($queryResult)){
                $queryResult = $queryResult->map(function($dataList){ 
                $dataList->vitals_id         = $this->securityLibObj->encrypt($dataList->vitals_id);
                $dataList->pat_id        = $this->securityLibObj->encrypt($dataList->pat_id);
                $dataList->visit_id      = $this->securityLibObj->encrypt($dataList->visit_id);
                $dataList->fector_id     = $this->securityLibObj->encrypt($dataList->fector_id);
                return $dataList;
            });
        }
        return $queryResult;        
    }

    /**
    * @DateOfCreation        12 July 2018
    * @ShortDescription      This function is responsible to update Patient Vitals Record
    * @param                 Array  $requestData   
    * @return                Array of status and message
    */
    public function updatePatientVitalsInfo($requestData, $whereData)
    {
        if(!empty($whereData)){
            $updateData = $this->utilityLibObj->fillterArrayKey($requestData, $this->fillable);
            $response = $this->dbUpdate($this->table, $updateData, $whereData);
            if($response){
                return true;
            }
        }
        return false;
    }

    /**
    * @DateOfCreation        12 July 2018
    * @ShortDescription      This function is responsible to multiple add Patient Vitals Record
    * @param                 Array  $insertData   
    * @return                Array of status and message
    */
    public function addPatientVitalsInfo($insertData)
    {
        if(!empty(array_filter($insertData))){
            $response = $this->dbBatchInsert($this->table, $insertData);
            if($response){
                return true;
            }
        }
        return false;
    }

    /**
     * @DateOfCreation        26 June 2018
     * @ShortDescription      This function is responsible to get chart data for difrrent fector type vitals
     * @param                 integer $visitId   
     * @return                object Array of medical history records
     */
    public function getPatientVitalsByFactorIdPatientIdAndDoctorId($patId,$doctorId,$extra=[]) 
    {   
        DB::enableQueryLog();

        $dateType   = Config::get('constants.USER_VIEW_DATE_FORMAT_CARBON');
        $dbDateType = Config::get('constants.DB_SAVE_DATE_FORMAT');
        $fectorId   = $extra['fector_id'];
        $queryResult = DB::table( $this->table )
                        ->select( DB::raw("DISTINCT ON (".$this->table.".created_at::date) ".$this->table.".created_at, DATE(".$this->table.".created_at) as date, fector_value as datavalue, ".$this->table.".visit_id" )
                            ) 
                        ->join($this->tablePatientVisit,function($join) {
                                $join->on($this->tablePatientVisit.'.visit_id', '=', $this->table.'.visit_id')
                                ->where($this->tablePatientVisit.'.is_deleted', '=', Config::get('constants.IS_DELETED_NO'), 'and');
                            })
                        ->join($this->tableDoctorPatientRelation,function($join) use($patId) {
                                $join->on($this->tableDoctorPatientRelation.'.user_id', '=', $this->tablePatientVisit.'.user_id')
                                ->where($this->tableDoctorPatientRelation.'.pat_id', '=', $patId, 'and')
                                ->where($this->tableDoctorPatientRelation.'.is_deleted', '=', Config::get('constants.IS_DELETED_NO'), 'and');
                            })
                        ->where( $this->table.'.is_deleted',  Config::get('constants.IS_DELETED_NO') )
                        ->where( $this->table.'.fector_id',$fectorId)
                        ->where( $this->table.'.pat_id',$patId)
                        ->where( $this->table.'.fector_value','>=',0)
                        ->orderBy(DB::raw($this->table.".created_at::date"),'DESC')
                        ->limit(5)
                        ->orderBy($this->table.'.created_at', 'DESC');

        $queryResult = $queryResult->get()->take(5)
                                    ->map(function($dataList) use ($dateType,$dbDateType){ 
                                            $dateResponse       = $this->dateTimeLibObj->changeSpecificFormat($dataList->date,$dbDateType,$dateType);
                                            $dataList->date = $dateResponse['code'] ==  Config::get('restresponsecode.SUCCESS') ? $dateResponse['result'] :'';
                                            return $dataList;
                                        });   
        return $queryResult;
    }

    /**
     * @DateOfCreation        26 June 2018
     * @ShortDescription      This function is responsible to get chart data for difrrent fector type vitals
     * @param                 integer $visitId   
     * @return                object Array of medical history records
     */
    public function getPatientVitalsByPatientIdAndDoctorId($patId, $doctorId, $extra=[]) 
    {  DB::enableQueryLog();

        if(isset($extra['is_second_last']) && $extra['is_second_last']){

            $res =  DB::select(
                        DB::raw( "
                            SELECT 
                                fector_id,
                                fector_value,
                                DATE(vitals.created_at) AS DateValue,
                                (Select CONCAT(visit_type,'_SECOND_LAST_VISIT_', vitals.fector_id) AS ts from patients_visits AS pv where pv.visit_id = vitals.visit_id) AS visit_type,
                                (Select DATE(created_at) AS tsDate from patients_visits AS pv where pv.visit_id = vitals.visit_id) AS visit_date
                            FROM 
                                vitals 
                            LEFT JOIN 
                                doctor_patient_relation AS dpr 
                                ON dpr.pat_id = :patId AND vitals.created_by = dpr.user_id
                            WHERE vitals.pat_id =:patId AND vitals.is_deleted =:isDeleted AND 
                                visit_id = (SELECT visit_id FROM patients_visits WHERE pat_id=:patId AND visit_type =:visitTypeFollow AND is_deleted =:isDeleted ORDER BY created_at DESC LIMIT 1 OFFSET 1)"
                        ), 
                        array(
                           'patId'              => $patId,
                           'visitTypeFollow'    => Config::get('constants.FOLLOW_VISIT_TYPE'),
                           'isDeleted'          => Config::get('constants.IS_DELETED_NO')
                        )
                    );
        } else{
            $res =  DB::select(
                        DB::raw( "
                            SELECT 
                                fector_id,
                                fector_value,
                                DATE(vitals.created_at) as DateValue,
                                (Select CONCAT(visit_type,'_',vitals.fector_id) as ts from patients_visits as pv where pv.visit_id = vitals.visit_id) as visit_type,
                                (Select DATE(created_at) as tsDate from patients_visits as pv where pv.visit_id = vitals.visit_id) as visit_date
                            from 
                                vitals 
                            Inner Join 
                                doctor_patient_relation as dpr 
                                ON dpr.pat_id = :patId and vitals.created_by = dpr.user_id
                            where vitals.pat_id =:patId and vitals.is_deleted =:isDeleted and 
                                (visit_id = (select visit_id from patients_visits where pat_id=:patId and visit_type =:visitTypeInitial and is_deleted =:isDeleted limit 1) 
                                or 
                                visit_id = (select visit_id from  patients_visits where pat_id=:patId  and visit_type =:visitTypeFollow and is_deleted =:isDeleted order by created_at desc limit 1))"
                        ), 
                        array(
                           'patId'              => $patId,
                           'visitTypeFollow'    => Config::get('constants.FOLLOW_VISIT_TYPE'),
                           'visitTypeInitial'   => Config::get('constants.INITIAL_VISIT_TYPE'),
                           'isDeleted'          => Config::get('constants.IS_DELETED_NO')
                        )
                    );
        }
// dd(DB::getQueryLog());
        return $res; 
    }

    /**
     * @DateOfCreation        26 June 2018
     * @ShortDescription      This function is responsible to get chart data for different factor type vitals
     * @param                 integer $visitId   
     * @return                object Array of medical history records
     */
    public function getPatientVisitByPatientIdAndDoctorId($patId, $doctorId, $type, $extra) 
    {  
        // OutPut query
        // select DATE(patients_visits.created_at::date) as created_at,visit_type,visit_id from "patients_visits" inner join "doctor_patient_relation" on "doctor_patient_relation"."pat_id" = "patients_visits"."pat_id" and "doctor_patient_relation"."user_id" = patients_visits.user_id where "patients_visits"."is_deleted" = 2 and "patients_visits"."pat_id" = 11 and "patients_visits"."visit_type" = 1 order by "patients_visits"."visit_id" asc limit 1
        
        $queryResult = DB::table( $this->tablePatientVisit )
                        ->select( DB::raw("DATE(".$this->tablePatientVisit.".created_at::date) as created_at,visit_type,visit_id")
                            )
                        ->join($this->tableDoctorPatientRelation,function($join) use($patId) {
                                $join->on($this->tableDoctorPatientRelation.'.user_id', '=', $this->tablePatientVisit.'.user_id')
                                ->where($this->tableDoctorPatientRelation.'.pat_id', '=', $patId, 'and')
                                ->where($this->tableDoctorPatientRelation.'.is_deleted', '=', Config::get('constants.IS_DELETED_NO'), 'and');
                            })
                        ->where( $this->tablePatientVisit.'.is_deleted',  Config::get('constants.IS_DELETED_NO') )
                        ->where( $this->tablePatientVisit.'.pat_id',$patId)
                        ->where( $this->tablePatientVisit.'.visit_type',$type);

        if($type == Config::get('constants.INITIAL_VISIT_TYPE')){
            $queryResult->orderBy( $this->tablePatientVisit.'.visit_id','asc');   
            $result = $queryResult->limit(1)->first();
        } else if(isset($extra['is_second_last']) && $extra['is_second_last']) {
            // $queryResult->where->(DB::raw('') );
            $queryResult->orderBy( $this->tablePatientVisit.'.visit_id','desc');               
            $result = $queryResult->offset(1)->limit(1)->first();
        }else{
            $queryResult->orderBy( $this->tablePatientVisit.'.visit_id','desc');               
            $result = $queryResult->limit(1)->first();
        }
       
        return $result;
    }
}
