<?php

namespace App\Modules\DoctorProfile\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Passport\HasApiTokens;
use App\Traits\Encryptable;
use Illuminate\Support\Facades\DB;
use App\Libraries\SecurityLib;
use Config;
/**
 * DoctorMedia Class
 *
 * @package                Safe Health
 * @subpackage             Doctor Media
 * @category               Model
 * @DateOfCreation         11 May 2018
 * @ShortDescription       This is model which need to perform the options related to
doctor media table

 */

class DoctorMedia extends Model {

    use Encryptable;
    // @var string $table
    // This protected member contains table name
    protected $table = 'doctor_media';

    // @var string $timestamps
    // This will enable automatic insert of times
    public $timestamps = false;

    // @var Array $encryptedFields
    // This protected member contains fields that need to encrypt while saving in database
    protected $encryptable = ['doc_exp_organisation_name'];
    
    /**
     * @DateOfCreation        11 May 2018
     * @ShortDescription      This function is responsible for creating insert new media in DB
     * @param                 Array $data This contains full user input data
     * @return                True/False
     */

    /**
     *@ShortDescription Override the primary key.
     *
     * @var string
     */
    protected $primaryKey = 'doc_media_id';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // Init security library object
        $this->securityLibObj = new SecurityLib();
    }

    public function insertMedia($data)
    {
        // @var Boolean $response
        // This variable contains insert query response
        $response = false;

        // Prepair insert query
        $response = $this->dbInsert($this->table, $data);
        
        return DB::getPdo()->lastInsertId();
    }

    /**
     * @DateOfCreation        14 May 2018
     * @ShortDescription      This function is responsible for deleting media in DB
     * @param                 Array $data This contains full user input data
     * @return                True/False
     */
    public function deleteMedia($doc_media_id)
    {
        // @var Boolean $response
        // This variable contains insert query response
        $response = false;

        // Prepair insert query
        // @var Array $updateData
        // This Array contains update data for blog
        $updateData = array(
            'doc_media_status' => Config::get('constants.DOCTOR_MEDIA_PENDING')
        );
        $whereData = array(
                        'doc_media_id' => $doc_media_id
                    );

        // Prepair update query
        $response = $this->dbUpdate($this->table, $updateData, $whereData);

        return $response;
    }

    /**
     * @DateOfCreation        14 May 2018
     * @ShortDescription      This function is responsible for deleting media in DB
     * @param                 Array $data This contains full user input data
     * @return                True/False
     */
    public function getMedia($requestData) {
        $queryResult['pages'] = Config::get('constants.DATA_LIMIT'); //donot use its only for mobile structure
        $queryResult['result'] =  DB::table($this->table)
            ->where('doc_media_status', Config::get('constants.DOCTOR_MEDIA_ACTIVE'))
            ->where('user_id', $requestData['doctorId'])
            ->get()
            ->map(function ($doctorMedia) {
                $doctorMedia->doc_media_id = $this->securityLibObj->encrypt($doctorMedia->doc_media_id);

                $doctorMedia->doc_type = substr(strrchr($doctorMedia->doc_media_file, '.'),1);
                $doctorMedia->doc_media_file = $this->securityLibObj->encrypt($doctorMedia->doc_media_file);
                return $doctorMedia;
            });
        return $queryResult;
    }

    /**
     * @DateOfCreation        08 Sept 2018
     * @ShortDescription      This function is to get the Primary key name
     * @return                integer primary key name id
     */
    public function getTablePrimaryIdColumn()
    {
        return $this->primaryKey;
    }

    
    /**
     * @DateOfCreation        08 Sept 2018
     * @ShortDescription      This function is responsible to check the primary value exist in the system or not
     * @param                 integer $primaryId   
     * @return                boolean
     */
    public function isPrimaryIdExist($primaryId){
        $primaryIdExist = DB::table($this->table)
                        ->where($this->primaryKey, $primaryId)
                        ->exists();
        return $primaryIdExist;
    }
}
