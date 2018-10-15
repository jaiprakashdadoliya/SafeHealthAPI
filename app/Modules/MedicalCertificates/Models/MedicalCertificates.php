<?php

namespace App\Modules\MedicalCertificates\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Laravel\Passport\HasApiTokens;
use App\Traits\Encryptable;
use App\Libraries\SecurityLib;
use Config;
use Carbon\Carbon;

/**
 * MedicalCertificates
 *
 * @package                 Safehealth
 * @subpackage              MedicalCertificates
 * @category                Model
 * @DateOfCreation          27 June 2018
 * @ShortDescription        This Model to handle database operation with current table
                            doctors MedicalCertificates
 **/
class MedicalCertificates extends Model {

    use HasApiTokens,Encryptable;

    // @var string $table
    // This protected member contains table name
    protected $table = 'medical_certificates';

    // @var string $primaryKey
    // This protected member contains primary key
    protected $primaryKey = 'mc_id';

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
     * @DateOfCreation        26 Sept 2018
     * @ShortDescription      This function is responsible for get Medical Certificates Data
     * @param                 $requestData 
     * @return                array
     */
    public function getMedicalCertificatesData($requestData)
    {
        $medicalCertificatesData = DB::table('medical_certificates')
                        ->select(DB::raw("mc_text, mc_id"))
                        ->where([
                                'user_id'    => $requestData['user_id'], 
                            ])
                        ->first();
        if(!empty($medicalCertificatesData)){
            $medicalCertificatesData->mc_id = $this->securityLibObj->encrypt($medicalCertificatesData->mc_id);
        }
        return $medicalCertificatesData;
    }

    /**
    * @DateOfCreation        10 July 2018
    * @ShortDescription      This function is responsible to get the MedicalCertificates record by id
    * @param                 String $mc_id
    * @return                Array of MedicalCertificates data
    */
    public function getMedicalCertificateById($mc_id)
    {
        $queryResult = DB::table($this->table)
            ->select('mc_id', 'mc_text')
            ->where('mc_id', $mc_id)
            ->first();
         return $this->decryptSingleData($queryResult);
    }


    /**
     * @DateOfCreation        08 June 2018
     * Create or Edit doctor MedicalCertificates with regarding details
     * @param array $data MedicalCertificates data
     * @return Array doctor MedicalCertificates if inserted otherwise updated
     */
    public function saveMedicalCertificatesData($requestData=array()) {
        $requestData['updated_at'] = Carbon::now();
        $requestData['updated_by'] = $requestData['user_id'];

        if($requestData['mc_id'] && !empty($requestData['mc_id'])) {
            unset($requestData['effect_date']);
            unset($requestData['certificate_date']);
            $requestData = $this->encryptData($requestData);
            $isUpdated = DB::table($this->table)
                        ->where('mc_id', $requestData['mc_id'])
                        ->update($requestData);
            if(!empty($isUpdated)) {
                $medicalCertificatesData = $this->getMedicalCertificateById($requestData['mc_id']);
                $medicalCertificatesData->mc_id = $this->securityLibObj->encrypt($medicalCertificatesData->mc_id);
                return $medicalCertificatesData;
            }
        }else{
            unset($requestData['mc_id']);
            $requestData['created_by'] = $requestData['user_id'];
            $requestData['created_at'] = Carbon::now();
            $requestData = $this->encryptData($requestData);
            $isInserted = DB::table($this->table)->insert($requestData); 
            if(!empty($isInserted)) {
                 $medicalCertificatesData = $this->getMedicalCertificateById(DB::getPdo()->lastInsertId());

                // Encrypt the ID
                $medicalCertificatesData->mc_id = $this->securityLibObj->encrypt(DB::getPdo()->lastInsertId());
                return $medicalCertificatesData;
            }
        }
        return false;
    }

}
