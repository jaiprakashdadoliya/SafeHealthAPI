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

class PhysicalExaminations extends Model {

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
    protected $table         = 'physical_examinations';
    protected $tableVisits   = 'patients_visits';
    protected $tableDoctorPatientRelation = 'doctor_patient_relation';
    
    // This protected member contains fields that need to encrypt while saving in database
    protected $encryptable = [];
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [ 'pat_id', 
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
    protected $primaryKey = 'pe_id';

    /**
     * @DateOfCreation        26 June 2018
     * @ShortDescription      This function is responsible to get the patient Physical Examinations record
     * @param                 integer $visitId,$patientId, $encrypt   
     * @return                object Array of Physical Examinations records
     */
    public function getPhysicalExaminationsByVistID($visitId,$patientId = '',$encrypt = true) 
    {
        $selectData = ['pe_id','pat_id','visit_id','fector_id','fector_value','resource_type','ip_address'];
        $whereData  = ['visit_id'=> $visitId,'is_deleted'=>  Config::get('constants.IS_DELETED_NO')];
        if(!empty($patientId)){
            $whereData ['pat_id'] = $patientId;
        }
        $queryResult = $this->dbBatchSelect($this->table, $selectData, $whereData);
            if($encrypt && !empty($queryResult)){
                $queryResult = $queryResult->map(function($dataList){ 
                $dataList->pe_id = $this->securityLibObj->encrypt($dataList->pe_id);
                $dataList->pat_id = $this->securityLibObj->encrypt($dataList->pat_id);
                $dataList->visit_id = $this->securityLibObj->encrypt($dataList->visit_id);
                $dataList->fector_id = $this->securityLibObj->encrypt($dataList->fector_id);
                return $dataList;
            });
        }
        return $queryResult;        
    }

    /**
    * @DateOfCreation        27 June 2018
    * @ShortDescription      This function is responsible to update family Physical Examinations Record
    * @param                 Array  $requestData   
    * @return                Array of status and message
    */
    public function updatePhysicalExaminationsByVistID($requestData,$whereData)
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
    * @DateOfCreation        27 June 2018
    * @ShortDescription      This function is responsible to multiple add family Physical Examinations Record
    * @param                 Array  $insertData   
    * @return                Array of status and message
    */
    public function addPhysicalExaminationsByVistID($insertData)
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
    public function getPatientPhysicalExaminationsByFactorIdPatientIdAndDoctorId($patId,$doctorId,$extra=[]) 
    {  
        $dateType = Config::get('constants.USER_VIEW_DATE_FORMAT_CARBON');
        $dbDateType = Config::get('constants.DB_SAVE_DATE_FORMAT');
        $fectorId = $extra['fector_id'];
        $queryResult = DB::table( $this->table )
                        ->select( DB::raw("DISTINCT ON (".$this->table.".created_at::date) ".$this->table.".created_at, DATE(".$this->table.".created_at) as date, fector_value as datavalue" )
                            ) 
                        ->join($this->tableVisits,function($join) {
                                $join->on($this->tableVisits.'.visit_id', '=', $this->table.'.visit_id')
                                ->where($this->tableVisits.'.is_deleted', '=', Config::get('constants.IS_DELETED_NO'), 'and');
                            })
                        ->join($this->tableDoctorPatientRelation,function($join) use($patId) {
                                $join->on($this->tableDoctorPatientRelation.'.user_id', '=', $this->tableVisits.'.user_id')
                                ->where($this->tableDoctorPatientRelation.'.pat_id', '=', $patId, 'and')
                                ->where($this->tableDoctorPatientRelation.'.is_deleted', '=', Config::get('constants.IS_DELETED_NO'), 'and');
                            })
                        ->where( $this->table.'.is_deleted',  Config::get('constants.IS_DELETED_NO') )
                        ->where( $this->table.'.fector_id',$fectorId)
                        ->where( $this->table.'.pat_id',$patId)
                        ->where( $this->table.'.fector_value','>=',0)
                        ->orderBy(DB::raw($this->table.".created_at::date"),'DESC')
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
    public function getPatientPhysicalExaminationsByFactorIdPatientIdAndDoctorIdAndVisitIds($patId, $doctorId, $extra=[]) 
    {  
        $dateType   = Config::get('constants.USER_VIEW_DATE_FORMAT_CARBON');
        $dbDateType = Config::get('constants.DB_SAVE_DATE_FORMAT');
        $fectorId   = $extra['fector_id'];
        $visitIds   = $extra['visit_id'];
        $queryResult = DB::table( $this->table )
                        ->select( DB::raw("fector_value as datavalue, ".$this->table.".visit_id" )
                            ) 
                        ->join($this->tableVisits,function($join) {
                                $join->on($this->tableVisits.'.visit_id', '=', $this->table.'.visit_id')
                                ->where($this->tableVisits.'.is_deleted', '=', Config::get('constants.IS_DELETED_NO'), 'and');
                            })
                        ->where( $this->table.'.is_deleted',  Config::get('constants.IS_DELETED_NO') )
                        ->where( $this->table.'.fector_id',$fectorId)
                        ->where( $this->table.'.pat_id',$patId)
                        ->where( $this->table.'.fector_value','>=',0)
                        ->whereIn( $this->table.'.visit_id',$visitIds);
        
        $queryResult = $queryResult->get();
        return $queryResult;
    }

}
