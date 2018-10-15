<?php
namespace App\Modules\Region\Models;
use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\HasApiTokens;
use App\Traits\Encryptable;
use Illuminate\Support\Facades\DB;
use App\Libraries\SecurityLib;
use Config;

/**
 * City
 *
 * @package                ILD
 * @subpackage             City
 * @category               Model
 * @DateOfCreation         11 june 2018
 * @ShortDescription       This Model to handle database operation with current table
                           City
 **/
class City extends Model {

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
    protected $table = 'cities';
    
    // @var Array $encryptedFields
    // This protected member contains fields that need to encrypt while saving in database
    protected $encryptable = [];
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['id', 'name', 'state_id'];

    /**
     *@ShortDescription Override the primary key.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
    * @DateOfCreation        30 May 2018
    * @ShortDescription      This function is responsible to get the city list
    * @param                 String $stateId   
    * @return                object Array of city records
    */
    public function getCityListByStateId($stateId)
    {

        $selectData  =  ['id', 'name'];
        $whereData   =  array(
                            'state_id' => $stateId,
                        ); 
        $queryResult =  $this->dbBatchSelect($this->table, $selectData, $whereData)
                        ->map(function ($cityResult) {
                            $cityResult->id = $this->securityLibObj->encrypt($cityResult->id);
                            return $cityResult;
                        });
        $queryResult[count($queryResult)] = ['id'=>$this->securityLibObj->encrypt(Config::get('constants.OTHER_CITY_ID')), 'name'=>Config::get('constants.OTHER_CITY_TEXT')];
        return $queryResult;
    }

     

}
