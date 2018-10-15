<?php
namespace App\Modules\Visits\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\HasApiTokens;
use App\Traits\Encryptable;
use App\Libraries\SecurityLib;
use App\Libraries\UtilityLib;
use App\Libraries\FileLib;
use Config;
use DB;
use App\Modules\Visits\Models\Visits;

/**
 * InvestigationReport
 *
 * @package                Safe Health
 * @subpackage             InvestigationReport
 * @category               Model
 * @DateOfCreation         5 Oct 2018
 * @ShortDescription       This Model to handle database operation of Investigation Report table
 **/

class InvestigationReport extends Model {

    use HasApiTokens, Encryptable;

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

        // Init File Library object
        $this->FileLib = new FileLib();

        // Init Visit model object
        $this->visitModelObj = new Visits();
    }

    /**
    *@ShortDescription Table for the Users.
    *
    * @var String
    */
    protected $table          = 'investigation_report';
    
    // This protected member contains fields that need to encrypt while saving in database
    protected $encryptable = [];
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [ 
                            'pat_id',
                            'visit_id',
                            'ip_address',
                            'resource_type',
                            'is_deleted',
                            'report_file',
                            'report_type',
                            'report_description'
                        ];

    /**
     *@ShortDescription Override the primary key.
     *
     * @var string
     */
    protected $primaryKey = 'ir_id';

    /**
     * @DateOfCreation        5 Oct 2018
     * @ShortDescription      This function is responsible to get visit Investigation Reports
     * @param                 
     * @return                object Array of all records
     */
    public function getInvestigationReportData($requestData) 
    {   
        $queryResult = DB::table( $this->table )
                        ->select( 
                                'ir_id',
                                'report_file',
                                'report_type',
                                'report_description'
                            ) 
                        ->where( 'is_deleted', Config::get('constants.IS_DELETED_NO') )
                        ->where( 'pat_id', $requestData['pat_id'])
                        ->where( 'visit_id', $requestData['visit_id']);
               
        $queryResult = $queryResult->get()
                                    ->map(function($iReport) {
                                        $iReport->ir_id         = $this->securityLibObj->encrypt($iReport->ir_id);
                                        $iReport->report_type   = $this->securityLibObj->encrypt($iReport->report_type);
                                        
                                        return $iReport;
                                    });
        return $queryResult;
    }

    /**
     * @DateOfCreation        8Oct 2018
     * @ShortDescription      This function is responsible to get visit Investigation Reports By Primary ID
     * @param                 
     * @return                object Array of all records
     */
    public function getInvestigationReportDataById($selectData = [], $whereData) 
    {   
        $queryResult = DB::table( $this->table )
                        ->select( $selectData ) 
                        ->where( 'is_deleted', Config::get('constants.IS_DELETED_NO') )
                        ->where($whereData);
               
        $queryResult = $queryResult->get();
        return $queryResult;
    }

    /**
    * @DateOfCreation        5 Oct 2018
    * @ShortDescription      This function is responsible to save Investigation Reports data
    * @param                 Array  $requestData   
    * @return                Array of status and message
    */
    public function saveInvestigationReportData($requestData)
    {
        $response  = $this->dbInsert($this->table, $requestData);

        if($response){
            $id = DB::getPdo()->lastInsertId();
            return $id;
            
        }else{
            return $response;
        }
    }

    /**
    * @DateOfCreation        5 Oct 2018
    * @ShortDescription      This function is responsible to update Investigation Reports data
    * @param                 Array  $requestData   
    * @return                Array of status and message
    */
    public function updateInvestigationReportData($requestData, $id)
    {
        $whereData = [ 'ir_id' => $id ];
        
        // Prepare update query
        $response = $this->dbUpdate($this->table, $requestData, $whereData);
        
        if($response){
            return true;
        }
        return false;
    }

    /**
    * @DateOfCreation        5 Oct 2018
    * @ShortDescription      This function is responsible to update Investigation Reports data
    * @param                 Array  $requestData   
    * @return                Array of status and message
    */
    public function checkAndUpdateInvestigationReport($requestData)
    {
        $response = false;

        $error = false;
        $insertDataArr = [];

        if(!empty($requestData)){
            foreach ($requestData as $data) {
                if(!empty($data['report_file'] || $data['report_description'])){

                    if(!empty($data['report_file']) && is_object($data['report_file'])){
                        
                        $destination    = Config::get('constants.INVESTIGATION_REPORT_PATH');
                        $storagPath     = Config::get('constants.STORAGE_MEDIA_PATH');
                        $fileUpload     = $this->FileLib->fileUpload($data['report_file'], $destination);

                        if($fileUpload["code"] == '5000'){
                            return false;
                        }
                        $data['report_file'] = $fileUpload['uploaded_file'];
                    } else{
                        unset($data['report_file']);
                    }

                    $whereCheck = ['pat_id' => $data['pat_id'], 'visit_id' => $data['visit_id'], 'report_type' => $data['report_type']];
                    $checkDataExist = $this->visitModelObj->checkIfRecordExist($this->table, ['ir_id', 'report_file'], $whereCheck, 'get');
                    $checkDataExist = json_decode(json_encode($checkDataExist), true);

                    if(!empty($checkDataExist)){
                        if(isset($checkDataExist[0]) && !empty($checkDataExist[0]['report_file']) && isset($data['report_file'])){
                            $path = Config::get('constants.STORAGE_MEDIA_PATH').Config::get('constants.INVESTIGATION_REPORT_PATH').$checkDataExist[0]['report_file'];
                            $path = storage_path($path);
                            unlink($path);
                        }
                        $response = $this->dbUpdate($this->table, $data, $whereCheck);

                        if(!$response){
                            $error = true;
                        }
                    }else{
                        // insert
                        $insertDataArr[] = $data;
                    }
                }
            }

            if(!empty($insertDataArr)){
                if(!$this->dbBatchInsert($this->table, $insertDataArr)){
                    $error = true;
                }
            }

            if(!$error){
                return true;
            }
            return false;
        }
        return true;
    }
}
