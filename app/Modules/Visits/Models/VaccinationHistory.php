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
 * VaccinationHistory
 *
 * @package                Safe Health
 * @subpackage             VaccinationHistory
 * @category               Model
 * @DateOfCreation         21 Sept 2018
 * @ShortDescription       This Model to handle database operation of Vaccination History
 **/

class VaccinationHistory extends Model {

    use HasApiTokens, Encryptable;

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
    protected $table = 'patient_vaccination_history';
    
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
                            'vaccine_name',
                            'vaccine_date',
                            'ip_address',
                            'resource_type',
                            'is_deleted',
                        ];

    /**
     *@ShortDescription Override the primary key.
     *
     * @var string
     */
    protected $primaryKey = 'vaccination_id';

    /**
     * @DateOfCreation        21 Sept 2018
     * @ShortDescription      This function is responsible to get Clinical Notes list
     * @param                 
     * @return                object Array of all medicines
     */
    public function getVaccinationHistoryData($requestData) 
    {   
        $query = DB::table( $this->table )
                        ->select( 
                                'vaccination_id',
                                'vaccine_name',
                                'vaccine_date',
                                'pat_id'
                            ) 
                        ->where( 'is_deleted', Config::get('constants.IS_DELETED_NO') )
                        ->where( 'pat_id', $this->securityLibObj->decrypt($requestData['patId']) );

        /* Condition for Filtering the result */
        if(!empty($requestData['filtered'])){
            foreach ($requestData['filtered'] as $key => $value) {
                $query = $query->where(function ($query) use ($value){
                            $query
                            ->where('vaccine_name', 'ilike', "%".$value['value']."%")
                            ->orWhere(DB::raw('CAST(vaccine_date AS TEXT)'), 'ilike', '%'.$value['value'].'%');
                        });
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
                                ->map(function($historyData){
                                        $historyData->vaccination_id = $this->securityLibObj->encrypt($historyData->vaccination_id);
                                        $historyData->pat_id         = $this->securityLibObj->encrypt($historyData->pat_id);

                                        return $historyData;
                                    });
        return $queryResult;
    }

    /**
    * @DateOfCreation        21 Sept 2018
    * @ShortDescription      This function is responsible to add Vaccination History Record
    * @param                 Array  $requestData   
    * @return                Array of status and message
    */
    public function saveVaccinationHistoryData($requestData)
    {
        $response  = $this->dbInsert($this->table, $requestData);

        if($response){
            $id = DB::getPdo()->lastInsertId();
            return $id;
            
        }else{
            return $response;
        }
    }

    /**
    * @DateOfCreation        21 Sept 2018
    * @ShortDescription      This function is responsible to update Vaccination History data
    * @param                 Array  $requestData   
    * @return                Array of status and message
    */
    public function updateClinicalNotesData($requestData, $vaccinationId)
    {
        $whereData = [ 'vaccination_id' => $vaccinationId ];
        
        // Prepare update query
        $response = $this->dbUpdate($this->table, $requestData, $whereData);
        
        if($response){
            return true;
        }
        return false;
    }
}
