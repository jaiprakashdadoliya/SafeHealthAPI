<?php
namespace App\Modules\Visits\Models;
use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\HasApiTokens;
use App\Traits\Encryptable;
use Config;
use DB;
use App\Libraries\SecurityLib;
use App\Libraries\UtilityLib;
use App\Libraries\DateTimeLib;

/**
 * Investigation
 *
 * @package                ILD India Registry
 * @subpackage             Investigation
 * @category               Model
 * @DateOfCreation         11 june 2018
 * @ShortDescription       This Model to handle database operation of Investigation
 **/

class Spirometries extends Model {

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
    protected $table                        = 'spirometries';
    protected $tableSpirometryFectors       = 'spirometry_fectors';
    protected $tablePatientVisit            = 'patients_visits';
    protected $tableDoctorPatientRelation   = 'doctor_patient_relation';
    
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
                        'spirometry_date',
                        'resource_type',
                        'ip_address'
                        ];

    /**
     *@ShortDescription Override the primary key.
     *
     * @var string
     */
    protected $primaryKey = 'spirometry_id';

    /**
     * @DateOfCreation        12 July 2018
     * @ShortDescription      This function is responsible to get the patient Investigation record
     * @param                 integer $visitId, $patientId, $encrypt   
     * @return                object Array of Investigation records
     */
    public function getPatientSpirometriesInfo($visitId, $patientId = '', $encrypt = true) 
    {
        $selectData = ['spirometry_id', 'pat_id', 'visit_id', 'spirometry_date'];
        $whereData  = ['visit_id'=> $visitId, 'is_deleted'=>  Config::get('constants.IS_DELETED_NO')];
        
        if(!empty($patientId)){
            $whereData ['pat_id'] = $patientId;
        }
        $queryResult = $this->dbBatchSelect($this->table, $selectData, $whereData);
            if($encrypt && !empty($queryResult)){
                $queryResult = $queryResult->map(function($dataList){ 
                $dataList->spirometry_id    = $this->securityLibObj->encrypt($dataList->spirometry_id);
                $dataList->pat_id           = $this->securityLibObj->encrypt($dataList->pat_id);
                $dataList->visit_id         = $this->securityLibObj->encrypt($dataList->visit_id);
                $dataList->spirometry_date  = !empty($dataList->spirometry_date) ? date('d/m/Y', strtotime($dataList->spirometry_date)) : NULL;
                return $dataList;
            });
        }
        return $queryResult;        
    }

    /**
     * @DateOfCreation        12 July 2018
     * @ShortDescription      This function is responsible to get the patient Investigation record
     * @param                 integer $visitId, $patientId, $encrypt   
     * @return                object Array of Investigation records
     */
    public function getSpirometryTableFectorsData($spirometryId) 
    {
        $this->fillable = [];
        $spirometryId = $this->securityLibObj->decrypt($spirometryId);
        $selectData = ['sf_id', 'spirometry_id', 'fector_id', 'fector_value', 'fector_pre_value', 'fector_post_value'];
        $whereData  = ['spirometry_id'=> $spirometryId, 'is_deleted'=>  Config::get('constants.IS_DELETED_NO')];
        
        $queryResult = $this->dbBatchSelect($this->tableSpirometryFectors, $selectData, $whereData);
            if(!empty($queryResult)){
                $queryResult = $queryResult->map(function($dataList){ 
                $dataList->sf_id         = $this->securityLibObj->encrypt($dataList->sf_id);
                $dataList->spirometry_id = $this->securityLibObj->encrypt($dataList->spirometry_id);
                $dataList->fector_id     = $this->securityLibObj->encrypt($dataList->fector_id);
                return $dataList;
            });
        }
        return $queryResult;        
    }

    /**
    * @DateOfCreation        12 July 2018
    * @ShortDescription      This function is responsible to update Patient Investigation Record
    * @param                 Array  $requestData   
    * @return                Array of status and message
    */
    public function updatePatientInvestigationInfo($requestData, $whereData)
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
    * @ShortDescription      This function is responsible to multiple add Patient Investigation Record
    * @param                 Array  $insertData   
    * @return                Array of status and message
    */
    public function addPatientInvestigationInfo($insertData)
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
     * @DateOfCreation        16 July 2018
     * @ShortDescription      This function is responsible to get the spirometry factor record
     * @param                 integer $visitId 
     * @return                string of factor value
     */
    public function getSpirometryFectorByVistIdAndFectorId($visitId, $fectorIds) 
    {   
        $onConditionLeftSide = $this->table.'.spirometry_id';
        $onConditionRightSide = $this->tableSpirometryFectors.'.spirometry_id';
        $queryResult = DB::table($this->table)
           ->leftJoin($this->tableSpirometryFectors,function($join) use ($onConditionLeftSide,$onConditionRightSide){
                                $join->on($onConditionLeftSide, '=', $onConditionRightSide)
                                ->where($this->tableSpirometryFectors.'.is_deleted', '=', Config::get('constants.IS_DELETED_NO'), 'and');
                            })
            ->select($this->table.'.visit_id', $this->tableSpirometryFectors.'.fector_id', $this->tableSpirometryFectors.'.fector_pre_value', $this->tableSpirometryFectors.'.fector_post_value')
            ->where($this->table.'.visit_id', $visitId)
            ->whereIn($this->tableSpirometryFectors.'.fector_id', $fectorIds)
            ->where($this->table.'.is_deleted', Config::get('constants.IS_DELETED_NO'));
        if(!empty($patientId)){
            $queryResult = $queryResult->where($this->table.'.pat_id', $patientId);
        }
        $queryResult =$queryResult->get();
        return $queryResult;
    }

    /**
     * @DateOfCreation        3 Oct 2018
     * @ShortDescription      This function is responsible to get to get chart data for different spirometry factor type vitals
     * @param                 integer $visitId 
     * @return                string of factor value
     */
    public function getPatientSpirometryByFactorIdPatientIdAndDoctorId($patId,$doctorId,$extra=[]) 
    {   
        $dateType   = Config::get('constants.USER_VIEW_DATE_FORMAT_CARBON');
        $dbDateType = Config::get('constants.DB_SAVE_DATE_FORMAT');
        $fectorId   = $extra['fector_id'];
        $queryResult = DB::table( $this->tableSpirometryFectors )
                        ->select( DB::raw("DISTINCT ON (".$this->tableSpirometryFectors.".created_at::date) ".$this->tableSpirometryFectors.".created_at, DATE(".$this->tableSpirometryFectors.".created_at) as date, fector_pre_value as datavalue, fector_post_value as data_post_value, ".$this->table.".visit_id" )
                            ) 
                        ->join($this->table,function($join) {
                                $join->on($this->table.'.spirometry_id', '=', $this->tableSpirometryFectors.'.spirometry_id')
                                ->where($this->table.'.is_deleted', '=', Config::get('constants.IS_DELETED_NO'), 'and');
                            })
                        ->join($this->tablePatientVisit,function($join) {
                                $join->on($this->tablePatientVisit.'.visit_id', '=', $this->table.'.visit_id')
                                ->where($this->tablePatientVisit.'.is_deleted', '=', Config::get('constants.IS_DELETED_NO'), 'and');
                            })
                        ->join($this->tableDoctorPatientRelation,function($join) use($patId) {
                                $join->on($this->tableDoctorPatientRelation.'.user_id', '=', $this->tablePatientVisit.'.user_id')
                                ->where($this->tableDoctorPatientRelation.'.pat_id', '=', $patId, 'and')
                                ->where($this->tableDoctorPatientRelation.'.is_deleted', '=', Config::get('constants.IS_DELETED_NO'), 'and');
                            })
                        ->where( $this->tableSpirometryFectors.'.is_deleted',  Config::get('constants.IS_DELETED_NO') )
                        ->where( $this->tableSpirometryFectors.'.fector_id',$fectorId)
                        ->where( $this->table.'.pat_id',$patId)
                        ->where( $this->tableSpirometryFectors.'.fector_pre_value','>=',0)
                        ->orderBy(DB::raw($this->tableSpirometryFectors.".created_at::date"),'DESC')
                        ->limit(5)
                        ->orderBy($this->tableSpirometryFectors.'.created_at', 'DESC');

        $queryResult = $queryResult->get()->take(5)
                                    ->map(function($dataList) use ($dateType,$dbDateType){ 
                                            $dateResponse   = $this->dateTimeLibObj->changeSpecificFormat($dataList->date,$dbDateType,$dateType);
                                            $dataList->date = $dateResponse['code'] ==  Config::get('restresponsecode.SUCCESS') ? $dateResponse['result'] :'';
                                            return $dataList;
                                        });   
        return $queryResult;
    }
}
