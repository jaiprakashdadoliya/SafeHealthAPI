<?php

namespace App\Modules\DoctorProfile\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Traits\Encryptable;
use App\Libraries\SecurityLib;
use Config;
/**
 * Cities
 *
 * @package                SafeHealth
 * @subpackage             Cities
 * @category               Model
 * @DateOfCreation         18 May 2018
 * @ShortDescription       This class is responsiable for Cities
 */
class Cities extends Model {
    use Encryptable;
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
    protected $primaryKey = 'id';

    /**
     *@ShortDescription Override the Table.
     *
     * @var string
    */
    protected $table = 'cities';

    // @var Array $encryptedFields
    // This protected member contains fields that need to encrypt while saving in database
    protected $encryptable = [];

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct() {
        // Init security library object
        $this->securityLibObj = new SecurityLib();
    }

    /**
     * Get the city by state.
     */
    public function getCityByState($requestData)
    {
        //return DB::table($this->table)->where(['state_id'=> $requestData['state_id']])->get();
        $selectData  =  ['id as city_id', 'name as city_name'];
        $whereData   =  array(
                            'state_id' => $this->securityLibObj->decrypt($requestData['state_id'])
                        ); 
        $queryResult =  $this->dbBatchSelect($this->table, $selectData, $whereData)
                        ->map(function ($cityResult) {
                            $cityResult->city_id = $this->securityLibObj->encrypt($cityResult->city_id);
                            return $cityResult;
                        });
        $queryResult[count($queryResult)] = ['city_id'=>$this->securityLibObj->encrypt(Config::get('constants.OTHER_CITY_ID')), 'city_name'=>Config::get('constants.OTHER_CITY_TEXT')];
        return $queryResult;
    }

   

}
