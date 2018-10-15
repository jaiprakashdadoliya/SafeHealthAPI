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
 * ClinicalNotes
 *
 * @package                Safe Health
 * @subpackage             ClinicalNotes
 * @category               Model
 * @DateOfCreation         21 Aug 2018
 * @ShortDescription       This Model to handle database operation of Clinical Notes
 **/

class ClinicalNotes extends Model {

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
    protected $table          = 'clinical_notes';
    
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
                            'clinical_notes',
                            'ip_address',
                            'resource_type',
                            'is_deleted',
                        ];

    /**
     *@ShortDescription Override the primary key.
     *
     * @var string
     */
    protected $primaryKey = 'clinical_notes_id';

    /**
     * @DateOfCreation        21 Aug 2018
     * @ShortDescription      This function is responsible to get Clinical Notes list
     * @param                 
     * @return                object Array of all medicines
     */
    public function getClinicalNotesListData($requestData) 
    {   
        
        $queryResult = DB::table( $this->table )
                        ->select( 
                                'clinical_notes_id',
                                'clinical_notes'
                            ) 
                        ->where([ 'is_deleted' => Config::get('constants.IS_DELETED_NO'),
                                  'pat_id' => $this->securityLibObj->decrypt($requestData['pat_id']),
                                  'visit_id' => $this->securityLibObj->decrypt($requestData['visit_id']) 
                        ])->first();
               
        if(!empty($queryResult)){
            $queryResult->clinical_notes_id = $this->securityLibObj->encrypt($queryResult->clinical_notes_id);
            $queryResult->clinical_notes    = !empty($queryResult->clinical_notes) ? json_decode($queryResult->clinical_notes) : [];
        }
            return $queryResult;
                                   
    }

    /**
    * @DateOfCreation        21 Aug 2018
    * @ShortDescription      This function is responsible to update Clinical Notes Record
    * @param                 Array  $requestData   
    * @return                Array of status and message
    */
    public function saveClinicalNotesData($requestData)
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
    * @DateOfCreation        14 July 2018
    * @ShortDescription      This function is responsible to update Clinical Notes data
    * @param                 Array  $requestData   
    * @return                Array of status and message
    */
    public function updateClinicalNotesData($requestData, $clinicalNotesId)
    {
        $whereData = [ 'clinical_notes_id' => $clinicalNotesId ];
        
        // Prepare update query
        $response = $this->dbUpdate($this->table, $requestData, $whereData);
        
        if($response){
            return true;
        }
        return false;
    }
}
