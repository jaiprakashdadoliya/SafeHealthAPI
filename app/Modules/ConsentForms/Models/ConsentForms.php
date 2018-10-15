<?php

namespace App\Modules\ConsentForms\Models;

use Illuminate\Database\Eloquent\Model;
use App\Libraries\SecurityLib;
use Illuminate\Support\Facades\DB;
use App\Traits\Encryptable;
use Config;

/**
 * ConsentForms Class
 *
 * @package                ConsentForms
 * @subpackage             Consent Forms
 * @category               Model
 * @DateOfCreation         7 june 2018
 * @ShortDescription       This is model which need to perform the options related to 
                           consentForms table
 */
class ConsentForms extends Model 
{
	use Encryptable;
    /**
     * The attributes that should be override default primary key.
     *
     * @var string 
     */
    protected $primaryKey = 'consent_form_id';

    /**
     * The attributes that should be override default table name.
     *
     * @var string 
     */
    protected $table = 'consent_forms';

    // @var Array $encryptedFields
    // This protected member contains fields that need to encrypt while saving in database
    protected $encryptable = [];

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

    /**
     * Create doctor membership list with regarding details
     *
     * @param array $data membership data
     * @return int doctor member id if inserted otherwise false
     */
    public function consentFormsList($requestData)
    {
       	$selectData  =  ['consent_form_id', 'consent_form_title', 'consent_form_content'];
        $whereData   =  [
                        'is_deleted'=>  Config::get('constants.IS_DELETED_NO'),
                        'user_id'=>  $requestData['user_id'],
                        ];
        $queryResult['result'] =  DB::table($this->table)
                    ->select($selectData)
                    ->where($whereData)
                    ->get()
                    ->map(function ($consentForm) {
                            $consentForm->consent_form_id = $this->securityLibObj->encrypt($consentForm->consent_form_id);
                            return $consentForm;
                        });
        return $queryResult;
    }

    /**
     * Create doctor consentForm with regarding details
     *
     * @param array $data consentForm data
     * @return Array doctor member if inserted otherwise false
     */

    public function saveConsentForm($requestData=array())
    {
        
        $consent_form_id = $this->securityLibObj->decrypt($requestData['consent_form_id']);
        unset($requestData['consent_form_id']);
        if(!empty($consent_form_id)){
            $whereData =  array('consent_form_id' => $consent_form_id);
            $queryResult =  $this->dbUpdate($this->table, $requestData, $whereData);
            if($queryResult){
                $consentFormUpdateData = $this->getConsentFormById($consent_form_id);
                $consentFormUpdateData->consent_form_id = $this->securityLibObj->encrypt($consent_form_id);
                return $consentFormUpdateData;
            }
        }else{
            $queryResult = $this->dbInsert($this->table, $requestData);
            if($queryResult){
                $consentFormData = $this->getConsentFormById(DB::getPdo()->lastInsertId());
                // Encrypt the ID
                $consentFormData->consent_form_id = $this->securityLibObj->encrypt(DB::getPdo()->lastInsertId());
                return $consentFormData;
            }
        }
        return false;
    }

   /**
    * @DateOfCreation        22 May 2018
    * @ShortDescription      This function is responsible to get the consentForm by id
    * @param                 String $consent_form_id   
    * @return                Array of consentForm
    */
    public function getConsentFormById($consent_form_id)
    {   
    	$selectData = ['consent_form_id', 'consent_form_title', 'consent_form_content'];
        $whereData = array(
                        'consent_form_id' =>  $consent_form_id, 
                        'is_deleted' => Config::get('constants.IS_DELETED_NO')
                    );
        $queryResult = $this->dbSelect($this->table, $selectData, $whereData);
        return $queryResult;
    }

    /**
     * delete doctor consentForm with regarding id
     *
     * @param int $id consentForm id
     * @return boolean particular doctor consentForm detail delete or not
     */
    public function deleteConsentForm($consent_form_id='')
    {
        $updateData = array('is_deleted' => Config::get('constants.IS_DELETED_YES'));
        $whereData = array('consent_form_id' => $consent_form_id);
        $queryResult =  $this->dbUpdate($this->table, $updateData, $whereData);
        if($queryResult){
            return true;
        }
        return false;
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
