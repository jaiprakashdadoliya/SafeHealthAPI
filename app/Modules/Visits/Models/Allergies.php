<?php
namespace App\Modules\Visits\Models;
use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\HasApiTokens;
use App\Traits\Encryptable;
use Illuminate\Support\Facades\DB;
use App\Libraries\SecurityLib;
use Config;
use App\Libraries\UtilityLib;

/**
 * Allergies
 *
 * @package                Safe health
 * @subpackage             Allergies
 * @category               Model
 * @DateOfCreation         8 Oct 2018
 * @ShortDescription       This Model to handle database operation with current table
                           City
 **/
class Allergies extends Model {

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
    protected $table = 'allergies';
    
    // @var Array $encryptedFields
    // This protected member contains fields that need to encrypt while saving in database
    protected $encryptable = [];
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['parent_id', 'allergy_name','status','resource_type','ip_address'];

    /**
     *@ShortDescription Override the primary key.
     *
     * @var string
     */
    protected $primaryKey = 'allergy_id';

    /**
     * @DateOfCreation        08 Oct 2018
     * @ShortDescription      This function is responsible to save record for the Patient Medication History
     * @param                 array $requestData   
     * @return                integer Patient Medication History id
     */
    public function getTableName()
    {
        return $this->table;
    }

    /**
     * @DateOfCreation        08 Oct 2018
     * @ShortDescription      This function is responsible to save record for the Patient Medication History
     * @param                 array $requestData   
     * @return                integer Patient Medication History id
     */
    public function getTablePrimaryIdColumn()
    {
        return $this->primaryKey;
    }

    /**
     * @DateOfCreation        8 Oct 2018
     * @ShortDescription      This function is responsible to get allergies data
     * @param                 integer $whereData   
     * @return                Array of list data
     */
    public function getAllergiesList($whereData = NULL)
    {
        $query = DB::table($this->table)
                    ->select($this->table.'.allergy_id', 
                            $this->table.'.parent_id',
                            $this->table.'.allergy_name'
                        )
                    ->where($this->table.'.is_deleted', Config::get('constants.IS_DELETED_NO'));

        if(!empty($whereData)){
            $query->where($whereData);
        }

        $resultData = $query->get()
                            ->map(function($allergiesList) {
                                $allergiesList->allergy_id = $this->securityLibObj->encrypt($allergiesList->allergy_id);
                                $allergiesList->parent_id  = $this->securityLibObj->encrypt($allergiesList->parent_id);

                                return $allergiesList;
                            });
        return $resultData;
    }
}
