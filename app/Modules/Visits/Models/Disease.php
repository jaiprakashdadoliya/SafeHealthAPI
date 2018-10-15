<?php

namespace App\Modules\Visits\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\HasApiTokens;
use App\Traits\Encryptable;
use App\Libraries\SecurityLib;
use Config;
use App\Libraries\UtilityLib;
use DB;
use App\Modules\Setup\Models\StaticDataConfig as StaticData;

/**
 * Disease
 *
 * @package                ILD India Registry
 * @subpackage             Disease
 * @category               Model
 * @DateOfCreation         11 june 2018
 * @ShortDescription       This Model to handle database for Disease table and relative
 **/
class Disease extends Model {

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

        // Init General staticData Model Object
        $this->staticDataObj = new StaticData();
    }

    /**
    *@ShortDescription Table for the Users.
    *
    * @var String
    */
    protected $tableDiseases         = 'diseases';
    protected $tableDiseaseExtraInfo = 'disease_extra_information';
    
    // This protected member contains fields that need to encrypt while saving in database
    protected $encryptable = [];
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [ 'disease_name', 
                            'resource_type',
                            'ip_address'
                        ];

    /**
     *@ShortDescription Override the primary key.
     *
     * @var string
     */
    protected $primaryKey = 'disease_id';

    

}
