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
 * FamilyMedicalHistory
 *
 * @package                ILD India Registry
 * @subpackage             FamilyMedicalHistory
 * @category               Model
 * @DateOfCreation         11 june 2018
 * @ShortDescription       This Model to handle database operation of medical history
 **/

class FamilyMedicalHistory extends Model {

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
    protected $table         = 'family_medical_histories';
    protected $tableDiseases = 'diseases';
    
    // This protected member contains fields that need to encrypt while saving in database
    protected $encryptable = [];
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [ 'pat_id', 
                            'visit_id',
                            'fmh_disease_id',
                            'disease_status',
                            'family_relation',
                            'resource_type',
                            'ip_address'
                        ];

    /**
     *@ShortDescription Override the primary key.
     *
     * @var string
     */
    protected $primaryKey = 'fmh_id';

    /**
     * @DateOfCreation        26 June 2018
     * @ShortDescription      This function is responsible to get the patient Medical History record
     * @param                 integer $visitId   
     * @return                object Array of medical history records
     */
    public function getFamilyMedicalHistory($visitId,$type =[]) 
    {       
        $type = !is_array($type) && !empty($type) ? explode(',', $type) : $type;
        $msgString = '';
        if(count($type)>0){
            $msg = [];
            foreach ($type as $value) {
               $msg[]= " ? = ANY (string_to_array(is_show_in_type,',')) ";
            }
            $msgString = implode(' OR ', $msg);

        }

        $queryResult = DB::table( $this->tableDiseases )
                        ->select( $this->tableDiseases.'.disease_name',
                                $this->tableDiseases.'.disease_id',
                                $this->tableDiseases.'.is_show_in_type',
                                $this->table.'.fmh_id', 
                                $this->table.'.pat_id', 
                                $this->table.'.disease_status', 
                                $this->table.'.family_relation', 
                                $this->table.'.visit_id', 
                                $this->table.'.is_deleted'
                            ) 
                        ->leftJoin($this->table,function($join) use ($visitId){
                                $join->on($this->table.'.fmh_disease_id', '=', $this->tableDiseases.'.disease_id')
                                ->where($this->table.'.visit_id', '=', $visitId, 'and')
                                ->where($this->table.'.is_deleted', '=', Config::get('constants.IS_DELETED_NO'), 'and');
                            })
                        ->where( $this->tableDiseases.'.is_deleted',  Config::get('constants.IS_DELETED_NO') );
                        if(!empty($msgString)){
                         $queryResult =   $queryResult->whereRaw("( ".$msgString." )",$type);
                        }
                        
               
        $queryResult = $queryResult->get()
                                    ->map(function($medicalHistory){
                                        if(!empty($medicalHistory->fmh_id)){
                                            $medicalHistory->fmh_id = $this->securityLibObj->encrypt($medicalHistory->fmh_id);                                            
                                        }
                                        return $medicalHistory;
                                    });
        return $queryResult;
    }

    public function getMedicalHistoryDisease(){
        $queryResult = DB::table( $this->tableDiseases )
                        ->select( 'disease_name', 'disease_id') 
                        ->where( 'is_deleted',  Config::get('constants.IS_DELETED_NO') )
                        ->where( 'is_show_in_type',  Config::get('constants.IS_SHOW_IN_TYPE_MEDICAL_HISTORY') )
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
            ->select( 'fmh_id' ) 
            ->where('is_deleted', Config::get('constants.IS_DELETED_NO'))
            ->where('visit_id', $vistId)
            ->where('fmh_disease_id', $diseaseId);
               
        return $queryResult->get()->count();
    }

    /**
    * @DateOfCreation        27 June 2018
    * @ShortDescription      This function is responsible to update family medical history Record
    * @param                 Array  $requestData   
    * @return                Array of status and message
    */
    public function updateFamilyMedicalHistory($requestData,$whereData)
    {
        $updateData = $this->utilityLibObj->fillterArrayKey($requestData, $this->fillable);
        $response = $this->dbUpdate($this->table, $updateData, $whereData);
        if($response){
            return true;
        }
        return false;
    }

    /**
    * @DateOfCreation        27 June 2018
    * @ShortDescription      This function is responsible to multiple add family medical history Record
    * @param                 Array  $requestData   
    * @return                Array of status and message
    */
    public function addFamilyMedicalHistory($insertData)
    {
        $response = $this->dbBatchInsert($this->table, $insertData);
        if($response){
            return true;
        }
        return false;
    }

    public function getFamilyMedicalInShowType(){
        $data = [Config::get('constants.IS_SHOW_IN_TYPE_FAMILY_MEDICAL_HISTORY_PART_1'),Config::get('constants.IS_SHOW_IN_TYPE_FAMILY_MEDICAL_HISTORY_PART_2'),Config::get('constants.IS_SHOW_IN_TYPE_FAMILY_MEDICAL_HISTORY_PART_3')];
        return $data;
    }
}
