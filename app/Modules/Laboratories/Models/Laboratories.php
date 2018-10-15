<?php

namespace App\Modules\Laboratories\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Traits\Encryptable;
use App\Libraries\SecurityLib;
use Config;

class Laboratories extends Model {
	use Encryptable;
	// @var string $table
    // This protected member contains table name
    protected $table = 'laboratories';

    // @var string $primaryKey
    // This protected member contains primary key
    protected $primaryKey = 'lab_id';

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
    * @DateOfCreation        29 Apr 2018
    * @ShortDescription      This function is responsible for creating new clinic in DB
    * @param                 Array $data This contains full user input data 
    * @return                True/False
    */
    public function createLaboratory($data, $userId)
    {
        // @var Boolean $response
        // This variable contains insert query response
        $response = false;

        // @var Array $inserData
        // This Array contains insert data for users
        $inserData = array(
            'user_id'               => $userId,
            'resource_type'         => $data['resource_type'],
            'ip_address'            => $data['ip_address'],
            'created_by'            => 0,
            'updated_by'            => 0
        );

        // Prepair insert query
        $response = DB::table($this->table)->insert(
                        $inserData
                    );
        return $response;
    }

}
