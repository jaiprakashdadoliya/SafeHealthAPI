<?php
namespace App\Modules\Visits\Models;
use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\HasApiTokens;
use App\Traits\Encryptable;
use Config;
use DB;
use Carbon\Carbon;
use App\Libraries\SecurityLib;
use App\Libraries\UtilityLib;
use App\Libraries\DateTimeLib;
use App\Modules\Setup\Models\StaticDataConfig as StaticData;
use App\Modules\Visits\Models\Vitals;
use App\Modules\Visits\Models\ClinicalNotes;
use App\Modules\Visits\Models\Medication;
use App\Modules\Visits\Models\Symptoms;
use App\Modules\Visits\Models\Diagnosis;
use App\Modules\Visits\Models\PhysicalExaminations;
use App\Modules\Visits\Models\LaboratoryReport;


/**
 * Prescription
 *
 * @package                Safe Health
 * @subpackage             Prescription
 * @category               Model
 * @DateOfCreation         23 Aug 2018
 * @ShortDescription       This Model to handle database operation of Visit Prescription
 **/

class Prescription extends Model {

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

        // Init staticData Model Object
        $this->staticDataModelObj = new StaticData();

        // Init Vitals model object
        $this->vitalsObj = new Vitals();

        // Init ClinicalNotes model object
        $this->clinicalNotesModelObj = new ClinicalNotes();

        // Init Medication model object
        $this->medicationModelObj = new Medication();

        // Init Symptoms model object
        $this->symptomsModelObj = new Symptoms();

        // Init Symptoms model object
        $this->diagnosisModelObj = new Diagnosis();

        // Init Lab report Model object
        $this->laboratoryReportModelObj = new LaboratoryReport();

        // Init PhysicalExaminations model object
        $this->physicalExaminationsObj = new PhysicalExaminations(); 
    }

    /**
    *@ShortDescription Table for the Users.
    *
    * @var String
    */
    protected $tableVitals = 'vitals';
    protected $tablePatientVisit = 'patients_visits';
    protected $tablePatients     = 'patients';
    protected $tableUsers        = 'users';
    protected $tableDoctors      = 'doctors';
    
    // This protected member contains fields that need to encrypt while saving in database
    protected $encryptable = [];
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [];

    /**
     *@ShortDescription Override the primary key.
     *
     * @var string
     */
    protected $primaryKey = '';

    /**
     * @DateOfCreation        23 Aug 2018
     * @ShortDescription      This function is responsible to get the prescription data
     * @param                 integer $visitId, $patientId, $encrypt   
     * @return                object Array of Physical Vitals records
     */
    public function generatePrescriptionPdf($userId, $visitId, $isPrintSymptom = 0, $isPrintDiagnosis = 0, $isPrintLabTest = 0)
    {
        // GET PATIENT Info
        $getPatientInfo = $this->dbSelect($this->tableUsers, ['user_firstname as patient_firstname', 'user_lastname as patient_lastname', 'user_mobile as patient_mobile', 'user_gender'], ['user_id' => $userId]);
        $getGender      =  $this->utilityLibObj->changeArrayKey($this->staticDataModelObj->getGenderData(), 'id');
        $getPatientInfo->user_gender = $getGender[$getPatientInfo->user_gender]['value'];
        
        // GET DOCTOR info, patient basic info and visit info
        $queryResult = DB::table( $this->tablePatientVisit )
                        ->select( $this->tablePatientVisit.'.status',
                                $this->tablePatientVisit.'.visit_type',
                                $this->tablePatientVisit.'.pat_id', 
                                $this->tablePatientVisit.'.user_id', 
                                $this->tablePatientVisit.'.created_at', 
                                $this->tablePatients.'.pat_code',
                                $this->tablePatients.'.pat_title',
                                $this->tablePatients.'.pat_blood_group',
                                $this->tablePatients.'.pat_phone_num',
                                $this->tablePatients.'.pat_dob',
                                $this->tablePatients.'.pat_code',
                                $this->tableDoctors.'.doc_registration_number',
                                $this->tableUsers.'.user_firstname as doctor_firstname',
                                $this->tableUsers.'.user_lastname as doctor_lastname',
                                $this->tableUsers.'.user_mobile as doctor_mobile'
                            ) 
                        ->join($this->tablePatients,function($join){
                                $join->on($this->tablePatients.'.user_id', '=', $this->tablePatientVisit.'.pat_id')
                                    ->where($this->tablePatients.'.is_deleted', '=', Config::get('constants.IS_DELETED_NO'), 'and');
                            })
                        ->join($this->tableDoctors,function($join){
                                $join->on($this->tablePatientVisit.'.user_id', '=', $this->tableDoctors.'.user_id')
                                    ->where($this->tablePatientVisit.'.is_deleted', '=', Config::get('constants.IS_DELETED_NO'), 'and');
                            })
                        ->join($this->tableUsers,function($join){
                                $join->on($this->tableUsers.'.user_id', '=', $this->tableDoctors.'.user_id')
                                    ->where($this->tableUsers.'.is_deleted', '=', Config::get('constants.IS_DELETED_NO'), 'and');
                            })
                        ->where( $this->tablePatientVisit.'.is_deleted',  Config::get('constants.IS_DELETED_NO') )
                        ->where( $this->tablePatientVisit.'.visit_id',  $visitId );
        $visitInfo = $queryResult->get()
                                ->map(function($visitInfo){
                                    $visitInfo->pat_dob = !empty($visitInfo->pat_dob) ? Carbon::parse($visitInfo->pat_dob)->age.' Year' : '';
                                    $visitInfo->created_at = !empty($visitInfo->created_at) ? Carbon::parse($visitInfo->created_at)->format('d M, Y') : Carbon::parse(Carbon::today())->format('d M, Y');
                                    return $visitInfo;
                                });
        $visitInfo = !empty($visitInfo) ? json_decode(json_encode($visitInfo[0]), true) : $visitInfo;
        
        // GET VITALS
        $vitalsArr = [];
        $getVitalsWeightFormData = $this->staticDataModelObj->getStaticDataFunction(['getWeight']);
        $weightData = !empty($visitId) ? $this->physicalExaminationsObj->getPhysicalExaminationsByVistID($visitId, $userId,true) : [];
        $weightData = !empty($weightData) ? $this->utilityLibObj->changeArrayKey($weightData,'fector_id') : [];
        $encryptedFactorId = $this->securityLibObj->encrypt(Config::get('dataconstants.VISIT_PHYSICAL_WEIGHT'));
        $weightVitals = 0;
        if(!empty($weightData)){
            $weightVitals = $weightData[$encryptedFactorId]['fector_value'];
        }
        $vitalsArr[] = ['value' => $weightVitals, 'label' => 'Weight', 'unit' => 'kg'];

        $getVitalsFormData = $this->staticDataModelObj->getStaticDataFunction(['vitalsFectorData']);
        $formValuData = !empty($visitId) ? $this->vitalsObj->getPatientVitalsInfo($visitId, $userId, true) : [];
        $formValuData = !empty($formValuData) ? $this->utilityLibObj->changeArrayKey($formValuData,'fector_id') : [];
        foreach ($getVitalsFormData as $key => $value) {
            $encryptFactorId = $this->securityLibObj->encrypt($value['id']);
            $factorValue = 0;
            if(array_key_exists($encryptFactorId, $formValuData)){
                $factorValue = $getVitalsFormData[$key]['value'] = $formValuData[$encryptFactorId]['fector_value'];
            }
            $vitalsArr[] = ['value' => $factorValue, 'label' => $value['lable'], 'unit' => $value['unit']];
        }

        // GET CLINICAL NOTES
        $encryptedPatientId = $this->securityLibObj->encrypt($userId);
        $encryptedVisitId   = $this->securityLibObj->encrypt($visitId);
        $clinicalNotesData  = $this->clinicalNotesModelObj->getClinicalNotesListData(['pat_id' => $encryptedPatientId, 'visit_id' => $encryptedVisitId]);
        $clinicalNotesData  = !empty($clinicalNotesData) ? $clinicalNotesData->clinical_notes : [];
        
        // GET PRESCRIBED MEDICINE
        $prescribedMedicines = $this->medicationModelObj->getPatientMedicationData($visitId, $userId);
        $prescribedMedicines = !empty($prescribedMedicines) ? json_decode(json_encode($prescribedMedicines), true) : $prescribedMedicines;

        // GET PATIENT SYMPTOMS
        $symptomData = [];
        if($isPrintSymptom == 1){
            $whereData = ['patId' => $encryptedPatientId, 'visitId' => $encryptedVisitId, 'filtered' => [], 'sorted' => '', 'page' => 0, 'pageSize' => -1];
            $symptomData = $this->symptomsModelObj->getSymptomsDataByPatientIdAndVistId($whereData);
            $symptomData = !empty($symptomData['result']) ? json_decode(json_encode($symptomData['result']), true) : $symptomData;
        }

        // GET PATIENT DIAGNOSIS
        $patientDiagnosis = [];
        if($isPrintDiagnosis == 1){
            $whereData = ['patId' => $encryptedPatientId, 'visit_id' => $visitId, 'filtered' => [], 'sorted' => '', 'page' => 0, 'pageSize' => -1];
            $patientDiagnosis = $this->diagnosisModelObj->getPatientDiagnosisHistoryList($whereData);
            $patientDiagnosis = !empty($patientDiagnosis['result']) ? json_decode(json_encode($patientDiagnosis['result']), true) : $patientDiagnosis;            
        }

        $patientLabTest = [];
        if($isPrintLabTest == 1){
            $whereData = ['patId' => $encryptedPatientId, 'visitId' => $encryptedVisitId, 'filtered' => [], 'sorted' => '', 'page' => 0, 'pageSize' => -1];
            $patientLabTest = $this->laboratoryReportModelObj->getListData($whereData);
            $patientLabTest = !empty($patientLabTest['result']) ? json_decode(json_encode($patientLabTest['result']), true) : $patientLabTest;            
        }
        
        return ['vital' => $vitalsArr, 'clinical_notes' => $clinicalNotesData, 'medicines' => $prescribedMedicines, 'patient_info' => $getPatientInfo, 'visit_info' => $visitInfo, 'symptom_data' => $symptomData, 'diagnosis_data' => $patientDiagnosis, 'labtest_data' => $patientLabTest];
    }
    
}
