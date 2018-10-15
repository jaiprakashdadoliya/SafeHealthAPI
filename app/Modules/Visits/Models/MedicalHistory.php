<?php
namespace App\Modules\Visits\Models;
use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\HasApiTokens;
use App\Traits\Encryptable;
use App\Libraries\SecurityLib;
use Config;
use App\Libraries\UtilityLib;
use DB;

/**
 * MedicalHistory
 *
 * @package                ILD India Registry
 * @subpackage             MedicalHistory
 * @category               Model
 * @DateOfCreation         11 june 2018
 * @ShortDescription       This Model to handle database operation of medical history
 **/

class MedicalHistory extends Model {

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
    }

    /**
    *@ShortDescription Table for the Users.
    *
    * @var String
    */
    protected $table         = 'patient_medical_histories';
    protected $tableDiseases = 'diseases';
    protected $tableDoctorPatientRelation = 'doctor_patient_relation';
    protected $tableVisits = 'patients_visits';
    
    // This protected member contains fields that need to encrypt while saving in database
    protected $encryptable = [];
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [ 'pat_id', 
                            'visit_id',
                            'pmh_disease_id',
                            'is_happend',
                            'resource_type',
                            'ip_address'
                        ];

    /**
     *@ShortDescription Override the primary key.
     *
     * @var string
     */
    protected $primaryKey = 'pmh_id';

    /**
     * @DateOfCreation        26 June 2018
     * @ShortDescription      This function is responsible to get the patient Medical History record
     * @param                 integer $visitId   
     * @return                object Array of medical history records
     */
    public function getPatientMedicalHistory($visitId) 
    {   $type = Config::get('constants.IS_SHOW_IN_TYPE_MEDICAL_HISTORY');    
        $queryResult = DB::table( $this->tableDiseases )
                        ->select( $this->tableDiseases.'.disease_name',
                                $this->tableDiseases.'.disease_id',
                                $this->table.'.pmh_id', 
                                $this->table.'.pat_id', 
                                $this->table.'.is_happend', 
                                $this->table.'.visit_id', 
                                $this->table.'.is_deleted'
                            ) 
                        ->leftJoin($this->table,function($join) use ($visitId){
                                $join->on($this->table.'.pmh_disease_id', '=', $this->tableDiseases.'.disease_id')
                                ->where($this->table.'.visit_id', '=', $visitId, 'and');
                            })
                        ->where( $this->tableDiseases.'.is_deleted',  Config::get('constants.IS_DELETED_NO') )
                        ->whereRaw("? = ANY (string_to_array(is_show_in_type,','))",[$type]);
               
        $queryResult = $queryResult->get()
                                    ->map(function($medicalHistory){
                                        if(!empty($medicalHistory->pmh_id)){
                                            $medicalHistory->pmh_id = $this->securityLibObj->encrypt($medicalHistory->pmh_id);                                            
                                        }
                                        return $medicalHistory;
                                    });
        return $queryResult;
    }

    public function getMedicalHistoryDisease(){
        $type = Config::get('constants.IS_SHOW_IN_TYPE_MEDICAL_HISTORY');
        $queryResult = DB::table( $this->tableDiseases )
                        ->select( 'disease_name', 'disease_id') 
                        ->where( 'is_deleted',  Config::get('constants.IS_DELETED_NO') )
                        ->whereRaw("? = ANY (string_to_array(is_show_in_type,','))",[$type])
                        ->get()
                        ->map(function($medicalHistoryDisease){
                            $medicalHistoryDisease->encryptedDiseaseId = $this->securityLibObj->encrypt($medicalHistoryDisease->disease_id);
                            return $medicalHistoryDisease;
                        });
        return $queryResult;
    }

    /**
     * @DateOfCreation        27 June 2018
     * @ShortDescription      This function is responsible to check if fector record is exist or not
     * @param                 integer $patId   
     * @return                object Array of symptoms records
     */
    public function checkMedicalHistoryExist($vistId, $diseaseId) 
    {        
        $queryResult = DB::table($this->table)
            ->select( 'pmh_id' ) 
            ->where('is_deleted', Config::get('constants.IS_DELETED_NO'))
            ->where('visit_id', $vistId)
            ->where('pmh_disease_id', $diseaseId);
               
        return $queryResult->get()->count();
    }

    /**
     * @DateOfCreation        27 June 2018
     * @ShortDescription      This function is responsible to save record for the Medical History
     * @param                 array $requestData   
     * @return                integer medical history id
     */
    public function addMedicalHistoryRecord($requestData)
    {
        $inserData = $this->utilityLibObj->fillterArrayKey($requestData, $this->fillable);
        $response  = $this->dbInsert($this->table, $inserData);            

        if($response){
            $id = DB::getPdo()->lastInsertId();
            return $id;
            
        }else{
            return $response;
        }
    }

    /**
    * @DateOfCreation        27 June 2018
    * @ShortDescription      This function is responsible to update Medical History Record
    * @param                 Array  $requestData   
    * @return                Array of status and message
    */
    public function updateMedicalHistoryRecord($requestData)
    {
        $updateData = $this->utilityLibObj->fillterArrayKey($requestData, $this->fillable);
        $whereData = [
                    'pmh_disease_id' => $requestData['pmh_disease_id'],
                    'pat_id'         => $requestData['pat_id'],
                    'visit_id'       => $requestData['visit_id']
                    ];
        
        $response = $this->dbUpdate($this->table, $updateData, $whereData);
        
        if($response){
            return true;
        }
        return false;
    }


    /**
     * @DateOfCreation        26 June 2018
     * @ShortDescription      This function is responsible to get the patient Medical History record
     * @param                 integer $visitId   
     * @return                object Array of medical history records
     */
    public function getPatientMedicalHistoryByPatientIdAndDoctorId($patId,$doctorId,$extra=[]) 
    {
        $queryResult = DB::table( $this->tableDiseases )
                        ->select( $this->tableDiseases.'.disease_name',
                                $this->tableDiseases.'.disease_id'
                            ) 
                        ->join($this->table,function($join) use ($patId,$doctorId){
                                $join->on($this->table.'.pmh_disease_id', '=', $this->tableDiseases.'.disease_id')
                                ->where($this->table.'.pat_id', '=', $patId, 'and')
                                ->where($this->table.'.is_happend', '!=', Config::get('dataconstants.DISEASE_FOUNT_NEVER'), 'and')
                                ->where($this->table.'.is_deleted', '=', Config::get('constants.IS_DELETED_NO'), 'and');
                            })
                        ->join($this->tableVisits,function($join) use($patId) {
                                $join->on($this->tableVisits.'.visit_id', '=', $this->table.'.visit_id');
                            })
                        ->join($this->tableDoctorPatientRelation,function($join) use($patId) {
                                $join->on($this->tableDoctorPatientRelation.'.user_id', '=', $this->tableVisits.'.user_id')
                                ->where($this->tableDoctorPatientRelation.'.pat_id', '=', $patId, 'and')
                                ->where($this->tableDoctorPatientRelation.'.is_deleted', '=', Config::get('constants.IS_DELETED_NO'), 'and');
                            })
                        ->where( $this->tableDiseases.'.is_deleted',  Config::get('constants.IS_DELETED_NO') )
                        ->groupBy($this->tableDiseases.'.disease_id');
               
        $queryResult = $queryResult->distinct()->get()
                                    ->map(function($medicalHistory){
                                        if(!empty($medicalHistory->pmh_id)){
                                            $medicalHistory->pmh_id = $this->securityLibObj->encrypt($medicalHistory->pmh_id);                                            
                                        }
                                        return $medicalHistory;
                                    });

        return $queryResult;
    }
}
