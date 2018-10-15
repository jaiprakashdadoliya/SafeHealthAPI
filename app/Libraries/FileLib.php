<?php
namespace App\Libraries;
use Illuminate\Http\Request;
use File;
use Storage;
use Config;
use Uuid;

/**
 * FileLib Class
 *
 * @package                Safe Health
 * @subpackage             FileLib
 * @category               Library
 * @DateOfCreation         05 Apr 2018
 * @ShortDescription       This class is responsible for all type of file manipulation functions 
                           like Upload file, Copy file, Rename file, Move file etc. 
                           For Storage functions i have define 'root' => storage_path('app/public'),
                           in config/filesystem.php
 */
class FileLib {
    
    // @var String $fileprefix
    // This protected member contains random 3 digit string
    protected $fileprefix = '';

    // @var String $fileprefix
    // This protected member contains permission of directory
    protected $filepermission = '';

    /**
     * Create a new library instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->fileprefix  = Config::get('constants.FILEPREFIX');
        $this->filepermission  = Config::get('constants.FILEPERMISSION');
    }

    public function createDirectory($directoryPath)
    {
             if (! File::exists($directoryPath)) {
                File::makeDirectory($directoryPath, $this->filepermission, true);
                return ['code' => '1000', 'message' => __('messages.1024')]; 
        }
    }

    /**
     * @DateOfCreation        05 Apr 2018
     * @ShortDescription      This function is responsible to upload file .
     * @param                 String $filename
                             String $destinationPath
     * @return                Array  ( With status of operation and message) 
     */
    public function fileUpload($filename, $destinationPath)
    {
        $utilitylibObj = new UtilityLib();
        $result = false;
        $randomString = Uuid::generate();
        $input['filename'] = $randomString.'.'.$filename->getClientOriginalExtension();
        $destinationPath = storage_path('app/public/'.$destinationPath);
        $checkDirectory = $this->createDirectory($destinationPath);
        if($checkDirectory['code'] == '5000'){
            return ['code' => '5000', 'message' => __('messages.5027')];
        }
        $upload = $filename->move($destinationPath, $input['filename']);
        
        // Check File uploaded or not
        if($upload){
            return ['code'=>'1000','message' => __('messages.1012'), 'uploaded_file' => $input['filename']];
         }else{
            return ['code'=>'5000','message' => __('messages.5013'), 'uploaded_file' => ''];

        }
    }
    
    /**
     * @DateOfCreation        05 Apr 2018
     * @ShortDescription       This function is responsible to rename file.
     * @param                 String $filePath                             
                             String $oldname
                             String $newname    
     * @return               Array  ( With status of file and message ) 
     */
    public function renameFile($oldname, $newname, $filePath)
    {
        if (File::isWritable($filePath))
        { 
            try{
                $check = Storage::disk('local')->move($filePath.'/'.$oldname,$filePath.'/'.$newname);
                if($check){
                    return ['code' => '1000', 'message' => __('messages.1002')];
                }else{
                    return ['code' => '5000', 'message' => __('messages.5002')];
                }
            }catch (\Exception $e) {
                return ['code' => '3000', 'message' => $e->getMessage()];
            }
        }else{
           return ['code' => '5000', 'message' => __('messages.5027')]; 
        }    
    }

    /**
     * @DateOfCreation        05 Apr 2018
     * @ShortDescription      This function is responsible to copy one file to any other 
                             location according to the parameter.
     * @param                 String $sourcePath                             
                             String $destinationPath 
     * @return                Array  ( With status of operation and message)  
     */
    public function copyFile($sourcePath, $destinationPath) 
    {
        if(Storage::disk('local')->exists($sourcePath)){
            try{
                $check = Storage::disk('local')->copy($sourcePath, $destinationPath);
                if($check){
                    return ['code' => '1000', 'message' => __('messages.1003')];
                }else{
                    return ['code' => '5000', 'message' => __('messages.5003')];
                }
            }catch (\Exception $e){
                return ['code' => '3000', 'message' => $e->getMessage()];
            }
        }else{
            return ['code' => '5000', 'message' => __('messages.5004')];
        }
    }

    /**
     * @DateOfCreation        05 Apr 2018
     * @ShortDescription      This function is responsible to move one file to any other 
                             location according to the parameter.
     * @param                 String $sourcePath                             
                             String $destinationPath 
     * @return                Array  ( With status of operation and message)  
     */
    public function moveFile($sourcePath, $destinationPath)
    {
        try{
            $check = Storage::disk('local')->move($sourcePath, $destinationPath);
            if($check){
                return ['code' => '1000', 'message' => __('messages.5004')];
            }else{
                return ['code' => '5000', 'message' => __('messages.5005')];
            }
        }catch (\Exception $e) {
                return ['code' => '3000', 'message' => $e->getMessage()];
            }
    }

    /**
     * @DateOfCreation        05 Apr 2018
     * @ShortDescription       This function is responsible to download the file according to the 
                            path given.
     * @param                 String $sourcePath                             
                             String $outputName 
     * @return                Array  ( With status of operation and message)  
     */
    public function downloadFile($sourcePath, $outputName)
    {
        if(Storage::disk('local')->exists($sourcePath) && is_readable($sourcePath)){
            return response()->download(public_path($sourcePath),$outputName);
        }else{
            return ['code' => '5000', 'message' => __('messages.5004')];
        }
    }

    /**
     * @DateOfCreation        05 Apr 2018
     * @ShortDescription      This function is responsible to set the permission of the 
                             file
     * @param                 String $filePath                             
                             String $visibility (two option public,private by default public) 
     * @return                Array  ( With status of operation and message)  
     */
    public function setPermission($filePath, $visibility = 'public')
    {
        try{
            $visibilityStatus = Storage::disk('local')->setVisibility($filePath, $visibility);
            return ['code' => '1000', 'message' => __('messages.1005')];
        }catch (\Exception $e){
            return ['code' => '3000', 'message' => $e->getMessage()];
        }
    }

    /**
     * @DateOfCreation        18 May 2018
     * @ShortDescription      This function is responsible to convert base64  to the
                              path given.
     * @param                 String data
     * @param                 String output file
     * @return                string  ( With status of operation and message)
     */
    public function base64ToPng($data, $outputFile, $filename) {
        list($type, $data) = explode(';', $data);
        list(, $data)      = explode(',', $data);
        $data = base64_decode($data);
        $randomString = Uuid::generate();
        $filename = $randomString.'.'.$filename;
        $destinationPath = $outputFile;
        $upload = file_put_contents($destinationPath.$filename, $data);
        if($upload){
            return ['code'=>'1000','message' => __('messages.1012'), 'uploaded_file' => $filename];
        }else{
            return ['code'=>'5000','message' => __('messages.5013'), 'uploaded_file' => ''];

        }
    }
}