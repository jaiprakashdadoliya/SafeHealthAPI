<?php

namespace App\Modules\Settings\Models;

use Illuminate\Database\Eloquent\Model;
use App\Libraries\SecurityLib;
use Illuminate\Support\Facades\DB;
use App\Traits\Encryptable;
use Config;
use App\Modules\Setup\Models\StaticDataConfig;

/**
 * AppointmentCategory Class
 *
 * @package                AppointmentCategory
 * @subpackage             Doctor AppointmentCategory
 * @category               Model
 * @DateOfCreation         7 june 2018
 * @ShortDescription       This is model which need to perform the options related to 
                           AppointmentCategory table
 */
class Settings extends Model 
{
	use Encryptable;
    /**
     * The attributes that should be override default primary key.
     *
     * @var string 
     */
    protected $primaryKey = 'lab_temp_id';

    /**
     * The attributes that should be override default table name.
     *
     * @var string 
     */
    protected $table = 'laboratory_templates';
    protected $tableMedicines           = 'medicines';
    protected $tablePatMedicineTemplate = 'patient_medicine_templates';
    protected $tableDrugType            = 'drug_type';
    protected $tableDrugDoseUnit        = 'drug_dose_unit';
    protected $tableSymptoms            = 'symptoms';
    protected $tableDisease             = 'diseases';
    protected $tableMasterLabTest       = 'master_laboratory_tests';
    protected $tableMasterLabTestRelation = 'master_laboratory_tests_relation';
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
        // Init model static data
        $this->staticDataModelObj = new StaticDataConfig();
    }

    /**
     *Get the list of laboratory templates saved for particular doctor
     *
     * @param array $data Request data
     * @return int doctor member id if inserted otherwise false
     */
    public function getList($requestData)
    {
       	$selectData  =  ['lab_temp_id', 'temp_name', 'symptoms_data', 'diagnosis_data', 'laboratory_test_data'];
        $whereData   =  [
                        'user_id'=> $requestData['user_id'],
                        'is_deleted'=>  Config::get('constants.IS_DELETED_NO'),
                        ];
        $query =  DB::table($this->table)
                    ->select($selectData)
                    ->where($whereData);
        /* Condition for Filtering the result */
            if(!empty($requestData['filtered'])){
                foreach ($requestData['filtered'] as $key => $value) {
                    $query = $query->where('temp_name', 'ilike', "%".$value['value']."%");
                }
            }
        
        /* Condition for Sorting the result */
        if(!empty($requestData['sorted'])){
            foreach ($requestData['sorted'] as $key => $value) {
                $orderBy = $value['desc'] ? 'desc' : 'asc';
                $query = $query->orderBy($value['id'], $orderBy);
            }
        }
        if(!empty($requestData['page']) && $requestData['page'] > 0){
            $offset = $requestData['page']*$requestData['pageSize'];
        }else{
            $offset = 0;
        }
        $queryResult['pages'] = intval($query->count()/$requestData['pageSize']);
        $queryResult['result'] = $query
                    ->offset($offset)
                    ->limit($requestData['pageSize'])
                    ->get()
                    ->map(function ($labTemplates) {
                            $labTemplates->lab_temp_id = $this->securityLibObj->encrypt($labTemplates->lab_temp_id);
                            return $labTemplates;
                        });
        return $queryResult;
    }

    /**
     * Create doctor service with regarding details
     *
     * @param array $data service data
     * @return Array doctor member if inserted otherwise false
     */

    public function createLabTemplates($requestData=array())
    {
		$queryResult = $this->dbInsert($this->table, $requestData);
        if($queryResult){
            $templateData = $this->getLabTemplateById(DB::getPdo()->lastInsertId());
            $templateData->lab_temp_id = $this->securityLibObj->encrypt(DB::getPdo()->lastInsertId());
            return $templateData;
        }
        return false;
    }

   /**
    * @DateOfCreation        22 May 2018
    * @ShortDescription      This function is responsible to get the service by id
    * @param                 String $lab_temp_id   
    * @return                Array of service
    */
    public function getLabTemplateById($lab_temp_id)
    {   
    	$selectData = ['lab_temp_id', 'temp_name', 'symptoms_data', 'diagnosis_data', 'laboratory_test_data'];
        $whereData = array(
                        'lab_temp_id' =>  $lab_temp_id, 
                        'is_deleted' => Config::get('constants.IS_DELETED_NO')
                    );
        $queryResult = $this->dbSelect($this->table, $selectData, $whereData);
        return $queryResult;
    }

    /**
     * Update Lab templates with regarding details
     *
     * @param array $data Template
     * @return boolean true if updated otherwise false
     */
    public function updateLabTemplates($requestData=array())
    {
        $lab_temp_id = $this->securityLibObj->decrypt($requestData['lab_temp_id']);
        unset($requestData['lab_temp_id']);
        $whereData =  array('lab_temp_id' => $lab_temp_id);
        $queryResult =  $this->dbUpdate($this->table, $requestData, $whereData);
        if($queryResult){
            $labTemplatesUpdateData = $this->getLabTemplateById($lab_temp_id);
            $labTemplatesUpdateData->lab_temp_id = $this->securityLibObj->encrypt($lab_temp_id);
            return $labTemplatesUpdateData;
        }
        return false;
    }
    /**
     * delete Lab Templates with regarding id
     *
     * @param int $id Template id
     * @return boolean particular doctor Template detail delete or not
     */
    public function deleteLabTemplate($lab_temp_id='')
    {
        $updateData = array('is_deleted' => Config::get('constants.IS_DELETED_YES'));
        $whereData = array('lab_temp_id' => $lab_temp_id);
        $queryResult =  $this->dbUpdate($this->table, $updateData, $whereData);
        if($queryResult){
            return true;
        }
        return false;
    }

    /**
     * @DateOfCreation        14 July 2018
     * @ShortDescription      This function is responsible to get medicine list
     * @param                 
     * @return                object Array of all medicines
     */
    public function getMedicineListData() 
    {   
        $queryResult = DB::table( $this->tableMedicines )
                        ->select( 
                                $this->tableMedicines.'.medicine_id',
                                $this->tableMedicines.'.medicine_name',
                                $this->tableMedicines.'.medicine_dose',
                                $this->tableDrugDoseUnit.'.drug_dose_unit_name'
                            )
                        ->leftJoin($this->tableDrugDoseUnit, function($join) {
                                $join->on($this->tableMedicines.'.drug_dose_unit_id', '=', $this->tableDrugDoseUnit.'.drug_dose_unit_id')
                                    ->where($this->tableDrugDoseUnit.'.is_deleted', '=', Config::get('constants.IS_DELETED_NO'), 'and');
                            }) 
                        ->where( $this->tableMedicines.'.is_deleted', Config::get('constants.IS_DELETED_NO') );
               
        $queryResult = $queryResult->get()
                                    ->map(function($medicineList){
                                        if(!empty($medicineList->medicine_id)){
                                            $medicineList->medicine_id = $this->securityLibObj->encrypt($medicineList->medicine_id);
                                            $medicineList->medicine_name = !empty($medicineList->medicine_dose) ? $medicineList->medicine_name. ' ( '.$medicineList->medicine_dose.' '.$medicineList->drug_dose_unit_name.' )' : $medicineList->medicine_name;
                                        }
                                        return $medicineList;
                                    });
        return $queryResult;
    }


    public function checkDataExist($requestData, $type,$user_id=''){
        if($type == 'symptoms_data'){
            foreach($requestData as $key => $data){
                $queryResult = DB::table($this->tableSymptoms)->where(['symptom_name'=>$data['text'],'is_deleted'=>Config::get('constants.IS_DELETED_NO')])->count();
                 if($queryResult == 0 ){
                    $request['symptom_name'] = $data['text'];
                    $response  = DB::table($this->tableSymptoms)->insert($request);
                    if($response){
                       $requestData[$key]['id']= DB::getPdo()->lastInsertId();
                    }

                }else{
                    $requestData[$key]['id'] = $this->securityLibObj->decrypt($requestData[$key]['id']);
                }
            }
            return $requestData;
        }

        if($type == 'diagnosis_data'){
            foreach($requestData as $key => $data){
                $queryResult = DB::table($this->tableDisease)->where(['disease_name'=>$data['text'],'is_deleted'=>Config::get('constants.IS_DELETED_NO')])->count();
                 if($queryResult == 0 ){
                    $request['disease_name'] = $data['text'];
                    $request['is_show_in_type'] = 1;
                    $response  = DB::table($this->tableDisease)->insert($request);
                    if($response){
                       $requestData[$key]['id']= DB::getPdo()->lastInsertId();
                    }

                }else{
                    $requestData[$key]['id'] = $this->securityLibObj->decrypt($requestData[$key]['id']);
                }
            }
            return $requestData;
        }

        if($type == 'laboratory_test_data'){
            foreach($requestData as $key => $data){
                $queryResult = DB::table($this->tableMasterLabTest)->where(['mlt_name'=>$data['text'],'is_deleted'=>Config::get('constants.IS_DELETED_NO')])->count();
                 if($queryResult == 0 ){
                    $request['mlt_name'] = $data['text'];
                    $response  = $this->dbInsert($this->tableMasterLabTest, $request);
                    if($response){
                        $lastInsertId = DB::getPdo()->lastInsertId();
                        $insertData = [
                            'mlt_id' => $lastInsertId,
                            'user_id'=> $user_id
                        ];
                       $this->dbInsert($this->tableMasterLabTestRelation, $insertData);
                       $requestData[$key]['id']= $lastInsertId;
                    }

                }else{
                    $requestData[$key]['id'] = $this->securityLibObj->decrypt($requestData[$key]['id']);
                }
            }
            return $requestData;
        }


    }

    /**
     * @DateOfCreation        22 Aug 2018
     * @ShortDescription      This function is responsible to get the medicine dose unit
     * @return                array dose unit data
     * @param                 
     */
    public function getDoseUnit() {
        $queryResult = DB::table($this->tableDrugDoseUnit)
                    ->select('drug_dose_unit_id', 'drug_dose_unit_name')
                    ->where('is_deleted',Config::get('constants.IS_DELETED_NO'))
                    ->get()
                    ->map(function($drugUnit){
                        $drugUnit->drug_dose_unit_id = $this->securityLibObj->encrypt($drugUnit->drug_dose_unit_id);
                        return $drugUnit;
                    });

        return $queryResult;
    }

    /**
     * @DateOfCreation        22 Aug 2018
     * @ShortDescription      This function is responsible to get the medicine dose type
     * @return                array dose unit data
     * @param                 
     */

    public function getAllDrugType($param=[],$encrypt=true) 
    {   
        $name = isset($param['drug_type_name']) ? $param['drug_type_name'] :'';    
        $selectData = ['drug_type_id','drug_type_name'];
        $whereData = ['is_deleted'=>Config::get('constants.IS_DELETED_NO')];
        $queryResult = DB::table($this->tableDrugType)
                        ->select($selectData)
                        ->where($whereData);     
        if($name!=''){
            $queryResult = $queryResult->where('drug_type_name','ilike',$name);
        }
        $queryResult = $queryResult->get();
        if(count($queryResult)>0 && $encrypt){
            $queryResult = $queryResult->map(function($dataList){ 
                $dataList->drug_type_id = $this->securityLibObj->encrypt($dataList->drug_type_id);
                return $dataList;
            });
        }
        return $queryResult;
    }

    /**
     * @DateOfCreation        14 July 2018
     * @ShortDescription      This function is responsible to save template
     * @param                 
     * @return                object Array of all medicines
     */
    public function getMedicineTemplate($requestData)
    {
         $query = DB::table($this->tablePatMedicineTemplate)
            ->select( 'temp_name', 'pat_med_temp_id' ) 
            ->where(['user_id'=>$requestData['user_id'], 'is_deleted'=> Config::get('constants.IS_DELETED_NO')]);

                    /* Condition for Filtering the result */
        if(!empty($requestData['filtered'])){
            foreach ($requestData['filtered'] as $key => $value) {
                 $query = $query->where('temp_name', 'ilike', "%".$value['value']."%");
            }
        }

        /* Condition for Sorting the result */
        if(!empty($requestData['sorted'])){
            foreach ($requestData['sorted'] as $key => $value) {
                $orderBy = $value['desc'] ? 'desc' : 'asc';
                $query = $query->orderBy($value['id'], $orderBy);
            }
        }
        if($requestData['page'] > 0){
            $offset = $requestData['page']*$requestData['pageSize'];
        }else{
            $offset = 0;
        }
        $queryResult['pages'] = ceil($query->count()/$requestData['pageSize']);
        $queryResult['result'] =  
            $query
                ->offset($offset)
                ->limit($requestData['pageSize'])
                ->get()
                ->map(function($patientMedication){
                    $patientMedication->pat_med_temp_id  = $this->securityLibObj->encrypt($patientMedication->pat_med_temp_id);
                    return $patientMedication;
                });
        if(!empty($queryResult)){
            return $queryResult;
        }else{
            return false;
        }
    }

    /**
     * @DateOfCreation        14 July 2018
     * @ShortDescription      This function is responsible to get template
     * @param                 
     * @return                object medicine template
     */
    public function getMedicineTemplateList($requestData)
    {
        $queryResult = DB::table($this->tablePatMedicineTemplate)
            ->select('medication_data') 
            ->where(['user_id'=>$requestData['user_id'],'pat_med_temp_id'=>$requestData['pat_med_temp_id'], 'is_deleted'=> Config::get('constants.IS_DELETED_NO')])
            ->first();
        if(!empty($queryResult) && $queryResult->medication_data != NULL){
            $medication_data =  json_decode($queryResult->medication_data);
            foreach ($medication_data as $key => $medicationData) {
               $query = DB::table('drug_dose_unit')->select('drug_dose_unit_name')->where(['drug_dose_unit_id'=> $this->securityLibObj->decrypt($medicationData->medicine_dose_unit), 'is_deleted'=> Config::get('constants.IS_DELETED_NO')])->first();
               $medication_data[$key]->medicine_dose_unitVal = $query->drug_dose_unit_name;
            }
            return $medication_data;
        }else{
            return array();
        }
    }


        /**
     * @DateOfCreation        14 July 2018
     * @ShortDescription      This function is responsible to save template
     * @param                 
     * @return                object Array of all medicines
     */
    public function saveMedicineTemplate($requestData)
    {
        unset($requestData['pat_med_temp_id']);
        $response  = $this->dbInsert($this->tablePatMedicineTemplate, $requestData);
        if($response){
            $id = DB::getPdo()->lastInsertId();
            return $this->getTemplateById($id);
            
        }else{
            return $response;
        }
    }

        /**
     * @DateOfCreation        14 July 2018
     * @ShortDescription      This function is responsible to save template
     * @param                 
     * @return                object Array of all medicines
     */
    public function updateMedicineTemplate($requestData)
    {
        $requestData['pat_med_temp_id'] = $this->securityLibObj->decrypt($requestData['pat_med_temp_id']);
        $whereData = array('pat_med_temp_id' => $requestData['pat_med_temp_id']);
        $queryResult =  $this->dbUpdate($this->tablePatMedicineTemplate, $requestData, $whereData);
        if($queryResult){
            return $this->getTemplateById($requestData['pat_med_temp_id']);
        }
        return false;
    }

    /**
     * @DateOfCreation        14 July 2018
     * @ShortDescription      This function is responsible to save template
     * @param                 
     * @return                object Array of all medicines
     */
    public function deleteMedicineTemplate($requestData)
    {
        $requestData['pat_med_temp_id'] = $this->securityLibObj->decrypt($requestData['pat_med_temp_id']);
        $whereData = array('pat_med_temp_id' => $requestData['pat_med_temp_id']);
        $updateData = array('is_deleted' => Config::get('constants.IS_DELETED_YES'));
        $queryResult =  $this->dbUpdate($this->tablePatMedicineTemplate, $updateData, $whereData);
        if($queryResult){
            return true;
        }
        return false;
    }

    

    /**
     * @DateOfCreation        14 July 2018
     * @ShortDescription      This function is responsible to get template
     * @param                 
     * @return                object Array of all medicines
     */
    public function getTemplateById($pat_med_temp_id)
    {
         $queryResult = DB::table($this->tablePatMedicineTemplate)
            ->select( 'temp_name', 'pat_med_temp_id' ) 
            ->where(['pat_med_temp_id'=>$pat_med_temp_id, 'is_deleted'=> Config::get('constants.IS_DELETED_NO')])
            ->first(); 
        if(!empty($queryResult)){
            $queryResult->pat_med_temp_id  = $this->securityLibObj->encrypt($queryResult->pat_med_temp_id);
            return $queryResult;
        }else{
            return false;
        }
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
