<?php
namespace App\Modules\Setup\Models;
use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\HasApiTokens;
use App\Traits\Encryptable;
use Illuminate\Support\Facades\DB;
use App\Libraries\SecurityLib;
use Config;
use App\Traits\RestApi;
use App\Libraries\UtilityLib;


/**
 * Symptoms
 *
 * @package                ILD
 * @subpackage             Symptoms
 * @category               Model
 * @DateOfCreation         11 june 2018
 * @ShortDescription       This Model to handle database operation with current table
                           City
 **/
class Symptoms extends Model {

    use HasApiTokens,Encryptable,RestApi;

    /**
     * Create a new model instance.
     *
     * @return void
     */
    public function __construct()
    {
        // Init security library object
        $this->securityLibObj = new SecurityLib();

        // Init UtilityLib library object
        $this->utilityLibObj = new UtilityLib();

    }

    /**
    *@ShortDescription Table for the Users.
    *
    * @var String
    */
    protected $table = 'symptoms';
    
    // @var Array $encryptedFields
    // This protected member contains fields that need to encrypt while saving in database
    protected $encryptable = [];
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['symptom_id', 'symptom_name','snomedct_concept_id','snomedct_id'];

    /**
     *@ShortDescription Override the primary key.
     *
     * @var string
     */
    protected $primaryKey = 'symptom_id';

    /**
    * @DateOfCreation        30 May 2018
    * @ShortDescription      This function is responsible to get the symptoms option list
    * @param                 String $stateId   
    * @return                object Array of city records
    */
    public function getSymptomsOptionList($symptomName = '')
    {
        $queryResult = DB::table($this->table)
            ->select('symptom_id', 'symptom_name')
            ->where('is_deleted', Config::get('constants.IS_DELETED_NO'));
        if(!empty($symptomName)){
            $queryResult = $queryResult->where('symptom_name', 'ilike', $symptomName.'%');
        }        
        $queryResult = $queryResult->get()
            ->map(function($symptomLists){
            $symptomLists->symptom_id = $this->securityLibObj->encrypt($symptomLists->symptom_id);
            return $symptomLists;
        });
        return $queryResult;
    }

    /**
    * @DateOfCreation        30 May 2018
    * @ShortDescription      This function is responsible to get the symptoms option list
    * @param                 String $stateId   
    * @return                object Array of city records
    */
    public function getSymptomsOptionListSearchByName($symptomName)
    {
        if(!empty($symptomName)){
        $queryResult = DB::table($this->table)
            ->select('symptom_id', 'symptom_name','snomedct_concept_id','snomedct_id', DB::raw("concat_ws('_',snomedct_concept_id,snomedct_id) as snomedct_concept_id_concat"))
            ->where('is_deleted', Config::get('constants.IS_DELETED_NO'));
            $queryResult = $queryResult->where('symptom_name', 'ilike', '%'.$symptomName.'%');
        $queryResult = $queryResult->get()
            ->map(function($symptomLists){
            $symptomLists->symptom_id = $this->securityLibObj->encrypt($symptomLists->symptom_id);
            return $symptomLists;
        });
        return $queryResult;
        }else{
            return [];
        }       
    }

    /**
    * @DateOfCreation        30 May 2018
    * @ShortDescription      This function is responsible to get the symptoms option list from Snomedct api
    * @param                 String $stateId   
    * @return                object Array of city records
    */
    public function getSymptomsOptionListBySnomedct($symptomName,$symptomsList=[]){
        $symptomsList = count($symptomsList)> 0 ? json_decode(json_encode($symptomsList),TRUE) :[];
        $symptomsListLower = !empty($symptomsList) ? array_map(function($row){
            $row['symptom_name'] = strtolower($row['symptom_name']);
            return $row;
        },$symptomsList):[];
        $symptomsListLower = !empty($symptomsListLower) ? $this->utilityLibObj->changeArrayKey($symptomsListLower,'symptom_name') :[];
        $url =Config::get('constants.SNOMEDCT_API_URL');
        $data['term'] = $symptomName;
        $data['state'] = Config::get('constants.SNOMEDCT_STATE_AVTIVE');
        $data['semantictag'] = Config::get('constants.SNOMEDCT_SEMANTICTAG');
        $data['acceptability'] = Config::get('constants.SNOMEDCT_ACCEPTABILITY_PREFERRED');
        $data['returnlimit'] = Config::get('constants.SNOMEDCT_RETURNLIMIT_UNLIMITED');
        $data['groupbyconcept'] = Config::get('constants.SNOMEDCT_GROUP_BY_CONCEPT_FALSE');
        $data['refsetid'] = null;
        $data['parentid'] = null;
        $data = $this->curl_call($url,$data,'get',['header'=>['content-type'=>'*/*']]);
        $output= ($data['status']== true && !empty($data['data'])) ? array_map(function($row) use($symptomsListLower){
            if(!isset($symptomsListLower[strtolower($row['term'])])){
                $newRow=[];
                $newRow['symptom_id']   = $this->securityLibObj->encrypt($row['conceptId'].'_'.$row['id']);
                $newRow['symptom_name'] = $row['term'];
                return $newRow;
            }else{
                return false;
            }
        },$data['data']):[];
         
        return array_merge( $symptomsList,array_filter($output));
    }
}
