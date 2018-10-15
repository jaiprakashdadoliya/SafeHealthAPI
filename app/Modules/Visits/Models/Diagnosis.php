<?php

namespace App\Modules\Visits\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\HasApiTokens;
use App\Traits\Encryptable;
use App\Libraries\SecurityLib;
use Config;
use App\Libraries\UtilityLib;
use DB;
use App\Modules\Setup\Models\StaticDataConfig as StaticData;

/**
 * Visits
 *
 * @package                ILD India Registry
 * @subpackage             Visits
 * @category               Model
 * @DateOfCreation         11 june 2018
 * @ShortDescription       This Model to handle database operation of Visits
 **/
class Diagnosis extends Model {

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

        // Init General staticData Model Object
        $this->staticDataObj = new StaticData();
    }

    /**
    *@ShortDescription Table for the Users.
    *
    * @var String
    */
    protected $table                        = 'patients_visit_diagnosis';
    protected $tableDiseases                = 'diseases';
    protected $tableDiagnosisExtraInfo      = 'diagnosis_extra_info';
    protected $tableDiseaseExtraInfo        = 'disease_extra_information';
    protected $tableDoctorPatientRelation   = 'doctor_patient_relation';
    
    // This protected member contains fields that need to encrypt while saving in database
    protected $encryptable = [];
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [ 'pat_id', 
                            'visit_id',
                            'disease_id',
                            'date_of_diagnosis',
                            'diagnosis_end_date',
                            'resource_type',
                            'ip_address'
                        ];

    /**
     *@ShortDescription Override the primary key.
     *
     * @var string
     */
    protected $primaryKey   = 'visit_diagnosis_id';
    protected $tableVisits  = 'patients_visits';

    /**
     * @DateOfCreation        5 JULY 2018
     * @ShortDescription      This function is responsible to get the patient diagnosis records
     * @param                 integer $visitId
     * @return                object Array of medical history records
     */
    public function getPatientMedicalHistory($visitId = null, $patId = null) 
    {   
        $type = Config::get('constants.IS_SHOW_IN_TYPE_VISIT_DIAGNOSIS');    
        $queryResult = DB::table( $this->tableDiseases )
                        ->select( $this->tableDiseases.'.disease_name',
                                $this->tableDiseases.'.disease_id',
                                $this->table.'.visit_diagnosis_id', 
                                $this->table.'.pat_id', 
                                $this->table.'.visit_id', 
                                $this->table.'.date_of_diagnosis', 
                                $this->table.'.diagnosis_end_date', 
                                $this->table.'.is_deleted'
                            ) 
                        ->leftJoin($this->table,function($join) use ($visitId){
                                $join->on($this->table.'.disease_id', '=', $this->tableDiseases.'.disease_id')
                                ->where($this->table.'.visit_id', '=', $visitId, 'and');
                            })
                        ->where( $this->tableDiseases.'.is_deleted',  Config::get('constants.IS_DELETED_NO') )
                        ->whereRaw("? = ANY (string_to_array(is_show_in_type,','))",[$type]);
               
        $queryResult = $queryResult->orderBy($this->tableDiseases.'.is_show_extra_information', 'asc')
                                    ->get()
                                    ->map(function($patientDiseases){
                                        $patientDiseases->disease_id = $this->securityLibObj->encrypt($patientDiseases->disease_id);
                                        if(!empty($patientDiseases->visit_diagnosis_id)){
                                            $patientDiseases->pat_id             = $this->securityLibObj->encrypt($patientDiseases->pat_id);
                                            $patientDiseases->visit_id           = $this->securityLibObj->encrypt($patientDiseases->visit_id);
                                            $patientDiseases->visit_diagnosis_id = $this->securityLibObj->encrypt($patientDiseases->visit_diagnosis_id);
                                            $patientDiseases->date_of_diagnosis  = !empty($patientDiseases->date_of_diagnosis) ? date('d/m/Y', strtotime($patientDiseases->date_of_diagnosis)) : NULL;
                                            $patientDiseases->diagnosis_end_date = !empty($patientDiseases->diagnosis_end_date) ? date('d/m/Y', strtotime($patientDiseases->diagnosis_end_date)) : NULL;
                                        }
                                        return $patientDiseases;
                                    });
        return $queryResult;
    }

    /**
     * @DateOfCreation        6 JULY 2018
     * @ShortDescription      This function is responsible to get the patient diagnosis records
     * @param                 integer $visitId
     * @return                object Array of medical history records
     */
    public function getDiagnosisExtraFectors($diseaseId, $diseaseName, $visitDiagnosisID) 
    {   
        $diseaseId   = $this->securityLibObj->decrypt($diseaseId);
        $viewIn      = Config::get('constants.IS_SHOW_IN_TYPE_VISIT_FORM'); //VIEW IN 1
        $queryResult = DB::table( $this->tableDiseaseExtraInfo )
                        ->select($this->tableDiseaseExtraInfo.'.dei_id', 'info_type', 'info_title', 'info_view_in', 'diagnosis_fector_key', 'diagnosis_fector_value') 
                        ->leftJoin($this->tableDiagnosisExtraInfo,function($join) use ($visitDiagnosisID){
                                $join->on($this->tableDiseaseExtraInfo.'.dei_id', '=', $this->tableDiagnosisExtraInfo.'.diagnosis_fector_key')
                                ->where($this->tableDiagnosisExtraInfo.'.visit_diagnosis_id', '=', $visitDiagnosisID, 'and')
                                ->where($this->tableDiagnosisExtraInfo.'.is_deleted', '=', Config::get('constants.IS_DELETED_NO'), 'and');
                            })
                        ->where( $this->tableDiseaseExtraInfo.'.is_deleted',  Config::get('constants.IS_DELETED_NO') )
                        ->where( 'info_view_in', $viewIn )
                        ->where( 'disease_id', $diseaseId )
                        ->orderBy('info_order', 'asc');
               
        $queryResult = $queryResult->get()
                                    ->map(function($diseasesExtraInfo) use ($diseaseName){
                                        $diseasesExtraInfo->dei_id = $this->securityLibObj->encrypt($diseasesExtraInfo->dei_id);
                                        $diseasesExtraInfo->extraInfoOptions = [];
                                        $diseasesExtraInfo->diseaseName = $diseaseName;
                                        if($diseasesExtraInfo->info_type == 'checkbox'){
                                            $diseasesExtraInfo->extraInfoOptions = array_map(function($row){
                                                $row['id'] = (string) $row['id'];
                                                return $row;

                                            },$this->staticDataObj->getDiseaseExtraOptions($diseaseName));                                          
                                        }
                                        return $diseasesExtraInfo;
                                    });
        return $queryResult;
    }

     /**
     * @DateOfCreation        26 June 2018
     * @ShortDescription      This function is responsible to get the patient Medical History record
     * @param                 integer $visitId   
     * @return                object Array of medical history records
     */
    public function getPatientDiagnosisByPatientIdAndDoctorId($patId, $doctorId, $extra=[]) 
    {  
        $type = Config::get('constants.IS_SHOW_IN_TYPE_VISIT_DIAGNOSIS');
        $queryResult = DB::table( $this->tableDiseases )
                        ->select( 
                            $this->tableDiseases.'.disease_name',
                            $this->tableDiseases.'.disease_id'
                        ) 
                        ->join($this->table,function($join) use ($patId){
                                $join->on($this->table.'.disease_id', '=', $this->tableDiseases.'.disease_id')
                                ->where($this->table.'.pat_id', '=', $patId, 'and')
                                ->where($this->table.'.date_of_diagnosis', '!=',null, 'and')
                                ->where($this->table.'.is_deleted', '=', Config::get('constants.IS_DELETED_NO'), 'and');
                            })
                        ->join($this->tableVisits,function($join) {
                                $join->on($this->tableVisits.'.visit_id', '=', $this->table.'.visit_id')
                                ->where($this->tableVisits.'.is_deleted', '=', Config::get('constants.IS_DELETED_NO'), 'and');
                            })
                        ->join($this->tableDoctorPatientRelation,function($join) use($patId) {
                                $join->on($this->tableDoctorPatientRelation.'.user_id', '=', $this->tableVisits.'.user_id')
                                ->where($this->tableDoctorPatientRelation.'.pat_id', '=', $patId, 'and')
                                ->where($this->tableDoctorPatientRelation.'.is_deleted', '=', Config::get('constants.IS_DELETED_NO'), 'and');
                            })
                        ->where( $this->tableDiseases.'.is_deleted',  Config::get('constants.IS_DELETED_NO') )
                        ->groupBy($this->tableDiseases.'.disease_id');
        
        $queryResult = $queryResult->distinct()->get()
                                    ->map(function($patientDiseases){
                                        if(!empty($patientDiseases->visit_diagnosis_id)){
                                            $patientDiseases->visit_diagnosis_id = $this->securityLibObj->encrypt($patientDiseases->visit_diagnosis_id);
                                        }
                                        return $patientDiseases;
                                    });
        return $queryResult;
    }

    /**
     * @DateOfCreation        8 Aug 2018
     * @ShortDescription      This function is responsible to get the patient diagnosis History records
     * @param                 integer $visitId
     * @return                object Array of medical history records
     */
    public function getPatientDiagnosisHistoryList($requestData) 
    {   
        $visitId = $requestData['visit_id'];
        $queryResult = DB::table( $this->tableDiseases )
                        ->select( $this->tableDiseases.'.disease_name',
                                $this->tableDiseases.'.disease_id',
                                $this->table.'.visit_diagnosis_id', 
                                $this->table.'.pat_id', 
                                $this->table.'.visit_id', 
                                $this->table.'.date_of_diagnosis', 
                                $this->table.'.diagnosis_end_date', 
                                $this->table.'.is_deleted'
                            ) 
                        ->join($this->table,function($join) use ($visitId){
                                $join->on($this->table.'.disease_id', '=', $this->tableDiseases.'.disease_id')
                                ->where($this->table.'.visit_id', '=', $visitId, 'and');
                            })
                        ->where( $this->tableDiseases.'.is_deleted', Config::get('constants.IS_DELETED_NO') )
                        ->where( $this->table.'.is_deleted', Config::get('constants.IS_DELETED_NO') );

        /* Condition for Filtering the result */
        if(!empty($requestData['filtered'])){
            foreach ($requestData['filtered'] as $key => $value) {
                $queryResult = $queryResult->where(function ($queryResult) use ($value){
                                $queryResult
                                ->where($this->tableDiseases.'.disease_name', 'ilike', "%".$value['value']."%")
                                ->orWhere(DB::raw('CAST(date_of_diagnosis AS TEXT)'), 'ilike', '%'.$value['value'].'%');
                            });
            }
        }

        /* Condition for Sorting the result */
        if(!empty($requestData['sorted'])){
            foreach ($requestData['sorted'] as $key => $value) {
                $orderBy     = $value['desc'] ? 'desc' : 'asc';
                $queryResult = $queryResult->orderBy($value['id'], $orderBy);
            }
        }
        if($requestData['page'] > 0){
            $offset = $requestData['page']*$requestData['pageSize'];
        }else{
            $offset = 0;
        }
        $result['pages'] = ceil($queryResult->count()/$requestData['pageSize']);
               
        $result['result'] = $queryResult->offset($offset)
                                    ->limit($requestData['pageSize'])
                                    ->get()
                                    ->map(function($patientDiseases){
                                        $patientDiseases->disease_id = $this->securityLibObj->encrypt($patientDiseases->disease_id);
                                        if(!empty($patientDiseases->visit_diagnosis_id)){
                                            $patientDiseases->pat_id             = $this->securityLibObj->encrypt($patientDiseases->pat_id);
                                            $patientDiseases->visit_id           = $this->securityLibObj->encrypt($patientDiseases->visit_id);
                                            $patientDiseases->visit_diagnosis_id = $this->securityLibObj->encrypt($patientDiseases->visit_diagnosis_id);
                                            $patientDiseases->date_of_diagnosis  = $patientDiseases->date_of_diagnosis;
                                        }
                                        return $patientDiseases;
                                    });

        return $result;
    }

    /**
     * @DateOfCreation        8 Aug 2018
     * @ShortDescription      This function is responsible to get the patient diagnosis History records
     * @param                 integer $visitId
     * @return                object Array of medical history records
     */
    public function patientDiagnosisOptionList($requestData) 
    {   
        $diseaseList = $this->dbBatchSelect($this->tableDiseases, ['disease_name', 'disease_id'], ['is_deleted' => Config::get('constants.IS_DELETED_NO')]);
        $diseaseList = $diseaseList
                        ->map(function($diseases){
                            $diseases->disease_id = $this->securityLibObj->encrypt($diseases->disease_id);
                            return $diseases;
                        });
        return $diseaseList;
    }

    /**
     * @DateOfCreation        8 Aug 2018
     * @ShortDescription      This function is responsible to add patient diagnosis records
     * @param                 array Request Data
     * @return                Integer id
     */
    public function addUpdatePatientVisitDiagnosis($requestData, $visitDiagnosisID = null){
        
        $data = [
            'pat_id'            => $requestData['pat_id'],
            'visit_id'          => $requestData['visit_id'],
            'disease_id'        => $requestData['disease_id'],
            'date_of_diagnosis' => $requestData['date_of_diagnosis'],
            'diagnosis_end_date'=> $requestData['diagnosis_end_date'],
            'resource_type'     => $requestData['resource_type'],
            'ip_address'        => $requestData['ip_address'],
        ];

        if($visitDiagnosisID){
            return $this->dbUpdate($this->table, $data, ['visit_diagnosis_id' => $visitDiagnosisID]);
        }else{
            return $this->dbInsert($this->table, $data);            
        }
    }

    /**
     * @DateOfCreation        8 Aug 2018
     * @ShortDescription      This function is responsible to delete patient diagnosis records
     * @param                 array Request Data
     * @return                Integer id
     */
    public function deletePatientDiagnosis($requestData){
        
        $data = [
            'is_deleted'    => Config::get('constants.IS_DELETED_YES'),
            'resource_type' => $requestData['resource_type'],
            'ip_address'    => $requestData['ip_address'],
        ];

        return $this->dbUpdate($this->table, $data, ['visit_diagnosis_id' =>$requestData['visit_diagnosis_id']]);
    }

    /**
     * @DateOfCreation        8 Aug 2018
     * @ShortDescription      This function is responsible to insert master Symptom data if Symptom name not exists
     * @param                 Array  $requestData
     * @return                Array of id
     */
    public function createDiseaseId($requestData) {
        $diseaseName    = trim($requestData['disease_name']);
        $resultDisease  = $this->getDiseaseDataByDiseaseName($diseaseName);

        if(!empty($resultDisease) && isset($resultDisease->disease_id)){
            return $resultDisease->disease_id;
        }else{
            $filldata   = ['is_show_in_type', 'disease_name', 'ip_address', 'resource_type'];
            $requestData['is_show_in_type'] = Config::get('constants.IS_SHOW_IN_TYPE_VISIT_DIAGNOSIS');
            $inserData  = $this->utilityLibObj->fillterArrayKey($requestData, $filldata);
            $response   = $this->dbInsert($this->tableDiseases, $inserData);
            if($response){
                $id = DB::getPdo()->lastInsertId();
                return $id;
            }else{
                return $response;
            }  
        }
    }

    /**
     * @DateOfCreation        18 June 2018
     * @ShortDescription      This function is responsible to get the symptoms visit data
     * @param                 integer $symptomVisitId   
     * @return                object Array of symptoms visit records
     */
    public function getDiseaseDataByDiseaseName($diseaseName) {
        $diseaseName = trim($diseaseName);
        $queryResult = DB::table($this->tableDiseases)
                    ->select('disease_id')
                    ->where('disease_name', 'ILIKE', $diseaseName)
                    ->where('is_deleted',Config::get('constants.IS_DELETED_NO'))
                    ->first();
        return $queryResult;
    }

    /**
     * @DateOfCreation        8 Aug 2018
     * @ShortDescription      This function is responsible to get the patient Diagnosis extra information
     * @param                 integer $visitId, $patientId, $encrypt   
     * @return                object Array of Physical Changes In records
     */
    public function getpatientDiagnosis($visitId, $patientId = '', $diseaseId = '', $encrypt = true) 
    {
        $dataQuery = 'select pvd.date_of_diagnosis,
                    (select string_agg(dei.diagnosis_fector_key::character varying, \',\') as diagnosis_fector_key from diagnosis_extra_info as dei where dei.visit_diagnosis_id= pvd.visit_diagnosis_id),
                    (select string_agg(dei.diagnosis_fector_value, \',\') as diagnosis_fector_value from diagnosis_extra_info as dei where dei.visit_diagnosis_id= pvd.visit_diagnosis_id), 
                    (select string_agg(dei.dei_id::character varying, \',\') as dei_id from diagnosis_extra_info as dei where dei.visit_diagnosis_id= pvd.visit_diagnosis_id)

                    from patients_visit_diagnosis as pvd where pvd.visit_id = '.$visitId;

        if(!empty($patientId)){
            $dataQuery .= ' AND pvd.pat_id = '.$patientId;
        }

        if(!empty($diseaseId)){
            $dataQuery .= ' AND pvd.disease_id = '.$diseaseId;
        }
        
        $queryResult =  DB::select(DB::raw($dataQuery));
        $queryResultData = [];
        if($encrypt && !empty($queryResult)){
            $dateOfDiagnosis = '';
            foreach($queryResult as $key => $result){
                $explodeDeiId   = explode(',', $result->dei_id);
                $explodeKey     = explode(',', $result->diagnosis_fector_key);
                $explodeValue   = explode(',', $result->diagnosis_fector_value);

                foreach ($explodeDeiId as $key => $value) {
                    $temp =[];
                    $keyfactor = $explodeKey[$key];

                    $valuefactor = $explodeValue[$key];
                    $temp['dei_id']                 = $this->securityLibObj->encrypt($value);
                    $temp['diagnosis_fector_key']   = $this->securityLibObj->encrypt($keyfactor);
                    $temp['diagnosis_fector_value'] = ($valuefactor);                    
                    $temp['diseaseId']              = $this->securityLibObj->encrypt($diseaseId); 
                    $queryResultData[] = (object)$temp;                   
                }
                $dateOfDiagnosis = $result->date_of_diagnosis; 
            }

            $queryResultData[] = (object)[
                'date_of_diagnosis'     => $dateOfDiagnosis,
                'disease_id'            => $diseaseId,
                'diagnosis_fector_key'  => 1
            ];
        }
        
        return $queryResultData;        
    }
}
