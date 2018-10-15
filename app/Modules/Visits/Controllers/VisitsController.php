<?php

namespace App\Modules\Visits\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Auth;
use Session;
use App\Traits\SessionTrait;
use App\Traits\RestApi;
use App\Traits\FxFormHandler;
use Config;
use Illuminate\Support\Facades\Validator;
use App\Libraries\SecurityLib;
use App\Libraries\ExceptionLib;
use DB;
use App\Libraries\FileLib;
use App\Libraries\UtilityLib;
use App\Libraries\DateTimeLib;
use File;
use App\Modules\Visits\Models\Visits;
use App\Modules\Visits\Models\Diagnosis;
use App\Modules\Visits\Models\Disease;
use App\Modules\Setup\Models\StaticDataConfig as StaticData;
use App\Modules\Visits\Models\PhysicalExaminations;
use App\Modules\Visits\Models\Spirometry;
use App\Modules\Visits\Models\Sixmwt;
use App\Modules\Visits\Models\InvestigationAbg;
use App\Modules\Visits\Models\Investigation;
use App\Modules\Visits\Models\ThoracoscopicLung;
use App\Modules\Visits\Models\PatientDeathInfo;
use App\Modules\Visits\Models\Hospitalizations;
use App\Modules\Visits\Models\HospitalizationsExtraInfo;
use App\Modules\Visits\Models\VisitChangesIn;
use App\Modules\Visits\Models\Vitals;
use App\Modules\Visits\Models\Spirometries;
use App\Modules\Visits\Models\PatientSixmwts;
use App\Modules\Visits\Models\TreatmentRequirement;
use App\Modules\Visits\Models\SurgicalLungBiopsy;
use App\Modules\Visits\Models\ChestXray;
use App\Modules\Visits\Models\HRCT;
use App\Modules\Visits\Models\UIP;
use App\Modules\Visits\Models\FiberopticBronchoscopy;
use App\Modules\Visits\Models\PulmonaryFunctionTest;
use App\Modules\Bookings\Models\Bookings;
use App\Modules\Search\Models\Search;
use App\Modules\Visits\Models\SleepStudy;
use App\Modules\Visits\Models\InvestigationReport;

class VisitsController extends Controller
{
    use SessionTrait, RestApi, FxFormHandler;

    // @var Array $http_codes
    // This protected member contains Http Status Codes
    protected $http_codes = [];

    // Store Post Method
    protected $method = '';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(Request $request)
    {
        $this->http_codes = $this->http_status_codes();

        // Init security library object
        $this->securityLibObj = new SecurityLib();

        // Init Utility Library object
        $this->utilityLibObj = new UtilityLib();

        $this->method = $request->method();

        // Init exception library object
        $this->exceptionLibObj = new ExceptionLib();

        // Init dateTime library object
        $this->dateTimeLibObj = new DateTimeLib();     
        
        // Init Patient model object
        $this->visitModelObj = new Visits(); 

        // Init General staticData Model Object
        $this->staticDataObj = new StaticData();

        // Init General Diagnosis Model Object
        $this->diagnosisObj = new Diagnosis();

        // Init General disease Data Model Object
        $this->diseaseObj = new Disease();

        // Init PhysicalExaminations model object
        $this->physicalExaminationsObj = new PhysicalExaminations();

        // Init Spirometry model object
        $this->spirometryObj = new Spirometry();

        // Init Sixmwt model object
        $this->sixmwtObj = new Sixmwt();

        // Init InvestigationAbg model object
        $this->investigationAbgObj = new InvestigationAbg();

        // Init ThoracoscopicLung model object
        $this->thoracoscopicLungObj = new ThoracoscopicLung();

        // Init PatientDeathInfo model object
        $this->patientDeathInfoObj = new PatientDeathInfo();

        // Init PatientDeathInfo model object
        $this->hospitalizationsObj = new Hospitalizations();
        
        // Init Hospitalizations Extra Info model object
        $this->hospitalizationsExtraInfoObj = new HospitalizationsExtraInfo();

        // Init Visit changes in model object
        $this->visitChangesInObj = new VisitChangesIn();

        // Init Vitals model object
        $this->vitalsObj = new Vitals();

        // Init investigation model object
        $this->investigationObj = new Investigation();

        // Init Spirometries model object
        $this->spirometriesObj = new Spirometries();

        // Init PatientSixmwts model object
        $this->patientSixmwtsObj = new PatientSixmwts();

        // Init Treatment Requirement model object
        $this->treatmentRequirementObj = new TreatmentRequirement();

        // Init SurgicalLungBiopsy model object
        $this->surgicalLungBiopsyObj = new SurgicalLungBiopsy(); 

        // Init ChestXray model object
        $this->chestXrayObj = new ChestXray();

        // Init HRCT model object
        $this->hrctObj = new HRCT();

        // Init HRCT model object
        $this->uipObj = new UIP();

        // Init HRCT model object
        $this->fiberopticBronchoscopyObj = new FiberopticBronchoscopy();

        // Init Pulmonary Function model object
        $this->pulmonaryFunctionTestObj = new PulmonaryFunctionTest();

        //Init Bookings model object
        $this->bookingsObj = new Bookings();

        //Init search model object
        $this->searchModelObj = new Search();

        //Init Sleep Study model object
        $this->sleepStudyModelObj = new SleepStudy();

        //Init Investigation Report model object
        $this->investigationReportModelObj = new InvestigationReport();
    }

    public function getVisitComponents(Request $request)
    {
        $requestData = $this->getRequestData($request);
        if($request->user()->user_type == Config::get('constants.USER_TYPE_PATIENT')){
            $requestData['userId']   = $this->visitModelObj->getCurrentVisitDoctorId($request->user()->user_id, $requestData['visitId']);
        }else{
            $requestData['userId']   = (in_array($request->user()->user_type, Config::get('constants.USER_TYPE_STAFF'))) ? $request->user()->created_by : $request->user()->user_id;
        }
        $visitComponents = $this->visitModelObj->getVisitComponents($requestData);
        if($visitComponents){
            return $this->resultResponse(
                Config::get('restresponsecode.SUCCESS'), 
                $visitComponents, 
                [],
                trans('Visits::messages.component_data_fetch_successfully'), 
                $this->http_codes['HTTP_OK']
            );
        }else{
            return $this->resultResponse(
                Config::get('restresponsecode.ERROR'), 
                [], 
                [],
                trans('Visits::messages.component_data_fetch_fail'), 
                $this->http_codes['HTTP_OK']
            );
        }          
    }

    public function MasterVisitComponentsList(Request $request)
    {
        $requestData = $this->getRequestData($request);
        $requestData['userId']         = (in_array($request->user()->user_type, Config::get('constants.USER_TYPE_STAFF'))) ? $request->user()->created_by : $request->user()->user_id;
        $visitComponents = $this->visitModelObj->MasterVisitComponentsList($requestData);
        if($visitComponents){
            return $this->resultResponse(
                Config::get('restresponsecode.SUCCESS'), 
                $visitComponents, 
                [],
                trans('Visits::messages.component_data_fetch_successfully'), 
                $this->http_codes['HTTP_OK']
            );
        }else{
            return $this->resultResponse(
                Config::get('restresponsecode.ERROR'), 
                [], 
                [],
                trans('Visits::messages.component_data_fetch_fail'), 
                $this->http_codes['HTTP_OK']
            );
        }          
    }

    public function UpdateVisitSettingComponent(Request $request)
    {
        $requestData = $this->getRequestData($request);
        $requestData['user_id']         = (in_array($request->user()->user_type, Config::get('constants.USER_TYPE_STAFF'))) ? $request->user()->created_by : $request->user()->user_id;
        $visitComponents = $this->visitModelObj->UpdateVisitSettingComponent($requestData);
        if($visitComponents){
            return $this->resultResponse(
                Config::get('restresponsecode.SUCCESS'), 
                $visitComponents, 
                [],
                trans('Visits::messages.component_data_fetch_successfully'), 
                $this->http_codes['HTTP_OK']
            );
        }else{
            return $this->resultResponse(
                Config::get('restresponsecode.ERROR'), 
                [], 
                [],
                trans('Visits::messages.component_data_fetch_fail'), 
                $this->http_codes['HTTP_OK']
            );
        }          
    }

    /**
     * @DateOfCreation        3 July 2018
     * @ShortDescription      This function is responsible to get the Domestic factor field value
     * @return                Array of status and message
     */
    public function getNewVisitFormFector(Request $request)
    {
        $requestData    = $this->getRequestData($request);
        $formType       = isset($requestData['form_type']) ?  $requestData['form_type'] : [];
        $formType       = !empty($formType) && !is_array($formType) ? [$formType] :$formType;
        $visitId        = isset($requestData['visit_id']) ?  $requestData['visit_id'] : '';
        $visitIdEncrypt = $visitId;
        $patientId      = isset($requestData['pat_id']) ?  $requestData['pat_id'] : '';
        $patientId      = !empty($patientId) ? $this->securityLibObj->decrypt($patientId) : '';
        $visitId        = !empty($visitId) ? $this->securityLibObj->decrypt($visitId) : '';
        $formTypeData   = [
                            'new-form-fector'                       => 'newVisitFormFector',
                            'death-info-form-fector'                => 'VisitsDeathInfoFormFector',
                            'hospitalization-form-fector'           => 'VisitsHospitalizationFormFector',
                            'hospitalization-reference-form-fector' => 'VisitsHospitalizationReferenceFormFector',
                            'occupational-status-form-fector'       => 'VisitsOccupationalStatusFormFector',
                            'changes-in-form-fector'                => 'VisitsChangesInFormFector',
                            'vitals-form-fector'                    => 'VisitsVitalsFormFector',
                            'vitals-Weight-form-fector'             => 'VisitsVitalsFormFectorWeight',
                            'physical-examination-form-fector'      => 'VisitsPhysicalExaminationFormFector',
                            'treatment-oxygen-requirements'         => 'treatmentOxygenRequirements',
                            'treatment-other-requirements'          => 'treatmentOtherRequirements',
                            'investigation-fector'                  => 'InvestigationFector',
                            'spirometries-fector'                   => 'SpirometriesFector',
                            'six-mwt-fector'                        => 'SixMWTFector',
                            'diagnosis-form-fectors'                => 'DiagnosisFormFectors',
                            'hospitalization-table-fector'          => 'VisitsHospitalizationTableFector',
                            'spirometries-table-fector'             => 'SpirometriesTableFector',
                            'six-mwt-table-fector'                  => 'SixMWTTableFector',
                            'treatment-fectors'                     => 'VisitTreatmentFectors',
                            'abg-form-fector'                       => 'AbgInvestigationFector',
                            'abg-form-factor-date'                  => 'AbgInvestigationFectorDate',
                            'pulmonary-function-test'               => 'PulmonaryFunctionTestFector',
                            'sleep_study_form'                      => 'SleepStudyReport',
                            'investigation_report_form'             => 'InvestigationReport',
                        ];
        $formTypeExtraData = [
                            'thoracoscopic-lung-biopsy-factor'      => 'ThoracoscopicLungBiopsy',
                            'surgical-lung-biopsy'                  => 'SurgicalLungBiopsy',
                            'surgical-lung-biopsy-date'             => 'SurgicalLungBiopsyDate',
                            'chest-xray'                            => 'ChestXray',
                            'hrct'                                  => 'HRCT',
                            'hrct-date'                             => 'HRCTDate',
                            'hrct-extra'                            => 'HRCTEXTRA',
                            'uip'                                   => 'UIP',
                            'fiber-optic-broncho-date'              => 'FiberopticBronchoscopyDate',
                            'fiber-optic-broncho'                   => 'FiberopticBronchoscopy',
                            'patient-disease-gerd-form-factor'      => 'GerdDiseaseFormFactor',
                            'patient-disease-ctd-form-factor'       => 'CtdDiseaseFormFactor',
                            'patient-disease-pulmonary-form-factor' => 'PulmonaryDiseaseFormFactor',
                        ];

        $formTypeInter = !empty($formType) ? array_intersect_key($formTypeData,array_flip($formType)) : [];
        $formTypeInterExtra = !empty($formType) ? array_intersect_key($formTypeExtraData,array_flip($formType)) : [];
        $staticDataKey = $this->staticDataObj->getStaticDataConfigList()['new_visit_fectors'];

        $formTypeInter = array_merge($formTypeInter,$formTypeInterExtra);

        $finalCheckupRecords    = [];
        $patientSpirometriesID  = '';
        $patientSixmwtID        = '';
        $patientFiberopticBronchoscopy = [];
        $patientFiberopticBronchoscopyType = [];

        if(!empty($staticDataKey)){
            foreach ($staticDataKey as $formName => $formFields) {
                if (!empty($formType) && !empty($formTypeInter) && !in_array($formName, $formTypeInter)){
                    continue;
                } elseif (!empty($formType) && empty($formTypeInter)) {
                    continue;
                } elseif (empty($formType) && in_array($formName,$formTypeExtraData)) {
                    continue;
                }

                $data = [];
                $formValuData = [];
                $factorValueSelectColumnName = 'fector_value';
                

                if ($formName ==='VisitsPhysicalExaminationFormFector' || $formName === 'InvestigationFector' || $formName === 'VisitsVitalsFormFectorWeight') {
                    $formValuData = !empty($visitId) ? $this->physicalExaminationsObj->getPhysicalExaminationsByVistID($visitId,$patientId,true) : [];
                    $formValuData = !empty($formValuData) ? $this->utilityLibObj->changeArrayKey($formValuData,'fector_id') : [];
                }

                if ($formName ==='newVisitFormFector') {
                    $formValuData = !empty($visitId) ? $this->visitModelObj->getVisitDetailsByVistID($visitId, $patientId) : [];
                }

                if ($formName ==='VisitsDeathInfoFormFector') {
                    $formValuData = !empty($visitId) ? $this->patientDeathInfoObj->getPatientDeathInfo($visitId, $patientId, true) : [];
                }

                if ($formName ==='VisitsHospitalizationFormFector' || $formName ==='VisitsHospitalizationReferenceFormFector') {
                    $formValuData = !empty($visitId) ? $this->hospitalizationsObj->getPatientHospitalizationsInfo($visitId, $patientId, true) : [];
                }

                if ($formName ==='VisitsChangesInFormFector') {
                    $formValuData = !empty($visitId) ? $this->visitChangesInObj->getPatientChangesInInfo($visitId, $patientId, true) : [];
                    $formValuData = !empty($formValuData) ? $this->utilityLibObj->changeArrayKey($formValuData,'fector_id') : [];
                }

                if ($formName ==='VisitsVitalsFormFector') {
                    $formValuData = !empty($visitId) ? $this->vitalsObj->getPatientVitalsInfo($visitId, $patientId, true) : [];
                    $formValuData = !empty($formValuData) ? $this->utilityLibObj->changeArrayKey($formValuData,'fector_id') : [];
                }

                if ($formName ==='treatmentOxygenRequirements') {
                    $formValuData = !empty($visitId) ? $this->treatmentRequirementObj->getTreatmentRequirementsInfo($visitId, $patientId, true) : [];
                    $formValuData = !empty($formValuData) ? $this->utilityLibObj->changeArrayKey($formValuData,'fector_id') : [];
                }

                if ($formName ==='treatmentOtherRequirements') {
                    $formValuData = !empty($visitId) ? $this->treatmentRequirementObj->getTreatmentRequirementsInfo($visitId, $patientId, true) : [];
                    $formValuData = !empty($formValuData) ? $this->utilityLibObj->changeArrayKey($formValuData,'fector_id') : [];
                }elseif($formName ==='PulmonaryFunctionTestFector'){
                    $formValuData = !empty($visitId) ? $this->pulmonaryFunctionTestObj->getPulmonaryFunctionByVistID($visitId, $patientId, true) : [];
                }elseif($formName ==='SpirometriesFector'){
                    $formValuData = !empty($visitId) ? $this->spirometryObj->getSpirometryByVistID($visitId,$patientId,true) : [];
                    $formValuData = !empty($formValuData) ? $this->utilityLibObj->changeArrayKey($formValuData,'visit_id') : [];

                    $patientSpirometriesID = (count($formValuData) > 0) ? $formValuData[$visitIdEncrypt]['spirometry_id'] : ''; 
                }elseif($formName ==='SixMWTFector'){
                    $formValuData = !empty($visitId) ? $this->sixmwtObj->getSixmwtByVistID($visitId,$patientId,true) : [];
                    $formValuData = !empty($formValuData) ? $this->utilityLibObj->changeArrayKey($formValuData,'visit_id') : [];

                    $patientSixmwtID = (count($formValuData) > 0) ? $formValuData[$visitIdEncrypt]['sixmwt_id'] : '';
                }elseif($formName ==='AbgInvestigationFector'){
                    $formValuData = !empty($visitId) ? $this->investigationAbgObj->getInvestigationAbgByVistID($visitId,$patientId,true) : [];
                    $formValuData = !empty($formValuData) ? $this->utilityLibObj->changeArrayKey($formValuData,'fector_id') : [];
                }elseif($formName ==='AbgInvestigationFectorDate'){

                    $formValuData = !empty($visitId) ? $this->investigationAbgObj->getInvestigationAbgByVistID($visitId,$patientId,true) : [];
                    $formValuData = !empty($formValuData) ? $this->utilityLibObj->changeArrayKey($formValuData,'visit_id') : [];

                }elseif($formName ==='ThoracoscopicLungBiopsy'){
                    $formValuData = !empty($visitId) ? $this->thoracoscopicLungObj->getThoracoscopicLungByVistID($visitId,$patientId,true) : [];
                    $formValuData = !empty($formValuData) ? $this->utilityLibObj->changeArrayKey($formValuData,'visit_id') : [];
                }elseif($formName ==='SurgicalLungBiopsy'){
                    $factorValueSelectColumnName = 'pslbf_factor_value';
                    $formValuData = !empty($visitId) ? $this->surgicalLungBiopsyObj->getSurgicalLungBiopsyByVistID($visitId,$patientId,true) : [];
                    $formValuData = !empty($formValuData) ? $this->utilityLibObj->changeArrayKey($formValuData,'pslbf_factor_id') : [];
                }elseif($formName ==='SurgicalLungBiopsyDate'){
                    $formValuData = !empty($visitId) ? $this->surgicalLungBiopsyObj->getSurgicalLungBiopsyByVistID($visitId,$patientId,true) : [];
                    $formValuData = !empty($formValuData) ? $this->utilityLibObj->changeArrayKey($formValuData,'visit_id') : [];

                }elseif($formName ==='ChestXray'){
                    $formValuData = !empty($visitId) ? $this->chestXrayObj->getChestXrayByVistID($visitId,$patientId,true) : [];
                    $formValuData = !empty($formValuData) ? $this->utilityLibObj->changeArrayKey($formValuData,'pcx_type') : [];
                }elseif($formName ==='HRCT'){
                    $factorValueSelectColumnName = 'phrct_factor_value';
                    $formValuData = !empty($visitId) ? $this->hrctObj->getHRCTByVistID($visitId,$patientId,true) : [];
                    $formValuData = !empty($formValuData) ? $this->utilityLibObj->changeArrayKey($formValuData,'phrct_factor_id') : [];
                }elseif($formName ==='HRCTDate'){
                    $formValuData = !empty($visitId) ? $this->hrctObj->getHRCTByVistID($visitId,$patientId,true) : [];
                    $formValuData = !empty($formValuData) ? $this->utilityLibObj->changeArrayKey($formValuData,'visit_id') : [];
                }elseif($formName ==='HRCTEXTRA'){
                    $factorValueSelectColumnName = 'phrct_factor_value';
                    $formValuData = !empty($visitId) ? $this->hrctObj->getHRCTByVistID($visitId,$patientId,true) : [];
                    $formValuData = !empty($formValuData) ? $this->utilityLibObj->changeArrayKey($formValuData,'phrct_factor_id') : [];
                }elseif($formName ==='UIP'){

                    $formValuData = !empty($visitId) ? $this->uipObj->getUIPByVistID($visitId,$patientId,true) : [];
                    $formValuData = !empty($formValuData) ? $this->utilityLibObj->changeArrayKey($formValuData,'visit_id') : [];

                }elseif($formName ==='FiberopticBronchoscopyDate'){
                    $formValuData = !empty($visitId) ? $this->fiberopticBronchoscopyObj->getFiberopticBronchoscopyByVistID($visitId,$patientId,true) : [];
                    $patientFiberopticBronchoscopy = !empty($formValuData) ? $this->utilityLibObj->changeArrayKey($formValuData,'factor_id') : [];
                    $patientFiberopticBronchoscopyType = !empty($formValuData) ? $this->utilityLibObj->changeArrayKey($formValuData,'factor_id_value') : [];
                    $formValuData = !empty($formValuData) ? $this->utilityLibObj->changeArrayKey($formValuData,'visit_id') : [];
                }elseif($formName ==='GerdDiseaseFormFactor'){
                    $diseaseId = $this->staticDataObj->getDieseaseIdByName('GERD');
                    $formValuData = !empty($visitId) ? $this->diagnosisObj->getpatientDiagnosis($visitId, $patientId, $diseaseId, true) : [];
                    $formValuData = !empty($formValuData) ? $this->utilityLibObj->changeArrayKey($formValuData,'diagnosis_fector_key') : [];
                }elseif($formName ==='CtdDiseaseFormFactor'){
                    $diseaseId = $this->staticDataObj->getDieseaseIdByName('CTD');
                    $formValuData = !empty($visitId) ? $this->diagnosisObj->getpatientDiagnosis($visitId, $patientId, $diseaseId, true) : [];
                    $formValuData = !empty($formValuData) ? $this->utilityLibObj->changeArrayKey($formValuData,'diagnosis_fector_key') : [];
                }elseif($formName ==='PulmonaryDiseaseFormFactor'){
                    $diseaseId = $this->staticDataObj->getDieseaseIdByName('Pulmonary hypertension: 2D ECHO');
                    $formValuData = !empty($visitId) ? $this->diagnosisObj->getpatientDiagnosis($visitId, $patientId, $diseaseId, true) : [];
                    $formValuData = !empty($formValuData) ? $this->utilityLibObj->changeArrayKey($formValuData,'diagnosis_fector_key') : [];
                }elseif($formName ==='InvestigationReport'){

                    $formValuData = !empty($visitId) ? $this->investigationReportModelObj->getInvestigationReportData(['visit_id' => $visitId, 'pat_id' => $patientId]) : [];
                    $formValuData = !empty($formValuData) ? $this->utilityLibObj->changeArrayKey($formValuData, 'report_type') : [];

                }elseif($formName ==='SleepStudyReport'){

                    $formValuData = !empty($visitId) ? $this->sleepStudyModelObj->getSleepStudyData(['visit_id' => $visitId, 'pat_id' => $patientId]) : [];
                    $formValuData = !empty($formValuData) ? array($formValuData): [];
                }


                $handlers = [];
                $handler = [];
                $handlerName = [];
                $pulmonaryDiseaseDate = '';
                $gerdDiseaseDate = '';
                $ctdDiseaseDate = '';
                foreach($formFields as $fectorKey => $fectorValue){
                    $temp = [];
                    $encryptFectorKey = $this->securityLibObj->encrypt($fectorValue['id']);

                    $fieldName = '';
                    if($formName == 'newVisitFormFector') {
                        $form = $formName;
                        $fieldName = $fectorValue['name'];
                    } else if($formName == 'VisitsDeathInfoFormFector'){
                        $form = $formName;
                        $fieldName = $fectorValue['name'];
                    } else if($formName == 'VisitsHospitalizationFormFector'){
                        $form = $formName;
                        $fieldName = $fectorValue['name'];
                    } else if($formName == 'VisitsHospitalizationReferenceFormFector'){
                        $form = $formName;
                        $fieldName = $fectorValue['name'];
                    } else if($formName == 'VisitsOccupationalStatusFormFector'){
                        $form = $formName;
                        $fieldName = $fectorValue['name'];
                    } else if($formName == 'VisitsChangesInFormFector'){
                        $form = $formName;
                        $fieldName = $fectorValue['name'].'_'.$encryptFectorKey;
                    } else if($formName == 'VisitsVitalsFormFector'){
                        $form = $formName;
                        $fieldName = $fectorValue['name'].'_'.$encryptFectorKey;
                    } else if($formName == 'VisitsPhysicalExaminationFormFector' || $formName =='InvestigationFector' || $formName =='VisitsVitalsFormFectorWeight'){
                        $form = $formName;
                        $fieldName = $fectorValue['name'].'_'.$encryptFectorKey;
                    } else if($formName == 'treatmentOxygenRequirements'){
                        $form = $formName;
                        $fieldName = $fectorValue['name'].'_'.$encryptFectorKey;
                    } else if($formName == 'treatmentOtherRequirements'){
                        $form = $formName;
                        $fieldName = $fectorValue['name'].'_'.$encryptFectorKey;
                    } else if($formName == 'InvestigationReport'){
                        $form = $formName;
                        $fieldName = $fectorValue['name'].'_'.$encryptFectorKey;
                    } else if($formName == 'SleepStudyReport'){
                        $form = $formName;
                        $fieldName = $fectorValue['name'];
                    } else if($formName == 'SpirometriesFector'){
                        $form = $formName;
                        $fieldName = $fectorValue['name'];
                        $encryptFectorKey = $this->securityLibObj->encrypt($visitId);
                        if(!empty($formValuData)){
                            $formValuData[$encryptFectorKey]['fector_value'] = !empty($formValuData) ? $formValuData[$encryptFectorKey][$fieldName]:'';
                        }
                    } else if($formName == 'SixMWTFector'){
                        $form = $formName;
                        $fieldName = $fectorValue['name'];
                        $encryptFectorKey = $this->securityLibObj->encrypt($visitId);
                        if(!empty($formValuData)){
                            $formValuData[$encryptFectorKey]['fector_value'] = !empty($formValuData) ? $formValuData[$encryptFectorKey][$fieldName]:'';
                        }
                    } else if ($formName ==='AbgInvestigationFector' ) {
                        $form = $formName;
                        $fieldName = $fectorValue['name'];
                    }else if ($formName ==='PulmonaryFunctionTestFector' ) {
                        $form = $formName;
                        $fieldName = $fectorValue['name'];
                    }else if($formName ==='AbgInvestigationFectorDate' ){
                        $form = $formName;
                        $fieldName = $fectorValue['name'];
                        $encryptFectorKey = $this->securityLibObj->encrypt($visitId);
                        if(!empty($formValuData)){
                            $formValuData[$encryptFectorKey]['fector_value'] = !empty($formValuData) ? $formValuData[$encryptFectorKey][$fieldName]:'';
                        }
                    }elseif ($formName =='ThoracoscopicLungBiopsy' && in_array('ThoracoscopicLungBiopsy',$formTypeInterExtra)) {
                        $form = $formName;
                        $fieldName = $fectorValue['name'];
                        $encryptFectorKey = $this->securityLibObj->encrypt($visitId);
                        if(!empty($formValuData)){

                            $formValuData[$encryptFectorKey]['fector_value'] = !empty($formValuData) ? $formValuData[$encryptFectorKey][$fieldName]:'';
                        }
                    }else if ($formName ==='SurgicalLungBiopsy' && in_array('SurgicalLungBiopsy',$formTypeInterExtra)) {
                        $form = $formName;
                        $fieldName = $fectorValue['name'].$encryptFectorKey;
                    }else if($formName ==='SurgicalLungBiopsyDate' && in_array('SurgicalLungBiopsyDate',$formTypeInterExtra)){
                        $form = $formName;
                        $fieldName = $fectorValue['name'];
                        $encryptFectorKey = $this->securityLibObj->encrypt($visitId);
                        if(!empty($formValuData)){
                            $formValuData[$encryptFectorKey]['fector_value'] = !empty($formValuData) ? $formValuData[$encryptFectorKey][$fieldName]:'';
                        }
                    }else if($formName ==='ChestXray' && in_array('ChestXray',$formTypeInterExtra)){
                        $form = $formName;
                        $fieldName = $fectorValue['name'];
                        $fieldType = $fectorValue['type'];
                        $encryptFectorKey = $this->securityLibObj->encrypt($visitId);
                        if(!empty($formValuData)){
                            $formValuData[$encryptFectorKey][$fieldName] = !empty($formValuData) ? $formValuData[$fieldType][$fieldName]:'';
                        }
                        $factorValueSelectColumnName = $fieldName;
                        $fieldName = $fieldName.($fieldType == '1' ? '_recent':'_old');
                    }else if ($formName ==='HRCT' && in_array('HRCT',$formTypeInterExtra)) {
                        $form = $formName;
                        $fieldName = $fectorValue['name'].$encryptFectorKey;
                    }else if($formName ==='HRCTDate' && in_array('HRCTDate',$formTypeInterExtra)){
                        $form = $formName;
                        $fieldName = $fectorValue['name'];
                        $encryptFectorKey = $this->securityLibObj->encrypt($visitId);
                        if(!empty($formValuData)){
                            $formValuData[$encryptFectorKey]['fector_value'] = !empty($formValuData) ? $formValuData[$encryptFectorKey][$fieldName]:'';
                        }
                    }else if ($formName ==='HRCTEXTRA' && in_array('HRCTEXTRA',$formTypeInterExtra)) {
                        $form = $formName;
                        $fieldName = $fectorValue['name'].$encryptFectorKey;
                    }else if($formName ==='UIP' && in_array('UIP',$formTypeInterExtra)){
                        $form = $formName;
                        $fieldName = $fectorValue['name'];
                        $encryptFectorKey = $this->securityLibObj->encrypt($visitId);
                        if(!empty($formValuData)){
                            $formValuData[$encryptFectorKey]['fector_value'] = !empty($formValuData) ? $formValuData[$encryptFectorKey][$fieldName]:'';
                        }
                    }else if($formName ==='FiberopticBronchoscopyDate' && in_array('FiberopticBronchoscopyDate',$formTypeInterExtra)){
                        $form = $formName;
                        $fieldName = $fectorValue['name'];
                        $encryptFectorKey = $this->securityLibObj->encrypt($visitId);
                        if(!empty($formValuData)){
                            $formValuData[$encryptFectorKey]['fector_value'] = !empty($formValuData) ? $formValuData[$encryptFectorKey][$fieldName]:'';
                        }
                    }else if($formName ==='GerdDiseaseFormFactor' && in_array('GerdDiseaseFormFactor',$formTypeInterExtra)){
                        $form = $formName;
                        $fieldName = $fectorValue['name'];
                        if($this->securityLibObj->decrypt($encryptFectorKey) != 1){
                            $fieldName = $fectorValue['name'].'_'.$encryptFectorKey;                            
                        } 
                    }else if($formName ==='CtdDiseaseFormFactor' && in_array('CtdDiseaseFormFactor',$formTypeInterExtra)){
                        $form = $formName;
                        $fieldName = $fectorValue['name'];
                        if($this->securityLibObj->decrypt($encryptFectorKey) != 1){
                            $fieldName = $fectorValue['name'].'_'.$encryptFectorKey;
                        }
                    }else if($formName ==='PulmonaryDiseaseFormFactor' && in_array('PulmonaryDiseaseFormFactor',$formTypeInterExtra)){
                        $form = $formName;
                        $fieldName = $fectorValue['name'];
                        if($this->securityLibObj->decrypt($encryptFectorKey) != 1){
                            $fieldName = $fectorValue['name'].'_'.$encryptFectorKey;
                        }
                    }

                    if($formName == 'newVisitFormFector') {
                        $tempValue = count($formValuData) > 0 ? (string) $formValuData[0]->$fieldName : '';
                    } elseif($formName == 'VisitsDeathInfoFormFector'){
                        $tempValue = count($formValuData) > 0 ? (string) $formValuData[0]->$fieldName : '';
                    } elseif($formName == 'VisitsHospitalizationFormFector'){
                        $tempValue = count($formValuData) > 0 ? (string) $formValuData[0]->$fieldName : '';
                    } elseif($formName == 'VisitsHospitalizationReferenceFormFector'){
                        $tempValue = count($formValuData) > 0 ? (string) $formValuData[0]->$fieldName : '';
                    } elseif($formName == 'SleepStudyReport'){
                        $tempValue = count($formValuData) > 0 ? (string) $formValuData[0]->$fieldName : '';
                    } elseif($formName == 'InvestigationReport'){
                        $tempFieldName = explode('_',$fieldName);
                        count($tempFieldName) > 0 ? array_pop($tempFieldName) : $tempFieldName;
                        
                        $tempFieldName = count($tempFieldName) > 0 ? implode('_', $tempFieldName) : $tempFieldName;

                        if($tempFieldName == 'report_file'){
                            $tempValue = !empty($formValuData) && isset($formValuData[$encryptFectorKey]) ? (string) $formValuData[$encryptFectorKey]['ir_id']  : '';
                        } else {
                            $tempValue = !empty($formValuData) && isset($formValuData[$encryptFectorKey]) ? (string) $formValuData[$encryptFectorKey][$tempFieldName]  : '';
                        }
                        
                    }elseif($formName == 'treatmentOtherRequirements' && $fectorValue['field_type'] == 'checkbox'){

                        if(!empty($formValuData)){
                            $tempValue = $formValuData[$encryptFectorKey][$factorValueSelectColumnName];
                            if(isset($tempValue[1]))
                                $tempValue = [(string) $tempValue[0], (string) $tempValue[1]];
                            else
                                $tempValue = [(string) $tempValue[0]];
                        } else {
                            $tempValue = [];
                        }
                    } elseif($formName == 'PulmonaryFunctionTestFector'){
                        $tempValue = count($formValuData) > 0 ? (string) $formValuData[0]->$fieldName : '';
                    } elseif($formName == 'GerdDiseaseFormFactor'){
                        $tempValue = !empty($formValuData) && isset($formValuData[$encryptFectorKey]) ? (string) $formValuData[$encryptFectorKey]->diagnosis_fector_value : '';  
                        
                        if(!empty($formValuData) && isset($formValuData[1]) && !isset($formValuData[$encryptFectorKey]) && empty($gerdDiseaseDate)){
                           $gerdDiseaseDate = $tempValue = $formValuData[1]['date_of_diagnosis'];
                        }
                    }elseif($formName == 'CtdDiseaseFormFactor'){

                        $tempValue = !empty($formValuData) && isset($formValuData[$encryptFectorKey]) ? (string) $formValuData[$encryptFectorKey]->diagnosis_fector_value : '';  

                        if(!empty($formValuData) && isset($formValuData[1]) && !isset($formValuData[$encryptFectorKey]) && empty($ctdDiseaseDate)){
                            $ctdDiseaseDate = $tempValue = $formValuData[1]['date_of_diagnosis'];
                        }
                    }elseif($formName == 'PulmonaryDiseaseFormFactor'){
                        $tempValue = !empty($formValuData) && isset($formValuData[$encryptFectorKey]) ? (string) $formValuData[$encryptFectorKey]->diagnosis_fector_value : '';
                        
                        if(!empty($formValuData) && isset($formValuData[1]) && !isset($formValuData[$encryptFectorKey]) && empty($pulmonaryDiseaseDate)){
                            $pulmonaryDiseaseDate = $tempValue = $formValuData[1]['date_of_diagnosis'];
                        }
                    }else {
                        $tempValue = !empty($formValuData) && isset($formValuData[$encryptFectorKey][$factorValueSelectColumnName]) ? (string) $formValuData[$encryptFectorKey][$factorValueSelectColumnName] : '';
                    }

                    $temp = [  
                        'showOnForm'=>true,
                        'name'  => $fieldName,
                        'title' => $fectorValue['value'],
                        'type'  => $fectorValue['field_type'],
                        'value' => ($fectorValue['field_type'] == 'customcheckbox') ? [$tempValue] : $tempValue,
                        'fieldName' => (!empty($fectorValue['field_name'])) ? $fectorValue['field_name'] : '',
                        'showHideTrigger' => (!empty($fectorValue['show_hide_trigger'])) ? $fectorValue['show_hide_trigger'] : '',
                    ];

                    if(isset($fectorValue['isClearfix'])){
                        $temp = array_merge($temp, ['clearFix' => true]);
                    }
                    if(isset($fectorValue['isFileView'])){
                        $temp = array_merge($temp, ['showFileView' => true]);
                    }
                    if(isset($fectorValue['fileType'])){
                        $temp = array_merge($temp, ['fileType' => $fectorValue['fileType']]);
                    }
                    if(isset($fectorValue['placeholder'])){
                        $temp = array_merge($temp, ['placeholder' => $fectorValue['placeholder']]);
                    }

                    if($fectorValue['field_type'] == 'date'){
                        $temp['format'] = "DD/MM/YYYY";                    
                    }

                    if(isset($fectorValue['restrictType']) && !empty($fectorValue['restrictType'])){
                        $temp['restrictType'] = $fectorValue['restrictType'];
                    }

                    $temp['cssClasses'] = $fectorValue['cssClasses'];
                    if($fectorValue['field_type'] == ('customcheckbox' || 'checkbox'))
                    {                
                        $data[$fieldName.'_data'] = $this->getCheckBoxOption('option', $fectorValue['options']);
                    } else if ($fectorValue['field_type'] == 'select'){
                        $data[$fieldName.'_data'] = $this->getCheckBoxOption('option', $fectorValue['options']);
                    }
                    
                    if(isset($fectorValue['handlers'])  && !empty($fectorValue['handlers']))
                    { 
                        $handlers[$fieldName.'_handle'] =  $fectorValue['handlers'];
                        $handler[$fieldName] =  $fectorValue['handlers'];
                        $handlerName[$fieldName] =  $fectorValue['field_name'];
                    }

                    if($formName == 'AbgInvestigationFector') {
                        $finalCheckupRecords[$form]['fields']['fields'][]  = $temp;
                    }else if($formName == 'InvestigationReport'){
                        $finalCheckupRecords[$form]['fields']['fields'][]  = $temp;
                    }else{
                        $finalCheckupRecords[$form]['fields'][]  = $temp;
                    }
                    $finalCheckupRecords[$form]['data']      = $data;
                    $finalCheckupRecords[$form]['handlers']  = $handlers;
                    $finalCheckupRecords[$form]['handlerData']  = $handler;
                    $finalCheckupRecords[$form]['handlerName']  = $handlerName;
                    $finalCheckupRecords[$form]['formName']  = $formName;
                }

                if($formName == 'AbgInvestigationFector'){
                    $finalCheckupRecords[$formName]['fields'][]=[
                    'name' => 'ABG',
                    'title' => 'While breathing air at SEA level',
                    'type' => 'group',
                    'showOnForm' => true,
                    'cssClasses' => ['groupHeadClass'=>'group-head add-tb-spaces sub-heading','groupContainerClass'=>''],
                    'fields' => $finalCheckupRecords[$formName]['fields']['fields']
                    ];

                    if(isset($finalCheckupRecords[$formName]['fields']['fields'])){
                        unset($finalCheckupRecords[$formName]['fields']['fields']);
                    }
                }

                if($formName == 'InvestigationReport'){
                    $groupFieldData = array_chunk($finalCheckupRecords[$formName]['fields']['fields'],2);
                    if(!empty($groupFieldData)){
                        $i = 1;
                        foreach ($groupFieldData as $fields) {
                            $groupTitle = explode('#', $fields[0]['title'])[1];
                            $clearFix   = ($i%2) == 0 ? true : false;

                            $fields[0]['title'] = strstr($fields[0]['title'], '#', true);
                            $finalCheckupRecords[$formName]['fields'][]= array(
                                                                            'name'          => $groupTitle,
                                                                            'title'         => $groupTitle,
                                                                            'type'          => "group",
                                                                            'showOnForm'    => true,
                                                                            'cssClasses'    => [ 'groupHeadClass' => 'investigation-group-head col-md-12 mb-10 mt20', 'groupContainerClass' => '', 'groupParentClass' => 'col-md-6'],
                                                                            'fields'        => $fields,
                                                                            'clearFix'      => $clearFix
                                                                        );
                            $i++;
                        }
                    }

                    if(isset($finalCheckupRecords[$formName]['fields']['fields'])){
                        unset($finalCheckupRecords[$formName]['fields']['fields']);
                    }
                }
            }
        }

        if((empty($formType)) || 
            (
                !empty($formType) &&
                !empty($formTypeInter) && 
                in_array('DiagnosisFormFectors', $formTypeInter)
            )
        ){
            $diseaseData = $this->diagnosisObj->getPatientMedicalHistory($visitId, $patientId);
            $diseaseDataArr = [];
            if(!empty($diseaseData)){
                foreach ($diseaseData as $disease) {
                    $diseaseDataArr[] = [
                        'disease_name'          => $disease->disease_name,
                        'disease_id'            => $disease->disease_id,
                        'visit_diagnosis_id'    => $disease->visit_diagnosis_id,
                        'date_of_diagnosis'     => $disease->date_of_diagnosis,
                        'pat_id'                => $disease->pat_id,
                        'visit_id'              => $disease->visit_id,
                        'fectors'               => $this->getDiagnosisExtraFectors($disease->disease_id, $disease->disease_name, $disease->visit_diagnosis_id)
                    ];
                }
            }

            $finalCheckupRecords['DiagnosisFormFectors'] = $diseaseDataArr;
        }

        if((empty($formType)) || 
            (
                !empty($formType) &&
                !empty($formTypeInter) && 
                in_array('VisitsHospitalizationTableFector', $formTypeInter)
            )
        ){
            $hospitalizationExtraInfo           = $this->hospitalizationsExtraInfoObj->getHospitalizationsExtraInfo($visitId, $patientId);
            $hospitalizationExtraInfoWithKey    = $this->utilityLibObj->changeArrayKey($hospitalizationExtraInfo, 'hospitalization_fector_id');
            $visitsHospitalizationTableFector   = $this->staticDataObj->getHospitalizationTableFector();

            foreach ($visitsHospitalizationTableFector as $key => $tableFector) {
                $fectorId = $tableFector['hospitalization_fector_id'];
                if(array_key_exists($fectorId, $hospitalizationExtraInfoWithKey)){
                    $fectorKey = 'hospitalization_diagnosis_details_key_'.$fectorId;
                    $date = !empty($hospitalizationExtraInfoWithKey[$fectorId]['hospitalization_date']) ? $hospitalizationExtraInfoWithKey[$fectorId]['hospitalization_date'] : null;

                    $visitsHospitalizationTableFector[$key]['hospitalization_diagnosis_details_'.$fectorId]     = $hospitalizationExtraInfoWithKey[$fectorId]['hospitalization_diagnosis_details'];
                    $visitsHospitalizationTableFector[$key]['hospitalization_date_'.$fectorId]                  = $date;
                    $visitsHospitalizationTableFector[$key]['hospitalization_duration_'.$fectorId]              = $hospitalizationExtraInfoWithKey[$fectorId]['hospitalization_duration'];
                    $visitsHospitalizationTableFector[$key]['hospitalization_duration_unit_value_'.$fectorId]   = (int)$hospitalizationExtraInfoWithKey[$fectorId]['hospitalization_duration_unit'];
                }
            }

            $finalCheckupRecords['VisitsHospitalizationTableFector'] = $visitsHospitalizationTableFector;
        }

        if((empty($formType)) || 
            (
                !empty($formType) &&
                !empty($formTypeInter) && 
                in_array('SpirometriesTableFector', $formTypeInter)
            )
        ){
            $getSpirometryFectorsDB     = $this->spirometriesObj->getSpirometryTableFectorsData($patientSpirometriesID);
            $spirometryFectorsWithKey   = $this->utilityLibObj->changeArrayKey($getSpirometryFectorsDB, 'fector_id');
            $spirometriesTableFector    = $this->staticDataObj->getSpirometriesTableFector();
           
            $keyFEV1    = $this->securityLibObj->encrypt(Config::get('dataconstants.SPIROMETRY_FEV1_FACTOR_ID'));
            $keyFVC     = $this->securityLibObj->encrypt(Config::get('dataconstants.SPIROMETRY_FVC_FACTOR_ID'));
            $keyFEV1FVC = $this->securityLibObj->encrypt(Config::get('dataconstants.SPIROMETRY_FEV1_FVC_FACTOR_ID'));
            if(count($spirometryFectorsWithKey) > 0){

                $spirometryFectorsWithKey[$keyFEV1FVC]['fector_pre_value'] = '-';
                if(isset($spirometryFectorsWithKey[$keyFEV1]) && isset($spirometryFectorsWithKey[$keyFVC])){
                    if(!empty($spirometryFectorsWithKey[$keyFEV1]['fector_pre_value'] && $spirometryFectorsWithKey[$keyFVC]['fector_pre_value'])){
                        $spirometryFectorsWithKey[$keyFEV1FVC]['fector_pre_value'] = round($spirometryFectorsWithKey[$keyFEV1]['fector_pre_value'] / $spirometryFectorsWithKey[$keyFVC]['fector_pre_value'], 2);
                    }                    
                }

                $spirometryFectorsWithKey[$keyFEV1FVC]['fector_post_value'] = '-';
                if(isset($spirometryFectorsWithKey[$keyFEV1]) && isset($spirometryFectorsWithKey[$keyFVC])){
                    if(!empty($spirometryFectorsWithKey[$keyFEV1]['fector_post_value'] && $spirometryFectorsWithKey[$keyFVC]['fector_post_value'])){
                        $spirometryFectorsWithKey[$keyFEV1FVC]['fector_post_value'] = round($spirometryFectorsWithKey[$keyFEV1]['fector_post_value'] / $spirometryFectorsWithKey[$keyFVC]['fector_post_value'], 2);
                    }  
                }              
            }

            foreach ($spirometriesTableFector as $fectorKey => $fectorValue) {
                $fectorId = $fectorValue['spirometries_fector_id'];
                if(array_key_exists($fectorId, $spirometryFectorsWithKey)){
                    $spirometriesTableFector[$fectorKey]['spirometries_fector_pre_value']  = $spirometryFectorsWithKey[$fectorId]['fector_pre_value'];
                    $spirometriesTableFector[$fectorKey]['spirometries_fector_post_value'] = $spirometryFectorsWithKey[$fectorId]['fector_post_value'];
                }
            }

            $finalCheckupRecords['SpirometriesTableFector'] = $spirometriesTableFector;           
        }

        if((empty($formType)) || 
            (
                !empty($formType) &&
                !empty($formTypeInter) && 
                in_array('SixMWTTableFector', $formTypeInter)
            )
        ){
            $getSixmwtsFectorsDB   = $this->patientSixmwtsObj->getSixmwtsTableFectorsData($patientSixmwtID);
            $sixmwtsFectorsWithKey = $this->utilityLibObj->changeArrayKey($getSixmwtsFectorsDB, 'fector_id');
        
            $sixmwtTableFector    = $this->staticDataObj->getSixMinutWalkTestTableFector();

            foreach ($sixmwtTableFector as $fectorKey => $fectorValue) {
                $fectorIdForKey1 = $fectorValue['sixmwt_fector_type_key_1'];
                $fectorIdForKey2 = $fectorValue['sixmwt_fector_type_key_2'];
 
                if(array_key_exists($fectorIdForKey1, $sixmwtsFectorsWithKey)){
                    $sixmwtTableFector[$fectorKey]['sixmwt_before_sixmwt_'.$fectorIdForKey1] = $sixmwtsFectorsWithKey[$fectorIdForKey1]['before_sixmwt'];
                    $sixmwtTableFector[$fectorKey]['sixmwt_after_sixmwt_'.$fectorIdForKey1]  = $sixmwtsFectorsWithKey[$fectorIdForKey1]['after_sixmwt'];
                }
                if(array_key_exists($fectorIdForKey2, $sixmwtsFectorsWithKey)){
                    $sixmwtTableFector[$fectorKey]['sixmwt_before_sixmwt_'.$fectorIdForKey2] = $sixmwtsFectorsWithKey[$fectorIdForKey2]['before_sixmwt'];
                    $sixmwtTableFector[$fectorKey]['sixmwt_after_sixmwt_'.$fectorIdForKey2]  = $sixmwtsFectorsWithKey[$fectorIdForKey2]['after_sixmwt'];
                }
            }
            $finalCheckupRecords['SixMWTTableFector'] = $sixmwtTableFector;
        }
        
        if((empty($formType)) || 
            (
                !empty($formType) &&
                !empty($formTypeInter) && 
                in_array('VisitTreatmentFectors', $formTypeInter)
            )
        ){
            $visitTreatmentFectors = $this->visitModelObj->getVisitTreatmentFectors($visitId);
            $finalCheckupRecords['VisitTreatmentFectors'] = array_chunk(json_decode(json_encode($visitTreatmentFectors)), 2);
        }

        if(isset($finalCheckupRecords['AbgInvestigationFector']) && isset($finalCheckupRecords['AbgInvestigationFectorDate'])){
            $finalCheckupRecords['AbgInvestigationFector']['fields'] = array_merge($finalCheckupRecords['AbgInvestigationFectorDate']['fields'],$finalCheckupRecords['AbgInvestigationFector']['fields']);
            unset($finalCheckupRecords['AbgInvestigationFectorDate']);
        }

        //  GET Visit and Patient General Information
        if(empty($formType)){
          $finalCheckupRecords['PatientInformation'] = $this->visitModelObj->getVisitAndPatientInfo($visitId, $patientId);
        }

        if(!empty($formTypeInterExtra) && in_array('FiberopticBronchoscopy',$formTypeInterExtra)){
            $fiberopticBronchoscopyTableFector    = $this->staticDataObj->getFiberopticBronchoscopyType();
            $tempData = [];
            $values = $patientFiberopticBronchoscopy;
            $valuesType = $patientFiberopticBronchoscopyType;
            foreach ($fiberopticBronchoscopyTableFector as $key => $value) {
                $temp = [];
                $encryptType = $this->securityLibObj->encrypt($value['type']);
                $encryptId = $this->securityLibObj->encrypt($value['id']);
                $suggestiveValue = 'suggestive_value_'.$encryptType.'_'.$encryptId;
                $temp['name'] = $suggestiveValue;
                $temp['title'] = $value['value'];
                $temp['id'] = $value['id'];
                $temp['type'] = $value['type'];
                $options = array_map(function($row) use($values,$valuesType,$value){
                    $row['title'] = $row['value'];

                if($value['type'] != '1' ){
                    $row['value'] = !empty($values) && isset($values[$value['id'].'_'.$value['type']]['pfbd_per_suggestive']) ? $values[$value['id'].'_'.$value['type']]['pfbd_per_suggestive'] : '';
                    $row['custom_value'] = !empty($values) && isset($values[$value['id'].'_'.$value['type']]['pfbd_value']) && !empty($values[$value['id'].'_'.$value['type']]['pfbd_value']) ? $this->securityLibObj->decrypt($values[$value['id'].'_'.$value['type']]['pfbd_value']) : '';
                }else{
                    $row['value'] = !empty($valuesType) && isset($valuesType[$value['id'].'_'.$value['type'].'_'.$row['id']]['pfbd_per_suggestive']) ? $valuesType[$value['id'].'_'.$value['type'].'_'.$row['id']]['pfbd_per_suggestive'] : '';
                    $row['custom_value'] = '';
                }
                $row['id'] = $this->securityLibObj->encrypt($row['id']);

                    return $row;

                }, $value['option']); 
                $temp['option'] = $options;
                $tempData[$value['type']][] = $temp; 
            }
            $finalCheckupRecords['FiberopticBronchoscopy'] = $tempData;

        }        

        if(isset($finalCheckupRecords['SurgicalLungBiopsy']) && isset($finalCheckupRecords['SurgicalLungBiopsyDate'])){
            $finalCheckupRecords['SurgicalLungBiopsy']['fields'] = array_merge($finalCheckupRecords['SurgicalLungBiopsyDate']['fields'],$finalCheckupRecords['SurgicalLungBiopsy']['fields']);
             $finalCheckupRecords['SurgicalLungBiopsy']['data'] = array_merge($finalCheckupRecords['SurgicalLungBiopsyDate']['data'],$finalCheckupRecords['SurgicalLungBiopsy']['data']);

              $finalCheckupRecords['SurgicalLungBiopsy']['handlers'] = array_merge($finalCheckupRecords['SurgicalLungBiopsyDate']['handlers'],$finalCheckupRecords['SurgicalLungBiopsy']['handlers']);
            unset($finalCheckupRecords['SurgicalLungBiopsyDate']);
        }

        if(isset($finalCheckupRecords['HRCT']) && isset($finalCheckupRecords['HRCTDate']) && isset($finalCheckupRecords['HRCTEXTRA'])){
            $finalCheckupRecords['HRCT']['fields'] = array_merge($finalCheckupRecords['HRCT']['fields'],$finalCheckupRecords['HRCTDate']['fields'],$finalCheckupRecords['HRCTEXTRA']['fields']);
             $finalCheckupRecords['HRCT']['data'] = array_merge($finalCheckupRecords['HRCT']['data'],$finalCheckupRecords['HRCTDate']['data'],$finalCheckupRecords['HRCTEXTRA']['data']);

              $finalCheckupRecords['HRCT']['handlers'] = array_merge($finalCheckupRecords['HRCT']['handlers'],$finalCheckupRecords['HRCTDate']['handlers'],$finalCheckupRecords['HRCTEXTRA']['handlers']);
            unset($finalCheckupRecords['HRCTDate']);
            unset($finalCheckupRecords['HRCTEXTRA']);
        }

        /*if(isset($finalCheckupRecords['VisitsVitalsFormFectorWeight'])&& isset($finalCheckupRecords['VisitsVitalsFormFector'])){
            $finalCheckupRecords['VisitsVitalsFormFector']['fields'] = array_merge($finalCheckupRecords['VisitsVitalsFormFectorWeight']['fields'],$finalCheckupRecords['VisitsVitalsFormFector']['fields']);
             $finalCheckupRecords['VisitsVitalsFormFector']['data'] = array_merge($finalCheckupRecords['VisitsVitalsFormFectorWeight']['data'],$finalCheckupRecords['VisitsVitalsFormFector']['data']);

              $finalCheckupRecords['VisitsVitalsFormFector']['handlers'] = array_merge($finalCheckupRecords['VisitsVitalsFormFectorWeight']['handlers'],$finalCheckupRecords['VisitsVitalsFormFector']['handlers']);

              $finalCheckupRecords['VisitsVitalsFormFector']['handlerData'] = array_merge($finalCheckupRecords['VisitsVitalsFormFectorWeight']['handlerData'],$finalCheckupRecords['VisitsVitalsFormFector']['handlerData']);

              $finalCheckupRecords['VisitsVitalsFormFector']['handlerName'] = array_merge($finalCheckupRecords['VisitsVitalsFormFectorWeight']['handlerName'],$finalCheckupRecords['VisitsVitalsFormFector']['handlerName']);

              unset($finalCheckupRecords['VisitsVitalsFormFectorWeight']);

        }*/

        $getVisitStatus = $this->visitModelObj->checkIfRecordExist('patients_visits', 'status', ['visit_id' => $visitId], 'get');
        $finalCheckupRecords['visit_status'] = $getVisitStatus[0]->status;
        return $this->resultResponse(
                Config::get('restresponsecode.SUCCESS'), 
                $finalCheckupRecords, 
                [],
                trans('Visits::messages.new_visit_factor_get_data_successfull'),
                $this->http_codes['HTTP_OK']
            );
    }

    /**
     * @DateOfCreation        13 june 2018
     * @ShortDescription      This function is responsible for insert Patient Data 
     * @param                 Array $request   
     * @return                Array of status and message
     */    
    public function getCheckBoxOption($type, $options = [], $extraData = [])
    {
        $returnResponse = '';
        $finalOptionArr = [];
        switch($type){
            case 'option':
                if(!empty($options)){
                    foreach ($options as $key => $value) {
                        $array = [];
                        $array['value'] = (string) $key;
                        $array['label'] = $value;
                        $finalOptionArr[] = $array;
                    }   
                }
            break;
        }
            
        return $finalOptionArr;
    }

    /**
     * @DateOfCreation        6 July 2018
     * @ShortDescription      This function is responsible to get disease extra factor
     * @return                Array of status and message
     */
    public function getDiagnosisExtraFectors($diseaseId, $diseaseName, $visitDiagnosisId = NULL){
        
        $visitDiagnosisID = !empty($visitDiagnosisId) ? $this->securityLibObj->decrypt($visitDiagnosisId) : $visitDiagnosisId;
        $diagnosisExtraData = $this->diagnosisObj->getDiagnosisExtraFectors($diseaseId, $diseaseName, $visitDiagnosisID);
        $extraFectorsArr = [];
        if(!empty($diagnosisExtraData)){
            foreach ($diagnosisExtraData as $extraFectors) {
                if($extraFectors->info_type == 'text'){
                    $extraFectorsArr[] = [
                        'id'            => $extraFectors->dei_id, 
                        'fector_title'  => $extraFectors->info_title,
                        'value'         => $extraFectors->diagnosis_fector_value,
                        'field_type'    => 'text',
                        'name'          => 'extra_info_fector_'.$extraFectors->dei_id.'_'.$diseaseId,
                        'options'       => $extraFectors->extraInfoOptions
                    ];
                } else if($extraFectors->info_type == 'checkbox'){
                    $extraFectorsArr[] = [
                        'id'            => $extraFectors->dei_id, 
                        'fector_title'  => $extraFectors->info_title,
                        'value'         => [(string) $extraFectors->diagnosis_fector_value],
                        'field_type'    => 'checkbox',
                        'name'          => 'extra_info_fector_'.$extraFectors->dei_id.'_'.$diseaseId,
                        'options'       => $extraFectors->extraInfoOptions
                    ];
                }               
            }
        } 

        return $extraFectorsArr;
    }

    /**
     * @DateOfCreation        6 July 2018
     * @ShortDescription      This function is responsible to save and update visit form data
     * @return                Array of status and message
     */
    function add_edit(Request $request){
        $requestData = $this->getRequestData($request);

        $postData = [];

        $userId         = (in_array($request->user()->user_type, Config::get('constants.USER_TYPE_STAFF'))) ? $request->user()->created_by : $request->user()->user_id;
        $resourceType   = $requestData['resource_type'];
        $ipAddress      = $requestData['ip_address'];
        $patientId      = $this->securityLibObj->decrypt($requestData['pat_id']);
        $visitId        = $this->securityLibObj->decrypt($requestData['visit_id']);

        foreach($requestData as $dataKey => $dataValue){

            if(stripos($dataKey, 'requirement_fector_id_') !== false){
                $fectorId = explode('requirement_fector_id_', $dataKey)[1];

                if($this->securityLibObj->decrypt($fectorId) == Config::get('dataconstants.TREATMENT_FECTOR_VACCINE')){ // SAVE SIMPLE CHECKBOX VALUE AS COMMA SEPARATED 
                    $dataValue = $this->arrayToStringVal($dataValue, true);
                }else{
                    $dataValue = $this->arrayToStringVal($dataValue);                    
                }

                $postData['treatment_requirement'][] = [
                    'fector_id'                 => $this->securityLibObj->decrypt($fectorId),
                    'fector_value'              => $dataValue,
                    'pat_id'                    => $patientId,
                    'visit_id'                  => $visitId,
                    'ip_address'                => $ipAddress,
                    'resource_type'             => $resourceType
                ];                
            }

            $dataValue = $this->arrayToStringVal($dataValue);
            if(stripos($dataKey, 'date_of_diagnosis_') !== false){
                $explodeDataKey = explode('date_of_diagnosis_', $dataKey);
                $diseaseId      = $explodeDataKey[1];
                
                $dateOfDiagnosis= !empty($dataValue) ? $this->dateTimeLibObj->covertUserDateToServerType($dataValue,'dd/mm/YY','Y-m-d')['result'] : $dataValue;
                $postData['diagnosis_info'][] = [
                    'disease_id'        => $this->securityLibObj->decrypt($diseaseId),
                    'date_of_diagnosis' => $dateOfDiagnosis,
                    'pat_id'            => $patientId,
                    'visit_id'          => $visitId,
                    'ip_address'        => $ipAddress,
                    'resource_type'     => $resourceType,
                ];
            }

            if(stripos($dataKey, 'extra_info_fector_') !== false){
                $fectorAndDiseaseId = explode('extra_info_fector_', $dataKey)[1];
                $explodeFectorAndDiseaseId = explode('_', $fectorAndDiseaseId);
                $fectorId  = $this->securityLibObj->decrypt($explodeFectorAndDiseaseId[0]);
                $diseaseId = $this->securityLibObj->decrypt($explodeFectorAndDiseaseId[1]);
                
                $postData['diagnosis_extra_info'][] = [
                    'diagnosis_fector_key'      => $fectorId,
                    'disease_id'                => $diseaseId,
                    'diagnosis_fector_value'    => $dataValue,
                    'pat_id'                    => $patientId,
                    'visit_id'                  => $visitId,
                    'ip_address'                => $ipAddress,
                    'resource_type'             => $resourceType
                ];
            } 

            // Diagnosis extra information from diagnosis form
            if(stripos($dataKey, 'diagnosis_fector_value_') !== false){
                $fectorAndDiseaseId = explode('diagnosis_fector_value_', $dataKey)[1];
                $explodeFectorAndDiseaseId = explode('_', $fectorAndDiseaseId);
                $diseaseId = $this->securityLibObj->decrypt($explodeFectorAndDiseaseId[0]);
                $fectorId  = $this->securityLibObj->decrypt($explodeFectorAndDiseaseId[1]);
                
                $postData['diagnosis_from_extra_info'][] = [
                    'diagnosis_fector_key'      => $fectorId,
                    'disease_id'                => $diseaseId,
                    'diagnosis_fector_value'    => $dataValue,
                    'pat_id'                    => $patientId,
                    'visit_id'                  => $visitId,
                    'ip_address'                => $ipAddress,
                    'resource_type'             => $resourceType
                ];
            }           

            if(stripos($dataKey, 'physical_examination_fector_') !== false){
                $fectorId = explode('physical_examination_fector_', $dataKey)[1];

                $postData['physical_examinations'][] = [
                    'fector_id'     => $this->securityLibObj->decrypt($fectorId ),
                    'fector_value'  => $dataValue,
                    'pat_id'        => $patientId,
                    'visit_id'      => $visitId,
                    'ip_address'    => $ipAddress,
                    'resource_type' => $resourceType
                ];
            }

            if(stripos($dataKey, 'vitals_fector_') !== false){
                $fectorId = explode('vitals_fector_', $dataKey)[1];
                $postData['vitals'][] = [
                    'fector_id'     => $this->securityLibObj->decrypt($fectorId),
                    'fector_value'  => $dataValue,
                    'pat_id'        => $patientId,
                    'visit_id'      => $visitId,
                    'ip_address'    => $ipAddress,
                    'resource_type' => $resourceType
                ];
            }

            if(stripos($dataKey, 'changes_in_fector_') !== false){
                $fectorId = explode('changes_in_fector_', $dataKey)[1];

                $postData['visits_changes_in'][] = [
                    'fector_id'     => $this->securityLibObj->decrypt($fectorId),
                    'fector_value'  => $dataValue,
                    'pat_id'        => $patientId,
                    'visit_id'      => $visitId,
                    'ip_address'    => $ipAddress,
                    'resource_type' => $resourceType
                ];
            }

            if(stripos($dataKey, 'sixmwt_before_sixmwt_') !== false){
                $fectorId = explode('sixmwt_before_sixmwt_', $dataKey)[1];
                $fectorId = $this->securityLibObj->decrypt($fectorId);
                
                if($fectorId == 1 || $fectorId == 3){
                    $fectorType = 1;
                }else{
                    $fectorType = 2;                    
                }

                $postData['patient_sixmwt_fectors'][$fectorId][] = [
                    'fector_id'     => $fectorId,
                    'fector_type'   => $fectorType,
                    'pat_id'        => $patientId,
                    'visit_id'      => $visitId,
                    'ip_address'    => $ipAddress,
                    'resource_type' => $resourceType,
                    'before_sixmwt' => $dataValue,                  
                ];
            }

            if(stripos($dataKey, 'sixmwt_after_sixmwt_') !== false){
                $fectorId = explode('sixmwt_after_sixmwt_', $dataKey)[1];
                $fectorId = $this->securityLibObj->decrypt($fectorId);
                
                if($fectorId == 1 || $fectorId == 3){
                    $fectorType = 1;
                }else{
                    $fectorType = 2;                    
                }
                $postData['patient_sixmwt_fectors'][$fectorId][] = [
                    'fector_id'     => $fectorId,
                    'fector_type'   => $fectorType,
                    'pat_id'        => $patientId,
                    'visit_id'      => $visitId,
                    'ip_address'    => $ipAddress,
                    'resource_type' => $resourceType,
                    'after_sixmwt'  => $dataValue,
                ];
            }

            if(stripos($dataKey, 'spirometries_fector_pre_value_') !== false){
                $fectorId = explode('spirometries_fector_pre_value_', $dataKey)[1];
                $fectorId = $this->securityLibObj->decrypt($fectorId);
                
                $postData['spirometry_fectors'][$fectorId][] = [
                    'fector_id'         => $fectorId,
                    'fector_pre_value'  => $dataValue,
                ];
            }

            if(stripos($dataKey, 'spirometries_fector_post_value_') !== false){
                $fectorId = explode('spirometries_fector_post_value_', $dataKey)[1];
                $fectorId = $this->securityLibObj->decrypt($fectorId);
                
                $postData['spirometry_fectors'][$fectorId][] = [
                    'fector_id'         => $fectorId,
                    'fector_post_value'  => $dataValue,
                ];
            }

            // Investigation Report Description
            if(stripos($dataKey, 'report_description_') !== false){
                $fectorId = explode('report_description_', $dataKey)[1];
                $fectorId = $this->securityLibObj->decrypt($fectorId);
                
                $postData['investigation_report'][$fectorId][] = [
                    'report_type'        => $fectorId,
                    'report_description' => $dataValue,
                ];
            }
            // Investigation Report File
            if(stripos($dataKey, 'report_file_') !== false){
                $fectorId = explode('report_file_', $dataKey)[1];
                $fectorId = $this->securityLibObj->decrypt($fectorId);
                
                $postData['investigation_report'][$fectorId][] = [
                    'report_type' => $fectorId,
                    'report_file' => $dataValue,
                ];
            }

            // Hospitalization hospitalizations_extra_info data
            if(stripos($dataKey, 'hospitalization_diagnosis_details_') !== false){
                $fectorId = explode('hospitalization_diagnosis_details_', $dataKey)[1];
                $fectorId = $this->securityLibObj->decrypt($fectorId);

                $postData['hospitalizations_extra_info'][$fectorId][] = [
                    'hospitalization_diagnosis_details'  => $dataValue,
                    'hospitalization_fector_id'  => $fectorId,
                ];
            }

            if(stripos($dataKey, 'hospitalization_date_') !== false){
                $fectorId = explode('hospitalization_date_', $dataKey)[1];
                $fectorId = $this->securityLibObj->decrypt($fectorId);
                
                $postData['hospitalizations_extra_info'][$fectorId][] = [
                    'hospitalization_date'  => $dataValue,
                    'hospitalization_fector_id'  => $fectorId,
                ];
            }

            if(stripos($dataKey, 'hospitalization_duration_') !== false && stripos($dataKey, 'hospitalization_duration_unit_') === false){
                $fectorId = explode('hospitalization_duration_', $dataKey)[1];
                $fectorId = $this->securityLibObj->decrypt($fectorId);
                
                $postData['hospitalizations_extra_info'][$fectorId][] = [
                    'hospitalization_duration'  => $dataValue,
                    'hospitalization_fector_id'  => $fectorId,
                ];
            }

            if(stripos($dataKey, 'hospitalization_duration_unit_value_') !== false){
                $fectorId = explode('hospitalization_duration_unit_value_', $dataKey)[1];
                $fectorId = $this->securityLibObj->decrypt($fectorId);
                
                $postData['hospitalizations_extra_info'][$fectorId][] = [
                    'hospitalization_duration_unit'  => $dataValue,
                    'hospitalization_fector_id'  => $fectorId,
                ];
            }
            // Hospitalization hospitalizations_extra_info data end here
            
            // Treatment 
            if(stripos($dataKey, 'treatment_start_date_') !== false){
                $medicineId = explode('treatment_start_date_', $dataKey)[1];
                $medicineId = $this->securityLibObj->decrypt($medicineId);
                
                $postData['treatments'][$medicineId][] = [
                    'medicine_id'           => $medicineId,
                    'treatment_start_date'  => !empty($dataValue) ? $this->dateTimeLibObj->covertUserDateToServerType($dataValue, 'dd/mm/YY', 'Y-m-d')['result'] : NULL,
                ];
            }
            if(stripos($dataKey, 'treatment_end_date_') !== false){
                $medicineId = explode('treatment_end_date_', $dataKey)[1];
                $medicineId = $this->securityLibObj->decrypt($medicineId);
                
                $postData['treatments'][$medicineId][] = [
                    'medicine_id'         => $medicineId,
                    'treatment_end_date'  => !empty($dataValue) ? $this->dateTimeLibObj->covertUserDateToServerType($dataValue, 'dd/mm/YY', 'Y-m-d')['result'] : NULL,
                ];
            }
            // Treatment END
        }

        $generalInfo = [
            'pat_id'        => $patientId,
            'visit_id'      => $visitId,
            'ip_address'    => $ipAddress,
            'resource_type' => $resourceType,
        ];

        // Calculate BMI if form submitted from initial visit page
        if(isset($requestData['submitted_from']) && $requestData['submitted_from'] == Config::get('dataconstants.SUBMITTED_FROM_INITIAL_VISIT')){
            $weightFectorId = $this->staticDataObj->getWeight()[0]['id'];
            $heightFectorId = $this->staticDataObj->getHeight()[0]['id'];
            $bmiFectorId    = $this->staticDataObj->getBmi()[0]['id'];

            $getPatientHeightRecord = $this->physicalExaminationsObj->getPatientPhysicalExaminationsByFactorIdPatientIdAndVisitIds($patientId, $visitId, $heightFectorId);

            if(!empty($getPatientHeightRecord)){
                $heightInDB = $getPatientHeightRecord[0]->datavalue;
                $newWeight  = $requestData['physical_examination_fector_'.$this->securityLibObj->encrypt($weightFectorId)];
                
                if(!empty($heightInDB)){
                    $BMI = $this->utilityLibObj->calculateBMI($newWeight, $heightInDB);

                    $postData['physical_examinations'][] = [
                        'fector_id'     => $bmiFectorId,
                        'fector_value'  => number_format($BMI, 2, '.', ''),
                        'pat_id'        => $patientId,
                        'visit_id'      => $visitId,
                        'ip_address'    => $ipAddress,
                        'resource_type' => $resourceType,
                    ];
                }
            }
        }

        if(array_key_exists('hostpitalization_cardiac_myocardial_infarction', $requestData)){
            $postData['hospitalizations'] = [
                'hostpitalization_cardiac_myocardial_infarction' => $requestData['hostpitalization_cardiac_myocardial_infarction'],
                'hospitalization_respiratory'   => $requestData['hospitalization_respiratory'],
                'hospitalization_status'        => $this->arrayToStringVal($requestData['hospitalization_status']),
                'hospitalization_how_many'      => $requestData['hospitalization_how_many'],
                'hospitalization_why'           => $requestData['hospitalization_why'],
                'date_of_hospitalization'       => !empty($requestData['date_of_hospitalization']) ? $this->dateTimeLibObj->covertUserDateToServerType($requestData['date_of_hospitalization'],'dd/mm/YY','Y-m-d')['result'] : NULL,
                'pat_id'                        => $patientId,
                'visit_id'                      => $visitId,
                'ip_address'                    => $ipAddress,
                'resource_type'                 => $resourceType
            ];
        }

        if(array_key_exists('patient_death_status', $requestData)){
            $deathStatus  = $this->arrayToStringVal($requestData['patient_death_status']);
            $causeOfDeath = $this->arrayToStringVal($requestData['cause_of_death']);
            $postData['patients_death_info'] = [
                'patient_death_status'  => !empty($deathStatus) ? $deathStatus : NULL,
                'date_of_death'         => !empty($requestData['date_of_death']) ? $this->dateTimeLibObj->covertUserDateToServerType($requestData['date_of_death'],'dd/mm/YY','Y-m-d')['result'] : NULL,
                'cause_of_death'        => !empty($causeOfDeath) ? $causeOfDeath : NULL,
                'pat_id'                => $patientId ,
                'visit_id'              => $visitId,
                'ip_address'            => $ipAddress,
                'resource_type'         => $resourceType
            ];
        }

        if(array_key_exists('weight', $requestData)){
            $postData['investigation'] = [
                'weight'        => $requestData['weight'],
                'height'        => $requestData['height'],
                'bmi'           => $requestData['bmi'],
                'pat_id'        => $patientId ,
                'visit_id'      => $visitId,
                'ip_address'    => $ipAddress,
                'resource_type' => $resourceType
            ];
        }

        if(array_key_exists('pulmonary_function_test_status', $requestData)){
            $postData['pulmonary_function_test'] = [
                'pulmonary_function_test_status' => $this->arrayToStringVal($requestData['pulmonary_function_test_status'])
            ];

            $postData['pulmonary_function_test'] = array_merge($postData['pulmonary_function_test'], $generalInfo);
        }

        // SLEEP STUDY
        if(array_key_exists('investigation_ahi', $requestData) ){
            $studyData = [
                            'investigation_ahi'         => $requestData['investigation_ahi'],
                            'investigation_ri'          => $requestData['investigation_ri'],
                            'investigation_conclusion'  => $requestData['investigation_conclusion']
                        ];

            $postData['sleep_study'] = array_merge($studyData, $generalInfo);
        }

        // Investigation Reports data
        $dataInvestigationReport['investigation_report'] = [];
        if(array_key_exists('investigation_report', $postData) && !empty($postData['investigation_report'])){
            foreach ($postData['investigation_report'] as $fectorId => $fectorsData) {
                $dataInvestigationReport['investigation_report'][] = array_merge ($fectorsData[0], $fectorsData[1], $generalInfo);
            }
        }

        if(array_key_exists('visit_status', $requestData) ){
            $postData['visit_status'] = [
                'status'                          => Config::get('constants.VISIT_COMPLETED'),
                'ip_address'                      => $ipAddress,
                'resource_type'                   => $resourceType,
                'pat_id'                          => $patientId,
                'visit_id'                        => $visitId,
            ];
        }
        
        $dataSixmwtFectors['patient_sixmwt_fectors'] = [];
        if(array_key_exists('patient_sixmwt_fectors', $postData) && !empty($postData['patient_sixmwt_fectors'])){
            foreach ($postData['patient_sixmwt_fectors'] as $fectorId => $fectorsData) {
                $dataSixmwtFectors['patient_sixmwt_fectors'][] = array_merge ($fectorsData[0], $fectorsData[1]);
            }
        }

        if(array_key_exists('sixmwt_date', $requestData)){
            $postData['patient_sixmwts'] = [
                'sixmwt_date'   => $requestData['sixmwt_date'],
                'sixmwts_status'=> $this->arrayToStringVal($requestData['sixmwts_status']),
                'ip_address'    => $ipAddress,
                'resource_type' => $resourceType,
                'pat_id'        => $patientId,
                'visit_id'      => $visitId,
            ];
        }

        if(array_key_exists('spirometry_date', $requestData)){
            $postData['spirometries'] = [
                'spirometry_date'   => $requestData['spirometry_date'],
                'ip_address'    => $ipAddress,
                'resource_type' => $resourceType,
                'pat_id'        => $patientId,
                'visit_id'      => $visitId,
            ];
        } else {
            $postData['spirometries'] = array_merge(['spirometry_date' => date('d/m/Y')], $generalInfo);
        }

        $dataSpirometryFectors['spirometry_fectors'] = [];
        if(array_key_exists('spirometry_fectors', $postData) && !empty($postData['spirometry_fectors'])){
            foreach ($postData['spirometry_fectors'] as $fectorId => $fectorsData) {
                $dataSpirometryFectors['spirometry_fectors'][] = array_merge ($fectorsData[0], $fectorsData[1]);
            }
        }

        $treatmentFectors = [];
        if(array_key_exists('treatments', $postData) && !empty($postData['treatments'])){
            foreach ($postData['treatments'] as $fectorId => $fectorsData) {
                $treatmentFectors[] = array_merge ($fectorsData[0], $fectorsData[1], $generalInfo);
            }
        }

        $hospitalizationsExtraInfoFectors = [];
        if(array_key_exists('hospitalizations_extra_info', $postData) && !empty($postData['hospitalizations_extra_info'])){
            foreach ($postData['hospitalizations_extra_info'] as $fectorId => $fectorsData) {
                $hospitalizationsExtraInfoFectors[] = array_merge ($fectorsData[0], $fectorsData[1], $fectorsData[2], $fectorsData[3], $generalInfo);
            }       
        }

        if(array_key_exists('abg_date', $requestData)){
            $getAbgInvestigationFectorData = true;
        }
        if(array_key_exists('ptlb_date', $requestData)){
            $getThoracoscopicLungFectorData = true;
        }
        if(array_key_exists('pslb_date', $requestData)){
            $getsurgicalLungBiopsyData = true;
        }
        if(array_key_exists('pcx_date_recent', $requestData)){
            $getChestXrayData = true;
        }
        if(array_key_exists('phrct_date', $requestData)){
            $getHRCTData = true;
        }
        if(array_key_exists('puip_is_happen', $requestData)){
            $getUIPData = true;
        }
        if(array_key_exists('pfb_date', $requestData)){
            $getFiberopticBronchoscopyData = true;
        }

        try{
            $checkSuccessHispitalizationData            = true;
            $checkSuccessDiagnosisInfo                  = true;
            $checkSuccessPhysicalExaminations           = true;
            $checkSuccessPatientsDeathInfoData          = true;
            $checkSuccessTreatmentRequirement           = true;
            $checkSuccessPatientsVisitData              = true;
            $checkSuccessvitals                         = true;
            $checkSuccessvisitsChangesIn                = true;
            $checkSuccessDiagnosisExtraInfo             = true;
            $checkSuccessPatientSixmwt                  = true;
            $checkSuccessPatientSpirometry              = true;
            $checkSuccessInvestigation                  = true;
            $checkSuccessHospitalizationsExtraInfo      = true;
            $checkSuccessTreatments                     = true;
            $checkSuccessAbgInvestigationFectorData     = true;
            $checkSuccessThoracoscopicLungFectorData    = true;
            $checkSuccessSurgicalLungBiopsyData         = true;
            $checkSuccessvChestXrayData                 = true;
            $checkSuccessHRCTData                       = true;
            $checkSuccessUIPData                        = true;
            $checkSuccessFiberopticBronchoscopyData     = true;
            $checkSuccessPulmonaryFunctionTestData      = true;
            $checkSuccessVisitStatus                    = true;
            $checkSuccessSleepStudy                     = true;
            $checkSuccessInvestigationReport            = true;

            $errors = [];

            DB::beginTransaction();
            // Save Records
            if(isset($postData['visits_changes_in'])){
                $checkSuccessvisitsChangesIn = $this->saveVisitsChangesIn($postData['visits_changes_in']);
                $errors = $checkSuccessvisitsChangesIn  !== true ? true : [];
                // echo 'checkSuccessvisitsChangesIn+++++ '.$checkSuccessvisitsChangesIn.'<br>';                
            }

            if(isset($postData['pulmonary_function_test'])){
                $checkSuccessPulmonaryFunctionTestData = $this->savePulmonaryFunctionTestData($postData['pulmonary_function_test']);
                $errors = $checkSuccessPulmonaryFunctionTestData  !== true ? true : [];
                // echo 'checkSuccessPulmonaryFunctionTestData+++++ '.$checkSuccessPulmonaryFunctionTestData.'<br>';                
            }

            if(isset($postData['patients_visits'])){
                $checkSuccessPatientsVisitData = $this->savePatientsVisitData($postData['patients_visits'], $patientId, $visitId);
                $errors = $checkSuccessPatientsVisitData  !== true ? true : [];
                // echo 'checkSuccessPatientsVisitData+++++ '.$checkSuccessPatientsVisitData.'<br>';
            }

            if(isset($postData['visit_status'])){
                $checkSuccessVisitStatus = $this->visitModelObj->saveVisitStatus($postData['visit_status']);
                $errors = $checkSuccessVisitStatus  !== true ? true : [];
                // echo 'checkSuccessPatientsVisitData+++++ '.$checkSuccessPatientsVisitData.'<br>';
            }

            if(isset($postData['hospitalizations'])){
                $checkSuccessHispitalizationData = $this->saveHispitalizationData($postData['hospitalizations']);
                $errors = $checkSuccessHispitalizationData  !== true ? true : [];
                // echo 'checkSuccessHispitalizationData+++++ '.$checkSuccessHispitalizationData.'<br>';
            }

            $checkSuccessDiagnosisInfo = true;
            if(isset($postData['diagnosis_info'])){
                $checkSuccessDiagnosisInfo = $this->saveDiagnosisInfo($postData['diagnosis_info']);
                $errors = $checkSuccessDiagnosisInfo  !== true ? true : [];
                // echo 'checkSuccessDiagnosisInfo+++++ '.$checkSuccessDiagnosisInfo.'<br>';
            }

            $checkSuccessPhysicalExaminations = true;
            if(isset($postData['physical_examinations'])){
                $checkSuccessPhysicalExaminations = $this->savePhysicalExaminations($postData['physical_examinations']);
                $errors = $checkSuccessPhysicalExaminations  !== true ? true : [];
                // echo 'checkSuccessPhysicalExaminations+++++ '.$checkSuccessPhysicalExaminations.'<br>';
            }

            $checkSuccessPatientsDeathInfoData = true;
            if(isset($postData['patients_death_info'])){
                $checkSuccessPatientsDeathInfoData = $this->savePatientsDeathInfoData($postData['patients_death_info']);
                $errors = $checkSuccessPatientsDeathInfoData  !== true ? true : [];
                // echo 'checkSuccessPatientsDeathInfoData+++++ '.$checkSuccessPatientsDeathInfoData.'<br>';
            }

            if(isset($postData['treatment_requirement'])){
                $checkSuccessTreatmentRequirement = $this->saveTreatmentRequirement($postData['treatment_requirement']);
                $errors = $checkSuccessTreatmentRequirement  !== true ? true : [];
                // echo 'checkSuccessTreatmentRequirement+++++ '.$checkSuccessTreatmentRequirement.'<br>';
            }

            if(isset($postData['vitals'])){
                $checkSuccessvitals = $this->saveVitals($postData['vitals'], $userId);
                $errors = $checkSuccessvitals !== true ? true : [];
                // echo 'checkSuccessvitals+++++ '.$checkSuccessvitals.'<br>';
            }

            if(isset($postData['diagnosis_extra_info'])){
                $checkSuccessDiagnosisExtraInfo = $this->saveDiagnosisExtraInfo($postData['diagnosis_extra_info']);
                $errors = $checkSuccessDiagnosisExtraInfo !== true ? true : [];
                // echo 'checkSuccessDiagnosisExtraInfo+++++ '.$checkSuccessDiagnosisExtraInfo.'<br>';
            }

            if(isset($postData['diagnosis_from_extra_info'])){
                $checkSuccessDiagnosisExtraInfo = $this->saveDiagnosisExtraInfo($postData['diagnosis_from_extra_info']);
                $errors = $checkSuccessDiagnosisExtraInfo !== true ? true : [];
                // echo 'checkSuccessDiagnosisExtraInfo+++++ '.$checkSuccessDiagnosisExtraInfo.'<br>';
            }

            if(isset($postData['patient_sixmwts'])){
                $checkSuccessPatientSixmwt = $this->savePatientSixmwt($postData['patient_sixmwts'], $dataSixmwtFectors['patient_sixmwt_fectors']);
                $errors = $checkSuccessPatientSixmwt !== true ? true : [];
                // echo 'checkSuccessPatientSixmwt+++++ '.$checkSuccessPatientSixmwt.'<br>';
            }

            if(isset($postData['spirometries'])){
                $checkSuccessPatientSpirometry = $this->savePatientSpirometry($postData['spirometries'], $dataSpirometryFectors['spirometry_fectors']);
                $errors = $checkSuccessPatientSpirometry !== true ? true : [];
                // echo 'checkSuccessPatientSpirometry+++++ '.$checkSuccessPatientSpirometry.'<br>';
            }

            if(isset($postData['investigation'])){
                $checkSuccessInvestigation = $this->saveInvestigation($postData['investigation']);
                $errors = $checkSuccessInvestigation !== true ? true : [];
                // echo 'checkSuccessInvestigation+++++ '.$checkSuccessInvestigation.'<br>';
            }

            if(isset($postData['sleep_study'])){
                $checkSuccessSleepStudy = $this->saveSleepStudy($postData['sleep_study']);
                $errors = $checkSuccessSleepStudy !== true ? true : [];
                // echo 'checkSuccessSleepStudy+++++ '.$checkSuccessSleepStudy.'<br>';
            }

            if(!empty($dataInvestigationReport['investigation_report'])){
                $checkSuccessInvestigationReport = $this->saveInvestigationReport($dataInvestigationReport['investigation_report']);
                $errors = $checkSuccessInvestigationReport  !== true ? $checkSuccessInvestigationReport : [];
                // echo 'checkSuccessInvestigationReport+++++ '.$checkSuccessInvestigationReport.'<br>';                
            }

            $checkSuccessHospitalizationsExtraInfo = true;
            if(!empty($hospitalizationsExtraInfoFectors)){
                $checkSuccessHospitalizationsExtraInfo = $this->saveHospitalizationsExtraInfo($hospitalizationsExtraInfoFectors);
                $errors = $checkSuccessHospitalizationsExtraInfo !== true ? true : [];
                // echo 'checkSuccessHospitalizationsExtraInfo+++++ '.$checkSuccessHospitalizationsExtraInfo.'<br>';
            }

            if(!empty($treatmentFectors)){
                $checkSuccessTreatments = $this->saveTreatments($treatmentFectors);
                $errors = $checkSuccessTreatments !== true ? true : [];
                // echo 'checkSuccessTreatments+++++ '.$checkSuccessTreatments.'<br>';
            }

            if(isset($getAbgInvestigationFectorData) && $getAbgInvestigationFectorData){
                $checkSuccessAbgInvestigationFectorData = $this->saveAbgInvestigation($requestData,$visitId,$patientId);
                $errors = $checkSuccessAbgInvestigationFectorData !== true ? true : [];
            }

            if(isset($getThoracoscopicLungFectorData) && $getThoracoscopicLungFectorData){
                $checkSuccessThoracoscopicLungFectorData = $this->saveThoracoscopicLung($requestData,$visitId,$patientId);
                $errors = $checkSuccessThoracoscopicLungFectorData !== true ? true : [];
            }

            if(isset($getsurgicalLungBiopsyData) && $getsurgicalLungBiopsyData){
                $checkSuccessSurgicalLungBiopsyData = $this->surgicalLungBiopsyObj->saveSurgicalLungBiopsy($requestData,$visitId,$patientId);
                $errors = $checkSuccessSurgicalLungBiopsyData !== true ? true : [];
            }

            if(isset($getChestXrayData) && $getChestXrayData){
                $checkSuccessvChestXrayData = $this->chestXrayObj->saveChestXray($requestData,$visitId,$patientId);
                $errors = $checkSuccessvChestXrayData !== true ? true : [];
            }

            if(isset($getHRCTData) && $getHRCTData){
                $checkSuccessHRCTData = $this->hrctObj->saveHRCT($requestData,$visitId,$patientId);
                $errors = $checkSuccessHRCTData !== true ? true : [];
            }

            if(isset($getUIPData) && $getUIPData){
                $checkSuccessUIPData = $this->uipObj->saveUIP($requestData,$visitId,$patientId);
                $errors = $checkSuccessUIPData !== true ? true : [];
            }

            if(isset($getFiberopticBronchoscopyData) && $getFiberopticBronchoscopyData){
                $checkSuccessFiberopticBronchoscopyData = $this->fiberopticBronchoscopyObj->saveFiberopticBronchoscopy($requestData,$visitId,$patientId);
                $errors = $checkSuccessFiberopticBronchoscopyData !== true ? true : [];
            }

            // if( ($checkSuccessHispitalizationData && $checkSuccessDiagnosisInfo && $checkSuccessPhysicalExaminations && $checkSuccessPatientsDeathInfoData && $checkSuccessTreatmentRequirement && $checkSuccessPatientsVisitData && $checkSuccessvitals && $checkSuccessvisitsChangesIn && $checkSuccessDiagnosisExtraInfo && $checkSuccessPatientSixmwt && $checkSuccessPatientSpirometry && $checkSuccessInvestigation && $checkSuccessHospitalizationsExtraInfo && $checkSuccessTreatments && $checkSuccessAbgInvestigationFectorData && $checkSuccessThoracoscopicLungFectorData && $checkSuccessSurgicalLungBiopsyData && $checkSuccessvChestXrayData && $checkSuccessHRCTData && $checkSuccessUIPData && $checkSuccessFiberopticBronchoscopyData && $checkSuccessPulmonaryFunctionTestData && $checkSuccessVisitStatus && $checkSuccessSleepStudy && $checkSuccessInvestigationReport) === TRUE){
            if(empty($errors)){
                
                if(isset($requestData['booking_id']) && !empty($requestData['booking_id'])){
                    $this->bookingsObj->updateBookingState($this->securityLibObj->decrypt($requestData['booking_id']), Config::get('constants.BOOKING_COMPLETED'));
                }

                DB::commit();
                $dbCommitStatus = false;
                return $this->resultResponse(
                    Config::get('restresponsecode.SUCCESS'), 
                    [], 
                    [],
                    trans('Visits::messages.visits_add_update_success'), 
                    $this->http_codes['HTTP_OK']
                );
            } else {
                DB::rollback();
                return $this->resultResponse(
                    Config::get('restresponsecode.ERROR'), 
                    [], 
                    $errors,
                    trans('Visits::messages.visits_add_update_fail'), 
                    $this->http_codes['HTTP_OK']
                );
            }
        }catch (\Exception $ex) {
            DB::rollback();
            $eMessage = $this->exceptionLibObj->reFormAndLogException($ex,'VisitsController', 'add_edit');
            return $this->resultResponse(
                Config::get('restresponsecode.EXCEPTION'), 
                [], 
                [],
                $eMessage, 
                $this->http_codes['HTTP_OK']
            );
        }  
    }

    /**
     * @DateOfCreation        10 July 2018
     * @ShortDescription      This function is responsible to verify array input and change to string
     * @param                 array $data   
     * @return                boolean true / false
     */
    public function arrayToStringVal($data, $isAllowArrayVal = false){
        if(is_array($data) && !empty($data)){
            $dataValue = $data[0];

            if($isAllowArrayVal){
                foreach ($data as $key => $value) {
                   if(is_null($value) || $value == ''){
                        unset($data[$key]);
                   }
                }
                $dataValue = implode(',', $data);
            }
        } else if(is_array($data) && empty($data)){
            $dataValue = NULL;                    
        }else{
            $dataValue = !empty($data) ? $data : NULL;                    
        }
        return $dataValue;
    }

    /**
     * @DateOfCreation        10 July 2018
     * @ShortDescription      This function is responsible to call model function for save diagnosis info
     * @param                 array $data   
     * @return                boolean true / false
     */
    function saveDiagnosisInfo($data){
        return $this->visitModelObj->saveDiagnosisInfo($data);
    }

    /**
     * @DateOfCreation        10 July 2018
     * @ShortDescription      This function is responsible to call model function for save Hispitalization Data
     * @param                 array $data   
     * @return                boolean true / false
     */
    function saveHispitalizationData($data){
        return $this->visitModelObj->saveHispitalizationData($data);
    }
    
    /**
     * @DateOfCreation        10 July 2018
     * @ShortDescription      This function is responsible to call model function for save Diagnosis Extra Info
     * @param                 array $data   
     * @return                boolean true / false
     */
    public function saveDiagnosisExtraInfo($data){
        return $this->visitModelObj->saveDiagnosisExtraInfo($data);
    }

    /**
     * @DateOfCreation        10 July 2018
     * @ShortDescription      This function is responsible to call model function for save Patients Death Info Data
     * @param                 array $data   
     * @return                boolean true / false
     */
    public function savePatientsDeathInfoData($data){
        return $this->visitModelObj->savePatientsDeathInfoData($data);
    }

    /**
     * @DateOfCreation        10 July 2018
     * @ShortDescription      This function is responsible to call model function for save Physical Examinations
     * @param                 array $data   
     * @return                boolean true / false
     */
    public function savePhysicalExaminations($data){
        return $this->visitModelObj->savePhysicalExaminations($data);
    }

    /**
     * @DateOfCreation        10 July 2018
     * @ShortDescription      This function is responsible to call model function for save Patients Visit Data
     * @param                 array $data   
     * @return                boolean true / false
     */
    public function savePatientsVisitData($data){
        return $this->visitModelObj->savePatientsVisitData($data);   
    }

    /**
     * @DateOfCreation        10 July 2018
     * @ShortDescription      This function is responsible to call model function for save Treatment Requirement
     * @param                 array $data   
     * @return                boolean true / false
     */
    public function saveTreatmentRequirement($data){
        return $this->visitModelObj->saveTreatmentRequirement($data);
    }

    /**
     * @DateOfCreation        10 July 2018
     * @ShortDescription      This function is responsible to call model function for save Vitals Data
     * @param                 array $data   
     * @return                boolean true / false
     */
    public function saveVitals($data, $userId = null){
        return $this->visitModelObj->saveVitals($data, $userId);
    }

    /**
     * @DateOfCreation        10 July 2018
     * @ShortDescription      This function is responsible to call model function for save Visits Changes In Data
     * @param                 array $data   
     * @return                boolean true / false
     */
    public function saveVisitsChangesIn($data){
        return $this->visitModelObj->saveVisitsChangesIn($data);
    }

    /**
     * @DateOfCreation        10 July 2018
     * @ShortDescription      This function is responsible to call model function for save Investigation Data
     * @param                 array $data   
     * @return                boolean true / false
     */
    public function saveInvestigation($data){
        return $this->visitModelObj->saveInvestigation($data);
    }

    /**
     * @DateOfCreation        10 July 2018
     * @ShortDescription      This function is responsible to call model function for save Treatments Data
     * @param                 array $data   
     * @return                boolean true / false
     */
    public function saveTreatments($data){
        return $this->visitModelObj->saveTreatments($data);
    }

    /**
     * @DateOfCreation        10 July 2018
     * @ShortDescription      This function is responsible to call model function for save Patient Sixmwt Data
     * @param                 array $data   
     * @return                boolean true / false
     */
    public function savePatientSixmwt($patientSixmwtsData, $sixmwtFectorsData){
        return $this->visitModelObj->savePatientSixmwt($patientSixmwtsData, $sixmwtFectorsData);
    }

    /**
     * @DateOfCreation        10 July 2018
     * @ShortDescription      This function is responsible to call model function for save Patient Spirometry Data
     * @param                 array $data   
     * @return                boolean true / false
     */
    public function savePatientSpirometry($patientSpirometryData, $spirometryFectorsData){
        return $this->visitModelObj->savePatientSpirometry($patientSpirometryData, $spirometryFectorsData);
    }

    /**
     * @DateOfCreation        10 July 2018
     * @ShortDescription      This function is responsible to call model function for save Hospitalizations Extra Info
     * @param                 array $data   
     * @return                boolean true / false
     */
    public function saveHospitalizationsExtraInfo($hospitalizationsExtraInfoFectors){
        return $this->visitModelObj->saveHospitalizationsExtraInfo($hospitalizationsExtraInfoFectors);
    }

    /**
     * @DateOfCreation        26 July 2018
     * @ShortDescription      This function is responsible to call model function for save Pulmonary Function
     * @param                 array $data   
     * @return                boolean true / false
     */
    public function savePulmonaryFunctionTestData($pulmonaryFunctionData){
        return $this->pulmonaryFunctionTestObj->savePulmonaryFunctionTestData($pulmonaryFunctionData);
    }

    /**
     * @DateOfCreation        5 Oct 2018
     * @ShortDescription      This function is responsible to call model function for save Sleep Study
     * @param                 array $data   
     * @return                boolean true / false
     */
    public function saveSleepStudy($saveSleepStudyData){
        return $this->sleepStudyModelObj->checkAndUpdateSleepStudy($saveSleepStudyData);
    }

    /**
     * @DateOfCreation        5 Oct 2018
     * @ShortDescription      This function is responsible to call model function for save Investigation Report
     * @param                 array $data   
     * @return                boolean true / false
     */
    public function saveInvestigationReport($investigationReportData){
        if(!empty($investigationReportData)){
            foreach ($investigationReportData as $key => $value) {
                $error = false;
                $errors = [];
                
                if(is_object($value['report_file'])){
                    $validationData = [
                        'report_file' => 'max:4096|mimes:'.Config::get('constants.INVESTIGATION_REPORT_MIME_TYPE'),
                    ];

                    $validator = Validator::make($value, $validationData);
                    

                    if($validator->fails()) {
                        $error = true;
                        $errors = $validator->errors();
                    }
                    if($error){
                        return $errors;
                    }
                }
            }
        }

        return $this->investigationReportModelObj->checkAndUpdateInvestigationReport($investigationReportData);
    }

    /**
     * @DateOfCreation        12 July 2018
     * @ShortDescription      This function is responsible to get patient's all visits
     * @param                 array $data   
     * @return                array response
     */
    public function getPatientVisitList(Request $request){
        $requestData = $this->getRequestData($request);
        $requestData['user_id']   = (in_array($request->user()->user_type, Config::get('constants.USER_TYPE_STAFF'))) ? $request->user()->created_by : $request->user()->user_id;

        $patientVisitList = $this->visitModelObj->getPatientVisits($requestData);
        return $this->resultResponse(
            Config::get('restresponsecode.SUCCESS'), 
            $patientVisitList, 
            [],
            trans('Visits::messages.visits_list_get_data_successfull'),
            $this->http_codes['HTTP_OK']
        );
    }

    /**
     * @DateOfCreation        10 July 2018
     * @ShortDescription      This function is responsible to call model function for save Hospitalizations Extra Info
     * @param                 array $data   
     * @return                boolean true / false
     */
    public function saveAbgInvestigation($requestData,$visitId,$patientId){

        $formValuDataDate = $this->investigationAbgObj->getInvestigationAbgByVistID($visitId,$patientId,false);
        $formValuData = !empty($formValuDataDate) ? $this->utilityLibObj->changeArrayKey($formValuDataDate,'fector_id'): [];
        $formValuDataVisitId = !empty($formValuDataDate) ? $this->utilityLibObj->changeArrayKey($formValuDataDate,'visit_id'): [];
        $abgDate = $requestData['abg_date'];
        $ia_id = !empty($formValuDataVisitId) && isset($formValuDataVisitId[$visitId]) ? $formValuDataVisitId[$visitId]['ia_id']:'';

        $staticDataFactor = $this->staticDataObj->abgInvestigationFector();
        $staticDataFactor = !empty($staticDataFactor) ? $this->utilityLibObj->changeArrayKey($staticDataFactor,'id'): [];

        if(empty($abgDate)){
            $valueData= [];
             foreach ($staticDataFactor as $factorKey => $factorValue) {
                $valueData[] = isset($requestData[$factorValue['name']]) ? $requestData[$factorValue['name']] :'';
             }
            $valueData = array_filter($valueData);
            $abgDate = !empty($valueData) ? date('Y-m-d') :'';
        }else{
            $dateResponse = $this->dateTimeLibObj->covertUserDateToServerType($abgDate,'dd/mm/YY','Y-m-d');
            if($dateResponse['code']=='5000'){
                return false;
            }
            $abgDate = $dateResponse['result'];
        }

        if(empty($abgDate)){
            return true;
        }
        if(empty($ia_id)){
            $temp =[];
            $temp['pat_id'] = $patientId;
            $temp['visit_id'] = $visitId;
            $temp['abg_date'] = $abgDate;
            $temp['resource_type'] = $requestData['resource_type'];
            $temp['ip_address'] = $requestData['ip_address'];
            $responseData = $this->investigationAbgObj->saveAbgInvestigationDate($temp);
            if(!$responseData){
                return $responseData;
            }
            $ia_id = $responseData;
        }elseif(!empty($ia_id) && !empty($abgDate)){
            $temp =[];
            $temp['pat_id'] = $patientId;
            $temp['visit_id'] = $visitId;
            $temp['abg_date'] = $abgDate;
            $temp['resource_type'] = $requestData['resource_type'];
            $temp['ip_address'] = $requestData['ip_address'];
            $whereData = [];
            $whereData['ia_id'] = $ia_id;
            $whereData['pat_id'] = $patientId;
            $whereData['visit_id'] = $visitId;
            $responseData = $this->investigationAbgObj->updateAbgInvestigationDate($temp,$whereData);
            if(!$responseData){
                return $responseData;
            }
        }
        $insertData = [];
        
        foreach ($staticDataFactor as $factorKey => $factorValue) {
            $iaf_id = !empty($formValuData) && isset($formValuData[$factorKey]['iaf_id']) ? $formValuData[$factorKey]['iaf_id'] :'';
            $value = isset($requestData[$factorValue['name']]) ? $requestData[$factorValue['name']] :'';
            $temp =[];
            $temp['ia_id'] = $ia_id;
            $temp['fector_id'] = $factorKey;
            $temp['fector_value'] = $value;
            $temp['resource_type'] = $requestData['resource_type'];
            $temp['ip_address'] = $requestData['ip_address'];

            if(!empty($iaf_id)){
                $whereData = [];
                $whereData['iaf_id'] = $iaf_id;
                $whereData['ia_id'] = $ia_id;
                $responseData = $this->investigationAbgObj->updateAbgInvestigationFactor($temp,$whereData);
                if(!$responseData){
                    $dbstaus = false;
                    break;
                }else{
                    continue;
                }

            }elseif(!empty($value)){
               $insertData[] = $temp; 
            }
        }
        if(isset($dbstaus)){
            return false;
        }
        if(!empty($insertData)){
            $responseData = $this->investigationAbgObj->addAbgInvestigationFactor($insertData);
            return $responseData;
        }
        return true;
    }


    /**
     * @DateOfCreation        10 July 2018
     * @ShortDescription      This function is responsible to call model function for save saveThoracoscopicLung
     * @param                 array $data   
     * @return                boolean true / false
     */
    public function saveThoracoscopicLung($requestData,$visitId,$patientId){

        $formValuDataDate = $this->thoracoscopicLungObj->getThoracoscopicLungByVistID($visitId,$patientId,false);
        $formValuDataVisitId = !empty($formValuDataDate) ? $this->utilityLibObj->changeArrayKey($formValuDataDate,'visit_id'): [];
        $ptlbDate = $requestData['ptlb_date'];
        $ptlb_id = !empty($formValuDataVisitId) && isset($formValuDataVisitId[$visitId]) ? $formValuDataVisitId[$visitId]['ptlb_id']:'';

        $staticDataFactor = $this->staticDataObj->getThoracoscopicLungBiopsy();
        $staticDataFactor = !empty($staticDataFactor) ? $this->utilityLibObj->changeArrayKey($staticDataFactor,'id'): [];
        $valueData = [];
        foreach ($staticDataFactor as $factorKey => $factorValue) {
            $valueData[$factorValue['name']] = isset($requestData[$factorValue['name']]) ? $requestData[$factorValue['name']] :'';
            if($factorValue['field_type'] =='date' && !empty($valueData[$factorValue['name']])){
                $dateResponse = $this->dateTimeLibObj->covertUserDateToServerType($ptlbDate,'dd/mm/YY','Y-m-d');
                    if($dateResponse['code']=='5000'){
                        $dbstaus = false;
                        break;
                    }
                $valueData[$factorValue['name']] =$dateResponse['result'];
            }
            if(!empty($ptlb_id) && empty($valueData[$factorValue['name']])){
                $valueData[$factorValue['name']] = null;
            }
        }
        if(isset($dbstaus)){
            return false;
        }
        if(empty($ptlb_id) && empty(array_filter($valueData))){
            return true;
        }elseif(!empty($ptlb_id) && !empty($valueData)){
            $whereData = [];
            $whereData['visit_id'] = $visitId;
            $whereData['pat_id'] = $patientId;
            $whereData['ptlb_id'] = $ptlb_id;
            $valueData['resource_type'] = $requestData['resource_type'];
            $valueData['ip_address'] = $requestData['ip_address'];
            //$valueData = array_filter($valueData);
            $responseData = $this->thoracoscopicLungObj->updateThoracoscopicLungByVistID($valueData,$whereData);
            return $responseData;
        }elseif(empty($ptlb_id) && !empty($valueData)) {
            $valueData['resource_type'] = $requestData['resource_type'];
            $valueData['ip_address'] = $requestData['ip_address'];
            $valueData['visit_id'] = $visitId;
            $valueData['pat_id'] = $patientId;
            $valueData = array_filter($valueData);
           $responseData = $this->thoracoscopicLungObj->addThoracoscopicLungByVistID($valueData);
           if($responseData){
                return true;
            }
            return false;
        }
        return true;
    }

    /**
     * @DateOfCreation        09 Aug 2018
     * @ShortDescription      This function is responsible to get next visit appointment schedule time slot
     * @param                 array $data   
     * @return                array
     */
    public function getAppointmentTimeSlot(Request $request){
        $requestData    = $this->getRequestData($request);
        $userId         = $request->user()->user_id;
        $user_type      = $request->user()->user_type;
        $appointmentDate= !empty($requestData['appointmentDate']) ? $this->dateTimeLibObj->covertUserDateToServerType($requestData['appointmentDate'], 'dd/mm/YYYY', 'Y-m-d')['result'] : $requestData['appointmentDate'];
        
        
        $clinicId       = !empty($requestData['clinic_id']) ? $this->securityLibObj->decrypt($requestData['clinic_id']) : $requestData['clinic_id'];
        $timeSlots = $this->searchModelObj->doctorTimeSlotList($clinicId, $appointmentDate);
        if($timeSlots){
            return $this->resultResponse(
                    Config::get('restresponsecode.SUCCESS'), 
                    $timeSlots, 
                    [],
                    trans('Doctors::messages.doctors_clinic_detail'),
                    $this->http_codes['HTTP_OK']
                );
        }else{
            return $this->resultResponse(
                Config::get('restresponsecode.ERROR'), 
                ['date'=>$appointmentDate,'user_id'=>$this->securityLibObj->encrypt($userId)], 
                [],
                trans('Doctors::messages.doctors_clinic_error'),
                $this->http_codes['HTTP_OK']
            );
        }
    }

    /**
     * @DateOfCreation        8 Oct 2018
     * @ShortDescription      This function is responsible for view uploaded files with authentication check
     * @param                 $fileId, $fileType
     * @return                array
     */
    public function viewFile($fileId, $fileType){

        $storagPath  = Config::get('constants.STORAGE_MEDIA_PATH');
        $destination = Config::get('constants.DEFAULT_IMAGE_NAME');
        
        // IF VIEW FILE IS INVESTIGATION REPORT
        if($fileType == Config::get('dataconstants.FILE_TYPE_INVESTIGATION_REPORT')){
            $fileData = $this->investigationReportModelObj->getInvestigationReportDataById(['ir_id', 'report_file'], ['ir_id' => $this->securityLibObj->decrypt($fileId)]);

            $destination    = Config::get('constants.INVESTIGATION_REPORT_PATH');
            if(!empty($fileData) && !empty($fileData[0]->report_file) ){
                $path = $storagPath.$destination.$fileData[0]->report_file;
                $path = storage_path($path);

                if(!File::exists($path)){
                    $path = public_path(Config::get('constants.DEFAULT_IMAGE_PATH'));
                }

            }
            
            $filenewName    = File::name($path);
            $filenewName    .= '.'.File::extension($path);
            $type           = File::mimeType($path);
            $headers        = ['Content-Type: '.$type];
            
            return response()->file($path, $headers);            
        }
    }
}
