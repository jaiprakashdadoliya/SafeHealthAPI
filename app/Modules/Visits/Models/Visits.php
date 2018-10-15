<?php

namespace App\Modules\Visits\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\HasApiTokens;
use App\Traits\Encryptable;
use App\Libraries\SecurityLib;
use Config;
use App\Libraries\UtilityLib;
use App\Libraries\DateTimeLib;
use DB;
use App\Modules\Patients\Models\Patients;
use App\Modules\Visits\Models\Symptoms;
use App\Modules\Visits\Models\Spirometries;
use App\Modules\Visits\Models\Hospitalizations;
use App\Modules\Patients\Models\PatientsActivities;

class Visits extends Model {

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

        // Init dateTime library object
        $this->dateTimeLibObj = new DateTimeLib(); 

        // Init Patient model object
        $this->patientModelObj = new Patients(); 

        // Init Symptoms model object
        $this->symptomsModelObj = new Symptoms();

        // Init Spirometries model object
        $this->spirometriesObj = new Spirometries();

        // Init PatientDeathInfo model object
        $this->hospitalizationsObj = new Hospitalizations();

        // Init Patients Activities Model Object
        $this->patientActivitiesModelObj = new PatientsActivities();
    }

    protected $encryptable = [];

    /**
    *@ShortDescription Table for the Users.
    *
    * @var String
    */
    protected $tableHospitalizations        = 'hospitalizations';
    protected $tableHospitalizationExtraInfo= 'hospitalizations_extra_info';
    protected $tablePatientsVisits          = 'patients_visits';
    protected $tablePatientsDeathInfo       = 'patients_death_info';
    protected $tableDiagnosisExtraInfo      = 'diagnosis_extra_info';
    protected $tableDiagnosisInfo           = 'patients_visit_diagnosis';
    protected $tableTreatmentRequirement    = 'treatment_requirement';
    protected $tablePhysicalExaminations    = 'physical_examinations';
    protected $tablevitals                  = 'vitals';
    protected $tableVisitsChangesIn         = 'visits_changes_in';
    protected $tablePatientSixmwts          = 'patient_sixmwts';
    protected $tablePatientSixmwtFectors    = 'patient_sixmwt_fectors';
    protected $tablePatientSpirometries     = 'spirometries';
    protected $tablePatientSpirometryFectors= 'spirometry_fectors';
    protected $tablePatientInvestigation    = 'investigation';
    protected $tableMedicines               = 'medicines';
    protected $tableTreatments              = 'treatments';
    protected $tablePatients                = 'patients';
    protected $tableUsers                   = 'users';
    protected $tableDoctorPatientRelation   = 'doctor_patient_relation';
    protected $tableVisitComponents         = 'visits_components';
    protected $tableVisitComponentsSetting  = 'visits_components_settings';


    /**
     * @DateOfCreation        07 August 2018
     * @ShortDescription      This function is responsible get visit components to load
     * @param                 array $data   
     * @return                object Array of components
     */
    public function getVisitComponents($requestData)
    {
        $queryResult = DB::table( $this->tableVisitComponents )
        ->select( $this->tableVisitComponents.'.component_title', $this->tableVisitComponents.'.component_container_name', $this->tableVisitComponents.'.visit_cmp_order', $this->tableVisitComponentsSetting.'.visit_cmp_id',$this->tableVisitComponentsSetting.'.show_in')    
        ->join($this->tableVisitComponentsSetting, $this->tableVisitComponents.'.visit_cmp_id','=', $this->tableVisitComponentsSetting.'.visit_cmp_id')
        ->where( $this->tableVisitComponentsSetting.'.is_visible',  Config::get('constants.IS_DELETED_NO') )
        ->where( $this->tableVisitComponentsSetting.'.user_id',  $requestData['userId'] )
        ->where( $this->tableVisitComponents.'.is_deleted', Config::get('constants.IS_DELETED_NO'));
        if($requestData['visitNumber'] > 1){
            $queryResult = $queryResult->where( $this->tableVisitComponentsSetting.'.show_in',  Config::get('constants.SHOW_IN_FOLLOWUP_YES'));
        }
        $queryResult = $queryResult->orderBy($this->tableVisitComponents.'.visit_cmp_order','asc')
        ->get();
        return $queryResult;
               
    }

    /**
     * @DateOfCreation        11 september 2018
     * @ShortDescription      This function is responsible get doctor ID from visit and pat Id
     * @param                 array $data   
     * @return                Doctor ID
     */
    public function getCurrentVisitDoctorId($patId, $visitId)
    {   
        $visitId = $this->securityLibObj->decrypt($visitId);
        return $query = DB::table( $this->tablePatientsVisits )
        ->select($this->tablePatientsVisits.'.user_id')
        ->where($this->tablePatientsVisits.'.visit_id', $visitId)
        ->where($this->tablePatientsVisits.'.pat_id', $patId)
        ->first()->user_id;
    }


    /**
     * @DateOfCreation        07 August 2018
     * @ShortDescription      This function is responsible get visit components to load
     * @param                 array $data   
     * @return                object Array of components
     */
    public function MasterVisitComponentsList($requestData)
    {
        $userId = $requestData['userId'];
        $query = DB::table( $this->tableVisitComponents )
        ->select( $this->tableVisitComponents.'.component_title', $this->tableVisitComponents.'.component_container_name', $this->tableVisitComponents.'.visit_cmp_id',DB::raw("(SELECT is_visible FROM ".$this->tableVisitComponentsSetting." WHERE ".$this->tableVisitComponentsSetting.".visit_cmp_id = ".$this->tableVisitComponents.".visit_cmp_id AND ".$this->tableVisitComponentsSetting.".user_id = {$userId}) AS is_visible"),DB::raw("(SELECT show_in FROM ".$this->tableVisitComponentsSetting." WHERE ".$this->tableVisitComponentsSetting.".visit_cmp_id = ".$this->tableVisitComponents.".visit_cmp_id AND ".$this->tableVisitComponentsSetting.".user_id = {$userId}) AS show_in"))    
        ->where( $this->tableVisitComponents.'.is_deleted', Config::get('constants.IS_DELETED_NO'));
            /* Condition for Filtering the result */
            if(!empty($requestData['filtered'])){
                foreach ($requestData['filtered'] as $key => $value) {
                    $query = $query->where($this->tableVisitComponents.'.component_title', 'ilike', "%".$value['value']."%");
                }
            }
        
        /* Condition for Sorting the result */
        if(!empty($requestData['sorted'])){
            foreach ($requestData['sorted'] as $key => $value) {
                $orderBy = $value['desc'] ? 'desc' : 'asc';
                $query = $query->orderBy($value['id'], $orderBy);
            }
        }
        if(!empty($requestData['page']) && $requestData['page'] > 0){
            $offset = $requestData['page']*$requestData['pageSize'];
        }else{
            $offset = 0;
        }
        $queryResult['pages'] = ceil($query->count()/$requestData['pageSize']);
        $queryResult['result'] = $query
                    ->offset($offset)
                    ->limit($requestData['pageSize'])
                    ->get()
                    ->map(function ($visitComponents) {
                            $visitComponents->visit_cmp_id = $this->securityLibObj->encrypt($visitComponents->visit_cmp_id);
                            return $visitComponents;
                        });
        return $queryResult;
               
    }


    public function UpdateVisitSettingComponent($requestData)
    {
        $whereCheck = [
            'user_id' => $requestData['user_id'],
            'visit_cmp_id'=>$this->securityLibObj->decrypt($requestData['visit_cmp_id'])
        ];
        $is_exist = DB::table($this->tableVisitComponentsSetting)->where($whereCheck)->count();
        if($requestData['is_visible'] == null || empty($requestData['is_visible'])){
            $requestData['is_visible'] = 1;
        }
        if($requestData['show_in'] == null || empty($requestData['show_in'])){
            $requestData['show_in'] = 1;
        }
        if($is_exist == 1){
            $response = $this->dbUpdate($this->tableVisitComponentsSetting, 
                        ['is_visible'=>$requestData['is_visible'],'show_in'=>$requestData['show_in']], $whereCheck);
            if($response){
                return true;
            }
        }else{
            unset($requestData['component_title']);
            unset($requestData['component_container_name']);
            $requestData['visit_cmp_id'] = $this->securityLibObj->decrypt($requestData['visit_cmp_id']);
            $response = $this->dbInsert($this->tableVisitComponentsSetting, $requestData);
            return true;
        }
    }
    
    /**
     * @DateOfCreation        10 July 2018
     * @ShortDescription      This function is responsible to save / update patient visits data
     * @param                 array $data   
     * @return                object Array of medical history records
     */
    public function savePatientsVisitData($data){

        if(!empty($data['visit_followed_elsewhere'] || $data['visit_followup_status'] || $data['visit_symptom_status'] || $data['status'] || $data['visit_suspect_active_infection'])){
            
            if(empty($data['visit_followed_elsewhere'])) unset($data['visit_followed_elsewhere']);
            if(empty($data['visit_followup_status'])) unset($data['visit_followup_status']);
            if(empty($data['visit_symptom_status'])) unset($data['visit_symptom_status']);

            $whereCheck = ['pat_id' => $data['pat_id'], 'visit_id' => $data['visit_id'], 'is_deleted' => Config::get('constants.IS_DELETED_NO')];
            $response = $this->dbUpdate($this->tablePatientsVisits, $data, $whereCheck);

            if($response){
                return true;
            }
            return false;            
        }
        return true;
    }

    /**
     * @DateOfCreation        7 Aug 2018
     * @ShortDescription      This function is responsible to save / update patient visits Status
     * @param                 array $data   
     * @return                object Array of medical history records
     */
    public function saveVisitStatus($data){

        if($data['status']){
            $whereCheck = ['pat_id' => $data['pat_id'], 'visit_id' => $data['visit_id'], 'is_deleted' => Config::get('constants.IS_DELETED_NO')];
            $response = $this->dbUpdate($this->tablePatientsVisits, $data, $whereCheck);

            if($response){
                return true;
            }
            return false;            
        }
        return true;
    }

    /**
     * @DateOfCreation        10 July 2018
     * @ShortDescription      This function is responsible to save / update patient visits hospitalization data
     * @param                 array $insertData   
     * @return                object Array of medical history records
     */
    public function saveHispitalizationData($insertData){
        $response = false;
        if(!empty($insertData['hostpitalization_cardiac_myocardial_infarction'] || $insertData['hospitalization_respiratory'] || $insertData['hospitalization_status'] || $insertData['hospitalization_how_many'] || $insertData['hospitalization_why'] || $insertData['date_of_hospitalization'])){

            $whereCheck = ['pat_id' => $insertData['pat_id'],'visit_id' => $insertData['visit_id']];
            $checkDataExist = $this->checkIfRecordExist($this->tableHospitalizations, 'hospitalization_id', $whereCheck);

            if($checkDataExist){
                $response = $this->dbUpdate($this->tableHospitalizations, $insertData, $whereCheck);
            }else{
                $response = $this->dbInsert($this->tableHospitalizations, $insertData);
            }
           
            if($response){
                return true;
            }
            return false;
        }
        return true;
    }

    /**
     * @DateOfCreation        10 July 2018
     * @ShortDescription      This function is responsible to save / update patient death info
     * @param                 array $insertData   
     * @return                object Array of medical history records
     */
    public function savePatientsDeathInfoData($insertData){
        $response = false;
       
        if(!empty(trim($insertData['patient_death_status']) || trim($insertData['date_of_death']) || trim($insertData['cause_of_death']))){

            $whereCheck = ['pat_id' => $insertData['pat_id'],'visit_id' => $insertData['visit_id']];
            $checkDataExist = $this->checkIfRecordExist($this->tablePatientsDeathInfo, 'pdi_id', $whereCheck);

            if($checkDataExist){
                $response = $this->dbUpdate($this->tablePatientsDeathInfo, $insertData, $whereCheck);
            }else{
                $response = $this->dbInsert($this->tablePatientsDeathInfo, $insertData);
            }            

            if($response){
                return true;
            }
            return false;
        }
        return true;
    }

    /**
     * @DateOfCreation        10 July 2018
     * @ShortDescription      This function is responsible to save / update Diagnosis Extra Info
     * @param                 array $insertData   
     * @return                object Array of medical history records
     */
    public function saveDiagnosisExtraInfo($insertData){

        $error = false;
        $insertDataArr = [];
        
        foreach ($insertData as $value) {
            if(!empty($value['diagnosis_fector_value'])){
                $whereCheck     = ['disease_id' => $value['disease_id'], 'pat_id' => $value['pat_id'],'visit_id' => $value['visit_id']];
                $getDiagnosisId = $this->checkIfRecordExist($this->tableDiagnosisInfo, 'visit_diagnosis_id', $whereCheck, 'get_record');
                $getDiagnosisId = json_decode(json_encode($getDiagnosisId));

                if(!empty($getDiagnosisId)){
                    unset($value['visit_id']);
                    unset($value['pat_id']);
                    unset($value['disease_id']);
                    
                    $diagnosisId = $getDiagnosisId[0]->visit_diagnosis_id;
                    
                    $value['visit_diagnosis_id'] = $diagnosisId;
                    $whereCheckExtraInfo = ['visit_diagnosis_id' => $diagnosisId, 'diagnosis_fector_key' => $value['diagnosis_fector_key']];
                    $checkDataExist = $this->checkIfRecordExist($this->tableDiagnosisExtraInfo, 'dei_id', $whereCheckExtraInfo);

                    if($checkDataExist){
                        // Update
                        $response = $this->dbUpdate($this->tableDiagnosisExtraInfo, $value, $whereCheckExtraInfo);
                        
                        if(!$response){
                            $error = true;
                        }
                    }else{
                        // insert
                        $insertDataArr[] = $value;
                    }
                }else{
                    // add new diagnosis record and insert factor
                    $dataDiagnosis[] = $value;
                    unset($dataDiagnosis[0]['diagnosis_fector_value']);
                    unset($dataDiagnosis[0]['diagnosis_fector_key']);
                        
                        
                    $diagnosisData  = $this->saveDiagnosisInfo($dataDiagnosis);

                    if($diagnosisData){
                        $whereCheck     = ['disease_id' => $value['disease_id'], 'pat_id' => $value['pat_id'],'visit_id' => $value['visit_id']];
                        $getDiagnosisId = $this->checkIfRecordExist($this->tableDiagnosisInfo, 'visit_diagnosis_id', $whereCheck, 'get_record');
                        $getDiagnosisId = json_decode(json_encode($getDiagnosisId));

                        unset($value['visit_id']);
                        unset($value['pat_id']);
                        unset($value['disease_id']);

                        $value['visit_diagnosis_id'] = $getDiagnosisId[0]->visit_diagnosis_id;
                        
                        // insert
                        $insertDataArr[] = $value;
                    }                    
                }
            }
        }

        if(!empty($insertDataArr)){
            if(!$this->dbBatchInsert($this->tableDiagnosisExtraInfo, $insertDataArr)){
                $error = true;
            }
        }

        if(!$error){
            return true;
        }
        return false;
    }
    
    /**
     * @DateOfCreation        10 July 2018
     * @ShortDescription      This function is responsible to save / update Physical Examinations
     * @param                 array $insertData   
     * @return                object Array of medical history records
     */
    public function savePhysicalExaminations($insertData){
        $error = false;
        $insertDataArr = [];
        foreach ($insertData as $value) {
            $whereCheck = ['fector_id' => $value['fector_id'], 'pat_id' => $value['pat_id'],'visit_id' => $value['visit_id']];
            $checkDataExist = $this->checkIfRecordExist($this->tablePhysicalExaminations, 'pe_id', $whereCheck);
            
            if($checkDataExist){
                // Update
                $response = $this->dbUpdate($this->tablePhysicalExaminations, $value, $whereCheck);
                
                if(!$response){
                    $error = true;
                }
            }else{
                if(!empty($value['fector_value'])){
                    // insert
                    $insertDataArr[] = $value;
                }        
            }

        }

        if(!empty($insertDataArr)){
            if(!$this->dbBatchInsert($this->tablePhysicalExaminations, $insertDataArr)){
                $error = true;
            }
        }

        if(!$error){
            return true;
        }
        return false;
    }
    
    /**
     * @DateOfCreation        10 July 2018
     * @ShortDescription      This function is responsible to save / update Treatment Requirement
     * @param                 array $insertData   
     * @return                object Array of medical history records
     */
    public function saveTreatmentRequirement($insertData){
        $error = false;
        $insertDataArr = [];
        foreach ($insertData as $value) {
            if(!empty($value['fector_value']) || $value['fector_id'] == Config::get('dataconstants.TREATMENT_FECTOR_VACCINE')){
                $whereCheck = ['fector_id' => $value['fector_id'], 'pat_id' => $value['pat_id'],'visit_id' => $value['visit_id']];
                $checkDataExist = $this->checkIfRecordExist($this->tableTreatmentRequirement, 'treatment_requirement_id', $whereCheck);

                if($checkDataExist){
                    // Update
                    $response = $this->dbUpdate($this->tableTreatmentRequirement, $value, $whereCheck);
                    
                    if(!$response){
                        $error = true;
                    }
                }else{
                    // insert
                    $insertDataArr[] = $value;
                } 
            }
        }

        if(!empty($insertDataArr)){
            if(!$this->dbBatchInsert($this->tableTreatmentRequirement, $insertDataArr)){
                $error = true;
            }
        }

        if(!$error){
            return true;
        }
        return false;
    }
    
    /**
     * @DateOfCreation        10 July 2018
     * @ShortDescription      This function is responsible to save / update Diagnosis Info
     * @param                 array $insertData   
     * @return                object Array of medical history records
     */
    public function saveDiagnosisInfo($insertData){

        $error = false;
        $insertDataArr = [];
        foreach ($insertData as $value) {
            $whereCheck = ['disease_id' => $value['disease_id'], 'pat_id' => $value['pat_id'],'visit_id' => $value['visit_id']];
            $checkDataExist = $this->checkIfRecordExist($this->tableDiagnosisInfo, 'visit_diagnosis_id', $whereCheck);

            if($checkDataExist){
                // Update
                $response = $this->dbUpdate($this->tableDiagnosisInfo, $value, $whereCheck);
                
                if(!$response){
                    $error = true;
                }
            }else{
                // insert
                $insertDataArr[] = $value;
            }
        }

        if(!empty($insertDataArr)){
            if(!$this->dbBatchInsert($this->tableDiagnosisInfo, $insertDataArr)){
                $error = true;
            }
        }

        if(!$error){
            return true;
        }
        return false;
    }
    
    /**
     * @DateOfCreation        10 July 2018
     * @ShortDescription      This function is responsible to save / update Vitals
     * @param                 array $insertData   
     * @return                object Array of medical history records
     */
    public function saveVitals($insertData, $userId = null){
        $error = false;
        $insertDataArr = [];

        $patId   = null;
        $visitId = null;
        foreach ($insertData as $value) {
            if(!empty($value['fector_value'])){

                if(empty($patId)){
                    $patId = $value['pat_id'];
                }

                if(empty($visitId)){
                    $visitId = $value['visit_id'];
                }

                $whereCheck = ['fector_id' => $value['fector_id'], 'pat_id' => $value['pat_id'],'visit_id' => $value['visit_id']];
                $checkDataExist = $this->checkIfRecordExist($this->tablevitals, 'vitals_id', $whereCheck);

                if($checkDataExist){
                    // Update
                    $response = $this->dbUpdate($this->tablevitals, $value, $whereCheck);
                    
                    if(!$response){
                        $error = true;
                    }
                }else{
                    // insert
                    $insertDataArr[] = $value;
                }
            }
        }

        if(!empty($insertDataArr)){
            if(!$this->dbBatchInsert($this->tablevitals, $insertDataArr)){
                $error = true;
            }
            
            if(!empty($userId) && !empty($patId) && !empty($visitId)){
                $activityData = ['pat_id' => $patId, 'user_id' => $userId, 'activity_table' => 'vitals', 'visit_id' => $visitId];
                $response = $this->patientActivitiesModelObj->insertActivity($activityData);
            }
        }

        if(!$error){
            return true;
        }
        return false;
    }
    
    /**
     * @DateOfCreation        10 July 2018
     * @ShortDescription      This function is responsible to save / update Investigation data
     * @param                 array $insertData   
     * @return                object Array of medical history records
     */
    public function saveInvestigation($insertData){

        if(!empty($insertData['weight'] || $insertData['height'] || $insertData['bmi'] )){
            $whereCheck = ['pat_id' => $insertData['pat_id'],'visit_id' => $insertData['visit_id']];
            $checkDataExist = $this->checkIfRecordExist($this->tablePatientInvestigation, 'investigation_id', $whereCheck);

            if($checkDataExist){
                // Update
                $response = $this->dbUpdate($this->tablePatientInvestigation, $insertData, $whereCheck);
            }else{
                // insert
                $response = $this->dbInsert($this->tablePatientInvestigation, $insertData);
            }

            if($response){
                return true;
            }
            return false;
        }
        return true;
    }
    
    /**
     * @DateOfCreation        10 July 2018
     * @ShortDescription      This function is responsible to save / update Visits Changes In data
     * @param                 array $insertData   
     * @return                object Array of medical history records
     */
    public function saveVisitsChangesIn($insertData){
        $error = false;
        $insertDataArr = [];
        foreach ($insertData as $value) {
            if(!empty($value['fector_value'])){
                $whereCheck = ['fector_id' => $value['fector_id'], 'pat_id' => $value['pat_id'],'visit_id' => $value['visit_id']];
                $checkDataExist = $this->checkIfRecordExist($this->tableVisitsChangesIn, 'vc_id', $whereCheck);

                if($checkDataExist){
                    // Update
                    $response = $this->dbUpdate($this->tableVisitsChangesIn, $value, $whereCheck);
                    
                    if(!$response){
                        $error = true;
                    }               
                }else{
                    // insert
                    $insertDataArr[] = $value;
                } 

            }
        }

        if(!empty($insertDataArr)){
            if(!$this->dbBatchInsert($this->tableVisitsChangesIn, $insertDataArr)){
                $error = true;
            }
        }

        if(!$error){
            return true;
        }
        return false;
    }
    
    /**
     * @DateOfCreation        10 July 2018
     * @ShortDescription      This function is responsible to save / update Hospitalizations Extra Info
     * @param                 array $insertData   
     * @return                object Array of medical history records
     */
    public function saveHospitalizationsExtraInfo($insertData){
        $error = false;
        $insertDataArr = [];
        foreach ($insertData as $value) {
            if(!empty($value['hospitalization_duration_unit']) || !empty($value['hospitalization_diagnosis_details']) || !empty($value['hospitalization_date']) || !empty($value['hospitalization_duration'])){

                $whereCheck = ['hospitalization_fector_id' => $value['hospitalization_fector_id'], 'pat_id' => $value['pat_id'],'visit_id' => $value['visit_id']];
                $checkDataExist = $this->checkIfRecordExist($this->tableHospitalizationExtraInfo, 'hei_id', $whereCheck);

                $value['hospitalization_date'] = !empty($value['hospitalization_date']) ? $this->dateTimeLibObj->covertUserDateToServerType($value['hospitalization_date'],'dd/mm/YY','Y-m-d')['result'] : NULL;
                if($checkDataExist){
                    // Update
                    $response = $this->dbUpdate($this->tableHospitalizationExtraInfo, $value, $whereCheck);
                    
                    if(!$response){
                        $error = true;
                    }
                }else{
                    // insert
                    $insertDataArr[] = $value;
                }

            }
        }

        if(!empty($insertDataArr)){
            if(!$this->dbBatchInsert($this->tableHospitalizationExtraInfo, $insertDataArr)){
                $error = true;
            }
        }
        
        if(!$error){
            return true;
        }
        return false;
    }
    
    /**
     * @DateOfCreation        10 July 2018
     * @ShortDescription      This function is responsible to save / update Treatments Data
     * @param                 array $insertData   
     * @return                object Array of medical history records
     */
    public function saveTreatments($insertData){
        $error = false;
        $insertDataArr = [];
        foreach ($insertData as $value) {
            
            if(!empty($value['treatment_start_date']) || !empty($value['treatment_end_date'])){

                $whereCheck = ['medicine_id' => $value['medicine_id'], 'pat_id' => $value['pat_id'],'visit_id' => $value['visit_id']];
                $checkDataExist = $this->checkIfRecordExist($this->tableTreatments, 'treatment_id', $whereCheck);

                if($checkDataExist){
                    // Update
                    $response = $this->dbUpdate($this->tableTreatments, $value, $whereCheck);
                    
                    if(!$response){
                        $error = true;
                    }
                }else{
                    // insert
                    $insertDataArr[] = $value;
                }

            }
        }

        if(!empty($insertDataArr)){
            if(!$this->dbBatchInsert($this->tableTreatments, $insertDataArr)){
                $error = true;
            }
        }

        if(!$error){
            return true;
        }
        return false;
    }
        
    /**
     * @DateOfCreation        10 July 2018
     * @ShortDescription      This function is responsible to save / update Patient Sixmwt data
     * @param                 array $insertData   
     * @return                object Array of medical history records
     */
    public function savePatientSixmwt($patientSixmwtsData, $sixmwtFectorsData){
        $error = false;
        $insertDataArr = [];

        // Check if patient Sixmwt record exist 
        $whereCheck     = ['pat_id' => $patientSixmwtsData['pat_id'],'visit_id' => $patientSixmwtsData['visit_id']];
        $checkDataExist = $this->checkIfRecordExist($this->tablePatientSixmwts, 'sixmwt_id', $whereCheck, 'get_record');

        $sixmwtId = False;
        $checkSixmwtId= json_decode(json_encode($checkDataExist),TRUE);
        if(empty($checkSixmwtId)){
            // insert
            $patientSixmwtsData['sixmwt_date']      = !empty($patientSixmwtsData['sixmwt_date']) ? $this->dateTimeLibObj->covertUserDateToServerType($patientSixmwtsData['sixmwt_date'],'dd/mm/YY','Y-m-d')['result'] : NULL;
            $response = $this->dbInsert($this->tablePatientSixmwts, $patientSixmwtsData);

            if($response){
                $sixmwtId = DB::getPdo()->lastInsertId();                
            } else {
                $error = true;
                return $response;
            }
        }
        else{
            $sixmwtId = $checkSixmwtId[0]['sixmwt_id']; 

            $patientSixmwtsData['sixmwt_date'] = !empty($patientSixmwtsData['sixmwt_date']) ? $this->dateTimeLibObj->covertUserDateToServerType($patientSixmwtsData['sixmwt_date'],'dd/mm/YY','Y-m-d')['result'] : NULL;
            $response = $this->dbUpdate($this->tablePatientSixmwts, $patientSixmwtsData, ['sixmwt_id' => $sixmwtId]);

            if(!$response){
                $error = true;
            }
        }
        
        if($sixmwtId){
            foreach ($sixmwtFectorsData as $value) {
                if(!empty($value['before_sixmwt']) || !empty($value['after_sixmwt'])){

                    $whereCheck     = ['sixmwt_id' => $sixmwtId,'fector_id' => $value['fector_id'], 'fector_type' => $value['fector_type']];
                    $checkDataExist = $this->checkIfRecordExist($this->tablePatientSixmwtFectors, 'sixmwt_fector_id', $whereCheck);

                    unset($value['visit_id']);
                    unset($value['pat_id']);
                    if($checkDataExist){
                        // Update
                        $response = $this->dbUpdate($this->tablePatientSixmwtFectors, $value, $whereCheck);
                        
                        if(!$response){
                            $error = true;
                        }
                    }else{
                        // insert
                        $value['sixmwt_id'] = $sixmwtId;
                        $insertDataArr[] = $value;
                    }
                }
            }            
        }

        if(!empty($insertDataArr)){
            if(!$this->dbBatchInsert($this->tablePatientSixmwtFectors, $insertDataArr)){
                $error = true;
            }
        }

        if(!$error){
            return true;
        }
        return false;
    }
    
    /**
     * @DateOfCreation        10 July 2018
     * @ShortDescription      This function is responsible to save / update patient spirometry data
     * @param                 array $insertData   
     * @return                object Array of medical history records
     */
    public function savePatientSpirometry($patientSpirometryData, $spirometryFectorsData){
        $error = false;
        $insertDataArr = [];

        $whereCheck     = ['pat_id' => $patientSpirometryData['pat_id'],'visit_id' => $patientSpirometryData['visit_id']];
        $checkDataExist = $this->checkIfRecordExist($this->tablePatientSpirometries, 'spirometry_id', $whereCheck, 'get_record');

        $spirometryId = False;
        $checkSpirometryId= json_decode(json_encode($checkDataExist),TRUE);
        if(empty($checkSpirometryId)){
            // insert
            $patientSpirometryData['spirometry_date'] = !empty($patientSpirometryData['spirometry_date']) ? $this->dateTimeLibObj->covertUserDateToServerType($patientSpirometryData['spirometry_date'],'dd/mm/YY','Y-m-d')['result'] : NULL;
            $response = $this->dbInsert($this->tablePatientSpirometries, $patientSpirometryData);

            if($response){
                $spirometryId = DB::getPdo()->lastInsertId();                
            } else {
                $error = true;
                return $response;
            }
        }
        else{
            $spirometryId = $checkSpirometryId[0]['spirometry_id']; 
            
            $patientSpirometryData['spirometry_date'] = !empty($patientSpirometryData['spirometry_date']) ? $this->dateTimeLibObj->covertUserDateToServerType($patientSpirometryData['spirometry_date'],'dd/mm/YY','Y-m-d')['result'] : NULL;
            $response = $this->dbUpdate($this->tablePatientSpirometries, $patientSpirometryData, ['spirometry_id' => $spirometryId]);

            if(!$response){
                $error = true;
            }
        }

        if($spirometryId){
            foreach ($spirometryFectorsData as $value) {
                if(!empty($value['fector_pre_value']) || !empty($value['fector_post_value'])){
                    // $whereCheck     = ['spirometry_id' => $spirometryId,'fector_id' => $value['fector_id'], 'fector_pre_value' => $value['fector_pre_value'], 'fector_post_value' => $value['fector_post_value']];
                    $whereCheck     = ['spirometry_id' => $spirometryId, 'fector_id' => $value['fector_id']];
                    $checkDataExist = $this->checkIfRecordExist($this->tablePatientSpirometryFectors, 'sf_id', $whereCheck);

                    if($checkDataExist){
                        // Update
                        $response = $this->dbUpdate($this->tablePatientSpirometryFectors, $value, $whereCheck);
                        if(!$response){
                            $error = true;
                        }
                    }else{
                        // insert
                        $value['spirometry_id'] = $spirometryId;
                        $insertDataArr[] = $value;
                    }                        
                }
            }            
        }

        if(!empty($insertDataArr)){
            if(!$this->dbBatchInsert($this->tablePatientSpirometryFectors, $insertDataArr)){
                $error = true;
            }
        }
        
        if(!$error){
            return true;
        }
        return false;
    }

    /**
     * @DateOfCreation        10 July 2018
     * @ShortDescription      This function is responsible to get the patient Treatment prescribed records
     * @param                 integer $visitId   
     * @return                object Array of medical history records
     */
    public function getVisitTreatmentFectors($visitId) 
    {   
        $queryResult = DB::table( $this->tableMedicines )
                        ->select( $this->tableMedicines.'.medicine_name',
                                $this->tableMedicines.'.medicine_id',
                                $this->tableTreatments.'.treatment_id', 
                                $this->tableTreatments.'.pat_id', 
                                $this->tableTreatments.'.visit_id',
                                $this->tableTreatments.'.treatment_start_date', 
                                $this->tableTreatments.'.treatment_end_date'
                            ) 
                        ->leftJoin($this->tableTreatments,function($join) use($visitId){
                                $join->on($this->tableTreatments.'.medicine_id', '=', $this->tableMedicines.'.medicine_id')
                                    ->where($this->tableTreatments.'.visit_id', '=', $visitId, 'and')
                                    ->where($this->tableTreatments.'.is_deleted', '=', Config::get('constants.IS_DELETED_NO'), 'and');
                            })
                        ->where( $this->tableMedicines.'.is_deleted',  Config::get('constants.IS_DELETED_NO') )
                        ->where( $this->tableMedicines.'.show_in',  1 );
               
        $queryResult = $queryResult->get()
                                    ->map(function($treatmentFectors){
                                        if(!empty($treatmentFectors->treatment_id)){
                                            $treatmentFectors->treatment_id         = $this->securityLibObj->encrypt($treatmentFectors->treatment_id);                                            
                                            $treatmentFectors->pat_id               = $this->securityLibObj->encrypt($treatmentFectors->pat_id);                                            
                                            $treatmentFectors->visit_id             = $this->securityLibObj->encrypt($treatmentFectors->visit_id);                                            
                                            $treatmentFectors->treatment_start_date = !empty($treatmentFectors->treatment_start_date) ? $treatmentFectors->treatment_start_date : '';
                                            $treatmentFectors->treatment_end_date   = !empty($treatmentFectors->treatment_end_date) ? $treatmentFectors->treatment_end_date : '';
                                        }
                                        $treatmentFectors->medicine_id = $this->securityLibObj->encrypt($treatmentFectors->medicine_id);                                            
                                        return $treatmentFectors;
                                    });
        return $queryResult;
    }

    /**
     * @DateOfCreation        9 july 2018
     * @ShortDescription      This function is responsible to check if record is exist or not
     * @param                 integer $patId   
     * @return                object Array of symptoms records
     */
    public function checkIfRecordExist($tableName, $select, $where, $type='count') 
    {
        // DB::enableQueryLog();
        $queryResult = DB::table($tableName)
            ->select( $select ) 
            ->where('is_deleted', Config::get('constants.IS_DELETED_NO'))
            ->where($where);
        
        if($type == 'count'){
            return $queryResult->get()->count();            
        } else {
            return $queryResult->get();            
        }
        // dd(DB::getQueryLog());
    }

    /**
     * @DateOfCreation        14 july 2018
     * @ShortDescription      This function is responsible to get visit list by patient id
     * @param                 integer $patId   
     * @return                object Array of symptoms records
     */
    public function getPatientVisits($requestData){
        $patId  = $this->securityLibObj->decrypt($requestData['patId']);
        $userId = $this->securityLibObj->decrypt($requestData['user_id']);

        $query = DB::table($this->tablePatientsVisits)
                            ->select(
                                    $this->tablePatientsVisits.'.status', 
                                    $this->tablePatientsVisits.'.visit_type', 
                                    $this->tablePatientsVisits.'.visit_symptom_status', 
                                    $this->tablePatientsVisits.'.visit_followup_status', 
                                    $this->tablePatientsVisits.'.visit_followed_elsewhere', 
                                    $this->tablePatientsVisits.'.user_id',
                                    $this->tablePatientsVisits.'.created_at',
                                    $this->tablePatientsVisits.'.visit_id',
                                    $this->tablePatientsVisits.'.pat_id',
                                    $this->tablePatientsVisits.'.visit_number',
                                    DB::raw("CONCAT(".$this->tableUsers.".user_firstname,' ',".$this->tableUsers.".user_lastname) AS doctor_name")
                                )
                            ->join($this->tableUsers, function($join) {
                                $join->on($this->tablePatientsVisits.'.user_id', '=', $this->tableUsers.'.user_id')
                                    ->where($this->tableUsers.'.is_deleted', '=', Config::get('constants.IS_DELETED_NO'), 'and');
                            })
                            ->where($this->tablePatientsVisits.'.is_deleted', Config::get('constants.IS_DELETED_NO'))
                            ->where($this->tablePatientsVisits.'.visit_type', '!=', Config::get('constants.PROFILE_VISIT_TYPE'))
                            ->where($this->tablePatientsVisits.'.pat_id', $patId);
        
         /* Condition for Filtering the result */
        if(!empty($requestData['filtered'])){
            
            foreach ($requestData['filtered'] as $key => $value) {

                $whereVisitNumber = $value['value'];
                if(stripos($value['value'], 'initial') !== false)
                {
                    $whereVisitNumber = 1;
                } else if( stripos($value['value'], 'followup' !== false) ) {
                    $whereVisitNumber = $value['value'];                    
                } 

                $query = $query->where(function ($query) use ($value, $whereVisitNumber){
                                $query
                                ->where(DB::raw('CAST('.$this->tablePatientsVisits.'.visit_number AS TEXT)'), 'ilike', '%'.$whereVisitNumber.'%')
                                ->orWhere(DB::raw('CAST('.$this->tablePatientsVisits.'.created_at AS TEXT)'), 'ilike', '%'.$value['value'].'%')
                                ->orWhere(DB::raw('CAST('.$this->tableUsers.'.user_firstname AS TEXT)'), 'ilike', '%'.$value['value'].'%')
                                ->orWhere(DB::raw('CAST('.$this->tableUsers.'.user_lastname AS TEXT)'), 'ilike', '%'.$value['value'].'%');
                            });
            }
        }

        /* Condition for Sorting the result */
        if(!empty($requestData['sorted'])){
            foreach ($requestData['sorted'] as $key => $value) {

                if($value['id'] == 'visit_status_label'){
                    $value['id'] = $this->tablePatientsVisits.'.status';
                }

                if($value['id'] == 'doctor_name'){
                    $value['id'] = $this->tableUsers.'.user_firstname';
                }

                $orderBy = $value['desc'] ? 'desc' : 'asc';
                $query = $query->orderBy($value['id'], $orderBy);
            }
        }else{
            $query = $query->orderBy($this->tablePatientsVisits.'.visit_number', 'asc');
        }
        if($requestData['page'] > 0){
            $offset = $requestData['page']*$requestData['pageSize'];
        }else{
            $offset = 0;
        }
        $queryResult['pages'] = ceil($query->count()/$requestData['pageSize']);

        $queryResult['result'] = 
            $query->offset($offset)
            ->limit($requestData['pageSize'])
            ->get()
            ->map(function($patientVisits){
                $patientVisits->symptom_list            = $this->getVisitSymptomsByVisitId($patientVisits->visit_id);
                $patientVisits->fvc_data                = $this->getSpirometryFectorByVistId($patientVisits->visit_id, Config::get('dataconstants.SPIROMETRY_FVC_FACTOR_ID'));
                $patientVisits->dlco_data               = $this->getSpirometryFectorByVistId($patientVisits->visit_id, Config::get('dataconstants.SPIROMETRY_DLCO_FACTOR_ID'));
                $patientVisits->date_of_hospitalization = $this->getHospitalizationDetailsByVisitId($patientVisits->visit_id);
                $patientVisits->visit_id                = $this->securityLibObj->encrypt($patientVisits->visit_id);
                $patientVisits->pat_id                  = $this->securityLibObj->encrypt($patientVisits->pat_id);
                $patientVisits->user_id                 = $this->securityLibObj->encrypt($patientVisits->user_id);
                $patientVisits->visit_number            = $patientVisits->visit_type == Config::get('constants.INITIAL_VISIT_TYPE') ? trans('Visits::messages.initial_visit') : trans('Visits::messages.followup_visit').' '.$patientVisits->visit_number;

                $patientVisits->visit_status_label      = $patientVisits->status == Config::get('dataconstants.VISIT_STATUS_IN_PROGRESS') ? trans('Visits::messages.in_progress') : ($patientVisits->status == Config::get('dataconstants.VISIT_STATUS_CANCEL') ? trans('Visits::messages.cancel') : trans('Visits::messages.finished'));
                $patientVisits->created_at              = date('d/m/Y H:i A', strtotime($patientVisits->created_at));
                return $patientVisits;
            });

        return $queryResult;        
    }

    /**
     * @DateOfCreation        12 July 2018
     * @ShortDescription      This function is responsible to get the Visit data by visit id
     * @param                 integer $visitId,$patientId, $encrypt   
     * @return                object Array of Physical Examinations records
     */
    public function getVisitDetailsByVistID($visitId, $patientId = '') 
    {
        $selectData = ['visit_id','pat_id', 'visit_symptom_status', 'visit_followup_status', 'visit_followed_elsewhere', 'visit_number', 'visit_suspect_active_infection'];
        $whereData  = ['visit_id'=> $visitId,'is_deleted'=>  Config::get('constants.IS_DELETED_NO')];
        if(!empty($patientId)){
            $whereData ['pat_id'] = $patientId;
        }
        $queryResult = $this->dbBatchSelect($this->tablePatientsVisits, $selectData, $whereData);
        if(!empty($queryResult)){
            $queryResult = $queryResult->map(function($dataList){ 
                $dataList->pat_id = $this->securityLibObj->encrypt($dataList->pat_id);
                $dataList->visit_id = $this->securityLibObj->encrypt($dataList->visit_id);
                return $dataList;
            });
        }
        return $queryResult;        
    }

    public function getVisitAndPatientInfo($visitId, $patientId){
        
        $whereData = ['visit_id'=> $visitId,'is_deleted'=>  Config::get('constants.IS_DELETED_NO')];
        $patientVisitData   = $this->dbSelect($this->tablePatientsVisits, ['visit_number', 'created_at'], $whereData);
        
        if(!empty($patientVisitData) && $patientVisitData->visit_number > 0){
            $patientVisitData->created_at   = !empty($patientVisitData->created_at) ? date('d M Y', strtotime($patientVisitData->created_at)) : '';
        }
        $patientProfileData = $this->patientModelObj->getPatientProfileData($patientId);

        return (object) array_merge((array) $patientVisitData, (array) $patientProfileData);
    }

    /**
     * @DateOfCreation        16 July 2018
     * @ShortDescription      This function is responsible to get the Symptoms name by visit id
     * @param                 integer $visitId 
     * @return                string of symptoms name
     */
    private function getVisitSymptomsByVisitId($visitId){
        $visitSymptomNames = $this->symptomsModelObj->getVisitSymptomsByVisitId($visitId);

        if(!empty($visitSymptomNames)){
            $symptomNameArray = array_column(json_decode(json_encode($visitSymptomNames), true), 'symptom_name');
            
            return implode(', ', $symptomNameArray);
        }

        return NULL;
    }

    /**
     * @DateOfCreation        16 July 2018
     * @ShortDescription      This function is responsible to get the SPIROMETRY factor value
     * @param                 integer $visitId 
     * @return                string of factor value
     */
    private function getSpirometryFectorByVistId($visitId, $fectorId){
        $fectorIds = [Config::get('dataconstants.SPIROMETRY_FVC_FACTOR_ID'), Config::get('dataconstants.SPIROMETRY_DLCO_FACTOR_ID')];

        $spirometriesFectorData = $this->spirometriesObj->getSpirometryFectorByVistIdAndFectorId($visitId, $fectorIds);

        if(!empty($spirometriesFectorData)){
            $spirometriesFectorData = $this->utilityLibObj->changeArrayKey($spirometriesFectorData,'fector_id');

            if(isset($spirometriesFectorData[$fectorId]))
                return $spirometriesFectorData[$fectorId]['fector_pre_value'];
        }
        return NULL;
    }

    /**
     * @DateOfCreation        16 July 2018
     * @ShortDescription      This function is responsible to get the Symptoms name by visit id
     * @param                 integer $visitId 
     * @return                string of symptoms name
     */
    private function getHospitalizationDetailsByVisitId($visitId){
        $data = $this->hospitalizationsObj->getPatientHospitalizationsInfo($visitId);

        if(!empty($data) && count($data) > 0){
            return !empty($data[0]->date_of_hospitalization) ? date('d/m/Y', strtotime($data[0]->date_of_hospitalization)) : NULL;
        }

        return NULL;
    }

    /**
    * @DateOfCreation        18 July 2018
    * @ShortDescription      This function is responsible to update Patient visit Record
    * @param                 Array  $requestData   
    * @return                Array of status and message
    */
    public function updateVisitInfo($updateData, $whereData)
    {
        if(!empty($whereData)){
            $response   = $this->dbUpdate($this->tablePatientsVisits, $updateData, $whereData);
            if($response){
                return true;
            }
        }
        return false;
    }

    /**
     * @DateOfCreation        2 Aug 2018
     * @ShortDescription      This function is responsible to create a first visit for the booking
     * @param                 $tablename - insertion table name 
     * @param                 Array $insertData 
     * @return                String {visit id}
     */
    public function createPatientDoctorVisit($tablename, $insertData){
        // @var Boolean $response
        // This variable contains insert query response
        $response = false;
        
        $response = $this->dbInsert($tablename, $insertData);
        if($response){
            $relId = DB::getPdo()->lastInsertId();
            return $relId;
        }else{
            return $response;
        }     
    
    }
}
