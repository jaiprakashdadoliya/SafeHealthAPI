<?php

namespace App\Modules\DoctorProfile\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Modules\Auth\Models\Auth as Users;
use App\Modules\DoctorProfile\Models\DoctorMedia;
use App\Traits\SessionTrait;
use App\Traits\RestApi;
use Config;
use App\Libraries\SecurityLib;
use App\Libraries\FileLib;
use App\Libraries\ImageLib;
use File;
use Response;

/**
 * DoctorMediaController
 *
 * @package                ILD India Registry
 * @subpackage             DoctorMediaController
 * @category               Controller
 * @DateOfCreation         23 may 2018
 * @ShortDescription       This controller to handle all the operation related to
doctors media
 **/

class DoctorMediaController extends Controller
{
    use SessionTrait, RestApi;

    // @var Array $http_codes
    // This protected member contains Http Status Codes
    protected $http_codes = [];
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->http_codes = $this->http_status_codes();

        // Init security library object
        $this->securityLibObj = new SecurityLib();

        // Init Doctor Media model object
        $this->doctorMedia = new DoctorMedia();

        // Init File Library object
        $this->FileLib = new FileLib();

        // Init Image Library object
        $this->ImageLib = new ImageLib();
    }

    /**
     * Getting all medias.
     *
     * @param  \Illuminate\Http\Request  $doctorId
     * @return \Illuminate\Http\Response
     */
    public function getAllMedia(Request $request) {

        $requestData = $this->getRequestData($request);
        $requestData['doctorId'] = $request->user()->user_id;

        if(isset($requestData['patient_id'])){
            $requestData['doctorId'] = $this->securityLibObj->decrypt($requestData['patient_id']);            
        }

        if($requestData['doctorId'] == ''){
            return $this->resultResponse(
                Config::get('restresponsecode.ERROR'),
                [],
                [],
                trans('DoctorProfile::messages.media_id_error'),
                $this->http_codes['HTTP_OK']
            );
        }

        $doctorMedia = $this->doctorMedia->getMedia($requestData);

        if(count($doctorMedia) > 0) {
            return $this->resultResponse(
                Config::get('restresponsecode.SUCCESS'),
                $doctorMedia,
                [],
                trans('DoctorProfile::messages.media_success'),
                $this->http_codes['HTTP_OK']
            );
        } else {
            return $this->resultResponse(
                Config::get('restresponsecode.SUCCESS'),
                [],
                [],
                trans('DoctorProfile::messages.media_success'),
                $this->http_codes['HTTP_OK']
            );
        }

    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function addMedia(Request $request){
        
        $requestData = $this->getRequestData($request);
        $user_id     = $request->user()->user_id;
        $user_type   = $request->user()->user_type;

        if(isset($requestData['patient_id']) && !empty($requestData['patient_id']) && $requestData['patient_id'] != 'undefined'){
            $user_id   = $this->securityLibObj->decrypt($requestData['patient_id']);
            $user_type = Config::get('constants.USER_TYPE_PATIENT');
        }

        // Validate request
        $validate = $this->DoctorMediaValidator($requestData, $user_type);

        if($validate["error"]){
            return $this->resultResponse(
                Config::get('restresponsecode.ERROR'),
                [],
                $validate['errors'],
                trans('DoctorProfile::messages.media_upload_error'),
                $this->http_codes['HTTP_OK']
            );
        }

        $isMediaAdded = false;
        $destination = Config::get('constants.DOCTOR_MEDIA_PATH');
        $fileUpload = $this->FileLib->fileUpload($requestData['doc_media_file'], $destination);

        $fileType = NULL;
        if(isset($fileUpload['code']) && $fileUpload['code'] == Config::get('restresponsecode.SUCCESS')){
            $getFileType = explode('.', $fileUpload['uploaded_file']);
            $fileType    = $getFileType[1];
        }

        $thumbGenerate = [];
        if($fileType && $fileType != 'pdf'){
            $thumbPath =  Config::get('constants.DOCTOR_MEDIA_THUMB_PATH');
            $thumb = [];
            $thumbName = $fileUpload['uploaded_file'];
            $thumb = array(['thumb_name' => $thumbName,'thumb_path' => $thumbPath,'width' => 350 , 'height' => 250]);
            $thumbGenerate = $this->ImageLib->genrateThumbnail($destination.$fileUpload['uploaded_file'],$thumb);            
        }else if($fileType && $fileType == 'pdf'){
            $thumbGenerate[0]['code'] = Config::get('restresponsecode.SUCCESS');
            $thumbGenerate[0]['uploaded_file'] = $fileUpload['uploaded_file'];
        }

        $mediaData = array();
        if((isset($thumbGenerate[0]) && $thumbGenerate[0]['code']) && $thumbGenerate[0]['code'] == 1000) {
            $mediaData = array("user_id" => $user_id,
                "doc_media_file" => $thumbGenerate[0]['uploaded_file'],
                'user_type'  => $user_type,
                "created_by" => $user_id,
                "updated_by" => $user_id,
                "ip_address" => $requestData['ip_address']
                );
            try {
                    $isMediaAdded = $this->doctorMedia->insertMedia($mediaData);
                    $mediaData['doc_media_id']   = $this->securityLibObj->encrypt($isMediaAdded);
                    $mediaData['doc_media_file'] = $this->securityLibObj->encrypt($thumbGenerate[0]['uploaded_file']);
                    $mediaData['doc_type']       = $fileType;
                } catch (\Exception $e) {
                    if(File::exists($destination.$fileUpload['uploaded_file'])){
                        File::delete($destination.$fileUpload['uploaded_file']);
                    }

                    if(File::exists($thumbPath.$fileUpload['uploaded_file'])){
                        File::delete($thumbPath.$fileUpload['uploaded_file']);
                    }
                }
        }

        // validate, is query executed successfully
        if($isMediaAdded){
            return $this->resultResponse(
                Config::get('restresponsecode.SUCCESS'),
                $mediaData,
                [],
                trans('DoctorProfile::messages.media_upload_success'),
                $this->http_codes['HTTP_OK']
            );
        }else{
            return $this->resultResponse(
                Config::get('restresponsecode.ERROR'),
                [],
                [],
                trans('DoctorProfile::messages.media_upload_error'),
                $this->http_codes['HTTP_OK']
            );
        }
    }

    /**
     * Deleting a media from storage.
     *
     * @param  \Illuminate\Http\Request  $doc_media_id
     * @return \Illuminate\Http\Response
     */
    public function deleteMedia(Request $request)
    {
        $requestData = $this->getRequestData($request);
        $primaryKey = $this->doctorMedia->getTablePrimaryIdColumn();
        $primaryId = $this->securityLibObj->decrypt($requestData[$primaryKey]);
        $isPrimaryIdExist = $this->doctorMedia->isPrimaryIdExist($primaryId);

        if(!$isPrimaryIdExist){
            return $this->resultResponse(
                Config::get('restresponsecode.ERROR'), 
                [], 
                [$primaryKey=> [trans('DoctorProfile::messages.media_id_error')]],
                trans('DoctorProfile::messages.media_id_error'), 
                $this->http_codes['HTTP_OK']
            ); 
        }

        $isMediaDeleted = $this->doctorMedia->deleteMedia($primaryId);
        if($isMediaDeleted) {
            return $this->resultResponse(
                Config::get('restresponsecode.SUCCESS'),
                [],
                [],
                trans('DoctorProfile::messages.media_delete_success'),
                $this->http_codes['HTTP_OK']
            );
        }
    }


    /**
     * @DateOfCreation        14 May 2018
     * @ShortDescription      This function is responsible for validating blog data
     * @param                 Array $data This contains full doctor media input data
     * @return                VIEW
     */
    protected function DoctorMediaValidator(array $data, $userType)
    {
        $error = false;
        $errors = [];

        $validator = Validator::make($data, [
            'doc_media_file' => $userType == Config::get('constants.USER_TYPE_PATIENT') ? 'required|max:4096|mimes:png,jpg,jpeg,pdf' : 'required|max:4000|mimes:png,jpg,jpeg|dimensions:max_width=1920,max_height=1200'
        ]);

        if($validator->fails()){
            $error = true;
            $errors = $validator->errors();
        }
        return ["error" => $error,"errors" => $errors];
    }

    /**
     * @DateOfCreation        22 May 2018
     * @ShortDescription      This function is responsible to get the image path
     * @param                 String $imageName
     * @return                response
     */
    public function getMedia($imageType = 0, $imageName, Request $request)
    {
        $requestData = $this->getRequestData($request);
        $imageName = $this->securityLibObj->decrypt($imageName);
        $imagePath = ($imageType ==  0) ? 'app/public/'.Config::get('constants.DOCTOR_MEDIA_PATH') : 'app/public/'.Config::get('constants.DOCTOR_MEDIA_THUMB_PATH');
        $path = storage_path($imagePath) . $imageName;

        if(!File::exists($path))
            $path = storage_path('app/public/'.Config::get('constants.DOCTOR_MEDIA_PATH')) . 'default.png';

        $file = File::get($path);
        $type = File::mimeType($path);

        if($type == 'pdf'){
            $headers = ['Content-Type: '.$type];
            return response()->file($path, $headers);            
        }

        $response = Response::make($file, 200);
        $response->header("Content-Type", $type);
        return $response;
    }


}
