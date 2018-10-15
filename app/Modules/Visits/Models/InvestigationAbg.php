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
 * InvestigationAbg
 *
 * @package                ILD India Registry
 * @subpackage             InvestigationAbg
 * @category               Model
 * @DateOfCreation         11 june 2018
 * @ShortDescription       This Model to handle database operation of InvestigationAbg
 **/

class InvestigationAbg extends Model {

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
    protected $table         = 'investigation_abg';
    protected $tableJoin     = 'investigation_abg_fector';
    
    // This protected member contains fields that need to encrypt while saving in database
    protected $encryptable = [];
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [ 'pat_id',
                            'visit_id',
                            'abg_date',
                            'resource_type',
                            'ip_address'
                        ];

    protected $fillableJoin = [ 'ia_id',
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
    protected $primaryKey = 'ia_id';

    /**
     * @DateOfCreation        26 June 2018
     * @ShortDescription      This function is responsible to get the patient InvestigationAbg record
     * @param                 integer $visitId,$patientId, $encrypt   
     * @return                object Array of InvestigationAbg records
     */
    public function getInvestigationAbgByVistID($visitId,$patientId = '',$encrypt = true) 
    {       
        $onConditionLeftSide = $this->table.'.ia_id';
        $onConditionRightSide = $this->tableJoin.'.ia_id';
        $queryResult = DB::table($this->table)
            ->leftJoin($this->tableJoin,function($join) use ($onConditionLeftSide,$onConditionRightSide){
                $join->on($onConditionLeftSide, '=', $onConditionRightSide)
                ->where($this->tableJoin.'.is_deleted', '=', Config::get('constants.IS_DELETED_NO'), 'and');
            })
            ->select($this->table.'.pat_id', $this->table.'.visit_id', $this->table.'.abg_date', $this->table.'.ia_id', $this->tableJoin.'.fector_id', $this->tableJoin.'.fector_value', $this->tableJoin.'.iaf_id')
            ->where($this->table.'.visit_id', $visitId)
            ->where($this->table.'.is_deleted', Config::get('constants.IS_DELETED_NO'));
        if(!empty($patientId)){
         $queryResult = $queryResult->where($this->table.'.pat_id', $patientId);
        }
        $queryResult =$queryResult->get();
        if($encrypt && !empty($queryResult)){
            $queryResult = $queryResult->map(function($dataList){ 
                $dataList->ia_id = $this->securityLibObj->encrypt($dataList->ia_id);
                $dataList->pat_id = $this->securityLibObj->encrypt($dataList->pat_id);
                $dataList->visit_id = $this->securityLibObj->encrypt($dataList->visit_id);
                $dataList->fector_id = $this->securityLibObj->encrypt($dataList->fector_id);
                return $dataList;
            });

        }
        return $queryResult;
    }

    /**
     * @DateOfCreation        18 June 2018
     * @ShortDescription      This function is responsible to save record for the symptoms
     * @param                 array $requestData   
     * @return                integer symptoms id
     */
    public function saveAbgInvestigationDate($inserData)
    {
        // @var Boolean $response
        // This variable contains insert query response
        $response = false;

        // @var Array $inserData
        // This Array contains insert data for Patient
        $inserData = $this->utilityLibObj->fillterArrayKey($inserData, $this->fillable);
                
        // Prepair insert query
        $response = $this->dbInsert($this->table, $inserData);
        if($response){
            $id = DB::getPdo()->lastInsertId();
            return $id;
            
        }else{
            return $response;
        }          
    }

    /**
    * @DateOfCreation        27 June 2018
    * @ShortDescription      This function is responsible to update Social Addiction Record
    * @param                 Array  $requestData   
    * @return                Array of status and message
    */
    public function updateAbgInvestigationDate($requestData,$whereData)
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
    * @ShortDescription      This function is responsible to update Social Addiction Record
    * @param                 Array  $requestData   
    * @return                Array of status and message
    */
    public function updateAbgInvestigationFactor($requestData,$whereData)
    {
        $updateData = $this->utilityLibObj->fillterArrayKey($requestData, $this->fillableJoin);
        $response = $this->dbUpdate($this->tableJoin, $updateData, $whereData);
        if($response){
            return true;
        }
        return false;
    }

    /**
    * @DateOfCreation        27 June 2018
    * @ShortDescription      This function is responsible to multiple add Social Addiction Record
    * @param                 Array  $requestData   
    * @return                Array of status and message
    */
    public function addAbgInvestigationFactor($insertData)
    {
        $response = $this->dbBatchInsert($this->tableJoin, $insertData);
        if($response){
            return true;
        }
        return false;
    }

}
