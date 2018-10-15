<?php
namespace App\Modules\DoctorProfile\Models;
use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\HasApiTokens;
use App\Traits\Encryptable;
use Illuminate\Support\Facades\DB;
use App\Libraries\SecurityLib;
use Config;
use Carbon\Carbon;

/**
 * DoctorTiming
 *
 * @package                 ILD India Registry
 * @subpackage              DoctorTiming
 * @category                Model
 * @DateOfCreation          21 may 2018
 * @ShortDescription        This Model to handle database operation with current table
                            doctors timing
 **/
class Unavailability extends Model {

    use HasApiTokens,Encryptable;

    /**
     * The attributes to declare primary key for the table.
     *
     * @var string
     */
    protected $primaryKey = 'unavailable_id';

    /**
     * The attributes to declare table name to store data.
     *
     * @var string
     */
    protected $table = 'unavailability';

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


}
