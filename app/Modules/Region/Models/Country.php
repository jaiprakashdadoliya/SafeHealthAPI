<?php
namespace App\Modules\Region\Models;
use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\HasApiTokens;
use App\Traits\Encryptable;
use Illuminate\Support\Facades\DB;
use App\Libraries\SecurityLib;
use Config;

/**
 * Country
 *
 * @package                ILD
 * @subpackage             Country
 * @category               Model
 * @DateOfCreation         11 June 2018
 * @ShortDescription       This Model to handle database operation with current table
                           Country
 **/
class Country extends Model {

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
    protected $table = 'country';
    
    // @var Array $encryptedFields
    // This protected member contains fields that need to encrypt while saving in database
    protected $encryptable = [];
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'country_id', 'country_name', 'country_code'
    ];

    /**
     *@ShortDescription Override the primary key.
     *
     * @var string
     */
    protected $primaryKey = 'country_id';

    /**
    * @DateOfCreation        30 May 2018
    * @ShortDescription      This function is responsible to get the country list
    * @return                object Array of country records
    */
    public function getCountryList()
    {
        $queryResult = DB::table($this->table)
                   ->select('country_id', 'country_code','country_name')
                   ->get()
                   ->map(function ($countryResult) {
                        $countryResult->country_id = $this->securityLibObj->encrypt($countryResult->country_id);
                        return $countryResult;
                    });
        return $queryResult;
    }

    /**
    * @DateOfCreation        28 June 2018
    * @ShortDescription      This function is responsible to get the country details by state id
    * @return                object Array of country records
    */
    public function getCountryDetailsByStateId($stateId = 0){
        $countryDetails = DB::table('states')
                        ->select([$this->table.'.country_code',$this->table.'.country_name',$this->table.'.country_id'])
                        ->join($this->table,'states.country_id','=',$this->table.'.country_id')
                        ->where(['states.id' => $stateId])
                        ->first();
        return $countryDetails;
    }    

     

}
