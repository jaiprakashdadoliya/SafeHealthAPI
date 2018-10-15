<?php
namespace App\Modules\Region\Models;
use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\HasApiTokens;
use App\Traits\Encryptable;
use Illuminate\Support\Facades\DB;
use App\Libraries\SecurityLib;
use Config;

/**
 * State
 *
 * @package                ILD
 * @subpackage             City
 * @category               Model
 * @DateOfCreation         11 june 2018
 * @ShortDescription       This Model to handle database operation with current table
                           State
 **/
class State extends Model {

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
    }

    /**
    *@ShortDescription Table for the Users.
    *
    * @var String
    */
    protected $table = 'states';
    
    // @var Array $encryptedFields
    // This protected member contains fields that need to encrypt while saving in database
    protected $encryptable = [];
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['id', 'name', 'country_id'];
    /**
     *@ShortDescription Override the primary key.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
    * @DateOfCreation        11 june 2018
    * @ShortDescription      This function is responsible to get the state list
    * @param                 String $countryId   
    * @return                object Array of state records
    */
    public function getStateListByCountryId($countryId)
    {

        $selectData  =  ['id', 'name'];
        $whereData   =  array(
                            'country_id' => $countryId,
                        ); 
        $queryResult =  $this->dbBatchSelect($this->table, $selectData, $whereData)
                        ->map(function ($stateResult) {
                            $stateResult->id = $this->securityLibObj->encrypt($stateResult->id);
                            return $stateResult;
                        });
        return $queryResult;
    }

     

}
