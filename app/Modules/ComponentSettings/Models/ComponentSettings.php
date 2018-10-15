<?php

namespace App\Modules\ComponentSettings\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Laravel\Passport\HasApiTokens;
use App\Traits\Encryptable;
use App\Libraries\SecurityLib;
use Config;
use Carbon\Carbon;

class ComponentSettings extends Model {
	use HasApiTokens,Encryptable;

    // @var string $table
    // This protected member contains table name
    protected $table = 'visits_components_settings';

    // @var string $primaryKey
    // This protected member contains primary key
    protected $primaryKey = 'com_set_id';

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
     * Get Component List with regarding details
     *
     * @param array $requestData 
     * @return array of components lists
     */
    public function getComponentsList($requestData) { 
        // DB::enableQueryLog();
        $selectData  =  ['visits_components_settings.visit_cmp_set_id','visits_components.component_title','visits_components.component_container_name'];
        $whereData   =  array(
        					'visits_components.is_deleted' => Config::get('constants.IS_DELETED_NO'),
                            'visits_components_settings.is_visible' => Config::get('constants.IS_DELETED_NO'),
                            'doctors_specialisations.user_id' => $requestData['user_id'] 
                        ); 
        $query =  DB::table($this->table)
                    ->join('visits_components',$this->table.'.visit_cmp_id','visits_components.visit_cmp_id')
        			->join('specialisations',$this->table.'.spl_id','specialisations.spl_id')
                    ->join('doctors_specialisations','specialisations.spl_id','doctors_specialisations.spl_id')
                    ->select($selectData)
                    ->where($whereData);
        $component_settings =  $query
                        ->get()
                        ->map(function($component_settings){
                            $component_settings->visit_cmp_set_id = $this->securityLibObj->encrypt($component_settings->visit_cmp_set_id);
                            return $component_settings;
                        });
        return $component_settings;
    }

}
