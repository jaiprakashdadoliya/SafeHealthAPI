<?php
namespace App\Libraries;
use Illuminate\Http\Request;
use File;
use Image;
use App\Libraries\FileLib;
use Storage;
/**
 * ImageLib Class
 *
 * @package                Safe Health
 * @subpackage             ImageLib
 * @category               Library
 * @DateOfCreation         05 Apr 2018
 * @ShortDescription       This class is responsible for all type of image manipulation functions 
                           like Upload image, Watermark, thumbnail etc. 
 */
class ImageLib {
    /**
     * Create a new library instance.
     *
     * @return void
     */
    public function __construct()
    {
         
    }
  
    /**
    * @DateOfCreation        05 Apr 2018
    * @ShortDescription      This function is responsible to generate thumbnail according to the passed 
    * @param                 String $source
                             Multidimentional Array $thumbArray(
                                [
                                $thumb_path  (Type String) // Thumbnail Destination path 
                                $thumb_name (Type String)  // Name of new thumbnail
                                $width (Type Int)          // Width of thumbnail 
                                $height (Type Int)         // Height of thumbnail 
                                ],
                              )
    * @return               Array  ( With status of each )  
    */
    public function genrateThumbnail($source,$thumbArray) {
        if(!empty($thumbArray)){
            $thumbUploadStatus = [];
            foreach ($thumbArray as $key => $value) {
                $destinationThumbPath = storage_path('app/public/'.$value['thumb_path']);
                $img = Image::make(storage_path('app/public/'.$source));
                $img->resize($value['width'],$value['height'],function ($constraint) {
                    $constraint->aspectRatio();
                });
                $filelibObj = new FileLib();
                $checkDirectory = $filelibObj->createDirectory($destinationThumbPath);
                if($checkDirectory['code'] == '5000'){
                    return ['code' => '5000', 'message' => __('messages.5027')];
                }
                // Save the thumbnail to given path
                $thumbUpload = $img->save($destinationThumbPath.'/'.$value['thumb_name']);
                // Check Thumbnail Saved or not
                if($thumbUpload){
                    $result = true;
                    $thumbUploadStatus[$key] =  ['code' => '1000','uploaded_file' => $value['thumb_name'],'thumb_key' => $key];
                }else{
                    $thumbUploadStatus[$key] =  ['code' => '5000','uploaded_file' => $value['thumb_name'],'thumb_key' => $key];
                }
            }
            return $thumbUploadStatus;
        }else{
            return $thumbUploadStatus = ['code' => '5000','message' => __('messages.5006'),'thumb_key' => $key];
        }
    }
    /**
    * @DateOfCreation        05 Apr 2018
    * @ShortDescription      This function is responsible to add watermark 
                             image on each original image according to the position.
                             Position  parameter is depend on the X and Y value you have to manage  both according your need.
    * @param                Array $originalImages  (
                                $filepath        // Full path of source image
                            )
                            String $watermarkImage
                            String $position  
                            @position Options
                                    * top-left (default)
                                    * top
                                    * top-right
                                    * left
                                    * center
                                    * right
                                    * bottom-left
                                    * bottom
                                    * bottom-right
                            Integer $x       // Horizontal value for position
                            Integer $y      // Vertical value for position 
    * @return               Array  ( With status of each )  
    */
    public function watermarkImage($originalImages,$watermarkImage,$position,$x,$y) {
        $waterMarkStatus = [];
        if(!empty($originalImages) && !empty($watermarkImage)){
            foreach ($originalImages as $key => $value) {
                // open an image file
                $img = Image::make(public_path($value['filePath']));
                
                // Image Watermark
                $img->insert(public_path($watermarkImage), $position, $x, $y);
                $checkImage = $img->save(public_path($value['filePath']));
                if($checkImage){
                    $waterMarkStatus[$key] = ['code' => '1000','message' => __('messages.1006'),'image_key' => $key];
                }else{
                    $waterMarkStatus[$key] = ['code' => '5000','message' => __('messages.5007'),'image_key' => $key];
                }
            }
            return $waterMarkStatus;
        }else{
            return $waterMarkStatus = ['code' => '5000','message' => __('messages.5008'),'image_key' => $key];
        }
    }

    /**
    * @DateOfCreation        05 Apr 2018
    * @ShortDescription      This function is responsible to add watermark text on 
                             each original , Font size and angle works only if the 
                             font file is set otherwise they both are ignored. Position 
                             parameter is depend on the X and Y value you have to manage
                             both according your need. 
    * @param                 Array $originalImages  (
                                $filepath      // Full path of source image
                             )
                             String $watermarkText  // Text for watermark
                             Integer $x     // Horizontal value for position
                             Integer $y    // Vertical value for position 
                             Array $setting (
                                String $fontFilePath // Path of font file. Default:''
                                String $textcolor // Hexadecimal color code. Default: #000000
                                Integer $fontsize  //  Default: 12 
                                String $horizontalAlign // left, right and center. Default: left
                                String $verticalAlign // top, bottom and middle. Default: bottom
                                Integer $angle  // - for Clockwise and + for anticlockwise. Default: 0   
                              )
    * @return                Array  ( With status of each )  
    */
    public function watermarkText($originalImages,$watermarkText,$x,$y,$setting = []) {
        $waterMarkStatus = [];
        $fontFilePath = (!empty($setting['fontFilePath']) ? $setting['fontFilePath'] : '');
        $textColor = (!empty($setting['textColor']) ? $setting['textColor'] : '#fff');
        $fontsize  = (!empty($setting['fontsize']) ? $setting['fontsize'] : '12');
        $horizontalAlign =  (!empty($setting['horizontalAlign']) ? $setting['horizontalAlign'] : 'left');
        $verticalAlign = (!empty($setting['verticalAlign']) ? $setting['verticalAlign'] : 'bottom');
        $angle = (!empty($setting['angle']) ? $setting['angle'] : 0);
        if(!empty($originalImages) && $watermarkText != ''){
            foreach ($originalImages as $key => $value) {
                $filePath = public_path($value['filePath']);
                if (File::exists($filePath)) { 
                    $img = Image::make($filePath);
                    
                    // Text Watermark 
                    $img->text($watermarkText, $x, $y, function($font) use($fontFilePath,$textColor,$fontsize,$horizontalAlign,$verticalAlign,$angle){
                        $font->file(public_path($fontFilePath));  
                        $font->size($fontsize);
                        $font->color($textColor); 
                        $font->align($horizontalAlign);
                        $font->valign($verticalAlign);
                        $font->angle($angle); 
                    });
                    
                    $checkImage = $img->save(public_path($value['filePath']));
                    
                    if($checkImage){
                        $waterMarkStatus[$key] = ['code' => '1000','message' => __('messages.1007'),'image_key' => $key];
                    }else{
                        $waterMarkStatus[$key] = ['code' => '5000','message' => __('messages.5009'),'image_key' => $key];
                    }
                }else{
                     $waterMarkStatus[$key] = ['code' => '5000','message' => __('messages.5004'),'image_key' => $key];
                }
            }
            return $waterMarkStatus;
        }else{
            return $waterMarkStatus = ['code' => '5000','message' => __('messages.5010'),'image_key' =>''];
        }
    }
     /**
    * @DateOfCreation        05 Apr 2018
    * @ShortDescription      This function is responsible to add rotate image each  
                             image according to the angle given.
    * @param                 Array $originalImages  (
                                $filepath      // Full path of source image
                             ) 
                             Integer $angle  // - for Clockwise and + for anticlockwise
    * @return                Array  ( With status of each )  
    */
    public function rotateImage($originalImages,$angle) {
        $rotateStatus = [];
        if(!empty($originalImages)){ 
            foreach ($originalImages as $key => $value) {
                $img = Image::make(public_path($value['filePath']));
                $img->orientate();
                $img->rotate($angle);
                $checkRoate = $img->save(public_path($value['filePath']));
                if($checkRoate){
                    $rotateStatus[$key] = ['code' => '1000','message' => __('messages.1008'),'image_key' => $key];
                }else{
                    $rotateStatus[$key] = ['code' => '5000','message' => __('messages.5011'),'image_key' => $key];
                }
            }
            return $rotateStatus;
        }
        else{
            return $rotateStatus = ['code' => '5000','message' => __('messages.5012'),'image_key' =>''];
        }
    }

    /**
    * @DateOfCreation        05 Apr 2018
    * @ShortDescription      This function is responsible to crop image, 
                             If you put $x and $y 0 ,then image will be crop
                             from center
    * @param                 String $sourcePath
                             String $destinationPath // Path output file with file name 
                             Integer $height  
                             Integer $width
                             Integer $x // Default : 0
                             Integer $y // Default : 0
    * @return                Array  ( With status of each )  
    */
    public function cropImage($sourcePath, $destinationPath, $height, $width, $x = 0, $y = 0)
    {
        $filepath = public_path($sourcePath);
        $outputFile = public_path($destinationPath);
        if (! File::exists($filepath)) { 
            return ['code' => '5000','message' => __('messages.5004')];
        }else{
            $img = Image::make($filepath);
            if($x > 0 OR $y > 0){
                $img->crop($width, $height, $x, $y);
            }else{
                $img->crop($width, $height);                
            }
            $checkCrop = $img->save($outputFile);
            if($checkCrop){
                return ['code' => '1000','message' => __('messages.1027')];
            }else{
                return ['code' => '5000','message' => __('messages.5028')];
            }
        }   
    }
}