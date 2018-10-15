<?php

namespace App\Modules\PaymentMode\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\HasApiTokens;
use App\Traits\Encryptable;
use Illuminate\Support\Facades\DB;
use App\Libraries\SecurityLib;
use App\Libraries\UtilityLib;
use App\Libraries\DateTimeLib;
use Config;

/**
 * PaymentMode Class
 *
 * @package                ILD INDIA
 * @subpackage             PaymentMode
 * @category               Model
 * @DateOfCreation         04 Oct 2018
 * @ShortDescription       This is model which need to perform the options related to 
                           PaymentMode info

 */
class PaymentMode extends Model {

    use Encryptable;

    // @var string $table
    // This protected member contains table name
    protected $table = 'payment_mode';

    // @var string $primaryKey
    // This protected member contains primary key
    protected $primaryKey = 'payment_mode_id';  


    protected $encryptable = [];

    protected $fillable = ['user_id', 'payment_mode_id','payment_mode', 'ip_address', 'resource_type'];

    /**
     * Create a new model instance.
     *
     * @return void
     */
    public function __construct()
    {
        // Init exception library object
        $this->utilityLibObj = new UtilityLib();

        // Init security library object
        $this->securityLibObj = new SecurityLib();

        // Init dateTime library object
        $this->dateTimeLibObj = new DateTimeLib();

    }

    

    /**
     * @DateOfCreation        04 Oct 2018
     * @ShortDescription      This function is responsible for get all Patients Records by user_id and list fillter and sorting apply for selected column
     * @param                 Array $data This contains full Patient user input data 
     * @return                True/False
     */
    public function getPaymentModeList($requestData)
    {
        //DB::enableQueryLog();
        $listQuery = $this->paymentModeListQuery($requestData['user_id']);

        if(!empty($requestData['filtered'])){
            foreach ($requestData['filtered'] as $key => $value) {

                if(!empty($value['value'])){
                    $listQuery = $listQuery->where(function ($listQuery) use ($value){
                                    $listQuery
                                    ->where('payment_mode', 'ilike', "%".$value['value']."%");
                                });
                }
            }
        }

        if(!empty($requestData['sorted'])){
            foreach ($requestData['sorted'] as $sortKey => $sortValue) {
                $orderBy = $sortValue['desc'] ? 'desc' : 'asc';
                $listQuery->orderBy($sortValue['id'], $orderBy);
            }
        }

        if($requestData['page'] > 0){
            $offset = $requestData['page'] * $requestData['pageSize'];
        }else{
            $offset = 0;            
        }

        $list['pages']   = ceil($listQuery->count()/$requestData['pageSize']);
        
        $list['result']  = $listQuery
                                ->offset($offset)
                                ->limit($requestData['pageSize'])
                                ->get()
                                ->map(function($listData){
                                    $listData->payment_mode_id = $this->securityLibObj->encrypt($listData->payment_mode_id);
                                    return $listData;
                                }); 
        return $list;
    }

    /**
     * @DateOfCreation        04 Oct 2018
     * @ShortDescription      This function is responsible for patient list query from user and patient tables
     * @param                 Array $data This contains full Patient user input data 
     * @return                Array of patients
     */
    public function paymentModeListQuery($userId){
        
        $selectData = [$this->table.'.payment_mode',$this->table.'.payment_mode_id'];
        

        $whereData = array(
                        $this->table.'.is_deleted'      => Config::get('constants.IS_DELETED_NO'),
                        $this->table.'.user_id'         => $userId
                    );
        $listQuery = DB::table($this->table)
                        ->select($selectData)
                        ->where($whereData);     
                                  
        return $listQuery;
    }
    
    /**
     * @DateOfCreation        04 Oct 2018
     * @ShortDescription      This function is responsible to save record for the Patient Medication History
     * @param                 array $requestData   
     * @return                integer Patient Medication History id
     */
    public function getTableName()
    {
        return $this->table;
    }

    /**
     * @DateOfCreation        04 Oct 2018
     * @ShortDescription      This function is responsible to save record for the Patient Medication History
     * @param                 array $requestData   
     * @return                integer Patient Medication History id
     */
    public function getTablePrimaryIdColumn()
    {
        return $this->primaryKey;
    }

    /**
     * @DateOfCreation        04 Oct 2018
     * @ShortDescription      This function is responsible to check the Visit  wefId exist in the system or not
     * @param                 integer $wefId   
     * @return                Array of status and message
     */
    public function isPrimaryIdExist($primaryId){
        $primaryIdExist = DB::table($this->table)
                        ->where($this->primaryKey, $primaryId)
                        ->exists();
        return $primaryIdExist;
    }
    /**
     * @DateOfCreation        04 Oct 2018
     * @ShortDescription      This function is responsible to Delete Payment Type data
     * @param                 integer $wefId   
     * @return                Array of status and message
     */
    public function doDeleteRequest($primaryId)
    {
        $queryResult = $this->dbUpdate( $this->table, 
                                        [ 'is_deleted' => Config::get('constants.IS_DELETED_YES') ], 
                                        [$this->primaryKey => $primaryId]
                                    );

        if($queryResult){
            return true;
        }
        return false;
    }

    /**
    * @DateOfCreation        04 Oct 2018
    * @ShortDescription      This function is responsible to update Payment Type Record
    * @param                 Array  $requestData   
    * @return                Array of status and message
    */
    public function updateRequest($requestData,$whereData)
    {
        $updateData = $this->utilityLibObj->fillterArrayKey($requestData, $this->fillable);
        $response = $this->dbUpdate($this->table, $updateData, $whereData);
        if($response){
            return true;
        }
        return false;
    }

    public function getAllPaymentMode($param=[],$userId='',$encrypt=true) 
    {   
        $name = isset($param['payment_mode']) ? $param['payment_mode'] :'';   
        
        $selectData = ['payment_mode_id','payment_mode'];
        $whereData = ['is_deleted'=>Config::get('constants.IS_DELETED_NO'), 'user_id' => $param['user_id']];
        $queryResult = DB::table($this->table)
                        ->select($selectData)
                        ->where($whereData);     
        if($name!=''){
            $queryResult = $queryResult->where('payment_mode','ilike',$name);
        }
        $queryResult = $queryResult->get();
        if(count($queryResult)>0 && $encrypt){
            $queryResult = $queryResult->map(function($dataList){ 
                $dataList->payment_mode_id = $this->securityLibObj->encrypt($dataList->payment_mode_id);
                return $dataList;
            });
        }
        return $queryResult;
    }

    /**
     * @DateOfCreation        04 Oct 2018
     * @ShortDescription      This function is responsible to save record for the Payment Type
     * @param                 array $requestData   
     * @return                integer auto increment id
     */
    public function savePaymentMode($inserData)
    {
        // @var Boolean $response
        // This variable contains insert query response
        $response = false;
        unset($inserData['payment_mode_id']);

        // @var Array $inserData
        // This Array contains insert data for Patient
        $inserData = $this->utilityLibObj->fillterArrayKey($inserData, $this->fillable);
                
        // Prepair insert query
        $response = $this->dbInsert($this->table, $inserData);
        if($response){
            $id = DB::getPdo()->lastInsertId();
            return $id;
            
        }else{
            return $response;
        }          
    }


}
