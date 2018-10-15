<?php
namespace App\Libraries;
use Illuminate\Http\Request;
use App\Libraries\DateTimeLib;
use Config;
use Auth;
use DB;
/**
 * Fxlogs Class
 *
 * @package                ILD INDIA REGISTRY
 * @subpackage             Fxlogs
 * @category               Library
 * @DateOfCreation         05 Apr 2018
 * @ShortDescription       This Library is responsible for all  fxlog related data
 */
class Fxlogs {

    // @var String $secret_key1
    // This protected member contains log table name
    protected $logTableName = '';

    protected $resourceTypeIndexName = '';

    protected $resourceTypeDefaultValue = '';

    protected $logOperationIndexName = '';
    /**
     * Create a new library instance.
     *
     * @return void
     */
    public function __construct() {

        // Init DateTimeLib Library object
        $this->dateTimeObj = new DateTimeLib();

        // table name get cong
        $this->logTableName =Config::get('constants.LOG_SAVE_TABLE_NAME');
        $this->resourceTypeIndexName =Config::get('constants.LOG_REQUEST_RESOURCE_TYPE_INDEX_NAME');
        $this->resourceTypeDefaultValue =Config::get('constants.LOG_REQUEST_RESOURCE_TYPE_DEFAULT_VALUE');
        $this->logOperationIndexName =Config::get('constants.LOG_OPERATION_INDEX_NAME');

    }
   
    
    /**
    * @DateOfCreation        10 Apr 2018
    * @ShortDescription      This function is responsible for INSERT DATA LOG
    * @param                 String $tableName 
    * @param                 array $requestProcessData 
    * @param                 array $extraInfo any spaical condition task perform 
    * @return                null
    */
    public function insertUpdateLog($tableName='',$InfoData=[]) {

        if(auth()->guard('api')->check()){
            $user_id = auth()->guard('api')->user()->user_id;
        }else{
            $user_id  = 0;
        }
        $requestProcessData = isset($InfoData['requestData']) ? $InfoData['requestData'] : [];
        $whereData = isset($InfoData['whereData']) ? $InfoData['whereData'] : [];
        $whereCustomData = isset($InfoData['whereCustomData']) ? $InfoData['whereCustomData'] : '';
        $operationType = $this->logOperationIndexName!= '' && isset($InfoData[$this->logOperationIndexName]) ? $InfoData[$this->logOperationIndexName] : 0;
        $resorseTypeDataCheck = is_array(current($requestProcessData)) ? $requestProcessData : (!empty($requestProcessData) ? [$requestProcessData] :[]);
        $resorseTypeData = $this->resourceTypeIndexName!='' && !empty($resorseTypeDataCheck) ? current(array_column($resorseTypeDataCheck, $this->resourceTypeIndexName)) : $this->resourceTypeDefaultValue;

        $wol = [];
        $wol['wol_table'] = $tableName;
        $wol['wol_type']  = $operationType;
        $wol['wol_data']  = is_array($requestProcessData)  && !empty($requestProcessData) ? json_encode($requestProcessData) : null;
        $wol['wol_where']  = is_array($whereData)  && !empty($whereData) ? json_encode($whereData) : null;
        $wol['wol_custom_where']  = is_array($whereCustomData)  && !empty($whereCustomData) ? json_encode($whereCustomData) : ($whereCustomData!='' ? $whereCustomData: null);
        $wol['wol_created_at']  = $this->dateTimeObj->getPostgresTimestampAfterXmin(0);
        $wol['wol_user_id']  = $user_id;
        $wol['wol_ip']  = \Request::ip();
        $wol['wol_resource_type']  = $resorseTypeData;
        DB::table($this->logTableName)->insert($wol);

    }
}