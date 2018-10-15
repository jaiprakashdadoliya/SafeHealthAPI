<?php
namespace App\Libraries;
use Illuminate\Http\Request;
use PDF;
/**
 * PdfLib Class
 *
 * @package                Safe Health
 * @subpackage             PdfLib
 * @category               Library
 * @DateOfCreation         10 Apr 2018
 * @ShortDescription       This class is responsible for Generating PDF from Html.

 */
class PdfLib {

	/**
     * Create a new library instance.
     *
     * @return void
     */
    public function __construct()
    {
  
    }
    /**
    * @DateOfCreation        10 Apr 2018
    * @ShortDescription      This function is responsible to Create PDF from HTML and download.
    * @param                 String $view // Path of view file
    						 Array $data  
    						 String $outputfile
    						 String $paper   //  letter, legal, A4
    						 String $orientation //  landscape , portrait		
    * @return                Download file
    */
    public function genrateAndDownloadPdf($view, $data = array(), $paper = 'a4', $orientation = 'landscape',$outputfile)
    {   
        $pdf = PDF::loadView($view,$data);
        return $pdf->setPaper($paper, $orientation)->download($outputfile);
    }

     /**
    * @DateOfCreation        10 Apr 2018
    * @ShortDescription      This function is responsible to Create PDF from HTML and show it in browser.
    * @param                 String $view // Path of view file
    						 Array $data  
    * @return                Show pdf in browser
    */
    public function genrateAndShowPdf($view, $data = array())
    {   
        $pdf = PDF::loadView($view,$data);
        return $pdf->stream();
    }

     /**
    * @DateOfCreation        16 Apr 2018
    * @ShortDescription      This function is responsible to Create PDF and save in the 
                             given path
    * @param                 String $view // Path of view file
                             Array $data
                             String Filepath ( File path with file name like : public/Folder/filename.pdf )  
    * @return                Show pdf in browser
    */
    public function genrateAndSavePdf($view, $data = array(), $filepath, $savedFileName)
    {
        $filelibObj = new FileLib();
        $checkDirectory = $filelibObj->createDirectory($filepath);
        if($checkDirectory['code'] == '5000'){
            return ['code' => '5000', 'message' => __('messages.5027')];  
        }
        $pdf = PDF::loadView($view, $data);
        return $pdf->save($filepath.'/'.$savedFileName);
    }
}