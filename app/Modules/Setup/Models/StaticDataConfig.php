<?php
namespace App\Modules\Setup\Models;
use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\HasApiTokens;
use App\Traits\Encryptable;
use Illuminate\Support\Facades\DB;
use App\Libraries\SecurityLib;
use App\Libraries\UtilityLib;
use App\Modules\Search\Models\Search;
use App\Modules\LaboratoryTests\Models\LaboratoryTests;
use App\Modules\Visits\Models\Allergies;
use Config;

/**
 * StaticDataConfig
 *
 * @package                ILD
 * @subpackage             StaticDataConfig
 * @category               Model
 * @DateOfCreation         11 june 2018
 * @ShortDescription       This Model to handle database operation with current table
                           City
 **/
class StaticDataConfig extends Model {

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

        // Init UtilityLib library object
        $this->utilityLibObj = new UtilityLib();

        // Init Allergies model object
        $this->allergiesModelObj = new Allergies();
    }

    /**
    *@ShortDescription Table for the Users.
    *
    * @var String
    */
    protected $table = '';
    
    // @var Array $encryptedFields
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
    * @DateOfCreation        22 June 2018
    * @ShortDescription      This function is responsible to get the StaticDataConfig  list
    * @return                object Array of StaticDataConfig records
    */
    public function getStaticDataConfigList()
    {   
        $checkupFactorData                  = $this->getCheckupFactorData();
        $checkupFactorDurationSelectData    = $this->getCheckupFactorDurationSelectData();
        $genderData                         = $this->getGenderData();
        $getStaffRole                       = $this->getStaffRole();
        $getAppointmentReason               = $this->getAppointmentReason();
        $titleData                          = $this->getTitleData();
        $yesNoData                          = $this->getYesNoData();
        $maritalStatusData                  = $this->getMaritalStatusData();
        $activeinactiveData                 = $this->getActiveInactiveData();
        $diseaseData                        = $this->getDiseaseData();
        $monthData                          = $this->getMonthData();
        $diseaseFoundTypeData               = $this->getDiseaseFoundTypeData();
        $inhaledTobaccoUseData              = $this->getInhaledTobaccoUseData();
        $doseMeasurementTypeData            = $this->getDoseMeasurementTypeData();
        $allergiesData                      = $this->getAllergiesData();
        $bloodGroupData                     = $this->getBloodGroupData();
        $generalCheckupDurationData         = $this->getGeneralCheckupDurationData();
        $domesticFactorConditionData        = $this->getDomesticFactorConditionData();
        $domesticFactorConditionTypeData    = $this->getDomesticFactorConditionTypeData();
        $getNewVisitFormFectorData          = $this->getNewVisitFormFectorData();
        $domesticFactorPatientResidentData  = $this->getDomesticFactorPatientResidentData();
        $socialAddictionKeyData             = $this->getSocialAddictionKeyData();
        $socialAddictionKeyUseData          = $this->getSocialAddictionUseKeyData();
        $familyRelationData                 = $this->getFamilyRelationData();
        $laboratoryTestData                 = $this->getLaboratoryTestData();
        $laboratoryTestTypeData             = $this->getLaboratoryTestTypeData();
        $labortyHepatitisOptionData         = $this->getlabortyHepatitisOptionData();
        $labortySputumAfbOptionData         = $this->getlabortySputumAfbOptionData();
        $posativeNegativeData               = $this->getPosativeNegativeData();
        $consultantImpressionData           = $this->getConsultantImpressionData();
        $consultantIdiopathicInterstitialIIPOptionData = $this->getConsultantIdiopathicInterstitialIIPOptionData();
        $consultantSuspectActiveInfectionOptionData    = $this->getConsultantSuspectActiveInfectionOptionData();
        $workEnvironmentYearOptionData      = $this->getWorkEnvironmentYearOptionData();
        $symptomsTestData                   = $this->getsymptomsTestData();

        $StaticDataConfig = [
                'checkup_factor'                                => $checkupFactorData,
                'checkup_factor_duration_select'                => $checkupFactorDurationSelectData,
                'gender'                                        => $genderData,
                'staffRole'                                     => $getStaffRole,
                'appointmentReason'                             => $getAppointmentReason,
                'title'                                         => $titleData,
                'yes_no_option'                                 => $yesNoData,
                'marital_status_option'                         => $maritalStatusData,
                'active_inactive_option'                        => $activeinactiveData,
                'disease_data'                                  => $diseaseData,
                'disease_found_type'                            => $diseaseFoundTypeData,
                'allergies'                                     => $allergiesData,
                'month'                                         => $monthData,
                'inhaled_tobacco_use'                           => $inhaledTobaccoUseData,
                'dose_measurement_Type'                         => $doseMeasurementTypeData,
                'blood_group'                                   => $bloodGroupData,
                'checkup_duration'                              => $generalCheckupDurationData,
                'domestic_factor_condition'                     => $domesticFactorConditionData,
                'domestic_factor_condition_type'                => $domesticFactorConditionTypeData,
                'new_visit_fectors'                             => $getNewVisitFormFectorData,
                'domestic_factor_condition_place'               => $domesticFactorPatientResidentData,
                'social_addiction_key'                          => $socialAddictionKeyData,
                'social_addiction_use_type'                     => $socialAddictionKeyUseData,
                'family_relation_type'                          => $familyRelationData,
                'laboratory_test'                               => $laboratoryTestData,
                'laboratory_test_type'                          => $laboratoryTestTypeData,
                'laboratory_test_hepatitis_option'              => $labortyHepatitisOptionData,
                'laboratory_test_sputum_afb_option'             => $labortySputumAfbOptionData,
                'posative_negative_option'                      => $posativeNegativeData,
                'consultant_impression_list'                    => $consultantImpressionData,
                'consultant_idiopathic_interstitial_iip'        => $consultantIdiopathicInterstitialIIPOptionData,
                'consultant_suspect_active_infection'           => $consultantSuspectActiveInfectionOptionData,
                'work_environment_year_option'                  => $workEnvironmentYearOptionData,
                'symptoms_test'                                 => $symptomsTestData,
                ];
        return $StaticDataConfig;
    }

    /**
     * @DateOfCreation        22 June 2018
     * @ShortDescription      This function is responsible to get the staticData for CheckupFactor list
     * @return                 Array of status and message
     */
    public function getCheckupFactorData(){
        $staticValue = [
            ['id' => 1, 'onlycheckbox' => 0, 'value' => trans('Setup::StaticDataConfigMessage.weight_loss')],
            ['id' => 2, 'onlycheckbox' => 0, 'value' => trans('Setup::StaticDataConfigMessage.difficulty_in_swallowing')],
            ['id' => 3, 'onlycheckbox' => 0, 'value' => trans('Setup::StaticDataConfigMessage.dry_eyes_or_dry_mouth')],
            ['id' => 4, 'onlycheckbox' => 0, 'value' => trans('Setup::StaticDataConfigMessage.rash_or_changes_in_skin')],
            ['id' => 5, 'onlycheckbox' => 0, 'value' => trans('Setup::StaticDataConfigMessage.oedema_on_legs')],
            ['id' => 6, 'onlycheckbox' => 0, 'value' => trans('Setup::StaticDataConfigMessage.blood_in_urine')],
            ['id' => 7, 'onlycheckbox' => 0, 'value' => trans('Setup::StaticDataConfigMessage.bruising_skin')],
            ['id' => 8, 'onlycheckbox' => 0, 'value' => trans('Setup::StaticDataConfigMessage.hand_ulcers')],
            ['id' => 9, 'onlycheckbox' => 0, 'value' => trans('Setup::StaticDataConfigMessage.mouth_ulcers')],
            ['id' => 10, 'onlycheckbox' => 0, 'value' => trans('Setup::StaticDataConfigMessage.chest_Pain')],
            ['id' => 11, 'onlycheckbox' => 0, 'value' => trans('Setup::StaticDataConfigMessage.joint_Pain')],
            ['id' => 12, 'onlycheckbox' => 0, 'value' => trans('Setup::StaticDataConfigMessage.symptoms_of_gastro-oesophagial_reflux_GERD')],
            ['id' => 13, 'onlycheckbox' => 1, 'value' => trans('Setup::StaticDataConfigMessage.indigestion')],
            ['id' => 14, 'onlycheckbox' => 1, 'value' => trans('Setup::StaticDataConfigMessage.heartburn')],
            ['id' => 15, 'onlycheckbox' => 1, 'value' => trans('Setup::StaticDataConfigMessage.acid_sour_taste')],
            ['id' => 16, 'onlycheckbox' => 1, 'value' => trans('Setup::StaticDataConfigMessage.belching')],
            ['id' => 17, 'onlycheckbox' => 1, 'value' => trans('Setup::StaticDataConfigMessage.bloating_sensation')],
            ['id' => 18, 'onlycheckbox' => 1, 'value' => trans('Setup::StaticDataConfigMessage.cough_after_meals')],
            ['id' => 19, 'onlycheckbox' => 1, 'value' => trans('Setup::StaticDataConfigMessage.cough_at_night_times_sleeping')]
        ];
        return $staticValue; 
    }

    /**
     * @DateOfCreation        22 June 2018
     * @ShortDescription      This function is responsible to get the staticData for CheckupFactor duration select option list
     * @return                 Array of status and message
     */
    public function getGenderData(){
        $staticValue = [
            ['id' => 1, 'value' => trans('Setup::StaticDataConfigMessage.male')],
            ['id' => 2, 'value' => trans('Setup::StaticDataConfigMessage.female')],
            ['id' => 3, 'value' => trans('Setup::StaticDataConfigMessage.transgender')]
        ];
        return $staticValue; 
    }

    /**
     * @DateOfCreation        22 June 2018
     * @ShortDescription      This function is responsible to get the staticData for staff role list
     * @return                 Array of status and message
     */
    public function getStaffRole(){
        $staticValue = [
            ['id' => 5, 'value' => trans('Setup::StaticDataConfigMessage.nurse')],
            ['id' => 6, 'value' => trans('Setup::StaticDataConfigMessage.ward_boy')],
            ['id' => 7, 'value' => trans('Setup::StaticDataConfigMessage.assistant')],
            ['id' => 8, 'value' => trans('Setup::StaticDataConfigMessage.receptionist')],
        ];
        return $staticValue; 
    }

    /**
     * @DateOfCreation        22 June 2018
     * @ShortDescription      This function is responsible to get the staticData for staff role list
     * @return                 Array of status and message
     */
    public function getAppointmentReason(){
        $staticValue = [
            ['id' => 1, 'value' => trans('Setup::StaticDataConfigMessage.annual_physical')],
            ['id' => 2, 'value' => trans('Setup::StaticDataConfigMessage.general_consultantion')],
            ['id' => 3, 'value' => trans('Setup::StaticDataConfigMessage.general_follow_up')],
            ['id' => 4, 'value' => trans('Setup::StaticDataConfigMessage.illness')],
        ];
        return $staticValue; 
    }

    /**
     * @DateOfCreation        22 June 2018
     * @ShortDescription      This function is responsible to get the staticData for gender list
     * @return                 Array of status and message
     */
    public function getCheckupFactorDurationSelectData(){
        $staticValue = [
            ['id' => 1, 'value' => trans('Setup::StaticDataConfigMessage.days')],
            ['id' => 2, 'value' => trans('Setup::StaticDataConfigMessage.weeks')],
            ['id' => 3, 'value' => trans('Setup::StaticDataConfigMessage.months')]
        ];
        return $staticValue; 
    }

    /**
     * @DateOfCreation        22 June 2018
     * @ShortDescription      This function is responsible to get the staticData for yes no option value
     * @return                 Array of status and message
     */
    public function getYesNoData(){
        $staticValue = [
            ['id' => '1', 'value' => trans('Setup::StaticDataConfigMessage.yes')],
            ['id' => '2', 'value' => trans('Setup::StaticDataConfigMessage.no')],
        ];
        return $staticValue; 
    }

    /**
     * @DateOfCreation        17 Sept 2018
     * @ShortDescription      This function is responsible to get the staticData for Marital status option value
     * @return                 Array of status and message
     */
    public function getMaritalStatusData(){
        $staticValue = [
            ['id' => '1', 'value' => trans('Setup::StaticDataConfigMessage.married')],
            ['id' => '2', 'value' => trans('Setup::StaticDataConfigMessage.Unmarried')],
        ];
        return $staticValue; 
    }

    /**
     * @DateOfCreation        03 August 2018
     * @ShortDescription      This function is responsible to get the staticData for Active inactive option value
     * @return                 Array of status and message
     */
    public function getActiveInactiveData(){
        $staticValue = [
            ['id' => 1, 'value' => trans('Setup::StaticDataConfigMessage.active')],
            ['id' => 2, 'value' => trans('Setup::StaticDataConfigMessage.inactive')],
        ];
        return $staticValue; 
    }

    /**
     * @DateOfCreation        22 June 2018
     * @ShortDescription      This function is responsible to get the staticData for yes no option value
     * @return                 Array of status and message
     */
    public function getTitleData(){
        $staticValue = [
            ['id' => 1, 'value' => trans('Setup::StaticDataConfigMessage.Mr')],
            ['id' => 2, 'value' => trans('Setup::StaticDataConfigMessage.Ms')],
            ['id' => 3, 'value' => trans('Setup::StaticDataConfigMessage.Mrs')],
            ['id' => 4, 'value' => trans('Setup::StaticDataConfigMessage.Dr')],
            ['id' => 5, 'value' => trans('Setup::StaticDataConfigMessage.Master')],

        ];
        return $staticValue; 
    }

    /**
     * @DateOfCreation        22 June 2018
     * @ShortDescription      This function is responsible to get the staticData for Disease Found Type list
     * @return                 Array of status and message
     */
    public function getDiseaseFoundTypeData(){
        $staticValue = [
            ['id' => 1, 'value' => trans('Setup::StaticDataConfigMessage.never')],
            ['id' => 2, 'value' => trans('Setup::StaticDataConfigMessage.Before_ILD_Treatment')],
            ['id' => 3, 'value' => trans('Setup::StaticDataConfigMessage.After_ILD_Treatment')],
        ];
        return $staticValue; 
    }

    /**
     * @DateOfCreation        22 June 2018
     * @ShortDescription      This function is responsible to get the staticData for Disease Found Type list
     * @return                 Array of status and message
     */
    public function getInhaledTobaccoUseData(){
        $staticValue = [
            ['id' => 1, 'value' => trans('Setup::StaticDataConfigMessage.never')],
            ['id' => 2, 'value' => trans('Setup::StaticDataConfigMessage.current')],
            ['id' => 3, 'value' => trans('Setup::StaticDataConfigMessage.ever')],
        ];
        return $staticValue; 
    }

    /**
     * @DateOfCreation        22 June 2018
     * @ShortDescription      This function is responsible to get the staticData for Disease Found Type list
     * @return                 Array of status and message
     */
    public function getDoseMeasurementTypeData(){
        $staticValue = [
            ['id' => 1, 'value' => trans('Setup::StaticDataConfigMessage.mg')],
            ['id' => 2, 'value' => trans('Setup::StaticDataConfigMessage.gm')],
            ['id' => 3, 'value' => trans('Setup::StaticDataConfigMessage.ml')],
        ];
        return $staticValue; 
    }
    
    /**
     * @DateOfCreation        03 August 2018
     * @ShortDescription      This function is responsible to get the staticData for Allergies list
     * @return                Array of status and message
     */
    public function getAllergiesData(){
        $where = array();
        $getAllergiesList = $this->allergiesModelObj->getAllergiesList($where);
        
        $parentAllergies = [];        
        $childAllergies  = [];        
        if(!empty($getAllergiesList)){
            foreach ($getAllergiesList as $key => $allergies) {
                if( $this->securityLibObj->decrypt($allergies->parent_id) == 0){
                    $parentAllergies[] = $allergies;
                } else { 
                    if(array_key_exists($allergies->parent_id, $childAllergies)){
                        $childAllergies[$allergies->parent_id][] = $allergies;
                    }else{
                        $childAllergies[$allergies->parent_id] = [];                        
                        $childAllergies[$allergies->parent_id][] = $allergies;                        
                    }
                }
            }
        }

        $staticValue = [
            'group_parent' => $parentAllergies, 
            'group_child'  => $childAllergies
            /*['id' => 1, 'value' => trans('Setup::StaticDataConfigMessage.food_allergy')],
            ['id' => 2, 'value' => trans('Setup::StaticDataConfigMessage.polan_allergy')],
            ['id' => 3, 'value' => trans('Setup::StaticDataConfigMessage.fungus_allergy')],
            ['id' => 4, 'value' => trans('Setup::StaticDataConfigMessage.peanut_allergy')],
            ['id' => 5, 'value' => trans('Setup::StaticDataConfigMessage.soy_allergy')],
            ['id' => 6, 'value' => trans('Setup::StaticDataConfigMessage.beef_allergy')],
            ['id' => 7, 'value' => trans('Setup::StaticDataConfigMessage.bean_allergy')],
            ['id' => 8, 'value' => trans('Setup::StaticDataConfigMessage.medicine_allergy')],
            ['id' => 9, 'value' => trans('Setup::StaticDataConfigMessage.others')],*/
        ];
        return $staticValue; 
    }

    /**
     * @DateOfCreation        03 August 2018
     * @ShortDescription      This function is responsible to get the staticData for Allergies list
     * @return                Array of status and message
     */
    public function getDiseaseData(){
        $staticValue = [
            ['id' => 1 , 'value' => trans('Setup::StaticDataConfigMessage.tuberculosis')],
            ['id' => 2 , 'value' => trans('Setup::StaticDataConfigMessage.extra_pulmonary_tuberculosis')],
            ['id' => 3 , 'value' => trans('Setup::StaticDataConfigMessage.hypertension')],
            ['id' => 4 , 'value' => trans('Setup::StaticDataConfigMessage.diabetes')],
            ['id' => 5 , 'value' => trans('Setup::StaticDataConfigMessage.coronary_heart_disease')],
            ['id' => 6 , 'value' => trans('Setup::StaticDataConfigMessage.heart_failure')],
            ['id' => 7 , 'value' => trans('Setup::StaticDataConfigMessage.thyroid_disease')],
            ['id' => 8 , 'value' => trans('Setup::StaticDataConfigMessage.stroke_cva')],
            ['id' => 9 , 'value' => trans('Setup::StaticDataConfigMessage.seizure')],
            ['id' => 10 , 'value' => trans('Setup::StaticDataConfigMessage.hepatitis_a_b_c')],
            ['id' => 11 , 'value' => trans('Setup::StaticDataConfigMessage.kidney_disease')],
            ['id' => 12 , 'value' => trans('Setup::StaticDataConfigMessage.anemia')],
            ['id' => 13 , 'value' => trans('Setup::StaticDataConfigMessage.eye_inflammation')],
            ['id' => 14 , 'value' => trans('Setup::StaticDataConfigMessage.asthma')],
            ['id' => 15 , 'value' => trans('Setup::StaticDataConfigMessage.bronchitis')],
            ['id' => 16 , 'value' => trans('Setup::StaticDataConfigMessage.sinus_disease')],
            ['id' => 17 , 'value' => trans('Setup::StaticDataConfigMessage.pulmonary_hypertension')],
            ['id' => 18 , 'value' => trans('Setup::StaticDataConfigMessage.pulmonary_embolism')],
            ['id' => 19 , 'value' => trans('Setup::StaticDataConfigMessage.sleep_apnoea')],
            ['id' => 20 , 'value' => trans('Setup::StaticDataConfigMessage.lung_cancer')],
            ['id' => 21, 'value' => trans('Setup::StaticDataConfigMessage.gerd')],
            ['id' => 22, 'value' => trans('Setup::StaticDataConfigMessage.hiatal_hernia')],
            ['id' => 23, 'value' => trans('Setup::StaticDataConfigMessage.bleeding_disorder')],
            ['id' => 24, 'value' => trans('Setup::StaticDataConfigMessage.raynauds_phenomenon')],
            ['id' => 25, 'value' => trans('Setup::StaticDataConfigMessage.rheumatoid_arthritis')],
            ['id' => 26, 'value' => trans('Setup::StaticDataConfigMessage.lupus')],
            ['id' => 27, 'value' => trans('Setup::StaticDataConfigMessage.scleroderma')],
            ['id' => 28, 'value' => trans('Setup::StaticDataConfigMessage.mixed_connective_tissue_disease')],
            ['id' => 29, 'value' => trans('Setup::StaticDataConfigMessage.sjogrens_syndrome')],
            ['id' => 30 , 'value' => trans('Setup::StaticDataConfigMessage.wegeners')],
            ['id' => 31 , 'value' => trans('Setup::StaticDataConfigMessage.polymyositis_or_dermatomyositis')],
            ['id' => 32 , 'value' => trans('Setup::StaticDataConfigMessage.bechets_disease')],
            ['id' => 33 , 'value' => trans('Setup::StaticDataConfigMessage.ankylosing_spondylitis')],
            ['id' => 34 , 'value' => trans('Setup::StaticDataConfigMessage.overlapping')],
            ['id' => 35 , 'value' => trans('Setup::StaticDataConfigMessage.unspecified_unclear')],
        ];
        return $staticValue; 
    }
    
    /**
     * @DateOfCreation        22 June 2018
     * @ShortDescription      This function is responsible to get the staticData for Disease Found Type list
     * @return                 Array of status and message
     */
    public function getMonthData(){
        $staticValue = [
            ['id' => 1, 'value' => trans('Setup::StaticDataConfigMessage.jan')],
            ['id' => 2, 'value' => trans('Setup::StaticDataConfigMessage.feb')],
            ['id' => 3, 'value' => trans('Setup::StaticDataConfigMessage.mar')],
            ['id' => 4, 'value' => trans('Setup::StaticDataConfigMessage.apr')],
            ['id' => 5, 'value' => trans('Setup::StaticDataConfigMessage.may')],
            ['id' => 6, 'value' => trans('Setup::StaticDataConfigMessage.jun')],
            ['id' => 7, 'value' => trans('Setup::StaticDataConfigMessage.jul')],
            ['id' => 8, 'value' => trans('Setup::StaticDataConfigMessage.aug')],
            ['id' => 9, 'value' => trans('Setup::StaticDataConfigMessage.sep')],
            ['id' => 10, 'value' => trans('Setup::StaticDataConfigMessage.oct')],
            ['id' => 11, 'value' => trans('Setup::StaticDataConfigMessage.nov')],
            ['id' => 12, 'value' => trans('Setup::StaticDataConfigMessage.dec')],
        ];
        return $staticValue; 
    }

    /**
     * @DateOfCreation        22 June 2018
     * @ShortDescription      This function is responsible to get the staticData for Blood Group list
     * @return                 Array of status and message
     */
    public function getBloodGroupData(){
        $staticValue = [
            ['id' => 1, 'value' => trans('Setup::StaticDataConfigMessage.a_negative')],
            ['id' => 2, 'value' => trans('Setup::StaticDataConfigMessage.a_posative')],
            ['id' => 3, 'value' => trans('Setup::StaticDataConfigMessage.b_negative')],
            ['id' => 4, 'value' => trans('Setup::StaticDataConfigMessage.b_posative')],
            ['id' => 5, 'value' => trans('Setup::StaticDataConfigMessage.o_negative')],
            ['id' => 6, 'value' => trans('Setup::StaticDataConfigMessage.o_posative')],
            ['id' => 7, 'value' => trans('Setup::StaticDataConfigMessage.ab_negative')],
            ['id' => 8, 'value' => trans('Setup::StaticDataConfigMessage.ab_posative')]
        ];
        return $staticValue; 
    }

    /**
     * @DateOfCreation        22 June 2018
     * @ShortDescription      This function is responsible to get the staticData for Blood Group list
     * @return                 Array of status and message
     */
    public function getGeneralCheckupDurationData(){
        $staticValue = [
            ['id' => 1, 'value' => trans('Setup::StaticDataConfigMessage.days')],
            ['id' => 2, 'value' => trans('Setup::StaticDataConfigMessage.weeks')],
            ['id' => 3, 'value' => trans('Setup::StaticDataConfigMessage.months')],
            ['id' => 4, 'value' => trans('Setup::StaticDataConfigMessage.years')]            
        ];
        return $staticValue; 
    }

    /**
     * @DateOfCreation        22 June 2018
     * @ShortDescription      This function is responsible to get the Blood Group name by id
     * @return                 Array of status and message
     */
    public function getBloodGroupNameById($bloodGroupId){
        $staticDataKey = $this->getBloodGroupData();
        $staticDataArrWithCustomKey = !empty($bloodGroupId) ? $this->utilityLibObj->changeArrayKey($staticDataKey, 'id') : [];
        $bloodGroupName = !empty($bloodGroupId) && !empty($staticDataArrWithCustomKey) && isset($staticDataArrWithCustomKey[$bloodGroupId]) ? $staticDataArrWithCustomKey[$bloodGroupId]['value'] : '';
        return $bloodGroupName;
    }

    /**
     * @DateOfCreation        22 June 2018
     * @ShortDescription      This function is responsible to get the staticData for Domestic factor condtion list
     * @return                 Array of status and message
     */
    public function getDomesticFactorConditionData(){
        $staticValue = [
            ['id' => 1, 'value' => trans('Setup::StaticDataConfigMessage.dusts'),'type'=>1,'formName'=>trans('Setup::StaticDataConfigMessage.condition_domestic_factor'), 'isCheckBox' => true],
            ['id' => 2, 'value' => trans('Setup::StaticDataConfigMessage.molds'),'type'=>1,'formName'=>trans('Setup::StaticDataConfigMessage.condition_domestic_factor'), 'isCheckBox' => true],
            ['id' => 3, 'value' => trans('Setup::StaticDataConfigMessage.air_conditioner'),'type'=>1, 'formName'=>trans('Setup::StaticDataConfigMessage.condition_domestic_factor'), 'isCheckBox' => true],            
            ['id' => 4, 'value' => trans('Setup::StaticDataConfigMessage.cooler'),'type'=>1, 'formName'=>trans('Setup::StaticDataConfigMessage.condition_domestic_factor'), 'isCheckBox' => true],            
            ['id' => 16, 'value' => trans('Setup::StaticDataConfigMessage.piegenos_at_home'),'type'=>1, 'formName'=>trans('Setup::StaticDataConfigMessage.condition_domestic_factor'), 'isCheckBox' => true],
            ['id' => 17, 'value' => trans('Setup::StaticDataConfigMessage.parrot_at_home'),'type'=>1, 'formName'=>trans('Setup::StaticDataConfigMessage.condition_domestic_factor'), 'isCheckBox' => true],
            ['id' => 5, 'value' => trans('Setup::StaticDataConfigMessage.brids_in_home'),'type'=>1, 'formName'=>trans('Setup::StaticDataConfigMessage.condition_domestic_factor'), 'isCheckBox' => true],            
            ['id' => 6, 'value' => trans('Setup::StaticDataConfigMessage.any_changes_in_house'),'type'=>1, 'formName'=>trans('Setup::StaticDataConfigMessage.condition_domestic_factor'), 'isCheckBox' => true],            
            ['id' => 7, 'value' => trans('Setup::StaticDataConfigMessage.cough_breathing_onset'),'type'=>1, 'formName'=>trans('Setup::StaticDataConfigMessage.condition_domestic_factor'), 'isCheckBox' => false],            
            ['id' => 8, 'value' => trans('Setup::StaticDataConfigMessage.open_cooking'),'type'=>2, 'formName'=>trans('Setup::StaticDataConfigMessage.does_current_domestic_factor'), 'isCheckBox' => true],            
            ['id' => 9, 'value' => trans('Setup::StaticDataConfigMessage.kerosene_cooking'),'type'=>2, 'formName'=>trans('Setup::StaticDataConfigMessage.does_current_domestic_factor'), 'isCheckBox' => true],            
            ['id' => 10, 'value' => trans('Setup::StaticDataConfigMessage.coal_cooking'),'type'=>2, 'formName'=>trans('Setup::StaticDataConfigMessage.does_current_domestic_factor'), 'isCheckBox' => true],            
            ['id' => 11, 'value' => trans('Setup::StaticDataConfigMessage.lpg_cooking'),'type'=>2, 'formName'=>trans('Setup::StaticDataConfigMessage.does_current_domestic_factor'), 'isCheckBox' => true],
            ['id' => 12, 'value' => trans('Setup::StaticDataConfigMessage.urban_district'),'type'=>3, 'formName'=>trans('Setup::StaticDataConfigMessage.previous_residential_domestic_factor'), 'isCheckBox' => true],            
            ['id' => 13, 'value' => trans('Setup::StaticDataConfigMessage.sub_urban_district'),'type'=>3, 'formName'=>trans('Setup::StaticDataConfigMessage.previous_residential_domestic_factor'), 'isCheckBox' => true],            
            ['id' => 14, 'value' => trans('Setup::StaticDataConfigMessage.rural_village_district'),'type'=>3, 'formName'=>trans('Setup::StaticDataConfigMessage.previous_residential_domestic_factor'), 'isCheckBox' => true],            
            ['id' => 15, 'value' => trans('Setup::StaticDataConfigMessage.other_district'),'type'=>3, 'formName'=>trans('Setup::StaticDataConfigMessage.previous_residential_domestic_factor'), 'isCheckBox' => false],
        ];
        return $staticValue; 
    }

    /**
     * @DateOfCreation        22 June 2018
     * @ShortDescription      This function is responsible to get the staticData for Domestic factor condtion  type list
     * @return                 Array of status and message
     */
    public function getDomesticFactorConditionTypeData(){
        $staticValue = [
            ['id' => 1, 'value' => trans('Setup::StaticDataConfigMessage.domestic_factor_type1')],
            ['id' => 2, 'value' => trans('Setup::StaticDataConfigMessage.domestic_factor_type2')],
            ['id' => 3, 'value' => trans('Setup::StaticDataConfigMessage.domestic_factor_type3')]    
        ];
        return $staticValue; 
    }

    /**
     * @DateOfCreation        3 July 2018
     * @ShortDescription      This function is responsible to get the staticData for New visit form
     * @return                 Array of status and message
     */
    public function getNewVisitFormFectorData(){

        $staticValue = [
            'newVisitFormFector' => [
                [
                    'id'            => 1, 
                    'value'         => trans('Setup::StaticDataConfigMessage.visit_symptom_status_title'),
                    'type'          => 1, 
                    'field_type'    => 'customcheckbox',
                    'name'          => 'visit_symptom_status',
                    'options'  => ['1' => trans('Setup::StaticDataConfigMessage.visit_symptom_improved'), 
                                   '2' => trans('Setup::StaticDataConfigMessage.visit_symptom_same'), 
                                   '3' => trans('Setup::StaticDataConfigMessage.visit_symptom_deteriorated')
                                ] ,
                    'cssClasses' => $this->checkboxCss('col-md-3', 'check-list-text', 'check-list-radio', '')
                ],
                [
                    'id'            => 2, 
                    'value'         => trans('Setup::StaticDataConfigMessage.visit_symptom_site_investigators_title'),
                    'type'          => 1, 
                    'field_type'    => 'customcheckbox', 
                    'name'          => 'visit_followup_status',
                    'options'       => ['1' => trans('Setup::StaticDataConfigMessage.yes'), '2' => trans('Setup::StaticDataConfigMessage.no')],
                    'cssClasses'    => $this->checkboxCss('col-md-3', 'check-list-text', 'check-list-radio', '')
                ],
                [
                    'id'            => 3, 
                    'value'         => trans('Setup::StaticDataConfigMessage.visit_symptom_followed_elsewhere_title'),
                    'type'          => 1, 
                    'field_type'    => 'customcheckbox', 
                    'name'          => 'visit_followed_elsewhere',
                    'options'       => ['1'   => trans('Setup::StaticDataConfigMessage.visit_followed_elsewhere_known'), 
                                        '2'   => trans('Setup::StaticDataConfigMessage.visit_followed_elsewhere_unknown')
                                        ],
                    'cssClasses' => $this->checkboxCss('col-md-3', 'check-list-text', 'check-list-radio', '')
                ],
                [
                    'id'            => 4,
                    'value'         => trans('Setup::StaticDataConfigMessage.consultant_suspect_active_infection'),
                    'type'          => 1, 
                    'field_type'    => 'select' ,
                    'name'          => 'visit_suspect_active_infection',
                    'options'       => [
                                        1 => trans('Setup::StaticDataConfigMessage.consultant_option_active_tbs'),
                                        2 => trans('Setup::StaticDataConfigMessage.consultant_option_pneumonia')
                                    ],
                    'cssClasses'    => $this->textBoxCss('col-md-3', 'form-group', '', ''),
                ],
            ],
            'VisitsDeathInfoFormFector' => [
                [
                    'id'        => 1, 
                    'value'     => '',
                    'type'      => 2, 
                    'name'      => 'patient_death_status', 
                    'options'   => [
                                    '1' => trans('Setup::StaticDataConfigMessage.visit_death_info_title_alive'), 
                                    '2' => trans('Setup::StaticDataConfigMessage.visit_death_info_title_died'), 
                                    '3' => trans('Setup::StaticDataConfigMessage.visit_death_info_title_unknown')
                                ], 
                    'field_type' => 'customcheckbox', 
                    'cssClasses' => $this->checkboxCss('col-md-12', 'check-list-text', 'check-list-radio', 'rp') 
                ],
                [
                    'id'        => 2, 
                    'value'     => trans('Setup::StaticDataConfigMessage.visit_death_info_lebel_date_of_death'),
                    'type'      => 2, 
                    'name'      => 'date_of_death', 
                    'field_type' => 'date', 
                    'options'   => [],
                    'cssClasses' => $this->textBoxCss('col-md-3', 'form-group', 'check-list-radio', '')
                ],
                [
                    'id'        => 3, 
                    'value'     => trans('Setup::StaticDataConfigMessage.visit_death_info_lebel_cause_of_death'),
                    'type'      => 2, 
                    'name'      => 'cause_of_death', 
                    'field_type' => 'text', 
                    'options'   => [],
                    'cssClasses' => $this->textBoxCss('col-md-9', 'form-group', 'check-list-radio', '')
                ],
            ],
            'VisitsHospitalizationFormFector' => [
                [
                    'id'        => 1, 
                    'value'     => '',
                    'type'      => 3, 
                    'name'      => 'hospitalization_status', 
                    'options'   => [
                                    '1' => trans('Setup::StaticDataConfigMessage.yes'), 
                                    '2' => trans('Setup::StaticDataConfigMessage.no')
                                ], 
                    'field_type' => 'customcheckbox', 
                    'cssClasses' => $this->checkboxCss('col-md-12', '', '', 'form-group') 
                ],
                [
                    'id'        => 2, 
                    'value'     => trans('Setup::StaticDataConfigMessage.visit_hospitalization_label_how_many'),
                    'type'      => 3, 
                    'name'      => 'hospitalization_how_many', 
                    'field_type' => 'text', 
                    'options'   => [],
                    'cssClasses' => $this->textBoxCss('col-md-3','','','')
                ],
                [
                    'id'        => 3, 
                    'value'     => trans('Setup::StaticDataConfigMessage.visit_hospitalization_label_why'),
                    'type'      => 3, 
                    'name'      => 'hospitalization_why', 
                    'field_type' => 'text', 
                    'options'   => [],
                    'cssClasses' => $this->textBoxCss('col-md-3','','','')
                ],
                [
                    'id'        => 4, 
                    'value'     => trans('Setup::StaticDataConfigMessage.visit_hospitalization_date_of_hospitalization'),
                    'type'      => 3, 
                    'name'      => 'date_of_hospitalization', 
                    'field_type' => 'date', 
                    'options'   => [],
                    'cssClasses' => $this->textBoxCss('col-md-3')
                ],
            ],
            'VisitsHospitalizationReferenceFormFector' => [
                [
                    'id'        => 1, 
                    'value'     => trans('Setup::StaticDataConfigMessage.visit_hospitalization_label_respiratory'),
                    'type'      => 4, 
                    'name'      => 'hospitalization_respiratory', 
                    'field_type' => 'select', 
                    'options'   => [
                                    1 => trans('Setup::StaticDataConfigMessage.visit_hospitalization_option_pneumonia'),
                                    2 => trans('Setup::StaticDataConfigMessage.visit_hospitalization_option_tuberculosis'),
                                    3 => trans('Setup::StaticDataConfigMessage.visit_hospitalization_option_pneumothorax'),
                                    4 => trans('Setup::StaticDataConfigMessage.visit_hospitalization_option_pulmonary'),
                                    5 => trans('Setup::StaticDataConfigMessage.visit_hospitalization_option_exacerbation_of_ild'),
                                    6 => trans('Setup::StaticDataConfigMessage.visit_hospitalization_option_exacerbation_of_ipf'),
                                    ],
                    'cssClasses' => $this->textBoxCss('col-md-3','','','')
                ],
                [
                    'id'        => 2, 
                    'value'     => trans('Setup::StaticDataConfigMessage.visit_hospitalization_label_cardiac'),
                    'type'      => 4, 
                    'name'      => 'hostpitalization_cardiac_myocardial_infarction', 
                    'field_type' => 'text', 
                    'options'   => [],
                    'cssClasses' => $this->textBoxCss('col-md-4','','','')
                ],
            ],
            'VisitsOccupationalStatusFormFector' => [
                [
                    'id'        => 1, 
                    'value'     => '',
                    'type'      => 5, 
                    'name'      => 'occupation_status', 
                    'field_type' => 'customcheckbox', 
                    'options'   => [
                                    1 => trans('Setup::StaticDataConfigMessage.visit_occupation_checkbox_option_same_job'),
                                    2 => trans('Setup::StaticDataConfigMessage.visit_occupation_checkbox_option_new_job'),
                                    3 => trans('Setup::StaticDataConfigMessage.visit_occupation_checkbox_option_full'),
                                    4 => trans('Setup::StaticDataConfigMessage.visit_occupation_checkbox_option_reduced'),
                                    5 => trans('Setup::StaticDataConfigMessage.visit_occupation_checkbox_option_not_working'),
                                    ],
                    'cssClasses' => $this->textBoxCss('col-md-12','','','')
                ],
            ],
            'VisitsChangesInFormFector' => [
                [
                    'id'        => 1, 
                    'value'     => trans('Setup::StaticDataConfigMessage.visit_changes_in_label_domestic_environment'),
                    'type'      => 6, 
                    'name'      => 'changes_in_fector', 
                    'field_type' => 'customcheckbox', 
                    'options'   => [
                                    '1' => trans('Setup::StaticDataConfigMessage.visit_changes_in_checkbox_option_no_change'),
                                    '2' => trans('Setup::StaticDataConfigMessage.visit_changes_in_checkbox_option_yes_change'),
                                    ],
                    'cssClasses' => $this->checkboxCss('col-md-4','','','form-group')
                ],
                [
                    'id'        => 2, 
                    'value'     => trans('Setup::StaticDataConfigMessage.visit_changes_in_label_air_coolers'),
                    'type'      => 6, 
                    'name'      => 'changes_in_fector', 
                    'field_type' => 'customcheckbox', 
                    'options'   => [
                                    '1' => trans('Setup::StaticDataConfigMessage.visit_changes_in_checkbox_option_discontinued'),
                                    '2' => trans('Setup::StaticDataConfigMessage.visit_changes_in_checkbox_option_cleaned'),
                                    '3' => trans('Setup::StaticDataConfigMessage.visit_changes_in_checkbox_option_changed_pads'),
                                    ],
                    'cssClasses' => $this->checkboxCss('col-md-4','','','form-group')
                ],
                [
                    'id'        => 3, 
                    'value'     => trans('Setup::StaticDataConfigMessage.visit_changes_in_label_birds_pigeons'),
                    'type'      => 6, 
                    'name'      => 'changes_in_fector', 
                    'field_type' => 'customcheckbox', 
                    'options'   => [
                                    '1' => trans('Setup::StaticDataConfigMessage.visit_changes_in_checkbox_option_persist'),
                                    '2' => trans('Setup::StaticDataConfigMessage.visit_changes_in_checkbox_option_no_exposed'),
                                    ],
                    'cssClasses' => $this->checkboxCss('col-md-4','','','form-group'),
                ],
                [
                    'id'        => 4, 
                    'value'     => trans('Setup::StaticDataConfigMessage.visit_changes_in_label_avoiding_factors'),
                    'type'      => 6, 
                    'name'      => 'changes_in_fector', 
                    'field_type' => 'customcheckbox', 
                    'options'   => [
                                    '1' => trans('Setup::StaticDataConfigMessage.visit_changes_in_checkbox_option_avoiding'),
                                    '2' => trans('Setup::StaticDataConfigMessage.visit_changes_in_checkbox_option_not_avoiding'),
                                    ],
                    'cssClasses' => $this->checkboxCss('col-md-4','','','form-group'),
                ],
                [
                    'id'        => 5, 
                    'value'     => trans('Setup::StaticDataConfigMessage.visit_changes_in_label_life_style'),
                    'type'      => 6, 
                    'name'      => 'changes_in_fector', 
                    'field_type' => 'customcheckbox', 
                    'options'   => [
                                    '1' => trans('Setup::StaticDataConfigMessage.yes'),
                                    '2' => trans('Setup::StaticDataConfigMessage.no')
                                    ],
                    'cssClasses' => $this->checkboxCss('col-md-4','','','form-group')
                ],
                [
                    'id'        => 6, 
                    'value'     => trans('Setup::StaticDataConfigMessage.visit_changes_in_label_occupational_status'),
                    'type'      => 6, 
                    'name'      => 'changes_in_fector', 
                    'field_type' => 'customcheckbox', 
                    'options'   => [
                                    1 => trans('Setup::StaticDataConfigMessage.visit_occupation_checkbox_option_same_job'),
                                    2 => trans('Setup::StaticDataConfigMessage.visit_occupation_checkbox_option_new_job'),
                                    3 => trans('Setup::StaticDataConfigMessage.visit_occupation_checkbox_option_full'),
                                    4 => trans('Setup::StaticDataConfigMessage.visit_occupation_checkbox_option_reduced'),
                                    5 => trans('Setup::StaticDataConfigMessage.visit_occupation_checkbox_option_not_working'),
                                    ],
                    'cssClasses' => $this->checkboxCss('col-md-4','','','form-group')
                ],
            ],
            'VisitsVitalsFormFectorWeight' => $this->getPhysicalOption(['getWeight']),
            'VisitsVitalsFormFector' => $this->vitalsFectorData(),
            'VisitsPhysicalExaminationFormFector' => $this->getPhysicalExzmaination(),
            'treatmentOxygenRequirements' => [
                [
                    'id'       => 1, 
                    'value'    => trans('Setup::StaticDataConfigMessage.visit_oxygen_requirement_label_no_oxygen'),
                    'type'     => 9,
                    'field_type' => 'customcheckbox',
                    'name'     => 'requirement_fector_id',
                    'options'  => ['1' => trans('Setup::StaticDataConfigMessage.yes'), 
                                   '2' => trans('Setup::StaticDataConfigMessage.no'),
                                ] ,
                    'cssClasses' => $this->checkboxCss('col-md-3', 'control-label', 'check-list-radio', 'form-group')
                ],
                [
                    'id'           => 2, 
                    'value'         => trans('Setup::StaticDataConfigMessage.visit_oxygen_requirement_label_change_oxygen_need'),
                    'type'          => 9,
                    'field_type'    => 'customcheckbox', 
                    'name'          => 'requirement_fector_id',
                    'options'       => ['1' => trans('Setup::StaticDataConfigMessage.visit_changes_in_checkbox_option_discontinued'), 
                                        '2' => trans('Setup::StaticDataConfigMessage.visit_oxygen_requirement_checkbox_option_increased'),
                                        '3' => trans('Setup::StaticDataConfigMessage.visit_oxygen_requirement_checkbox_option_using')
                                    ],
                    'cssClasses'    => $this->checkboxCss('col-md-3', 'control-label', 'check-list-radio', 'form-group')
                ],
                [
                    'id'           => 3, 
                    'value'         => trans('Setup::StaticDataConfigMessage.visit_oxygen_requirement_label_oxygen_usage_hour'),
                    'type'          => 9,
                    'field_type'    => 'customcheckbox', 
                    'name'          => 'requirement_fector_id',
                    'options'       => [
                                        '1'   => trans('Setup::StaticDataConfigMessage.visit_oxygen_requirement_checkbox_option_continuous'), 
                                        '2'   => trans('Setup::StaticDataConfigMessage.visit_oxygen_requirement_checkbox_option_with_exertion'),
                                        '3'   => trans('Setup::StaticDataConfigMessage.visit_oxygen_requirement_checkbox_option_during_sleep'),
                                        ],
                    'cssClasses' => $this->checkboxCss('col-md-3', 'control-label', 'check-list-radio', 'form-group')
                ],
                [
                    'id'           => 4, 
                    'value'         => trans('Setup::StaticDataConfigMessage.visit_oxygen_requirement_label_cpap_during_sleep'),
                    'type'          => 9,
                    'field_type'    => 'text',
                    'placeholder'   => ' ', 
                    'name'          => 'requirement_fector_id',
                    'options'       => [],
                    'cssClasses'    => $this->textBoxCss('col-md-3')
                ],
            ],
            'treatmentOtherRequirements' => [
                [
                    'id'       => Config::get('dataconstants.TREATMENT_FECTOR_VACCINE'), 
                    'value'    => trans('Setup::StaticDataConfigMessage.visit_other_requirement_label_vaccine'),
                    'type'     => 10,
                    'field_type' => 'checkbox',
                    'name'     => 'requirement_fector_id',
                    'options'  => [
                                    '1' => trans('Setup::StaticDataConfigMessage.visit_other_requirement_label_pneumococcal'), 
                                    '2' => trans('Setup::StaticDataConfigMessage.visit_oxygen_requirement_checkbox_option_influenza'),
                                ] ,
                    'cssClasses' => $this->checkboxCss('col-md-3', 'control-label', 'check-list-radio', 'form-group')
                ],
                [
                    'id'           => 6, 
                    'value'         => trans('Setup::StaticDataConfigMessage.visit_other_requirement_label_walk_exercises'),
                    'type'          => 10,
                    'field_type'    => 'customcheckbox', 
                    'name'          => 'requirement_fector_id',
                    'options'       => [
                                        '1' => trans('Setup::StaticDataConfigMessage.visit_oxygen_requirement_checkbox_option_daily'), 
                                        '2' => trans('Setup::StaticDataConfigMessage.visit_oxygen_requirement_checkbox_option_weekly'),
                                        '3' => trans('Setup::StaticDataConfigMessage.visit_oxygen_requirement_checkbox_option_not_applicable')
                                    ],
                    'cssClasses'    => $this->checkboxCss('col-md-3', 'control-label', 'check-list-radio', 'form-group')
                ],
                [
                    'id'           => 7, 
                    'value'         => trans('Setup::StaticDataConfigMessage.visit_other_requirement_label_yoga_exercises'),
                    'type'          => 10,
                    'field_type'    => 'customcheckbox', 
                    'name'          => 'requirement_fector_id',
                    'options'       => [
                                        '1'   => trans('Setup::StaticDataConfigMessage.visit_oxygen_requirement_checkbox_option_daily'), 
                                        '2'   => trans('Setup::StaticDataConfigMessage.visit_oxygen_requirement_checkbox_option_weekly'),
                                        '3'   => trans('Setup::StaticDataConfigMessage.visit_oxygen_requirement_checkbox_option_not_applicable'),
                                        ],
                    'cssClasses' => $this->checkboxCss('col-md-3', 'control-label', 'check-list-radio', 'form-group')
                ],
            ],
            'InvestigationFector' => $this->getPhysicalOption(['getWeight','getHeight','getBmi']),
            'SpirometriesFector' =>  [
                [
                    'id'        => 1, 
                    'value'     => '',
                    'type'      => 12, 
                    'name'      => 'spirometry_date', 
                    'field_type' => 'date', 
                    'placeholder'    =>trans('Setup::StaticDataConfigMessage.visit_spirometry_date'), 
                    'options'   => [],
                    'cssClasses' => $this->textBoxCss('col-md-2', '', '', '')
                ]
            ],
            'SixMWTFector' =>  [
                [
                    'id'            => 2, 
                    'value'         => '',
                    'type'          => 13, 
                    'name'          => 'sixmwts_status', 
                    'placeholder'   => '', 
                    'field_type'    => 'customcheckbox', 
                    'options'       => $this->covertToOption($this->getYesNoData()),
                    'cssClasses'    => $this->checkboxCss('col-md-2 add-more-spaces-tm', '', 'col-md-12 text-right', 'form-group checkbox-formgroup mt-5'),
                ],
                [
                    'id'            => 1, 
                    'value'         => '',
                    'type'          => 13, 
                    'name'          => 'sixmwt_date', 
                    'field_type'    => 'date', 
                    'placeholder'    =>trans('Setup::StaticDataConfigMessage.visit_6mwt_date'), 
                    'options'       => [],
                    'cssClasses'    => $this->textBoxCss('col-md-2', '', '', '')
                ],           
            ],
            'PulmonaryFunctionTestFector' =>  [
                [
                    'id'            => 1, 
                    'value'         => trans('Setup::StaticDataConfigMessage.visit_pulmonary_function_test'),
                    'type'          => 14, 
                    'name'          => 'pulmonary_function_test_status', 
                    'placeholder'   => '', 
                    'field_type'    => 'customcheckbox', 
                    'options'       => $this->covertToOption($this->getYesNoData()),
                    'cssClasses'    => $this->checkboxCss('col-md-6 add-more-spaces-tm', 'col-md-8 bold-text sub-heading align-checkbox-list rp', 'col-md-4 text-right', 'form-group checkbox-formgroup'),
                ]          
            ],
            'AbgInvestigationFector'        =>  $this->abgInvestigationFector(),
            'AbgInvestigationFectorDate'    =>  $this->abgInvestigationFectorDate(),
            'ThoracoscopicLungBiopsy'       =>  $this->getThoracoscopicLungBiopsy(),
            'SurgicalLungBiopsyDate'        =>  $this->getSurgicalLungBiopsyDate(),
            'SurgicalLungBiopsy'            =>  $this->getSurgicalLungBiopsy(),
            'ChestXray'                     =>  $this->getChestXray(),
            'HRCT'                          =>  $this->getHRCT(),
            'HRCTDate'                      =>  $this->getHRCTDate(),
            'HRCTEXTRA'                     =>  $this->getHRCTExtra(),
            'UIP'                           =>  $this->getUIP(),
            'FiberopticBronchoscopyDate'    =>  $this->getFiberopticBronchoscopyDate(),
            'GerdDiseaseFormFactor'         => $this->getStaticDataFunction(['getGerdFrom']),
            'CtdDiseaseFormFactor'          => $this->getStaticDataFunction(['getCtdFrom']),
            'PulmonaryDiseaseFormFactor'    => $this->getStaticDataFunction(['getPulmonaryForm']),
            'SleepStudyReport'              => $this->getSleepStudyReportForm(),
            'InvestigationReport'           => $this->getInvestigationReport(),
        ];
        return $staticValue;
    }

    /**
     * @DateOfCreation        5 July 2018
     * @ShortDescription      This function is responsible to set css class into checkbox factors
     * @return                Array of status and message
     */
    private function checkboxCss($parentClass = 'col-md-4', $labelClass = '', $inputContainerClass= '', $inputGroupClass= ''){
        return ['inputParentClass'      => $parentClass,
                'labelClass'            => $labelClass,
                'inputContainerClass'   => $inputContainerClass,                                
                'inputGroupClass'       => $inputGroupClass];
    }

    /**
     * @DateOfCreation        5 July 2018
     * @ShortDescription      This function is responsible to set css class into text box factors
     * @return                Array of status and message
     */
    private function textBoxCss($parentClass = 'col-md-4', $inputGroupClass = 'col-md-12', $labelClass='control-label', $inputContainerClass='', $inputClass='form-control'){
        return ['inputParentClass'      => $parentClass,
                'inputGroupClass'       => $inputGroupClass,
                'labelClass'            => $labelClass,
                'inputContainerClass'   => $inputContainerClass,
                'inputClass'            => $inputClass
            ];
    }

    /**
     * @DateOfCreation        9 July 2018
     * @ShortDescription      This function is responsible to set extra options like checkbox or text box
     * @return                Array of status and message
     */
    public function getDiseaseExtraOptions($diseaseName){
        $options = [];
        if($diseaseName == 'GERD'){
            $options = [
                        ['id' => '1', 'value' => trans('Setup::StaticDataConfigMessage.by_history')],
                        ['id' => '2', 'value' => trans('Setup::StaticDataConfigMessage.by_investigation')]
                    ];
        } 

        if($diseaseName == 'CTD'){
            $options = [
                        ['id' => '1', 'value' => trans('Setup::StaticDataConfigMessage.yes')],
                        ['id' => '2', 'value' => trans('Setup::StaticDataConfigMessage.no')]
                    ];
        }

        if($diseaseName == 'Pulmonary hypertension: 2D ECHO'){
            $options = [
                        ['id' => '1', 'value' => trans('Setup::StaticDataConfigMessage.yes')],
                        ['id' => '2', 'value' => trans('Setup::StaticDataConfigMessage.no')]
                    ];
        }
        return $options;

    }

    /**
     * @DateOfCreation        9 July 2018
     * @ShortDescription      This function is responsible to set Table factors of hospitalization
     * @return                Array of status and message
     */
    public function getHospitalizationTableFector(){
        $hospitalizations = [
                                '1' => trans('Setup::StaticDataConfigMessage.visit_hospitalization_table_worsening_respiratory_symptoms'),
                                '2' => trans('Setup::StaticDataConfigMessage.visit_hospitalization_table_requiring_oxygen'),
                                '3' => trans('Setup::StaticDataConfigMessage.visit_hospitalization_table_intensive_care_unit'),
                                '4' => trans('Setup::StaticDataConfigMessage.visit_hospitalization_table_mechanical_ventilation'),
                            ];
        $optionData = [];
        foreach ($hospitalizations as $hostipatalizationFectorId => $hospitalizationTableFector) {
            $key = $this->securityLibObj->encrypt($hostipatalizationFectorId);
            $optionData[] = [
                                'hospitalization_fector_id'             => $key,
                                'hospitalization_fector_value'          => $hospitalizationTableFector,
                                'hospitalization_diagnosis_details_key_'.$key => '',
                                'hospitalization_date_'.$key            => '',
                                'hospitalization_duration_'.$key        => '',
                                'hospitalization_duration_unit_'.$key   => $this->getCheckupFactorDurationSelectData(),
                            ];
        }

        return $optionData;
    }

    /**
     * @DateOfCreation        9 July 2018
     * @ShortDescription      This function is responsible to set Table factors of Spirometries
     * @return                Array of status and message
     */
    public function getSpirometriesTableFector(){
        $spirometriesTableData = [
                                Config::get('dataconstants.SPIROMETRY_FEV1_FACTOR_ID')          => trans('Setup::StaticDataConfigMessage.visit_spirometry_fector_title_fev1'),
                                Config::get('dataconstants.SPIROMETRY_FVC_FACTOR_ID')           => trans('Setup::StaticDataConfigMessage.visit_spirometry_fector_title_fvc'),
                                Config::get('dataconstants.SPIROMETRY_FEV1_FVC_FACTOR_ID')      => trans('Setup::StaticDataConfigMessage.visit_spirometry_fector_title_fev1_fvc'),
                                Config::get('dataconstants.SPIROMETRY_PEFFR_FACTOR_ID')         => trans('Setup::StaticDataConfigMessage.visit_spirometry_fector_title_pefr'),
                                /*Config::get('dataconstants.SPIROMETRY_LUNG_CAPACITY_FACTOR_ID') => trans('Setup::StaticDataConfigMessage.visit_spirometry_fector_title_lung_capacity'),
                                Config::get('dataconstants.SPIROMETRY_RESIDUAL_VOLUME_FACTOR_ID') => trans('Setup::StaticDataConfigMessage.visit_spirometry_fector_title_residual_volume'),
                                Config::get('dataconstants.SPIROMETRY_DLCO_FACTOR_ID')          => trans('Setup::StaticDataConfigMessage.visit_spirometry_fector_title_dlco'),
                                Config::get('dataconstants.SPIROMETRY_ML_MMHG_FACTOR_ID')       => trans('Setup::StaticDataConfigMessage.visit_spirometry_fector_title_ml_mmhg'),
                                Config::get('dataconstants.SPIROMETRY_ML_KPG_FACTOR_ID')        => trans('Setup::StaticDataConfigMessage.visit_spirometry_fector_title_ml_kpa'),
                                Config::get('dataconstants.SPIROMETRY_KCO_FACTOR_ID')           => trans('Setup::StaticDataConfigMessage.visit_spirometry_fector_title_kco'),*/
                            ];
        $optionData = [];
        foreach ($spirometriesTableData as $fectorId => $fectorValue) {
            $key = $this->securityLibObj->encrypt($fectorId);
            $optionData[] = [
                                'spirometries_fector_id'           => $key,
                                'spirometries_fector_value'        => $fectorValue,
                                'spirometries_fector_pre_value'    => '',
                                'spirometries_fector_post_value'   => '',
                                'pre_unit'                         => $fectorId == Config::get('dataconstants.SPIROMETRY_DLCO_FACTOR_ID') || $fectorId == Config::get('dataconstants.SPIROMETRY_KCO_FACTOR_ID') ? trans('Setup::StaticDataConfigMessage.visit_spirometry_fector_title_ml_mmhg_min'): '',
                                'post_unit'                        => $fectorId == Config::get('dataconstants.SPIROMETRY_DLCO_FACTOR_ID') || $fectorId == Config::get('dataconstants.SPIROMETRY_KCO_FACTOR_ID') ? trans('Setup::StaticDataConfigMessage.visit_spirometry_fector_title_mmol_min_kpa'): '',
                            ];
        }

        return $optionData;
    }

    /**
     * @DateOfCreation        9 July 2018
     * @ShortDescription      This function is responsible to set Table factors of Spirometries
     * @return                Array of status and message
     */
    public function getSixMinutWalkTestTableFector(){
        $spirometriesTableData = [
                            '11' => [
                                1 => trans('Setup::StaticDataConfigMessage.visit_6mwt_fector_title_spo2'),
                                2 => trans('Setup::StaticDataConfigMessage.visit_6mwt_fector_title_spo2'),
                                
                            ],
                            '12' => [
                                3 => trans('Setup::StaticDataConfigMessage.visit_6mwt_fector_title_distance_coverd'),
                                4 => trans('Setup::StaticDataConfigMessage.visit_6mwt_fector_title_distance_coverd'),
                            ]
                        ];
        $optionData = [];
        foreach ($spirometriesTableData as $keyType => $fectors) {

            if($keyType == '11'){
                $keyOne = 1;
                $keyTwo = 2;
                $val = $spirometriesTableData[$keyType][1];
            }else{
                $keyOne = 3;
                $keyTwo = 4;
                $val = $spirometriesTableData[$keyType][3];
            }

            $key1 = $this->securityLibObj->encrypt($keyOne);
            $key2 = $this->securityLibObj->encrypt($keyTwo);

            $optionData[] = [
                                'sixmwt_fector_type_key_1'    => $key1,
                                'sixmwt_fector_id_'.$key1     => $key1,
                                'sixmwt_fector_value_'.$key1  => $val,
                                'sixmwt_before_sixmwt_'.$key1 => '',
                                'sixmwt_after_sixmwt_'.$key1  => '',

                                'sixmwt_fector_type_key_2'    => $key2,
                                'sixmwt_fector_id_'.$key2     => $key2,
                                'sixmwt_fector_value_'.$key2  => $val,
                                'sixmwt_before_sixmwt_'.$key2 => '',
                                'sixmwt_after_sixmwt_'.$key2  => '',
                            ];
        }

        return $optionData;
    }

    /* @DateOfCreation        22 June 2018
     * @ShortDescription      This function is responsible to get the staticData for Domestic factor condtion  type list
     * @return                 Array of status and message
     */
    public function getDomesticFactorPatientResidentData(){
        $staticValue = [
            ['id' => 1, 'value' => trans('Setup::StaticDataConfigMessage.domestic_factor_place1'),'type'=>4],
            ['id' => 2, 'value' => trans('Setup::StaticDataConfigMessage.domestic_factor_place2'),'type'=>4],
            ['id' => 3, 'value' => trans('Setup::StaticDataConfigMessage.domestic_factor_place3'),'type'=>4],    
            ['id' => 4, 'value' => trans('Setup::StaticDataConfigMessage.domestic_factor_place4'),'type'=>4]    
        ];
        return $staticValue; 
    }

    /**
     * @DateOfCreation        22 June 2018
     * @ShortDescription      This function is responsible to get the staticData for Domestic factor condtion  type list
     * @return                 Array of status and message
     */
    public function getSocialAddictionKeyData(){
        $staticValue = [
            ['id' => 1, 'value' => trans('Setup::StaticDataConfigMessage.smoked_substance'),'input_type_option' =>'yes_no_option'],
            ['id' => 2, 'value' => trans('Setup::StaticDataConfigMessage.inhaled_tobacco'),'input_type_option' =>'inhaled_tobacco_use'],
            ['id' => 3, 'value' => trans('Setup::StaticDataConfigMessage.nonveg_substance'),'input_type_option' =>'yes_no_option'],
        ];
        return $staticValue; 
    }

    /**
     * @DateOfCreation        22 June 2018
     * @ShortDescription      This function is responsible to get the staticData for Domestic factor condtion  type list
     * @return                 Array of status and message
     */
    public function getSocialAddictionUseKeyData(){
        $staticValue = [
            ['id' => 1, 'value' => trans('Setup::StaticDataConfigMessage.bidi')],
            ['id' => 2, 'value' => trans('Setup::StaticDataConfigMessage.cigarette')],
            ['id' => 3, 'value' => trans('Setup::StaticDataConfigMessage.hookah')],    
            ['id' => 4, 'value' => trans('Setup::StaticDataConfigMessage.chewing')],
            ['id' => 5, 'value' => trans('Setup::StaticDataConfigMessage.alcohol')]
        ];
        return $staticValue; 
    }

    /**
     * @DateOfCreation        22 June 2018
     * @ShortDescription      This function is responsible to get the staticData for Blood Group list
     * @return                 Array of status and message
     */
    public function getFamilyRelationData(){
        $staticValue = [
            ['id' => 1, 'value' => trans('Setup::StaticDataConfigMessage.grandparents')],
            ['id' => 2, 'value' => trans('Setup::StaticDataConfigMessage.parents')],
            ['id' => 3, 'value' => trans('Setup::StaticDataConfigMessage.brothers')],
            ['id' => 4, 'value' => trans('Setup::StaticDataConfigMessage.sisters')],
            ['id' => 5, 'value' => trans('Setup::StaticDataConfigMessage.aunts')],
            ['id' => 6, 'value' => trans('Setup::StaticDataConfigMessage.uncles')],
            ['id' => 7, 'value' => trans('Setup::StaticDataConfigMessage.first_cousins')],
            ['id' => 8, 'value' => trans('Setup::StaticDataConfigMessage.children')]
        ];
        return $staticValue; 
    }


    public function getAllergiesHistoryData()
    {
        $staticValue = [
            [
                'id'         => 1, 
                'value'      => trans('Setup::StaticDataConfigMessage.respiratory_allergies_history'),
                'type'       => 1, 
                'name'       => 'respiratory', 
                'input_type' => 'select', 
                'input_type_option' => $this->getDynamicOptionsData(['Breathlessness','Cough','Expectoration']),
                'placeholder'=> trans('Setup::StaticDataConfigMessage.respiratory_allergies_history'), 
                'cssClasses' => $this->checkboxCss('col-md-2','control-label', 'check-list-radio', ''),
                'isClearfix' => FALSE,
            ],
            [
                'id' => 2,
                'value' => trans('Setup::StaticDataConfigMessage.season_relation_allergies_history'),
                'type'=>1, 
                'input_type' => 'customcheckbox',
                'input_type_option'=>$this->getDynamicOptionsData(['yes','no']),
                'placeholder' =>'',
                'cssClasses' => $this->checkboxCss('col-md-2','control-label', 'check-list-radio', ''),
                'isClearfix' => FALSE,
            ],
            [
                'id' => 3,
                'value' => trans('Setup::StaticDataConfigMessage.best_season_relation_allergies_history'),
                'type'=>1, 
                'input_type' => 'select',
                'name'       => 'best_season_relation', 
                'input_type_option'=>$this->getDynamicOptionsData(['Winter','Summer','Rainy']),
                'placeholder' =>'',
                'cssClasses' => $this->checkboxCss('col-md-2','control-label', 'check-list-radio', ''),
                'isClearfix' => FALSE,
            ],
            [
                'id' => 4,
                'value' => trans('Setup::StaticDataConfigMessage.worst_season_relation_allergies_history'),
                'type'=>1, 
                'input_type' => 'select',
                'name'       => 'worst_season_relation', 
                'input_type_option'=>$this->getDynamicOptionsData(['Winter','Summer','Rainy']),
                'placeholder' =>'',
                'cssClasses' => $this->checkboxCss('col-md-2','control-label', 'check-list-radio', ''),
                'isClearfix' => TRUE,
            ],
            [
                'id' => 5,
                'value' => trans('Setup::StaticDataConfigMessage.time_relation_allergies_history'),
                'type'       => 1, 
                'name'       => 'time_relation_allergies_history', 
                'input_type' => 'customcheckbox',
                'input_type_option'=>$this->getDynamicOptionsData(['yes','no']),
                'placeholder'=> trans('Setup::StaticDataConfigMessage.time_relation_allergies_history'), 
                   'cssClasses' => $this->checkboxCss('col-md-2','control-label', 'check-list-radio', ''),
                'isClearfix' => FALSE,
            ],
            [
                'id' => 6,
                'value' => trans('Setup::StaticDataConfigMessage.time_relation_allergies_type'),
                'type'       => 1, 
                'name'       => 'time_relation_allergies_history', 
                'input_type' => 'select', 
                'input_type_option' => $this->getDynamicOptionsData(['Night','Early Morning','Morning after awaking','Day time']),
                'placeholder'=> trans('Setup::StaticDataConfigMessage.time_relation_allergies_history'), 
                   'cssClasses' => $this->checkboxCss('col-md-2','control-label', 'check-list-radio', ''),
                'isClearfix' => false,
            ],
            [
                'id' => 7,
                'value' => trans('Setup::StaticDataConfigMessage.aggravating_symptoms_allergies_history'),
                'type'       => 1, 
                'input_type' => 'checkbox',
                'input_type_option'=> $this->getDynamicOptionsData(['dust','fumes','foods','strong smell','smoke']),
                'placeholder' =>'',
                'cssClasses' => $this->checkboxCss('col-md-4','control-label', 'check-list-radio', ''),
                'isClearfix' => TRUE,
            ],
            [
                'id' => 8,
                'value' => trans('Setup::StaticDataConfigMessage.aggravating_symptoms_untowards'),
                'type'       => 1, 
                'input_type' => 'customcheckbox',
                'input_type_option'=>$this->getDynamicOptionsData(['yes','no']),
                'placeholder' =>'',
                'cssClasses' => $this->checkboxCss('col-md-4','control-label', 'check-list-radio', ''),
                'isClearfix' => FALSE,
            ],
            [
                'id' => 9,
                'value' => trans('Setup::StaticDataConfigMessage.response_anti_allergic'),
                'type'       => 1, 
                'input_type' => 'customcheckbox',
                'input_type_option'=>$this->getDynamicOptionsData(['yes','no']),
                'placeholder' =>'',
                'cssClasses' => $this->checkboxCss('col-md-2','control-label', 'check-list-radio', ''),
                'isClearfix' => TRUE,
            ],
            [
                'id' => 10,
                'value' => trans('Setup::StaticDataConfigMessage.any_positive_family_history'),
                'type'       => 1, 
                'input_type' => 'customcheckbox',
                'input_type_option'=>$this->getDynamicOptionsData(['yes','no']),
                'placeholder' =>'',
                'cssClasses' => $this->checkboxCss('col-md-2','control-label', 'check-list-radio', ''),
                'isClearfix' => FALSE,
            ],
            [
                'id' => 11,
                'value' => trans('Setup::StaticDataConfigMessage.any_positive_family_history_type'),
                'type'       => 1, 
                'name'       => 'any_positive_family_history_type', 
                'input_type' => 'select', 
                'input_type_option' => $this->getDynamicOptionsData(['Allergic Rhinitis','Asthma','Eczema','Atopy']),
                'placeholder'=> trans('Setup::StaticDataConfigMessage.time_relation_allergies_history'), 
                   'cssClasses' => $this->checkboxCss('col-md-2','control-label', 'check-list-radio', ''),
                'isClearfix' => FALSE,
            ],
            [
                'id' => 12,
                'value' => trans('Setup::StaticDataConfigMessage.skin_problems'),
                'type'       => 1, 
                'input_type' => 'text' ,
               'input_type_option'=>'',
               'placeholder' =>'',
               'cssClasses' => $this->textBoxCss('col-md-2', 'form-group', 'check-list-', ''),
               'restrictType'  => 'digitsOnly',
               'isClearfix' => FALSE,
            ],
            
        ];

        return $staticValue;
    }


    /**
     * @DateOfCreation        22 June 2018
     * @ShortDescription      This function is responsible to get the staticData for patient symptoms condition list
     * @return                 Array of status and message
     */
    public function getsymptomsTestData(){
        $staticValue = [
            [
               'id' => 1, 
               'value' => trans('Setup::StaticDataConfigMessage.cough_onset'),
               'type'=>1, 
               'formName'=>trans('Setup::StaticDataConfigMessage.form_name_cough'),
               'input_type' => 'text' ,
               'input_type_option'=>'',
               'placeholder' =>'',
               'cssClasses' => $this->textBoxCss('col-md-2', 'form-group', 'check-list-', ''),
               'restrictType'  => 'digitsOnly',
               'isClearfix' => FALSE,
            ],
            [
                'id' => 2,
                'value' => trans('Setup::StaticDataConfigMessage.cough_type'),
                'type'=>1, 
                'formName'=>trans('Setup::StaticDataConfigMessage.form_name_cough'),
                'input_type' => 'customcheckbox',
                'input_type_option'=>$this->getDynamicOptionsData(['Dry','Wet']),
                'placeholder' =>'',
                'cssClasses' => $this->checkboxCss('col-md-2','control-label', 'check-list-radio', ''),
                'isClearfix' => FALSE,
            ],
            [
                'id'         => 3, 
                'value'      => trans('Setup::StaticDataConfigMessage.cough_variation'),
                'type'       => 1, 
                'name'       => 'hopi_cough_variation', 
                'input_type' => 'select', 
                'input_type_option' => $this->getDynamicOptionsData(['Day','Night','Postural','Seasonal']),
                'placeholder'=> trans('Setup::StaticDataConfigMessage.cough_variation'), 
                   'cssClasses' => $this->checkboxCss('col-md-2','control-label', 'check-list-radio', ''),
                'isClearfix' => FALSE,
            ],
            [
               'id' => 4, 
               'value' => trans('Setup::StaticDataConfigMessage.cough_associate_with'),
               'type'=>1, 
               'formName'=>trans('Setup::StaticDataConfigMessage.form_name_cough'),
               'input_type' => 'text' ,
               'input_type_option'=>'',
               'placeholder' =>'',
               'cssClasses' => $this->textBoxCss('col-md-2', 'form-group', 'check-list-', ''),
               'isClearfix' => FALSE,
            ],
            [
               'id' => 5, 
               'value' => trans('Setup::StaticDataConfigMessage.aggravated_by'),
               'type'=>1, 
               'formName'=>trans('Setup::StaticDataConfigMessage.form_name_cough'),
               'input_type' => 'text' ,
               'input_type_option'=>'',
               'placeholder' =>'',
               'cssClasses' => $this->textBoxCss('col-md-2', 'form-group', 'check-list-', ''),
               'isClearfix' => FALSE,
            ],
            [
               'id' => 6, 
               'value' => trans('Setup::StaticDataConfigMessage.present_status'),
               'type'=>1, 
               'formName'=>trans('Setup::StaticDataConfigMessage.form_name_cough'),
               'input_type' => 'text' ,
               'input_type_option'=>'',
               'placeholder' =>'',
               'cssClasses' => $this->textBoxCss('col-md-2', 'form-group', 'check-list-', ''),
               'isClearfix' => TRUE,
            ],
            [
               'id' => 7, 
               'value' => trans('Setup::StaticDataConfigMessage.expectoration_color'),
               'type'=>2,
               'formName'=>trans('Setup::StaticDataConfigMessage.form_name_expectoration'), 
               'input_type' => 'text' ,
               'input_type_option'=>'',
               'placeholder' =>'',
               'cssClasses' => $this->textBoxCss('col-md-2', 'form-group', 'check-list-', ''),
               'isClearfix' => FALSE,
            ],
            [
               'id' => 8, 
               'value' => trans('Setup::StaticDataConfigMessage.expectoration_consistency'),
               'type'=>2,
               'formName'=>trans('Setup::StaticDataConfigMessage.form_name_expectoration'), 
               'input_type' => 'text' ,
               'input_type_option'=>'',
               'placeholder' =>'',
               'cssClasses' => $this->textBoxCss('col-md-2', 'form-group', 'check-list-', ''),
               'isClearfix' => FALSE,
            ],
            [
               'id' => 9, 
               'value' => trans('Setup::StaticDataConfigMessage.expectoration_quantity'),
               'type'=>2,
               'formName'=>trans('Setup::StaticDataConfigMessage.form_name_expectoration'), 
               'input_type' => 'text' ,
               'input_type_option'=>'',
               'placeholder' =>'',
               'cssClasses' => $this->textBoxCss('col-md-2', 'form-group', 'check-list-', ''),
               'isClearfix' => FALSE,
            ],
            [
               'id' => 10, 
               'value' => trans('Setup::StaticDataConfigMessage.expectoration_smell'),
               'type'=>2,
               'formName'=>trans('Setup::StaticDataConfigMessage.form_name_expectoration'), 
               'input_type' => 'text' ,
               'input_type_option'=>'',
               'placeholder' =>'',
               'cssClasses' => $this->textBoxCss('col-md-2', 'form-group', 'check-list-', ''),
               'isClearfix' => FALSE,
            ],
            [
                'id' => 11,
                'value' => trans('Setup::StaticDataConfigMessage.expectoration_variation'),
                'type'=>2,
                'formName'=>trans('Setup::StaticDataConfigMessage.form_name_expectoration'), 
                'input_type' => 'customcheckbox',
                'input_type_option'=>$this->getDynamicOptionsData(['Diurnal','Postural']),
                'placeholder' =>'',
                   'cssClasses' => $this->checkboxCss('col-md-2','control-label', 'check-list-radio', ''),
                'isClearfix' => FALSE,
            ],
            [
                'id' => 12,
                'value' => trans('Setup::StaticDataConfigMessage.expectoration_morning'),
                'type'=>2,
                'formName'=>trans('Setup::StaticDataConfigMessage.form_name_expectoration'), 
                'input_type' => 'customcheckbox',
                'input_type_option'=>$this->covertToOptionlabel($this->getYesNoData()),
                'placeholder' =>'',
                   'cssClasses' => $this->checkboxCss('col-md-2','control-label', 'check-list-radio', ''),
                'isClearfix' => TRUE,
            ],
            [
               'id' => 13, 
               'value' => trans('Setup::StaticDataConfigMessage.aggravated_by'),
               'type'=>2,
               'formName'=>trans('Setup::StaticDataConfigMessage.form_name_expectoration'), 
               'input_type' => 'text' ,
               'input_type_option'=>'',
               'placeholder' =>'',
               'cssClasses' => $this->textBoxCss('col-md-2', 'form-group', 'check-list-', ''),
               'isClearfix' => FALSE,
            ],
            [
               'id' => 14, 
               'value' => trans('Setup::StaticDataConfigMessage.present_status'),
               'type'=>2,
               'formName'=>trans('Setup::StaticDataConfigMessage.form_name_expectoration'), 
               'input_type' => 'customcheckbox',
               'input_type_option'=>$this->covertToOptionlabel($this->getYesNoData()),
               'placeholder' =>'',
               'cssClasses' => $this->checkboxCss('col-md-2','control-label', 'check-list-radio', ''),
               'isClearfix' => FALSE,
            ],
            [
               'id' => 15, 
               'value' => trans('Setup::StaticDataConfigMessage.hemoptysis_color'),
               'type'=>3,
               'formName'=>trans('Setup::StaticDataConfigMessage.form_name_hemoptysis'), 
               'input_type' => 'text' ,
               'input_type_option'=>'',
               'placeholder' =>'',
               'cssClasses' => $this->textBoxCss('col-md-2', 'form-group', 'check-list-', ''),
               'isClearfix' => FALSE,
            ],
            [
               'id' => 16, 
               'value' => trans('Setup::StaticDataConfigMessage.hemoptysis_frequency'),
               'type'=>3,
               'formName'=>trans('Setup::StaticDataConfigMessage.form_name_hemoptysis'), 
               'input_type' => 'text' ,
               'input_type_option'=>'',
               'placeholder' =>'',
               'cssClasses' => $this->textBoxCss('col-md-2', 'form-group', 'check-list-', ''),
               'isClearfix' => FALSE,
            ],
            [
               'id' => 17, 
               'value' => trans('Setup::StaticDataConfigMessage.hemoptysis_quantity'),
               'type'=>3,
               'formName'=>trans('Setup::StaticDataConfigMessage.form_name_hemoptysis'), 
               'input_type' => 'text' ,
               'input_type_option'=>'',
               'placeholder' =>'',
               'cssClasses' => $this->textBoxCss('col-md-2', 'form-group', 'check-list-', ''),
               'isClearfix' => FALSE,
            ],
            [
               'id' => 18, 
               'value' => trans('Setup::StaticDataConfigMessage.hemoptysis_postural_variation'),
               'type'=>3,
               'formName'=>trans('Setup::StaticDataConfigMessage.form_name_hemoptysis'), 
               'input_type' => 'text' ,
               'input_type_option'=>'',
               'placeholder' =>'',
               'cssClasses' => $this->textBoxCss('col-md-2', 'form-group', 'check-list-', ''),
               'isClearfix' => FALSE,
            ],
            [
               'id' => 19, 
               'value' => trans('Setup::StaticDataConfigMessage.hemoptysis_last_episode'),
               'type'=>3,
               'formName'=>trans('Setup::StaticDataConfigMessage.form_name_hemoptysis'), 
               'input_type' => 'text' ,
               'input_type_option'=>'',
               'placeholder' =>'',
               'cssClasses' => $this->textBoxCss('col-md-2', 'form-group', 'check-list-', ''),
               'isClearfix' => FALSE,
            ],
            [
               'id' => 20, 
               'value' => trans('Setup::StaticDataConfigMessage.hemoptysis_postural_symptoms'),
               'type'=>3,
               'formName'=>trans('Setup::StaticDataConfigMessage.form_name_hemoptysis'), 
               'input_type' => 'text' ,
               'input_type_option'=>'',
               'placeholder' =>'',
               'cssClasses' => $this->textBoxCss('col-md-2', 'form-group', 'check-list-', ''),
               'isClearfix' => FALSE,
            ],
            [
               'id' => 21, 
               'value' => trans('Setup::StaticDataConfigMessage.hemoptysis_associate_with'),
               'type'=>3,
               'formName'=>trans('Setup::StaticDataConfigMessage.form_name_hemoptysis'), 
               'input_type' => 'text' ,
               'input_type_option'=>'',
               'placeholder' =>'',
               'cssClasses' => $this->textBoxCss('col-md-2', 'form-group', 'check-list-', ''),
               'isClearfix' => FALSE,
            ],
            [
               'id' => 22, 
               'value' => trans('Setup::StaticDataConfigMessage.aggravated_by'),
               'type'=>3,
               'formName'=>trans('Setup::StaticDataConfigMessage.form_name_hemoptysis'), 
               'input_type' => 'text' ,
               'input_type_option'=>'',
               'placeholder' =>'',
               'cssClasses' => $this->textBoxCss('col-md-2', 'form-group', 'check-list-', ''),
               'isClearfix' => FALSE,
            ],
            [
               'id' => 23, 
               'value' => trans('Setup::StaticDataConfigMessage.present_status'),
               'type'=>3,
               'formName'=>trans('Setup::StaticDataConfigMessage.form_name_hemoptysis'), 
               'input_type' => 'customcheckbox',
               'input_type_option'=>$this->covertToOptionlabel($this->getYesNoData()),
               'placeholder' =>'',
                'cssClasses' => $this->checkboxCss('col-md-2','control-label', 'check-list-radio', ''),
               'isClearfix' => FALSE,
            ],
            [
               'id' => 24, 
               'value' => trans('Setup::StaticDataConfigMessage.dyspnoea_onset'),
               'type'=>4,
               'formName'=>trans('Setup::StaticDataConfigMessage.form_name_dyspnoea'),  
               'input_type' => 'text' ,
               'input_type_option'=>'',
               'placeholder' =>'',
               'cssClasses' => $this->textBoxCss('col-md-2', 'form-group', 'check-list-', ''),
               'isClearfix' => FALSE,
            ],
            [
               'id' => 25, 
               'value' => trans('Setup::StaticDataConfigMessage.dyspnoea_progression'),
               'type'=>4,
               'formName'=>trans('Setup::StaticDataConfigMessage.form_name_dyspnoea'),  
               'input_type' => 'text' ,
               'input_type_option'=>'',
               'placeholder' =>'',
               'cssClasses' => $this->textBoxCss('col-md-2', 'form-group', 'check-list-', ''),
               'isClearfix' => FALSE,
            ],
            [
               'id' => 26, 
               'value' => trans('Setup::StaticDataConfigMessage.dyspnoea_mmrc_grading'),
               'type'=>4,
               'formName'=>trans('Setup::StaticDataConfigMessage.form_name_dyspnoea'),  
               'input_type' => 'text' ,
               'input_type_option'=>'',
               'placeholder' =>'',
               'cssClasses' => $this->textBoxCss('col-md-2', 'form-group', 'check-list-', ''),
               'isClearfix' => FALSE,
            ],
            [
               'id' => 27, 
               'value' => trans('Setup::StaticDataConfigMessage.dyspnoea_pnd'),
               'type'=>4,
               'formName'=>trans('Setup::StaticDataConfigMessage.form_name_dyspnoea'),  
               'input_type' => 'text' ,
               'input_type_option'=>'',
               'placeholder' =>'',
               'cssClasses' => $this->textBoxCss('col-md-2', 'form-group', 'check-list-', ''),
               'isClearfix' => FALSE,
            ],
            [
               'id' => 28, 
               'value' => trans('Setup::StaticDataConfigMessage.dyspnoea_orthopnoea'),
               'type'=>4,
               'formName'=>trans('Setup::StaticDataConfigMessage.form_name_dyspnoea'),  
               'input_type' => 'text' ,
               'input_type_option'=>'',
               'placeholder' =>'',
               'cssClasses' => $this->textBoxCss('col-md-2', 'form-group', 'check-list-', ''),
               'isClearfix' => FALSE,
            ],
            [
               'id' => 29, 
               'value' => trans('Setup::StaticDataConfigMessage.dyspnoea_associate_with'),
               'type'=>4,
               'formName'=>trans('Setup::StaticDataConfigMessage.form_name_dyspnoea'),  
               'input_type' => 'text' ,
               'input_type_option'=>'',
               'placeholder' =>'',
               'cssClasses' => $this->textBoxCss('col-md-2', 'form-group', 'check-list-', ''),
               'isClearfix' => FALSE,
            ],
            [
               'id' => 30, 
               'value' => trans('Setup::StaticDataConfigMessage.aggravated_by'),
               'type'=>4,
               'formName'=>trans('Setup::StaticDataConfigMessage.form_name_dyspnoea'),  
               'input_type' => 'text' ,
               'input_type_option'=>'',
               'placeholder' =>'',
               'cssClasses' => $this->textBoxCss('col-md-2', 'form-group', 'check-list-', ''),
               'isClearfix' => FALSE,
            ],
            [
               'id' => 31, 
               'value' => trans('Setup::StaticDataConfigMessage.present_status'),
               'type'=>4,
               'formName'=>trans('Setup::StaticDataConfigMessage.form_name_dyspnoea'),  
               'input_type' => 'customcheckbox',
               'input_type_option'=>$this->covertToOptionlabel($this->getYesNoData()),
               'placeholder' =>'',
               'cssClasses' => $this->checkboxCss('col-md-2','control-label', 'check-list-radio', ''),
               'isClearfix' => FALSE,
            ],
            [
               'id' => 32, 
               'value' => trans('Setup::StaticDataConfigMessage.wheeze_duration'),
               'type'=>5, 
               'formName'=>trans('Setup::StaticDataConfigMessage.form_name_wheeze'),
               'input_type' => 'text' ,
               'input_type_option'=>'',
               'placeholder' =>'',
               'cssClasses' => $this->textBoxCss('col-md-2', 'form-group', 'check-list-', ''),
               'isClearfix' => FALSE,
            ],
            [
               'id' => 33, 
               'value' => trans('Setup::StaticDataConfigMessage.wheeze_periodicity'),
               'type'=>5, 
               'formName'=>trans('Setup::StaticDataConfigMessage.form_name_wheeze'),
               'input_type' => 'text' ,
               'input_type_option'=>'',
               'placeholder' =>'',
               'cssClasses' => $this->textBoxCss('col-md-2', 'form-group', 'check-list-', ''),
               'isClearfix' => FALSE,
            ],
            [
                'id' => 34,
                'value' => trans('Setup::StaticDataConfigMessage.wheeze_variation'),
                'type'=>5, 
                'formName'=>trans('Setup::StaticDataConfigMessage.form_name_wheeze'),
                'input_type' => 'customcheckbox',
                'input_type_option'=>$this->getDynamicOptionsData(['Seasonal','Diurnal']),
                'placeholder' =>'',
                   'cssClasses' => $this->checkboxCss('col-md-2','control-label', 'check-list-radio', ''),
                'isClearfix' => FALSE,
            ],
            [
                'id'         => 35, 
                'value'      => trans('Setup::StaticDataConfigMessage.wheeze_allergic_features'),
                'type'       => 5, 
                'formName'=>trans('Setup::StaticDataConfigMessage.form_name_wheeze'),
                'input_type' => 'text' ,
                'input_type_option'=>'',
                'placeholder' =>'',
                'cssClasses' => $this->textBoxCss('col-md-2', 'form-group', 'check-list-', ''),
                'isClearfix' => FALSE,
            ],
            [
                'id'         => 36, 
                'value'      => trans('Setup::StaticDataConfigMessage.wheeze_urticaria'),
                'type'       => 5, 
                'formName'=>trans('Setup::StaticDataConfigMessage.form_name_wheeze'),
                'input_type' => 'text' ,
                'input_type_option'=>'',
                'placeholder' =>'',
                'cssClasses' => $this->textBoxCss('col-md-2', 'form-group', 'check-list-', ''),
                'isClearfix' => FALSE,
            ],
            [
                'id'         => 37, 
                'value'      => trans('Setup::StaticDataConfigMessage.wheeze_recurrent_sneezing'),
                'type'       => 5, 
                'formName'=>trans('Setup::StaticDataConfigMessage.form_name_wheeze'),
                'input_type' => 'text' ,
                'input_type_option'=>'',
                'placeholder' =>'',
                'cssClasses' => $this->textBoxCss('col-md-2', 'form-group', 'check-list-', ''),
                'isClearfix' => TRUE,
            ],
            [
               'id' => 38, 
               'value' => trans('Setup::StaticDataConfigMessage.aggravated_by'),
               'type'=>5, 
               'formName'=>trans('Setup::StaticDataConfigMessage.form_name_wheeze'),
               'input_type' => 'text' ,
               'input_type_option'=>'',
               'placeholder' =>'',
               'cssClasses' => $this->textBoxCss('col-md-2', 'form-group', 'check-list-', ''),
               'isClearfix' => FALSE,
            ],
            [
               'id' => 39, 
               'value' => trans('Setup::StaticDataConfigMessage.present_status'),
               'type' => 5, 
               'formName'=>trans('Setup::StaticDataConfigMessage.form_name_wheeze'),
               'input_type' => 'customcheckbox',
               'input_type_option'=>$this->covertToOptionlabel($this->getYesNoData()),
               'placeholder' =>'',
               'cssClasses' => $this->checkboxCss('col-md-2','control-label', 'check-list-radio', ''),
               'isClearfix' => FALSE,
            ],
            [
                'id'         => 40, 
                'value'      => trans('Setup::StaticDataConfigMessage.chest_pain_onset'),
                'type'       => 6, 
                'formName'=>trans('Setup::StaticDataConfigMessage.form_name_chest_pain'),
                'input_type' => 'text' ,
                'input_type_option'=>'',
                'placeholder' =>'',
                'cssClasses' => $this->textBoxCss('col-md-2', 'form-group', 'check-list-', ''),
                'isClearfix' => FALSE,
            ],
            [
                'id'         => 41, 
                'value'      => trans('Setup::StaticDataConfigMessage.chest_pain_location'),
                'type'       => 6, 
                'formName'=>trans('Setup::StaticDataConfigMessage.form_name_chest_pain'),
                'input_type' => 'text' ,
                'input_type_option'=>'',
                'placeholder' =>'',
                'cssClasses' => $this->textBoxCss('col-md-2', 'form-group', 'check-list-', ''),
                'isClearfix' => FALSE,
            ],
            [
                'id'         => 42, 
                'value'      => trans('Setup::StaticDataConfigMessage.chest_pain_duration'),
                'type'       => 6, 
                'formName'=>trans('Setup::StaticDataConfigMessage.form_name_chest_pain'),
                'input_type' => 'text' ,
                'input_type_option'=>'',
                'placeholder' =>'',
                'cssClasses' => $this->textBoxCss('col-md-2', 'form-group', 'check-list-', ''),
                'isClearfix' => FALSE,
            ],
            [
                'id'         => 43, 
                'value'      => trans('Setup::StaticDataConfigMessage.chest_pain_character'),
                'type'       => 6, 
                'formName'=>trans('Setup::StaticDataConfigMessage.form_name_chest_pain'),
                'input_type' => 'text' ,
                'input_type_option'=>'',
                'placeholder' =>'',
                'cssClasses' => $this->textBoxCss('col-md-2', 'form-group', 'check-list-', ''),
                'isClearfix' => FALSE,
            ],
            [
                'id'         => 44, 
                'value'      => trans('Setup::StaticDataConfigMessage.chest_pain_associated_feature'),
                'type'       => 6, 
                'formName'=>trans('Setup::StaticDataConfigMessage.form_name_chest_pain'),
                'input_type' => 'text' ,
                'input_type_option'=>'',
                'placeholder' =>'',
                'cssClasses' => $this->textBoxCss('col-md-2', 'form-group', 'check-list-', ''),
                'isClearfix' => FALSE,
            ],
            [
                'id'         => 45, 
                'value'      => trans('Setup::StaticDataConfigMessage.chest_pain_radiation'),
                'type'       => 6, 
                'formName'=>trans('Setup::StaticDataConfigMessage.form_name_chest_pain'),
                'input_type' => 'text' ,
                'input_type_option'=>'',
                'placeholder' =>'',
                'cssClasses' => $this->textBoxCss('col-md-2', 'form-group', 'check-list-', ''),
                'isClearfix' => FALSE,
            ],
            [
                'id'         => 46, 
                'value'      => trans('Setup::StaticDataConfigMessage.chest_pain_time'),
                'type'       => 6, 
                'formName'=>trans('Setup::StaticDataConfigMessage.form_name_chest_pain'),
                'input_type' => 'text' ,
                'input_type_option'=>'',
                'placeholder' =>'',
                'cssClasses' => $this->textBoxCss('col-md-2', 'form-group', 'check-list-', ''),
                'isClearfix' => FALSE,
            ],
            [
                'id'         => 47, 
                'value'      => trans('Setup::StaticDataConfigMessage.chest_pain_severity'),
                'type'       => 6, 
                'formName'=>trans('Setup::StaticDataConfigMessage.form_name_chest_pain'),
                'input_type' => 'text' ,
                'input_type_option'=>'',
                'placeholder' =>'',
                'cssClasses' => $this->textBoxCss('col-md-2', 'form-group', 'check-list-', ''),
                'isClearfix' => FALSE,
            ],
            [
                'id' => 48, 
                'value' => trans('Setup::StaticDataConfigMessage.aggravated_by'),
                'type'=>6, 
                'formName'=>trans('Setup::StaticDataConfigMessage.form_name_chest_pain'),
                'input_type' => 'text' ,
                'input_type_option'=>'',
                'placeholder' =>'',
                'cssClasses' => $this->textBoxCss('col-md-2', 'form-group', 'check-list-', ''),
                'isClearfix' => FALSE,
            ],
            [
                'id'         => 49, 
                'value'      => trans('Setup::StaticDataConfigMessage.fever_grade'),
                'type'       => 7, 
                'formName'=>trans('Setup::StaticDataConfigMessage.form_name_fever'),
                'input_type' => 'text' ,
                'input_type_option'=>'',
                'placeholder' =>'',
                'cssClasses' => $this->textBoxCss('col-md-2', 'form-group', 'check-list-', ''),
                'isClearfix' => FALSE,
            ],
            [
                'id'         => 50, 
                'value'      => trans('Setup::StaticDataConfigMessage.fever_max_temp'),
                'type'       => 7, 
                'formName'=>trans('Setup::StaticDataConfigMessage.form_name_fever'),
                'input_type' => 'text' ,
                'input_type_option'=>'',
                'placeholder' =>'',
                'cssClasses' => $this->textBoxCss('col-md-2', 'form-group', 'check-list-', ''),
                'isClearfix' => FALSE,
            ],
            [
                'id'         => 51, 
                'value'      => trans('Setup::StaticDataConfigMessage.fever_type'),
                'type'       => 7, 
                'formName'=>trans('Setup::StaticDataConfigMessage.form_name_fever'),
                'input_type' => 'text' ,
                'input_type_option'=>'',
                'placeholder' =>'',
                'cssClasses' => $this->textBoxCss('col-md-2', 'form-group', 'check-list-', ''),
                'isClearfix' => FALSE,
            ],
            [
                'id'         => 52, 
                'value'      => trans('Setup::StaticDataConfigMessage.fever_associated_with_chills'),
                'type'       => 7, 
                'formName'=>trans('Setup::StaticDataConfigMessage.form_name_fever'),
                'input_type' => 'text' ,
                'input_type_option'=>'',
                'placeholder' =>'',
                'cssClasses' => $this->textBoxCss('col-md-2', 'form-group', 'check-list-', ''),
                'isClearfix' => FALSE,
            ],
            [
                'id'         => 53, 
                'value'      => trans('Setup::StaticDataConfigMessage.fever_diurnal_variation'),
                'type'       => 7, 
                'formName'=>trans('Setup::StaticDataConfigMessage.form_name_fever'),
                'input_type' => 'text' ,
                'input_type_option'=>'',
                'placeholder' =>'',
                'cssClasses' => $this->textBoxCss('col-md-2', 'form-group', 'check-list-', ''),
                'isClearfix' => FALSE,
            ],
            [
                'id'         => 54, 
                'value'      => trans('Setup::StaticDataConfigMessage.fever_evening_rise_of_temp'),
                'type'       => 7, 
                'formName'=>trans('Setup::StaticDataConfigMessage.form_name_fever'),
                'input_type' => 'text' ,
                'input_type_option'=>'',
                'placeholder' =>'',
                'cssClasses' => $this->textBoxCss('col-md-2', 'form-group', 'check-list-', ''),
                'isClearfix' => TRUE,
            ],
            [
                'id'         => 55, 
                'value'      => trans('Setup::StaticDataConfigMessage.fever_night_sweats'),
                'type'       => 7, 
                'formName'=>trans('Setup::StaticDataConfigMessage.form_name_fever'),
                'input_type' => 'text' ,
                'input_type_option'=>'',
                'placeholder' =>'',
                'cssClasses' => $this->textBoxCss('col-md-2', 'form-group', 'check-list-', ''),
                'isClearfix' => FALSE,
            ],
            [
                'id'         => 56, 
                'value'      => trans('Setup::StaticDataConfigMessage.fever_relieved_by'),
                'type'       => 7, 
                'formName'=>trans('Setup::StaticDataConfigMessage.form_name_fever'),
                'input_type' => 'text' ,
                'input_type_option'=>'',
                'placeholder' =>'',
                'cssClasses' => $this->textBoxCss('col-md-2', 'form-group', 'check-list-', ''),
                'isClearfix' => FALSE,
            ],
            [
                'id'         => 57, 
                'value'      => trans('Setup::StaticDataConfigMessage.present_status'),
                'type'       => 7, 
                'formName'=>trans('Setup::StaticDataConfigMessage.form_name_fever'),
                'input_type' => 'text' ,
                'input_type_option'=>'',
                'placeholder' =>'',
                'cssClasses' => $this->textBoxCss('col-md-2', 'form-group', 'check-list-', ''),
                'isClearfix' => FALSE,
            ],
            [
               'id' => 58, 
               'value' => trans('Setup::StaticDataConfigMessage.weight_loss_type1'),
               'type' => 8, 
               'formName'=>trans('Setup::StaticDataConfigMessage.form_name_weight_loose'),
               'input_type' => 'customcheckbox',
               'input_type_option'=>$this->getDynamicOptionsData(['Quantified','Unquantified']),
               'placeholder' =>'',
               'cssClasses' => $this->checkboxCss('col-md-3','control-label', 'check-list-radio', ''),
               'isClearfix' => FALSE,
            ],
            [
               'id' => 59, 
               'value' => trans('Setup::StaticDataConfigMessage.weight_loss_type2'),
               'type' => 8, 
               'formName'=>trans('Setup::StaticDataConfigMessage.form_name_weight_loose'),
               'input_type' => 'customcheckbox',
               'input_type_option'=>$this->getDynamicOptionsData(['lntentional','Unintentional']),
               'placeholder' =>'',
               'cssClasses' => $this->checkboxCss('col-md-3','control-label', 'check-list-radio', ''),
               'isClearfix' => FALSE,
            ],
            [
               'id' => 60, 
               'value' => trans('Setup::StaticDataConfigMessage.weight_loss_type3'),
               'type' => 8, 
               'formName'=>trans('Setup::StaticDataConfigMessage.form_name_weight_loose'),
               'input_type' => 'customcheckbox',
               'input_type_option'=>$this->getDynamicOptionsData(['Significant','Insignificant']),
               'placeholder' =>'',
               'cssClasses' => $this->checkboxCss('col-md-3','control-label', 'check-list-radio', ''),
               'isClearfix' => FALSE,
            ],
              [
               'id' => 63, 
               'value' => trans('Setup::StaticDataConfigMessage.anorexia'),
               'type' => 9, 
               'formName'=>trans('Setup::StaticDataConfigMessage.form_name_other_complaints'),
               'input_type' => 'customcheckbox',
               'input_type_option'=>$this->covertToOptionlabel($this->getYesNoData()),
               'placeholder' =>'',
               'cssClasses' => $this->checkboxCss('col-md-2','control-label', 'check-list-radio', ''),
               'isClearfix' => FALSE,
            ],
            [

               'id' => 62, 
               'value' => trans('Setup::StaticDataConfigMessage.hoarseness_of_voice'),
               'type' => 9, 
               'formName'=>trans('Setup::StaticDataConfigMessage.form_name_other_complaints'),
               'input_type' => 'customcheckbox',
               'input_type_option'=>$this->covertToOptionlabel($this->getYesNoData()),
               'placeholder' =>'',
               'cssClasses' => $this->checkboxCss('col-md-2','control-label', 'check-list-radio', ''),
               'isClearfix' => FALSE,
            ],
            [
               'id' => 63, 
               'value' => trans('Setup::StaticDataConfigMessage.ptosis'),
               'type' => 9, 
               'formName'=>trans('Setup::StaticDataConfigMessage.form_name_other_complaints'),
               'input_type' => 'customcheckbox',
               'input_type_option'=>$this->covertToOptionlabel($this->getYesNoData()),
               'placeholder' =>'',
               'cssClasses' => $this->checkboxCss('col-md-2','control-label', 'check-list-radio', ''),
               'isClearfix' => FALSE,
            ],
            [
               'id' => 64, 
               'value' => trans('Setup::StaticDataConfigMessage.recurrent_respiratory_infection'),
               'type' => 9, 
               'formName'=>trans('Setup::StaticDataConfigMessage.form_name_other_complaints'),
               'input_type' => 'customcheckbox',
               'input_type_option'=>$this->covertToOptionlabel($this->getYesNoData()),
               'placeholder' =>'',
               'cssClasses' => $this->checkboxCss('col-md-3','control-label', 'check-list-radio', ''),
               'isClearfix' => FALSE,
            ],
            [
               'id' => 65, 
               'value' => trans('Setup::StaticDataConfigMessage.collagen_vascular_disease'),
               'type' => 9, 
               'formName'=>trans('Setup::StaticDataConfigMessage.form_name_other_complaints'),
               'input_type' => 'select', 
               'input_type_option' => $this->getDynamicOptionsData(['Joint pain','rash','oral ulcers','seizures','excessive hairfall']),
               'placeholder'=> trans('Setup::StaticDataConfigMessage.cough_variation'), 
               'cssClasses' => $this->textBoxCss('col-md-4', 'form-group', 'control-label', ''),
               'isClearfix' => TRUE,
            ],
            [
               'id' => 66, 
               'value' => trans('Setup::StaticDataConfigMessage.palpitation'),
               'type' => 9, 
               'formName'=>trans('Setup::StaticDataConfigMessage.form_name_other_complaints'),
               'input_type' => 'customcheckbox',
               'input_type_option'=>$this->covertToOptionlabel($this->getYesNoData()),
               'placeholder' =>'',
               'cssClasses' => $this->checkboxCss('col-md-2','control-label', 'check-list-radio', ''),
               'isClearfix' => FALSE,
            ],
            [
               'id' => 67, 
               'value' => trans('Setup::StaticDataConfigMessage.bladder'),
               'type' => 9, 
               'formName'=>trans('Setup::StaticDataConfigMessage.form_name_other_complaints'),
               'input_type' => 'customcheckbox',
               'input_type_option'=>$this->covertToOptionlabel($this->getYesNoData()),
               'placeholder' =>'',
               'cssClasses' => $this->checkboxCss('col-md-2','control-label', 'check-list-radio', ''),
               'isClearfix' => FALSE,
            ],
            [
               'id' => 68, 
               'value' => trans('Setup::StaticDataConfigMessage.swelling_of_face'),
               'type' => 9, 
               'formName'=>trans('Setup::StaticDataConfigMessage.form_name_other_complaints'),
               'input_type' => 'customcheckbox',
               'input_type_option'=>$this->covertToOptionlabel($this->getYesNoData()),
               'placeholder' =>'',
               'cssClasses' => $this->checkboxCss('col-md-3','control-label', 'check-list-radio', ''),
               'isClearfix' => FALSE,
            ],
            [
               'id' => 69, 
               'value' => trans('Setup::StaticDataConfigMessage.cns_symptoms'),
               'type' => 9, 
               'formName'=>trans('Setup::StaticDataConfigMessage.form_name_other_complaints'),
               'input_type' => 'select', 
               'input_type_option' => $this->getDynamicOptionsData(['LOC','Head ache','weakness','vomiting']),
               'placeholder'=> trans('Setup::StaticDataConfigMessage.cough_variation'), 
               'cssClasses' => $this->textBoxCss('col-md-4', 'form-group', 'control-label', ''),
               'isClearfix' => FALSE,
            ],
            
        ];
        return $staticValue; 
    }

    /**
     * @DateOfCreation        22 June 2018
     * @ShortDescription      This function is responsible to get the staticData for Domestic factor condtion list
     * @return                 Array of status and message
     */
    public function getLaboratoryTestData(){
        $staticValue = [
            [
               'id' => 1, 
               'value' => trans('Setup::StaticDataConfigMessage.cbc_hb'),
               'type'=>1, 
               'input_type' => 'text' ,
               'input_type_option'=>'',
               'placeholder' =>'',
               'cssClasses' => $this->textBoxCss('col-md-2', 'form-group', 'check-list-', ''),
               'isClearfix' => FALSE,
            ],
            [
             'id' => 2, 
               'value' => trans('Setup::StaticDataConfigMessage.cbc_tlc'),
               'type'=>1, 
               'input_type' => 'text' ,
               'input_type_option'=>'',
               'placeholder' =>'',
               'cssClasses' => $this->textBoxCss('col-md-2', 'form-group', '', ''),
               'isClearfix' => FALSE,
          ],
            [
             'id' => 3, 
               'value' => trans('Setup::StaticDataConfigMessage.cbc_platelets'),
               'type'=>1, 
               'input_type' => 'text' ,
               'input_type_option'=>'',
               'placeholder' =>'',
               'cssClasses' => $this->textBoxCss('col-md-2', 'form-group', '', ''),
               'isClearfix' => FALSE,
          ],
            [
             'id' => 4, 
               'value' => trans('Setup::StaticDataConfigMessage.cbc_mcv'),
               'type'=>1, 
               'input_type' => 'text' ,
               'input_type_option'=>'',
               'placeholder' =>'',
               'cssClasses' => $this->textBoxCss('col-md-2', 'form-group', '', ''),
               'isClearfix' => FALSE,
          ],
            [
             'id' => 5, 
               'value' => trans('Setup::StaticDataConfigMessage.dlc_l'),
               'type'=>2, 
               'input_type' => 'text' ,
               'input_type_option'=>'',
               'placeholder' =>'',
               'cssClasses' => $this->textBoxCss('col-md-2', 'form-group', '', ''),
               'isClearfix' => FALSE,
          ],
            [
             'id' => 6, 
               'value' => trans('Setup::StaticDataConfigMessage.dlc_n'),
               'type'=>2, 
               'input_type' => 'text' ,
               'input_type_option'=>'',
               'placeholder' =>'',
               'cssClasses' => $this->textBoxCss('col-md-2', 'form-group', '', ''),
               'isClearfix' => FALSE,
          ],
            [
             'id' => 7, 
               'value' => trans('Setup::StaticDataConfigMessage.dlc_e'),
               'type'=>2, 
               'input_type' => 'text' ,
               'input_type_option'=>'',
               'placeholder' =>'',
               'cssClasses' => $this->textBoxCss('col-md-2', 'form-group', '', ''),
               'isClearfix' => FALSE,
          ],
            [
             'id' => 8, 
               'value' => trans('Setup::StaticDataConfigMessage.dlc_m'),
               'type'=>2, 
               'input_type' => 'text' ,
               'input_type_option'=>'',
               'placeholder' =>'',
               'cssClasses' => $this->textBoxCss('col-md-2', 'form-group', '', ''),
               'isClearfix' => FALSE,
          ],
            [
             'id' => 9, 
               'value' => trans('Setup::StaticDataConfigMessage.dlc_b'),
               'type'=>2, 
               'input_type' => 'text' ,
               'input_type_option'=>'',
               'placeholder' =>'',
               'cssClasses' => $this->textBoxCss('col-md-2', 'form-group', '', ''),
               'isClearfix' => FALSE,
          ],
            [
                'id' => 10,
                'value' => trans('Setup::StaticDataConfigMessage.b_sugar_f'),
                'type'=>3, 
                'input_type' => 'text' ,
                'input_type_option'=>'',
                'placeholder' =>'',
                'cssClasses' => $this->textBoxCss('col-md-2', 'form-group', '', ''),
                'isClearfix' => FALSE,
            ],
            [
                'id' => 11,
                'value' => trans('Setup::StaticDataConfigMessage.b_sugar_pp'),
                'type'=>3, 
                'input_type' => 'text' ,
                'input_type_option'=>'',
                'placeholder' =>'',
                'cssClasses' => $this->textBoxCss('col-md-2', 'form-group', '', ''),
                'isClearfix' => FALSE,
            ],
            [
                'id' => 12,
                'value' => trans('Setup::StaticDataConfigMessage.renal_urea'),
                'type'=>4, 
                'input_type' => 'text' ,
                'input_type_option'=>'',
                'placeholder' =>'',
                'cssClasses' => $this->textBoxCss('col-md-2', 'form-group', '', ''),
                'isClearfix' => FALSE,
            ],
            [
                'id' => 13,
                'value' => trans('Setup::StaticDataConfigMessage.renal_creatinine'),
                'type'=>4, 
                'input_type' => 'text' ,
                'input_type_option'=>'',
                'placeholder' =>'',
                'cssClasses' => $this->textBoxCss('col-md-2', 'form-group', '', ''),
                'isClearfix' => FALSE,
            ],
            [
                'id' => 14,
                'value' => trans('Setup::StaticDataConfigMessage.liver_bilirubi'),
                'type'=>5, 
                'input_type' => 'text' ,
                'input_type_option'=>'',
                'placeholder' =>'',
                'cssClasses' => $this->textBoxCss('col-md-2', 'form-group', '', ''),
                'isClearfix' => FALSE,
            ],
            [
                'id' => 15,
                'value' => trans('Setup::StaticDataConfigMessage.liver_sgot'),
                'type'=>5, 
                'input_type' => 'text' ,
                'input_type_option'=>'',
                'placeholder' =>'',
                'cssClasses' => $this->textBoxCss('col-md-2', 'form-group', '', ''),
                'isClearfix' => FALSE,
            ],
            [
                'id' => 16,
                'value' => trans('Setup::StaticDataConfigMessage.liver_sgpt'),
                'type'=>5, 
                'input_type' => 'text' ,
                'input_type_option'=>'',
                'placeholder' =>'',
                'cssClasses' => $this->textBoxCss('col-md-2', 'form-group', '', ''),
                'isClearfix' => FALSE,
            ],
            [
                'id' => 17,
                'value' => trans('Setup::StaticDataConfigMessage.liver_hepatitis'),
                'type'=>5, 
                'input_type' => 'select' ,
                'input_type_option'=>'laboratory_test_hepatitis_option',
                'placeholder' =>'',
                'cssClasses' => $this->textBoxCss('col-md-2', 'form-group', '', ''),
                'isClearfix' => FALSE,
            ],
            [
                'id' => 18,
                'value' => trans('Setup::StaticDataConfigMessage.urinalysis_protein'),
                'type'=>6, 
                'input_type' => 'text' ,
                'input_type_option'=>'',
                'placeholder' =>'',
                'cssClasses' => $this->textBoxCss('col-md-2', 'form-group', '', ''),
                'isClearfix' => FALSE,
            ],
            [
                'id' => 19,
                'value' => trans('Setup::StaticDataConfigMessage.urinalysis_sugar'),
                'type'=>6, 
                'input_type' => 'text' ,
                'input_type_option'=>'',
                'placeholder' =>'',
                'cssClasses' => $this->textBoxCss('col-md-2', 'form-group', '', ''),
                'isClearfix' => FALSE,
            ],
            [
                'id' => 20,
                'value' => trans('Setup::StaticDataConfigMessage.urinalysis_cast'),
                'type'=>6, 
                'input_type' => 'text' ,
                'input_type_option'=>'',
                'placeholder' =>'',
                'cssClasses' => $this->textBoxCss('col-md-2', 'form-group', '', ''),
                'isClearfix' => FALSE,
            ],
            [
                'id' => 21,
                'value' => trans('Setup::StaticDataConfigMessage.urinalysis_pus_cells'),
                'type'=>6, 
                'input_type' => 'text' ,
                'input_type_option'=>'',
                'placeholder' =>'',
                'cssClasses' => $this->textBoxCss('col-md-2', 'form-group', '', ''),
                'isClearfix' => FALSE,
            ],
            [
                'id' => 22,
                'value' => trans('Setup::StaticDataConfigMessage.sputum_afb'),
                'type'=>7, 
                'input_type' => 'select' ,
                'input_type_option'=>'laboratory_test_sputum_afb_option',
                'placeholder' =>'',
                'cssClasses' => $this->textBoxCss('col-md-2', 'form-group', '', ''),
                'isClearfix' => FALSE,
            ],
            [
                'id' => 23,
                'value' => trans('Setup::StaticDataConfigMessage.sputum_fungus'),
                'type'=>7, 
                'input_type' => 'text' ,
                'input_type_option'=>'',
                'placeholder' =>'',
                'cssClasses' => $this->textBoxCss('col-md-2', 'form-group', '', ''),
                'isClearfix' => FALSE,
            ],
            [
                'id' => 24,
                'value' => trans('Setup::StaticDataConfigMessage.collagen_vascular_ana'),
                'type'=>8, 
                'input_type' => 'customcheckbox',
                'input_type_option'=>'posative_negative_option',
                'placeholder' =>'',
                'cssClasses' => $this->checkboxCss('col-md-12 rp', 'col-md-3 align-checkbox-list', 'col-md-9', 'form-group checkbox-listing checkbox-formgroup'),
                'isClearfix' => FALSE,
            ],
            [
                'id' => 25,
                'value' => trans('Setup::StaticDataConfigMessage.collagen_vascular_rf'),
                'type'=>8, 
                'input_type' => 'customcheckbox',
                'input_type_option'=>'posative_negative_option',
                'placeholder' =>'',
                'cssClasses' => $this->checkboxCss('col-md-12 rp', 'col-md-3 align-checkbox-list', 'col-md-9', 'form-group checkbox-listing checkbox-formgroup'),
                'isClearfix' => FALSE,
            ],
            [
                'id' => 26,
                'value' => trans('Setup::StaticDataConfigMessage.collagen_vascular_dsdna'),
                'type'=>8, 
                'input_type' => 'customcheckbox',
                'input_type_option'=>'posative_negative_option',
                'placeholder' =>'',
                'cssClasses' => $this->checkboxCss('col-md-12 rp', 'col-md-3 align-checkbox-list', 'col-md-9', 'form-group checkbox-listing checkbox-formgroup'),
                'isClearfix' => FALSE,
            ],
            [
                'id' => 27,
                'value' => trans('Setup::StaticDataConfigMessage.collagen_vascular_sm'),
                'type'=>8, 
                'input_type' => 'customcheckbox',
                'input_type_option'=>'posative_negative_option',
                'placeholder' =>'',
                'cssClasses' => $this->checkboxCss('col-md-12 rp', 'col-md-3 align-checkbox-list', 'col-md-9', 'form-group checkbox-listing checkbox-formgroup'),
                'isClearfix' => FALSE,
            ],
            [
                'id' => 28,
                'value' => trans('Setup::StaticDataConfigMessage.collagen_vascular_rnp'),
                'type'=>8, 
                'input_type' => 'customcheckbox',
                'input_type_option'=>'posative_negative_option',
                'placeholder' =>'',
                'cssClasses' => $this->checkboxCss('col-md-12 rp', 'col-md-3 align-checkbox-list', 'col-md-9', 'form-group checkbox-listing checkbox-formgroup'),
                'isClearfix' => FALSE,
            ],
            [
                'id' => 29,
                'value' => trans('Setup::StaticDataConfigMessage.collagen_vascular_scl_70'),
                'type'=>8, 
                'input_type' => 'customcheckbox',
                'input_type_option'=>'posative_negative_option',
                'placeholder' =>'',
                'cssClasses' => $this->checkboxCss('col-md-12 rp', 'col-md-3 align-checkbox-list', 'col-md-9', 'form-group checkbox-listing checkbox-formgroup'),
                'isClearfix' => FALSE,
            ],
            [
                'id' => 30,
                'value' => trans('Setup::StaticDataConfigMessage.collagen_vascular_ssa'),
                'type'=>8, 
                'input_type' => 'customcheckbox',
                'input_type_option'=>'posative_negative_option',
                'placeholder' =>'',
                'cssClasses' => $this->checkboxCss('col-md-12 rp', 'col-md-3 align-checkbox-list', 'col-md-9', 'form-group checkbox-listing checkbox-formgroup'),
                'isClearfix' => FALSE,
            ],
            [
                'id' => 31,
                'value' => trans('Setup::StaticDataConfigMessage.collagen_vascular_ssb'),
                'type'=>8, 
                'input_type' => 'customcheckbox',
                'input_type_option'=>'posative_negative_option',
                'placeholder' =>'',
                'cssClasses' => $this->checkboxCss('col-md-12 rp', 'col-md-3 align-checkbox-list', 'col-md-9', 'form-group checkbox-listing checkbox-formgroup'),
                'isClearfix' => FALSE,
            ],
            [
                'id' => 32,
                'value' => trans('Setup::StaticDataConfigMessage.collagen_vascular_cpk'),
                'type'=>8, 
                'input_type' => 'customcheckbox',
                'input_type_option'=>'posative_negative_option',
                'placeholder' =>'',
                'cssClasses' => $this->checkboxCss('col-md-12 rp', 'col-md-3 align-checkbox-list', 'col-md-9', 'form-group checkbox-listing checkbox-formgroup'),
                'isClearfix' => FALSE,
            ],
            [
                'id' => 33,
                'value' => trans('Setup::StaticDataConfigMessage.collagen_vascular_anti_jo_1'),
                'type'=>8, 
                'input_type' => 'customcheckbox',
                'input_type_option'=>'posative_negative_option',
                'placeholder' =>'',
                'cssClasses' => $this->checkboxCss('col-md-12 rp', 'col-md-3 align-checkbox-list', 'col-md-9', 'form-group checkbox-listing checkbox-formgroup'),
                'isClearfix' => FALSE,
            ],
            [
                'id' => 34,
                'value' => trans('Setup::StaticDataConfigMessage.collagen_vascular_anca'),
                'type'=>8, 
                'input_type' => 'customcheckbox',
                'input_type_option'=>'posative_negative_option',
                'placeholder' =>'',
                'cssClasses' => $this->checkboxCss('col-md-12 rp', 'col-md-3 align-checkbox-list', 'col-md-9', 'form-group checkbox-listing checkbox-formgroup'),
                'isClearfix' => FALSE,
            ],
            [
                'id' => 35,
                'value' => trans('Setup::StaticDataConfigMessage.collagen_vascular_ace'),
                'type'=>8, 
                'input_type' => 'customcheckbox',
                'input_type_option'=>'posative_negative_option',
                'placeholder' =>'',
                'cssClasses' => $this->checkboxCss('col-md-12 rp', 'col-md-3 align-checkbox-list', 'col-md-9', 'form-group checkbox-listing checkbox-formgroup'),
                'isClearfix' => FALSE,
            ],
            [
                'id' => 36,
                'value' => trans('Setup::StaticDataConfigMessage.collagen_vascular_ebv_titler'),
                'type'=>8, 
                'input_type' => 'customcheckbox',
                'input_type_option'=>'posative_negative_option',
                'placeholder' =>'',
                'cssClasses' => $this->checkboxCss('col-md-12 rp', 'col-md-3 align-checkbox-list', 'col-md-9', 'form-group checkbox-listing checkbox-formgroup'),
                'isClearfix' => FALSE,
            ],
            [
                'id' => 37,
                'value' => trans('Setup::StaticDataConfigMessage.collagen_vascular_hiv'),
                'type'=>8, 
                'input_type' => 'customcheckbox',
                'input_type_option'=>'posative_negative_option',
                'placeholder' =>'',
                'cssClasses' => $this->checkboxCss('col-md-12 rp', 'col-md-3 align-checkbox-list', 'col-md-9', 'form-group checkbox-listing checkbox-formgroup'),
                'isClearfix' => FALSE,
            ],
            [
                'id' => 38,
                'value' => trans('Setup::StaticDataConfigMessage.collagen_vascular_lymphocyte'),
                'type'=>8, 
                'input_type' => 'text' ,
                'input_type_option'=>'',
                'placeholder' =>'',
                'cssClasses' => $this->textBoxCss('col-md-6', 'form-group', '', ''),
                'isClearfix' => FALSE
            ],
            [
                'id' => 39,
                'value' => trans('Setup::StaticDataConfigMessage.collagen_vascular_mantoux'),
                'type'=>8, 
                'input_type' => 'text' ,
                'input_type_option'=>'',
                'placeholder' =>'',
                'cssClasses' => $this->textBoxCss('col-md-6', 'form-group', '', ''),
                'isClearfix' => TRUE,
            ],
            [
                'id' => 40,
                'value' => trans('Setup::StaticDataConfigMessage.echocardiogram_date'),
                'type'=>9, 
                'input_type' => 'date' ,
                'input_type_option'=>'',
                'placeholder' =>'',
                'format'=> Config::get('constants.REACT_WEB_DATE_FORMAT'),
                'cssClasses' => $this->textBoxCss('col-md-3', 'form-group', '', ''),
                'isClearfix' => FALSE,
            ],
            [
                'id' => 41,
                'value' => trans('Setup::StaticDataConfigMessage.echocardiogram_ef'),
                'type'=>9, 
                'input_type' => 'text' ,
                'input_type_option'=>'',
                'placeholder' =>'',
                'cssClasses' => $this->textBoxCss('col-md-3', 'form-group', '', ''),
                'isClearfix' => FALSE,
            ],
            [
                'id' => 42,
                'value' => trans('Setup::StaticDataConfigMessage.echocardiogram_wall_asymmetry'),
                'type'=>9, 
                'input_type' => 'text' ,
                'input_type_option'=>'',
                'placeholder' =>'',
                'cssClasses' => $this->textBoxCss('col-md-3', 'form-group', '', ''),
                'isClearfix' => FALSE,
            ],
            [
                'id' => 43,
                'value' => trans('Setup::StaticDataConfigMessage.echocardiogram_wall_estimated'),
                'type'=>9, 
                'input_type' => 'text' ,
                'input_type_option'=>'',
                'placeholder' =>'',
                'cssClasses' => $this->textBoxCss('col-md-3', 'form-group', '', ''),
                'isClearfix' => FALSE,
            ],
            [
                'id' => 44,
                'value' => trans('Setup::StaticDataConfigMessage.echocardiogram_wall_rv_dilation'),
                'type'=>9, 
                'input_type' => 'text' ,
                'input_type_option'=>'',
                'placeholder' =>'',
                'cssClasses' => $this->textBoxCss('col-md-3', 'form-group', '', ''),
                'isClearfix' => FALSE,
            ],
        ];
        return $staticValue; 
    }

    /**
     * @DateOfCreation        22 June 2018
     * @ShortDescription      This function is responsible to get the staticData for Blood Group list
     * @return                 Array of status and message
     */
    public function getLaboratoryTestTypeData(){
        $staticValue = [
            [
            'id' => 1, 
            'value' => trans('Setup::StaticDataConfigMessage.cbc')],
            ['id' => 2, 'value' => trans('Setup::StaticDataConfigMessage.dlc')],
            ['id' => 3, 'value' => trans('Setup::StaticDataConfigMessage.b_suga')],
            ['id' => 4, 'value' => trans('Setup::StaticDataConfigMessage.renal')],
            ['id' => 5, 'value' => trans('Setup::StaticDataConfigMessage.liver')],
            ['id' => 6, 'value' => trans('Setup::StaticDataConfigMessage.urinalysis')],
            ['id' => 7, 'value' => trans('Setup::StaticDataConfigMessage.sputum')],
            ['id' => 8, 'value' => trans('Setup::StaticDataConfigMessage.collagen')],
            ['id' => 9, 'value' => trans('Setup::StaticDataConfigMessage.echocardiogram')]
        ];
        return $staticValue; 
    }

    /**
     * @DateOfCreation        22 June 2018
     * @ShortDescription      This function is responsible to get the staticData for Blood Group list
     * @return                 Array of status and message
     */
    public function getlabortyHepatitisOptionData(){
        $staticValue = [
            ['id' => 1, 'value' => trans('Setup::StaticDataConfigMessage.hepatitis_a')],
            ['id' => 2, 'value' => trans('Setup::StaticDataConfigMessage.hepatitis_b')],
            ['id' => 3, 'value' => trans('Setup::StaticDataConfigMessage.hepatitis_c')]            
        ];
        return $staticValue; 
    }

    /**
     * @DateOfCreation        22 June 2018
     * @ShortDescription      This function is responsible to get the staticData for Domestic factor condtion  type list
     * @return                 Array of status and message
     */
    public function getlabortySputumAfbOptionData(){
        $staticValue = [
            ['id' => 1, 'value' => trans('Setup::StaticDataConfigMessage.sputum_afb_negative')],
            ['id' => 2, 'value' => trans('Setup::StaticDataConfigMessage.sputum_afb_1')],
            ['id' => 3, 'value' => trans('Setup::StaticDataConfigMessage.sputum_afb_2')],    
            ['id' => 4, 'value' => trans('Setup::StaticDataConfigMessage.sputum_afb_3')]    
        ];
        return $staticValue; 
    }

    /**
     * @DateOfCreation        22 June 2018
     * @ShortDescription      This function is responsible to get the staticData for yes no option value
     * @return                 Array of status and message
     */
    public function getPosativeNegativeData(){
        $staticValue = [
            ['id' => '1', 'value' => trans('Setup::StaticDataConfigMessage.posative')],
            ['id' => '2', 'value' => trans('Setup::StaticDataConfigMessage.negative')],
        ];
        return $staticValue; 
    }

    /**
     * @DateOfCreation        22 June 2018
     * @ShortDescription      This function is responsible to get the staticData for Consultant Impression form
     * @return                 Array of status and message
     */
    public function getConsultantImpressionData(){
        $staticValue = [
            [
                'id' => 1, 
                'value' => trans('Setup::StaticDataConfigMessage.consultant_suspected_ild_diagnosis'),
                'input_type' => 'customcheckbox' ,
                'input_type_option'=>'yes_no_option',
                'placeholder' =>'',
                'cssClasses' => $this->checkboxCss('', 'col-md-4 align-checkbox-list', 'col-md-7 ml-19', 'form-group checkbox-listing checkbox-formgroup row'),
                'isClearfix' => FALSE,
            ],
            [
                'id' => 2, 
                'value' => trans('Setup::StaticDataConfigMessage.consultant_idiopathic_interstitial'),
                'type'=>1, 
                'input_type' => 'select' ,
                'input_type_option'=>'consultant_idiopathic_interstitial_iip',
                'placeholder' =>'',
                'cssClasses' => $this->textBoxCss('col-md-6', 'form-group', '', ''),
                'isClearfix' => TRUE,
            ],
            [
                'id' => 3, 
                'value' => trans('Setup::StaticDataConfigMessage.consultant_ipf'),
                'input_type' => 'customcheckbox' ,
                'input_type_option'=>'yes_no_option',
                'placeholder' =>'',
                'cssClasses' => $this->checkboxCss('col-md-12', 'col-md-4 align-checkbox-list', 'col-md-6', 'form-group checkbox-listing checkbox-formgroup'),
                'isClearfix' => FALSE,
            ],
            [
                'id' => 4, 
                'value' => trans('Setup::StaticDataConfigMessage.consultant_unclassnameifide_ild'),
                'input_type' => 'customcheckbox' ,
                'input_type_option'=>'yes_no_option',
                'placeholder' =>'',
                'cssClasses' => $this->checkboxCss('col-md-12', 'col-md-4 align-checkbox-list', 'col-md-6', 'form-group checkbox-listing checkbox-formgroup'),
                'isClearfix' => FALSE,
            ],
            [
                'id' => 5, 
                'value' => trans('Setup::StaticDataConfigMessage.consultant_occupational_ild'),
                'input_type' => 'customcheckbox' ,
                'input_type_option'=>'yes_no_option',
                'placeholder' =>'',
                'cssClasses' => $this->checkboxCss('col-md-12', 'col-md-4 align-checkbox-list', 'col-md-6', 'form-group checkbox-listing checkbox-formgroup'),
                'isClearfix' => FALSE,
            ],
            [
                'id' => 6, 
                'value' => trans('Setup::StaticDataConfigMessage.consultant_granulomatous'),
                'input_type' => 'customcheckbox' ,
                'input_type_option'=>'yes_no_option',
                'placeholder' =>'',
                'cssClasses' => $this->checkboxCss('col-md-12', 'col-md-4 align-checkbox-list', 'col-md-6', 'form-group checkbox-listing checkbox-formgroup'),
                'isClearfix' => FALSE,
            ],
            [
                'id' => 7, 
                'value' => trans('Setup::StaticDataConfigMessage.consultant_hypersensitivity'),
                'input_type' => 'customcheckbox' ,
                'input_type_option'=>'yes_no_option',
                'placeholder' =>'',
                'cssClasses' => $this->checkboxCss('col-md-12', 'col-md-4 align-checkbox-list', 'col-md-6', 'form-group checkbox-listing checkbox-formgroup'),
                'isClearfix' => FALSE,
            ],
            [
                'id' => 8, 
                'value' => trans('Setup::StaticDataConfigMessage.consultant_familial_ipf'),
                'input_type' => 'customcheckbox' ,
                'input_type_option'=>'yes_no_option',
                'placeholder' =>'',
                'cssClasses' => $this->checkboxCss('col-md-12', 'col-md-4 align-checkbox-list', 'col-md-6', 'form-group checkbox-listing checkbox-formgroup'),
                'isClearfix' => FALSE,
            ],
            [
                'id' => 9, 
                'value' => trans('Setup::StaticDataConfigMessage.consultant_connective_tissue'),
                'input_type' => 'customcheckbox' ,
                'input_type_option'=>'yes_no_option',
                'placeholder' =>'',
                'cssClasses' => $this->checkboxCss('col-md-12', 'col-md-4 align-checkbox-list', 'col-md-6', 'form-group checkbox-listing checkbox-formgroup'),
                'isClearfix' => FALSE,
            ],
            [
                'id' => 10,
                'value' => trans('Setup::StaticDataConfigMessage.consultant_other_rare_ild'),
                'input_type' => 'customcheckbox' ,
                'input_type_option'=>'yes_no_option',
                'placeholder' =>'',
                'cssClasses' => $this->checkboxCss('col-md-12', 'col-md-4 align-checkbox-list', 'col-md-6', 'form-group checkbox-listing checkbox-formgroup'),
                'isClearfix' => FALSE,
            ],
            [
                'id' => 11,
                'value' => trans('Setup::StaticDataConfigMessage.other_district'),
                'input_type' => 'text' ,
                'input_type_option'=>'',
                'placeholder' =>'',
                'cssClasses' => $this->textBoxCss('col-md-4', 'form-group', '', ''),
                'isClearfix' => TRUE,
            ],
            [
                'id' => 12,
                'value' => trans('Setup::StaticDataConfigMessage.consultant_other_coexisting_lung_disease'),
                'input_type' => 'text' ,
                'input_type_option'=>'',
                'placeholder' =>'',
                'cssClasses' => $this->textBoxCss('col-md-4', 'form-group', '', ''),
                'isClearfix' => FALSE,
            ],
            [
                'id' => 13,
                'value' => trans('Setup::StaticDataConfigMessage.consultant_comorbidities'),
                'type'=>4, 
                'input_type' => 'text' ,
                'input_type_option'=>'',
                'placeholder' =>'',
                'cssClasses' => $this->textBoxCss('col-md-4', 'form-group', '', ''),
                'isClearfix' => FALSE,
            ]
        ];
        return $staticValue; 
    }

    /**
     * @DateOfCreation        22 June 2018
     * @ShortDescription      This function is responsible to get the staticData for Domestic factor condtion  type list
     * @return                 Array of status and message
     */
    public function getConsultantIdiopathicInterstitialIIPOptionData(){
        $staticValue = [
            ['id' => 1, 'value' => trans('Setup::StaticDataConfigMessage.consultant_option_uip')],
            ['id' => 2, 'value' => trans('Setup::StaticDataConfigMessage.consultant_option_nsip')],
            ['id' => 3, 'value' => trans('Setup::StaticDataConfigMessage.consultant_option_cop')],    
            ['id' => 4, 'value' => trans('Setup::StaticDataConfigMessage.consultant_option_lip')],    
            ['id' => 5, 'value' => trans('Setup::StaticDataConfigMessage.consultant_option_rb_ild')]    
        ];
        return $staticValue; 
    }

    /**
     * @DateOfCreation        22 June 2018
     * @ShortDescription      This function is responsible to get the staticData for yes no option value
     * @return                 Array of status and message
     */
    public function getConsultantSuspectActiveInfectionOptionData(){
        $staticValue = [
            ['id' => '1', 'value' => trans('Setup::StaticDataConfigMessage.consultant_option_active_tbs')],
            ['id' => '2', 'value' => trans('Setup::StaticDataConfigMessage.consultant_option_pneumonia')],
        ];
        return $staticValue; 
    }


    /**
     * @DateOfCreation        22 June 2018
     * @ShortDescription      This function is responsible to get the staticData for yes no option value
     * @return                 Array of status and message
     */
    public function getWorkEnvironmentYearOptionData(){
        $currentYear = date('Y');
        $range = range(1970, $currentYear);
        $staticValue = array_map(function($row){
            $newrow = [];
            $newrow['id'] = $row;
            $newrow['value'] = $row;
            return $newrow;
        },$range);
    
        return $staticValue; 
    }

    public function abgInvestigationFector(){

        $staticValue = [
                [
                    'id'            => 1, 
                    'value'         => trans('Setup::StaticDataConfigMessage.abg_ph'),
                    'name'          => 'abg_ph', 
                    'field_type'    => 'text', 
                    'placeholder'   => trans('Setup::StaticDataConfigMessage.abg_ph'), 
                    'options'       => [],
                    'cssClasses'    => $this->textBoxCss('col-md-3', 'form-group', 'control-label', '')
                ],
                [
                    'id'            => 2, 
                    'value'         => trans('Setup::StaticDataConfigMessage.abg_po2').trans('Setup::StaticDataConfigMessage.abg_mm_title'),
                    'name'          => 'abg_po2', 
                    'field_type'    => 'text', 
                    'options'       => [],
                    'placeholder'   => trans('Setup::StaticDataConfigMessage.abg_po2'), 
                    'cssClasses'    => $this->textBoxCss('col-md-3', 'form-group', 'control-label', '')
                ],
                [
                    'id'            => 3, 
                    'value'         => trans('Setup::StaticDataConfigMessage.abg_pco2').trans('Setup::StaticDataConfigMessage.abg_mm_title'),
                    'name'          => 'abg_pco2', 
                    'field_type'    => 'text', 
                    'options'       => [],
                    'placeholder'   => trans('Setup::StaticDataConfigMessage.abg_pco2'), 
                    'cssClasses'    => $this->textBoxCss('col-md-3', 'form-group', 'control-label', '')
                ],
                [
                    'id'            => 4, 
                    'value'         => trans('Setup::StaticDataConfigMessage.abg_hco3'),
                    'name'          => 'abg_hco3', 
                    'field_type'    => 'text', 
                    'options'       => [],
                    'placeholder'   => trans('Setup::StaticDataConfigMessage.abg_hco3'), 
                    'cssClasses'    => $this->textBoxCss('col-md-3', 'form-group', 'control-label', '')
                ]

            ];
        return $staticValue;
    }

    public function abgInvestigationFectorDate(){
        $staticValue =[
            [
                'id'            => 1, 
                'value'         => trans('Setup::StaticDataConfigMessage.abg_date'),
                'name'          => 'abg_date', 
                'field_type'    => 'date', 
                'options'       => [],
                'cssClasses'    => $this->textBoxCss('', 'col-md-2', '', ''),
                'isClearfix'    => TRUE,
            ]
        ];
        return $staticValue;
    }

    public function getThoracoscopicLungBiopsy(){

        $staticValue = [
                [
                    'id'            => 1, 
                    'value'         => '',
                    'name'          => 'ptlb_is_happen', 
                    'field_type'    => 'customcheckbox', 
                    'placeholder'   => '', 
                    'options'       => $this->covertToOption($this->getYesNoData()),
                    'cssClasses'    => $this->checkboxCss('col-md-2', 'check-list-text', 'check-list-radio', 'form-group')
                ],
                [
                    'id'            => 2, 
                    'value'         => '',
                    'name'          => 'ptlb_date', 
                    'field_type'    => 'date', 
                    'options'       => [],
                    'placeholder'   => 'Date', 
                    'cssClasses'    => $this->textBoxCss('', 'col-md-2', '', ''),
                    'isClearfix'    => TRUE,
                ],
                [
                    'id'            => 3, 
                    'value'         => '',
                    'name'          => 'ptlb_is_left_lung', 
                    'field_type'    => 'checkbox', 
                    'placeholder'   => '', 
                    'options'       => $this->covertToOption($this->getLeftPostionOfLungData()),
                    'cssClasses'    => $this->checkboxCss('left-lung ml-15', 'check-list-text', 'check-list-radio', 'form-group'),
                ],
                [
                    'id'            => 4, 
                    'value'         => '',
                    'name'          => 'ptlb_is_right_lung', 
                    'field_type'    => 'checkbox', 
                    'placeholder'   => '', 
                    'options'       => $this->covertToOption($this->getRightPostionOfLungData()),
                    'cssClasses'    => $this->checkboxCss('right-lung', 'check-list-text', 'check-list-radio', 'form-group'),
                    'isClearfix'    => TRUE,
                ],
                [
                    'id'            => 5, 
                    'value'         => trans('Setup::StaticDataConfigMessage.ptlb_left_lung_lobe'),
                    'name'          => 'ptlb_left_lung_lobe', 
                    'field_type'    => 'select', 
                    'options'       => $this->covertToOption($this->getTypeOfLungData()),
                    'placeholder'   => trans('Setup::StaticDataConfigMessage.ptlb_right_lung_lobe'), 
                    'cssClasses'    => $this->textBoxCss('col-md-2', 'form-group', 'control-label', '')
                ],
                [
                    'id'            => 6, 
                    'value'         => trans('Setup::StaticDataConfigMessage.ptlb_right_lung_lobe'),
                    'name'          => 'ptlb_right_lung_lobe', 
                    'field_type'    => 'select', 
                    'options'       => $this->covertToOption($this->getTypeOfLungData()),
                    'placeholder'   => trans('Setup::StaticDataConfigMessage.ptlb_right_lung_lobe'), 
                    'cssClasses'    => $this->textBoxCss('col-md-2', 'form-group', 'control-label', '')
                ]

            ];
        return $staticValue;
    }

    /**
     * @DateOfCreation        22 June 2018
     * @ShortDescription      This function is responsible to get the staticData for yes no option value
     * @return                 Array of status and message
     */
    public function getLeftPostionOfLungData(){
        $staticValue = [
            ['id' => '1', 'value' => trans('Setup::StaticDataConfigMessage.left_lung')],
            
        ];
        return $staticValue; 
    }

     /**
     * @DateOfCreation        22 June 2018
     * @ShortDescription      This function is responsible to get the staticData for yes no option value
     * @return                 Array of status and message
     */
    public function getRightPostionOfLungData(){
        $staticValue = [
            ['id' => '1', 'value' => trans('Setup::StaticDataConfigMessage.right_lung')],
        ];
        return $staticValue; 
    }

    /**
     * @DateOfCreation        22 June 2018
     * @ShortDescription      This function is responsible to get the staticData for yes no option value
     * @return                 Array of status and message
     */
    public function getTypeOfLungData(){
        $staticValue = [
            ['id' => '1', 'value' => trans('Setup::StaticDataConfigMessage.upper_lobe')],
            ['id' => '2', 'value' => trans('Setup::StaticDataConfigMessage.middle_lobe')],
            ['id' => '3', 'value' => trans('Setup::StaticDataConfigMessage.lower_lobe')],
        ];
        return $staticValue; 
    }

    /**
     * @DateOfCreation        22 June 2018
     * @ShortDescription      This function is responsible to get the staticData for yes no option value
     * @return                 Array of status and message
     */
    public function covertToOption($arrayData){
        $data=[];
        foreach ($arrayData as $key => $value) {
            $data[$value['id']] = $value['value'];
        }
        return $data;
    }


    /**
     * @DateOfCreation        22 June 2018
     * @ShortDescription      This function is responsible to get the staticData for yes no option value
     * @return                 Array of status and message
     */
    public function covertToOptionlabel($arrayData){
        $data = [];
        if(!empty($arrayData) && is_array($arrayData)){
            $data = array_map(function($tag) {
            return array(
                'value' => $tag['id'],
                'label' => $tag['value']
            );
            }, $arrayData);
        } 
        return $data;       
    }

    /**
     * @DateOfCreation        16 July 2018
     * @ShortDescription      This function is responsible to get the staticData for medication form
     * @return                 Array of status and message
     */
    public function getMedicationsFector($fectorType, $fectorKey){
        $dataArray = [
            'medicine_duration_unit' => [
                Config::get('dataconstants.MEDICINE_DURATION_UNIT_DAYS')    => trans('Setup::StaticDataConfigMessage.days'),
                Config::get('dataconstants.MEDICINE_DURATION_UNIT_WEEKS')   => trans('Setup::StaticDataConfigMessage.weeks'),
                Config::get('dataconstants.MEDICINE_DURATION_UNIT_MONTHS')  => trans('Setup::StaticDataConfigMessage.months'),
            ],
            'medicine_frequency' => [
                1 => trans('Setup::StaticDataConfigMessage.ones_in_a_day'),
                2 => trans('Setup::StaticDataConfigMessage.twice_in_a_day'),
                3 => trans('Setup::StaticDataConfigMessage.thrice_in_a_day'),
                4 => trans('Setup::StaticDataConfigMessage.feerly_as_needed'),
            ],
            'medicine_dose_unit' => [
                1 => trans('Setup::StaticDataConfigMessage.mg'),
                2 => trans('Setup::StaticDataConfigMessage.ml'),
                3 => trans('Setup::StaticDataConfigMessage.gm'),
            ],
            'medicine_meal_opt' => [
                1 => trans('Setup::StaticDataConfigMessage.before_meal'),
                2 => trans('Setup::StaticDataConfigMessage.after_meal'),
            ]
        ];

        if(!empty($fectorKey)){
            return $dataArray[$fectorType][$fectorKey];
        }
        return false;
    }

    /**
     * @DateOfCreation        16 July 2018
     * @ShortDescription      This function is responsible to get the staticData for Surgical Lung Biopsy form
     * @return                 Array of status and message
     */
    public function getSurgicalLungBiopsy(){

        $staticValue = [
                [
                    'id'            => 1, 
                    'value'         => trans('Setup::StaticDataConfigMessage.uip_consistent'),
                    'name'          => 'pslbf_factor_', 
                    'field_type'    => 'customcheckbox', 
                    'placeholder'   => '', 
                    'options'       => $this->covertToOption($this->getUipConsistentOptionData()),
                    'cssClasses'    =>  $this->checkboxCss('col-md-12', 'col-md-4 align-checkbox-list', 'col-md-8 pl-5', 'form-group checkbox-listing checkbox-formgroup')
                ],
                [
                    'id'            => 2, 
                    'value'         => trans('Setup::StaticDataConfigMessage.hypersensitivity_pneumonitis'),
                    'name'          => 'pslbf_factor_', 
                    'field_type'    => 'customcheckbox', 
                    'placeholder'   => '', 
                    'options'       => $this->covertToOption($this->getYesNoData()),
                    'cssClasses'    => $this->checkboxCss('col-md-12', 'col-md-4 align-checkbox-list', 'col-md-8 pl-5', 'form-group checkbox-listing checkbox-formgroup')
                ],
                [
                    'id'            => 3, 
                    'value'         => trans('Setup::StaticDataConfigMessage.sarcoidosis'),
                    'name'          => 'pslbf_factor_', 
                    'field_type'    => 'customcheckbox', 
                    'placeholder'   => '', 
                    'options'       => $this->covertToOption($this->getYesNoData()),
                    'cssClasses'    => $this->checkboxCss('col-md-12', 'col-md-4 align-checkbox-list', 'col-md-8 pl-5', 'form-group checkbox-listing checkbox-formgroup'),
                    'isClearfix'    => TRUE,
                ],
                [
                    'id'            => 4, 
                    'value'         => trans('Setup::StaticDataConfigMessage.nsip'),
                    'name'          => 'pslbf_factor_', 
                    'field_type'    => 'customcheckbox', 
                    'placeholder'   => '', 
                    'options'       => $this->covertToOption($this->getYesNoData()),
                    'cssClasses'    => $this->checkboxCss('col-md-12', 'col-md-4 align-checkbox-list', 'col-md-8 pl-5', 'form-group checkbox-listing checkbox-formgroup'),
                    'isClearfix'    => TRUE,
                ],
                [
                    'id'            => 5, 
                    'value'         => trans('Setup::StaticDataConfigMessage.others'),
                    'name'          => 'pslbf_factor_', 
                    'field_type'    => 'customcheckbox', 
                    'placeholder'   => '', 
                    'options'       => $this->covertToOption($this->getYesNoData()),
                    'cssClasses'    => $this->checkboxCss('col-md-12', 'col-md-4 align-checkbox-list', 'col-md-8 pl-5', 'form-group checkbox-listing checkbox-formgroup'),
                    'isClearfix'    => TRUE,
                ],
                [
                    'id'            => 6, 
                    'value'         => trans('Setup::StaticDataConfigMessage.lung_biopsy_report'),
                    'name'          => 'pslbf_factor_', 
                    'field_type'    => 'text', 
                    'options'       => [],
                    'placeholder'   => trans('Setup::StaticDataConfigMessage.lung_biopsy_report'), 
                    'cssClasses'    => $this->textBoxCss('col-md-12 lung-biopsy-report', 'form-group col-md-12', 'control-label', '')
                ]

            ];
        return $staticValue;
    }

    /**
     * @DateOfCreation        22 June 2018
     * @ShortDescription      This function is responsible to get the staticData for yes no option value
     * @return                 Array of status and message
     */
    public function getSurgicalLungBiopsyDate(){
        $staticValue =[
            [
                'id'            => 1, 
                'value'         => trans('Setup::StaticDataConfigMessage.surgical_pathology'),
                'name'          => 'pslb_is_happen', 
                'field_type'    => 'customcheckbox', 
                'placeholder'   => '', 
                'options'       => $this->covertToOption($this->getYesNoData()),
                'cssClasses'    => $this->checkboxCss('col-md-6', 'col-md-8 align-checkbox-list', 'col-md-4', 'form-group checkbox-listing checkbox-formgroup'),
                //'isClearfix'    => TRUE,
            ],
            [
                'id'            => 2, 
                'value'         => '',
                'name'          => 'pslb_date', 
                'field_type'    => 'date', 
                'options'       => [],
                'placeholder'   => trans('Setup::StaticDataConfigMessage.date'),
                'cssClasses'    => $this->textBoxCss('col-md-2', 'input-rm', 'col-md-4', ''),
                'isClearfix'    => TRUE,
            ]
        ];
        return $staticValue;
    }

    /**
     * @DateOfCreation        22 June 2018
     * @ShortDescription      This function is responsible to get the staticData for yes no option value
     * @return                 Array of status and message
     */
    public function getUipConsistentOptionData(){
        $staticValue = [
            ['id' => '1', 'value' => trans('Setup::StaticDataConfigMessage.def')],
            ['id' => '2', 'value' => trans('Setup::StaticDataConfigMessage.prob')],
            ['id' => '3', 'value' => trans('Setup::StaticDataConfigMessage.poss')],
            ['id' => '4', 'value' => trans('Setup::StaticDataConfigMessage.definitely_not')],
        ];
        return $staticValue; 
    }

    /**
     * @DateOfCreation        22 June 2018
     * @ShortDescription      This function is responsible to get the staticData for yes no option value
     * @return                 Array of status and message
     */
    public function getChestXray(){
        $staticValue =[
            [
                'id'            => 1, 
                'value'         => trans('Setup::StaticDataConfigMessage.chest_xray_recent'),
                'name'          => 'pcx_bilateral_shadows_present', 
                'field_type'    => 'customcheckbox', 
                'placeholder'   => '', 
                'options'       => $this->covertToOption($this->getYesNoData()),
                'cssClasses'    => $this->checkboxCss('col-md-6', 'col-md-8 align-checkbox-list', 'col-md-4', 'form-group checkbox-listing checkbox-formgroup'),
                'type'          => Config::get('dataconstants.CHEST_XRAY_RECENT_TYPE')
            ],
            [
                'id'            => 2, 
                'value'         => '',
                'name'          => 'pcx_date', 
                'field_type'    => 'date', 
                'placeholder'    => trans('Setup::StaticDataConfigMessage.date'), 
                'options'       => [],
                'cssClasses'    => $this->textBoxCss('col-md-2', 'input-rm', '', ''),
                'isClearfix'    => TRUE,
                'type'          => Config::get('dataconstants.CHEST_XRAY_RECENT_TYPE')
            ],
            [
                'id'            => 3, 
                'value'         => trans('Setup::StaticDataConfigMessage.chest_xray_old'),
                'name'          => 'pcx_bilateral_shadows_present', 
                'field_type'    => 'customcheckbox', 
                'placeholder'   => '', 
                'options'       => $this->covertToOption($this->getYesNoData()),
                'cssClasses'    => $this->checkboxCss('col-md-6 ', 'col-md-8 align-checkbox-list', 'col-md-4', 'form-group checkbox-listing checkbox-formgroup'),
                'type'          => Config::get('dataconstants.CHEST_XRAY_OLD_TYPE')
            ],
            [
                'id'            => 4, 
                'value'         => '',
                'name'          => 'pcx_date', 
                'field_type'    => 'date', 
                'placeholder'    => trans('Setup::StaticDataConfigMessage.date'),
                'options'       => [],
                'cssClasses'    => $this->textBoxCss('col-md-2', 'input-rm', '', ''),
                'isClearfix'    => TRUE,
                'type'          => Config::get('dataconstants.CHEST_XRAY_OLD_TYPE')
            ]
        ];
        return $staticValue;
    }

    /**
     * @DateOfCreation        22 June 2018
     * @ShortDescription      This function is responsible to get the staticData for HRCT Factor
     * @return                 Array of status and message
     */
    public function getHRCT(){

        $staticValue = [
                [
                    'id'            => 1, 
                    'value'         => trans('Setup::StaticDataConfigMessage.1mm_cuts'),
                    'name'          => 'phrct_factor_', 
                    'field_type'    => 'customcheckbox', 
                    'placeholder'   => '', 
                    'options'       => $this->covertToOption($this->getYesNoData()),
                    'cssClasses'    =>  $this->checkboxCss('col-md-6 rpl', 'col-md-8 align-checkbox-list', 'col-md-4', 'form-group checkbox-listing checkbox-formgroup'),
                    'isClearfix'    => TRUE,
                ],
                [
                    'id'            => 2, 
                    'value'         => trans('Setup::StaticDataConfigMessage.prone_supine_views'),
                    'name'          => 'phrct_factor_', 
                    'field_type'    => 'customcheckbox', 
                    'placeholder'   => '', 
                    'options'       => $this->covertToOption($this->getYesNoData()),
                    'isClearfix'    => TRUE,
                    'cssClasses'    => $this->checkboxCss('col-md-6 rpl', 'col-md-8 align-checkbox-list', 'col-md-4', 'form-group checkbox-listing checkbox-formgroup')
                ],
                [
                    'id'            => 3, 
                    'value'         => trans('Setup::StaticDataConfigMessage.expiratory_view'),
                    'name'          => 'phrct_factor_', 
                    'field_type'    => 'customcheckbox', 
                    'placeholder'   => '', 
                    'options'       => $this->covertToOption($this->getYesNoData()),
                    'cssClasses'    => $this->checkboxCss('col-md-6 rpl', 'col-md-8 align-checkbox-list', 'col-md-4', 'form-group checkbox-listing checkbox-formgroup'),
                    'isClearfix'    => TRUE,
                ]

            ];
        return $staticValue;
    }

    /**
     * @DateOfCreation        22 June 2018
     * @ShortDescription      This function is responsible to get the staticData for HRCT main
     * @return                 Array of status and message
     */
    public function getHRCTDate(){
        $staticValue =[
            [
                'id'            => 1, 
                'value'         => trans('Setup::StaticDataConfigMessage.date_of_hrct'),
                'name'          => 'phrct_date', 
                'field_type'    => 'date', 
                'options'       => [],
                'cssClasses'    => $this->textBoxCss('col-md-3', 'form-group', '', ''),
            ],
            [
                'id'            => 2, 
                'value'         => trans('Setup::StaticDataConfigMessage.report'),
                'name'          => 'phrct_report', 
                'field_type'    => 'text', 
                'placeholder'   => '', 
                'options'       => [],
                'cssClasses'    => $this->checkboxCss('col-md-9', 'control-label', '', ''),
                'isClearfix'    => TRUE,
            ]
            
        ];
        return $staticValue;
    }

    /**
     * @DateOfCreation        22 June 2018
     * @ShortDescription      This function is responsible to get the staticData for UIP
     * @return                 Array of status and message
     */
    public function getUIP(){

        $staticValue = [
                [
                    'id'            => 1, 
                    'value'         => trans('Setup::StaticDataConfigMessage.uip'),
                    'name'          => 'puip_is_happen', 
                    'field_type'    => 'customcheckbox', 
                    'placeholder'   => '', 
                    'options'       => $this->covertToOption($this->getYesNoData()),
                    'cssClasses'    =>  $this->checkboxCss('', 'col-md-1 bold-text align-checkbox-list', 'col-md-8', 'form-group checkbox-listing checkbox-formgroup')
                ]
            ];
        return $staticValue;
    }

    /**
     * @DateOfCreation        22 June 2018
     * @ShortDescription      This function is responsible to get the staticData for HRCT main
     * @return                 Array of status and message
     */
    public function getFiberopticBronchoscopyDate(){
        $staticValue =[
            [
                'id'            => 1, 
                'value'         => trans('Setup::StaticDataConfigMessage.fiberoptic_bronchoscopy_when_relevant'),
                'name'          => 'pfb_is_happen', 
                'field_type'    => 'customcheckbox', 
                'placeholder'   => '', 
                'options'       => $this->covertToOption($this->getYesNoData()),
                'cssClasses'    => $this->checkboxCss('col-md-6 add-more-spaces-tm', 'col-md-8 bold-text sub-heading align-checkbox-list', 'col-md-4 text-right', 'form-group checkbox-formgroup mt-5'),
            ],
            [
                'id'            => 2, 
                'value'         => '',
                'name'          => 'pfb_date', 
                'placeholder'   => trans('Setup::StaticDataConfigMessage.date'), 
                'field_type'    => 'date', 
                'options'       => [],
                'cssClasses'    => $this->textBoxCss('col-md-2', '', '', ''),
                'isClearfix'    => TRUE,
            ]
        ];
        return $staticValue;
    }

    /**
     * @DateOfCreation        22 June 2018
     * @ShortDescription      This function is responsible to get the staticData for HRCT main
     * @return                 Array of status and message
     */
    public function getFiberopticBronchoscopyType(){
        $staticValue =[
                        [
                            'id'            => 1, 
                            'value'         => trans('Setup::StaticDataConfigMessage.broncho_alveolar_lavage'),
                            'type'          => 1,
                            'option'        => $this->getFiberopticBronchoscopyTypeOption(1)
                        ],
                        [
                            'id'            => 2, 
                            'value'         => trans('Setup::StaticDataConfigMessage.transbronchial_lung_biopsy'),
                            'type'          => 2,
                            'option'        => $this->getFiberopticBronchoscopyTypeOption(2)
                        ],
                        [
                            'id'            => 3, 
                            'value'         => trans('Setup::StaticDataConfigMessage.endo_bronchial_biopsy'),
                            'type'          => 2,
                            'option'        => $this->getFiberopticBronchoscopyTypeOption(3)
                        ]
                    ];
        return $staticValue;
    }

    /**
     * @DateOfCreation        22 June 2018
     * @ShortDescription      This function is responsible to get the staticData for HRCT main
     * @return                 Array of status and message
     */
    public function getFiberopticBronchoscopyTypeOption($index = ''){
        $staticValue =[
                        '1' =>  [
                                    [
                                        'id'            => 1, 
                                        'value'         => trans('Setup::StaticDataConfigMessage.neutrophils'),
                                        'field_type'    => 'text',
                                        'cssClasses'    => $this->textBoxCss('col-md-3', '', '', ''),
                                        'placeholder'   => '',

                                    ],
                                    [
                                        'id'            => 2, 
                                        'value'         => trans('Setup::StaticDataConfigMessage.lymphocytes'),
                                        'field_type'    => 'text',
                                        'cssClasses'    => $this->textBoxCss('col-md-3', '', '', ''),
                                        'placeholder'   => '',

                                    ],
                                    [
                                        'id'            => 3, 
                                        'value'         => trans('Setup::StaticDataConfigMessage.eosinophils'),
                                        'field_type'    => 'text',
                                        'cssClasses'    => $this->textBoxCss('col-md-3', '', '', ''),
                                        'placeholder'   => '',

                                    ],
                                    [
                                        'id'            => 4, 
                                        'value'         => trans('Setup::StaticDataConfigMessage.pas_positive_material'),
                                        'field_type'    => 'text',
                                        'cssClasses'    => $this->textBoxCss('col-md-3', '', '', ''),
                                        'placeholder'   => '',

                                    ]
                                ],
                        '2' =>  [
                                    [
                                        'id'            => 1, 
                                        'value'         => '',
                                        'field_type'    => 'text',
                                        'cssClasses'    => $this->textBoxCss('col-md-3', '', '', ''),
                                        'placeholder'   => '',

                                    ]
                                ],
                        '3' =>  [
                                    [
                                        'id'            => 1, 
                                        'value'         => '',
                                        'field_type'    => 'text',
                                        'cssClasses'    => $this->textBoxCss('col-md-3', '', '', ''),
                                        'placeholder'   => '',

                                    ]
                                ],
        ];

        if(!empty($index)){
            $staticValue = isset($staticValue[$index]) ? $staticValue[$index] : [];
        }
        return $staticValue;
    }

    /**
     * @DateOfCreation        22 June 2018
     * @ShortDescription      This function is responsible to get the staticData for vitalsFactor main
     * @return                 Array of status and message
     */
    public function vitalsFectorData(){
        $staticValue = [
                [
                    'id'         => 7, 
                    'value'      => trans('Setup::StaticDataConfigMessage.visit_vitals_label_temperature'),
                    'type'       => 7, 
                    'name'       => 'vitals_fector', 
                    'field_type' => 'text', 
                    'options'    => [],
                    'cssClasses' => $this->textBoxCss('col-md-2','form-group','',''),
                    'lable'      => trans('Setup::StaticDataConfigMessage.visit_vitals_label_temperature_lable'),
                    'unit'       => trans('Setup::StaticDataConfigMessage.visit_vitals_label_temperature_unit')
                ],
                [
                    'id'        => 2, 
                    'value'     => trans('Setup::StaticDataConfigMessage.visit_vitals_label_pulse'),
                    'type'      => 7, 
                    'name'      => 'vitals_fector', 
                    'field_type' => 'text', 
                    'options'   => [],
                    'cssClasses' => $this->textBoxCss('col-md-2','form-group','',''),
                    'lable'      => trans('Setup::StaticDataConfigMessage.visit_vitals_label_pulse_lable'),
                    'unit'      => trans('Setup::StaticDataConfigMessage.visit_vitals_label_pulse_unit')
                ],[
                    'id'        => 3, 
                    'value'     => trans('Setup::StaticDataConfigMessage.visit_vitals_label_bp_sys'),
                    'type'      => 7, 
                    'name'      => 'vitals_fector', 
                    'field_type' => 'text', 
                    'options'   => [],
                    'cssClasses' => $this->textBoxCss('col-md-2','form-group','',''),
                    'lable'      => trans('Setup::StaticDataConfigMessage.visit_vitals_label_bp_sys_lable'),
                    'unit'      => trans('Setup::StaticDataConfigMessage.visit_vitals_label_bp_sys_unit')
                ],[
                    'id'        => 4, 
                    'value'     => trans('Setup::StaticDataConfigMessage.visit_vitals_label_bp_dia'),
                    'type'      => 7, 
                    'name'      => 'vitals_fector', 
                    'field_type' => 'text', 
                    'options'   => [],
                    'cssClasses' => $this->textBoxCss('col-md-2','form-group','',''),
                    'lable'      => trans('Setup::StaticDataConfigMessage.visit_vitals_label_bp_dia_lable'),
                    'unit'      => trans('Setup::StaticDataConfigMessage.visit_vitals_label_bp_dia_unit')
                ],[
                    'id'        => 5, 
                    'value'     => trans('Setup::StaticDataConfigMessage.visit_vitals_label_spo2'),
                    'type'      => 7, 
                    'name'      => 'vitals_fector', 
                    'field_type' => 'text', 
                    'options'   => [],
                    'cssClasses' => $this->textBoxCss('col-md-2','form-group','',''),
                    'lable'      => trans('Setup::StaticDataConfigMessage.visit_vitals_label_spo2_lable'),
                    'unit'      => trans('Setup::StaticDataConfigMessage.visit_vitals_label_spo2_unit')
                ],[
                    'id'        => 6, 
                    'value'     => trans('Setup::StaticDataConfigMessage.visit_vitals_label_respiratory_rate'),
                    'type'      => 7, 
                    'name'      => 'vitals_fector', 
                    'field_type' => 'text', 
                    'options'   => [],
                    'cssClasses' => $this->textBoxCss('col-md-2','form-group','',''),
                    'lable'      => trans('Setup::StaticDataConfigMessage.visit_vitals_label_respiratory_rate_lable'),
                    'unit'      => trans('Setup::StaticDataConfigMessage.visit_vitals_label_respiratory_rate_unit')
                ],
            ];
            return $staticValue;
    }

    /**
     * @DateOfCreation        23 July 2018
     * @ShortDescription      This function is responsible to get the staticData for medicine Route
     * @return                 Array of status and message
     */
    public function getMedicineRoute($routeID = null){
        $routeData = [
                        1 => trans('Setup::StaticDataConfigMessage.route_po'),
                        2 => trans('Setup::StaticDataConfigMessage.route_im'),
                        3 => trans('Setup::StaticDataConfigMessage.route_iv')
                    ];

        return !empty($routeID) ? $routeData[$routeID] : $routeData;
    }

    /**
     * @DateOfCreation        23 July 2018
     * @ShortDescription      This function is responsible to get the staticData for medicine Route
     * @return                 Array of status and message
     */
    public function getWeight( ){
        $staticData = [
                       [
                        'id'        => 1, 
                        'value'     => trans('Setup::StaticDataConfigMessage.visit_vitals_label_weight'),
                        'type'      => 8, 
                        'name'      => 'physical_examination_fector', 
                        'field_type' => 'text', 
                        'options'   => [],
                        'cssClasses' => $this->textBoxCss('col-md-2','form-group','',''),
                        'handlers'  => 'changeValue',
                        'field_name'  => 'weight',
                        'restrictType'  => 'digitsWithDotOnly',
                    ]
                ];

        return $staticData;
    }

    /**
     * @DateOfCreation        23 July 2018
     * @ShortDescription      This function is responsible to get the staticData for medicine Route
     * @return                 Array of status and message
     */
    public function getHeight(){
        $staticData = [
                       
                    [
                        'id'        => 2, 
                        'value'     => trans('Setup::StaticDataConfigMessage.visit_physical_examination_label_height'),
                        'type'      => 8, 
                        'name'      => 'physical_examination_fector', 
                        'field_type' => 'text', 
                        'options'   => [],
                        'cssClasses' => $this->textBoxCss('col-md-2','form-group','',''),
                        'handlers'  => 'changeValue',
                        'field_name'  => 'height',
                        'restrictType'  => 'digitsWithDotOnly',
                    ]
                    
                ];

        return $staticData;
    }

    /**
     * @DateOfCreation        23 July 2018
     * @ShortDescription      This function is responsible to get the staticData for medicine Route
     * @return                 Array of status and message
     */
    public function getBmi(){
        $staticData = [
                       
                    [
                        'id'        => 3, 
                        'value'     => trans('Setup::StaticDataConfigMessage.visit_physical_examination_label_bmi'),
                        'type'      => 8, 
                        'name'      => 'physical_examination_fector', 
                        'field_type' => 'text', 
                        'options'   => [],
                        'cssClasses' => $this->textBoxCss('col-md-2','form-group','',''),
                        'handlers'  => 'changeValue',
                        'field_name'  => 'bmi',
                        'restrictType'  => 'digitsOnly',
                    ]
                    
                ];

        return $staticData;
    }

    /**
     * @DateOfCreation        23 July 2018
     * @ShortDescription      This function is responsible to get the staticData for medicine Route
     * @return                 Array of status and message
     */
    public function getPhysicalExzmainationOther(){
        $staticData = [
                       
                    [
                        'id'        => 4, 
                        'value'     => trans('Setup::StaticDataConfigMessage.visit_physical_examination_label_jpv'),
                        'type'      => 8, 
                        'name'      => 'physical_examination_fector', 
                        'field_type' => 'text', 
                        'options'   => [],
                        'cssClasses' => $this->textBoxCss('col-md-2','form-group','','')
                    ],
                    [
                        'id'        => 10, 
                        'value'     => trans('Setup::StaticDataConfigMessage.visit_physical_examination_checkbox_cyanosis'),
                        'type'      => 8, 
                        'name'      => 'physical_examination_fector', 
                        'field_type' => 'customcheckbox', 
                        'options'   => [
                                        '1' => trans('Setup::StaticDataConfigMessage.yes'),
                                        '2' => trans('Setup::StaticDataConfigMessage.no'),
                                        ],
                        'cssClasses' => $this->checkboxCss('col-md-3','control-label', 'check-list-radio', '')                
                    ],
                    [
                        'id'        => 11, 
                        'value'     => trans('Setup::StaticDataConfigMessage.visit_physical_examination_checkbox_clubbing'),
                        'type'      => 8, 
                        'name'      => 'physical_examination_fector', 
                        'field_type' => 'customcheckbox', 
                        'options'   => [
                                        '1' => trans('Setup::StaticDataConfigMessage.visit_physical_examination_checkbox_option_present'),
                                        '2' => trans('Setup::StaticDataConfigMessage.visit_physical_examination_checkbox_option_absent'),
                                        ],
                        'cssClasses' => $this->checkboxCss('col-md-3','control-label', 'check-list-radio', '')
                    ],
                    [
                        'id'        => 12, 
                        'value'     => trans('Setup::StaticDataConfigMessage.visit_physical_examination_checkbox_crackles'),
                        'type'      => 8, 
                        'name'      => 'physical_examination_fector', 
                        'field_type' => 'customcheckbox', 
                        'options'   => [
                                        '1' => trans('Setup::StaticDataConfigMessage.visit_physical_examination_checkbox_option_present'),
                                        '2' => trans('Setup::StaticDataConfigMessage.visit_physical_examination_checkbox_option_absent'),
                                        ],
                        'cssClasses' => $this->checkboxCss('col-md-3','control-label', 'check-list-radio', '')
                    ],
                    [
                        'id'        => 13, 
                        'value'     => trans('Setup::StaticDataConfigMessage.visit_physical_examination_checkbox_wheeze'),
                        'type'      => 8, 
                        'name'      => 'physical_examination_fector', 
                        'field_type' => 'customcheckbox', 
                        'options'   => [
                                        '1' => trans('Setup::StaticDataConfigMessage.visit_physical_examination_checkbox_option_present'),
                                        '2' => trans('Setup::StaticDataConfigMessage.visit_physical_examination_checkbox_option_absent'),
                                        ],
                        'cssClasses' => $this->checkboxCss('col-md-3','control-label', 'check-list-radio', '')
                    ],
                    [
                        'id'        => 14, 
                        'value'     => trans('Setup::StaticDataConfigMessage.visit_physical_examination_checkbox_p2_loud'),
                        'type'      => 8, 
                        'name'      => 'physical_examination_fector', 
                        'field_type' => 'customcheckbox', 
                        'options'   => [
                                        '1' => trans('Setup::StaticDataConfigMessage.visit_physical_examination_checkbox_option_present'),
                                        '2' => trans('Setup::StaticDataConfigMessage.visit_physical_examination_checkbox_option_absent'),
                                        ],
                        'cssClasses' => $this->checkboxCss('col-md-3','control-label', 'check-list-radio', '')
                    ],
                    [
                        'id'        => 15, 
                        'value'     => trans('Setup::StaticDataConfigMessage.visit_physical_examination_checkbox_pallor'),
                        'type'      => 8, 
                        'name'      => 'physical_examination_fector', 
                        'field_type' => 'customcheckbox', 
                        'options'   => [
                                        '1' => trans('Setup::StaticDataConfigMessage.visit_physical_examination_checkbox_option_present'),
                                        '2' => trans('Setup::StaticDataConfigMessage.visit_physical_examination_checkbox_option_absent'),
                                        ],
                        'cssClasses' => $this->checkboxCss('col-md-3','control-label', 'check-list-radio', '')
                    ],
                    [
                        'id'        => 16, 
                        'value'     => trans('Setup::StaticDataConfigMessage.visit_physical_examination_checkbox_icterus'),
                        'type'      => 8, 
                        'name'      => 'physical_examination_fector', 
                        'field_type' => 'customcheckbox', 
                        'options'   => [
                                        '1' => trans('Setup::StaticDataConfigMessage.visit_physical_examination_checkbox_option_present'),
                                        '2' => trans('Setup::StaticDataConfigMessage.visit_physical_examination_checkbox_option_absent'),
                                        ],
                        'cssClasses' => $this->checkboxCss('col-md-3','control-label', 'check-list-radio', ''),
                        'isClearfix' => TRUE,
                    ],
                    [
                        'id'        => 17, 
                        'value'     => trans('Setup::StaticDataConfigMessage.visit_physical_examination_label_lymphadenopathy'),
                        'type'      => 8, 
                        'name'      => 'physical_examination_fector', 
                        'field_type' => 'customcheckbox', 
                        'options'   => [
                                        '1' => trans('Setup::StaticDataConfigMessage.visit_physical_examination_checkbox_option_present'),
                                        '2' => trans('Setup::StaticDataConfigMessage.visit_physical_examination_checkbox_option_absent'),
                                        ],

                        'handlers'  => 'showHide',
                        'field_name'  => 'lymphocytes',
                        'cssClasses' => $this->checkboxCss('col-md-3','control-label', 'check-list-radio', ''),
                    ],
                    [
                        'id'        => 18, 
                        'value'     => trans('Setup::StaticDataConfigMessage.visit_physical_examination_label_location'),
                        'type'      => 8, 
                        'name'      => 'physical_examination_fector', 
                        'placeholder' => 'Location', 
                        'field_type'  => 'text', 
                        'options'     => [],
                        'show_hide_trigger' => 'lymphocytes',
                        'cssClasses' => $this->textBoxCss('col-md-2','form-group','','')
                    ],
                    [
                        'id'        => 19, 
                        'value'     => trans('Setup::StaticDataConfigMessage.visit_physical_examination_select_initial_body_state'),
                        'type'      => 8, 
                        'name'      => 'physical_examination_fector', 
                        'field_type' => 'select', 
                        'options'   => $this->getDynamicOptionsDataId(['Conscious','Oriented','Comfortable']),
                        'placeholder'=> '', 
                        'cssClasses' => $this->textBoxCss('col-md-2', 'form-group', 'control-label', '')
                    ],
                    [
                        'id'        => 20, 
                        'value'     => trans('Setup::StaticDataConfigMessage.visit_physical_examination_label_temp'),
                        'type'      => 8, 
                        'name'      => 'physical_examination_fector', 
                        'field_type' => 'text', 
                        'options'   => [],
                        'cssClasses' => $this->textBoxCss('col-md-2','form-group','',''),
                        'isClearfix' => TRUE
                    ],
                    [
                        'id'        => 1005, 
                        'value'     => trans('Setup::StaticDataConfigMessage.visit_physical_examination_heading_pulse'),
                        'type'      => 8, 
                        'name'      => 'physical_examination_fector',
                        'field_type' => 'groupHeading',
                        'options'   => [], 
                        'cssClasses' => ['col-md-12','form-group'],
                        'isClearfix' => TRUE
                    ],
                    [
                        'id'        => 21, 
                        'value'     => trans('Setup::StaticDataConfigMessage.visit_physical_examination_label_pulse_rate'),
                        'type'      => 8, 
                        'name'      => 'physical_examination_fector', 
                        'field_type' => 'text', 
                        'options'   => [],
                        'cssClasses' => $this->textBoxCss('col-md-2','form-group','','')
                    ],
                    [
                        'id'        => 22, 
                        'value'     => trans('Setup::StaticDataConfigMessage.visit_physical_examination_label_pulse_rhythm'),
                        'type'      => 8, 
                        'name'      => 'physical_examination_fector', 
                        'field_type' => 'text', 
                        'options'   => [],
                        'cssClasses' => $this->textBoxCss('col-md-2','form-group','','')
                    ],
                    [
                        'id'        => 23, 
                        'value'     => trans('Setup::StaticDataConfigMessage.visit_physical_examination_label_pulse_volume'),
                        'type'      => 8, 
                        'name'      => 'physical_examination_fector', 
                        'field_type' => 'text', 
                        'options'   => [],
                        'cssClasses' => $this->textBoxCss('col-md-2','form-group','','')
                    ],
                    [
                        'id'        => 24, 
                        'value'     => trans('Setup::StaticDataConfigMessage.visit_physical_examination_label_pulse_character'),
                        'type'      => 8, 
                        'name'      => 'physical_examination_fector', 
                        'field_type' => 'text', 
                        'options'   => [],
                        'cssClasses' => $this->textBoxCss('col-md-2','form-group','','')
                    ],
                    [
                        'id'        => 25, 
                        'value'     => trans('Setup::StaticDataConfigMessage.visit_physical_examination_label_peripheral_pulses'),
                        'type'      => 8, 
                        'name'      => 'physical_examination_fector', 
                        'field_type' => 'text', 
                        'options'   => [],
                        'cssClasses' => $this->textBoxCss('col-md-2','form-group','','')
                    ],
                    [
                        'id'        => 26, 
                        'value'     => trans('Setup::StaticDataConfigMessage.visit_physical_examination_label_rr_rf_delay'),
                        'type'      => 8, 
                        'name'      => 'physical_examination_fector', 
                        'field_type' => 'text', 
                        'options'   => [],
                        'cssClasses' => $this->textBoxCss('col-md-2','form-group','','')
                    ],
                    [
                        'id'        => 27, 
                        'value'     => trans('Setup::StaticDataConfigMessage.visit_physical_examination_label_pulse_deficit'),
                        'type'      => 8, 
                        'name'      => 'physical_examination_fector', 
                        'field_type' => 'text', 
                        'options'   => [],
                        'cssClasses' => $this->textBoxCss('col-md-2','form-group','','')
                    ],
                    [
                        'id'        => 28, 
                        'value'     => trans('Setup::StaticDataConfigMessage.visit_physical_examination_label_vessel_wall'),
                        'type'      => 8, 
                        'name'      => 'physical_examination_fector', 
                        'field_type' => 'text', 
                        'options'   => [],
                        'cssClasses' => $this->textBoxCss('col-md-2','form-group','','')
                    ],
                    [
                        'id'        => 29, 
                        'value'     => trans('Setup::StaticDataConfigMessage.visit_physical_examination_label_carotid_bruit'),
                        'type'      => 8, 
                        'name'      => 'physical_examination_fector', 
                        'field_type' => 'text', 
                        'options'   => [],
                        'cssClasses' => $this->textBoxCss('col-md-2','form-group','','')
                    ],
                    [
                        'id'        => 30, 
                        'value'     => trans('Setup::StaticDataConfigMessage.visit_physical_examination_label_bp_mmhg'),
                        'type'      => 8, 
                        'name'      => 'physical_examination_fector', 
                        'field_type' => 'text', 
                        'options'   => [],
                        'cssClasses' => $this->textBoxCss('col-md-2','form-group','','')
                    ],
                    [
                        'id'        => 1002, 
                        'value'     => trans('Setup::StaticDataConfigMessage.visit_physical_examination_heading_respiration'),
                        'type'      => 8, 
                        'name'      => 'physical_examination_fector',
                        'field_type' => 'groupHeading',
                        'options'   => [], 
                        'cssClasses' => ['col-md-12','form-group'],
                        'isClearfix' => TRUE
                    ],
                    [
                        'id'        => 31, 
                        'value'     => trans('Setup::StaticDataConfigMessage.visit_physical_examination_label_respiration_rate'),
                        'type'      => 8, 
                        'name'      => 'physical_examination_fector', 
                        'field_type' => 'text', 
                        'options'   => [],
                        'cssClasses' => $this->textBoxCss('col-md-2','form-group','','')
                    ],
                    [
                        'id'        => 32, 
                        'value'     => trans('Setup::StaticDataConfigMessage.visit_physical_examination_label_respiration_rhythm'),
                        'type'      => 8, 
                        'name'      => 'physical_examination_fector', 
                        'field_type' => 'text', 
                        'options'   => [],
                        'cssClasses' => $this->textBoxCss('col-md-2','form-group','','')
                    ],
                    [
                        'id'        => 33, 
                        'value'     => trans('Setup::StaticDataConfigMessage.visit_physical_examination_checkbox_respiration_type'),
                        'type'      => 8, 
                        'name'      => 'physical_examination_fector', 
                        'field_type' => 'customcheckbox', 
                        'options'   => [
                                        '1' => trans('Setup::StaticDataConfigMessage.visit_physical_examination_checkbox_option_abdomino_thoracic'),
                                        '2' => trans('Setup::StaticDataConfigMessage.visit_physical_examination_checkbox_option_thoraco_abdominal'),
                                        ],
                        'cssClasses' => $this->checkboxCss('col-md-4','control-label', 'check-list-radio', ''),
                    ],
                    [
                        'id'        => 34, 
                        'value'     => trans('Setup::StaticDataConfigMessage.visit_physical_examination_label_spo2'),
                        'type'      => 8, 
                        'name'      => 'physical_examination_fector', 
                        'field_type' => 'text', 
                        'options'   => [],
                        'cssClasses' => $this->textBoxCss('col-md-2','form-group','',''),
                        'isClearfix' => TRUE
                    ],
                    [
                        'id'        => 43, 
                        'value'     => trans('Setup::StaticDataConfigMessage.visit_physical_examination_select_spine'),
                        'type'      => 8, 
                        'name'      => 'physical_examination_fector', 
                        'field_type' => 'select', 
                        'options'   => $this->getDynamicOptionsDataId(['Kyphosis','Scoliosis','Gibbus']),
                        'placeholder'=> '', 
                        'cssClasses' => $this->textBoxCss('col-md-2', 'form-group', 'control-label', '')
                    ],
                    [
                        'id'        => 44, 
                        'value'     => trans('Setup::StaticDataConfigMessage.visit_physical_examination_label_skull'),
                        'type'      => 8, 
                        'name'      => 'physical_examination_fector', 
                        'field_type' => 'text', 
                        'options'   => [],
                        'cssClasses' => $this->textBoxCss('col-md-2','form-group','','')
                    ],
                    [
                        'id'        => 48, 
                        'value'     => trans('Setup::StaticDataConfigMessage.visit_physical_examination_label_marfanoid_features'),
                        'type'      => 8, 
                        'name'      => 'physical_examination_fector', 
                        'field_type' => 'customcheckbox', 
                        'options'   => [
                                    '1' => trans('Setup::StaticDataConfigMessage.yes'),
                                    '2' => trans('Setup::StaticDataConfigMessage.no')
                                ],
                        'cssClasses' => $this->checkboxCss('col-md-2','control-label', 'check-list-radio', ''),
                    ],
                    [
                        'id'        => 49, 
                        'value'     => trans('Setup::StaticDataConfigMessage.visit_physical_examination_label_thyroid'),
                        'type'      => 8, 
                        'name'      => 'physical_examination_fector', 
                        'field_type' => 'customcheckbox', 
                        'options'   => [
                                    '1' => trans('Setup::StaticDataConfigMessage.yes'),
                                    '2' => trans('Setup::StaticDataConfigMessage.no')
                                ],
                        'cssClasses' => $this->checkboxCss('col-md-2','control-label', 'check-list-radio', ''),
                    ],
                    [
                        'id'        => 50, 
                        'value'     => trans('Setup::StaticDataConfigMessage.visit_physical_examination_label_testes'),
                        'type'      => 8, 
                        'name'      => 'physical_examination_fector', 
                        'field_type' => 'customcheckbox', 
                        'options'   => [
                                    '1' => trans('Setup::StaticDataConfigMessage.yes'),
                                    '2' => trans('Setup::StaticDataConfigMessage.no')
                                ],
                        'cssClasses' => $this->checkboxCss('col-md-2','control-label', 'check-list-radio', ''),
                        'isClearfix' => TRUE
                    ],
                    [
                        'id'        => 53, 
                        'value'     => trans('Setup::StaticDataConfigMessage.visit_physical_examination_label_congenital_anomalies'),
                        'type'      => 8, 
                        'name'      => 'physical_examination_fector', 
                        'field_type' => 'text', 
                        'options'   => [],
                        'cssClasses' => $this->textBoxCss('col-md-2','form-group','','')
                    ],
                    [
                        'id'        => 5, 
                        'value'     => trans('Setup::StaticDataConfigMessage.visit_physical_examination_label_face'),
                        'type'      => 8, 
                        'name'      => 'physical_examination_fector', 
                        'field_type' => 'text', 
                        'options'   => [],
                        'cssClasses' => $this->textBoxCss('col-md-2','form-group','','')
                    ],
                    [
                        'id'        => 6, 
                        'value'     => trans('Setup::StaticDataConfigMessage.visit_physical_examination_label_eye'),
                        'type'      => 8, 
                        'name'      => 'physical_examination_fector', 
                        'field_type' => 'text', 
                        'options'   => [],
                        'cssClasses' => $this->textBoxCss('col-md-2','form-group','','')
                    ],
                    [
                        'id'        => 7, 
                        'value'     => trans('Setup::StaticDataConfigMessage.visit_physical_examination_label_skin'),
                        'type'      => 8, 
                        'name'      => 'physical_examination_fector', 
                        'field_type' => 'text', 
                        'options'   => [],
                        'cssClasses' => $this->textBoxCss('col-md-2','form-group','','')
                    ],
                    [
                        'id'        => 8, 
                        'value'     => trans('Setup::StaticDataConfigMessage.visit_physical_examination_label_joints'),
                        'type'      => 8, 
                        'name'      => 'physical_examination_fector', 
                        'field_type' => 'text', 
                        'options'   => [],
                        'cssClasses' => $this->textBoxCss('col-md-2','form-group','','')
                    ],
                    [
                        'id'        => 9, 
                        'value'     => trans('Setup::StaticDataConfigMessage.visit_physical_examination_label_other'),
                        'type'      => 8, 
                        'name'      => 'physical_examination_fector', 
                        'field_type' => 'text', 
                        'options'   => [],
                        'cssClasses' => $this->textBoxCss('col-md-2','form-group','','')
                    ],
                ];

        return $staticData;
    }


    /**
     * @DateOfCreation        23 July 2018
     * @ShortDescription      This function is responsible to get the staticData for medicine Route
     * @return                 Array of status and message
     */
    public function getPhysicalExzmaination(){
            $staticData  = [];
            $staticData  = array_merge($staticData,$this->getWeight(),$this->getHeight(),$this->getBmi(),$this->getPhysicalExzmainationOther());
            return $staticData;
    }

    /**
     * @DateOfCreation        23 July 2018
     * @ShortDescription      This function is responsible to get the staticData for medicine Route
     * @return                 Array of status and message
     */
    public function getPhysicalOption($functionName){
        $optionData = ['getWeight','getHeight','getBmi'];
        $staticData = [];
        if(!empty($functionName)){
            foreach ($functionName as $key => $value) {
                if(in_array($value, $optionData)){
                    $staticData = array_merge($staticData,$this->$value());
                }
            }
        }
        return $staticData;
    }

    /**
     * @DateOfCreation        22 June 2018
     * @ShortDescription      This function is responsible to get the staticData for HRCT Factor
     * @return                 Array of status and message
     */
    public function getHRCTExtra(){

         $staticValue = [
                [
                    'id'            => 4, 
                    'value'         => trans('Setup::StaticDataConfigMessage.hrct_reticulations_traction'),
                    'name'          => 'phrct_factor_', 
                    'field_type'    => 'customcheckbox', 
                    'placeholder'   => '', 
                    'options'       => $this->covertToOption($this->getYesNoData()),
                    'cssClasses'    =>  $this->checkboxCss('col-md-12 rpl', 'col-md-9 align-checkbox-list', 'col-md-3', 'form-group checkbox-listing checkbox-formgroup'),
                    'isClearfix'    => TRUE,
                ],
                [
                    'id'            => 5, 
                    'value'         => trans('Setup::StaticDataConfigMessage.hrct_type_select'),
                    'name'          => 'phrct_factor_', 
                    'field_type'    => 'select', 
                    'options'       => $this->covertToOption($this->getTypeOfTypeSelect()),
                    'placeholder'   => trans('Setup::StaticDataConfigMessage.hrct_type_select'), 
                    'cssClasses'    => $this->textBoxCss('col-md-3', 'form-group', 'control-label', ''),
                    'isClearfix'    => TRUE,
                ],
                [
                    'id'            => 6, 
                    'value'         => trans('Setup::StaticDataConfigMessage.hrct_cysts'),
                    'name'          => 'phrct_factor_', 
                    'field_type'    => 'customcheckbox', 
                    'placeholder'   => '', 
                    'options'       => $this->covertToOption($this->getYesNoData()),
                    'isClearfix'    => TRUE,
                    'cssClasses'    => $this->checkboxCss('col-md-6 rpl', 'col-md-8 align-checkbox-list', 'col-md-4', 'form-group checkbox-listing checkbox-formgroup')
                ],
                [
                    'id'            => 7, 
                    'value'         => trans('Setup::StaticDataConfigMessage.hrct_emphysema'),
                    'name'          => 'phrct_factor_', 
                    'field_type'    => 'customcheckbox', 
                    'placeholder'   => '', 
                    'options'       => $this->covertToOption($this->getYesNoData()),
                    'cssClasses'    => $this->checkboxCss('col-md-6 rpl', 'col-md-8 align-checkbox-list', 'col-md-4', 'form-group checkbox-listing checkbox-formgroup'),
                    'isClearfix'    => TRUE,
                ],
                [
                    'id'            => 8, 
                    'value'         => trans('Setup::StaticDataConfigMessage.hrct_mediastinal_lymphadenopathy'),
                    'name'          => 'phrct_factor_', 
                    'field_type'    => 'customcheckbox', 
                    'placeholder'   => '', 
                    'options'       => $this->covertToOption($this->getYesNoData()),
                    'cssClasses'    => $this->checkboxCss('col-md-6 rpl', 'col-md-8 align-checkbox-list', 'col-md-4', 'form-group checkbox-listing checkbox-formgroup'),
                    'isClearfix'    => TRUE,
                ],
                [
                    'id'            => 9, 
                    'value'         => trans('Setup::StaticDataConfigMessage.hrct_sub_pleural_sparing'),
                    'name'          => 'phrct_factor_', 
                    'field_type'    => 'customcheckbox', 
                    'placeholder'   => '', 
                    'options'       => $this->covertToOption($this->getYesNoData()),
                    'cssClasses'    => $this->checkboxCss('col-md-6 rpl', 'col-md-8 align-checkbox-list', 'col-md-4', 'form-group checkbox-listing checkbox-formgroup'),
                    'isClearfix'    => TRUE,
                ],
                [
                    'id'            => 10, 
                    'value'         => trans('Setup::StaticDataConfigMessage.hrct_esophageal_dilatation'),
                    'name'          => 'phrct_factor_', 
                    'field_type'    => 'customcheckbox', 
                    'placeholder'   => '', 
                    'options'       => $this->covertToOption($this->getYesNoData()),
                    'cssClasses'    => $this->checkboxCss('col-md-6 rpl', 'col-md-8 align-checkbox-list', 'col-md-4', 'form-group checkbox-listing checkbox-formgroup'),
                    'isClearfix'    => TRUE,
                ],
                [
                    'id'            => 11, 
                    'value'         => trans('Setup::StaticDataConfigMessage.hrct_distribution'),
                    'name'          => 'phrct_factor_', 
                    'field_type'    => 'select', 
                    'options'       => $this->covertToOption($this->getTypeOfDistribution()),
                    'placeholder'   => trans('Setup::StaticDataConfigMessage.hrct_distribution'), 
                    'cssClasses'    => $this->textBoxCss('col-md-3', 'form-group', 'control-label', ''),
                ],
                [
                    'id'            => 12, 
                    'value'         => trans('Setup::StaticDataConfigMessage.hrct_axial_distribution'),
                    'name'          => 'phrct_factor_', 
                    'field_type'    => 'select', 
                    'options'       => $this->covertToOption($this->getTypeOfAxialDistribution()),
                    'placeholder'   => trans('Setup::StaticDataConfigMessage.hrct_axial_distribution'), 
                    'cssClasses'    => $this->textBoxCss('col-md-3', 'form-group', 'control-label', '')
                ]

            ];
        return $staticValue;
    }

     /**
     * @DateOfCreation        22 June 2018
     * @ShortDescription      This function is responsible to get the staticData for yes no option value
     * @return                 Array of status and message
     */
    public function getTypeOfAxialDistribution(){
        $staticValue = [
            ['id' => '1', 'value' => trans('Setup::StaticDataConfigMessage.hrct_option_peripheral_subpleural')],
            ['id' => '2', 'value' => trans('Setup::StaticDataConfigMessage.hrct_option_peribronchovascular_central_perihilar')],
        ];
        return $staticValue; 
    }

     /**
     * @DateOfCreation        22 June 2018
     * @ShortDescription      This function is responsible to get the staticData for yes no option value
     * @return                 Array of status and message
     */
    public function getTypeOfDistribution(){
        $staticValue = [
            ['id' => '1', 'value' => trans('Setup::StaticDataConfigMessage.hrct_option_upper')],
            ['id' => '2', 'value' => trans('Setup::StaticDataConfigMessage.hrct_option_lower')],
            ['id' => '3', 'value' => trans('Setup::StaticDataConfigMessage.hrct_option_diffuse')],
        ];
        return $staticValue; 
    }

     /**
     * @DateOfCreation        22 June 2018
     * @ShortDescription      This function is responsible to get the staticData for yes no option value
     * @return                 Array of status and message
     */
    public function getTypeOfTypeSelect(){
        $staticValue = [
            ['id' => '1', 'value' => trans('Setup::StaticDataConfigMessage.hrct_option_Perilymphatic')],
            ['id' => '2', 'value' => trans('Setup::StaticDataConfigMessage.hrct_option_Random')],
            ['id' => '3', 'value' => trans('Setup::StaticDataConfigMessage.hrct_option_ill_defined')],
        ];
        return $staticValue; 
    }

    /**
     * @DateOfCreation        8 Aug 2018
     * @ShortDescription      This function is responsible to get the disease id by name
     * @return                id
     */
    public function getDieseaseIdByName($name){
        return $this->dbSelect('diseases', 'disease_id', ['disease_name' => $name, 'is_deleted' => Config::get('constants.IS_DELETED_NO')])->disease_id;
    }

    /**
     * @DateOfCreation        08 Aug 2018
     * @ShortDescription      This function is responsible to get the staticData for GERD Diagnosis
     * @return                Array of status and message
     */
    private function getGerdFrom(){
        $getGERDDiseaseId = $this->getDieseaseIdByName('GERD');
        return 
        [
            [
                'id'        => 1, 
                'value'     => trans('Setup::StaticDataConfigMessage.date'),
                'type'      => 1, 
                'name'      => 'date_of_diagnosis_'.$this->securityLibObj->encrypt($getGERDDiseaseId), 
                'field_type' => 'date', 
                'options'   => [],
                'cssClasses' => $this->textBoxCss('col-md-6', 'form-group', 'check-list-radio', '')
            ],
            [
                'id'            => 2, 
                'value'         => trans('Setup::StaticDataConfigMessage.gerd'),
                'name'          => 'diagnosis_fector_value_'.$this->securityLibObj->encrypt($getGERDDiseaseId), 
                'field_type'    => 'customcheckbox', 
                'options'       => [
                                        1 => trans('Setup::StaticDataConfigMessage.by_history'),
                                        2 => trans('Setup::StaticDataConfigMessage.by_investigation')
                                ],
                'cssClasses'    => $this->textBoxCss('col-md-6', 'form-group', 'control-label', '')
            ],
        ];
    }

    /**
     * @DateOfCreation        08 Aug 2018
     * @ShortDescription      This function is responsible to get the staticData for CTD Diagnosis
     * @return                Array of status and message
     */
    private function getCtdFrom(){
        $getCTDDiseaseId = $this->getDieseaseIdByName('CTD');
        return 
        [
            [
                'id'            => 1, 
                'value'         => trans('Setup::StaticDataConfigMessage.date'),
                'type'          => 1, 
                'name'          => 'date_of_diagnosis_'.$this->securityLibObj->encrypt($getCTDDiseaseId), 
                'placeholder'   => trans('Setup::StaticDataConfigMessage.date'), 
                'field_type'    => 'date', 
                'options'       => [],
                'cssClasses'    => $this->textBoxCss('col-md-4', 'form-group', 'check-list-radio', '')
            ],
            [
                'id'            => 3, 
                'value'         => trans('Setup::StaticDataConfigMessage.visit_diagnosis_title'),
                'type'          => 1, 
                'name'          => 'diagnosis_fector_value_'.$this->securityLibObj->encrypt($getCTDDiseaseId), 
                'placeholder'   => trans('Setup::StaticDataConfigMessage.visit_diagnosis_title'), 
                'field_type'    => 'text', 
                'options'       => [],
                'cssClasses'    => $this->textBoxCss('col-md-4', 'form-group', 'check-list-radio', '')
            ],
            [
                'id'            => 4, 
                'value'         => trans('Setup::StaticDataConfigMessage.gerd'),
                'name'          => 'diagnosis_fector_value_'.$this->securityLibObj->encrypt($getCTDDiseaseId), 
                'field_type'    => 'customcheckbox', 
                'options'       => [
                                    '1' => trans('Setup::StaticDataConfigMessage.yes'), 
                                    '2' => trans('Setup::StaticDataConfigMessage.no')
                                ],
                'cssClasses'    => $this->textBoxCss('col-md-4', 'form-group', 'control-label', '')
            ],
        ];
    }

    /**
     * @DateOfCreation        08 Aug 2018
     * @ShortDescription      This function is responsible to get the staticData for Pulmonary Diagnosis
     * @return                 Array of status and message
     */
    private function getPulmonaryForm(){
        $getPulmonaryDiseaseId = $this->getDieseaseIdByName('Pulmonary hypertension: 2D ECHO');
        return 
        [
            [
                'id'        => 1, 
                'value'     => trans('Setup::StaticDataConfigMessage.date'),
                'type'      => 1, 
                'name'      => 'date_of_diagnosis_'.$this->securityLibObj->encrypt($getPulmonaryDiseaseId), 
                'field_type' => 'date', 
                'options'   => [],
                'cssClasses' => $this->textBoxCss('col-md-4', 'form-group', 'check-list-radio', '')
            ],
            [
                'id'            => 5, 
                'value'         => trans('Setup::StaticDataConfigMessage.visit_diagnosis_pa_systolic'),
                'type'          => 1, 
                'name'          => 'diagnosis_fector_value_'.$this->securityLibObj->encrypt($getPulmonaryDiseaseId), 
                'field_type'    => 'text', 
                'options'       => [],
                'placeholder'   => trans('Setup::StaticDataConfigMessage.visit_diagnosis_pa_systolic'), 
                'cssClasses'    => $this->textBoxCss('col-md-4', 'form-group', 'check-list-radio', '')
            ],
            [
                'id'            => 6, 
                'value'         => trans('Setup::StaticDataConfigMessage.visit_diagnosis_rt_heart_catheter'),
                'type'          => 1, 
                'name'          => 'diagnosis_fector_value_'.$this->securityLibObj->encrypt($getPulmonaryDiseaseId), 
                'field_type'    => 'text', 
                'options'       => [],
                'placeholder'   => trans('Setup::StaticDataConfigMessage.visit_diagnosis_rt_heart_catheter'), 
                'cssClasses'    => $this->textBoxCss('col-md-4', 'form-group', 'check-list-radio', '')
            ],
            [
                'id'            => 7, 
                'value'         => trans('Setup::StaticDataConfigMessage.visit_diagnosis_mean_wedge_pressure'),
                'type'          => 1, 
                'name'          => 'diagnosis_fector_value_'.$this->securityLibObj->encrypt($getPulmonaryDiseaseId), 
                'field_type'    => 'text', 
                'options'       => [],
                'placeholder'   => trans('Setup::StaticDataConfigMessage.visit_diagnosis_mean_wedge_pressure'), 
                'cssClasses'    => $this->textBoxCss('col-md-4', 'form-group', 'check-list-radio', '')
            ],
            [
                'id'            => 8, 
                'value'         => trans('Setup::StaticDataConfigMessage.visit_diagnosis_repeat_serology'),
                'type'          => 1, 
                'name'          => 'diagnosis_fector_value_'.$this->securityLibObj->encrypt($getPulmonaryDiseaseId), 
                'field_type'    => 'customcheckbox', 
                'options'       => [
                                    '1' => trans('Setup::StaticDataConfigMessage.yes'), 
                                    '2' => trans('Setup::StaticDataConfigMessage.no')
                                ],
                'cssClasses'    => $this->textBoxCss('col-md-4', 'form-group', 'check-list-radio', '')
            ],
        ];

    }

    /**
     * @DateOfCreation        8 Aug 2018
     * @ShortDescription      This function is responsible to get response of function
     * @return                Array of status and message
     */
    public function getStaticDataFunction($functionName){
        $optionData = ['getGerdFrom','getCtdFrom','getPulmonaryForm', 'vitalsFectorData', 'getWeight','getTimingOption','getSlotDuarionOption','getsymptomsTestData','getSystemicExamination','getAllergiesHistoryData'];
        $staticData = [];
        if(!empty($functionName)){
            foreach ($functionName as $key => $value) {
                if(in_array($value, $optionData)){
                    $staticData = array_merge($staticData,$this->$value());
                }
            }
        }
        return $staticData;
    }

    /**
     * @DateOfCreation        8 Aug 2018
     * @ShortDescription      This function is responsible to gender name using genderId
     * @return                string gender name 
     */
    public function getGenderNameById($genderId=''){
        $genderData = $this->getGenderData();
        $genderData = array_pluck($genderData,'value','id');
        $genderName = isset($genderData[$genderId]) ? $genderData[$genderId] :'';
        return $genderName;
    }

    /**
     * @DateOfCreation        8 Aug 2018
     * @ShortDescription      This function is responsible to gender name using genderId
     * @return                string gender name 
     */
    public function getTitleNameById($titleId=''){
        $titleData = $this->getTitleData();
        $titleData = array_pluck($titleData,'value','id');
        $titleName = isset($titleData[$titleId]) ? $titleData[$titleId] :'';
        return $titleName;
    }

    /**
     * @DateOfCreation        8 Aug 2018
     * @ShortDescription      This function is responsible to gender name using genderId
     * @return                string gender name 
     */
    public function getStaffRoleById($StaffId=''){
        $StaffData = $this->getStaffRole();
        $StaffData = array_pluck($StaffData,'value','id');
        $StaffRole = isset($StaffData[$StaffId]) ? $StaffData[$StaffId] :'';
        return $StaffRole;
    }

     /**
     * @DateOfCreation        22 June 2018
     * @ShortDescription      This function is responsible to get the staticData for Domestic factor condtion list
     * @return                 Array of status and message
     */
    public function getManageCalendarData(){
        $staticValue = [
            [
                'id' => 1,
                'value' => trans('Setup::StaticDataConfigMessage.manage_calendar_slot_duration'),
                'input_type' => 'select' ,
                'input_type_option'=>'getSlotDuarionOption',
                'placeholder' =>'',
                'cssClasses' => $this->textBoxCss('col-md-3', 'form-group', 'control-label', ''),
                'isClearfix' => FALSE,
                'input_name' =>'mcs_slot_duration',
                'validations_required'=>true
            ],
            [
                'id' => 2,
                'value' => trans('Setup::StaticDataConfigMessage.manage_calendar_start_time'),
                'input_type' => 'select' ,
                'input_type_option'=>'getTimingOption',
                'placeholder' =>'',
                'cssClasses' => $this->textBoxCss('col-md-3', 'form-group', 'control-label', ''),
                'isClearfix' => FALSE,
                'input_name' =>'mcs_start_time',
                'validations_required'=>true
            ],
            [
                'id' => 3,
                'value' => trans('Setup::StaticDataConfigMessage.manage_calendar_end_time'),
                'input_type' => 'select' ,
                'input_type_option'=>'getTimingOption',
                'placeholder' =>'',
                'cssClasses' => $this->textBoxCss('col-md-3', 'form-group', 'control-label', ''),
                'isClearfix' => True,
                'input_name' =>'mcs_end_time',
                'validations_required'=>true
            ],
            
        ];
        return $staticValue; 
    }

    /**
     * @DateOfCreation        22 June 2018
     * @ShortDescription      This function is responsible to get the staticData for yes no option value
     * @return                 Array of status and message
     */
    public function getSlotDuarionOption(){
        $staticValue = [
            ['id' => '15', 'value' => trans('Setup::StaticDataConfigMessage.15_minutes')],
            ['id' => '30', 'value' => trans('Setup::StaticDataConfigMessage.30_minutes')],
            ['id' => '45', 'value' => trans('Setup::StaticDataConfigMessage.45_minutes')],
            ['id' => '60', 'value' => trans('Setup::StaticDataConfigMessage.60_minutes')],
        ];
        return $staticValue; 
    }

    /**
     * @DateOfCreation        22 June 2018
     * @ShortDescription      This function is responsible to get the staticData for yes no option value
     * @return                 Array of status and message
     */
    public function getTimingOption(){
        $searchObj = new Search();
        $timeing = [
            'start_time' => Config::get('constants.MANGE_DEFAULT_START_TIME'),
            'end_time' => Config::get('constants.MANGE_DEFAULT_END_TIME'),
            'slot_duration' => Config::get('constants.MANGE_DEFAULT_SLOT_DURATION'),
        ];
        $extraTimeSlotCreat = [
        'time_slot_format' => 'h:i A',
        'booking_calculation_disable' => '1',
        ];
        $timeSlots = $searchObj->createTimeSlot((object) $timeing, date('Y-m-d'),$extraTimeSlotCreat);
        $staticValue = !empty($timeSlots) ? array_map(function($row){
            $newRow = [];
            $newRow['id'] = $row['slot_time'];
            $newRow['value'] = $row['slot_time_format'];
            return $newRow;
        },$timeSlots):[];
        return $staticValue; 
    }

    /**
     * @DateOfCreation        22 June 2018
     * @ShortDescription      This function is responsible to get the staticData for Domestic factor condtion list
     * @return                 Array of status and message
     */
    public function getManageCalendarAppointmentData(){
        $staticValue = [
            [
                'id' => 1,
                'value' => trans('Setup::StaticDataConfigMessage.appointment_patient'),
                'input_type' => 'select' ,
                'input_type_option'=>'patientDetails',
                'placeholder' =>'',
                'cssClasses' => $this->textBoxCss('col-md-6', 'form-group', 'control-label', ''),
                'isClearfix' => FALSE,
                'input_name' =>'pat_id',
                'validations_required'=>true
            ],
            [
                'id' => 2,
                'value' => trans('Setup::StaticDataConfigMessage.appointment_reason'),
                'input_type' => 'select' ,
                'input_type_option'=>'patAppointmentReasonsData',
                'placeholder' =>'',
                'cssClasses' => $this->textBoxCss('col-md-6', 'form-group', 'control-label', ''),
                'isClearfix' => True,
                'input_name' =>'booking_reason',
                'validations_required'=>true
            ],
            [
                'id' => 3,
                'value' => trans('Setup::StaticDataConfigMessage.appointment_clinic'),
                'input_type' => 'select' ,
                'input_type_option'=>'clinicData',
                'placeholder' =>'',
                'cssClasses' => $this->textBoxCss('col-md-6', 'form-group', 'control-label', ''),
                'isClearfix' => FALSE,
                'input_name' =>'clinic_id',
                'validations_required'=>true
            ],
            [
                'id' => 4,
                'value' => trans('Setup::StaticDataConfigMessage.appointment_date'),
                'input_type' => 'text' ,
                'input_type_option'=>'',
                'placeholder' =>'',
                'cssClasses' => $this->textBoxCss('col-md-6', 'form-group', 'control-label', ''),
                'isClearfix' => True,
                'input_name' =>'booking_date',
                'readOnly' =>True,
                'validations_required'=>true
            ],
            [
                'id' => 5,
                'value' => trans('Setup::StaticDataConfigMessage.appointment_time'),
                'input_type' => 'select' ,
                'input_type_option'=>'bookingTimeData',
                'placeholder' =>'',
                'cssClasses' => $this->textBoxCss('col-md-6', 'form-group', 'control-label', ''),
                'isClearfix' => True,
                'input_name' =>'booking_time',
                'validations_required'=>true
            ],
            
        ];
        return $staticValue; 
    }


    /**
     * @DateOfCreation        22 June 2018
     * @ShortDescription      This function is responsible to get the staticData for yes no option value
     * @return                 Array of status and message
     */
    public function getDynamicOptionsData($type = array()){
        $option = array();
        if(!empty($type) && is_array($type)){
            $i = 1;
            foreach ($type as $key => $value) {
                $option[] = [
                    'label'=> $value,
                    'value'=> (String)$i
                ];
                $i++;
            }
        }
        return $option; 
    }

    /**
     * @DateOfCreation        22 June 2018
     * @ShortDescription      This function is responsible to get the staticData for yes no option value
     * @return                 Array of status and message
     */
    public function getDynamicSelectOption($type = array()){
        $option = array();
        if(!empty($type) && is_array($type)){
            $i = 1;
            foreach ($type as $key => $value) {
                $option[] = [
                    'value'=> $value,
                    'id'=> $i
                ];
                $i++;
            }
        }
        return $option; 
    }


    /**
     * @DateOfCreation        22 June 2018
     * @ShortDescription      This function is responsible to get the staticData for yes no option value
     * @return                 Array of status and message
     */
    public function getDynamicOptionsDataId($type = array()){
        $option = array();
        if(!empty($type) && is_array($type)){
            $i = 1;
            foreach ($type as $key => $value) {
                $option[$i] = $value;
                $i++;
            }
        }
        return $option; 
    }

    /**
     * @DateOfCreation        22 June 2018
     * @ShortDescription      This function is responsible to get the staticData for patient symptoms condition list
     * @return                 Array of status and message
     */
    public function getSystemicExamination(){
        $staticValue = [
            [
                'id'        => 1001, 
                'value'     => trans('Setup::StaticDataConfigMessage.systemic_examination_upper_respiratory_tract'),
                'type'      => 1, 
                'name'      => 'upper_respiratory_tract',
                'input_type' => 'groupHeading',
                'input_type_option' => '', 
                'cssClasses' => ['col-md-12','form-group'],
                'isClearfix' => TRUE
            ],
            [
               'id' => 2, 
               'value' => trans('Setup::StaticDataConfigMessage.systemic_examination_upper_respiratory_tract_nose'),
               'type'       => 1, 
                'name'       => 'upper_respiratory_tract_nose', 
                'input_type' => 'select', 
                'input_type_option' => $this->getDynamicOptionsData(['polyps','postnasal drip','DNS']),
                'placeholder'=> trans('Setup::StaticDataConfigMessage.upper_respiratory_tract_nose'), 
                'cssClasses' => $this->checkboxCss('col-md-2','control-label', 'check-list-radio', ''),
                'isClearfix' => FALSE,
            ],
            [
               'id' => 3, 
               'value' => trans('Setup::StaticDataConfigMessage.systemic_examination_upper_respiratory_tract_throat'),
               'type'=>1, 
                'name'       => 'upper_respiratory_tract_nose', 
                'input_type' => 'select', 
                'input_type_option' => $this->getDynamicOptionsData(['tonsils','oral hygiene','nicotine stain', 'poor']),
                'placeholder'=> trans('Setup::StaticDataConfigMessage.upper_respiratory_tract_throat'), 
                'cssClasses' => $this->checkboxCss('col-md-2','control-label', 'check-list-radio', ''),
                'isClearfix' => TRUE,
            ],
            [
                'id'        => 1002, 
                'value'     => trans('Setup::StaticDataConfigMessage.systemic_examination_inspection'),
                'type'      => 1, 
                'name'      => 'inspection',
                'input_type' => 'groupHeading',
                'input_type_option' => '', 
                'cssClasses' => ['col-md-12','form-group'],
                'isClearfix' => TRUE
            ],
            [
               'id' => 4, 
               'value' => trans('Setup::StaticDataConfigMessage.systemic_examination_inspection_chest_symmetry'),
               'type'=>1, 
                'name'       => 'upper_respiratory_tract_nose', 
                'input_type' => 'select', 
                'input_type_option' => $this->getDynamicOptionsData(['Symmetrical','Assymmetrical']),
                'placeholder'=> trans('Setup::StaticDataConfigMessage.upper_respiratory_tract_throat'), 
                'cssClasses' => $this->checkboxCss('col-md-2','control-label', 'check-list-radio', ''),
                'isClearfix' => FALSE,
            ],
            [
               'id' => 5, 
               'value' => trans('Setup::StaticDataConfigMessage.systemic_examination_inspection_pectus_excavatum_carinatum'),
               'type'=>1, 
               'input_type' => 'text' ,
               'input_type_option'=>'',
               'placeholder' =>'',
               'cssClasses' => $this->textBoxCss('col-md-2', 'form-group', 'check-list-', ''),
               'isClearfix' => FALSE,
            ],
            [
               'id' => 6, 
               'value' => trans('Setup::StaticDataConfigMessage.systemic_examination_inspection_shape_of_chest'),
               'type'=>1, 
                'name'       => 'shape_of_chest', 
                'input_type' => 'select', 
                'input_type_option' => $this->getDynamicOptionsData(['Normal','Barral','Flat']),
                'placeholder'=> '', 
                'cssClasses' => $this->checkboxCss('col-md-2','control-label', 'check-list-radio', ''),
                'isClearfix' => FALSE,
            ],
            [
               'id' => 7, 
               'value' => trans('Setup::StaticDataConfigMessage.systemic_examination_inspection_respiratory_movements'),
               'type'=>1, 
               'name'       => 'upper_respiratory_tract_nose', 
                'input_type' => 'customcheckbox', 
                'input_type_option' => $this->getDynamicOptionsData(['Equal','Unequal']),
                'placeholder'=> '', 
                'cssClasses' => $this->checkboxCss('col-md-2','control-label', 'check-list-radio', ''),
                'isClearfix' => FALSE,
            ],
            [
               'id' => 8, 
               'value' => trans('Setup::StaticDataConfigMessage.systemic_examination_inspection_respiratory_movements'),
               'type'=>1, 
               'name'       => 'upper_respiratory_tract_nose', 
                'input_type' => 'customcheckbox', 
                'input_type_option' => $this->getDynamicOptionsData(['Left','Right']),
                'placeholder'=> '', 
                'cssClasses' => $this->checkboxCss('col-md-2','control-label', 'check-list-radio', ''),
                'isClearfix' => FALSE,
            ],
            [
               'id' => 9, 
               'value' => trans('Setup::StaticDataConfigMessage.systemic_examination_inspection_tracheal_position'),
               'type'=>1, 
               'name'       => 'tracheal_position', 
                'input_type' => 'select', 
                'input_type_option' => $this->getDynamicOptionsData(['Center','Left','Right']),
                'placeholder'=> '', 
                'cssClasses' => $this->checkboxCss('col-md-2','control-label', 'check-list-radio', ''),
                'isClearfix' => TRUE,
            ],
            [
               'id' => 10, 
               'value' => trans('Setup::StaticDataConfigMessage.systemic_examination_inspection_trail_sign'),
               'type'=>1, 
               'name'       => 'trail_sign', 
                'input_type' => 'customcheckbox', 
                'input_type_option' => $this->getDynamicOptionsData(['Present','Absent']),
                'placeholder'=> '', 
                'cssClasses' => $this->checkboxCss('col-md-2','control-label', 'check-list-radio', ''),
                'isClearfix' => FALSE,
            ],
            [
               'id' => 11, 
               'value' => trans('Setup::StaticDataConfigMessage.systemic_examination_inspection_droop'),
               'type'=>1, 
               'input_type' => 'text' ,
               'input_type_option'=>'',
               'placeholder' =>'',
               'cssClasses' => $this->textBoxCss('col-md-2', 'form-group', 'check-list-', ''),
               'isClearfix' => FALSE,
            ],
            [
               'id' => 12, 
               'value' => trans('Setup::StaticDataConfigMessage.systemic_examination_inspection_wasting'),
               'type'=>1, 
               'input_type' => 'text' ,
               'input_type_option'=>'',
               'placeholder' =>'',
               'cssClasses' => $this->textBoxCss('col-md-3', 'form-group', 'check-list-', ''),
               'isClearfix' => FALSE,
            ],
            [
               'id' => 13, 
               'value' => trans('Setup::StaticDataConfigMessage.systemic_examination_inspection_nipple_position'),
               'type'=>1, 
               'input_type' => 'text' ,
               'input_type_option'=>'',
               'placeholder' =>'',
               'cssClasses' => $this->textBoxCss('col-md-2', 'form-group', 'check-list-', ''),
               'isClearfix' => FALSE,
            ],
            [
               'id' => 14, 
               'value' => trans('Setup::StaticDataConfigMessage.systemic_examination_inspection_spino_scapular_distance'),
               'type'=>1, 
               'input_type' => 'text' ,
               'input_type_option'=>'',
               'placeholder' =>'',
               'cssClasses' => $this->textBoxCss('col-md-3', 'form-group', 'check-list-', ''),
               'isClearfix' => TRUE,
            ],
            [
               'id' => 15, 
               'value' => trans('Setup::StaticDataConfigMessage.systemic_examination_inspection_rib_crowding'),
               'type'=>1, 
               'input_type' => 'text' ,
               'input_type_option'=>'',
               'placeholder' =>'',
               'cssClasses' => $this->textBoxCss('col-md-2', 'form-group', 'check-list-', ''),
               'isClearfix' => FALSE,
            ],
            [
               'id' => 16, 
               'value' => trans('Setup::StaticDataConfigMessage.systemic_examination_inspection_intercostal_spaces'),
               'type'=>1, 
               'input_type' => 'customcheckbox',
                'input_type_option'=>$this->covertToOptionlabel($this->getYesNoData()),
                'placeholder' =>'',
                'cssClasses' => $this->checkboxCss('col-md-2','control-label', 'check-list-radio', ''),
                'isClearfix' => FALSE,
            ],
            [
               'id' => 17, 
               'value' => trans('Setup::StaticDataConfigMessage.systemic_examination_inspection_intercostal_indrawing'),
               'type'=>1, 
               'input_type' => 'customcheckbox',
                'input_type_option'=>$this->covertToOptionlabel($this->getYesNoData()),
                'placeholder' =>'',
                'cssClasses' => $this->checkboxCss('col-md-2','control-label', 'check-list-radio', ''),
                'isClearfix' => FALSE,
            ],
            [
               'id' => 18, 
               'value' => trans('Setup::StaticDataConfigMessage.systemic_examination_inspection_intercostal_hoovers'),
               'type'=>1, 
               'input_type' => 'customcheckbox',
                'input_type_option'=>$this->covertToOptionlabel($this->getYesNoData()),
                'placeholder' =>'',
                'cssClasses' => $this->checkboxCss('col-md-2','control-label', 'check-list-radio', ''),
                'isClearfix' => FALSE,
            ],
            [
               'id' => 17, 
               'value' => trans('Setup::StaticDataConfigMessage.systemic_examination_inspection_respiratory_paradox'),
               'type'=>1, 
               'input_type' => 'text' ,
               'input_type_option'=>'',
               'placeholder' =>'',
               'cssClasses' => $this->textBoxCss('col-md-2', 'form-group', 'check-list-', ''),
               'isClearfix' => FALSE,
            ],
            [
               'id' => 18, 
               'value' => trans('Setup::StaticDataConfigMessage.systemic_examination_inspection_respiratory_alternans'),
               'type'=>1, 
               'input_type' => 'text' ,
               'input_type_option'=>'',
               'placeholder' =>'',
               'cssClasses' => $this->textBoxCss('col-md-2', 'form-group', 'check-list-', ''),
               'isClearfix' => FALSE,
            ],
            [
               'id' => 19, 
               'value' => trans('Setup::StaticDataConfigMessage.systemic_examination_inspection_accessary_muscles'),
               'type'=>1, 
               'input_type' => 'customcheckbox',
                'input_type_option'=>$this->covertToOptionlabel($this->getYesNoData()),
                'placeholder' =>'',
                'cssClasses' => $this->checkboxCss('col-md-3','control-label', 'check-list-radio', ''),
                'isClearfix' => TRUE,
            ],
            [
               'id' => 20, 
               'value' => trans('Setup::StaticDataConfigMessage.systemic_examination_inspection_apical_impulse'),
               'type'=>1, 
               'input_type' => 'customcheckbox',
                'input_type_option'=>$this->getDynamicOptionsData(['Present','Absent']),
               'placeholder' =>'',
               'handlers'  => 'showHide',
               'field_name'  => 'inspection_apical_impulse',
               'cssClasses' => $this->textBoxCss('col-md-2', 'form-group', 'check-list-', ''),
               'isClearfix' => FALSE,
            ],
            [
                'id'        => 108, 
                'value'     => trans('Setup::StaticDataConfigMessage.systemic_examination_inspection_location'),
                'type'      => 1, 
                'name'      => 'physical_examination_fector', 
                'placeholder' => 'Location', 
                'input_type'  => 'text', 
                'input_type_option'     => '',
                'show_hide_trigger' => 'inspection_apical_impulse',
                'cssClasses' => $this->textBoxCss('col-md-2','form-group','',''),
                'isClearfix' => FALSE,
            ],
            [
               'id' => 21, 
               'value' => trans('Setup::StaticDataConfigMessage.systemic_examination_inspection_dilated_veins'),
               'type'=>1, 
               'input_type' => 'customcheckbox',
                'input_type_option'=>$this->covertToOptionlabel($this->getYesNoData()),
                'placeholder' =>'',
                'cssClasses' => $this->checkboxCss('col-md-2','control-label', 'check-list-radio', ''),
                'isClearfix' => FALSE,
            ],
            [
               'id' => 22, 
               'value' => trans('Setup::StaticDataConfigMessage.systemic_examination_inspection_scars'),
               'type'=>1, 
               'input_type' => 'customcheckbox',
                'input_type_option'=>$this->covertToOptionlabel($this->getYesNoData()),
                'placeholder' =>'',
                'cssClasses' => $this->checkboxCss('col-md-2','control-label', 'check-list-radio', ''),
                'isClearfix' => FALSE,
            ],
            [
               'id' => 23, 
               'value' => trans('Setup::StaticDataConfigMessage.systemic_examination_inspection_sinuses'),
               'type'=>1, 
               'input_type' => 'customcheckbox',
                'input_type_option'=>$this->covertToOptionlabel($this->getYesNoData()),
                'placeholder' =>'',
                'cssClasses' => $this->checkboxCss('col-md-2','control-label', 'check-list-radio', ''),
                'isClearfix' => FALSE,
            ],
            [
               'id' => 24, 
               'value' => trans('Setup::StaticDataConfigMessage.systemic_examination_inspection_visible_pulsations'),
               'type'=>1, 
               'input_type' => 'customcheckbox',
                'input_type_option'=>$this->covertToOptionlabel($this->getYesNoData()),
                'placeholder' =>'',
                'cssClasses' => $this->checkboxCss('col-md-2','control-label', 'check-list-radio', ''),
                'isClearfix' => FALSE,
            ],
            [
               'id' => 25, 
               'value' => trans('Setup::StaticDataConfigMessage.systemic_examination_inspection_skin'),
               'type'=>1, 
               'input_type' => 'text' ,
               'input_type_option'=>'',
               'placeholder' =>'',
               'cssClasses' => $this->textBoxCss('col-md-2', 'form-group', 'check-list-', ''),
               'isClearfix' => TRUE,
            ],
            [
                'id'        => 1003, 
                'value'     => trans('Setup::StaticDataConfigMessage.systemic_examination_heading_palpation'),
                'type'      => 1, 
                'name'      => 'palpation',
                'input_type' => 'groupHeading',
                'input_type_option' => '', 
                'cssClasses' => ['col-md-12'],
                'isClearfix' => TRUE
            ],
            [
               'id' => 26, 
               'value' => trans('Setup::StaticDataConfigMessage.systemic_examination_palpation_inspiratory_findings'),
               'type'=>1,
               'input_type' => 'text',
               'input_type_option'=>'',
               'placeholder' =>'',
               'cssClasses' => $this->textBoxCss('col-md-2', 'form-group', 'check-list-', ''),
               'isClearfix' => FALSE,
            ],
            [
               'id' => 27, 
               'value' => trans('Setup::StaticDataConfigMessage.systemic_examination_palpation_tracheal_position'),
               'type'=>1, 
               'input_type' => 'select', 
            'input_type_option' => $this->getDynamicOptionsData(['Central','Right','Left']),
               'placeholder' =>'',
               'cssClasses' => $this->textBoxCss('col-md-2', 'form-group', 'check-list-', ''),
               'isClearfix' => FALSE,
            ],
            [
                'id' => 28, 
                'value' => trans('Setup::StaticDataConfigMessage.systemic_examination_palpation_apical_impulse'),
                'type'=>1, 
                'input_type' => 'customcheckbox',
                'input_type_option'=>$this->getDynamicOptionsData(['Present','Absent']),
                'placeholder' =>'',
                'handlers'  => 'showHide',
                'field_name'  => 'palpation_apical_impulse',
                'cssClasses' => $this->checkboxCss('col-md-2','control-label', 'check-list-radio', ''),
                'isClearfix' => FALSE,
            ],
            [
                'id'        => 109, 
                'value'     => trans('Setup::StaticDataConfigMessage.systemic_examination_palpation_location'),
                'type'      => 1, 
                'name'      => 'physical_examination_fector', 
                'placeholder' => 'Location', 
                'input_type'  => 'text', 
                'input_type_option'     => '',
                'show_hide_trigger' => 'palpation_apical_impulse',
                'cssClasses' => $this->textBoxCss('col-md-2','form-group','',''),
                'isClearfix' => FALSE,
            ],
            [
               'id' => 29, 
               'value' => trans('Setup::StaticDataConfigMessage.systemic_examination_palpation_rib_crowding'),
               'type'=>1, 
               'input_type' => 'text' ,
               'input_type_option'=>'',
               'placeholder' =>'',
               'cssClasses' => $this->textBoxCss('col-md-2', 'form-group', 'check-list-', ''),
               'isClearfix' => FALSE,
            ],
            [
               'id' => 30, 
               'value' => trans('Setup::StaticDataConfigMessage.systemic_examination_palpation_chest_wall_tenderness'),
               'type'=>1, 
               'input_type' => 'text' ,
               'input_type_option'=>'',
               'placeholder' =>'',
               'cssClasses' => $this->textBoxCss('col-md-2', 'form-group', 'check-list-', ''),
               'isClearfix' => TRUE,
            ],
            [
               'id' => 31, 
               'value' => trans('Setup::StaticDataConfigMessage.systemic_examination_palpation_crepitus'),
               'type'=>1, 
               'input_type' => 'text' ,
               'input_type_option'=>'',
               'placeholder' =>'',
               'cssClasses' => $this->textBoxCss('col-md-2', 'form-group', 'check-list-', ''),
               'isClearfix' => FALSE,
            ],
            [
               'id' => 32, 
               'value' => trans('Setup::StaticDataConfigMessage.systemic_examination_palpation_sc_emphysema'),
               'type'=>1, 
               'input_type' => 'text' ,
               'input_type_option'=>'',
               'placeholder' =>'',
               'cssClasses' => $this->textBoxCss('col-md-2', 'form-group', 'check-list-', ''),
               'isClearfix' => FALSE,
            ],
            [
               'id' => 33, 
               'value' => trans('Setup::StaticDataConfigMessage.systemic_examination_palpation_harrisons_sulcus'),
               'type'=>1, 
               'input_type' => 'text' ,
               'input_type_option'=>'',
               'placeholder' =>'',
               'cssClasses' => $this->textBoxCss('col-md-2', 'form-group', 'check-list-', ''),
               'isClearfix' => FALSE,
            ],
            [
               'id' => 34, 
               'value' => trans('Setup::StaticDataConfigMessage.systemic_examination_palpation_hoovers_sign'),
               'type'=>1, 
               'input_type' => 'text' ,
               'input_type_option'=>'',
               'placeholder' =>'',
               'cssClasses' => $this->textBoxCss('col-md-2', 'form-group', 'check-list-', ''),
               'isClearfix' => TRUE,
            ],
            [
                'id'        => 1004, 
                'value'     => trans('Setup::StaticDataConfigMessage.systemic_examination_heading_cardio_vascular'),
                'type'      => 1, 
                'name'      => 'cardio_vascular',
                'input_type' => 'groupHeading',
                'input_type_option' => '', 
                'cssClasses' => ['col-md-12'],
                'isClearfix' => TRUE
            ],
            [
                'id' => 35, 
                'value' => trans('Setup::StaticDataConfigMessage.systemic_examination_cv_apical_impulse'),
                'type'=>1, 
                'input_type' => 'customcheckbox',
                'input_type_option'=>$this->getDynamicOptionsData(['Present','Absent']),
                'placeholder' =>'',
                'cssClasses' => $this->checkboxCss('col-md-2','control-label', 'check-list-radio', ''),
                'isClearfix' => FALSE,
            ],
            [
               'id' => 36, 
               'value' => trans('Setup::StaticDataConfigMessage.systemic_examination_cv_precardial_buldge'),
               'type'=>1, 
               'input_type' => 'text' ,
               'input_type_option'=>'',
               'placeholder' =>'',
               'cssClasses' => $this->textBoxCss('col-md-2', 'form-group', 'check-list-', ''),
               'isClearfix' => FALSE,
            ],
            [
               'id' => 37, 
               'value' => trans('Setup::StaticDataConfigMessage.systemic_examination_cv_parasternal_heave'),
               'type'=>1, 
               'input_type' => 'text' ,
               'input_type_option'=>'',
               'placeholder' =>'',
               'cssClasses' => $this->textBoxCss('col-md-2', 'form-group', 'check-list-', ''),
               'isClearfix' => FALSE,
            ],
            [
               'id' => 38, 
               'value' => trans('Setup::StaticDataConfigMessage.systemic_examination_cv_palpable_sounds'),
               'type'=>1, 
               'input_type' => 'text' ,
               'input_type_option'=>'',
               'placeholder' =>'',
               'cssClasses' => $this->textBoxCss('col-md-2', 'form-group', 'check-list-', ''),
               'isClearfix' => FALSE,
            ],
            [
               'id' => 39, 
               'value' => trans('Setup::StaticDataConfigMessage.systemic_examination_cv_thrill'),
               'type'=>1, 
               'input_type' => 'text' ,
               'input_type_option'=>'',
               'placeholder' =>'',
               'cssClasses' => $this->textBoxCss('col-md-2', 'form-group', 'check-list-', ''),
               'isClearfix' => TRUE,
            ],
            [
                'id' => 401, 
                'value' => trans('Setup::StaticDataConfigMessage.systemic_examination_cv_s1'),
                'type'=>1, 
                'input_type' => 'customcheckbox',
                'input_type_option'=>$this->getDynamicOptionsData(['Present','Absent']),
                'placeholder' =>'',
                'cssClasses' => $this->checkboxCss('col-md-2','control-label', 'check-list-radio', ''),
                'isClearfix' => FALSE,
            ],
            [
                'id' => 402, 
                'value' => trans('Setup::StaticDataConfigMessage.systemic_examination_cv_s2'),
                'type'=>1, 
                'input_type' => 'customcheckbox',
                'input_type_option'=>$this->getDynamicOptionsData(['Present','Absent']),
                'placeholder' =>'',
                'cssClasses' => $this->checkboxCss('col-md-2','control-label', 'check-list-radio', ''),
                'isClearfix' => FALSE,
            ],
            [
                'id' => 403, 
                'value' => trans('Setup::StaticDataConfigMessage.systemic_examination_cv_s3'),
                'type'=>1, 
                'input_type' => 'customcheckbox',
                'input_type_option'=>$this->getDynamicOptionsData(['Present','Absent']),
                'placeholder' =>'',
                'cssClasses' => $this->checkboxCss('col-md-2','control-label', 'check-list-radio', ''),
                'isClearfix' => FALSE,
            ],
            [
                'id' => 404, 
                'value' => trans('Setup::StaticDataConfigMessage.systemic_examination_cv_s4'),
                'type'=>1, 
                'input_type' => 'customcheckbox',
                'input_type_option'=>$this->getDynamicOptionsData(['Present','Absent']),
                'placeholder' =>'',
                'cssClasses' => $this->checkboxCss('col-md-2','control-label', 'check-list-radio', ''),
                'isClearfix' => TRUE,
            ],
            [
                'id'        => 1005, 
                'value'     => trans('Setup::StaticDataConfigMessage.systemic_examination_heading_abdomen'),
                'type'      => 1, 
                'name'      => 'abdomen',
                'input_type' => 'groupHeading',
                'input_type_option' => '', 
                'cssClasses' => ['col-md-12'],
                'isClearfix' => TRUE
            ],
            [
               'id' => 42, 
               'value' => trans('Setup::StaticDataConfigMessage.systemic_examination_abdomen_symmetry_distension'),
               'type'=>1, 
               'input_type' => 'text' ,
               'input_type_option'=>'',
               'placeholder' =>'',
               'cssClasses' => $this->textBoxCss('col-md-2', 'form-group', 'check-list-', ''),
               'isClearfix' => FALSE,
            ],
            [
               'id' => 43, 
               'value' => trans('Setup::StaticDataConfigMessage.systemic_examination_abdomen_all_quadrants_moves'),
               'type'=>1, 
               'input_type' => 'text' ,
               'input_type_option'=>'',
               'placeholder' =>'',
               'cssClasses' => $this->textBoxCss('col-md-3', 'form-group', 'check-list-', ''),
               'isClearfix' => FALSE,
            ],
            [
               'id' => 44, 
               'value' => trans('Setup::StaticDataConfigMessage.systemic_examination_abdomen_soft'),
               'type'=>1, 
               'input_type' => 'text' ,
               'input_type_option'=>'',
               'placeholder' =>'',
               'cssClasses' => $this->textBoxCss('col-md-2', 'form-group', 'check-list-', ''),
               'isClearfix' => FALSE,
            ],
            [
               'id' => 45, 
               'value' => trans('Setup::StaticDataConfigMessage.systemic_examination_abdomen_tenderness'),
               'type'=>1, 
               'input_type' => 'text' ,
               'input_type_option'=>'',
               'placeholder' =>'',
               'cssClasses' => $this->textBoxCss('col-md-2', 'form-group', 'check-list-', ''),
               'isClearfix' => FALSE,
            ],
            [
               'id' => 46, 
               'value' => trans('Setup::StaticDataConfigMessage.systemic_examination_abdomen_liver'),
               'type'=>1, 
               'input_type' => 'text' ,
               'input_type_option'=>'',
               'placeholder' =>'',
               'cssClasses' => $this->textBoxCss('col-md-2', 'form-group', 'check-list-', ''),
               'isClearfix' => TRUE,
            ],
            [
               'id' => 41, 
               'value' => trans('Setup::StaticDataConfigMessage.systemic_examination_cv_murmurs'),
               'type'=>1, 
               'input_type' => 'text' ,
               'input_type_option'=>'',
               'placeholder' =>'',
               'cssClasses' => $this->textBoxCss('col-md-2', 'form-group', 'check-list-', ''),
               'isClearfix' => TRUE,
            ],
            [
                'id'        => 1006, 
                'value'     => trans('Setup::StaticDataConfigMessage.systemic_examination_heading_central_nervous_system'),
                'type'      => 1, 
                'name'      => 'central_nervous_system',
                'input_type' => 'groupHeading',
                'input_type_option' => '', 
                'cssClasses' => ['col-md-12'],
                'isClearfix' => TRUE
            ],
            [
               'id' => 51, 
               'value' => trans('Setup::StaticDataConfigMessage.systemic_examination_cns_consciousness'),
               'type'=>1, 
               'input_type' => 'text' ,
               'input_type_option'=>'',
               'placeholder' =>'',
               'cssClasses' => $this->textBoxCss('col-md-2', 'form-group', 'check-list-', ''),
               'isClearfix' => FALSE,
            ],
            [
               'id' => 52, 
               'value' => trans('Setup::StaticDataConfigMessage.systemic_examination_cns_orientation'),
               'type'=>1, 
               'input_type' => 'text' ,
               'input_type_option'=>'',
               'placeholder' =>'',
               'cssClasses' => $this->textBoxCss('col-md-2', 'form-group', 'check-list-', ''),
               'isClearfix' => FALSE,
            ],
            [
               'id' => 53, 
               'value' => trans('Setup::StaticDataConfigMessage.systemic_examination_cns_fnd'),
               'type'=>1, 
               'input_type' => 'text' ,
               'input_type_option'=>'',
               'placeholder' =>'',
               'cssClasses' => $this->textBoxCss('col-md-2', 'form-group', 'check-list-', ''),
               'isClearfix' => FALSE,
            ],
            [
               'id' => 54, 
               'value' => trans('Setup::StaticDataConfigMessage.systemic_examination_cns_horners_syndrome'),
               'type'=>1, 
               'input_type' => 'text' ,
               'input_type_option'=>'',
               'placeholder' =>'',
               'cssClasses' => $this->textBoxCss('col-md-2', 'form-group', 'check-list-', ''),

               'isClearfix' => FALSE,
            ],
            [
               'id' => 55, 
               'value' => trans('Setup::StaticDataConfigMessage.systemic_examination_cns_planters'),
               'type'=>1, 
               'input_type' => 'text' ,
               'input_type_option'=>'',
               'placeholder' =>'',
               'cssClasses' => $this->textBoxCss('col-md-2', 'form-group', 'check-list-', ''),
               'isClearfix' => TRUE,
            ]
         ];
        return $staticValue;     
    }

    /**
     * @DateOfCreation        05 Oct 2018
     * @ShortDescription      This function is responsible to get the form data of Investigation sleep study report
     * @return                Array of status and message
     */
    public function getSleepStudyReportForm(){
        $formValue = [
            [
                'id'            => 1, 
                'value'         => trans('Setup::StaticDataConfigMessage.sleep_study_ahi'),
                'type'          => 1, 
                'name'          => 'investigation_ahi', 
                'placeholder'   => '', 
                'field_type'    => 'text', 
                'options'       => $this->covertToOption($this->getYesNoData()),
                'cssClasses' => $this->textBoxCss('col-md-3', 'form-group', 'control-label', ''),
            ],
            [
                'id'            => 2, 
                'value'         => trans('Setup::StaticDataConfigMessage.sleep_study_ri'),
                'type'          => 1, 
                'name'          => 'investigation_ri', 
                'field_type'    => 'text', 
                'placeholder'    =>trans('Setup::StaticDataConfigMessage.visit_6mwt_date'), 
                'options'       => [],
                'cssClasses'    => $this->textBoxCss('col-md-3', 'form-group', 'control-label', ''),
                'isClearfix'    =>true
            ],  
            [
                'id'            => 3, 
                'value'         => trans('Setup::StaticDataConfigMessage.sleep_study_conclusion'),
                'type'          => 1, 
                'name'          => 'investigation_conclusion', 
                'field_type'    => 'textarea', 
                'placeholder'    =>trans('Setup::StaticDataConfigMessage.visit_6mwt_date'), 
                'options'       => [],
                'cssClasses'    => $this->textBoxCss('col-md-6', 'form-group', 'control-label', '')
            ],  
        ];

        return $formValue;
    }

    /**
     * @DateOfCreation        05 Oct 2018
     * @ShortDescription      This function is responsible to get the form data of Investigation report form
     * @return                Array of status and message
     */
    public function getInvestigationReport(){
        $laboratoryTestObj = new LaboratoryTests();

        $getAllReportsType = $laboratoryTestObj->getAllLaboratoryTests();
        $formData = array();
        if(!empty($getAllReportsType)){
            $fieldID = 0;
            foreach($getAllReportsType as $reportTypeData){
                $conditionalClass = ($fieldID % 2) == 0 ? 'description-float' : '';
                
                $type = $this->securityLibObj->decrypt($reportTypeData->mlt_id);
                $data = array(
                            [

                                'id'            => $type, 
                                'value'         => trans('Setup::StaticDataConfigMessage.upload_report').'#'.$reportTypeData->mlt_name,
                                'type'          => $type, 
                                'name'          => 'report_file', 
                                'placeholder'   => trans('Setup::StaticDataConfigMessage.upload_report'), 
                                'field_type'    => 'file', 
                                'options'       => '',
                                'cssClasses' => $this->textBoxCss('col-md-12', 'form-group', '', '', 'yellow lab-report btn text-btn col-md-2'),
                                'isClearfix'    => true,
                                'isFileView'    => true,
                                'fileType'      => 'investigation-report',
                            ],
                            [
                                'id'            => $type, 
                                'value'         => trans('Setup::StaticDataConfigMessage.description_report'),
                                'type'          => $type, 
                                'name'          => 'report_description', 
                                'placeholder'   => '', 
                                'field_type'    => 'textarea', 
                                'options'       => '',
                                'cssClasses'    => $this->textBoxCss('col-md-12', 'form-group', 'control-label', ''),
                            ],
                        );

                $formData = array_merge($formData, $data);
                $fieldID++;
            }
        }
        return $formData;
    }


}
