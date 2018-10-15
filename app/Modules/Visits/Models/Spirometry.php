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
 * Spirometry
 *
 * @package                ILD India Registry
 * @subpackage             Spirometry
 * @category               Model
 * @DateOfCreation         11 june 2018
 * @ShortDescription       This Model to handle database operation of spirometry
 **/

class Spirometry extends Model {

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
    protected $table         = 'spirometries';
    protected $tableJoin     = 'spirometry_fectors';
    
    // This protected member contains fields that need to encrypt while saving in database
    protected $encryptable = [];
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [ 'pat_id',
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
     * @DateOfCreation        26 June 2018
     * @ShortDescription      This function is responsible to get the patient spirometry record
     * @param                 integer $visitId,$patientId, $encrypt   
     * @return                object Array of spirometry records
     */
    public function getSpirometryByVistID($visitId,$patientId = '',$encrypt = true) 
    {       
        $onConditionLeftSide = $this->table.'.spirometry_id';
        $onConditionRightSide = $this->tableJoin.'.spirometry_id';
        $queryResult = DB::table($this->table)
           ->leftJoin($this->tableJoin,function($join) use ($onConditionLeftSide,$onConditionRightSide){
                                $join->on($onConditionLeftSide, '=', $onConditionRightSide)
                                ->where($this->tableJoin.'.is_deleted', '=', Config::get('constants.IS_DELETED_NO'), 'and');
                            })
            ->select($this->table.'.pat_id', $this->table.'.visit_id', $this->table.'.spirometry_date', $this->table.'.spirometry_id', $this->tableJoin.'.fector_id', $this->tableJoin.'.fector_value', $this->tableJoin.'.fector_pre_value', $this->tableJoin.'.fector_post_value')
            ->where($this->table.'.visit_id', $visitId)
            ->where($this->table.'.is_deleted', Config::get('constants.IS_DELETED_NO'));
        if(!empty($patientId)){
         $queryResult = $queryResult->where($this->table.'.pat_id', $patientId);
        }
        $queryResult =$queryResult->get();
        if($encrypt && !empty($queryResult)){
            $queryResult = $queryResult->map(function($dataList){ 
                $dataList->spirometry_id = $this->securityLibObj->encrypt($dataList->spirometry_id);
                $dataList->pat_id = $this->securityLibObj->encrypt($dataList->pat_id);
                $dataList->visit_id = $this->securityLibObj->encrypt($dataList->visit_id);
                $dataList->fector_id = $this->securityLibObj->encrypt($dataList->fector_id);
                return $dataList;
            });

        }
        return $queryResult;
    }
}
