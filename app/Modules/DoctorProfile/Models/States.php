<?php

namespace App\Modules\DoctorProfile\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Traits\Encryptable;
use App\Libraries\SecurityLib;
/**
 * States
 *
 * @package                SafeHealth
 * @subpackage             States
 * @category               Model
 * @DateOfCreation         18 May 2018
 * @ShortDescription       This class is responsiable for States
 */
class States extends Model {
    use Encryptable;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [];

    // @var Array $encryptedFields
    // This protected member contains fields that need to encrypt while saving in database
    protected $encryptable = [];

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
    protected $table = 'states';
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
     * Get the all states.
     */
    public function getAllStates()
    {
        return  DB::table($this->table)->get()
                ->map(function ($stateResult) {
                    $stateResult->state_id = $this->securityLibObj->encrypt($stateResult->id);
                return $stateResult;
        });
    }
}
