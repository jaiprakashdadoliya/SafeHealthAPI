<?php

namespace App\Modules\Accounts\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\HasApiTokens;
use App\Traits\Encryptable;
use App\Libraries\SecurityLib;
use Config;
use App\Libraries\UtilityLib;
use App\Libraries\DateTimeLib;
use DB;
use DateTime;

class Accounts extends Model {

	use HasApiTokens,Encryptable;

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

        // Init dateTime library object
        $this->dateTimeLibObj = new DateTimeLib(); 
    
    }

    protected $encryptable = [];

    /**
    *@ShortDescription Table for the Accounts.
    *
    * @var String
    */
    protected $tablePayments        = 'payments_history';
    protected $tableInvoices 		= 'invoices_history';
    protected $tableCheckupType     = 'checkup_type';
    protected $tablePaymentMode 	= 'payment_mode';
    protected $tableUsers           = 'users';
    protected $tablePatients        = 'patients';
    protected $tableDoctors         = 'doctors';
	
	/**
    * @DateOfCreation        04 Sep 2018
    * @ShortDescription      This function is responsible to get the Payment history
    * @param                 String $user_id
    * @return                Array of status and message
    */
    public function getPaymentHistory($requestData)
    {	
    	$whereData  = [
        				$this->tablePayments.'.user_id'=> $requestData['user_id'],
        				$this->tablePayments.'.is_deleted'=> Config::get('constants.IS_DELETED_NO')
        			];

        $selectData = [$this->tablePayments.'.payment_id', $this->tablePayments.'.reciept_number', $this->tablePayments.'.amount', $this->tableCheckupType.'.checkup_type', $this->tablePaymentMode.'.payment_mode', $this->tableInvoices.'.invoice_number', $this->tableUsers.'.user_firstname', $this->tableUsers.'.user_lastname', $this->tableUsers.'.user_gender', $this->tablePatients.'.pat_dob', $this->tablePatients.'.pat_code', $this->tablePatients.'.pat_profile_img'];

        $data_limit = Config::get('constants.DATA_LIMIT');
        $query = DB::table($this->tablePayments)
					->select($selectData)
                    ->join($this->tableUsers,function($join) {
                        $join->on($this->tableUsers.'.user_id', '=', $this->tablePayments.'.pat_id')
                        ->where($this->tableUsers.'.is_deleted', '=', Config::get('constants.IS_DELETED_NO'), 'and');
                    })
                    ->leftjoin($this->tableCheckupType,function($join) {
                        $join->on($this->tableCheckupType.'.checkup_type_id', '=', $this->tablePayments.'.checkup_type_id')
                        ->where($this->tableCheckupType.'.is_deleted', '=', Config::get('constants.IS_DELETED_NO'), 'and');
                      })
                    ->leftjoin($this->tablePaymentMode,function($join) {
                        $join->on($this->tablePaymentMode.'.payment_mode_id', '=', $this->tablePayments.'.payment_mode_id')
                        ->where($this->tablePaymentMode.'.is_deleted', '=', Config::get('constants.IS_DELETED_NO'), 'and');
                    })
                    ->leftjoin($this->tablePatients,function($join) {
                        $join->on($this->tablePatients.'.user_id', '=', $this->tablePayments.'.pat_id')
                        ->where($this->tablePatients.'.is_deleted', '=', Config::get('constants.IS_DELETED_NO'), 'and');
                    })
                    ->leftjoin($this->tableInvoices,function($join) {
                        $join->on($this->tableInvoices.'.payment_id', '=', $this->tablePayments.'.payment_id')
                        ->where($this->tableInvoices.'.is_deleted', '=', Config::get('constants.IS_DELETED_NO'), 'and');
                    })
					->where($whereData);

		if($requestData['page'] > 0){
            $offset = $requestData['page']*$data_limit;
        }else{
            $offset = 0;
        }
        $finalDataCount = $query->count();
        $queryResult['pages'] = ceil($finalDataCount/$requestData['pageSize']);
        $queryResult['result'] = $query
								->offset($offset)
								->limit($requestData['pageSize'])
								->get()
	                            ->map(function ($payments) {
	                                $payments->payment_id = $this->securityLibObj->encrypt($payments->payment_id);
                                    $payments->pat_dob = !empty($payments->pat_dob) ? $this->dateTimeLibObj->ageCalculation($payments->pat_dob,'Y-m-d') : '';
                                    $payments->pat_profile_img = !empty($payments->pat_profile_img) ? url('api/patient-profile-image/'.$this->securityLibObj->encrypt($payments->pat_profile_img)) : '';
	                                return $payments;
	                            });

        $queryResult['searched_count'] = $finalDataCount;
        $queryResult['pages'] = ceil($finalDataCount/$data_limit);
        $queryResult['page'] = $requestData['page'];
        return $queryResult;
	}

	/**
    * @DateOfCreation        04 Sep 2018
    * @ShortDescription      This function is responsible to get the Invoice history
    * @param                 String $user_id
    * @return                Array of status and message
    */
	public function getInvoiceHistory($requestData)
    {
        $pageSize = Config::get('constants.DATA_LIMIT');
    	$whereData  = [
        				$this->tableInvoices.'.user_id'=> $requestData['user_id'],
        				$this->tableInvoices.'.is_deleted'=>  Config::get('constants.IS_DELETED_NO')
        			];

        $selectData = ['invoice_id', 'invoice_number','discount',DB::raw("CONCAT(doc.user_firstname,' ',doc.user_lastname) AS doc_name"),DB::raw("CONCAT(pat.user_firstname,' ',pat.user_lastname) AS pat_name"),'pat.user_gender','pat_code','pat_dob','pat_profile_img','doc_consult_fee','checkup_type'];
        $query = DB::table($this->tableInvoices)
							->select($selectData)
                            ->join($this->tableUsers.' AS doc','doc.user_id', '=', $this->tableInvoices.'.user_id')
                            ->join($this->tableUsers.' AS pat','pat.user_id', '=', $this->tableInvoices.'.pat_id')
                            ->join($this->tablePatients, $this->tablePatients.'.user_id', '=', $this->tableInvoices.'.pat_id')
                            ->join($this->tableDoctors, $this->tableDoctors.'.user_id', '=', $this->tableInvoices.'.user_id')
                            ->leftjoin($this->tableCheckupType, $this->tableCheckupType.'.user_id', '=','doc.user_id')
                            ->where($whereData);

		if(!empty($requestData['page']) && $requestData['page'] > 0){
            $offset = $requestData['page']*$pageSize;
        }else{
            $offset = 0;
        }
        $queryResult['pages'] = ceil($query->count()/$pageSize);

        $queryResult['result'] = $query
                                ->offset($offset)
                                ->limit($pageSize)
                                ->get()
                                ->map(function ($invoices) {
                                    $invoices->invoice_id = $this->securityLibObj->encrypt($invoices->invoice_id);
                                    $invoices->pat_profile_img = !empty($invoices->pat_profile_img) ? url('api/patient-profile-image/'.$this->securityLibObj->encrypt($invoices->pat_profile_img)) : '';
                                    $invoices->pat_dob = $this->dateTimeLibObj->ageCalculation($invoices->pat_dob,'Y-m-d');
                                    $invoices->total_amt = $invoices->doc_consult_fee - $invoices->discount;
                                    return $invoices;
                                });
        return $queryResult;
	}

    /**
     * @DateOfCreation        05 Oct 2018
     * @ShortDescription      This function is responsible to save record for the Payment 
                               History from visit section
     * @param                 array $requestData
     * @return                integer auto increment id
     */
    public function createPaymentsHistoryFromVisit($data)
    {
        $insertData = [];
        $insertData['user_id']          = $data['user_id'];
        $insertData['pat_id']           = $data['pat_id'];
        $createNewPayment               = $this->checkLastPaymentValidity($data['user_id'], $data['pat_id']);
        if($createNewPayment){
            $insertData['payment_mode_id']  = $this->getDefaultPaymentMode($data['user_id']);
            $insertData['checkup_type_id']  = $this->getDefaultCheckupType($data['user_id']);
            $insertData['amount']           = $this->getDoctorConsultFee($data['user_id']);
            $insertData['reciept_number']   = $this->utilityLibObj->alphanumericString();
            $insertData['resource_type']    = $data['resource_type'];
            $insertData['ip_address']       = $data['ip_address'];

            // Prepair insert query
            $response = $this->dbInsert($this->tablePayments, $insertData);
            if($response){
                $id = DB::getPdo()->lastInsertId();
                return $id;
            }else{
                return $response;
            }          
        }
    }

    /**
     * @DateOfCreation        05 Oct 2018
     * @ShortDescription      This function is responsible to get default Payment Mode
     * @param                 array $requestData
     * @return                integer auto increment id
     */
    public function getDefaultPaymentMode($userId)
    {
        $response = DB::table($this->tablePaymentMode)
                            ->select('payment_mode_id')
                            ->where('is_deleted', '=', Config::get('constants.IS_DELETED_NO'))
                            ->where('user_id', '=', $userId)
                            ->first();
        return (!empty($response)) ? $response->payment_mode_id : null;
    }

    /**
     * @DateOfCreation        05 Oct 2018
     * @ShortDescription      This function is responsible to get default checkup type
     * @param                 array $requestData
     * @return                integer auto increment id
     */
    public function getDefaultCheckupType($userId)
    {
        $response = DB::table($this->tableCheckupType)
                            ->select('checkup_type_id')
                            ->where('is_deleted', '=', Config::get('constants.IS_DELETED_NO'))
                            ->where('user_id', '=', $userId)
                            ->first();
        return (!empty($response)) ? $response->checkup_type_id : null;
    }

    /**
     * @DateOfCreation        05 Oct 2018
     * @ShortDescription      This function is responsible to get default checkup type
     * @param                 array $requestData
     * @return                integer auto increment id
     */
    public function getDoctorConsultFee($userId)
    {
        $response = DB::table($this->tableDoctors)
                            ->select('doc_consult_fee')
                            ->where('is_deleted', '=', Config::get('constants.IS_DELETED_NO'))
                            ->where('user_id', '=', $userId)
                            ->first();
        return (!empty($response)) ? $response->doc_consult_fee : null;
    }

    /**
     * @DateOfCreation        05 Oct 2018
     * @ShortDescription      This function is responsible to check if last payment is still valid
     * @param                 array $requestData
     * @return                integer auto increment id
     */
    public function checkLastPaymentValidity($userId,$patId)
    { 
        $response = true;
        $result = DB::table($this->tablePayments)
                            ->select('created_at')
                            ->where('is_deleted', '=', Config::get('constants.IS_DELETED_NO'))
                            ->where('user_id', '=', $userId)
                            ->where('pat_id', '=', $patId)
                            ->orderby('payment_id', 'DESC')
                            ->first();

        if(!empty($result)){
            $today = new DateTime();
            $dateEnd = new DateTime(substr($result->created_at, 0, 10));
            $dateDiff = $today->diff($dateEnd);
            $diffDays = $dateDiff->d;

            if((int)$diffDays < Config::get('constants.PAYMENT_VALID_DAYS')){
                $response = false;
            }
        }
        return $response;
    }
}
